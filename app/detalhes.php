<div class="modal fade" id="modalAssinaturaDetalhes" tabindex="-1" aria-labelledby="modalAssinaturaDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #295f41;">
                <h5 class="modal-title" id="modalAssinaturaDetalhesLabel">Detalhes da Assinatura</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">               
                <div id="modalAssinaturaLoading" style="display: none;" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p>Buscando detalhes...</p>
                </div>
                
                <div id="modalAssinaturaErro" class="alert alert-danger" style="display: none;">
                    Não foi possível carregar os detalhes da assinatura.
                </div>
                
                <div id="modalAssinaturaConteudo">
                    <p><strong>Cliente:</strong> <span id="modalAssinaturaClienteNome" class="text-primary"></span></p>
                    <p><strong>Plano Atual:</strong> <span id="modalAssinaturaPlanoNome" class="fw-bold"></span></p>
                    <p><strong>Próximo Vencimento:</strong> <span id="modalAssinaturaProximoVenc" class="text-danger"></span></p>

                    <hr>
                    <h6>Serviços Incluídos e Uso no Ciclo Atual:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th class="text-center">Uso Atual</th>
                                    <th class="text-center">Limite no Ciclo</th>
                                </tr>
                            </thead>
                            <tbody id="modalAssinaturaServicosBody">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-white" style="background-color: #295f41;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>                
                
            </div>
        </div>
    </div>
</div>