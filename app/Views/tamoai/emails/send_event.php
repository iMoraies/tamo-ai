<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
?>
<header class="tamo-page-head">
    <div>
        <span>Enviar fechamento</span>
        <h1><?= esc($event['client']) ?></h1>
        <p><?= esc($date($event['event_date'])) ?> · Recebido: <?= esc($money($financial['received_amount'] ?? 0)) ?></p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/eventos/' . $event['id']) ?>">Voltar</a>
</header>

<form class="tamo-form" action="<?= base_url('/tamo-ai/emails/enviar-fechamento/' . $event['id'] . '/enviar') ?>" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Destinatários</h2>
        <div class="checkbox-grid">
            <?php foreach ($recipients as $recipient): ?>
                <label class="tamo-check"><input type="checkbox" name="recipient_ids[]" value="<?= esc($recipient['id']) ?>"> <?= esc($recipient['name']) ?> · <?= esc($recipient['email']) ?></label>
            <?php endforeach; ?>
        </div>
        <label>E-mails manuais
            <input name="manual_emails" placeholder="email1@dominio.com; email2@dominio.com">
        </label>
        <label>Modo de envio
            <select name="send_mode">
                <option value="fechamento">Fechamento completo</option>
                <option value="resumo">Resumo simples</option>
            </select>
        </label>
        <div class="form-actions"><button class="tamo-button primary" type="submit">Enviar fechamento</button></div>
    </section>
</form>

<section class="tamo-table-card">
    <table class="tamo-table">
        <thead><tr><th>Pagamento</th><th>Previsto</th><th>Pago</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr><td><?= esc($payment['name']) ?></td><td><?= esc($money($payment['expected_amount'])) ?></td><td><?= esc($money($payment['paid_amount'])) ?></td><td><?= esc($payment['status']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
