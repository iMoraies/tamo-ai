<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
    $time = static fn ($value): string => $value ? substr((string) $value, 0, 5) : '-';
    $role = (string) session('tamo_ai_role');
    $isAdmin = $role === 'admin';
    $canFinance = in_array($role, ['admin', 'financeiro'], true);
?>
<header class="tamo-page-head">
    <div>
        <span>Agenda do grupo</span>
        <h1>Próximos pagodes</h1>
        <p>Eventos, freelancers e divisão em uma visão limpa para todo mundo se organizar.</p>
    </div>
    <?php if ($isAdmin): ?>
        <a class="tamo-button primary" href="<?= base_url('/tamo-ai/eventos/novo') ?>">Novo evento</a>
    <?php endif; ?>
</header>

<section class="tamo-stats" aria-label="Resumo do mês">
    <article>
        <span>Eventos no mês</span>
        <strong><?= esc($stats['month_count']) ?></strong>
    </article>
    <article>
        <span>Confirmados</span>
        <strong><?= esc($stats['confirmed']) ?></strong>
    </article>
    <article>
        <span>Caixa do grupo</span>
        <strong><?= esc($money($stats['cash_box'])) ?></strong>
    </article>
</section>

<form class="tamo-filters" method="get" action="<?= base_url('/tamo-ai') ?>">
    <label>
        Status
        <select name="status">
            <option value="">Todos</option>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= esc($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Mês
        <input type="month" name="mes" value="<?= esc($month) ?>">
    </label>
    <label>
        Busca
        <input name="busca" value="<?= esc($search) ?>" placeholder="Cliente, bairro, integrante...">
    </label>
    <button class="tamo-button quiet" type="submit">Filtrar</button>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai') ?>">Limpar</a>
</form>

<section class="tamo-event-list">
    <?php if ($events === []): ?>
        <div class="tamo-empty">
            <strong>Nenhum evento encontrado.</strong>
            <p>Adicione o primeiro evento ou ajuste os filtros.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($events as $event): ?>
        <article class="tamo-event-card status-<?= esc($event['status']) ?>">
            <div class="event-summary">
                <div class="event-date">
                    <span><?= esc($event['weekday'] ?: '-') ?></span>
                    <strong><?= esc($date($event['event_date'])) ?></strong>
                    <small><?= esc($time($event['start_time'])) ?></small>
                </div>

                <div class="event-main">
                    <h2><?= esc($event['client']) ?></h2>
                    <p><?= esc($event['address']) ?><?= $event['neighborhood'] ? ' · ' . esc($event['neighborhood']) : '' ?></p>
                    <div class="event-tags">
                        <span><?= esc($event['set_count']) ?> sets</span>
                        <span><?= esc($event['interval_minutes']) ?> min intervalo</span>
                        <span><?= esc($money($event['total_fee'])) ?></span>
                    </div>
                </div>

                <div class="event-card-actions">
                    <?php if ($isAdmin): ?>
                        <form class="event-status-form" action="<?= base_url('/tamo-ai/eventos/status/' . $event['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <label>
                                <span>Status</span>
                                <select name="status" class="status-pill status-select" onchange="this.form.submit()" aria-label="Alterar status de <?= esc($event['client']) ?>">
                                    <?php foreach ($statuses as $key => $label): ?>
                                        <option value="<?= esc($key) ?>" <?= $event['status'] === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </form>
                    <?php else: ?>
                        <span class="status-pill"><?= esc($event['status_label']) ?></span>
                    <?php endif; ?>
                    <a class="event-card-button" href="<?= base_url('/tamo-ai/eventos/' . $event['id']) ?>">Financeiro</a>
                    <?php if ($isAdmin): ?>
                        <a class="event-card-button" href="<?= base_url('/tamo-ai/eventos/editar/' . $event['id']) ?>">Editar</a>
                        <form action="<?= base_url('/tamo-ai/eventos/deletar/' . $event['id']) ?>" method="post" onsubmit="return confirm('Remover este evento?')">
                            <?= csrf_field() ?>
                            <button class="event-card-button danger" type="submit">Excluir</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="event-info-panel">
                <h3>Informações:</h3>
                <div class="event-info-grid">
                    <span><small>Som:</small><?= esc($event['sound_contract'] ?: '-') ?></span>
                    <span><small>Violão:</small><?= esc($event['viola_player'] ?: '-') ?></span>
                    <span><small>Bateria:</small><?= esc($event['drummer'] ?: '-') ?></span>
                    <span><small>Cor do dia:</small><?= esc($event['day_color'] ?: '-') ?></span>
                </div>
            </div>

            <div class="event-money-panel">
                <h3>Contas do evento:</h3>
                <div class="event-money-grid">
                    <span><small>Custo total de freelancers:</small><?= esc($money($event['division']['freelancers_total'])) ?></span>
                <?php if ($event['status'] === 'ultimo-do-mes'): ?>
                    <span class="cash-box"><small>Vai para o caixa:</small><?= esc($money($event['division']['cash_box'])) ?></span>
                    <span><small>Por integrante:</small><?= esc($money(0)) ?></span>
                <?php else: ?>
                    <span><small>Para dividir:</small><?= esc($money($event['division']['net_total'])) ?></span>
                    <span><small>Por integrante:</small><?= esc($money($event['division']['per_member'])) ?></span>
                <?php endif; ?>
                    <span><small>Integrantes:</small><?= esc($event['division']['split_members']) ?> na divisão</span>
                    <span><small>Pagamento:</small><?= esc($event['payment_label']) ?></span>
                </div>
            </div>

            <?php if ($event['freelancers'] !== []): ?>
                <div class="freelancer-strip">
                    <?php foreach ($event['freelancers'] as $freelancer): ?>
                        <span><?= esc($freelancer['name'] ?: 'Freelancer') ?> · <?= esc($freelancer['instrument'] ?: 'Função') ?> · <?= esc($money($freelancer['fee'])) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
