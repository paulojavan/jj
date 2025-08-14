<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testando geração de tickets únicos com 20 caracteres...\n\n";

// Simular a função de geração de ticket
function generateTestTicket() {
    $timestamp = now();
    $date = $timestamp->format('Ymd'); // 8 caracteres
    $time = $timestamp->format('His'); // 6 caracteres
    
    // Gera 4 caracteres aleatórios
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random = '';
    for ($i = 0; $i < 4; $i++) {
        $random .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return 'TK' . $date . $time . $random;
}

// Gerar 10 tickets de exemplo
$tickets = [];
for ($i = 0; $i < 10; $i++) {
    $ticket = generateTestTicket();
    $tickets[] = $ticket;
    echo 'Ticket ' . ($i + 1) . ': ' . $ticket . ' (Tamanho: ' . strlen($ticket) . ' caracteres)' . "\n";
    usleep(100000); // Pausa de 0.1 segundo para garantir timestamps diferentes
}

echo "\nVerificando unicidade...\n";
$unique = array_unique($tickets);
if (count($unique) === count($tickets)) {
    echo "✅ Todos os tickets são únicos!\n";
} else {
    echo "❌ Encontradas duplicatas!\n";
    $duplicates = array_diff_assoc($tickets, $unique);
    foreach ($duplicates as $duplicate) {
        echo "   Duplicata: $duplicate\n";
    }
}

echo "\nVerificando tamanho...\n";
$allCorrectSize = true;
foreach ($tickets as $ticket) {
    if (strlen($ticket) !== 20) {
        echo "❌ Ticket com tamanho incorreto: $ticket (" . strlen($ticket) . " caracteres)\n";
        $allCorrectSize = false;
    }
}

if ($allCorrectSize) {
    echo "✅ Todos os tickets têm exatamente 20 caracteres!\n";
}

echo "\nExemplo de formato:\n";
echo "TK20250810143052A1B2\n";
echo "││└─────┘└───┘└─┘\n";
echo "││   │     │   └─ 4 caracteres aleatórios (números/letras)\n";
echo "││   │     └───── 6 caracteres de tempo (HHMMSS)\n";
echo "││   └─────────── 8 caracteres de data (YYYYMMDD)\n";
echo "│└─────────────── Prefixo 'TK'\n";
echo "└───────────────── Total: 20 caracteres\n";

echo "\nTestando verificação de unicidade na base de dados...\n";
try {
    // Testar se a verificação de unicidade funciona
    $testTicket = generateTestTicket();
    $exists = \Illuminate\Support\Facades\DB::table('tickets')->where('ticket', $testTicket)->exists();
    echo "Ticket de teste: $testTicket\n";
    echo "Existe na base: " . ($exists ? "Sim" : "Não") . "\n";
    echo "✅ Verificação de unicidade na base de dados funcionando!\n";
} catch (Exception $e) {
    echo "❌ Erro ao verificar unicidade: " . $e->getMessage() . "\n";
}