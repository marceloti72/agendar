<!DOCTYPE html>
<html lang="pt-br">
<head>
	<title>Login MARKAI - Gestão de Serviços</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="login/images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/fonts/iconic/css/material-design-iconic-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="login/vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/select2/select2.min.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="login/vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/css/util.css">
	<link rel="stylesheet" type="text/css" href="login/css/main.css">
<!--===============================================================================================-->
</head>
<style>
	.container-login100 {
		background-color:rgb(163, 163, 163) ;
	}

	
	@media (max-width: 580px) {
    .panel-heading {
        margin-left: -20px !important;
    }
}
</style>
<body>
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">		
			    <div class="panel-heading" align="center" style="width: 100%; margin-top: -78px; margin-left: -57px; margin-bottom: 40px;">
			    	<img src="sistema/img/icon.png" width="400px">
			 	</div>		
				<form action="sistema/autenticar.php" method="post">
					
					<div class="wrap-input100 validate-input">
						<input class="input100" type="text" name="usuario">
						<span class="focus-input100" data-placeholder="Usuário"></span>
					</div>

					<div class="wrap-input100 validate-input" data-validate="Enter password">
						<span class="btn-show-pass">
						<i class="zmdi zmdi-eye"></i>
						</span>
						<input class="input100" type="password" name="senha">
						<span class="focus-input100" data-placeholder="Senha"></span>
					</div>

					<div class="container-login100-form-btn">
						<div class="wrap-login100-form-btn">
							<div class="login100-form-bgbtn"></div>
							<button class="login100-form-btn">
								Entrar
							</button>
						</div>
					</div>

					<div class="text-center p-t-20">
						<span class="txt1">
							Primeiro acesso?
						</span>

						<a class="txt2" href="#" data-toggle="modal" data-target="#modalRecuperar" style="color: blue;">
							Cadastrar Senha
						</a>
					</div>
					<div class="text-center p-t-20">
						<span class="txt1">
							Esqueceu a senha?
						</span>

						<a class="txt2" href="#" data-toggle="modal" data-target="#modalRecuperar"style="color: red;">
							Recuperar
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>


	<!-- Modal -->
<div class="modal fade" id="modalRecuperar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header text-white" style="background-color: #4682B4;">
				<h5 class="modal-title" id="staticBackdropLabel">Cadastrar nova Senha</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form method="POST" id="form">
				<div class="modal-body">
					<div class="form-group">
						<label >Seu Email</label>
						<input type="email" class="form-control" id="email" name="email" placeholder="Email">
					</div>

					<small>
						<div id="mensagem">

						</div>
					</small> 

				</div>
				<div class="modal-footer">
					<button id="btn-fechar" type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
					<button type="submit" class="btn btn-info">Recuperar</button>
				</div>
			</form>
		</div>
	</div>
</div>
	

	<div id="dropDownSelect1"></div>
	
<!--===============================================================================================-->
	<script src="./login/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="./login/vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
	<script src="./login/vendor/bootstrap/js/popper.js"></script>
	<script src="./login/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="./login/vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="./login/vendor/daterangepicker/moment.min.js"></script>
	<script src="./login/vendor/daterangepicker/daterangepicker.js"></script>
<!--===============================================================================================-->
	<script src="./login/vendor/countdowntime/countdowntime.js"></script>
<!--===============================================================================================-->
	<script src="./login/js/main.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script type="text/javascript">
	$("#form").submit(function () {
		
		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url:"recuperar.php",
			type: 'POST',
			data: formData,

			success: function (mensagem) {
				$('#mensagem').removeClass()
				if (mensagem.trim() == "Sua senha foi Enviada para seu Email!") {

                    Swal.fire({
                        title: "Enviado com sucesso!",
                        text: "Sua senha foi enviada para seu Email e WhatsApp! Verifique a caixa de spam.",
                        icon: "success"
                        }).then((result) => {
                        if(result.isConfirmed){
                           window.location = "index.php";        
                     }});
                    
                    $('#mensagem').addClass('text-success')
                } else {
                	$('#mensagem').addClass('text-danger')
                }
                $('#mensagem').text(mensagem)
            },

            cache: false,
            contentType: false,
            processData: false,
            xhr: function () {  // Custom XMLHttpRequest
            	var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) { // Avalia se tem suporte a propriedade upload
                	myXhr.upload.addEventListener('progress', function () {
                		/* faz alguma coisa durante o progresso do upload */
                	}, false);
                }
                return myXhr;
            }
        });
	});

    $("#close").click(function(){	  
    window.location = "index.php";
})
</script>

</body>
</html>