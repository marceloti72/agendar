<?php 
@session_start();
$id_conta = $_SESSION['id_conta'];
include('../../conexao.php');

$id = $_GET['id'];

//ALIMENTAR OS DADOS NO RELATÓRIO
$html = file_get_contents($url."sistema/painel/rel/rel_servicos_clientes.php?id=$id");

// if($tipo_rel != 'PDF'){
// 	echo $html;
// 	exit();
// }
echo $html;
exit();
//CARREGAR DOMPDF
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

header("Content-Transfer-Encoding: binary");
header("Content-Type: image/png");

//INICIALIZAR A CLASSE DO DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$pdf = new DOMPDF($options);



//Definir o tamanho do papel e orientação da página
$pdf->set_paper('A4', 'portrait');

//CARREGAR O CONTEÚDO HTML
$pdf->load_html($html);

//RENDERIZAR O PDF
$pdf->render();

//NOMEAR O PDF GERADO
$pdf->stream(
'servicosClientes.pdf',
array("Attachment" => false)
);
?>