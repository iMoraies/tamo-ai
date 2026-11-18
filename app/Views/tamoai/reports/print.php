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
        body { font-family: Arial, sans-serif; margin: 32px; color: #1b130d; }
        h1 { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #ddd; padding: 9px; text-align: left; }
        th { color: #7b4a1f; font-size: 12px; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 18px; }
        .box { border: 1px solid #ddd; padding: 12px; }
        @media print { button { display: none; } body { margin: 18mm; } }
    </style>
</head>
<body>
    <button onclick="window.print()">Imprimir</button>
    <h1>Relatório mensal - Grupo Tamo Aí</h1>
    <p><?= esc($month . '/' . $year) ?></p>
    <div class="grid">
        <div class="box">Eventos<br><strong><?= esc($data['stats']['event_count']) ?></strong></div>
        <div class="box">Contratado<br><strong><?= esc($money($data['stats']['contracted'])) ?></strong></div>
        <div class="box">Recebido<br><strong><?= esc($money($data['stats']['received'])) ?></strong></div>
        <div class="box">Pendente<br><strong><?= esc($money($data['stats']['pending'])) ?></strong></div>
    </div>
    <table>
        <thead><tr><th>Data</th><th>Evento</th><th>Contratado</th><th>Recebido</th><th>Dividir</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($data['events'] as $event): $financial = $data['financials'][$event['id']] ?? []; ?>
                <tr><td><?= esc($date($event['event_date'])) ?></td><td><?= esc($event['client']) ?></td><td><?= esc($money($financial['contracted_amount'] ?? $event['total_fee'])) ?></td><td><?= esc($money($financial['received_amount'] ?? 0)) ?></td><td><?= esc($money($financial['amount_to_split'] ?? 0)) ?></td><td><?= esc($financial['received_status'] ?? $event['payment_status']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
