<?php require_once("cabecalho2.php") ?>
<style type="text/css">
	.sub_page .hero_area {
  min-height: auto;
}

/* --- Media Queries (Responsividade para Mobile) --- */
@media (max-width: 767px) { /* Aplica estes estilos em telas menores que 768px */

/* Reduz o tamanho da imagem dentro dos cards de serviço */
.product_section .col-4 .box .img-box img { /* Seletor mais específico */
    height: 100px; /* << REDUZA a altura da imagem (ajuste o valor) */
}

/* Reduz o padding interno da caixa de detalhes */
.product_section .col-4 .box .detail-box {
    padding: 0px; /* << REDUZA o padding (ajuste o valor) */
}

/* Reduz o tamanho da fonte do nome do serviço */
.product_section .col-4 .box .detail-box h5 {
    font-size: 0.7rem; /* << REDUZA o tamanho da fonte (ajuste o valor) */
    margin-bottom: 5px; /* Reduz espaço abaixo do nome */
    /* Opcional: Permite quebrar linha se o nome for muito longo */
    white-space: normal; /* Permite quebra de linha */
    overflow: visible;   /* Garante que não seja cortado */
    text-overflow: clip;   /* Remove '...' se quebrar linha */
    line-height: 1.2;    /* Ajusta altura da linha */
    
}

/* Reduz o tamanho da fonte do preço */
.product_section .col-4 .box .detail-box h6.price {
    font-size: 0.9rem; /* << REDUZA o tamanho da fonte (ajuste o valor) */
}
.product_section .col-4 .box .detail-box .new_price {
    font-size: 1em; /* Ajusta o tamanho do span do preço se necessário */
}


/* Reduz o tamanho e padding do botão */
.product_section .col-4 .box .detail-box a {
    font-size: 0.6em; /* << REDUZA o tamanho da fonte (ajuste o valor) */
    padding: 3px 10px; /* << REDUZA o padding (ajuste o valor) */
   
}

/* Opcional: Reduz o espaço entre as colunas (cards) */
.product_section .row > .col-4 {
    padding-left: 5px;
    padding-right: 5px;
}
.product_section .row {
    margin-left: -5px;
    margin-right: -5px;
}

}
/* --- Fim Media Queries --- */
</style>

</div>


  <?php 
$query = $pdo->query("SELECT * FROM produtos where estoque > 0 and valor_venda >  0 and id_conta = '$id_conta' ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){ 
   ?>

  <section class="product_section layout_padding">
    <div class="container-fluid">
      <div class="heading_container heading_center ">
        <h2 class="">
          Nossos Produtos
        </h2>
        <?php 
              if($pgto_api == 'Sim'){
              ?>
              <img src="images/mp2.png" alt="Banner Mercado Pago" class="img-fluid mb-4 produto-banner" style="max-width: 100px; height: auto;margin: 0;">
              <?php 
              }
              ?>
        <!-- <p class="col-lg-8 px-0">
          Confira alguns de nossos produtos, damos desconto caso compre em grande quantidade.
        </p> -->
      </div>
      <div class="row">
      <div class="row" style="background: #f0f0f2; padding-top: 20px; padding-bottom: 20px;">

<?php 
for($i=0; $i < $total_reg; $i++){
  foreach ($res[$i] as $key => $value){}
 
  $id = $res[$i]['id'];
  $nome = $res[$i]['nome'];   
  $valor = $res[$i]['valor_venda'];
  $foto = $res[$i]['foto'];
  $descricao = $res[$i]['descricao'];
   $valorF = number_format($valor, 2, ',', '.');
 $nomeF = mb_strimwidth($nome, 0, 23, "...");

 ?>

      <div class="col-4 col-md-2">
          <div class="box" style = 'border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4); margin-bottom: 20px'>
            <div class="img-box">
              <img src="sistema/painel/img/produtos/<?php echo $foto ?>" title="<?php echo $descricao ?>">
            </div>
            <div class="detail-box">
              <h5>
               <?php echo $nomeF ?>
              </h5>
              <h6 class="price">
                <span class="new_price">
                 R$ <?php echo $valorF ?>
                </span>
               
              </h6><?php 
              if($pgto_api != 'Sim'){?>
                      <a target="_blank" href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>&text=Ola, gostaria de saber mais informações sobre o produto <?php echo $nome ?>">
                        Comprar Agora
                      </a><?php 
                    }else{?>
                      <a href="pagamento2/<?php echo $id ?>/<?php echo $id_conta?>">
                        Comprar Agora
                      </a><?php 
                    }
                    ?>
            </div>
          </div>
        </div>
      
   <?php } ?>    


      </div>
      </div>
      
    </div>
  </section>

<?php } ?>

  <!-- product section ends -->




 
   <?php require_once("rodape2.php") ?>