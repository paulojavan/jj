# Requirements Document - Negativação de Clientes

## Introduction

A funcionalidade de negativação de clientes permite que administradores identifiquem clientes com parcelas em atraso significativo (mais de 60 dias) e realizem o processo de negativação no SPC. O sistema deve gerenciar todo o ciclo desde a identificação de clientes elegíveis para negativação até o controle de clientes já negativados, incluindo opções para reverter o processo quando necessário.

## Requirements

### Requirement 1

**User Story:** Como administrador, eu quero visualizar uma lista de clientes elegíveis para negativação, para que eu possa identificar rapidamente quais clientes precisam ser negativados.

#### Acceptance Criteria

1. WHEN o administrador acessa a página de negativação THEN o sistema SHALL exibir apenas clientes que possuem parcelas com mais de 60 dias de atraso
2. WHEN o sistema filtra clientes elegíveis THEN o sistema SHALL verificar que todos os tickets com id_cliente vinculado ao cliente têm campo spc como null
3. WHEN a lista é exibida THEN o sistema SHALL mostrar nome do cliente, CPF e quantidade de parcelas em atraso
4. IF o usuário não for administrador THEN o sistema SHALL negar acesso à funcionalidade
5. WHEN o administrador clica no nome de um cliente THEN o sistema SHALL redirecionar para a página de detalhamento do cliente

### Requirement 2

**User Story:** Como administrador, eu quero visualizar os detalhes completos de um cliente elegível para negativação, para que eu possa tomar uma decisão informada sobre a negativação.

#### Acceptance Criteria

1. WHEN o administrador acessa a página de detalhamento THEN o sistema SHALL exibir foto, nome e CPF do cliente
2. WHEN a página carrega THEN o sistema SHALL listar todas as parcelas em atraso do titular
3. WHEN a página carrega THEN o sistema SHALL listar todas as parcelas em atraso dos clientes autorizados vinculados ao titular
4. WHEN o sistema calcula valores THEN o sistema SHALL usar a mesma fórmula da rota pagamentos/cliente para calcular parcela + juros + multa
5. WHEN os valores são exibidos THEN o sistema SHALL mostrar a soma total de todas as parcelas em atraso
6. WHEN a página é renderizada THEN o sistema SHALL exibir botão para abrir WhatsApp com link para wa.me/número_do_cliente
7. WHEN a página é renderizada THEN o sistema SHALL exibir botão para realizar negativação

### Requirement 3

**User Story:** Como administrador, eu quero negativar um cliente, para que eu possa registrar formalmente a inadimplência no sistema.

#### Acceptance Criteria

1. WHEN o administrador clica em negativar THEN o sistema SHALL identificar todas as parcelas com status "aguardando pagamento"
2. WHEN o processo de negativação inicia THEN o sistema SHALL buscar o valor do campo ticket das parcelas
3. WHEN o sistema processa a negativação THEN o sistema SHALL alterar o campo spc para true na tabela tickets para os tickets relacionados
4. WHEN a negativação é processada THEN o sistema SHALL alterar o status do cliente para "inativo"
5. WHEN a negativação é processada THEN o sistema SHALL alterar o campo obs do cliente para "cliente negativado"
6. WHEN a negativação é concluída THEN o sistema SHALL redirecionar para a lista de clientes negativados
7. WHEN ocorre erro no processo THEN o sistema SHALL reverter todas as alterações e exibir mensagem de erro

### Requirement 4

**User Story:** Como administrador, eu quero visualizar uma lista de clientes negativados, para que eu possa acompanhar e gerenciar clientes que já foram negativados.

#### Acceptance Criteria

1. WHEN o administrador acessa a página de clientes negativados THEN o sistema SHALL listar todos os clientes com status "inativo" e obs "cliente negativado"
2. WHEN a lista é exibida THEN o sistema SHALL mostrar nome, CPF e data da negativação
3. WHEN o administrador clica em um cliente THEN o sistema SHALL redirecionar para página de detalhes do cliente negativado
4. IF o usuário não for administrador THEN o sistema SHALL negar acesso à funcionalidade

### Requirement 5

**User Story:** Como administrador, eu quero visualizar os detalhes de um cliente negativado, para que eu possa revisar as informações e tomar ações necessárias.

#### Acceptance Criteria

1. WHEN o administrador acessa detalhes do cliente negativado THEN o sistema SHALL exibir foto, nome e CPF do cliente
2. WHEN a página carrega THEN o sistema SHALL listar compras negativadas do titular separadamente
3. WHEN a página carrega THEN o sistema SHALL listar compras negativadas dos clientes autorizados separadamente
4. WHEN o sistema calcula valores THEN o sistema SHALL mostrar parcelas não pagas com cálculo atualizado de juros e multa
5. WHEN os valores são calculados THEN o sistema SHALL exibir soma total das parcelas a pagar
6. WHEN a página é renderizada THEN o sistema SHALL exibir botão para WhatsApp abrindo em nova guia
7. WHEN a página é renderizada THEN o sistema SHALL exibir botão para retornar parcelas
8. WHEN a página é renderizada THEN o sistema SHALL exibir botão para remover negativação

### Requirement 6

**User Story:** Como administrador, eu quero retornar parcelas de um cliente negativado, para que eu possa reverter pagamentos específicos quando necessário.

#### Acceptance Criteria

1. WHEN o administrador clica em "retornar parcelas" THEN o sistema SHALL identificar compras com tickets onde spc = true
2. WHEN o sistema processa o retorno THEN o sistema SHALL filtrar parcelas relacionadas com id_vendedor = 1
3. WHEN as parcelas são identificadas THEN o sistema SHALL alterar data_pagamento, hora, valor_pago, dinheiro, pix, cartao, metodo, id_vendedor, ticket_pagamento para NULL
4. WHEN as parcelas são processadas THEN o sistema SHALL alterar status para "aguardando pagamento"
5. WHEN o processo é concluído THEN o sistema SHALL exibir mensagem de confirmação
6. WHEN ocorre erro THEN o sistema SHALL reverter alterações e exibir mensagem de erro

### Requirement 7

**User Story:** Como administrador, eu quero remover a negativação de um cliente, para que eu possa reativar o cliente quando a situação for regularizada.

#### Acceptance Criteria

1. WHEN o administrador clica em "remover negativação" THEN o sistema SHALL identificar todos os tickets com spc = true do cliente
2. WHEN o sistema processa a remoção THEN o sistema SHALL alterar o campo spc para NULL nos tickets identificados
3. WHEN a negativação é removida THEN o sistema SHALL alterar o status do cliente para "ativo"
4. WHEN a negativação é removida THEN o sistema SHALL limpar o campo obs do cliente
5. WHEN o processo é concluído THEN o sistema SHALL redirecionar para lista de clientes negativados
6. WHEN ocorre erro THEN o sistema SHALL reverter alterações e exibir mensagem de erro

### Requirement 8

**User Story:** Como administrador, eu quero navegar facilmente entre as funcionalidades de negativação, para que eu possa gerenciar eficientemente o processo completo.

#### Acceptance Criteria

1. WHEN o sistema renderiza páginas de negativação THEN o sistema SHALL incluir navegação clara entre as seções
2. WHEN o administrador está em qualquer página de negativação THEN o sistema SHALL exibir menu com links para "Clientes para Negativar" e "Clientes Negativados"
3. WHEN o sistema exibe botões de ação THEN o sistema SHALL usar confirmações JavaScript para ações irreversíveis
4. WHEN o sistema processa ações THEN o sistema SHALL exibir feedback visual (loading, success, error)
5. WHEN ocorrem erros THEN o sistema SHALL exibir mensagens claras em português