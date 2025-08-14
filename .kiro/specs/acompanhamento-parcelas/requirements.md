# Requirements Document

## Introduction

Esta funcionalidade permite que clientes acompanhem suas parcelas pendentes através de uma interface web. O sistema deve permitir a consulta por CPF, exibir parcelas em aberto com cálculos de juros e multas, e permitir a seleção de parcelas para pagamento. A funcionalidade abrange tanto parcelas do titular quanto de clientes autorizados associados.

## Requirements

### Requirement 1

**User Story:** Como cliente, eu quero consultar minhas parcelas pendentes usando meu CPF, para que eu possa acompanhar meus débitos em aberto.

#### Acceptance Criteria

1. WHEN o cliente acessa a página de consulta THEN o sistema SHALL exibir um campo de entrada para CPF com máscara de formatação
2. WHEN o cliente digita o CPF THEN o sistema SHALL aplicar automaticamente a máscara no formato XXX.XXX.XXX-XX
3. WHEN o cliente submete o CPF THEN o sistema SHALL buscar o cliente na tabela clientes mantendo a formatação da máscara
4. IF o CPF não for encontrado THEN o sistema SHALL exibir mensagem de erro informando que o cliente não foi localizado
5. IF o CPF for encontrado THEN o sistema SHALL recuperar os campos id, nome e foto do cliente

### Requirement 2

**User Story:** Como cliente, eu quero visualizar todas as minhas parcelas em aberto, para que eu possa ter uma visão completa dos meus débitos.

#### Acceptance Criteria

1. WHEN o cliente é localizado THEN o sistema SHALL buscar todas as parcelas com status "aguardando pagamento" na tabela parcelas
2. WHEN as parcelas são recuperadas THEN o sistema SHALL separar as parcelas do titular (id_autorizado = null) das parcelas de clientes autorizados
3. IF existem parcelas de clientes autorizados THEN o sistema SHALL agrupar as parcelas por id_autorizado
4. WHEN as parcelas são exibidas THEN o sistema SHALL mostrar ticket da parcela, número da parcela, valor da parcela, dias de atraso e valor a pagar
5. WHEN há múltiplos clientes autorizados THEN o sistema SHALL exibir as parcelas separadamente por cliente autorizado

### Requirement 3

**User Story:** Como cliente, eu quero ver o cálculo correto de juros e multas nas minhas parcelas em atraso, para que eu saiba exatamente quanto preciso pagar.

#### Acceptance Criteria

1. WHEN o sistema calcula dias de atraso THEN o sistema SHALL comparar a data de vencimento com a data atual
2. IF os dias de atraso forem menores ou iguais aos dias de carência THEN o sistema SHALL exibir apenas o valor da parcela
3. IF os dias de atraso forem maiores que os dias de carência THEN o sistema SHALL calcular multa e juros
4. WHEN calcula a multa THEN o sistema SHALL aplicar a taxa_multa (percentual) sobre o valor da parcela
5. WHEN calcula os juros THEN o sistema SHALL aplicar a taxa_juros mensal dividida por 30 multiplicada pelos dias de atraso
6. IF os dias de atraso excederem dias_cobrança THEN o sistema SHALL limitar o cálculo de juros ao máximo de dias_cobrança
7. WHEN exibe o valor a pagar THEN o sistema SHALL somar valor_parcela + multa + juros

### Requirement 4

**User Story:** Como cliente, eu quero selecionar parcelas específicas para pagamento, para que eu possa escolher quais débitos quitar.

#### Acceptance Criteria

1. WHEN as parcelas são exibidas THEN o sistema SHALL incluir um checkbox para cada parcela
2. WHEN o cliente seleciona um checkbox THEN o sistema SHALL adicionar o valor da parcela ao total selecionado
3. WHEN o cliente desmarca um checkbox THEN o sistema SHALL subtrair o valor da parcela do total selecionado
4. WHEN parcelas são selecionadas/desmarcadas THEN o sistema SHALL atualizar automaticamente o valor total em tempo real
5. WHEN nenhuma parcela está selecionada THEN o sistema SHALL exibir total como R$ 0,00
6. WHEN múltiplas parcelas são selecionadas THEN o sistema SHALL somar todos os valores a pagar das parcelas marcadas

### Requirement 5

**User Story:** Como cliente, eu quero navegar facilmente entre a consulta por CPF e a visualização das parcelas, para que eu possa ter uma experiência fluida.

#### Acceptance Criteria

1. WHEN o sistema exibe as parcelas THEN o sistema SHALL manter as informações do cliente (nome e foto) visíveis
2. WHEN o cliente está na página de parcelas THEN o sistema SHALL fornecer opção para fazer nova consulta
3. WHEN o cliente faz nova consulta THEN o sistema SHALL limpar os dados da consulta anterior
4. WHEN há erro na consulta THEN o sistema SHALL manter o cliente na página de consulta com mensagem de erro
5. WHEN a consulta é bem-sucedida THEN o sistema SHALL navegar para a página de visualização das parcelas