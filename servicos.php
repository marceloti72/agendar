<?php require_once("cabecalho2.php") ?>
<style type="text/css">
	.sub_page .hero_area {
  min-height: auto;
}
</style>

</div>
  <section class="product_section layout_padding">
    <div class="container-fluid">
      <div class="heading_container heading_center ">
        <h2 class="">
          Nossos Serviços
        </h2>
        <p class="col-lg-8 px-0">
          <?php 
          $query = $pdo->query("SELECT * FROM cat_servicos where id_conta = '$id_conta' ORDER BY id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){ 
for($i=0; $i < $total_reg; $i++){
  foreach ($res[$i] as $key => $value){}
  $id = $res[$i]['id'];
  $nome = $res[$i]['nome'];?>
          <button style="border-radius: 15px; background-color:rgb(141, 157, 248); color: white; padding: 5px;border: 0"><?php echo $nome;?></button><?php 

}

}

$query = $pdo->query("SELECT * FROM servicos where ativo = 'Sim' and id_conta = '$id_conta' ORDER BY id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){ 
?>
        </p>
      </div>
      <div class="row" style="background: #f0f0f2">

<?php 
for($i=0; $i < $total_reg; $i++){
  foreach ($res[$i] as $key => $value){}
 
   $id = $res[$i]['id'];
  $nome = $res[$i]['nome'];   
  $valor = $res[$i]['valor'];
  $foto = $res[$i]['foto'];
   $valorF = number_format($valor, 2, ',', '.');
   $nomeF = mb_strimwidth($nome, 0, 20, "...");

 ?>

        <div class="col-sm-6 col-md-3">
          <div class="box">
            <div class="img-box">
              <img src="sistema/painel/img/servicos/<?php echo $foto ?>" title="<?php echo $descricao ?>">
            </div>
            <div class="detail-box">
              <h5>
               <?php echo $nomeF ?>
              </h5>
              <h6 class="price">
                <span class="new_price">
                 R$ <?php echo $valorF ?>
                </span>
               
              </h6>
              <a href="agendamentos">
                  Agendar
                </a>
            </div>
          </div>
        </div>
      
   <?php } ?>    


      </div>

      <?php } ?>
      
    </div>
  </section>



  <!-- product section ends -->




 
   <?php require_once("rodape2.php") ?>