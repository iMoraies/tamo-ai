<?php
    $value = static fn ($key, $default = '') => old($key, $item[$key] ?? $default);
?>
<header class="tamo-page-head">
    <div>
        <span>Indisponibilidades</span>
        <h1><?= esc($title) ?></h1>
        <p>Ajuste o período e as observações da indisponibilidade cadastrada.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/indisponibilidades?mes=' . substr((string) ($item['start_date'] ?? date('Y-m-d')), 0, 7)) ?>">Voltar</a>
</header>

<form class="tamo-form" action="<?= base_url($action) ?>" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Dados da indisponibilidade</h2>
        <div class="form-grid compact-grid">
            <?php if ($canManageAll): ?>
                <label>Integrante
                    <select name="user_id" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= esc($user['id']) ?>" <?= (int) $value('user_id') === (int) $user['id'] ? 'selected' : '' ?>>
                                <?= esc($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <label>Data inicial
                <input type="date" name="start_date" value="<?= esc($value('start_date')) ?>" required>
            </label>
            <label>Data final
                <input type="date" name="end_date" value="<?= esc($value('end_date')) ?>" required>
            </label>
            <label>Motivo curto
                <input name="reason" value="<?= esc($value('reason')) ?>" placeholder="Ex.: viagem, trabalho, família...">
            </label>
            <label class="span-3">Observações
                <textarea name="notes" placeholder="Detalhe opcional para o grupo entender o bloqueio."><?= esc($value('notes')) ?></textarea>
            </label>
        </div>
        <div class="form-actions">
            <button class="tamo-button primary" type="submit">Salvar alteração</button>
            <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/indisponibilidades?mes=' . substr((string) ($item['start_date'] ?? date('Y-m-d')), 0, 7)) ?>">Cancelar</a>
        </div>
    </section>
</form>
