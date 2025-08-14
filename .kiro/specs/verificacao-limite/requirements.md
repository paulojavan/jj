# Requirements Document

## Introduction

A funcionalidade de verificação de limite permite que administradores e usuários autorizados analisem o perfil de clientes para tomar decisões sobre ajustes de limite de crédito. O sistema deve fornecer uma interface completa para busca de clientes, visualização de perfil de compras e pagamentos, e gerenciamento de limites de crédito com controle de status ativo/inativo.

## Requirements

### Requirement 1

**User Story:** Como administrador ou usuário autorizado, eu quero acessar uma página de verificação de limite restrita, para que apenas pessoas com permissão adequada possam gerenciar limites de crédito dos clientes.

#### Acceptance Criteria

1. WHEN um usuário tenta acessar a página de verificação de limite THEN o sistema SHALL verificar se o usuário é administrador ou possui o campo 'limite' como true
2. IF o usuário não possui permissão THEN o sistema SHALL redirecionar para página de acesso negado ou login
3. WHEN um usuário autorizado acessa a página THEN o sistema SHALL exibir a interface de verificação de limite

### Requirement 2

**User Story:** Como usuário autorizado, eu quero buscar clientes por nome, apelido ou CPF, para que eu possa encontrar rapidamente o cliente cujo limite preciso verificar.

#### Acceptance Criteria

1. WHEN eu digito no campo de busca THEN o sistema SHALL permitir busca por nome, apelido ou CPF
2. WHEN eu realizo uma busca THEN o sistema SHALL exibir uma lista de clientes que correspondem aos critérios
3. WHEN a busca não retorna resultados THEN o sistema SHALL exibir mensagem informativa
4. WHEN eu seleciono um cliente da lista THEN o sistema SHALL carregar o perfil completo do cliente

### Requirement 3

**User Story:** Como usuário autorizado, eu quero visualizar as informações básicas do cliente selecionado, para que eu tenha contexto completo sobre o cliente antes de tomar decisões sobre limite.

#### Acceptance Criteria

1. WHEN um cliente é selecionado THEN o sistema SHALL exibir nome, apelido, RG, CPF e renda na parte superior da tela
2. WHEN as informações básicas são carregadas THEN o sistema SHALL também exibir as referências comerciais do cliente
3. IF alguma informação básica não estiver disponível THEN o sistema SHALL exibir campo vazio ou indicador de "não informado"

### Requirement 4

**User Story:** Como usuário autorizado, eu quero visualizar um perfil detalhado de compras do cliente, para que eu possa analisar o histórico de compras e comportamento de consumo.

#### Acceptance Criteria

1. WHEN um cliente é selecionado THEN o sistema SHALL gerar e exibir um perfil de compras detalhado
2. WHEN o perfil de compras é exibido THEN o sistema SHALL incluir informações como valor total de compras, frequência, produtos mais comprados, e período de atividade
3. WHEN o perfil de compras é carregado THEN o sistema SHALL organizar as informações de forma clara e analítica

### Requirement 5

**User Story:** Como usuário autorizado, eu quero visualizar um perfil detalhado de pagamentos do cliente, para que eu possa avaliar o comportamento de pagamento e risco de crédito.

#### Acceptance Criteria

1. WHEN um cliente é selecionado THEN o sistema SHALL gerar e exibir um perfil de pagamentos mais detalhado que o histórico de compras existente
2. WHEN o perfil de pagamentos é exibido THEN o sistema SHALL incluir informações como pontualidade de pagamentos, atrasos, valores em aberto, e padrões de pagamento
3. WHEN o perfil de pagamentos é carregado THEN o sistema SHALL calcular e exibir indicadores de risco e confiabilidade
4. WHEN há dados de pagamento THEN o sistema SHALL apresentar estatísticas que facilitem a análise e decisão sobre limite

### Requirement 6

**User Story:** Como usuário autorizado, eu quero alterar o limite de crédito do cliente através de um input específico, para que eu possa ajustar o limite baseado na análise dos perfis.

#### Acceptance Criteria

1. WHEN um cliente é selecionado THEN o sistema SHALL exibir um input para alteração do limite atual
2. WHEN eu altero o valor no input de limite THEN o sistema SHALL validar se o valor é numérico e positivo
3. WHEN eu salvo a alteração de limite THEN o sistema SHALL atualizar o limite no banco de dados
4. WHEN o limite é alterado THEN o sistema SHALL registrar a alteração com data, hora e usuário responsável

### Requirement 7

**User Story:** Como usuário autorizado, eu quero controlar o status ativo/inativo do cliente através de um checkbox deslizante, para que eu possa ativar ou desativar clientes conforme necessário.

#### Acceptance Criteria

1. WHEN um cliente é selecionado THEN o sistema SHALL exibir um checkbox deslizante mostrando o status atual (ativo/inativo)
2. WHEN eu altero o status através do checkbox THEN o sistema SHALL atualizar imediatamente o status no banco de dados
3. WHEN o status é alterado THEN o sistema SHALL registrar a alteração com data, hora e usuário responsável
4. WHEN um cliente está inativo THEN o sistema SHALL aplicar as regras de negócio apropriadas para clientes inativos