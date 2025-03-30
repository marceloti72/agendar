// app.js
let deferredPrompt;
alert('kjklj')

const installButton = document.getElementById('installButton');

// Esconde o botão por padrão até que o evento beforeinstallprompt seja disparado
installButton.style.display = 'none';

// Captura o evento beforeinstallprompt
window.addEventListener('beforeinstallprompt', (e) => {
    // Previne que o navegador mostre o prompt automaticamente
    e.preventDefault();
    // Armazena o evento para usar depois
    deferredPrompt = e;
    // Mostra o botão de instalação
    installButton.style.display = 'flex';

    // Adiciona um listener ao botão
    installButton.addEventListener('click', () => {
        // Mostra o prompt de instalação
        deferredPrompt.prompt();
        // Espera pela resposta do usuário
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('Usuário aceitou instalar o PWA');
            } else {
                console.log('Usuário recusou a instalação do PWA');
            }
            // Limpa o deferredPrompt
            deferredPrompt = null;
            // Esconde o botão após a interação
            installButton.style.display = 'none';
        });
    });
});

// Verifica se o app já está instalado
window.addEventListener('appinstalled', () => {
    console.log('PWA foi instalado com sucesso!');
    installButton.style.display = 'none';
});