// app.js
let deferredPrompt;
const installButton = document.getElementById('installButton');

// Não esconda o botão aqui por padrão. Deixe o CSS ou HTML controlar a visibilidade inicial.
// installButton.style.display = 'none'; // REMOVIDO

// Captura o evento beforeinstallprompt QUANDO/SE ele for disparado
window.addEventListener('beforeinstallprompt', (e) => {
    // Previne o prompt automático MINIMALMENTE necessário (alguns navegadores podem mostrar mesmo assim)
    // Em alguns casos, pode ser necessário remover este preventDefault se o botão não funcionar
     e.preventDefault();
    // Armazena o evento para usar depois
    deferredPrompt = e;
    console.log('`beforeinstallprompt` disparado e evento armazenado.');
     // Opcional: Mudar o texto/estilo do botão para indicar que está pronto para instalar
     // installButton.textContent = 'Instalar Agora!';
});

// Listener de clique no botão (Lógica Modificada)
installButton.addEventListener('click', () => {
    // Verifica se o evento foi capturado E ainda não foi usado
    if (deferredPrompt) {
        console.log('Tentando mostrar o prompt de instalação...');
        // Mostra o prompt
        deferredPrompt.prompt();
        // Espera pela resposta do usuário
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('Usuário aceitou instalar o PWA');
                // Opcional: Mudar o botão para "Abrir App" ou desabilitar
                 // installButton.textContent = 'Instalado!';
                 // installButton.disabled = true;
            } else {
                console.log('Usuário recusou a instalação do PWA');
            }
            // Limpa o deferredPrompt para que não possa ser usado novamente
            // (O navegador pode disparar o evento novamente mais tarde se os critérios forem atendidos)
            deferredPrompt = null;
        });
    } else {
        // Se deferredPrompt for nulo, o evento não foi capturado recentemente
        console.log('`deferredPrompt` não está disponível.');

        // Verifica se o app está rodando em modo standalone (instalado)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true; // Adiciona verificação para Safari iOS

        if (isStandalone) {
            console.log('App já parece estar rodando em modo standalone.');
            // Informa que já está instalado e rodando
            alert('O aplicativo já está instalado e em execução!');
            // Opcional: Mudar texto/desabilitar botão
            // installButton.textContent = 'App Instalado';
            // installButton.disabled = true;
        } else {
            console.log('App não está em modo standalone. Verificação manual necessária.');
            // Informa que pode já estar instalado ou o navegador não ofereceu
            alert('O aplicativo pode já estar instalado ou o navegador não está oferecendo a instalação no momento. Verifique o menu do seu navegador (opção "Instalar aplicativo" ou "Adicionar à tela inicial") ou a sua lista de aplicativos.');
        }
    }
});

// Listener para quando o app é efetivamente instalado
window.addEventListener('appinstalled', () => {
    console.log('PWA foi instalado com sucesso!');
    // Limpa o prompt para garantir que não seja mostrado novamente na mesma sessão
    deferredPrompt = null;
    // Opcional: Mudar o texto/estilo do botão para refletir a instalação
     // installButton.textContent = 'Instalado com Sucesso!';
     // installButton.disabled = true;
     // installButton.style.cursor = 'default';
});

// Opcional: Adicionar um pequeno delay ou verificar no carregamento se já está standalone
// para potencialmente ajustar o botão inicial (ex: mostrar "Abrir" se já instalado)
window.addEventListener('load', () => {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone) {
         console.log('App carregado em modo standalone.');
         // Opcional: Ajustar botão no carregamento inicial
         // installButton.textContent = 'Abrir App'; // Ou algo similar
         // installButton.onclick = () => { /* Adicionar lógica para focar/abrir se possível */ alert('App já aberto!'); };
    }
});