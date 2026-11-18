<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
?>
<div style="font-family:Arial,sans-serif;color:#1b130d;line-height:1.5">
    <h1 style="margin:0 0 8px">Fechamento financeiro - Grupo Tamo Aí</h1>
    <p><strong><?= esc($event['client']) ?></strong><br><?= esc($date($event['event_date'])) ?> · <?= esc($event['address']) ?></p>
    <p>
        Cachê contratado: <strong><?= esc($money($financial['contracted_amount'] ?? $event['total_fee'])) ?></strong><br>
        Valor recebido: <strong><?= esc($money($financial['received_amount'] ?? 0)) ?></strong><br>
        Valor para dividir: <strong><?= esc($money($financial['amount_to_split'] ?? 0)) ?></strong><br>
        Valor por integrante: <strong><?= esc($money($financial['amount_per_member'] ?? 0)) ?></strong>
    </p>
    <h2>Pagamentos</h2>
    <table cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse;width:100%">
        <thead><tr><th align="left">Nome</th><th align="left">Previsto</th><th align="left">Pago</th><th align="left">Status</th></tr></thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr><td><?= esc($payment['name']) ?></td><td><?= esc($money($payment['expected_amount'])) ?></td><td><?= esc($money($payment['paid_amount'])) ?></td><td><?= esc($payment['status']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (! empty($financial['notes'])): ?>
        <p><strong>Observações:</strong><br><?= nl2br(esc($financial['notes'])) ?></p>
    <?php endif; ?>
</div>
