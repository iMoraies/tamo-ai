<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?= esc($title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #1b130d; margin: 32px; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        h2 { margin-top: 26px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #ddd; padding: 9px; text-align: left; }
        th { font-size: 12px; text-transform: uppercase; color: #7b4a1f; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 18px; }
        .box { border: 1px solid #ddd; padding: 12px; }
        .box span { display: block; font-size: 12px; color: #76685d; }
        @media print { button { display: none; } body { margin: 18mm; } }
    </style>
</head>
<body>
    <button onclick="window.print()">Imprimir</button>
    <h1>Fechamento financeiro</h1>
    <p><strong><?= esc($event['client']) ?></strong> · <?= esc($date($event['event_date'])) ?> · <?= esc($event['address']) ?></p>
    <div class="grid">
        <div class="box"><span>Contratado</span><strong><?= esc($money($financial['contracted_amount'] ?? $event['total_fee'])) ?></strong></div>
        <div class="box"><span>Recebido</span><strong><?= esc($money($financial['received_amount'] ?? 0)) ?></strong></div>
        <div class="box"><span>Saídas</span><strong><?= esc($money($summary['out'])) ?></strong></div>
        <div class="box"><span>Saldo</span><strong><?= esc($money($summary['final_balance'])) ?></strong></div>
    </div>
    <h2>Pagamentos</h2>
    <table>
        <thead><tr><th>Nome</th><th>Função</th><th>Previsto</th><th>Pago</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr><td><?= esc($payment['name']) ?></td><td><?= esc($payment['role']) ?></td><td><?= esc($money($payment['expected_amount'])) ?></td><td><?= esc($money($payment['paid_amount'])) ?></td><td><?= esc($payment['status']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Comprovantes</h2>
    <table>
        <thead><tr><th>Título</th><th>Data</th><th>Valor</th><th>Forma</th></tr></thead>
        <tbody>
            <?php foreach ($receipts as $receipt): ?>
                <tr><td><?= esc($receipt['title']) ?></td><td><?= esc($date($receipt['payment_date'])) ?></td><td><?= esc($money($receipt['amount'])) ?></td><td><?= esc($receipt['payment_method']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (! empty($financial['notes'])): ?>
        <h2>Observações</h2>
        <p><?= nl2br(esc($financial['notes'])) ?></p>
    <?php endif; ?>
</body>
</html>
