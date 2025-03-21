<?php
@session_start();
$id_conta = $_SESSION['id_conta'];
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'configuracoes';
$data_atual = date('Y-m-d');
?>
<style>
	.tooltip-inner {
		background-color: #48D1CC;
		/* Amarelo */
		color: #000;
		/* Cor do texto */
	}
</style>
<?php

//verificar se ele tem a permissão de estar nessa página
if (@$configuracoes == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}

?>

<div class="row">
	<form method="post" id="form-config">
		<div class="modal-body">

			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="exampleInputEmail1">Nome Comercial</label>
						<input type="text" class="form-control" id="nome_sistema" name="nome_sistema" placeholder="Nome da Barbearia" value="<?php echo $nome_sistema ?>" required>
					</div>
				</div>
				<div class="col-md-4">

					<div class="form-group">
						<label for="exampleInputEmail1">E-mail <i class="fa fa-envelope"></i></label>
						<input type="email" class="form-control" id="email_sistema" name="email_sistema" placeholder="Email" value="<?php echo $email_sistema ?>" required>
					</div>
				</div>

				<div class="col-md-4">

					<div class="form-group">
						<label for="exampleInputEmail1">Whatsapp <i style="color: green;" class="bi bi-whatsapp"></i></label>
						<input type="text" class="form-control" id="whatsapp_sistema" name="whatsapp_sistema" placeholder="Whatsapp" value="<?php echo $whatsapp_sistema ?>" required>
					</div>
				</div>
			</div>


			<div class="row">

				<div class="col-md-2">

					<div class="form-group">
						<label for="exampleInputEmail1">Tel Fixo</label>
						<input type="text" class="form-control" id="telefone_fixo_sistema" name="telefone_fixo_sistema" placeholder="Fixo" value="<?php echo $telefone_fixo_sistema ?>" required>
					</div>
				</div>
				<div class="col-md-7">

					<div class="form-group">
						<label for="exampleInputEmail1">Endereço</label>
						<input type="text" class="form-control" id="endereco_sistema" name="endereco_sistema" placeholder="Rua X Numero X Bairro Cidade" value="<?php echo $endereco_sistema ?>">
					</div>
				</div>

				<div class="col-md-3">
					<div class="form-group">
						<label for="exampleInputEmail1">CNPJ</label>
						<input type="text" class="form-control" id="cnpj_sistema" name="cnpj_sistema" value="<?php echo $cnpj_sistema ?>">
					</div>
				</div>
			</div>


			<div class="row">

				<div class="col-md-4">
					<div class="form-group">
						<label for="exampleInputEmail1">Instagram</label>
						<input type="text" class="form-control" id="instagram_sistema" name="instagram_sistema" placeholder="Link do Perfil no Instagram" value="<?php echo $instagram_sistema ?>">
					</div>
				</div>

				<div class="col-md-8">
					<div class="form-group">
						<label for="exampleInputEmail1">Texto Cartão Fidelidade</label>
						<input maxlength="255" type="text" class="form-control" id="texto_fidelidade" name="texto_fidelidade" placeholder="Parabéns, você completou seus cartões, você ganhou ..." value="<?php echo @$texto_fidelidade ?>">
					</div>
				</div>


			</div>

			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Cartões Fidelidade <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Caso queria trabalhar com cartão de fidelidade, basta informar a quantidade de serviços para o brinde ao cliente, caso contrário deixe em branco." style="color: blue;"></i></label>
						<input type="number" class="form-control" id="quantidade_cartoes" name="quantidade_cartoes" placeholder="Quantidade Cartões Troca" value="<?php echo $quantidade_cartoes ?>">
					</div>
				</div>


				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Mens.Confirmação <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Marque SIM se quiser que o cliente receba um WahtsApp pedindo a confirmação do serviço. Defina os minutos no próximo campo." style="color: blue;"></i></label>
						<select class="form-control" name="msg_agendamento" id="msg_agendamento">
							<option value="Sim" <?php if ($msg_agendamento == 'Sim') { ?> selected <?php } ?>>Sim</option>
							<option value="Não" <?php if ($msg_agendamento == 'Não') { ?> selected <?php } ?>>Não</option>

						</select>
					</div>
				</div>

				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Min.Confirmação <i class="fa fa-info-circle" style="color: blue;" data-toggle="tooltip" data-placement="top" title="Informe os minutos de antecedência que o clientes receberá a mensagem pedindo a confirmação." style="color: blue;"></i></label>
						<input type="number" class="form-control" id="minutos_aviso" name="minutos_aviso" placeholder="Alerta Agendamento" value="<?php echo @$minutos_aviso ?>">
					</div>
				</div>


				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Alertas WhatsApp <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Notificações de WhatsApp, selecione SIM para o cliente receber diversos alertas como: Agendamentos, Confirmações, Cancelamentos, Campanhas de marketing, Mensagem de retorno, Mensagem de aniversário e outos.
						Lembre-se de fazer a leitura do QRcode no menu lateral em 'WhatsApp'." style="color: blue;"></i></label>
						<select class="form-control" name="api" id="api">
							<option value="Sim" <?php if ($api == 'Sim') { ?> selected <?php } ?>>Sim</option>
							<option value="Não" <?php if ($api == 'Não') { ?> selected <?php } ?>>Não</option>
						</select>
					</div>
				</div>

				<div class="col-md-3">
					<div class="form-group">
						<label for="exampleInputEmail1">Manter Agendamento Dias <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Informe aqui até quantos dias deseja manter os agendamentos concluidos no sistema." style="color: blue;"></i></label>
						<input type="number" class="form-control" id="agendamento_dias" name="agendamento_dias" value="<?php echo $agendamento_dias ?>" placeholder="Manter no Banco de Dados">
					</div>
				</div>

			</div>


			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Taxa de Cartões <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Aqui vc informa quem vai pagar as taxas dos cartões de Debito e Crétido. Lembrando que vc informa os valores das taxas no menu lateral em 'Cadastros -> Formas de Pagamentos'." style="color: blue;"></i></label>
						<select class="form-control" name="taxa_sistema" id="taxa_sistema">
							<option value="Cliente" <?php if (@$taxa_sistema == 'Cliente') { ?> selected <?php } ?>>Cliente Paga</option>
							<option value="Empresa" <?php if (@$taxa_sistema == 'Empresa') { ?> selected <?php } ?>>Estabelecimento Paga</option>

						</select>
					</div>
				</div>


				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Tipo Comissão</label>
						<select class="form-control" name="tipo_comissao" id="tipo_comissao">
							<option value="Porcentagem" <?php if ($tipo_comissao == 'Porcentagem') { ?> selected <?php } ?>>Porcentagem</option>
							<option value="R$" <?php if ($tipo_comissao == 'R$') { ?> selected <?php } ?>>R$ Reais</option>
						</select>
					</div>
				</div>



				<div class="col-md-3">
					<div class="form-group">
						<label for="exampleInputEmail1">Lançamento Comissão </label>
						<select class="form-control" name="lanc_comissao" id="lanc_comissao">
							<option value="Sempre" <?php if ($lanc_comissao == 'Sempre') { ?> selected <?php } ?>>Serviço Pendente e Pago</option>
							<option value="Pago" <?php if ($lanc_comissao == 'Pago') { ?> selected <?php } ?>>Serviço Pago</option>
						</select>
					</div>
				</div>



				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">% Agendamento <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Informe aqui a porcentagem que o cliente pagará ao agendar serviços pelo site. Funciona como um sinal para efetuar o agendamento." style="color: blue;"></i></label>
						<input type="number" class="form-control" id="porc_servico" name="porc_servico" placeholder="% pagar Agendamento" value="<?php echo $porc_servico ?>">
					</div>
				</div>

				<div class="col-md-2">
					<div class="form-group">
						<label for="exampleInputEmail1">Habilitar Encaixe <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Se habilitado, quando os horários de um profissional estiverem todos preenchidos, os clientes poderão se cadastrar no ENCAIXE, vagando um horário os clientes serão alertados por WhatsApp do horário disponível e poderão clicar no link de agendamento para concluir." style="color: blue;"></i></label>
						<select class="form-control" name="encaixe" id="encaixe">
							<option value="Sim" <?php if ($encaixe == 'Sim') { ?> selected <?php } ?>>Sim</option>
							<option value="Não" <?php if ($encaixe == 'Não') { ?> selected <?php } ?>>Não</option>
						</select>
					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-md-2">
					<div class="form-group">
						<label>
							Api Mercado Pago <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Já incluso no sistema! Basta abrir uma conta no MERCADO PAGO e informar nos campos seguintes o TOKEN e o PUBLIC KEY. Essas informações vc consegue em www.mercadopago.com.br/developers, após abrir a conta. Com ele vc terá diversas formas de pagamentos e com baixas automáticas! 👍" style="color: blue;"></i></label>
						<select class='form-control' name="api_mp" id="api_mp" style="width:100%" onchange="javascript:apimp(this);">
							<option>Selecione</option>
							<option value="Sim" <?php if (@$pgto_api == 'Sim') { ?> selected <?php } ?>>Sim
							</option>
							<option value="Não" <?php if (@$$pgto_api == 'Não') { ?> selected <?php } ?>>Não
							</option>
						</select>
					</div>
				</div>



				<div class="col-md-4">
					<div class="form-group">
						<label>Token</label>
						<input type="text" class="form-control" id="token_mp" name="token_mp"
							placeholder="Token do Mercado Pago" value="<?php echo $token_mp ?>">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>Public Key</label>
						<input type="text" class="form-control" id="key_mp" name="key_mp"
							placeholder="Public Key do Mercado Pago" value="<?php echo $key_mp ?>">
					</div>
				</div>
			</div>
		</div>

		<br>
		<small>
			<div id="mensagem-config" align="center"></div>
		</small>
</div>

<div class="modal-footer">
	<button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
</div>
</form>
</div>

<script>
	$(function() {
		$('[data-toggle="tooltip"]').tooltip()
	})
</script>