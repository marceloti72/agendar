<?php
require_once("../sistema/conexao2.php"); // Ajuste o caminho se necessário
@session_start();
$id_conta = @$_SESSION['id_conta']; // Garanta que id_conta está vindo da sessão ou outra fonte confiável

// --- Recebimento e Validação Inicial ---
$funcionario = isset($_POST['funcionario']) ? $_POST['funcionario'] : null;
$data = isset($_POST['data']) ? $_POST['data'] : null;
$hora_rec = isset($_POST['hora']) ? $_POST['hora'] : null; // Hora pré-selecionada
$nome = isset($_POST['nome']) ? $_POST['nome'] : null; // Hora pré-selecionada
$whatsapp = isset($_POST['telefone']) ? $_POST['telefone'] : null; // Hora pré-selecionada


// Validações essenciais
if (empty($funcionario)) {
    echo '<small class="text-danger">Selecione um Profissional.</small>';
    exit();
}
if (empty($data)) {
    echo '<small class="text-danger">Selecione uma Data.</small>';
    exit();
}

// Valida data (formato e se é passada)
try {
    $dataObj = new DateTime($data);
    $dataObj->setTime(0, 0, 0);
    $hojeObj = new DateTime('today');
    if ($dataObj < $hojeObj) {
        echo '000'; // Código JS para data passada
        exit();
    }
} catch (Exception $e) {
    echo '<small class="text-danger">Formato de data inválido.</small>';
    exit();
}

// --- Verifica Bloqueios ---
// Bloqueio Geral
$query_bloq_geral = $pdo->prepare("SELECT id FROM dias_bloqueio WHERE funcionario = '0' AND data = :data AND id_conta = :id_conta");
$query_bloq_geral->execute([':data' => $data, ':id_conta' => $id_conta]);
if ($query_bloq_geral->rowCount() > 0) {
    echo '<div class="alert alert-warning text-dark small p-1 text-center" role="alert">Não estaremos funcionando nesta Data!</div>';
    exit();
}

// Bloqueio do Funcionário
$query_bloq_func = $pdo->prepare("SELECT id FROM dias_bloqueio WHERE funcionario = :funcionario AND data = :data AND id_conta = :id_conta");
$query_bloq_func->execute([':funcionario' => $funcionario, ':data' => $data, ':id_conta' => $id_conta]);
if ($query_bloq_func->rowCount() > 0) {
    echo '<div class="alert alert-warning text-dark small p-1 text-center" role="alert">Este Profissional não atenderá nesta Data!</div>';
    exit();
}

// --- Verifica Dia de Trabalho e Horários do Funcionário ---
$diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado"];
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

$query_dia = $pdo->prepare("SELECT inicio, final, inicio_almoco, final_almoco FROM dias WHERE funcionario = :funcionario AND dia = :dia AND id_conta = :id_conta");
$query_dia->execute([':funcionario' => $funcionario, ':dia' => $dia_procurado, ':id_conta' => $id_conta]);
$res_dia = $query_dia->fetch(PDO::FETCH_ASSOC);

if (!$res_dia) {
    echo '<div class="alert alert-warning text-dark small p-1 text-center" role="alert">Este Profissional não trabalha neste Dia da Semana!</div>';
    exit();
}

$inicio = $res_dia['inicio'];
$final = $res_dia['final'];
$inicio_almoco = $res_dia['inicio_almoco'];
$final_almoco = $res_dia['final_almoco'];

// Busca o intervalo do funcionário
$query_func = $pdo->prepare("SELECT intervalo FROM usuarios WHERE id = :funcionario AND id_conta = :id_conta");
$query_func->execute([':funcionario' => $funcionario, ':id_conta' => $id_conta]);
$res_func = $query_func->fetch(PDO::FETCH_ASSOC);
$intervalo = $res_func ? $res_func['intervalo'] : 30; // Intervalo padrão de 30 min se não encontrar

if (empty($intervalo) || !is_numeric($intervalo) || $intervalo <= 0) {
    $intervalo = 30; // Define um padrão seguro
}

// --- Geração e Filtragem dos Horários ---
$horarios_disponiveis = [];
$hora_atual_check = date('H:i:s');
$data_e_hoje = ($data == date('Y-m-d'));

$hora_loop = $inicio;
while (strtotime($hora_loop) < strtotime($final)) { // Usa '<' para não incluir a hora final exata
    $hora_atual_loop = date('H:i:s', strtotime($hora_loop)); // Garante formato HH:MM:SS
    $hora_final_loop = date('H:i:s', strtotime("+$intervalo minutes", strtotime($hora_loop)));

    // 1. Pula horário de almoço
    $pula_almoco = false;
    if (!empty($inicio_almoco) && !empty($final_almoco)) {
        // Verifica se o início OU o fim do slot cai dentro do almoço
        if ((strtotime($hora_atual_loop) >= strtotime($inicio_almoco) && strtotime($hora_atual_loop) < strtotime($final_almoco)) ||
            (strtotime($hora_final_loop) > strtotime($inicio_almoco) && strtotime($hora_final_loop) <= strtotime($final_almoco)))
        {
            $pula_almoco = true;
            // Pula direto para o fim do almoço para otimizar
            $hora_loop = $final_almoco;
            continue; // Pula para a próxima iteração do while
        }
    }


    // 2. Verifica se o horário já passou (apenas para o dia de hoje)
    if ($data_e_hoje && strtotime($hora_atual_loop) < strtotime($hora_atual_check)) {
        // Incrementa e continua
        $hora_loop = $hora_final_loop;
        continue;
    }

    // 3. Verifica se já está agendado na tabela 'agendamentos'
    $query_agd = $pdo->prepare("SELECT id FROM agendamentos WHERE data = :data AND hora = :hora AND funcionario = :funcionario AND id_conta = :id_conta AND status <> 'Cancelado'"); // Adicionado status <> 'Cancelado'
    $query_agd->execute([':data' => $data, ':hora' => $hora_atual_loop, ':funcionario' => $funcionario, ':id_conta' => $id_conta]);

    // 4. Verifica se está bloqueado na tabela 'horarios_agd'
    $query_h_agd = $pdo->prepare("SELECT id FROM horarios_agd WHERE data = :data AND funcionario = :funcionario AND horario = :horario AND id_conta = :id_conta");
    $query_h_agd->execute([':data' => $data, ':funcionario' => $funcionario, ':horario' => $hora_atual_loop, ':id_conta' => $id_conta]);

    // Se não estiver agendado E não estiver bloqueado, adiciona à lista
    if ($query_agd->rowCount() == 0 && $query_h_agd->rowCount() == 0) {
        $horarios_disponiveis[] = $hora_atual_loop;
    }

    // Incrementa para o próximo horário
    $hora_loop = $hora_final_loop;
} // Fim do while

// --- Separação por Período ---
$horarios_manha = [];
$horarios_tarde = [];
$horarios_noite = [];

foreach ($horarios_disponiveis as $hora) {
    $hora_int = (int) date('H', strtotime($hora));
    if ($hora_int < 12) {
        $horarios_manha[] = $hora;
    } elseif ($hora_int < 18) {
        $horarios_tarde[] = $hora;
    } else {
        $horarios_noite[] = $hora;
    }
}

// --- Geração do HTML Final ---
$html_output = '<div class="row">'; // Abre a row principal

// Função auxiliar para gerar o HTML de cada horário (com classe hora-item e data-hora)
function gerarHtmlHorarioPeriodo($hora, $hora_rec) {
    $horaF = date("H:i", strtotime($hora));
    $id_unico_hora = "radio_hora_" . str_replace(':', '', $horaF);
    // Marca como checado se for a hora pré-selecionada
    $checado = (!empty($hora_rec) && $hora_rec == $horaF) ? 'checked' : '';
    // Adiciona classe 'hora-selecionada' se estiver checado
    $classe_selecionada = $checado ? 'hora-selecionada' : '';

    // O texto do label permanece, mas o clique JS vai funcionar no container
    $texto_hora = ''; // Você pode adicionar classes aqui se necessário

    $html = '<div class="col-3 hora-item ' . $classe_selecionada . '" data-hora="' . htmlspecialchars($horaF) . '">'; // Adiciona classes e data-hora
    $html .= '  <div class="form-check form-switch">';
    $html .= '      <input class="form-check-input" type="radio" role="switch" id="' . $id_unico_hora . '" name="hora" value="' . htmlspecialchars($horaF) . '" style="width:17px; height: 17px; cursor:pointer;" required ' . $checado . '>';
    $html .= '      <label class="form-check-label ' . $texto_hora . '" for="' . $id_unico_hora . '" style="cursor:pointer;">' . $horaF . '</label>';
    $html .= '  </div>';
    $html .= '</div>';
    return $html;
}

$encontrou_horario = false; // Flag para verificar se algum horário foi gerado

// Seção Manhã
if (!empty($horarios_manha)) {
    $encontrou_horario = true;
    $html_output .= '<div class="col-12 periodo-horario">';
    $html_output .= '<h6>Manhã</h6>';
    $html_output .= '<div class="horarios-lista row">'; // Adiciona classe row aqui para col-3 funcionar
    foreach ($horarios_manha as $hora) {
        $html_output .= gerarHtmlHorarioPeriodo($hora, $hora_rec);
    }
    $html_output .= '</div></div>'; // Fecha horarios-lista e periodo-horario
}

// Seção Tarde
if (!empty($horarios_tarde)) {
    $encontrou_horario = true;
    $html_output .= '<div class="col-12 periodo-horario">';
    $html_output .= '<h6>Tarde</h6>';
    $html_output .= '<div class="horarios-lista row">';
    foreach ($horarios_tarde as $hora) {
        $html_output .= gerarHtmlHorarioPeriodo($hora, $hora_rec);
    }
    $html_output .= '</div></div>';
}

// Seção Noite
if (!empty($horarios_noite)) {
    $encontrou_horario = true;
    $html_output .= '<div class="col-12 periodo-horario">';
    $html_output .= '<h6>Noite</h6>';
    $html_output .= '<div class="horarios-lista row">';
    foreach ($horarios_noite as $hora) {
        $html_output .= gerarHtmlHorarioPeriodo($hora, $hora_rec);
    }
    $html_output .= '</div></div>';
}

$html_output .= '</div>'; // Fecha a row principal

// Verifica se algum horário foi realmente gerado
if (!$encontrou_horario) {
    // Se nenhum horário foi adicionado ao $html_output, mostra a mensagem/Swal
    echo <<<HTML
    <script>
    Swal.fire({
        title: 'Horários Indisponíveis',
        text: 'Não temos mais horários disponíveis com este funcionário para essa data! Deseja se registar no encaixe? Avisaremos por WhatsApp se houver alguma desistência.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não',
        reverseButtons: true,
        allowOutsideClick: false,
    }).then((result) => {
        if (result.isConfirmed) {
            // Código para mostrar o seu modal #modalEncaixe
             $('#modalEncaixe').modal('show');
             // Pré-preenche os dados se possível
              $('#nomeEncaixe').val('{$nome}');
              $('#whatsappEncaixe').val('{$whatsapp}');
              $('#dataEncaixe').val('{$data}');
              $('#profissionalEncaixe').val('{$funcionario}');
        }
    });
    // Limpa a div de horários para não mostrar nada além do Swal
    // document.getElementById('listar-horarios').innerHTML = ''; // Descomente se o Swal não limpar
    </script>
    <div class="alert alert-warning text-dark small p-1 text-center" role="alert">Nenhum horário disponível!</div>

HTML;    
    ?>
    <div class="modal fade" id="modalEncaixe" tabindex="-1" role="dialog" aria-labelledby="modalEncaixeLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h4 class="modal-title">Registrar Encaixe<span id="titulo_servico"></span>  </h4>
				<a id="btn-fechar-servico" type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true">&times;</span>
				</a>
			</div>
            <div class="modal-body">
                <form id="formEncaixe">
                    <div class="form-group">
                        <label for="nomeEncaixe" style="color: black;">Nome</label>
                        <input type="text" class="form-control" id="nomeEncaixe" name="nomeEncaixe" value="<?php echo $nome?>" placeholder="Nome" required>
                    </div>
                    <div class="form-group">
                        <label for="whatsappEncaixe"  style="color: black;">WhatsApp</label>
                        <input type="tel" class="form-control" id="whatsappEncaixe" name="whatsappEncaixe" value="<?php echo $whatsapp?>" required placeholder="(xx) xxxxx-xxxx"> </div>
                    <input type="hidden" name="dataEncaixe" id="dataEncaixe" value="<?php echo isset($_POST['data']) ? htmlspecialchars($_POST['data']) : ''; ?>">
                    <input type="hidden" name="profissionalEncaixe" id="profissionalEncaixe" value="<?php echo isset($_POST['funcionario']) ? htmlspecialchars($_POST['funcionario']) : ''; ?>">
                     <input type="hidden" name="id_conta" value="<?php echo $id_conta; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</a>
                <a type="button" class="btn btn-primary" onclick="cadastrarEncaixe()">Registrar</a>
            </div>
        </div>
    </div>
</div>
    <script>
        function cadastrarEncaixe() {
            // Validação básica dos campos (pode ser aprimorada)
            var nome = $("#nomeEncaixe").val();
            var whatsapp = $("#whatsappEncaixe").val();

            if (!nome || !whatsapp) {
                Swal.fire('Erro', 'Preencha todos os campos!', 'error');
                return;
            }
            //Verificação se é um numero de WhatsApp Válido (Melhorado)
            var whatsappRegex = /^\(\d{2}\)\s?\d{4,5}-\d{4}$/;  //Formato (xx) xxxx-xxxx ou (xx) xxxxx-xxxx
            if (!whatsappRegex.test(whatsapp)) {
                Swal.fire('Erro', 'Número de WhatsApp inválido!', 'error');
                return;
            }

            // Coleta os dados do formulário e inputs hidden
            var data = $("#dataEncaixe").val();
            var profissional = $("#profissionalEncaixe").val();
            var id_conta = $("input[name='id_conta']").val();
            

            $.ajax({
                url: 'ajax/arquivo_cadastro_encaixe.php', // MUDE PARA O CAMINHO CORRETO!
                method: 'POST',
                data: {
                    nome: nome,
                    whatsapp: whatsapp,
                    data: data,
                    profissional: profissional,
                    id_conta: id_conta
                },
                dataType: 'json', // Espera uma resposta JSON
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Sucesso!',
                            text: 'Registro de encaixe realizado com sucesso! Entraremos em contato caso haja disponibilidade.',
                            icon: 'success',
                            allowOutsideClick: false,
                        }).then(() => {
                        $('#modalEncaixe').modal('hide'); // Fecha o modal após o sucesso
                        // Limpar Campos
                        $("#nomeEncaixe").val('');
                        $("#whatsappEncaixe").val('');
                        });

                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao cadastrar encaixe.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Log do erro completo no console
                    Swal.fire('Erro', 'Erro ao cadastrar encaixe. Tente novamente mais tarde.', 'error');
                }
            });
        }

//Adiciona uma máscara para o campo WhatsApp
$(document).ready(function(){
    $('#whatsappEncaixe').mask('(00) 00000-0000');
});
    
    </script>
    <?php


} else {
    // Envia o HTML gerado com os períodos
    echo $html_output;
}

?>