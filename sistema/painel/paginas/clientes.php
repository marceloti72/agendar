<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");
$id_conta = $_SESSION['id_conta'];

$pag = 'clientes';

// Verificar se o usuário tem permissão de estar nessa página
if (@$_SESSION['nivel_usuario'] != 'administrador') {
    echo "<script>window.location='agenda.php'</script>";
}
?>
<style>
    @media (max-width: 768px) {
        .novo, .importar {
            display: flex;
            width: 100%;
            height: 30px;
            margin-bottom: 10px;
            font-size: 14px;
            align-items: center;
            justify-content: center;
        }
    }
    .importar {
        margin-left: 10px;
    }
</style>

<div class="">    
    <a class="btn btn-primary novo" onclick="inserir()" style='border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)'>
        <i class="fa fa-plus" aria-hidden="true"></i> <span>Novo Cliente</span>
    </a>
    <a class="btn btn-success importar" onclick="$('#modalImportar').modal('show')" style='border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)'>
        <i class="fa fa-file-excel-o" aria-hidden="true"></i> <span>Importar Clientes</span>
    </a>
</div>

<div class="bs-example widget-shadow" style="padding:15px" id="listar"></div>

<!-- Modal Inserir -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title"><span id="titulo_inserir"></span></h4>
                <button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_cli">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="Telefone">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Cpf</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Cartões</label>
                                <input type="number" class="form-control" id="cartao" name="cartao" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua X Número 1 Bairro xxx">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nascimento</label>
                                <input type="date" class="form-control" id="data_nasc" name="data_nasc">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" id="id">
                    <br>
                    <small>
                        <div id="mensagem" align="center"></div>
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importar -->
<div class="modal fade" id="modalImportar" tabindex="-1" role="dialog" aria-labelledby="modalImportarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title" id="modalImportarLabel">Importar Clientes</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_importar" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="arquivo_excel">Selecione o arquivo Excel</label>
                        <input type="file" class="form-control" id="arquivo_excel" name="arquivo_excel" accept=".xlsx,.xls" required>
                    </div>
                    <small>
                        <div id="mensagem_importar" align="center"></div>
                    </small>
                </div>
                <div class="modal-footer">
                    <a href="https://www.markai.skysee.com.br/download/modelo_clientes.xlsx" class="btn btn-info" download><i class="fa fa-download"></i> Arquivo Modelo</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
                <button id="btn-fechar-perfil" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" style="border-bottom: 1px solid #cac7c7;">
                    <div class="col-md-6">
                        <span><b>Telefone: </b></span>
                        <span id="telefone_dados"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Cartões: </b></span>
                        <span id="cartoes_dados"></span>
                    </div>
                </div>
                <div class="row" style="border-bottom: 1px solid #cac7c7;">
                    <div class="col-md-6">
                        <span><b>Cadastro: </b></span>
                        <span id="data_cad_dados"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Nascimento: </b></span>
                        <span id="data_nasc_dados"></span>
                    </div>
                </div>
                <div class="row" style="border-bottom: 1px solid #cac7c7;">
                    <div class="col-md-6">
                        <span><b>Data Retorno: </b></span>
                        <span id="retorno_dados"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Último Serviço: </b></span>
                        <span id="servico_dados"></span>
                    </div>
                </div>
                <div class="row" style="border-bottom: 1px solid #cac7c7;">
                    <div class="col-md-12">
                        <span><b>Endereço: </b></span>
                        <span id="endereco_dados"></span>
                    </div>
                </div>
                <br>
                <small>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Último Serviço</th>
                                <th class="esc">Data</th>
                                <th class="esc">Valor</th>
                                <th class="esc">OBS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <td><span id="servico_dados_tab"></span></td>
                            <td><span id="data_dados_tab"></span></td>
                            <td><span id="valor_dados_tab"></span></td>
                            <td><span id="obs_dados_tab"></span></td>
                        </tbody>
                    </table>
                </small>
                <hr>
                <div id="listar-debitos"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contrato -->
<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h4 class="modal-title" id="exampleModalLabel"><span id="titulo_contrato"></span></h4>
                <button id="btn-fechar-conta" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -25px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-contrato">
                <div class="modal-body">
                    <div>
                        <textarea name="contrato" id="contrato" class="textareag"></textarea>
                    </div>
                    <input type="hidden" name="id" id="id_contrato">
                    <small>
                        <div id="mensagem-contrato" align="center"></div>
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Serviços Cliente -->
<div class="modal fade" id="servicosClienteModal" tabindex="-1" aria-labelledby="servicosClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <?php
        $id = $_GET['id'];
        $query2 = $pdo->query("SELECT * FROM clientes where id = '$id'");
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        $total_reg2 = @count($res2);
        $nome_cliente = @$res2[0]['nome'];
        ?>
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #4682B4;">
                <h5 class="modal-title" id="servicosClienteModalLabel">Últimos Serviços Cliente: <?php echo $nome_cliente ?></h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="titulo_cab">Últimos Serviços Cliente: <?php echo $nome_cliente ?></div>
                <div class="data_img"><?php echo mb_strtoupper($data_hoje) ?></div>
                <img class="imagem" src="<?php echo $url ?>/sistema/img/logo_rel.jpg" alt="Logo">
                <div class="cabecalho"></div>
                <?php
                $total_entradas = 0;
                $query = $pdo->query("SELECT * FROM receber where tipo = 'Serviço' and pago = 'Sim' and pessoa = '$id' ORDER BY data_pgto desc");
                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                $total_reg = @count($res);
                if ($total_reg > 0) {
                ?>
                    <table class="table table-striped borda">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Data PGTO</th>
                                <th>Recebido Por</th>
                                <th>Forma PGTO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 0; $i < $total_reg; $i++) {
                                $id = $res[$i]['id'];
                                $descricao = $res[$i]['descricao'];
                                $valor = $res[$i]['valor'];
                                $data_pgto = $res[$i]['data_pgto'];
                                $usuario_baixa = $res[$i]['usuario_baixa'];
                                $pgto = $res[$i]['pgto'];
                                $comanda = $res[$i]['comanda'];
                                $valor2 = $res[$i]['valor2'];
                                if ($comanda > 0) {
                                    $valor = $valor2;
                                }
                                $total_entradas += $valor;
                                $valorF = number_format($valor, 2, ',', '.');
                                $data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));
                                $query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
                                $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
                                $nome_usuario_pgto = (@count($res2) > 0) ? $res2[0]['nome'] : 'Nenhum!';
                            ?>
                                <tr>
                                    <td><?php echo $descricao ?></td>
                                    <td>R$ <?php echo $valorF ?></td>
                                    <td><?php echo $data_pgtoF ?></td>
                                    <td><?php echo $nome_usuario_pgto ?></td>
                                    <td><?php echo $pgto ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="total-summary">
                        <span>Total de Recebimentos: <?php echo $total_reg ?></span><br>
                        <span class="text-success">Total R$: <?php echo number_format($total_entradas, 2, ',', '.') ?></span>
                    </div>
                <?php } else { ?>
                    <p>Não há registros para exibir!</p>
                <?php } ?>
                <div class="cabecalho"></div>
            </div>
            <div class="modal-footer">
                <span><?php echo $nome_sistema ?> Whatsapp: <?php echo $whatsapp_sistema ?></span>
            </div>
        </div>
    </div>
</div>

<?php
if (@$_GET["funcao"] != null && @$_GET["funcao"] == "ultserv") {
    echo "<script>$('#servicosClienteModal').modal('show');</script>";
}
?>

<script type="text/javascript">
    var pag = "<?= $pag ?>";
</script>
<script src="js/ajax.js"></script>
<script src="//js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<script type="text/javascript">
    bkLib.onDomLoaded(nicEditors.allTextAreas);
</script>

<script type="text/javascript">
    $(document).ready(function() {
        listarClientes();
    });

    function listarClientes(pagina) {
        $("#pagina").val(pagina);
        var busca = $("#buscar").val();
        $.ajax({
            url: 'paginas/' + pag + "/listar.php",
            method: 'POST',
            data: { busca, pagina },
            dataType: "html",
            success: function(result) {
                $("#listar").html(result);
            }
        });
    }

    function listarDebitos(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar-debitos.php",
            method: 'POST',
            data: { id },
            dataType: "html",
            success: function(result) {
                $("#listar-debitos").html(result);
            }
        });
    }

    $("#form_cli").submit(function() {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'paginas/' + pag + "/salvar.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem').text('');
                $('#mensagem').removeClass();
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#btn-fechar').click();
                    var pagina = $("#pagina").val();
                    listarClientes(pagina);
                } else {
                    $('#mensagem').addClass('text-danger');
                    $('#mensagem').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    $("#form_importar").submit(function() {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'importar_clientes.php',
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem_importar').text('');
                $('#mensagem_importar').removeClass();
                if (mensagem.trim() == "Dados importados com sucesso!") {
                    $('#modalImportar').modal('hide');
                    var pagina = $("#pagina").val();
                    listarClientes(pagina);
                } else {
                    $('#mensagem_importar').addClass('text-danger');
                    $('#mensagem_importar').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    function excluir(id) {
        $.ajax({
            url: 'paginas/' + pag + "/excluir.php",
            method: 'POST',
            data: { id },
            dataType: "text",
            success: function m (mensagem) {
                if (mensagem.trim() == "Excluído com Sucesso") {
                    var pagina = $("#pagina").val();
                    listarClientes(pagina);
                } else {
                    $('#mensagem-excluir').addClass('text-danger');
                    $('#mensagem-excluir').text(mensagem);
                }
            },
        });
    }

    function listarTextoContrato(id) {
        $.ajax({
            url: 'paginas/' + pag + "/texto-contrato.php",
            method: 'POST',
            data: { id },
            dataType: "html",
            success: function(result) {
                nicEditors.findEditor("contrato").setContent(result);
            }
        });
    }

    $("#form-contrato").submit(function() {
        var id_emp = $('#id_contrato').val();
        event.preventDefault();
        nicEditors.findEditor('contrato').saveContent();
        var formData = new FormData(this);
        $.ajax({
            url: 'paginas/' + pag + "/salvar-contrato.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem-contrato').text('');
                $('#mensagem-contrato').removeClass();
                if (mensagem.trim() == "Salvo com Sucesso") {
                    let a = document.createElement('a');
                    a.target = '_blank';
                    a.href = 'rel/contrato_servico_class.php?id=' + id_emp;
                    a.click();
                } else {
                    $('#mensagem-contrato').addClass('text-danger');
                    $('#mensagem-contrato').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    function baixar(id, cliente) {
        $.ajax({
            url: 'paginas/receber/baixar.php',
            method: 'POST',
            data: { id },
            dataType: "text",
            success: function(mensagem) {
                if (mensagem.trim() == "Baixado com Sucesso") {
                    listarDebitos(cliente);
                } else {
                    $('#mensagem-excluir-baixar').addClass('text-danger');
                    $('#mensagem-excluir-baixar').text(mensagem);
                }
            },
        });
    }
</script>