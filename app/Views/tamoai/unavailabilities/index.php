<?php
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
    $sameDay = static fn ($item): bool => ($item['start_date'] ?? '') === ($item['end_date'] ?? '');
    $canChange = static fn ($item): bool => $canManageAll || (int) ($item['user_id'] ?? 0) === (int) $currentUserId;
?>
<header class="tamo-page-head">
    <div>
        <span>Indisponibilidades</span>
        <h1>Calendário dos integrantes</h1>
        <p>Cadastre ausências, bloqueios de agenda e dias em que cada integrante não poderá participar.</p>
    </div>
    <div class="calendar-month-actions" aria-label="Navegação do mês">
        <a class="month-arrow" href="<?= base_url('/tamo-ai/indisponibilidades?mes=' . $prevMonth . '&direcao=anterior') ?>" aria-label="Mês anterior">‹</a>
        <a class="tamo-button quiet" href="<?= base_url('/tamo-ai/indisponibilidades') ?>">Hoje</a>
        <a class="month-arrow" href="<?= base_url('/tamo-ai/indisponibilidades?mes=' . $nextMonth . '&direcao=proximo') ?>" aria-label="Próximo mês">›</a>
    </div>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">A tabela de indisponibilidades ainda não foi importada. Rode o SQL de atualização no banco.</div>
<?php else: ?>
    <form class="tamo-form unavailability-form" action="<?= base_url('/tamo-ai/indisponibilidades/salvar') ?>" method="post">
        <?= csrf_field() ?>
        <section class="form-section">
            <h2>Cadastrar indisponibilidade</h2>
            <?php if (! $canManageAll): ?>
                <p class="section-note">Você está cadastrando uma indisponibilidade para <?= esc(session('tamo_ai_name') ?: 'seu usuário') ?>.</p>
            <?php endif; ?>
            <div class="form-grid unavailability-form-grid">
                <?php if ($canManageAll): ?>
                    <label>Integrante
                        <select name="user_id" required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= esc($user['id']) ?>" <?= (int) old('user_id', $currentUserId) === (int) $user['id'] ? 'selected' : '' ?>>
                                    <?= esc($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <label>Data inicial
                    <input type="date" name="start_date" value="<?= esc(old('start_date')) ?>" required>
                </label>
                <label>Data final
                    <input type="date" name="end_date" value="<?= esc(old('end_date')) ?>" required>
                </label>
                <label>Motivo curto
                    <input name="reason" value="<?= esc(old('reason')) ?>" placeholder="Ex.: viagem, trabalho, família...">
                </label>
                <label class="span-3">Observações
                    <input name="notes" value="<?= esc(old('notes')) ?>" placeholder="Detalhe opcional para o grupo entender o bloqueio.">
                </label>
            </div>
            <div class="form-actions">
                <button class="tamo-button primary" type="submit">Adicionar no calendário</button>
            </div>
        </section>
    </form>

    <section class="calendar-panel calendar-slide-<?= esc($calendarDirection) ?>">
        <div class="calendar-heading">
            <a class="month-arrow subtle" href="<?= base_url('/tamo-ai/indisponibilidades?mes=' . $prevMonth . '&direcao=anterior') ?>" aria-label="Mês anterior">‹</a>
            <div>
                <span>Calendário</span>
                <h2><?= esc($monthLabel) ?></h2>
            </div>
            <a class="month-arrow subtle" href="<?= base_url('/tamo-ai/indisponibilidades?mes=' . $nextMonth . '&direcao=proximo') ?>" aria-label="Próximo mês">›</a>
            <form class="calendar-jump" method="get" action="<?= base_url('/tamo-ai/indisponibilidades') ?>">
                <label>Mês
                    <input type="month" name="mes" value="<?= esc($currentMonth) ?>">
                </label>
                <button class="tamo-button quiet" type="submit">Abrir</button>
            </form>
        </div>

        <div class="calendar-weekdays" aria-hidden="true">
            <span>Dom</span>
            <span>Seg</span>
            <span>Ter</span>
            <span>Qua</span>
            <span>Qui</span>
            <span>Sex</span>
            <span>Sáb</span>
        </div>

        <div class="availability-calendar">
            <?php foreach ($calendar as $week): ?>
                <?php foreach ($week as $day): ?>
                    <article class="calendar-day <?= $day['inMonth'] ? '' : 'outside' ?> <?= $day['today'] ? 'today' : '' ?>">
                        <div class="calendar-day-number"><?= esc($day['number']) ?></div>
                        <div class="calendar-day-items">
                            <?php foreach ($day['items'] as $item): ?>
                                <div class="calendar-pill">
                                    <strong><?= esc($item['user_name'] ?: 'Integrante') ?></strong>
                                    <?php if (! empty($item['reason'])): ?>
                                        <span><?= esc($item['reason']) ?></span>
                                    <?php endif; ?>
                                    <?php if (! $sameDay($item)): ?>
                                        <small><?= esc($date($item['start_date'])) ?> a <?= esc($date($item['end_date'])) ?></small>
                                    <?php endif; ?>
                                    <?php if ($canChange($item)): ?>
                                        <div class="calendar-pill-actions">
                                            <a href="<?= base_url('/tamo-ai/indisponibilidades/editar/' . $item['id']) ?>">Editar</a>
                                            <form action="<?= base_url('/tamo-ai/indisponibilidades/deletar/' . $item['id']) ?>" method="post" onsubmit="return confirm('Remover esta indisponibilidade?')">
                                                <?= csrf_field() ?>
                                                <button type="submit">Remover</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
