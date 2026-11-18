<?php
    $p = static fn ($key, $default = '') => old($key, $event[$key] ?? $default);
    $freelancers = old('freelancer_name') !== null
        ? array_map(static fn ($index) => [
            'name' => old('freelancer_name.' . $index),
            'instrument' => old('freelancer_instrument.' . $index),
            'fee' => old('freelancer_fee.' . $index),
        ], [0, 1, 2])
        : ($event['freelancers'] ?? []);
    $select = static function (string $value, string $label, string $current): string {
        return '<option value="' . esc($value) . '" ' . ($current === $value ? 'selected' : '') . '>' . esc($label) . '</option>';
    };
    $freelancerAt = static fn (int $index, string $key): string => (string) ($freelancers[$index][$key] ?? '');
?>
<header class="tamo-page-head">
    <div>
        <span>Evento</span>
        <h1><?= esc($title) ?></h1>
        <p>Cadastre os dados do pagode, os freelancers e a quantidade de integrantes para calcular a divisão.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai') ?>">Voltar</a>
</header>

<form class="tamo-form" action="<?= base_url($action) ?>" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Dados do evento</h2>
        <div class="form-grid">
            <label>Status
                <select name="status" required>
                    <?php foreach ($statuses as $key => $label): ?><?= $select($key, $label, $p('status', 'confirmado')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Data do evento
                <input type="date" name="event_date" value="<?= esc($p('event_date')) ?>" required>
            </label>
            <label>Horário de início
                <input type="time" name="start_time" value="<?= esc($p('start_time')) ?>" required>
            </label>
            <label>Cliente
                <input name="client" value="<?= esc($p('client')) ?>" required>
            </label>
            <label>Endereço
                <input name="address" value="<?= esc($p('address')) ?>">
            </label>
            <label>Bairro
                <input name="neighborhood" value="<?= esc($p('neighborhood')) ?>">
            </label>
        </div>
    </section>

    <section class="form-section">
        <h2>Pagode e cachê</h2>
        <div class="form-grid">
            <label>Quantidade de sets
                <select name="set_count">
                    <?php foreach ($setOptions as $item): ?><?= $select((string) $item, (string) $item, (string) $p('set_count', 3)) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Duração do intervalo
                <select name="interval_minutes">
                    <?php foreach ($intervalOptions as $item): ?><?= $select((string) $item, $item . ' min', (string) $p('interval_minutes', 30)) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Cachê total
                <input name="total_fee" value="<?= esc($p('total_fee')) ?>" placeholder="1200,00">
            </label>
            <label>Pagamento aos integrantes
                <select name="payment_status">
                    <?php foreach ($paymentStatuses as $key => $label): ?><?= $select($key, $label, $p('payment_status', $p('payment_done') ? 'sim' : 'nao')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Integrantes na divisão
                <input type="number" min="0" name="expected_members" value="<?= esc($p('expected_members', 0)) ?>" placeholder="Ex.: 6">
            </label>
        </div>
    </section>

    <section class="form-section">
        <h2>Freelancers do evento</h2>
        <p class="section-note">Cadastre até 3 freelancers. O sistema desconta esses valores do cachê antes de calcular a divisão.</p>
        <datalist id="freelancer-names">
            <?php foreach ($freelancerNames as $name): ?><option value="<?= esc($name) ?>"><?php endforeach; ?>
        </datalist>
        <div class="freelancer-form-grid">
            <?php for ($index = 0; $index < 3; $index++): ?>
                <div class="freelancer-row">
                    <label>Nome
                        <input name="freelancer_name[]" list="freelancer-names" value="<?= esc($freelancerAt($index, 'name')) ?>">
                    </label>
                    <label>Instrumento
                        <select name="freelancer_instrument[]">
                            <option value="">Selecione</option>
                            <?php foreach ($freelancerInstruments as $item): ?><?= $select($item, $item, $freelancerAt($index, 'instrument')) ?><?php endforeach; ?>
                        </select>
                    </label>
                    <label>Valor
                        <input name="freelancer_fee[]" value="<?= esc($freelancerAt($index, 'fee')) ?>" placeholder="200,00">
                    </label>
                </div>
            <?php endfor; ?>
        </div>
    </section>

    <section class="form-section">
        <h2>Produção do dia</h2>
        <div class="form-grid">
            <label>Cor do dia
                <select name="day_color">
                    <option value="">Selecione</option>
                    <?php foreach ($dayColors as $item): ?><?= $select($item, $item, $p('day_color')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Modelo de croqui
                <select name="sketch_model">
                    <option value="">Selecione</option>
                    <?php foreach ($sketchModels as $item): ?><?= $select($item, $item, $p('sketch_model')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Contratação de som
                <select name="sound_contract">
                    <option value="">Selecione</option>
                    <?php foreach ($soundOptions as $item): ?><?= $select($item, $item, $p('sound_contract')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Violão da vez
                <select name="viola_player">
                    <option value="">Selecione</option>
                    <?php foreach ($guitarPlayers as $item): ?><?= $select($item, $item, $p('viola_player')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Bateria da vez
                <select name="drummer">
                    <option value="">Selecione</option>
                    <?php foreach ($drummers as $item): ?><?= $select($item, $item, $p('drummer')) ?><?php endforeach; ?>
                </select>
            </label>
        </div>
        <label>Observações
            <textarea name="notes" rows="4"><?= esc($p('notes')) ?></textarea>
        </label>
    </section>

    <div class="form-actions">
        <button class="tamo-button primary" type="submit">Salvar evento</button>
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai') ?>">Cancelar</a>
    </div>
</form>
