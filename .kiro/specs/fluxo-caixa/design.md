# Design Document

## Overview

O sistema de fluxo de caixa será implementado como uma funcionalidade completa que permite visualização de relatórios financeiros com duas modalidades principais: relatório geral (agrupado por cidade e vendedor) e relatório individualizado (por vendedor específico). A arquitetura seguirá os padrões Laravel existentes no projeto, utilizando controladores, modelos, views Blade e respeitando a estrutura de permissões baseada no nível do usuário.

## Architecture

### Componentes Principais

1. **FluxoCaixaController**: Controlador principal que gerencia ambas as funcionalidades
2. **FluxoCaixaService**: Serviço responsável pela lógica de negócio e cálculos
3. **Views Blade**: Interface de usuário responsiva com Tailwind CSS
4. **Modelos Dinâmicos**: Acesso às tabelas de vendas e despesas por cidade
5. **Middleware de Autorização**: Controle de acesso baseado no nível do usuário

### Fluxo de Dados

```
Request → Controller → Service → Database → Service → Controller → View
```

## Components and Interfaces

### 1. FluxoCaixaController

**Responsabilidades:**
- Gerenciar rotas e requisições HTTP
- Validar permissões de usuário
- Coordenar chamadas ao serviço
- Retornar views com dados processados

**Métodos Principais:**
- `index()`: Exibe a página principal do fluxo geral
- `relatorioGeral(Request $request)`: Processa e exibe relatório geral
- `fluxoIndividualizado()`: Exibe página do fluxo individualizado
- `relatorioIndividualizado(Request $request)`: Processa relatório individual

### 2. FluxoCaixaService

**Responsabilidades:**
- Implementar lógica de negócio
- Realizar cálculos financeiros
- Acessar dados de múltiplas tabelas
- Formatar dados para apresentação

**Métodos Principais:**
- `obterDadosFluxoGeral($dataInicio, $dataFim, $usuario)`: Coleta dados do fluxo geral
- `obterDadosFluxoIndividual($dataInicio, $dataFim, $vendedorId)`: Coleta dados individuais
- `calcularVendas($vendas)`: Processa cálculos de vendas
- `calcularRecebimentos($parcelas)`: Processa recebimentos
- `obterDespesas($cidade, $dataInicio, $dataFim)`: Busca despesas do período
- `gerarRelatorios($dados)`: Organiza dados em relatórios

### 3. Estrutura de Views

```
resources/views/fluxo-caixa/
├── index.blade.php              # Página principal do fluxo geral
├── individualizado.blade.php    # Página do fluxo individualizado
├── partials/
│   ├── filtros-periodo.blade.php    # Componente de seleção de datas
│   ├── relatorio-vendas.blade.php   # Componente de relatório de vendas
│   ├── relatorio-recebimentos.blade.php # Componente de recebimentos
│   └── resumo-cidade.blade.php      # Componente de resumo por cidade
```

## Data Models

### Tabelas Utilizadas

1. **users**: Informações do usuário (nível, cidade)
2. **vendas_{cidade}**: Vendas por cidade (dinâmica)
3. **parcelas**: Recebimentos de parcelas
4. **despesas_{cidade}**: Despesas por cidade (dinâmica)
5. **cidades**: Informações das cidades

### Estrutura de Dados Esperada

#### Vendas (vendas_{cidade})
```php
[
    'id_vendedor' => 'integer',
    'id_vendedor_atendente' => 'integer', 
    'data_venda' => 'date',
    'data_estorno' => 'date|null',
    'valor_dinheiro' => 'decimal',
    'valor_pix' => 'decimal',
    'valor_cartao' => 'decimal',
    'valor_crediario' => 'decimal'
]
```

#### Parcelas
```php
[
    'id_vendedor' => 'integer',
    'data_pagamento' => 'date',
    'dinheiro' => 'decimal',
    'pix' => 'decimal',
    'cartao' => 'decimal',
    'metodo' => 'string'
]
```

#### Despesas (despesas_{cidade})
```php
[
    'data' => 'date',
    'tipo' => 'string',
    'status' => 'string',
    'valor' => 'decimal'
]
```

### Modelos Dinâmicos

Para acessar tabelas dinâmicas por cidade, será implementado um padrão de factory:

```php
class TabelaDinamica
{
    public static function vendas($cidade): string
    {
        return "vendas_" . strtolower($cidade);
    }
    
    public static function despesas($cidade): string  
    {
        return "despesas_" . strtolower($cidade);
    }
}
```

## Error Handling

### Validações de Entrada
- Validação de datas (formato, período válido)
- Validação de permissões de usuário
- Validação de existência de vendedor selecionado

### Tratamento de Erros
- Tabelas de cidade inexistentes
- Dados corrompidos ou inconsistentes
- Falhas de conexão com banco de dados
- Permissões insuficientes

### Mensagens de Erro
- Mensagens em português brasileiro
- Feedback visual com SweetAlert2
- Logs detalhados para debugging

## Testing Strategy

### Testes Unitários
- Testes para FluxoCaixaService
- Validação de cálculos financeiros
- Testes de permissões de usuário
- Testes de formatação de dados

### Testes de Integração
- Testes de fluxo completo controller → service → database
- Testes com dados de múltiplas cidades
- Testes de relatórios consolidados

### Testes de Interface
- Testes de responsividade
- Validação de formulários
- Testes de interação com SweetAlert2

### Cenários de Teste

1. **Administrador visualizando todas as cidades**
2. **Vendedor visualizando apenas sua cidade**
3. **Período com vendas estornadas**
4. **Período sem vendas ou recebimentos**
5. **Cálculo de despesas e valor líquido**
6. **Fluxo individualizado por vendedor**

## Interface Design

### Layout Principal
- Header com filtros de período (condicionais por nível)
- Seção de relatórios agrupados por cidade
- Cards individuais por vendedor
- Resumos consolidados
- Cores diferenciadas por método de pagamento

### Elementos Visuais
- **Verde**: Recebimentos e valores positivos
- **Vermelho**: Vendas estornadas e despesas
- **Azul**: Informações neutras e totais
- **Amarelo**: Alertas e observações

### Responsividade
- Design mobile-first
- Tabelas responsivas com scroll horizontal
- Cards empilháveis em dispositivos móveis
- Filtros colapsáveis em telas pequenas

## Security Considerations

### Controle de Acesso
- Middleware para verificação de nível de usuário
- Filtros automáticos por cidade para não-administradores
- Validação de permissões em cada endpoint

### Proteção de Dados
- Sanitização de inputs de data
- Prevenção de SQL injection com Query Builder
- Logs de auditoria para acessos administrativos

### Performance
- Cache de consultas pesadas
- Paginação para grandes volumes de dados
- Índices otimizados nas tabelas de vendas e parcelas