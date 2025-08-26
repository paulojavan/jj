# Design Document

## Overview

A funcionalidade de clientes ociosos permite identificar e reativar clientes que não interagem com a loja há mais de 150 dias. O sistema filtra clientes elegíveis (sem restrições SPC) e facilita o envio de mensagens personalizadas via WhatsApp para reengajamento.

## Architecture

### Componentes Principais

1. **Controller**: `ClienteController` - Extensão para incluir método de listagem de clientes ociosos
2. **Model**: `Cliente` - Utilização do modelo existente com campo `ociosidade`
3. **View**: Nova view para listagem e interação com clientes ociosos
4. **Route**: Nova rota para acessar a funcionalidade

### Fluxo de Dados

```
Request → Route → Controller → Model Query → View → User Action → WhatsApp Redirect
```

## Components and Interfaces

### 1. Controller Extension (ClienteController)

**Método: `clientesOciosos()`**
- Responsabilidade: Buscar e filtrar clientes ociosos
- Parâmetros: Request (opcional para filtros)
- Retorno: View com lista de clientes

**Método: `enviarMensagemOcioso($id)`**
- Responsabilidade: Atualizar campo ociosidade e redirecionar para WhatsApp
- Parâmetros: ID do cliente
- Retorno: Redirect para WhatsApp em nova aba

### 2. Query Builder Logic

```php
Cliente::whereRaw('DATEDIFF(CURDATE(), ociosidade) >= 150')
    ->whereDoesntHave('tickets', function($query) {
        $query->where('spc', true);
    })
    ->where('status', '!=', 'inativo')
    ->orderBy('ociosidade', 'asc')
```

### 3. View Structure

**Template**: `resources/views/cliente/ociosos.blade.php`
- Layout base: `layouts.base`
- Componentes: Tabela responsiva, botões de ação, modais de confirmação
- JavaScript: Abertura de nova aba, confirmações

### 4. Route Definition

```php
Route::get('/clientes/ociosos', [ClienteController::class, 'clientesOciosos'])->name('clientes.ociosos');
Route::post('/clientes/{id}/mensagem-ocioso', [ClienteController::class, 'enviarMensagemOcioso'])->name('clientes.mensagem.ocioso');
```

## Data Models

### Cliente Model (Existing)

**Campos Relevantes:**
- `id`: Identificador único
- `nome`: Nome completo do cliente
- `telefone`: Número para WhatsApp
- `ociosidade`: Data da última interação
- `status`: Status do cliente (ativo/inativo)

**Relacionamentos:**
- `tickets()`: HasMany - Compras do cliente
- Filtro: `tickets.spc = false`

### Ticket Model (Existing)

**Campos Relevantes:**
- `id_cliente`: FK para Cliente
- `spc`: Boolean - Indica se está negativado

## Error Handling

### 1. Validações de Entrada

- **Cliente não encontrado**: Retorno 404 com mensagem amigável
- **Telefone inválido**: Validação antes de gerar link WhatsApp
- **Cliente já contatado**: Verificação de data de ociosidade

### 2. Tratamento de Exceções

```php
try {
    // Lógica de negócio
} catch (Exception $e) {
    Log::error('Erro em clientes ociosos: ' . $e->getMessage());
    return back()->with('error', 'Erro ao processar solicitação');
}
```

### 3. Validações de Negócio

- **Período mínimo**: Verificar se passaram 150 dias
- **Status SPC**: Garantir que não há tickets com spc = true
- **Status do cliente**: Verificar se cliente está ativo

## Testing Strategy

### 1. Unit Tests

**ClienteOciosoTest.php**
- `test_lista_clientes_ociosos_com_filtro_correto()`
- `test_exclui_clientes_com_spc_true()`
- `test_calcula_dias_ociosidade_corretamente()`
- `test_atualiza_campo_ociosidade_ao_enviar_mensagem()`

### 2. Feature Tests

**ClienteOciosoFeatureTest.php**
- `test_acesso_pagina_clientes_ociosos()`
- `test_envio_mensagem_whatsapp_atualiza_ociosidade()`
- `test_link_whatsapp_gerado_corretamente()`
- `test_cliente_removido_da_lista_apos_contato()`

### 3. Integration Tests

- Teste de integração com WhatsApp Web
- Teste de atualização de banco de dados
- Teste de filtros combinados (ociosidade + SPC)

## Implementation Details

### 1. Cálculo de Ociosidade

```php
// Usar Carbon para cálculo de diferença de datas
$diasOciosos = Carbon::parse($cliente->ociosidade)->diffInDays(Carbon::now());
```

### 2. Formatação de Telefone

```php
// Limpar e formatar número para WhatsApp
$telefone = preg_replace('/[^0-9]/', '', $cliente->telefone);
if (!str_starts_with($telefone, '55')) {
    $telefone = '55' . $telefone;
}
```

### 3. Geração de Mensagem

```php
// Extrair dois primeiros nomes
$nomes = explode(' ', $cliente->nome);
$doisPrimeirosNomes = implode(' ', array_slice($nomes, 0, 2));

$mensagem = "Bom dia, {$doisPrimeirosNomes}, tudo bem com você? Estamos sentindo sua falta, notamos sua ausência de nossa loja nos últimos tempos. Confira nossas novidades no instagram @joecio_calcados. Você é um cliente especial para nós. Seu crediário continua ativo, esperamos por o seu retorno em uma de nossas lojas, estamos de braços abertos!";
```

### 4. Interface Responsiva

- **Desktop**: Tabela completa com todas as informações
- **Mobile**: Cards colapsáveis com informações essenciais
- **Componentes**: Utilizar Flowbite para consistência visual

### 5. Performance Considerations

- **Paginação**: Implementar para listas grandes
- **Índices**: Garantir índices em `ociosidade` e `spc`
- **Cache**: Considerar cache para consultas frequentes

### 6. Security Measures

- **CSRF Protection**: Tokens em formulários
- **Authorization**: Verificar permissões de usuário
- **Input Sanitization**: Validar dados de entrada
- **Rate Limiting**: Limitar envios de mensagem por usuário