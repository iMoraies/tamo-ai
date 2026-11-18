<?php
    $value = static fn ($key, $default = '') => old($key, $material[$key] ?? $default);
?>
<header class="tamo-page-head">
    <div>
        <span>Materiais</span>
        <h1><?= esc($title) ?></h1>
        <p>Registre pendências de compra com link do produto, preço estimado e urgência.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/materiais') ?>">Voltar</a>
</header>

<form class="tamo-form" action="<?= base_url($action) ?>" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Dados do material</h2>
        <div class="form-subsection">
            <h3>Identificação</h3>
            <div class="form-grid compact-grid">
                <label>Nome
                    <input name="name" value="<?= esc($value('name')) ?>" placeholder="Ex.: Cabo P10, pedestal, microfone..." required>
                </label>
                <label>Categoria
                    <select name="category">
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= $value('category', 'outro') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Preço estimado
                    <input name="estimated_price" value="<?= esc($value('estimated_price')) ?>" placeholder="120,00">
                </label>
                <label>Prioridade
                    <input type="number" min="0" name="priority" value="<?= esc($value('priority', 0)) ?>">
                </label>
                <label>Prazo desejado
                    <input type="date" name="needed_until" value="<?= esc($value('needed_until')) ?>">
                </label>
            </div>
        </div>

        <div class="form-subsection">
            <h3>Status da compra</h3>
            <div class="form-grid compact-grid">
                <label>Status
                    <select name="status">
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= $value('status', 'pendente') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Urgência
                    <select name="urgency">
                        <?php foreach ($urgencies as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= $value('urgency', 'normal') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="span-3">Link do produto online
                    <input type="url" name="product_url" value="<?= esc($value('product_url')) ?>" placeholder="https://...">
                </label>
            </div>
        </div>

        <div class="form-subsection">
            <h3>Observações</h3>
            <label>Detalhes
                <textarea name="notes" placeholder="Modelo ideal, motivo da compra, quantidade, observações..."><?= esc($value('notes')) ?></textarea>
            </label>
        </div>

        <div class="form-actions">
            <button class="tamo-button primary" type="submit">Salvar material</button>
            <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/materiais') ?>">Cancelar</a>
        </div>
    </section>
</form>
