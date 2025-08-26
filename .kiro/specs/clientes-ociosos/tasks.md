# Implementation Plan

- [x] 1. Implementar método de listagem de clientes ociosos no ClienteController


  - Criar método `clientesOciosos()` que filtra clientes com ociosidade >= 150 dias
  - Implementar query que exclui clientes com tickets onde spc = true
  - Adicionar paginação para performance com listas grandes
  - Calcular dias de ociosidade para cada cliente na listagem
  - _Requirements: 1.1, 1.2, 1.3, 1.4_



- [ ] 2. Criar rota para acessar funcionalidade de clientes ociosos
  - Adicionar rota GET `/clientes/ociosos` no arquivo de rotas web
  - Criar rota POST `/clientes/{id}/mensagem-ocioso` para envio de mensagens


  - Configurar middleware de autenticação para as rotas
  - _Requirements: 1.1, 2.1_

- [ ] 3. Desenvolver view para listagem de clientes ociosos
  - Criar arquivo `resources/views/cliente/ociosos.blade.php`
  - Implementar tabela responsiva com colunas: nome, data ociosidade, dias ociosos, ação

  - Adicionar botão "Enviar Mensagem" para cada cliente
  - Implementar design mobile-friendly com cards colapsáveis
  - Adicionar mensagem informativa quando não há clientes ociosos
  - _Requirements: 1.4, 4.1, 4.2, 4.3_

- [ ] 4. Implementar método de envio de mensagem WhatsApp
  - Criar método `enviarMensagemOcioso($id)` no ClienteController

  - Implementar lógica para extrair dois primeiros nomes do cliente
  - Formatar número de telefone para padrão WhatsApp (incluir código do país)
  - Gerar mensagem personalizada conforme especificação
  - Atualizar campo `ociosidade` do cliente para data atual
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1_

- [x] 5. Adicionar validações e tratamento de erros

  - Implementar validação de existência do cliente
  - Validar se cliente possui telefone válido
  - Adicionar tratamento de exceções com logs de erro
  - Implementar validação de negócio para período mínimo de 150 dias
  - Verificar status do cliente antes de permitir envio
  - _Requirements: 3.2, 3.3_



- [ ] 6. Implementar funcionalidade JavaScript para nova aba
  - Adicionar JavaScript para abrir WhatsApp em nova aba
  - Implementar confirmação antes de enviar mensagem
  - Adicionar feedback visual durante processamento


  - Configurar redirecionamento automático após atualização do banco
  - _Requirements: 2.1, 4.5_

- [ ] 7. Adicionar navegação e integração com interface existente
  - Adicionar link para clientes ociosos no menu de navegação
  - Integrar com layout base existente (`layouts.base`)


  - Aplicar classes CSS do Tailwind e Flowbite para consistência visual
  - Adicionar breadcrumbs para navegação
  - _Requirements: 4.1, 4.4_

- [ ] 8. Criar testes unitários para lógica de negócio
  - Escrever teste para filtro de clientes ociosos com período correto



  - Criar teste para exclusão de clientes com SPC true
  - Implementar teste para cálculo de dias de ociosidade
  - Testar atualização do campo ociosidade após envio de mensagem
  - Testar formatação de telefone para WhatsApp
  - _Requirements: 1.2, 1.3, 2.2, 2.3, 3.1_

- [ ] 9. Criar testes de feature para fluxo completo
  - Testar acesso à página de clientes ociosos
  - Verificar se envio de mensagem atualiza campo ociosidade
  - Testar geração correta do link WhatsApp
  - Verificar se cliente é removido da lista após contato
  - Testar comportamento com lista vazia
  - _Requirements: 1.1, 2.1, 2.4, 3.2_

- [ ] 10. Implementar melhorias de performance e segurança
  - Adicionar índices de banco de dados para campos `ociosidade` e `spc`
  - Implementar proteção CSRF nos formulários
  - Adicionar rate limiting para envio de mensagens
  - Otimizar queries com eager loading quando necessário
  - Implementar cache para consultas frequentes se aplicável
  - _Requirements: 4.4, 4.5_