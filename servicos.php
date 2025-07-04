<?php require_once("cabecalho2.php") ?>
<style type="text/css">
  /* Seus estilos CSS anteriores */
  .sub_page .hero_area {
     min-height: auto;
   }
   /* Adicione outros estilos se necessário */
    .box {
        margin-bottom: 20px; /* Adiciona espaço inferior aos boxes */
         height: 100%; /* Garante que os boxes na mesma linha tenham altura consistente */
         display: flex; /* Usa flexbox para alinhar conteúdo */
         flex-direction: column; /* Empilha conteúdo verticalmente */
    }
     .detail-box {
        flex-grow: 1; /* Faz o detail-box ocupar o espaço restante */
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Espaça o conteúdo verticalmente */
    }
    .detail-box a {
        margin-top: auto; /* Empurra o botão para baixo */
    }
     /* Garante que a row tenha espaçamento */
     .product_section .row {
        margin-left: -15px;
        margin-right: -15px;
    }
    .product_section .row > [class*='col-'] {
        padding-left: 15px;
        padding-right: 15px;
    }

    /* Seus estilos anteriores aqui... */

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
        font-size: 0.9rem; /* << REDUZA o tamanho da fonte (ajuste o valor) */
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
        font-size: 0.8em; /* << REDUZA o tamanho da fonte (ajuste o valor) */
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

</div> <section class="product_section layout_padding">
    <div class="container-fluid">
        <div class="heading_container heading_center ">
            <h2 class="">
                Nossos Serviços
            </h2><?php 
            
                $query_serv = $pdo->query("SELECT * FROM servicos where ativo = 'Sim' and id_conta = '$id_conta' ORDER BY id asc");
                $res_serv = $query_serv->fetchAll(PDO::FETCH_ASSOC);
                $total_reg = count($res_serv);
                if($total_reg > 0){
                ?>
           
            <?php 
              if($pgto_api == 'Sim'){
              ?>
              <img src="images/mp2.png" alt="Banner Mercado Pago" class="img-fluid mb-4 produto-banner" style="max-width: 100px; height: auto;margin: 0;">
              <?php 
              }
              ?>
        </div>

        <div class="row" style="background: #f0f0f2; padding-bottom: 20px;">

            <?php
            foreach($res_serv as $item){ // Use foreach diretamente, mais limpo
                $id = $item['id'];
                $nome = $item['nome'];
                $valor = $item['valor'];
                $foto = $item['foto'];
                $valorF = number_format($valor, 2, ',', '.');
                $nomeF = mb_strimwidth($nome, 0, 20, "...");
                $descricao = isset($item['descricao']) ? $item['descricao'] : $nome; // Garante que $descricao exista

            ?>

            <div class="col-4 col-md-2" style="margin-bottom: 25px">
                <div class="box" style = 'border-radius: 10px;box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.4)'>
                    <!-- <div class="img-box">
                         <img src="sistema/painel/img/servicos/<?php echo $foto; ?>"
                              alt="<?php echo htmlspecialchars($nome); ?>"
                              onerror="this.onerror=null; this.src='sistema/painel/img/servicos/sem-foto.jpg';"
                              title="<?php echo htmlspecialchars($descricao); ?>">
                    </div> -->
                    <div class="detail-box" style="margin-bottom: 20px">
                        <h5>
                           <?php echo htmlspecialchars($nomeF); ?>
                        </h5>
                        <h6 class="price">
                            <span class="new_price">
                               R$ <?php echo $valorF; ?>
                            </span>
                        </h6>
                        <a href="agendamentos">
                            Agendar
                        </a>
                    </div>
                </div>
            </div>

           <?php } // Fim do foreach ?>

        </div> <?php } else { // Caso não haja serviços
             echo '<p class="text-center">Nenhum serviço encontrado.</p>';
        } ?>

    </div>
</section>

<?php require_once("rodape2.php") ?>