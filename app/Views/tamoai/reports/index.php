<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
    $query = http_build_query(['ano' => $year, 'mes' => $month]);
?>
<header class="tamo-page-head">
    <div>
        <span>Relatórios</span>
        <h1>Resumo mensal</h1>
        <p>Visão financeira consolidada dos eventos, pagamentos, comprovantes e caixa.</p>
    </div>
    <div class="form-actions">
        <a class="tamo-button quiet" href="<?= base_url('/tamo-ai/relatorios/mensal/exportar-csv?' . $query) ?>">Exportar CSV</a>
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/relatorios/mensal/imprimir?' . $query) ?>" target="_blank" rel="noopener">Imprimir</a>
    </div>
</header>

<form class="tamo-filters" method="get" action="<?= base_url('/tamo-ai/relatorios') ?>">
    <label>Mês <input type="month" name="mes" value="<?= esc($year . '-' . $month) ?>"></label>
    <button class="tamo-button quiet" type="submit">Aplicar</button>
</form>

<section class="tamo-stats finance-stats">
    <article><span>Eventos</span><strong><?= esc($data['stats']['event_count']) ?></strong></article>
    <article><span>Contratado</span><strong><?= esc($money($data['stats']['contracted'])) ?></strong></article>
    <article><span>Recebido</span><strong><?= esc($money($data['stats']['received'])) ?></strong></article>
    <article><span>Pendente</span><strong><?= esc($money($data['stats']['pending'])) ?></strong></article>
    <article><span>Despesas</span><strong><?= esc($money($data['stats']['expenses'])) ?></strong></article>
    <article><span>Caixa</span><strong><?= esc($money($data['stats']['cash_balance'])) ?></strong></article>
</section>

<section class="tamo-table-card">
    <table class="tamo-table">
        <thead><tr><th>Data</th><th>Evento</th><th>Contratado</th><th>Recebido</th><th>Freelas</th><th>Dividir</th><th>Status</th></tr></thead>
        <tbody>
            <?php if ($data['events'] === []): ?><tr><td colspan="7">Nenhum evento no mês.</td></tr><?php endif; ?>
            <?php foreach ($data['events'] as $event): $financial = $data['financials'][$event['id']] ?? []; ?>
                <tr>
                    <td><?= esc($date($event['event_date'])) ?></td>
                    <td><a href="<?= base_url('/tamo-ai/eventos/' . $event['id']) ?>"><?= esc($event['client']) ?></a></td>
                    <td><?= esc($money($financial['contracted_amount'] ?? $event['total_fee'])) ?></td>
                    <td><?= esc($money($financial['received_amount'] ?? 0)) ?></td>
                    <td><?= esc($money($financial['total_freelancers'] ?? 0)) ?></td>
                    <td><?= esc($money($financial['amount_to_split'] ?? 0)) ?></td>
                    <td><?= esc($financial['received_status'] ?? $event['payment_status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
