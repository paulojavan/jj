# Requirements Document

## Introduction

Esta funcionalidade permite aos administradores do sistema JJ Calçados configurar as taxas de multa, juros e prazos para cobrança de parcelas em atraso. O sistema deve fornecer uma interface intuitiva para gerenciar essas configurações e exibir um card na tela inicial para acesso rápido às configurações.

## Requirements

### Requirement 1

**User Story:** Como administrador do sistema, eu quero configurar taxas de multa e juros para parcelas em atraso, para que o sistema possa calcular automaticamente os valores de cobrança.

#### Acceptance Criteria

1. WHEN o administrador acessa a tela de configuração THEN o sistema SHALL exibir os campos para taxa de multa (%), taxa de juros (%) e quantidade de dias para início da cobrança
2. WHEN o administrador insere uma taxa de multa THEN o sistema SHALL validar que o valor está entre 0% e 100%
3. WHEN o administrador insere uma taxa de juros THEN o sistema SHALL validar que o valor está entre 0% e 50%
4. WHEN o administrador insere quantidade de dias THEN o sistema SHALL validar que o valor é um número inteiro positivo
5. WHEN o administrador salva as configurações THEN o sistema SHALL armazenar os valores no banco de dados
6. WHEN as configurações são salvas com sucesso THEN o sistema SHALL exibir uma mensagem de confirmação

### Requirement 2

**User Story:** Como usuário do sistema, eu quero visualizar as configurações atuais de cobrança na tela inicial, para que eu possa consultar essas informações rapidamente.

#### Acceptance Criteria

1. WHEN qualquer usuário acessa a tela inicial THEN o sistema SHALL exibir um card com as configurações atuais de cobrança ao lado do card de configurar descontos
2. WHEN não existem configurações cadastradas THEN o sistema SHALL exibir valores padrão (multa: 2%, juros: 1%, dias: 30)
3. WHEN o administrador clica no card THEN o sistema SHALL redirecionar para a tela de configuração
4. WHEN um usuário não-administrador clica no card THEN o sistema SHALL exibir apenas as informações sem opção de edição
5. WHEN as configurações são atualizadas THEN o card SHALL refletir os novos valores imediatamente

### Requirement 3

**User Story:** Como administrador, eu quero editar as configurações existentes de cobrança, para que eu possa ajustar as taxas conforme necessário.

#### Acceptance Criteria

1. WHEN o administrador acessa a tela de configuração com dados existentes THEN o sistema SHALL pré-preencher os campos com os valores atuais
2. WHEN o administrador modifica qualquer campo THEN o sistema SHALL validar os novos valores antes de salvar
3. WHEN o administrador cancela a edição THEN o sistema SHALL manter os valores originais
4. WHEN o administrador confirma as alterações THEN o sistema SHALL atualizar os valores no banco de dados

### Requirement 4

**User Story:** Como administrador do sistema, eu quero que apenas administradores possam editar as configurações de cobrança, para que essas configurações sensíveis sejam protegidas.

#### Acceptance Criteria

1. WHEN um usuário não-administrador tenta acessar a URL de configuração diretamente THEN o sistema SHALL negar o acesso e redirecionar para página de erro 403
2. WHEN um administrador acessa a funcionalidade THEN o sistema SHALL permitir todas as operações de configuração
3. WHEN um usuário não-administrador visualiza o card na tela inicial THEN o sistema SHALL exibir apenas as informações sem botão de editar
4. WHEN um administrador visualiza o card na tela inicial THEN o sistema SHALL exibir botão "Configurar" para acessar a edição

### Requirement 5

**User Story:** Como desenvolvedor, eu quero que o sistema mantenha um histórico das alterações nas configurações, para que seja possível rastrear mudanças nas taxas.

#### Acceptance Criteria

1. WHEN uma configuração é criada ou alterada THEN o sistema SHALL registrar a data, hora, usuário e valores anteriores/novos
2. WHEN o administrador solicita o histórico THEN o sistema SHALL exibir todas as alterações em ordem cronológica
3. WHEN não há histórico disponível THEN o sistema SHALL exibir mensagem informativa