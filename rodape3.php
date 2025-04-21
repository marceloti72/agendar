  <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- Popper.js e Bootstrap JS (5.3 bundle inclui Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<!-- jQuery Mask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

<!-- Slick JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" integrity="sha512-XtmMtDEcNz2j7ekrtHvOVR4iwwaD6o/FUJe6+Zq+HgcCsk3kj4uSQQR8weQ2QVj1o0Pk6PwYLohm206ZzNfubg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Canvas Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<!-- Máscaras JS -->
<script type="text/javascript" src="<?php echo $url?>sistema/painel/js/mascaras.js"></script>

<!-- Custom JS -->
<script src="<?php echo $url?>js/custom.js"></script>





</body>

</html>




<script type="text/javascript">

$(document).ready(function(){
  $('#telefone_rodape').mask('(00) 00000-0000');
  $('#telefone_compra').mask('(00) 00000-0000');
  $('#inputTelefoneBusca').mask('(00) 00000-0000');
  $('#ass_telefone').mask('(00) 00000-0000');
  $('#ass_cpf').mask('000.000.000-00');

            


        
 });

  
  
 $("#form_cadastro").submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'ajax/cadastrar.php',
        type: 'POST',
        data: formData,
        success: function (mensagem) {
            $('#mensagem-rodape').text('');
            $('#mensagem-rodape').removeClass();
            if (mensagem.trim() == "Salvo com Sucesso") {
                $('#mensagem-rodape').text(mensagem);
                Swal.fire({
                    title: "Cadastro Efetuado! 😃",
                    text: "Você estará sempre atualizado com nossos serviços, produtos e promoções.",
                    icon: "success",
                    didOpen: () => {
                        // Dispara o confete
                        confetti({
                          particleCount: 150,
                          spread: 90,
                          origin: { y: 0.5 },
                          colors: ['#ff0000', '#00ff00', '#0000ff'], // Cores personalizadas (vermelho, verde, azul)
                          angle: 90,                          // Direção do lançamento
                          decay: 0.9,                         // Velocidade de desaceleração
                          startVelocity: 45                   // Velocidade inicial
                        });
                        // Ajusta o z-index do canvas do confete
                        setTimeout(() => {
                            const confettiCanvas = document.querySelector('canvas');
                            if (confettiCanvas) {
                                confettiCanvas.style.zIndex = '9999'; // Valor alto para ficar acima do Swal
                            }
                        }, 0);
                    }
                }).then((result) => {
                    // if (result.isConfirmed) {
                    //     window.location = "index.php";
                    // }
                });
            } else {
                $('#mensagem-rodape').text(mensagem);
            }
        },
        cache: false,
        contentType: false,
        processData: false,
    });
});



$(document).ready(function() {
    // Listener para os botões "Assinar" dentro do modal
    $('#modalAssinaturas2').on('click', '.btn-assinar', function() {
        const planoSelecionado = $(this).data('plano'); // Pega o valor de data-plano (bronze, prata, etc.)

        console.log("Plano selecionado:", planoSelecionado);
        // Adicione aqui a sua lógica para lidar com a seleção do plano:
        // - Redirecionar para uma página de pagamento/checkout com o plano selecionado
        //   Ex: window.location.href = '/checkout.php?plano=' + planoSelecionado;
        // - Fazer uma chamada AJAX para iniciar o processo de assinatura
        // - Etc.

        // Exemplo de feedback
        alert("Você selecionou o Plano " + planoSelecionado.charAt(0).toUpperCase() + planoSelecionado.slice(1) + "!");

        // Fecha o modal após a ação (opcional)
        $('#modalAssinaturas2').modal('hide');
    });
});





document.addEventListener('DOMContentLoaded', function () {

// Instâncias dos Modais Bootstrap
const modalPedirTelefoneElement = document.getElementById('modalPedirTelefone');
const modalDetalhesElement = document.getElementById('modalAssinaturaDetalhes');

if (!modalPedirTelefoneElement || !modalDetalhesElement) {
    console.error("Um ou ambos os modais não foram encontrados.");
    return;
}

const modalPedirTelefoneInstance = new bootstrap.Modal(modalPedirTelefoneElement);
const modalDetalhesInstance = new bootstrap.Modal(modalDetalhesElement);

// Elementos do Modal de Detalhes para atualização
const loadingIndicator = document.getElementById('modalAssinaturaLoading');
const errorContainer = document.getElementById('modalAssinaturaErro');
const contentContainer = document.getElementById('modalAssinaturaConteudo');
const clienteNomeSpan = document.getElementById('modalAssinaturaClienteNome');
const planoNomeSpan = document.getElementById('modalAssinaturaPlanoNome');
const proximoVencSpan = document.getElementById('modalAssinaturaProximoVenc');
const servicosTableBody = document.getElementById('modalAssinaturaServicosBody');
//const btnTrocarPlano = document.getElementById('btnModalTrocarPlano');

// Elementos do Modal de Pedir Telefone
const inputTelefoneBusca = document.getElementById('inputTelefoneBusca');
const senha = document.getElementById('senha2');
const btnBuscarPorTelefone = document.getElementById('btnBuscarPorTelefone');

// Função para formatar data (DD/MM/YYYY)
function formatarData(dataISO) {
    if (!dataISO || !dataISO.includes('-')) return 'N/D';
    const partes = dataISO.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

// Função para limpar/formatar telefone (APENAS DÍGITOS - ajuste conforme BD)
function limparTelefone(telefone) {
    return telefone.replace(/\D/g, ''); // Remove tudo que não for dígito
}

// Adiciona listener para o botão que INICIA o processo
// (ex: um botão na barra de navegação ou menu)
// Assumindo que este botão tem o id="btnIniciarBuscaAssinatura"
const btnIniciarBusca = document.getElementById('btnIniciarBuscaAssinatura'); // ** Crie este botão na sua página **
if (btnIniciarBusca) {
    btnIniciarBusca.addEventListener('click', function() {
        inputTelefoneBusca.value = ''; // Limpa campo
        inputTelefoneBusca.classList.remove('is-invalid'); // Remove validação
        modalPedirTelefoneInstance.show(); // Abre o modal para pedir telefone
    });
} else {
    console.warn("Botão #btnIniciarBuscaAssinatura não encontrado para iniciar o fluxo.");
}


// Adiciona listener para o botão DENTRO do modal de pedir telefone
if (btnBuscarPorTelefone) {
    btnBuscarPorTelefone.addEventListener('click', function() {
        const telefoneInput = inputTelefoneBusca.value;
        const senha2 = senha.value;
        const telefoneLimpo = limparTelefone(telefoneInput);

        if (telefoneLimpo.length < 10) { // Validação mínima (DDD + 8 ou 9 dígitos)
            inputTelefoneBusca.classList.add('is-invalid');
            return;
        }
        inputTelefoneBusca.classList.remove('is-invalid');

        // Fecha modal de pedir telefone
        modalPedirTelefoneInstance.hide();

        // Prepara e abre o modal de detalhes (mostrando loading)
        loadingIndicator.style.display = 'block';
        errorContainer.style.display = 'none';
        contentContainer.style.display = 'none';
        servicosTableBody.innerHTML = '';
        clienteNomeSpan.textContent = 'Buscando...'; // Feedback inicial
        planoNomeSpan.textContent = '';
        proximoVencSpan.textContent = '';
        //btnTrocarPlano.setAttribute('data-cliente-id', ''); // Limpa ID antigo

        modalDetalhesInstance.show();

        // --- Chamada AJAX para buscar os dados USANDO TELEFONE ---
        // Substitua 'caminho/para/buscar_detalhes_assinatura.php' pelo caminho real
        // Enviando telefone como parâmetro GET
        fetch(`buscar_detalhes_assinatura.php?telefone=${encodeURIComponent(telefoneInput)}&s=${encodeURIComponent(senha2)}`)
            .then(response => {
                if (!response.ok) {
                    // Tenta ler a resposta de erro mesmo se não for 2xx
                    return response.json().then(errData => {
                        throw { status: response.status, data: errData };
                    }).catch(() => {
                         // Se não conseguir ler JSON do erro, lança erro genérico
                         throw { status: response.status, data: { message: `Erro HTTP ${response.status}` } };
                    });
                }
                return response.json();
            })
            .then(data => {
                loadingIndicator.style.display = 'none';

                if (data.success) {
                    contentContainer.style.display = 'block';

                    // Preenche informações gerais
                    clienteNomeSpan.textContent = data.cliente_nome || 'Não informado';
                    planoNomeSpan.textContent = data.plano_nome || 'Nenhum plano ativo';
                    proximoVencSpan.textContent = formatarData(data.proximo_vencimento) || 'Não definido';
                    //btnTrocarPlano.setAttribute('data-cliente-id', data.cliente_id || ''); // Guarda o ID encontrado

                    // Preenche a tabela de serviços
                    if (data.servicos && data.servicos.length > 0) {
                        data.servicos.forEach(servico => {
                            const limiteTexto = (servico.limite_ciclo === 0) ? 'Ilimitado' : servico.limite_ciclo;
                            const usoAtual = servico.uso_atual || 0;

                            const row = `
                                <tr>
                                    <td>${servico.nome || 'Serviço Desconhecido'}</td>
                                    <td class="text-center">${usoAtual}</td>
                                    <td class="text-center">${limiteTexto}</td>
                                </tr>
                            `;
                            servicosTableBody.innerHTML += row;
                        });
                    } else if (data.plano_nome !== 'Nenhum') { // Só mostra se tem plano mas sem serviços listados ou sem ciclo
                         servicosTableBody.innerHTML = '<tr><td colspan="3" class="text-center">Não foi possível carregar o uso dos serviços (verifique o ciclo de pagamento).</td></tr>';
                    } else {
                         servicosTableBody.innerHTML = '<tr><td colspan="3" class="text-center">Nenhum serviço associado.</td></tr>';
                    }

                } else {
                    // Exibe mensagem de erro retornada pelo PHP
                    errorContainer.textContent = data.message || 'Erro ao buscar dados.';
                    errorContainer.style.display = 'block';
                    contentContainer.style.display = 'none'; // Garante que conteúdo antigo não apareça
                }
            })
            .catch(error => {
                console.error('Erro ao buscar detalhes da assinatura:', error);
                loadingIndicator.style.display = 'none';
                 // Tenta exibir a mensagem de erro vinda do servidor, se disponível
                const serverMessage = error.data?.message || 'Não foi possível conectar ao servidor.';
                errorContainer.textContent = serverMessage;
                errorContainer.style.display = 'block';
                contentContainer.style.display = 'none';
            });
    });
}


// Adiciona listener para o botão "Trocar Plano" (dentro do modal de detalhes)
// if (btnTrocarPlano) {
//     btnTrocarPlano.addEventListener('click', function() {
//         const clienteId = this.getAttribute('data-cliente-id');
//         if (!clienteId) {
//              alert("Não foi possível identificar o cliente para trocar o plano.");
//              return;
//         }
//         alert(`Funcionalidade "Trocar Plano" para cliente ID: ${clienteId} ainda não implementada.`);
        // Lógica para trocar plano...
        // modalDetalhesInstance.hide(); // Fecha modal atual
        // ... (abre outro modal ou redireciona)
//     });
// }

 // Opcional: Adicionar máscara ao campo de telefone usando uma biblioteca como Inputmask.js
 // Exemplo (precisa incluir a biblioteca Inputmask antes):
 /*
 const telInput = document.getElementById('inputTelefoneBusca');
 if (telInput && typeof Inputmask !== 'undefined') {
     Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(telInput);
 }
 */

});


</script>