# Requirements Document

## Introduction

O sistema de histórico de compras do cliente permitirá visualizar todas as compras realizadas por um cliente específico, incluindo detalhes das parcelas, status de pagamento e perfil de comportamento de pagamento. Esta funcionalidade será acessível através da página de pesquisa de clientes e fornecerá informações completas sobre o relacionamento comercial com cada cliente.

## Requirements

### Requirement 1

**User Story:** Como um funcionário da loja, eu quero visualizar o histórico de compras de um cliente, para que eu possa acompanhar seu relacionamento comercial com a loja.

#### Acceptance Criteria

1. WHEN o usuário estiver na página de pesquisa de cliente (rota clientes?cliente=) THEN o sistema SHALL exibir um botão "Histórico de Compras" abaixo do botão "Editar"
2. WHEN o usuário clicar no botão "Histórico de Compras" THEN o sistema SHALL redirecionar para uma página dedicada ao histórico do cliente
3. WHEN a página de histórico for carregada THEN o sistema SHALL exibir as compras do cliente ordenadas da mais recente para a mais antiga
4. WHEN houver mais de 20 compras THEN o sistema SHALL implementar paginação exibindo 20 compras por página

### Requirement 2

**User Story:** Como um funcionário da loja, eu quero visualizar os detalhes básicos de cada compra em cards expansíveis, para que eu possa ter uma visão geral rápida das transações.

#### Acceptance Criteria

1. WHEN as compras forem exibidas THEN o sistema SHALL mostrar cada compra em um card que contém: número do ticket, data da compra, valor total da compra e valor da entrada
2. WHEN o card estiver no estado colapsado THEN o sistema SHALL exibir apenas as informações básicas da compra
3. WHEN o usuário clicar no card THEN o sistema SHALL expandir o card para mostrar as parcelas da compra
4. WHEN o card estiver expandido THEN o sistema SHALL exibir um botão "Ver Compra Completa" para acessar os detalhes dos produtos

### Requirement 3

**User Story:** Como um funcionário da loja, eu quero visualizar as parcelas de cada compra com status colorido, para que eu possa identificar rapidamente a situação de pagamento.

#### Acceptance Criteria

1. WHEN o card da compra estiver expandido THEN o sistema SHALL exibir todas as parcelas com: número da parcela, data de vencimento, valor da parcela e status
2. WHEN uma parcela estiver paga THEN o sistema SHALL exibir o status em cor verde
3. WHEN uma parcela não estiver paga e não estiver vencida THEN o sistema SHALL exibir o status em cor preta
4. WHEN uma parcela não estiver paga e estiver vencida THEN o sistema SHALL exibir o status em cor vermelha
5. WHEN uma parcela tiver status de devolução THEN o sistema SHALL exibir o status em cor amarela

### Requirement 4

**User Story:** Como um funcionário da loja, eu quero visualizar os produtos de uma compra específica, para que eu possa ver exatamente o que foi adquirido.

#### Acceptance Criteria

1. WHEN o usuário clicar em "Ver Compra Completa" THEN o sistema SHALL exibir uma página com todos os produtos da compra
2. WHEN a página de detalhes da compra for exibida THEN o sistema SHALL mostrar a lista completa de produtos adquiridos
3. WHEN a página de detalhes da compra for exibida THEN o sistema SHALL exibir três botões de ação: "Duplicata", "Carnê de Pagamento" e "Mensagem de Aviso"

### Requirement 5

**User Story:** Como um funcionário da loja, eu quero visualizar o perfil de pagamento do cliente, para que eu possa entender seus hábitos de pagamento e histórico comercial.

#### Acceptance Criteria

1. WHEN a página de histórico de compras for exibida THEN o sistema SHALL calcular e exibir o perfil de pagamento do cliente
2. WHEN o perfil de pagamento for calculado THEN o sistema SHALL determinar se o cliente costuma pagar: atrasado, no dia ou adiantado (baseado na comparação entre data_vencimento e data_pagamento)
3. WHEN o perfil de pagamento for exibido THEN o sistema SHALL mostrar se o cliente tem um número alto de compras devolvidas em relação ao total de compras
4. WHEN o perfil de pagamento for exibido THEN o sistema SHALL exibir o valor total que o cliente já comprou na loja (excluindo compras devolvidas)
5. WHEN o perfil de pagamento for exibido THEN o sistema SHALL mostrar a data da primeira compra do cliente
6. WHEN o perfil de pagamento for exibido THEN o sistema SHALL indicar se o cliente faz compras de maneira regular ou esporádica

### Requirement 6

**User Story:** Como um funcionário da loja, eu quero que o sistema acesse corretamente os dados das compras, para que todas as informações sejam precisas e atualizadas.

#### Acceptance Criteria

1. WHEN o sistema buscar as compras do cliente THEN o sistema SHALL consultar a tabela "tickets" usando o identificador do cliente
2. WHEN o sistema buscar as parcelas THEN o sistema SHALL consultar a tabela de parcelas relacionada aos tickets
3. WHEN o sistema calcular estatísticas THEN o sistema SHALL considerar apenas compras não devolvidas para totais financeiros
4. WHEN o sistema determinar status de parcelas THEN o sistema SHALL comparar a data atual com a data de vencimento para identificar parcelas vencidas