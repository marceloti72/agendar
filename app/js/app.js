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



document.addEventListener('DOMContentLoaded', function () {

    // Instâncias dos Modais Bootstrap
    const modalPedirTelefoneElement = document.getElementById('modalPedirTelefone');
    const modalDetalhesElement = document.getElementById('modalAssinaturaDetalhes');

    if (!modalPedirTelefoneElement || !modalDetalhesElement) {
        console.error("Um ou ambos os modais não foram encontrados.");
        return;
    }

    const modalPedirTelefoneInstance = new bootstrap.Modal(modalPedirTelefoneElement);
    const modalDetalhesInstance = new bootstrap.Modal(modalDetalhesElement);

    // Elementos do Modal de Detalhes para atualização
    const loadingIndicator = document.getElementById('modalAssinaturaLoading');
    const errorContainer = document.getElementById('modalAssinaturaErro');
    const contentContainer = document.getElementById('modalAssinaturaConteudo');
    const clienteNomeSpan = document.getElementById('modalAssinaturaClienteNome');
    const planoNomeSpan = document.getElementById('modalAssinaturaPlanoNome');
    const proximoVencSpan = document.getElementById('modalAssinaturaProximoVenc');
    const servicosTableBody = document.getElementById('modalAssinaturaServicosBody');
    //const btnTrocarPlano = document.getElementById('btnModalTrocarPlano');

    // Elementos do Modal de Pedir Telefone
    const inputTelefoneBusca = document.getElementById('inputTelefoneBusca');
    const senha = document.getElementById('senha2');
    const btnBuscarPorTelefone = document.getElementById('btnBuscarPorTelefone');

    // Função para formatar data (DD/MM/YYYY)
    function formatarData(dataISO) {
        if (!dataISO || !dataISO.includes('-')) return 'N/D';
        const partes = dataISO.split('-');
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    // Função para limpar/formatar telefone (APENAS DÍGITOS - ajuste conforme BD)
    function limparTelefone(telefone) {
        return telefone.replace(/\D/g, ''); // Remove tudo que não for dígito
    }

    // Adiciona listener para o botão que INICIA o processo
    // (ex: um botão na barra de navegação ou menu)
    // Assumindo que este botão tem o id="btnIniciarBuscaAssinatura"
    const btnIniciarBusca = document.getElementById('btnIniciarBuscaAssinatura'); // ** Crie este botão na sua página **
    if (btnIniciarBusca) {
        btnIniciarBusca.addEventListener('click', function() {
            inputTelefoneBusca.value = ''; // Limpa campo
            inputTelefoneBusca.classList.remove('is-invalid'); // Remove validação
            modalPedirTelefoneInstance.show(); // Abre o modal para pedir telefone
        });
    } else {
        console.warn("Botão #btnIniciarBuscaAssinatura não encontrado para iniciar o fluxo.");
    }


    // Adiciona listener para o botão DENTRO do modal de pedir telefone
    if (btnBuscarPorTelefone) {
        btnBuscarPorTelefone.addEventListener('click', function() {
            const telefoneInput = inputTelefoneBusca.value;
            const senha2 = senha.value;
            const telefoneLimpo = limparTelefone(telefoneInput);

            if (telefoneLimpo.length < 10) { // Validação mínima (DDD + 8 ou 9 dígitos)
                inputTelefoneBusca.classList.add('is-invalid');
                return;
            }
            inputTelefoneBusca.classList.remove('is-invalid');

            // Fecha modal de pedir telefone
            modalPedirTelefoneInstance.hide();

            // Prepara e abre o modal de detalhes (mostrando loading)
            loadingIndicator.style.display = 'block';
            errorContainer.style.display = 'none';
            contentContainer.style.display = 'none';
            servicosTableBody.innerHTML = '';
            clienteNomeSpan.textContent = 'Buscando...'; // Feedback inicial
            planoNomeSpan.textContent = '';
            proximoVencSpan.textContent = '';
            //btnTrocarPlano.setAttribute('data-cliente-id', ''); // Limpa ID antigo

            modalDetalhesInstance.show();

            // --- Chamada AJAX para buscar os dados USANDO TELEFONE ---
            // Substitua 'caminho/para/buscar_detalhes_assinatura.php' pelo caminho real
            // Enviando telefone como parâmetro GET
            fetch(`buscar_detalhes_assinatura.php?telefone=${encodeURIComponent(telefoneInput)}&s=${encodeURIComponent(senha2)}`)
                .then(response => {
                    if (!response.ok) {
                        // Tenta ler a resposta de erro mesmo se não for 2xx
                        return response.json().then(errData => {
                            throw { status: response.status, data: errData };
                        }).catch(() => {
                             // Se não conseguir ler JSON do erro, lança erro genérico
                             throw { status: response.status, data: { message: `Erro HTTP ${response.status}` } };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    loadingIndicator.style.display = 'none';

                    if (data.success) {
                        contentContainer.style.display = 'block';

                        // Preenche informações gerais
                        clienteNomeSpan.textContent = data.cliente_nome || 'Não informado';
                        planoNomeSpan.textContent = data.plano_nome || 'Nenhum plano ativo';
                        proximoVencSpan.textContent = formatarData(data.proximo_vencimento) || 'Não definido';
                        //btnTrocarPlano.setAttribute('data-cliente-id', data.cliente_id || ''); // Guarda o ID encontrado

                        // Preenche a tabela de serviços
                        if (data.servicos && data.servicos.length > 0) {
                            data.servicos.forEach(servico => {
                                const limiteTexto = (servico.limite_ciclo === 0) ? 'Ilimitado' : servico.limite_ciclo;
                                const usoAtual = servico.uso_atual || 0;

                                const row = `
                                    <tr>
                                        <td>${servico.nome || 'Serviço Desconhecido'}</td>
                                        <td class="text-center">${usoAtual}</td>
                                        <td class="text-center">${limiteTexto}</td>
                                    </tr>
                                `;
                                servicosTableBody.innerHTML += row;
                            });
                        } else if (data.plano_nome !== 'Nenhum') { // Só mostra se tem plano mas sem serviços listados ou sem ciclo
                             servicosTableBody.innerHTML = '<tr><td colspan="3" class="text-center">Não foi possível carregar o uso dos serviços (verifique o ciclo de pagamento).</td></tr>';
                        } else {
                             servicosTableBody.innerHTML = '<tr><td colspan="3" class="text-center">Nenhum serviço associado.</td></tr>';
                        }

                    } else {
                        // Exibe mensagem de erro retornada pelo PHP
                        errorContainer.textContent = data.message || 'Erro ao buscar dados.';
                        errorContainer.style.display = 'block';
                        contentContainer.style.display = 'none'; // Garante que conteúdo antigo não apareça
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar detalhes da assinatura:', error);
                    loadingIndicator.style.display = 'none';
                     // Tenta exibir a mensagem de erro vinda do servidor, se disponível
                    const serverMessage = error.data?.message || 'Não foi possível conectar ao servidor.';
                    errorContainer.textContent = serverMessage;
                    errorContainer.style.display = 'block';
                    contentContainer.style.display = 'none';
                });
        });
    }


    
    // Adiciona listener para o botão "Trocar Plano" (dentro do modal de detalhes)
    // if (btnTrocarPlano) {
    //     btnTrocarPlano.addEventListener('click', function() {
    //         const clienteId = this.getAttribute('data-cliente-id');
    //         if (!clienteId) {
    //              alert("Não foi possível identificar o cliente para trocar o plano.");
    //              return;
    //         }
    //         alert(`Funcionalidade "Trocar Plano" para cliente ID: ${clienteId} ainda não implementada.`);
            // Lógica para trocar plano...
            // modalDetalhesInstance.hide(); // Fecha modal atual
            // ... (abre outro modal ou redireciona)
    //     });
    // }

     // Opcional: Adicionar máscara ao campo de telefone usando uma biblioteca como Inputmask.js
     // Exemplo (precisa incluir a biblioteca Inputmask antes):
     /*
     const telInput = document.getElementById('inputTelefoneBusca');
     if (telInput && typeof Inputmask !== 'undefined') {
         Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(telInput);
     }
     */

});