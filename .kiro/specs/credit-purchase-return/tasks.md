# Implementation Plan

- [x] 1. Criar service class para lógica de devolução


  - Implementar CreditReturnService com métodos para validação e processamento
  - Criar método canReturn() para verificar se devolução é possível
  - Implementar método processReturn() para executar devolução completa
  - Adicionar métodos auxiliares para atualização de parcelas, vendas e estoque
  - _Requirements: 3.1, 4.1, 5.1, 7.1_



- [ ] 2. Adicionar método canBeReturned ao modelo Ticket
  - Implementar lógica para verificar se ticket pode ser devolvido
  - Verificar se nenhuma parcela foi paga
  - Verificar se compra não foi devolvida anteriormente


  - Criar testes unitários para o método
  - _Requirements: 1.2, 1.3, 7.4_

- [ ] 3. Implementar método de devolução no ClienteController
  - Adicionar método processarDevolucao() no ClienteController


  - Implementar validações de entrada e permissões
  - Integrar com CreditReturnService para processar devolução
  - Retornar resposta JSON apropriada para frontend
  - _Requirements: 2.3, 6.1, 6.2, 7.3_



- [ ] 4. Atualizar view do histórico de compras
  - Adicionar botão "Devolução" ao lado do botão "Ver Compra Completa"
  - Implementar lógica condicional para habilitar/desabilitar botão
  - Adicionar tooltip explicativo quando botão estiver desabilitado
  - Integrar com JavaScript para chamada AJAX

  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [ ] 5. Implementar modal de confirmação com JavaScript
  - Criar função JavaScript para exibir modal de confirmação
  - Mostrar detalhes da compra (ticket, valor, parcelas) no modal
  - Implementar chamada AJAX para processar devolução

  - Adicionar tratamento de respostas de sucesso e erro
  - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [ ] 6. Implementar atualização de parcelas na devolução
  - Criar método updateParcelas() no CreditReturnService
  - Atualizar status de todas as parcelas do ticket para "devolucao"

  - Garantir que apenas parcelas não pagas sejam atualizadas
  - Adicionar validação de integridade dos dados
  - _Requirements: 3.1, 3.2, 3.3_

- [ ] 7. Implementar atualização de registros de vendas
  - Criar método updateSalesRecords() no CreditReturnService

  - Localizar todas as vendas com o ticket da devolução
  - Atualizar campos data_estorno e baixa_fiscal
  - Implementar busca dinâmica em tabelas de vendas por cidade
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [ ] 8. Implementar reajuste de estoque
  - Criar método updateInventory() no CreditReturnService
  - Identificar produtos e quantidades da venda devolvida
  - Determinar cidade/estoque baseado na venda original
  - Incrementar quantidades nos estoques correspondentes
  - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 9. Implementar controle transacional

  - Envolver todas as operações de devolução em transação de banco


  - Implementar rollback automático em caso de erro
  - Adicionar logging para auditoria das operações
  - Garantir consistência de dados entre todas as tabelas


  - _Requirements: 7.1, 7.2, 7.3_

- [ ] 10. Adicionar rota para processamento de devolução
  - Criar rota POST para processar devoluções
  - Implementar middleware de autenticação e autorização



  - Adicionar validação de parâmetros da rota
  - Configurar proteção CSRF
  - _Requirements: 2.3, 6.1_

- [ ] 11. Implementar feedback visual para o usuário
  - Adicionar mensagens de sucesso após devolução processada
  - Implementar exibição de erros específicos
  - Atualizar automaticamente a visualização do histórico
  - Mostrar novo status das parcelas como "Devolução"
  - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ] 12. Criar testes unitários para CreditReturnService
  - Testar método canReturn() com diferentes cenários
  - Testar processReturn() com dados válidos e inválidos
  - Testar métodos auxiliares de atualização
  - Verificar tratamento de erros e exceções
  - _Requirements: 7.1, 7.2, 7.3, 7.4_

- [ ] 13. Criar testes de integração para fluxo completo
  - Testar devolução bem-sucedida end-to-end
  - Testar cenários de erro (parcelas pagas, ticket inválido)
  - Verificar consistência de dados após devolução
  - Testar rollback em caso de falha
  - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1, 6.1, 7.1_