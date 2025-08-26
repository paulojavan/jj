# Requirements Document

## Introduction

Esta funcionalidade permite identificar e entrar em contato com clientes que estão ociosos há mais de 150 dias, facilitando a reativação de relacionamentos comerciais através de mensagens personalizadas via WhatsApp. O sistema deve filtrar clientes que não possuem restrições no SPC e automatizar o processo de envio de mensagens de reengajamento.

## Requirements

### Requirement 1

**User Story:** Como funcionário da loja, eu quero visualizar uma lista de clientes ociosos, para que eu possa identificar quem precisa ser contatado para reativação.

#### Acceptance Criteria

1. WHEN o usuário acessa a página de clientes ociosos THEN o sistema SHALL exibir uma lista de clientes que atendem aos critérios de ociosidade
2. WHEN o sistema calcula a ociosidade THEN SHALL considerar apenas clientes com campo 'ociosidade' com diferença mínima de 150 dias da data atual
3. WHEN o sistema filtra os clientes THEN SHALL excluir clientes que possuem tickets com campo 'spc' igual a true
4. WHEN a lista é exibida THEN SHALL mostrar nome completo, data de ociosidade e botão de ação para cada cliente

### Requirement 2

**User Story:** Como funcionário da loja, eu quero enviar mensagem personalizada via WhatsApp para clientes ociosos, para que eu possa reativá-los de forma eficiente.

#### Acceptance Criteria

1. WHEN o usuário clica no botão "Enviar Mensagem" THEN o sistema SHALL abrir uma nova aba do navegador
2. WHEN a nova aba é aberta THEN o sistema SHALL atualizar o campo 'ociosidade' do cliente para a data atual
3. WHEN o campo é atualizado THEN o sistema SHALL redirecionar para o WhatsApp Web com mensagem pré-formatada
4. WHEN a mensagem é formatada THEN SHALL incluir os dois primeiros nomes do cliente
5. WHEN a mensagem é enviada THEN SHALL conter o texto: "Bom dia, {dois primeiros nomes do cliente}, tudo bem com você? Estamos sentindo sua falta, notamos sua ausência de nossa loja nos últimos tempos. Confira nossas novidades no instagram @joecio_calcados. Você é um cliente especial para nós. Seu crediário continua ativo, esperamos por o seu retorno em uma de nossas lojas, estamos de braços abertos!"

### Requirement 3

**User Story:** Como administrador do sistema, eu quero que o sistema mantenha controle das datas de contato, para que eu possa evitar spam e controlar a frequência de mensagens.

#### Acceptance Criteria

1. WHEN uma mensagem é enviada para um cliente THEN o sistema SHALL atualizar automaticamente o campo 'ociosidade' para a data atual
2. WHEN o campo 'ociosidade' é atualizado THEN o cliente SHALL ser removido da lista de ociosos até que complete novamente 150 dias
3. WHEN o sistema processa a lista THEN SHALL garantir que clientes recentemente contatados não apareçam na listagem

### Requirement 4

**User Story:** Como funcionário da loja, eu quero uma interface intuitiva para gerenciar clientes ociosos, para que eu possa trabalhar de forma eficiente.

#### Acceptance Criteria

1. WHEN a página é carregada THEN o sistema SHALL exibir uma tabela responsiva com informações dos clientes
2. WHEN a tabela é exibida THEN SHALL incluir colunas para nome, data de ociosidade, dias de ociosidade e ação
3. WHEN não há clientes ociosos THEN o sistema SHALL exibir mensagem informativa
4. WHEN há muitos clientes THEN o sistema SHALL implementar paginação ou scroll infinito
5. WHEN o usuário interage com a interface THEN SHALL receber feedback visual das ações realizadas