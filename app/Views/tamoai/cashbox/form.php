<?php
    $value = static fn ($key, $default = '') => old($key, $movement[$key] ?? $default);
?>
<header class="tamo-page-head">
    <div>
        <span>Caixa do grupo</span>
        <h1>Editar movimento</h1>
        <p>Corrija descrição, valor, data, vínculo com evento ou comprovante do lançamento.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/caixa') ?>">Voltar</a>
</header>

<form class="tamo-form cash-form" action="<?= base_url('/tamo-ai/caixa/movimentos/atualizar/' . $movement['id']) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Dados do movimento</h2>
        <div class="form-subsection">
            <h3>Lançamento</h3>
            <div class="form-grid compact-grid">
                <label>Tipo
                    <select name="type">
                        <option value="entrada" <?= $value('type') === 'entrada' ? 'selected' : '' ?>>Adicionar ao caixa</option>
                        <option value="saida" <?= $value('type') === 'saida' ? 'selected' : '' ?>>Tirar do caixa</option>
                    </select>
                </label>
                <label>Valor
                    <input name="amount" value="<?= esc($value('amount')) ?>" required>
                </label>
                <label>Data
                    <input type="date" name="movement_date" value="<?= esc($value('movement_date', date('Y-m-d'))) ?>">
                </label>
                <label>Categoria
                    <select name="category">
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= $value('category', 'outro') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Forma
                    <select name="payment_method">
                        <?php foreach ($paymentMethods as $method): ?>
                            <option value="<?= esc($method) ?>" <?= $value('payment_method', 'Pix') === $method ? 'selected' : '' ?>><?= esc($method) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="form-subsection">
            <h3>Vínculo e comprovante</h3>
            <div class="form-grid two-grid">
                <label>Evento
                    <select name="event_id">
                        <option value="">Sem evento</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= esc($event['id']) ?>" <?= (int) $value('event_id') === (int) $event['id'] ? 'selected' : '' ?>>
                                <?= esc(date('d/m/Y', strtotime($event['event_date'])) . ' - ' . $event['client']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Trocar/adicionar comprovante
                    <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                </label>
            </div>
            <?php if ($receipt): ?>
                <p class="section-note">
                    Comprovante atual:
                    <a href="<?= base_url('/tamo-ai/comprovantes/' . $receipt['id'] . '/download') ?>"><?= esc($receipt['file_original_name'] ?: 'Baixar arquivo') ?></a>
                </p>
            <?php endif; ?>
        </div>

        <div class="form-subsection">
            <h3>Descrição interna</h3>
            <div class="form-grid two-grid">
                <label>Descrição
                    <input name="description" value="<?= esc($value('description')) ?>" placeholder="Ex.: Compra de cabo, sobra de evento, reembolso...">
                </label>
                <label>Observação
                    <input name="notes" value="<?= esc($value('notes')) ?>" placeholder="Detalhe interno opcional">
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button class="tamo-button primary" type="submit">Salvar alterações</button>
            <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/caixa') ?>">Cancelar</a>
        </div>
    </section>
</form>
