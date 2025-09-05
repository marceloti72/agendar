<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Comanda</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-container {
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .modal-body-scroll {
            overflow-y: auto;
        }
        .modal-header-custom {
            background-color: #2563eb;
            color: white;
            padding: 1rem 1.5rem;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
        .modal-icon {
            margin-right: 0.5rem;
        }
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .section-icon {
            color: #2563eb;
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }
        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 1.5rem 0;
        }
        .item-list-container {
            min-height: 50px;
        }
        .item-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .valor-display {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        .total-display {
            background-color: #d1fae5;
            color: #047857;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .pagamento-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .pagamento-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 0.5rem;
        }
        @media (min-width: 768px) {
            .modal-left-panel, .modal-right-panel {
                height: 80vh;
                overflow-y: auto;
            }
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Modal Overlay -->
    <div id="modalForm2" class="modal-overlay">
        <div class="modal-container bg-white rounded-xl shadow-2xl overflow-hidden md:flex">
            <!-- Modal Header -->
            <div class="w-full modal-header-custom flex justify-between items-center">
                <h4 class="text-xl font-bold" id="titulo_comanda">
                    <i class="fas fa-cash-register modal-icon"></i>
                    Nova Comanda
                </h4>
                <button type="button" id="btn-fechar" class="text-white text-3xl font-light leading-none hover:text-gray-200" onclick="hideModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form_salvar" class="w-full h-full flex flex-col md:flex-row">
                <!-- Left Panel -->
                <div class="md:w-3/4 modal-left-panel p-6 overflow-y-auto">
                    <div class="mb-4">
                        <h3 id="nome_do_cliente_aqui" class="text-2xl font-bold text-gray-800">Cliente Exemplo</h3>
                    </div>

                    <!-- Services Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                        <div class="section-header">
                            <i class="fas fa-cut section-icon"></i>
                            <h5 class="text-xl font-semibold text-gray-800">Serviços</h5>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                            <div class="col-span-8">
                                <label for="servico" class="block text-sm font-medium text-gray-700 mb-1">Serviço</label>
                                <select class="form-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="servico" name="servico">
                                    <option value="1">Corte de Cabelo</option>
                                    <option value="2">Barba</option>
                                    <option value="3">Coloração</option>
                                    <option value="4">Penteado</option>
                                    <!-- PHP was here, replaced with static options for demonstration -->
                                </select>
                            </div>
                            <div class="col-span-4">
                                <label for="funcionario2" class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
                                <select class="form-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="funcionario2" name="funcionario">
                                    <option value="1">João Silva</option>
                                    <option value="2">Maria Souza</option>
                                    <!-- PHP was here, replaced with static options for demonstration -->
                                </select>
                            </div>
                            <div class="md:col-span-12">
                                <button type="button" class="w-full btn-success bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow transition duration-200" onclick="inserirServico()">
                                    <i class="fa fa-plus mr-2"></i> Adicionar Serviço
                                </button>
                            </div>
                        </div>
                        <div class="item-list-container space-y-2" id="listar_servicos"></div>
                    </div>

                    <!-- Products Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                        <div class="section-header">
                            <i class="fas fa-box section-icon"></i>
                            <h5 class="text-xl font-semibold text-gray-800">Produtos</h5>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                            <div class="col-span-8">
                                <label for="produto" class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
                                <select class="form-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="produto" name="produto">
                                    <option value="1">Shampoo</option>
                                    <option value="2">Condicionador</option>
                                    <option value="3">Creme para Barba</option>
                                    <!-- PHP was here, replaced with static options for demonstration -->
                                </select>
                            </div>
                            <div class="col-span-4">
                                <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                                <input type="number" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="quantidade" id="quantidade" value="1" min="1">
                            </div>
                            <div class="md:col-span-12">
                                <button type="button" class="w-full btn-success bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow transition duration-200" onclick="inserirProduto()">
                                    <i class="fa fa-plus mr-2"></i> Adicionar Produto
                                </button>
                            </div>
                        </div>
                        <div class="item-list-container space-y-2" id="listar_produtos"></div>
                    </div>

                    <!-- Discounts & Observations Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                        <div class="section-header">
                            <i class="fas fa-percentage section-icon"></i>
                            <h5 class="section-title text-xl font-semibold text-gray-800">Descontos</h5>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sinal (Valor Pago)</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right text-red-500 font-semibold" id="valor_sinal" value="R$ 10,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desconto Cupom</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right text-red-500 font-semibold" id="valor_cupom" value="R$ 5,00" readonly>
                            </div>
                        </div>
                        <hr class="divider">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                            <textarea class="form-textarea block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="obs" id="obs2" maxlength="1000" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Panel -->
                <div class="md:w-1/4 bg-gray-50 p-6 modal-right-panel overflow-y-auto border-t md:border-t-0 md:border-l border-gray-200">
                    <div class="pagamento-container">
                        <div class="pagamento-header">
                            <img src="https://placehold.co/80x80/2563eb/ffffff?text=CASH" alt="Ícone Pagamento" class="pagamento-icon rounded-full p-2 bg-blue-500 mb-2">
                            <h4 class="text-2xl font-bold text-gray-800">PAGAMENTO</h4>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Serviços</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right valor-display" id="valor_servicos" value="R$ 0,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Produtos</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right valor-display" id="valor_produtos" value="R$ 0,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Descontos</label>
                                <input type="text" class="form-input block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-right valor-display text-red-500 font-semibold" id="valor_descontos" value="R$ 15,00" readonly>
                            </div>
                            <hr class="border-gray-300">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total a Pagar</label>
                                <input type="text" class="form-input block w-full rounded-md border-2 border-green-500 shadow-sm bg-green-100 text-right total-display" name="valor_total" id="valor_serv" value="R$ 0,00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                                <select class="form-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="forma_pgto" name="forma_pgto"> 
                                    <option value="">Selecione</option>
                                    <option value="Mercado Pago">Mercado Pago</option>
                                    <option value="Credito">Cartão de Crédito</option>
                                    <option value="Debito">Cartão de Débito</option>
                                    <option value="Pix">Pix</option>
                                    <option value="Dinheiro">Dinheiro</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <a href="#" id="btn_fechar_comanda" class="btn btn-success w-full bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 text-center" onclick="fecharComanda(event)">
                                <i class="fas fa-check-circle mr-2"></i> Fechar Comanda
                            </a>
                            <button type="button" class="btn btn-outline-secondary w-full border border-gray-400 text-gray-600 hover:bg-gray-200 font-bold py-3 px-6 rounded-lg shadow-sm transition duration-200" onclick="hideModal()">
                                <i class="fas fa-times-circle mr-2"></i> Sair
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </body>
</html>