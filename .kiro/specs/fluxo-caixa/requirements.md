# Requirements Document

## Introduction

Esta funcionalidade implementa um sistema completo de fluxo de caixa para o JJ Calçados, permitindo que administradores e vendedores visualizem relatórios de vendas e recebimentos. O sistema oferece duas modalidades: relatório geral por período (com agrupamento por cidade e vendedor) e relatório individualizado por vendedor específico. A funcionalidade respeita as permissões de usuário, onde administradores têm acesso completo a todas as cidades e períodos personalizados, enquanto vendedores visualizam apenas dados da sua cidade e do dia atual.

## Requirements

### Requirement 1

**User Story:** Como administrador, quero selecionar um intervalo de datas personalizado para visualizar o fluxo de caixa, para que eu possa analisar o desempenho financeiro em qualquer período desejado.

#### Acceptance Criteria

1. WHEN o usuário logado possui nível "administrador" THEN o sistema SHALL exibir campos de data inicial e data final para seleção de período
2. WHEN o administrador seleciona um intervalo de datas THEN o sistema SHALL filtrar todas as vendas e recebimentos dentro desse período
3. WHEN o administrador não seleciona datas THEN o sistema SHALL usar o dia atual como padrão
4. IF o usuário não for administrador THEN o sistema SHALL mostrar apenas dados do dia atual sem opção de alterar período

### Requirement 2

**User Story:** Como vendedor, quero visualizar o fluxo de caixa do dia atual da minha cidade, para que eu possa acompanhar meu desempenho diário.

#### Acceptance Criteria

1. WHEN o usuário logado não é administrador THEN o sistema SHALL exibir apenas vendas e recebimentos do dia atual
2. WHEN o vendedor acessa o fluxo de caixa THEN o sistema SHALL filtrar dados apenas da cidade associada ao usuário
3. WHEN o vendedor visualiza o relatório THEN o sistema SHALL mostrar apenas suas próprias vendas e recebimentos

### Requirement 3

**User Story:** Como administrador, quero visualizar dados de todas as cidades no relatório geral, para que eu possa ter uma visão completa do negócio.

#### Acceptance Criteria

1. WHEN o administrador acessa o fluxo de caixa THEN o sistema SHALL exibir dados de todas as cidades
2. WHEN o sistema agrupa os dados THEN o sistema SHALL organizar primeiro por cidade, depois por vendedor
3. WHEN um vendedor não realizou vendas no período THEN o sistema SHALL omitir esse vendedor do relatório

### Requirement 4

**User Story:** Como usuário, quero visualizar as vendas organizadas por vendedor, para que eu possa analisar o desempenho individual de cada um.

#### Acceptance Criteria

1. WHEN o sistema exibe vendas THEN o sistema SHALL agrupar por vendedor usando o campo id_vendedor da tabela vendas_cidade
2. WHEN o sistema filtra vendas THEN o sistema SHALL usar o campo data_venda para verificar se está no período selecionado
3. IF data_venda equals data_estorno THEN o sistema SHALL omitir essa venda do relatório
4. WHEN data_estorno is NULL THEN o sistema SHALL somar os valores da venda
5. WHEN data_estorno is NOT NULL THEN o sistema SHALL subtrair os valores da venda
6. WHEN uma venda foi estornada THEN o sistema SHALL exibir com background vermelho

### Requirement 5

**User Story:** Como usuário, quero visualizar os valores detalhados por método de pagamento, para que eu possa entender a distribuição dos recebimentos.

#### Acceptance Criteria

1. WHEN o sistema calcula vendas THEN o sistema SHALL somar separadamente valor_dinheiro, valor_pix, valor_cartao e valor_crediario
2. WHEN o sistema exibe o relatório THEN o sistema SHALL mostrar a soma de cada campo separadamente
3. WHEN o sistema apresenta os totais THEN o sistema SHALL detalhar cada método de pagamento individualmente

### Requirement 6

**User Story:** Como usuário, quero visualizar os recebimentos de parcelas organizados por método e data, para que eu possa acompanhar o fluxo de entrada de dinheiro.

#### Acceptance Criteria

1. WHEN o sistema busca recebimentos THEN o sistema SHALL filtrar a tabela parcelas pelo id_vendedor e data_pagamento
2. WHEN o sistema organiza recebimentos THEN o sistema SHALL ordenar por método, data_pagamento e depois por hora
3. WHEN o sistema exibe recebimentos THEN o sistema SHALL somar separadamente campos de dinheiro, pix e cartão
4. WHEN o sistema apresenta métodos THEN o sistema SHALL usar cores de background diferentes para pix, dinheiro e cartão

### Requirement 7

**User Story:** Como usuário, quero que as despesas sejam subtraídas dos recebimentos em dinheiro, para que eu tenha o valor líquido real disponível.

#### Acceptance Criteria

1. WHEN o sistema calcula recebimentos em dinheiro THEN o sistema SHALL subtrair as despesas do período
2. WHEN o sistema busca despesas THEN o sistema SHALL filtrar tabela despezas_nome_da_cidade pelo campo data
3. WHEN o sistema filtra despesas THEN o sistema SHALL considerar apenas registros onde tipo = "despesa" AND status = "Pago"
4. WHEN o sistema apresenta o relatório THEN o sistema SHALL mostrar o valor líquido após subtração das despesas

### Requirement 8

**User Story:** Como usuário, quero visualizar relatórios individuais de vendas e recebimentos por vendedor, para que eu possa analisar o desempenho específico.

#### Acceptance Criteria

1. WHEN o sistema gera relatórios THEN o sistema SHALL criar um relatório de vendas individual para cada vendedor
2. WHEN o sistema gera relatórios THEN o sistema SHALL criar um relatório de recebimentos individual para cada vendedor
3. WHEN o sistema finaliza relatórios individuais THEN o sistema SHALL gerar um relatório consolidado da cidade
4. IF o usuário é administrador THEN o sistema SHALL gerar um relatório geral de todas as cidades

### Requirement 9

**User Story:** Como usuário, quero acessar uma funcionalidade de fluxo individualizado, para que eu possa analisar um vendedor específico em um período determinado.

#### Acceptance Criteria

1. WHEN o usuário acessa fluxo individualizado THEN o sistema SHALL exibir campos de data inicial e data final
2. WHEN o usuário acessa fluxo individualizado THEN o sistema SHALL exibir um select com vendedores disponíveis
3. IF o usuário é administrador THEN o sistema SHALL mostrar todos os vendedores no select
4. IF o usuário não é administrador THEN o sistema SHALL mostrar apenas seu próprio usuário no select

### Requirement 10

**User Story:** Como usuário, quero que o fluxo individualizado use a mesma lógica de cálculo, para que eu tenha consistência nos relatórios.

#### Acceptance Criteria

1. WHEN o sistema filtra vendas no fluxo individualizado THEN o sistema SHALL usar o campo id_vendedor_atendente
2. WHEN o sistema calcula vendas individualizadas THEN o sistema SHALL aplicar a mesma lógica de soma/subtração baseada em data_estorno
3. WHEN o sistema busca recebimentos individualizados THEN o sistema SHALL usar a mesma lógica da funcionalidade geral
4. WHEN o sistema apresenta resultados THEN o sistema SHALL manter o mesmo formato e organização do relatório geral