# Design Document

## Overview

A funcionalidade de acompanhamento de parcelas será implementada como um módulo independente no sistema JJ Calçados, seguindo os padrões arquiteturais existentes do Laravel. O sistema consistirá em duas páginas principais: consulta por CPF e visualização de parcelas, com navegação fluida entre elas.

## Architecture

### MVC Pattern
- **Controller**: `ParcelaController` - Gerencia as requisições e lógica de negócio
- **Models**: Utiliza models existentes (`Cliente`, `Parcela`, `Autorizado`, `MultaConfiguracao`)
- **Views**: Duas views principais com layout responsivo usando Tailwind CSS

### Route Structure
```php
Route::get('/parcelas', [ParcelaController::class, 'index'])->name('parcelas.index');
Route::post('/parcelas/consultar', [ParcelaController::class, 'consultar'])->name('parcelas.consultar');
```

### Database Integration
- **clientes**: Busca por CPF (mantendo formatação da máscara)
- **parcelas**: Filtro por id_cliente e status "aguardando pagamento"
- **autorizados**: Relacionamento para parcelas de clientes autorizados
- **multa_configuracoes**: Configurações para cálculo de juros e multas

## Components and Interfaces

### ParcelaController

#### Methods
- `index()`: Exibe página de consulta por CPF
- `consultar(Request $request)`: Processa consulta e exibe parcelas

#### Key Responsibilities
- Validação e formatação de CPF
- Busca de cliente na base de dados
- Recuperação e agrupamento de parcelas
- Cálculo de juros e multas
- Preparação de dados para as views

### Service Classes

#### ParcelaCalculoService
```php
class ParcelaCalculoService
{
    public function calcularValorAPagar(Parcela $parcela, MultaConfiguracao $config): float
    public function calcularDiasAtraso(Carbon $dataVencimento): int
    public function calcularMulta(float $valorParcela, float $taxaMulta): float
    public function calcularJuros(float $valorParcela, float $taxaJuros, int $diasAtraso, int $diasCarencia, int $diasCobranca): float
}
```

### Frontend Components

#### CPF Input with Mask
- Máscara automática XXX.XXX.XXX-XX
- Validação em tempo real
- Integração com jQuery Mask Plugin

#### Parcelas Display
- Cards responsivos para cada parcela
- Agrupamento visual por titular/autorizados
- Checkboxes com cálculo automático de totais

#### Total Calculator
- Atualização em tempo real via JavaScript
- Formatação monetária brasileira
- Estado persistente durante navegação

## Data Models

### Existing Models Integration

#### Cliente Model
```php
// Campos utilizados: id, nome, cpf, foto
public function parcelas()
{
    return $this->hasMany(Parcela::class, 'id_cliente');
}
```

#### Parcela Model
```php
// Campos utilizados: ticket, numero, valor_parcela, data_vencimento, status, id_autorizado
public function cliente()
{
    return $this->belongsTo(Cliente::class, 'id_cliente');
}

public function autorizado()
{
    return $this->belongsTo(Autorizado::class, 'id_autorizado');
}
```

#### MultaConfiguracao Model
```php
// Campos utilizados: taxa_multa, taxa_juros, dias_carencia, dias_cobranca
public static function getAtiva()
{
    return self::first(); // Assume configuração única
}
```

### Data Transfer Objects

#### ParcelaDTO
```php
class ParcelaDTO
{
    public string $ticket;
    public string $numero;
    public float $valorParcela;
    public int $diasAtraso;
    public float $valorAPagar;
    public bool $selecionada = false;
    public ?string $nomeAutorizado = null;
}
```

## Error Handling

### Validation Rules
- CPF: required, formato válido, existe na base
- Dados de entrada: sanitização e validação

### Error Scenarios
1. **CPF não encontrado**: Mensagem amigável, manter na página de consulta
2. **Nenhuma parcela encontrada**: Informar que não há débitos pendentes
3. **Erro de cálculo**: Log do erro, exibir valor base da parcela
4. **Erro de conexão**: Mensagem de erro temporário

### Error Messages
```php
'cpf.required' => 'CPF é obrigatório',
'cpf.formato' => 'CPF deve estar no formato XXX.XXX.XXX-XX',
'cliente.nao_encontrado' => 'Cliente não encontrado com este CPF',
'parcelas.nao_encontradas' => 'Nenhuma parcela pendente encontrada'
```

## Testing Strategy

### Unit Tests
- **ParcelaCalculoService**: Testes para todos os métodos de cálculo
- **ParcelaController**: Testes para consulta e validação
- **Models**: Testes de relacionamentos e queries

### Integration Tests
- **Fluxo completo**: CPF → Consulta → Exibição de parcelas
- **Cálculos complexos**: Cenários com diferentes configurações de multa
- **Agrupamento**: Parcelas de titular e autorizados

### Feature Tests
- **Interface de usuário**: Navegação entre páginas
- **JavaScript**: Funcionamento da máscara e cálculo de totais
- **Responsividade**: Testes em diferentes tamanhos de tela

### Test Data Scenarios
1. Cliente sem parcelas pendentes
2. Cliente apenas com parcelas do titular
3. Cliente com parcelas de autorizados
4. Parcelas com diferentes níveis de atraso
5. Parcelas dentro do período de carência

## UI/UX Design

### Page Layout
- **Header**: Mantém padrão do sistema com navegação
- **Main Content**: Container centralizado, responsivo
- **Footer**: Links de navegação e informações do sistema

### Visual Hierarchy
1. **Página de Consulta**: Foco no campo CPF, call-to-action claro
2. **Página de Parcelas**: Informações do cliente em destaque, parcelas organizadas

### Responsive Design
- **Mobile First**: Design otimizado para dispositivos móveis
- **Breakpoints**: sm (640px), md (768px), lg (1024px)
- **Touch Targets**: Botões e checkboxes adequados para touch

### Accessibility
- **Labels**: Todos os campos com labels apropriados
- **Contrast**: Cores com contraste adequado (WCAG AA)
- **Keyboard Navigation**: Navegação completa via teclado
- **Screen Readers**: Estrutura semântica adequada

## Security Considerations

### Input Validation
- Sanitização de CPF antes da consulta
- Validação de formato no frontend e backend
- Proteção contra SQL injection via Eloquent ORM

### Data Protection
- Não exposição de dados sensíveis nas URLs
- Sessão segura para dados temporários
- Logs sem informações pessoais

### Access Control
- Verificação de permissões (se aplicável)
- Rate limiting para consultas
- Validação de origem das requisições