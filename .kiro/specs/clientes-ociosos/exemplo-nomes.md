# Exemplos de Extração de Nomes

## Lógica Implementada

A funcionalidade agora verifica se o segundo nome é uma conjunção. Se for, usa apenas o primeiro nome na mensagem.

### Conjunções Reconhecidas:
- da, de, do, das, dos
- e
- del, della, di, du
- van, von
- la, le, el

## Exemplos de Funcionamento:

### ✅ Casos com Conjunção (usa apenas primeiro nome):
- **DHEISIELLE DA SILVA SIQUEIRA** → `DHEISIELLE`
- **Maria de Souza Lima** → `Maria`
- **José do Carmo Silva** → `José`
- **Ana das Neves Santos** → `Ana`
- **Pedro dos Santos Silva** → `Pedro`
- **Carlos e Silva Oliveira** → `Carlos`

### ✅ Casos sem Conjunção (usa dois primeiros nomes):
- **João Silva Santos** → `João Silva`
- **Ana Paula Oliveira** → `Ana Paula`
- **Carlos Eduardo Lima** → `Carlos Eduardo`

### ✅ Casos especiais:
- **João** (nome único) → `João`
- **Maria José da Silva** → `Maria José` (segundo nome não é conjunção)

## Mensagem Final:

**Exemplo com conjunção:**
> "Bom dia, DHEISIELLE, tudo bem com você? Estamos sentindo sua falta..."

**Exemplo sem conjunção:**
> "Bom dia, João Silva, tudo bem com você? Estamos sentindo sua falta..."

Esta implementação torna as mensagens mais naturais e personalizadas para cada cliente.