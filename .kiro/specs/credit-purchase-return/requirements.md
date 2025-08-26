# Requirements Document

## Introduction

O sistema de devolução de compras no crediário permitirá que funcionários processem devoluções de compras realizadas a prazo, desde que nenhuma parcela tenha sido paga. Esta funcionalidade será acessível através do histórico de compras do cliente e realizará automaticamente os ajustes necessários no status das parcelas, registros de vendas e estoque dos produtos.

## Requirements

### Requirement 1

**User Story:** Como um funcionário da loja, eu quero visualizar um botão de devolução no histórico de compras, para que eu possa processar devoluções de compras no crediário quando necessário.

#### Acceptance Criteria

1. WHEN o usuário estiver visualizando o histórico de compras de um cliente THEN o sistema SHALL exibir um botão "Devolução" ao lado do botão "Ver Compra Completa" para cada compra
2. WHEN uma compra tiver pelo menos uma parcela paga THEN o sistema SHALL desabilitar o botão "Devolução" para essa compra
3. WHEN uma compra não tiver nenhuma parcela paga THEN o sistema SHALL habilitar o botão "Devolução" para essa compra
4. WHEN o botão estiver desabilitado THEN o sistema SHALL exibir uma dica visual indicando que a devolução não é possível devido a parcelas pagas

### Requirement 2

**User Story:** Como um funcionário da loja, eu quero confirmar a devolução antes de processá-la, para que eu possa evitar devoluções acidentais.

#### Acceptance Criteria

1. WHEN o usuário clicar no botão "Devolução" THEN o sistema SHALL exibir um modal de confirmação
2. WHEN o modal de confirmação for exibido THEN o sistema SHALL mostrar os detalhes da compra que será devolvida (número do ticket, valor total, quantidade de parcelas)
3. WHEN o usuário confirmar a devolução THEN o sistema SHALL processar a devolução
4. WHEN o usuário cancelar a confirmação THEN o sistema SHALL fechar o modal sem processar a devolução

### Requirement 3

**User Story:** Como um funcionário da loja, eu quero que o sistema atualize automaticamente o status das parcelas, para que elas sejam marcadas como devolvidas.

#### Acceptance Criteria

1. WHEN uma devolução for confirmada THEN o sistema SHALL alterar o status de todas as parcelas relacionadas ao ticket para "devolucao"
2. WHEN o status das parcelas for alterado THEN o sistema SHALL manter todos os outros campos das parcelas inalterados
3. WHEN as parcelas forem atualizadas THEN o sistema SHALL confirmar que todas as parcelas do ticket foram processadas

### Requirement 4

**User Story:** Como um funcionário da loja, eu quero que o sistema atualize os registros de vendas, para que eles reflitam a devolução processada.

#### Acceptance Criteria

1. WHEN uma devolução for processada THEN o sistema SHALL localizar todas as vendas com o mesmo ticket da compra devolvida
2. WHEN as vendas forem localizadas THEN o sistema SHALL atualizar o campo data_estorno para a data atual
3. WHEN as vendas forem localizadas THEN o sistema SHALL atualizar o campo baixa_fiscal para true
4. WHEN os registros de vendas forem atualizados THEN o sistema SHALL confirmar que todas as vendas relacionadas ao ticket foram processadas

### Requirement 5

**User Story:** Como um funcionário da loja, eu quero que o sistema reajuste automaticamente o estoque, para que as quantidades dos produtos devolvidos sejam restauradas.

#### Acceptance Criteria

1. WHEN uma devolução for processada THEN o sistema SHALL identificar todos os produtos vendidos no ticket devolvido
2. WHEN os produtos forem identificados THEN o sistema SHALL determinar a cidade/estoque correspondente baseado na venda original
3. WHEN a cidade for determinada THEN o sistema SHALL incrementar a quantidade no estoque correspondente para cada produto devolvido
4. WHEN o estoque for atualizado THEN o sistema SHALL confirmar que as quantidades foram restauradas corretamente

### Requirement 6

**User Story:** Como um funcionário da loja, eu quero receber feedback sobre o sucesso da devolução, para que eu saiba que o processo foi concluído corretamente.

#### Acceptance Criteria

1. WHEN uma devolução for processada com sucesso THEN o sistema SHALL exibir uma mensagem de sucesso
2. WHEN ocorrer um erro durante a devolução THEN o sistema SHALL exibir uma mensagem de erro específica
3. WHEN a devolução for concluída THEN o sistema SHALL atualizar automaticamente a visualização do histórico de compras
4. WHEN a visualização for atualizada THEN o sistema SHALL mostrar o novo status das parcelas como "Devolução"

### Requirement 7

**User Story:** Como um funcionário da loja, eu quero que o sistema mantenha a integridade dos dados, para que todas as operações de devolução sejam consistentes e rastreáveis.

#### Acceptance Criteria

1. WHEN uma devolução for processada THEN o sistema SHALL executar todas as operações em uma transação de banco de dados
2. WHEN ocorrer um erro em qualquer etapa THEN o sistema SHALL reverter todas as alterações feitas
3. WHEN a devolução for concluída THEN o sistema SHALL registrar a operação para auditoria
4. WHEN houver conflitos de dados THEN o sistema SHALL impedir a devolução e informar o erro ao usuário