<?php
    $value = static fn ($key, $default = '') => old($key, $receipt[$key] ?? $default);
?>
<header class="tamo-page-head">
    <div>
        <span>Comprovante</span>
        <h1><?= esc($title) ?></h1>
        <p>Anexe PDF, JPG, PNG ou WEBP. Os arquivos ficam fora da pasta pública do site.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/comprovantes') ?>">Voltar</a>
</header>

<form class="tamo-form" action="<?= base_url($action) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Dados do comprovante</h2>
        <div class="form-grid">
            <label>Evento
                <select name="event_id">
                    <option value="">Sem evento vinculado</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?= esc($event['id']) ?>" <?= (int) $value('event_id', $eventId ?? 0) === (int) $event['id'] ? 'selected' : '' ?>><?= esc(date('d/m/Y', strtotime($event['event_date'])) . ' - ' . $event['client']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Título <input name="title" value="<?= esc($value('title')) ?>" required></label>
            <label>Tipo
                <select name="type"><?php foreach ($types as $key => $label): ?><option value="<?= esc($key) ?>" <?= $value('type', 'outros') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select>
            </label>
            <label>Categoria
                <select name="category"><?php foreach ($categories as $key => $label): ?><option value="<?= esc($key) ?>" <?= $value('category', 'outro') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select>
            </label>
            <label>Valor <input name="amount" value="<?= esc($value('amount')) ?>" required></label>
            <label>Data <input type="date" name="payment_date" value="<?= esc($value('payment_date', date('Y-m-d'))) ?>"></label>
            <label>Forma
                <select name="payment_method"><?php foreach ($paymentMethods as $method): ?><option value="<?= esc($method) ?>" <?= $value('payment_method', 'Pix') === $method ? 'selected' : '' ?>><?= esc($method) ?></option><?php endforeach; ?></select>
            </label>
            <label>Quem pagou <input name="paid_by" value="<?= esc($value('paid_by')) ?>"></label>
            <label>Quem recebeu <input name="received_by" value="<?= esc($value('received_by')) ?>"></label>
            <label>Integrante
                <select name="member_id"><option value="">Não vincular</option><?php foreach ($members as $member): ?><option value="<?= esc($member['id']) ?>" <?= (int) $value('member_id') === (int) $member['id'] ? 'selected' : '' ?>><?= esc($member['name']) ?></option><?php endforeach; ?></select>
            </label>
            <label>Freelancer
                <select name="freelancer_id"><option value="">Não vincular</option><?php foreach ($freelancers as $freelancer): ?><option value="<?= esc($freelancer['id']) ?>" <?= (int) $value('freelancer_id') === (int) $freelancer['id'] ? 'selected' : '' ?>><?= esc($freelancer['name']) ?></option><?php endforeach; ?></select>
            </label>
            <label>Arquivo
                <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png,.webp" <?= empty($receipt) ? 'required' : '' ?>>
            </label>
        </div>
        <label>Observações <textarea name="notes"><?= esc($value('notes')) ?></textarea></label>
        <div class="form-actions">
            <button class="tamo-button primary" type="submit">Salvar comprovante</button>
            <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/comprovantes') ?>">Cancelar</a>
        </div>
    </section>
</form>
