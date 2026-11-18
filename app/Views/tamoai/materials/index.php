<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
?>
<header class="tamo-page-head">
    <div>
        <span>Materiais</span>
        <h1>Pendências de compra</h1>
        <p>Materiais que o grupo precisa comprar, com preço estimado, link, status e urgência.</p>
    </div>
    <?php if ($canManageFinance): ?>
        <a class="tamo-button primary" href="<?= base_url('/tamo-ai/materiais/novo') ?>">Novo material</a>
    <?php endif; ?>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">A tabela de materiais ainda não foi importada. Rode o SQL de atualização no banco.</div>
<?php else: ?>
    <section class="tamo-stats material-stats" aria-label="Resumo dos materiais">
        <article><span>Pendentes</span><strong><?= esc($summary['pending']) ?></strong></article>
        <article><span>Urgentes</span><strong><?= esc($summary['urgent']) ?></strong></article>
        <article><span>Aprovados</span><strong><?= esc($summary['approved']) ?></strong></article>
        <article><span>Estimativa em aberto</span><strong><?= esc($money($summary['estimated_total'])) ?></strong></article>
    </section>

    <form class="tamo-filters material-filters" method="get" action="<?= base_url('/tamo-ai/materiais') ?>">
        <label>Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Urgência
            <select name="urgencia">
                <option value="">Todas</option>
                <?php foreach ($urgencies as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= ($filters['urgency'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Busca
            <input name="busca" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Nome, categoria ou observação...">
        </label>
        <button class="tamo-button quiet" type="submit">Filtrar</button>
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/materiais') ?>">Limpar</a>
    </form>

    <section class="material-grid">
        <?php if ($materials === []): ?>
            <div class="tamo-empty">
                <strong>Nenhum material encontrado.</strong>
                <p>Cadastre uma pendência de compra ou ajuste os filtros.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($materials as $material): ?>
            <article class="material-card urgency-<?= esc($material['urgency']) ?>">
                <div class="material-card-head">
                    <div>
                        <span><?= esc($categories[$material['category']] ?? $material['category']) ?></span>
                        <h2><?= esc($material['name']) ?></h2>
                    </div>
                    <strong><?= esc($money($material['estimated_price'])) ?></strong>
                </div>

                <div class="material-meta">
                    <span class="mini-status <?= esc($material['status']) ?>"><?= esc($statuses[$material['status']] ?? $material['status']) ?></span>
                    <span class="mini-status <?= esc($material['urgency']) ?>"><?= esc($urgencies[$material['urgency']] ?? $material['urgency']) ?></span>
                    <?php if ((int) ($material['priority'] ?? 0) > 0): ?>
                        <span>Prioridade <?= esc($material['priority']) ?></span>
                    <?php endif; ?>
                    <?php if (! empty($material['needed_until'])): ?>
                        <span>Até <?= esc($date($material['needed_until'])) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (! empty($material['notes'])): ?>
                    <p><?= nl2br(esc($material['notes'])) ?></p>
                <?php endif; ?>

                <div class="material-actions">
                    <?php if (! empty($material['product_url'])): ?>
                        <a class="event-card-button" href="<?= esc($material['product_url']) ?>" target="_blank" rel="noopener">Ver produto</a>
                    <?php endif; ?>
                    <?php if ($canManageFinance): ?>
                        <a class="event-card-button" href="<?= base_url('/tamo-ai/materiais/editar/' . $material['id']) ?>">Editar</a>
                        <form action="<?= base_url('/tamo-ai/materiais/deletar/' . $material['id']) ?>" method="post" onsubmit="return confirm('Remover este material?')">
                            <?= csrf_field() ?>
                            <button class="event-card-button danger" type="submit">Remover</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
