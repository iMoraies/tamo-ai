<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
?>
<header class="tamo-page-head">
    <div>
        <span>Comprovantes</span>
        <h1>Arquivos financeiros</h1>
        <p>Comprovantes de recebimentos, pagamentos, despesas e movimentações do grupo.</p>
    </div>
    <?php if ($canManageFinance ?? false): ?>
        <a class="tamo-button primary" href="<?= base_url('/tamo-ai/comprovantes/novo') ?>">Novo comprovante</a>
    <?php endif; ?>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">A tabela de comprovantes ainda não foi importada.</div>
<?php else: ?>
    <form class="tamo-filters tamo-filters-wide" method="get" action="<?= base_url('/tamo-ai/comprovantes') ?>">
        <label>Mês <input type="month" name="mes" value="<?= esc(($filters['month'] ?? '') ? ($filters['year'] . '-' . $filters['month']) : '') ?>"></label>
        <label>Tipo
            <select name="tipo"><option value="">Todos</option><?php foreach ($types as $key => $label): ?><option value="<?= esc($key) ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select>
        </label>
        <label>Categoria
            <select name="categoria"><option value="">Todas</option><?php foreach ($categories as $key => $label): ?><option value="<?= esc($key) ?>" <?= ($filters['category'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select>
        </label>
        <label>Pessoa <input name="pessoa" value="<?= esc($filters['person'] ?? '') ?>" placeholder="Pagou ou recebeu"></label>
        <button class="tamo-button quiet" type="submit">Filtrar</button>
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/comprovantes') ?>">Limpar</a>
    </form>

    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead><tr><th>Comprovante</th><th>Evento</th><th>Tipo</th><th>Data</th><th>Valor</th><th>Forma</th><th></th></tr></thead>
            <tbody>
                <?php if ($receipts === []): ?><tr><td colspan="7">Nenhum comprovante encontrado.</td></tr><?php endif; ?>
                <?php foreach ($receipts as $receipt): ?>
                    <tr>
                        <td><strong><?= esc($receipt['title']) ?></strong><br><small><?= esc($receipt['file_original_name']) ?></small></td>
                        <td><?= esc($receipt['event'] ? $date($receipt['event']['event_date']) . ' - ' . $receipt['event']['client'] : '-') ?></td>
                        <td><?= esc($types[$receipt['type']] ?? $receipt['type']) ?></td>
                        <td><?= esc($date($receipt['payment_date'])) ?></td>
                        <td><?= esc($money($receipt['amount'])) ?></td>
                        <td><?= esc($receipt['payment_method']) ?></td>
                        <td class="table-actions">
                            <a href="<?= base_url('/tamo-ai/comprovantes/' . $receipt['id'] . '/download') ?>">Baixar</a>
                            <?php if ($canManageFinance ?? false): ?>
                                <a href="<?= base_url('/tamo-ai/comprovantes/editar/' . $receipt['id']) ?>">Editar</a>
                                <form action="<?= base_url('/tamo-ai/comprovantes/deletar/' . $receipt['id']) ?>" method="post" onsubmit="return confirm('Remover este comprovante?')"><?= csrf_field() ?><button type="submit">Remover</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
