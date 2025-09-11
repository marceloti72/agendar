<?php
// keep_alive.php
@session_start();

// Apenas o fato de iniciar a sessão já a renova.
// Podemos retornar uma confirmação.
if (isset($_SESSION['id_conta'])) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'expired']);
}
?>