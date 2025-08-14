# Design Document

## Overview

A funcionalidade de verificação de limite será implementada como um módulo dedicado no sistema JJ Calçados, permitindo que administradores e usuários autorizados analisem perfis de clientes e gerenciem limites de crédito. O sistema aproveitará a estrutura existente de modelos (Cliente, User, Pagamento, Parcela, Ticket) e seguirá os padrões arquiteturais do Laravel já estabelecidos no projeto.

## Architecture

### Controller Layer
- **VerificacaoLimiteController**: Novo controller dedicado para gerenciar todas as operações relacionadas à verificação de limite
- Integração com controllers existentes: ClienteController para busca de clientes
- Middleware de autenticação e autorização personalizado

### Service Layer
- **ClienteProfileService**: Serviço para gerar perfis detalhados de compras e pagamentos
- **LimiteManagementService**: Serviço para gerenciar alterações de limite e logs de auditoria
- Reutilização do **PaymentProfileService** existente como base para análises mais detalhadas

### Model Layer
- Utilização dos modelos existentes: Cliente, User, Pagamento, Parcela, Ticket
- Novo modelo **LimiteLog** para auditoria de alterações de limite
- Relacionamentos existentes serão aproveitados

### View Layer
- Nova view dedicada: `resources/views/verificacao-limite/index.blade.php`
- Componentes reutilizáveis para perfis de cliente
- Interface responsiva seguindo padrões Tailwind CSS + Flowbite

## Components and Interfaces

### 1. VerificacaoLimiteController

```php
class VerificacaoLimiteController extends Controller
{
    public function index(Request $request)
    public function buscarClientes(Request $request)
    public function perfilCliente($id)
    public function atualizarLimite(Request $request, $id)
    public function alterarStatus(Request $request, $id)
}
```

### 2. ClienteProfileService

```php
class ClienteProfileService
{
    public function gerarPerfilCompras($clienteId)
    public function gerarPerfilPagamentos($clienteId)
    public function calcularIndicadoresRisco($clienteId)
    public function obterEstatisticasDetalhadas($clienteId)
}
```

### 3. LimiteManagementService

```php
class LimiteManagementService
{
    public function atualizarLimite($clienteId, $novoLimite, $usuarioId)
    public function alterarStatus($clienteId, $novoStatus, $usuarioId)
    public function registrarLog($clienteId, $acao, $valorAnterior, $valorNovo, $usuarioId)
}
```

### 4. Middleware de Autorização

```php
class VerificacaoLimiteMiddleware
{
    public function handle($request, Closure $next)
    // Verifica se user é admin ou tem campo 'limite' = true
}
```

### 5. Modelo LimiteLog

```php
class LimiteLog extends Model
{
    protected $fillable = [
        'cliente_id', 'usuario_id', 'acao', 'valor_anterior', 
        'valor_novo', 'data_alteracao', 'observacoes'
    ];
}
```

## Data Models

### Estrutura da Tabela limite_logs

```sql
CREATE TABLE limite_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    acao ENUM('limite_alterado', 'status_alterado') NOT NULL,
    valor_anterior VARCHAR(255),
    valor_novo VARCHAR(255),
    observacoes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);
```

### Perfil de Compras - Estrutura de Dados

```php
[
    'total_compras' => float,
    'valor_total_gasto' => float,
    'ticket_medio' => float,
    'frequencia_compras' => [
        'total_meses_ativo' => int,
        'compras_por_mes' => float,
        'ultima_compra' => Carbon,
        'primeira_compra' => Carbon
    ],
    'produtos_preferidos' => [
        'marcas' => array,
        'grupos' => array,
        'numeracoes' => array
    ],
    'sazonalidade' => array,
    'evolucao_gastos' => array
]
```

### Perfil de Pagamentos - Estrutura de Dados

```php
[
    'pontualidade' => [
        'percentual_pontual' => float,
        'atraso_medio_dias' => float,
        'maior_atraso_dias' => int
    ],
    'inadimplencia' => [
        'parcelas_em_atraso' => int,
        'valor_em_atraso' => float,
        'percentual_inadimplencia' => float
    ],
    'comportamento_pagamento' => [
        'metodos_preferidos' => array,
        'valor_medio_parcela' => float,
        'prazo_medio_pagamento' => float
    ],
    'risco_calculado' => [
        'score' => int, // 0-100
        'classificacao' => string, // 'baixo', 'medio', 'alto'
        'recomendacao_limite' => float
    ]
]
```

## Error Handling

### Tratamento de Erros por Camada

1. **Controller Level**
   - Validação de entrada com Laravel Validation
   - Try-catch para operações críticas
   - Retorno de respostas JSON para AJAX
   - Redirecionamentos com mensagens de erro para operações síncronas

2. **Service Level**
   - Exceptions customizadas para regras de negócio
   - Logging detalhado de erros
   - Rollback de transações em caso de falha

3. **Database Level**
   - Constraints de integridade referencial
   - Validação de tipos de dados
   - Transações para operações críticas

### Tipos de Erro Específicos

```php
class LimiteVerificationException extends Exception {}
class ClienteNotFoundException extends Exception {}
class UnauthorizedLimiteAccessException extends Exception {}
```

## Testing Strategy

### 1. Unit Tests

- **ClienteProfileService**: Testes para cálculos de perfis
- **LimiteManagementService**: Testes para alterações de limite
- **VerificacaoLimiteController**: Testes para endpoints

### 2. Feature Tests

- **Autorização**: Teste de acesso restrito à funcionalidade
- **Busca de Clientes**: Teste de busca por nome, apelido e CPF
- **Perfis**: Teste de geração de perfis de compras e pagamentos
- **Alterações**: Teste de alteração de limite e status

### 3. Integration Tests

- **Database**: Teste de integridade dos dados
- **Services**: Teste de integração entre serviços
- **Frontend**: Teste de interface com Selenium/Dusk

### 4. Performance Tests

- **Busca**: Teste de performance para busca de clientes
- **Perfis**: Teste de performance para geração de perfis complexos
- **Concorrência**: Teste de alterações simultâneas de limite

## Interface Design

### Layout Principal

```
┌─────────────────────────────────────────────────────────────┐
│ Header: Verificação de Limite                               │
├─────────────────────────────────────────────────────────────┤
│ Busca: [Input: Nome/Apelido/CPF] [Botão: Buscar]          │
├─────────────────────────────────────────────────────────────┤
│ Informações Básicas do Cliente                             │
│ ┌─────────────────┬─────────────────┬─────────────────────┐ │
│ │ Nome: João      │ RG: 123456789   │ CPF: 000.000.000-00│ │
│ │ Apelido: João   │ Renda: R$ 2.000 │ Status: [Toggle]    │ │
│ └─────────────────┴─────────────────┴─────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ Referências Comerciais                                      │
│ • Ref 1: Nome - Telefone                                   │
│ • Ref 2: Nome - Telefone                                   │
│ • Ref 3: Nome - Telefone                                   │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────────┬─────────────────────────────────────┐ │
│ │ Perfil de Compras   │ Perfil de Pagamentos                │ │
│ │ • Total: R$ 5.000   │ • Pontualidade: 85%                 │ │
│ │ • Frequência: 2/mês │ • Atraso médio: 3 dias              │ │
│ │ • Ticket médio: R$  │ • Score de risco: 75/100            │ │
│ │   250               │ • Classificação: Médio               │ │
│ └─────────────────────┴─────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ Limite Atual: R$ 500,00                                    │
│ Novo Limite: [Input: R$ ____] [Botão: Atualizar]          │
└─────────────────────────────────────────────────────────────┘
```

### Componentes Visuais

1. **Busca de Clientes**: Input com autocomplete e resultados em dropdown
2. **Informações Básicas**: Cards organizados com informações essenciais
3. **Toggle Status**: Switch deslizante para ativo/inativo
4. **Perfis**: Cards lado a lado com métricas visuais
5. **Alteração de Limite**: Input numérico com validação em tempo real

### Responsividade

- **Desktop**: Layout em duas colunas para perfis
- **Tablet**: Layout empilhado com cards responsivos
- **Mobile**: Layout vertical com componentes colapsáveis

## Security Considerations

### 1. Autenticação e Autorização
- Middleware personalizado para verificar permissões
- Verificação dupla: admin OU campo 'limite' = true
- Session-based authentication seguindo padrão Laravel

### 2. Validação de Dados
- Sanitização de inputs de busca
- Validação de valores numéricos para limite
- CSRF protection em formulários

### 3. Auditoria
- Log completo de todas as alterações
- Registro de usuário responsável por cada ação
- Timestamp de todas as operações

### 4. Proteção de Dados Sensíveis
- Mascaramento de CPF em logs
- Criptografia de dados sensíveis se necessário
- Rate limiting para buscas

## Performance Optimization

### 1. Database Optimization
- Índices em campos de busca (nome, apelido, cpf)
- Eager loading para relacionamentos
- Query optimization para perfis complexos

### 2. Caching Strategy
- Cache de perfis de cliente por tempo limitado
- Cache de resultados de busca frequentes
- Invalidação de cache em alterações

### 3. Frontend Optimization
- Lazy loading de componentes pesados
- Debounce em campos de busca
- Paginação para resultados extensos

### 4. API Optimization
- Endpoints otimizados para busca AJAX
- Compressão de respostas JSON
- Rate limiting por usuário