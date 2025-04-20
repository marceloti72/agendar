document.addEventListener('DOMContentLoaded', function () {
    const btnIniciarBuscaAssinatura = document.getElementById('btnIniciarBuscaAssinatura');
    const btnBuscarPorTelefone = document.getElementById('btnBuscarPorTelefone');
    const inputTelefoneBusca = document.getElementById('inputTelefoneBusca');
    const inputSenha = document.getElementById('senha2');
    const modalPedirTelefone = document.getElementById('modalPedirTelefone');
    const modalPedirTelefoneInstance = new bootstrap.Modal(modalPedirTelefone);

    // Função para limpar o telefone (remover caracteres não numéricos)
    function limparTelefone(telefone) {
        return telefone.replace(/\D/g, '');
    }

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

    if (btnBuscarPorTelefone) {
        btnBuscarPorTelefone.addEventListener('click', function () {
            const telefoneInput = inputTelefoneBusca.value;
            const senha = inputSenha.value;
            const telefoneLimpo = limparTelefone(telefoneInput);

            // Validação do telefone (mínimo 10 dígitos: DDD + 8 ou 9 dígitos)
            if (telefoneLimpo.length < 10) {
                inputTelefoneBusca.classList.add('is-invalid');
                return;
            }

            // Validação da senha (não vazia)
            if (!senha) {
                inputSenha.classList.add('is-invalid');
                return;
            }

            inputTelefoneBusca.classList.remove('is-invalid');
            inputSenha.classList.remove('is-invalid');

            // Fecha o modal
            modalPedirTelefoneInstance.hide();

            // Cria um formulário dinâmico para enviar os dados via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'tela-assinatura-detalhes.php';

            // Adiciona os campos ao formulário
            const fields = [
                { name: 'telefone', value: telefoneInput },
                { name: 'senha', value: senha },
                { name: 'id_conta', value: '<?php echo htmlspecialchars($id_conta); ?>' }
            ];

            fields.forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = field.name;
                input.value = field.value;
                form.appendChild(input);
            });

            // Adiciona o formulário ao documento e submete
            document.body.appendChild(form);
            form.submit();
        });
    }
});