<?php
// Inicie a sessão para acessar as variáveis de sessão, se necessário
//@session_start();

// --- Obtenha o username ---
// Ajuste esta linha dependendo de onde vem o username
// Exemplo 1: Vindo de uma variável de sessão
//$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'usuario_padrao'; // Use um padrão se não logado

// Exemplo 2: Vindo de um parâmetro GET (menos comum para manifest)
$username = isset($_GET['u']) ? $_GET['u'] : 'usuario_padrao';

// --- Define o tipo de conteúdo como JSON ---
header('Content-Type: application/manifest+json'); // Ou application/json

// --- Estrutura do Manifesto como um array PHP ---
$manifestData = [
    "name" => "AGENDAR - Sistema de Gestão de Serviços",
    "short_name" => "Agendar",
    "description" => "Uma breve descrição do seu sistema.",
    // --- Monta a start_url dinamicamente ---
    "start_url" => "https://agendar.skysee.com.br/app/index.php?u=" . urlencode($username), // Caminho relativo é melhor
    // Ou use o caminho absoluto se precisar:
    // "start_url" => "https://agendar.skysee.com.br/index.php?u=" . urlencode($username),
    "display" => "standalone",
    "background_color" => "#FFFFFF",
    "theme_color" => "#4682B4",
    "orientation" => "portrait-primary",
    "icons" => [
        [
            "src" => "../images/icone_192.png", // Use caminhos relativos à raiz
            "sizes" => "192x192",
            "type" => "image/png",
            "purpose" => "any maskable"
        ],
        [
            "src" => "../images/icone_512.png",
            "sizes" => "512x512",
            "type" => "image/png",
            "purpose" => "any maskable"
        ]
        // Adicione mais ícones se necessário
    ]
];

// --- Codifica o array para JSON e envia a saída ---
echo json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>