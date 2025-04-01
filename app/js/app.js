// app.js
let deferredPrompt;

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



// Variável global para guardar a inscrição
let swRegistration = null;
// Chave PÚBLICA VAPID (Gerada no servidor - veja Parte 2)
// Substitua pela sua chave pública VAPID convertida para Uint8Array
const applicationServerPublicKey = 'SUA_CHAVE_PUBLICA_VAPID_CONVERTIDA'; // PRECISA SER GERADA

// Função para converter a chave VAPID de base64 para Uint8Array
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Função para pedir permissão e inscrever
function subscribeUser() {
    if (!swRegistration) {
        console.error('Service Worker não está registrado.');
        return;
    }

    const applicationServerKey = urlBase64ToUint8Array(applicationServerPublicKey);

    swRegistration.pushManager.subscribe({
        userVisibleOnly: true, // Obrigatório - indica que cada push resultará em uma notificação visível
        applicationServerKey: applicationServerKey // Chave pública VAPID
    })
    .then(subscription => {
        console.log('Usuário inscrito com sucesso:', JSON.stringify(subscription));
        // *** IMPORTANTE: Envie a 'subscription' para o seu servidor backend! ***
        sendSubscriptionToServer(subscription);
    })
    .catch(err => {
        if (Notification.permission === 'denied') {
            console.warn('Permissão para notificações foi negada.');
            alert('Você bloqueou as notificações. Para recebê-las, habilite nas configurações do navegador.');
        } else {
            console.error('Falha ao inscrever o usuário: ', err);
            alert('Não foi possível se inscrever para notificações.');
        }
    });
}

// Função para pedir permissão (chamar após uma ação do usuário, não no load)
function askPermission() {
    return new Promise(function(resolve, reject) {
        const permissionResult = Notification.requestPermission(function(result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
    .then(function(permissionResult) {
        if (permissionResult === 'granted') {
            console.log('Permissão concedida.');
            // Permissão concedida, agora podemos inscrever
            subscribeUser();
        } else {
             console.warn('Permissão negada.');
             alert('Permissão para notificações não concedida.');
        }
    });
}

// Função para enviar a inscrição para o servidor (EXEMPLO com fetch)
function sendSubscriptionToServer(subscription) {
    // Adapte a URL e o método conforme seu backend
    const serverUrl = '/salvar_inscricao_push.php'; // << SEU ENDPOINT PHP

    fetch(serverUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(subscription), // Envia o objeto de inscrição como JSON
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor ao salvar inscrição.');
        }
        return response.json(); // Ou response.text() se seu PHP retornar texto
    })
    .then(responseData => {
        console.log('Inscrição salva no servidor:', responseData);
    })
    .catch(error => {
        console.error('Erro ao enviar inscrição para o servidor:', error);
    });
}

// Registra o Service Worker (você já deve ter isso)
if ('serviceWorker' in navigator && 'PushManager' in window) {
    console.log('Service Worker e Push são suportados');

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js') // Caminho para o seu SW
            .then(swReg => {
                console.log('Service Worker registrado:', swReg);
                swRegistration = swReg; // Armazena o registro

                // Opcional: Verificar se já está inscrito ao carregar
                // swRegistration.pushManager.getSubscription().then(subscription => {
                //    if (subscription === null) {
                //        console.log('Não inscrito, oferecendo botão.');
                //        // Mostrar botão para pedir permissão
                //    } else {
                //        console.log('Já inscrito.');
                //        // Esconder/desabilitar botão de inscrição
                //    }
                // });

            })
            .catch(error => {
                console.error('Erro no registro do Service Worker:', error);
            });
    });
} else {
    console.warn('Push Messaging não é suportado');
    // Esconder botão de pedir permissão, etc.
}

// --- Adicione um botão no seu HTML para o usuário pedir permissão ---
// <button id="btnPermissaoPush">Ativar Notificações</button>
// E adicione um listener para ele:
const btnPermissao = document.getElementById('btnPermissaoPush');
if (btnPermissao) {
    btnPermissao.addEventListener('click', () => {
        askPermission(); // Chama a função para pedir permissão
        btnPermissao.disabled = true; // Desabilita após clique
    });

     //Verifica se ja tem permissão e desabilita
     if(Notification.permission === 'granted'){
        btnPermissao.disabled = true;
        btnPermissao.textContent = 'Notificações Ativadas';
     }
}