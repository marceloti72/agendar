const CACHE_NAME = 'versao-1'; // Mude a versão quando atualizar os arquivos
const urlsToCache = [
    '/app/', // Página inicial
    'index.php', // Ou sua página principal
    'css/style.css', // Seus arquivos CSS
    'js/script.js', // Seus arquivos JS
    'img/icone_192.png', // Ícone principal
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