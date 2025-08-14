# Implementation Plan

- [x] 1. Criar estrutura base do controlador e rotas


  - Criar FluxoCaixaController com métodos básicos
  - Definir rotas para fluxo geral e individualizado
  - Implementar middleware de autenticação
  - _Requirements: 1.1, 2.2_



- [ ] 2. Implementar service de lógica de negócio
  - Criar FluxoCaixaService com métodos principais
  - Implementar classe TabelaDinamica para acesso às tabelas por cidade


  - Criar métodos para obtenção de dados de vendas e recebimentos
  - _Requirements: 4.1, 4.2, 6.1_

- [ ] 3. Implementar controle de permissões e filtros de data
  - Criar lógica para verificação de nível de usuário (administrador vs vendedor)

  - Implementar filtros de período para administradores
  - Implementar restrição de data atual para vendedores
  - Implementar filtros por cidade baseados no usuário
  - _Requirements: 1.1, 1.2, 1.4, 2.1, 2.2, 3.1_

- [ ] 4. Implementar cálculos de vendas
  - Criar método para buscar vendas das tabelas dinâmicas por cidade

  - Implementar lógica de filtro por id_vendedor e data_venda
  - Implementar tratamento de vendas estornadas (data_estorno)
  - Implementar soma/subtração baseada em data_estorno
  - Implementar cálculo separado por método de pagamento (dinheiro, pix, cartão, crediário)
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2_


- [ ] 5. Implementar cálculos de recebimentos de parcelas
  - Criar método para buscar parcelas filtradas por id_vendedor e data_pagamento
  - Implementar organização por método, data_pagamento e hora
  - Implementar soma separada de campos dinheiro, pix e cartão

  - _Requirements: 6.1, 6.2, 6.3_

- [ ] 6. Implementar cálculo de despesas
  - Criar método para buscar despesas das tabelas dinâmicas por cidade
  - Implementar filtro por data, tipo="despesa" e status="Pago"
  - Implementar subtração de despesas dos recebimentos em dinheiro
  - _Requirements: 7.1, 7.2, 7.3, 7.4_



- [ ] 7. Implementar agrupamento e organização de dados
  - Criar lógica de agrupamento por cidade e depois por vendedor
  - Implementar filtro para omitir vendedores sem vendas no período
  - Implementar geração de relatórios individuais por vendedor
  - Implementar relatório consolidado por cidade
  - Implementar relatório geral para administradores


  - _Requirements: 3.2, 3.3, 8.1, 8.2, 8.3, 8.4_

- [ ] 8. Criar views para fluxo geral
  - Criar view principal index.blade.php com filtros de período
  - Implementar componente de seleção de datas condicionais por nível


  - Criar componente de exibição de relatórios agrupados por cidade
  - Implementar cards individuais por vendedor com vendas e recebimentos
  - Implementar cores diferenciadas para métodos de pagamento
  - Implementar background vermelho para vendas estornadas
  - _Requirements: 1.1, 1.4, 3.1, 4.6, 6.4_


- [ ] 9. Criar views para fluxo individualizado
  - Criar view individualizado.blade.php
  - Implementar campos de data inicial e final
  - Implementar select de vendedores baseado no nível do usuário
  - Criar componente de relatório individual
  - _Requirements: 9.1, 9.2, 9.3_



- [ ] 10. Implementar funcionalidade de fluxo individualizado
  - Implementar método relatorioIndividualizado no controller
  - Implementar filtro por id_vendedor_atendente para vendas
  - Aplicar mesma lógica de cálculo do fluxo geral
  - Implementar busca de recebimentos individualizados
  - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [ ] 11. Implementar componentes reutilizáveis de interface
  - Criar partial para filtros de período (filtros-periodo.blade.php)
  - Criar partial para relatório de vendas (relatorio-vendas.blade.php)
  - Criar partial para relatório de recebimentos (relatorio-recebimentos.blade.php)
  - Criar partial para resumo por cidade (resumo-cidade.blade.php)
  - Implementar responsividade com Tailwind CSS
  - _Requirements: 5.3, 6.4, 8.1, 8.2_

- [ ] 12. Implementar validações e tratamento de erros
  - Criar validações para campos de data (formato e período válido)
  - Implementar tratamento para tabelas de cidade inexistentes



  - Criar mensagens de erro em português com SweetAlert2
  - Implementar logs de erro para debugging
  - _Requirements: 1.1, 2.1, 9.1_

- [ ] 13. Implementar testes unitários para o service
  - Criar testes para FluxoCaixaService
  - Testar cálculos de vendas com e sem estornos
  - Testar cálculos de recebimentos e despesas
  - Testar agrupamentos e filtros por usuário
  - _Requirements: 4.4, 4.5, 6.1, 7.3_

- [ ] 14. Implementar testes de integração
  - Criar testes para fluxo completo do controller
  - Testar permissões de administrador vs vendedor
  - Testar relatórios com dados de múltiplas cidades
  - Testar fluxo individualizado
  - _Requirements: 1.1, 2.2, 3.1, 10.1_

- [ ] 15. Finalizar integração e ajustes finais
  - Integrar todas as funcionalidades no sistema principal
  - Adicionar links de navegação no menu principal
  - Realizar testes de usabilidade e responsividade
  - Otimizar performance para grandes volumes de dados
  - _Requirements: 8.4, 9.3, 10.4_