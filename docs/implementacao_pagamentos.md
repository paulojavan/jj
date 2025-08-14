# Documentação e Plano de Tarefas: Módulo de Pagamentos e Consulta de Parcelas

Este documento descreve as funcionalidades a serem implementadas e as tarefas necessárias para sua conclusão.

## Funcionalidade 1: Pagamento de Parcelas (Visão do Funcionário)

**Objetivo:** Permitir que um funcionário realize o recebimento de parcelas de um cliente, calculando juros e multas por atraso e registrando as formas de pagamento.

**Fluxo:**
1.  Na tela de pesquisa de clientes, um novo botão "Pagar" será adicionado para cada cliente.
2.  Ao clicar no botão, o funcionário é redirecionado para uma tela de pagamentos específica para aquele cliente.
3.  A tela exibe a foto e o nome do cliente.
4.  Abaixo, são listadas todas as parcelas com status "Aguardando Pagamento", separadas entre o titular e seus autorizados, e ordenadas por data de vencimento.
5.  Para cada parcela, o sistema exibe: Ticket, Nº da Parcela, Vencimento, Valor Original, Dias em Atraso e **Valor a Pagar**.
6.  O **Valor a Pagar** é calculado da seguinte forma:
    *   Se a parcela não estiver vencida ou estiver dentro do período de carência (`dias_carencia` na tabela `multa_configuracoes`), o valor a pagar é igual ao valor original.
    *   Caso contrário, o valor é acrescido de multa e juros diários, baseados na `taxa_multa` and `taxa_juros` da tabela `multa_configuracoes`.
7.  O funcionário pode selecionar múltiplas parcelas para pagamento. O sistema exibe o valor total a ser pago.
8.  O funcionário insere os valores recebidos nos campos: Dinheiro, PIX e Cartão.
9.  O botão "Realizar Pagamento" é habilitado somente quando a soma dos valores inseridos for igual ao total das parcelas selecionadas.
10. Ao confirmar, o sistema gera um registro na tabela `pagamentos` e atualiza o status e os detalhes de cada parcela paga na tabela `parcelas`.

## Funcionalidade 2: Consulta de Parcelas (Visão do Cliente)

**Objetivo:** Permitir que o cliente consulte suas próprias parcelas em aberto através de uma página pública, sem necessidade de login.

**Fluxo:**
1.  O cliente acessa uma página com design similar à de login.
2.  Ele insere o seu CPF em um campo com máscara.
3.  Ao buscar, o sistema exibe uma tela com a foto e o nome do cliente.
4.  Abaixo, são listadas todas as suas parcelas (e de seus autorizados) com status "Aguardando Pagamento", com os mesmos detalhes da visão do funcionário (incluindo cálculo de juros), porém, sem a opção de realizar o pagamento.

## Lista de Tarefas

### Backend

1.  [ ] Criar a rota `GET /pagamentos/cliente/{id}` para a tela de pagamentos.
2.  [ ] Criar o `PagamentoController`.
3.  [ ] Implementar o método `show(Cliente $cliente)` no `PagamentoController` para buscar o cliente, suas parcelas (e de autorizados), calcular os valores a pagar e retornar a view.
4.  [ ] Criar a rota `POST /pagamentos/cliente/{id}` para processar o pagamento.
5.  [ ] Implementar o método `store(Request $request, Cliente $cliente)` no `PagamentoController` para:
    *   Validar os dados recebidos.
    *   Gerar um ticket único para o pagamento.
    *   Criar o registro na tabela `pagamentos`.
    *   Atualizar cada parcela paga na tabela `parcelas` com os dados do pagamento (data, hora, valor pago, forma de pagamento, status, etc.).
6.  [ ] Criar a rota `GET /consulta-parcelas` para a página de busca por CPF.
7.  [ ] Criar a rota `POST /consulta-parcelas` para processar a busca.
8.  [ ] Criar o `ConsultaParcelaController`.
9.  [ ] Implementar o método `index()` no `ConsultaParcelaController` para exibir o formulário de busca.
10. [ ] Implementar o método `search(Request $request)` no `ConsultaParcelaController` para buscar o cliente pelo CPF e exibir suas parcelas.
11. [ ] Adicionar lógica no modelo `Parcela` ou em um `Service` para o cálculo do valor a pagar (considerando juros e multa).

### Frontend

1.  [ ] Criar a view `resources/views/pagamentos/show.blade.php`.
2.  [ ] Adicionar o botão "Pagar" na view de pesquisa de clientes, redirecionando para a rota de pagamentos.
3.  [ ] Desenvolver a interface da tela de pagamentos, exibindo os dados do cliente e a tabela de parcelas.
4.  [ ] Implementar o JavaScript para:
    *   Seleção de parcelas.
    *   Cálculo do total a pagar em tempo real.
    *   Máscaras monetárias para os campos de pagamento (Dinheiro, PIX, Cartão).
    *   Habilitar/desabilitar o botão de pagamento.
    *   Validação para garantir que a soma dos pagamentos corresponda ao total.
5.  [ ] Criar a view `resources/views/consulta/index.blade.php` com o formulário de CPF.
6.  [ ] Criar a view `resources/views/consulta/show.blade.php` para exibir as parcelas do cliente.
7.  [ ] Aplicar máscara de CPF no campo de input.
