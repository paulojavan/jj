# Implementation Plan - Negativação de Clientes

- [x] 1. Criar middleware de administrador e service de cálculo


  - Implementar AdminMiddleware para controle de acesso
  - Criar CalculoParcelaService para reutilizar lógica de cálculo de juros/multa
  - Registrar middleware no Kernel.php
  - _Requirements: 1.4, 2.4, 3.1_



- [ ] 2. Implementar métodos auxiliares nos models existentes
  - Adicionar método isElegivelNegativacao() no model Cliente
  - Adicionar método isNegativado() no model Cliente


  - Criar testes unitários para os novos métodos
  - _Requirements: 1.1, 1.2, 4.1_

- [x] 3. Criar controller principal de negativação


  - Implementar NegativacaoController com método index() para listar clientes elegíveis
  - Implementar lógica de query para buscar clientes com parcelas +60 dias e tickets sem spc
  - Criar testes para o método index()
  - _Requirements: 1.1, 1.2, 1.3_



- [ ] 4. Implementar detalhamento de cliente elegível
  - Criar método show() no NegativacaoController para exibir detalhes do cliente
  - Implementar cálculo de valores usando CalculoParcelaService
  - Separar parcelas do titular e dos autorizados
  - Criar testes para o método show()


  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [ ] 5. Implementar processo de negativação
  - Criar método negativar() no NegativacaoController


  - Implementar transação para atualizar tickets (spc=true) e cliente (status=inativo, obs=cliente negativado)
  - Adicionar validações e tratamento de erros
  - Criar testes para o processo de negativação
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_



- [ ] 6. Implementar listagem de clientes negativados
  - Criar método negativados() no NegativacaoController
  - Implementar query para buscar clientes com status inativo e obs "cliente negativado"
  - Criar testes para listagem de negativados
  - _Requirements: 4.1, 4.2, 4.3, 4.4_



- [ ] 7. Implementar detalhamento de cliente negativado
  - Criar método showNegativado() no NegativacaoController
  - Exibir compras negativadas separadas (titular vs autorizados)
  - Calcular valores atualizados com juros/multa


  - Criar testes para detalhamento de negativado
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 8. Implementar funcionalidade de retornar parcelas
  - Criar método retornarParcelas() no NegativacaoController


  - Identificar parcelas com id_vendedor=1 de tickets com spc=true
  - Limpar campos de pagamento e alterar status para "aguardando pagamento"
  - Criar testes para retorno de parcelas
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_



- [ ] 9. Implementar remoção de negativação
  - Criar método removerNegativacao() no NegativacaoController
  - Alterar spc para NULL nos tickets e reativar cliente
  - Implementar validações e transações
  - Criar testes para remoção de negativação


  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

- [ ] 10. Criar rotas para negativação
  - Definir grupo de rotas com middleware auth e admin
  - Implementar todas as rotas necessárias (index, show, negativar, etc.)


  - Adicionar nomes de rotas para facilitar navegação
  - Criar testes de rota e middleware
  - _Requirements: 1.5, 4.3, 8.1, 8.2_

- [x] 11. Criar view para lista de clientes elegíveis

  - Implementar negativacao/index.blade.php com layout responsivo
  - Exibir tabela com foto, nome, CPF, parcelas em atraso e valor total
  - Adicionar paginação e filtros de busca
  - Implementar links para detalhamento
  - _Requirements: 1.3, 1.5, 8.1_


- [ ] 12. Criar view para detalhamento de cliente elegível
  - Implementar negativacao/show.blade.php
  - Exibir dados do cliente, parcelas do titular e autorizados separadamente
  - Implementar botão WhatsApp com link wa.me/numero
  - Adicionar botão de negativação com confirmação JavaScript
  - _Requirements: 2.1, 2.2, 2.3, 2.6, 2.7, 8.3_


- [ ] 13. Criar view para lista de clientes negativados
  - Implementar negativacao/negativados.blade.php
  - Reutilizar componentes da lista de elegíveis com adaptações
  - Adicionar coluna de data de negativação
  - Implementar filtros específicos para negativados


  - _Requirements: 4.1, 4.2, 4.3, 8.1_

- [ ] 14. Criar view para detalhamento de cliente negativado
  - Implementar negativacao/show-negativado.blade.php
  - Exibir compras negativadas separadas por titular/autorizados
  - Implementar três botões: WhatsApp, Retornar Parcelas, Remover Negativação
  - Adicionar confirmações JavaScript para ações críticas
  - _Requirements: 5.1, 5.2, 5.3, 5.6, 5.7, 5.8, 8.3, 8.4_

- [ ] 15. Criar componentes reutilizáveis
  - Implementar component cliente-card para exibir dados do cliente
  - Criar component parcelas-table para tabelas de parcelas
  - Implementar component whatsapp-button para botões do WhatsApp
  - Criar component confirmation-modal para confirmações
  - _Requirements: 2.6, 5.6, 8.3, 8.4_

- [ ] 16. Integrar navegação no menu administrativo
  - Adicionar seção "Negativação SPC" no menu admin
  - Criar submenu com "Clientes para Negativar" e "Clientes Negativados"
  - Implementar indicadores visuais de status
  - Testar navegação entre páginas
  - _Requirements: 8.1, 8.2_

- [ ] 17. Implementar validações e tratamento de erros
  - Adicionar validações de entrada em todos os métodos
  - Implementar tratamento específico para cada tipo de exceção
  - Criar mensagens de erro em português
  - Adicionar logs de auditoria para operações críticas
  - _Requirements: 3.7, 6.6, 7.6, 8.5_

- [ ] 18. Criar testes de integração completos
  - Implementar teste end-to-end do fluxo de negativação
  - Testar cenários de erro e recuperação
  - Validar integridade dos dados após operações
  - Testar controle de acesso e middleware
  - _Requirements: 1.4, 3.7, 6.6, 7.6_