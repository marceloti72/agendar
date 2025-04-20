const CACHE_NAME = 'versao-1.17'; // Mude a versão quando atualizar os arquivos
const urlsToCache = [
    '/', // Página inicial
    'index.php', // Ou sua página principal
    'https://agendar.skysee.com.br/css/style.css', // Seus arquivos CSS
    'https://agendar.skysee.com.br/css/responsive.css', 
    'https://agendar.skysee.com.br/sistema/painel/css/custom.css', 
    'https://agendar.skysee.com.br/sistema/painel/css/style.css', 
    'https://agendar.skysee.com.br/sistema/painel/js/scripts.js', 
    
    'https://agendar.skysee.com.br/js/custom.js', // Seus arquivos JS
    'https://agendar.skysee.com.br/images/icone_192.png', // Ícone principal
    'https://agendar.skysee.com.br/images/icone_512.png', // Ícone principal
    // Adicione aqui TODOS os arquivos essenciais para a interface básica offline
    // Ex: outras páginas HTML/PHP, imagens importantes, fontes
];

// Evento de Instalação: Cacheia os arquivos estáticos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Cache aberto:', CACHE_NAME);
                return cache.addAll(urlsToCache);
            })
            .catch(error => {
                console.error('Falha ao cachear durante a instalação:', error);
            })
    );
});

// Evento Fetch: Intercepta requisições de rede
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request) // Tenta encontrar a requisição no cache
            .then(response => {
                // Se encontrar no cache, retorna a resposta do cache
                if (response) {
                    return response;
                }
                // Se não encontrar, faz a requisição à rede
                return fetch(event.request).then(
                    function(networkResponse) {
                        // Opcional: Cachear a resposta da rede para uso futuro
                        // Cuidado: Não cacheie tudo, especialmente requisições POST ou dados dinâmicos
                        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                            return networkResponse;
                        }
                        // Clona a resposta para poder usar no cache e retornar ao navegador
                        var responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME)
                            .then(function(cache) {
                                cache.put(event.request, responseToCache);
                            });
                        return networkResponse;
                    }
                ).catch(error => {
                    console.error("Erro no fetch:", error);
                    // Opcional: Retornar uma página offline padrão
                    // return caches.match('/offline.html');
                });
            })
    );
});

// Evento Activate: Limpa caches antigos (quando você atualiza a versão)
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME]; // Mantém apenas o cache atual
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        console.log('Deletando cache antigo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});



// Evento Push: Recebe a notificação do servidor push
self.addEventListener('push', event => {
    console.log('[Service Worker] Push Recebido.');
    let notificationData = {};

    // Tenta pegar dados do payload (se houver)
    if (event.data) {
        try {
            notificationData = event.data.json(); // Tenta decodificar como JSON
             console.log('[Service Worker] Dados Push:', notificationData);
        } catch (e) {
             console.log('[Service Worker] Push tem dados, mas não é JSON:', event.data.text());
             notificationData.body = event.data.text(); // Usa o texto como corpo se não for JSON
        }
    }

    const title = notificationData.title || 'Notificação'; // Título padrão
    const options = {
        body: notificationData.body || 'Você recebeu uma nova notificação.', // Corpo padrão
        icon: notificationData.icon || '/icones/icon-192x192.png', // Ícone padrão
        badge: notificationData.badge || '/icones/badge-72x72.png', // Ícone pequeno para barra de status (Android)
        vibrate: [100, 50, 100], // Vibração: vibra 100ms, pausa 50ms, vibra 100ms
        data: notificationData.data || { url: '/' }, // Dados extras (ex: URL para abrir ao clicar)
        // actions: [ // Botões de ação (opcional)
        //   { action: 'explore', title: 'Ver Detalhes' },
        //   { action: 'close', title: 'Fechar' },
        // ]
    };

    // Mostra a notificação
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Evento Notification Click: O que acontece quando o usuário clica na notificação
self.addEventListener('notificationclick', event => {
    console.log('[Service Worker] Notificação clicada.');

    const notification = event.notification;
    const action = event.action; // Identificador da ação (se houver botões)
    const notificationData = notification.data; // Dados passados no 'data' das opções

    notification.close(); // Fecha a notificação

    if (action === 'close') {
        // Ação de fechar (se você adicionou um botão 'close')
        return;
    }

    // Abre uma janela/aba ou foca uma existente
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }) // Procura por janelas abertas do PWA
            .then(windowClients => {
                // Verifica se já existe uma janela aberta com a URL desejada
                for (let i = 0; i < windowClients.length; i++) {
                    const client = windowClients[i];
                    // Compara a URL do cliente com a URL nos dados da notificação (se existir)
                    // Adapte a lógica de comparação se necessário
                    if (client.url === notificationData.url && 'focus' in client) {
                         console.log("Focando janela existente:", client.url);
                        return client.focus(); // Foca a janela existente
                    }
                }
                // Se nenhuma janela correspondente estiver aberta, abre uma nova
                 console.log("Abrindo nova janela:", notificationData.url || '/');
                if (clients.openWindow && notificationData.url) {
                    return clients.openWindow(notificationData.url); // Abre a URL dos dados
                } else if (clients.openWindow) {
                    return clients.openWindow('/'); // Abre a página inicial como padrão
                }
            })
    );
});

// Opcional: Evento para quando a notificação é fechada (sem clique)
self.addEventListener('notificationclose', event => {
    console.log('[Service Worker] Notificação fechada.');
});