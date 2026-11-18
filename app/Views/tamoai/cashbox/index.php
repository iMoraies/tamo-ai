<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
    $isAdmin = session('tamo_ai_role') === 'admin';
    $canManage = $canManageFinance ?? $isAdmin;
    $currentQuery = $_GET ?? [];
?>
<header class="tamo-page-head">
    <div>
        <span>Caixa do grupo</span>
        <h1>Controle do caixa</h1>
        <p>Acompanhe o valor acumulado, entradas, saídas e lançamentos ligados aos eventos.</p>
    </div>
    <?php if ($canManage): ?>
        <a class="tamo-button quiet" href="<?= base_url('/tamo-ai/caixa/exportar-csv?' . http_build_query($currentQuery)) ?>">Exportar CSV</a>
    <?php endif; ?>
</header>

<?php if (! $summary['table_ready']): ?>
    <div class="tamo-flash error">
        A tabela do caixa ainda não foi importada. Importe o arquivo <strong>tamo_ai_cash_box_movements.sql</strong> no banco antes de lançar entradas ou saídas.
    </div>
<?php endif; ?>

<section class="tamo-stats cash-stats" aria-label="Resumo do caixa">
    <article>
        <span>Caixa do grupo</span>
        <strong><?= esc($money($summary['total'])) ?></strong>
    </article>
    <article>
        <span>Adiantamento p/ integrantes</span>
        <strong><?= esc($money($summary['advance_balance'])) ?></strong>
    </article>
    <article>
        <span>Entradas manuais</span>
        <strong><?= esc($money($summary['manual_in'])) ?></strong>
    </article>
    <article>
        <span>Saídas manuais</span>
        <strong><?= esc($money($summary['manual_out'])) ?></strong>
    </article>
    <article>
        <span>Saldo do mês</span>
        <strong><?= esc($money($summary['month_balance'])) ?></strong>
    </article>
</section>

<form class="tamo-filters tamo-filters-wide" method="get" action="<?= base_url('/tamo-ai/caixa') ?>">
    <label>Mês
        <input type="month" name="mes" value="<?= esc(($filters['month'] ?? '') ? ($filters['year'] . '-' . $filters['month']) : '') ?>">
    </label>
    <label>Tipo
        <select name="tipo">
            <option value="">Todos</option>
            <option value="entrada" <?= ($filters['type'] ?? '') === 'entrada' ? 'selected' : '' ?>>Entrada</option>
            <option value="saida" <?= ($filters['type'] ?? '') === 'saida' ? 'selected' : '' ?>>Saída</option>
        </select>
    </label>
    <label>Categoria
        <select name="categoria">
            <option value="">Todas</option>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?= esc($key) ?>" <?= ($filters['category'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Evento
        <select name="evento">
            <option value="">Todos</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= esc($event['id']) ?>" <?= (int) ($filters['event_id'] ?? 0) === (int) $event['id'] ? 'selected' : '' ?>><?= esc(date('d/m/Y', strtotime($event['event_date'])) . ' - ' . $event['client']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="tamo-button quiet" type="submit">Filtrar</button>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/caixa') ?>">Limpar</a>
</form>

<?php if ($canManage): ?>
    <form class="tamo-form cash-form" action="<?= base_url('/tamo-ai/caixa/movimentos/salvar') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <section class="form-section">
            <h2>Novo movimento</h2>
            <div class="form-subsection">
                <h3>Dados do lançamento</h3>
                <div class="form-grid compact-grid">
                    <label>Tipo
                        <select name="type">
                            <option value="entrada">Adicionar ao caixa</option>
                            <option value="saida">Tirar do caixa</option>
                        </select>
                    </label>
                    <label>Valor
                        <input name="amount" placeholder="150,00" required>
                    </label>
                    <label>Data
                        <input type="date" name="movement_date" value="<?= esc(date('Y-m-d')) ?>">
                    </label>
                    <label>Categoria
                        <select name="category">
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Forma
                        <select name="payment_method">
                            <?php foreach ($paymentMethods as $method): ?>
                                <option value="<?= esc($method) ?>"><?= esc($method) ?></option>
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
                                <option value="<?= esc($event['id']) ?>"><?= esc(date('d/m/Y', strtotime($event['event_date'])) . ' - ' . $event['client']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Comprovante do Pix/documento
                        <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    </label>
                </div>
            </div>
            <div class="form-subsection">
                <h3>Descrição interna</h3>
                <div class="form-grid two-grid">
                    <label class="cash-description">Descrição
                        <input name="description" placeholder="Ex.: Compra de cabo, sobra de evento, reembolso...">
                    </label>
                    <label class="cash-description">Observação
                        <input name="notes" placeholder="Detalhe interno opcional">
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button class="tamo-button primary" type="submit">Lançar movimento</button>
            </div>
        </section>
    </form>
<?php endif; ?>

<section class="tamo-table-card">
    <table class="tamo-table cash-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Evento</th>
                <th>Descrição</th>
                <th>Forma</th>
                <th>Comprovante</th>
                <th>Valor</th>
                <?php if ($canManage): ?><th></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($movements === []): ?>
                <tr>
                    <td colspan="<?= $canManage ? 9 : 8 ?>">Nenhum movimento lançado ainda.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($movements as $movement): ?>
                <tr>
                    <td><?= esc($date($movement['movement_date'])) ?></td>
                    <td>
                        <span class="cash-type <?= esc($movement['type']) ?>">
                            <?= $movement['type'] === 'saida' ? 'Saída' : 'Entrada' ?>
                        </span>
                    </td>
                    <td><?= esc($categories[$movement['category'] ?? 'outro'] ?? ($movement['category'] ?? '-')) ?></td>
                    <td>
                        <?php $linkedEvent = $eventsById[$movement['event_id'] ?? 0] ?? null; ?>
                        <?php if ($linkedEvent): ?>
                            <a href="<?= base_url('/tamo-ai/eventos/' . $linkedEvent['id']) ?>">
                                <?= esc($date($linkedEvent['event_date']) . ' - ' . $linkedEvent['client']) ?>
                            </a>
                        <?php else: ?>
                            <span class="muted-cell">Sem evento</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= esc($movement['description']) ?>
                        <?php if (! empty($movement['notes'])): ?>
                            <small class="table-note"><?= esc($movement['notes']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($movement['payment_method'] ?? '-') ?></td>
                    <td>
                        <?php if (! empty($movement['receipt_id']) && isset($receiptsById[$movement['receipt_id']])): ?>
                            <a href="<?= base_url('/tamo-ai/comprovantes/' . $movement['receipt_id'] . '/download') ?>">Baixar</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="<?= $movement['type'] === 'saida' ? 'cash-negative' : 'cash-positive' ?>">
                        <?= $movement['type'] === 'saida' ? '-' : '+' ?> <?= esc($money($movement['amount'])) ?>
                    </td>
                    <?php if ($canManage): ?>
                        <td class="table-actions">
                            <a href="<?= base_url('/tamo-ai/caixa/movimentos/editar/' . $movement['id']) ?>">Editar</a>
                            <form action="<?= base_url('/tamo-ai/caixa/movimentos/deletar/' . $movement['id']) ?>" method="post" onsubmit="return confirm('Remover este movimento do caixa?')">
                                <?= csrf_field() ?>
                                <button type="submit">Remover</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
