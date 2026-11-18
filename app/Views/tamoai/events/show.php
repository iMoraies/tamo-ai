<?php
    $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $date = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '-';
    $field = static fn ($key, $default = '') => old($key, $financial[$key] ?? $default);
    $eventTitle = $date($event['event_date']) . ' - ' . ($event['client'] ?? 'Evento');
?>
<header class="tamo-page-head finance-page-head">
    <div>
        <span>Fechamento do evento</span>
        <h1><?= esc($eventTitle) ?></h1>
        <p><?= esc($event['address'] ?? '') ?><?= ! empty($event['neighborhood']) ? ' · ' . esc($event['neighborhood']) : '' ?></p>
    </div>
    <div class="form-actions">
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai') ?>">Voltar</a>
        <?php if (! empty($tableReady)): ?>
            <a class="tamo-button quiet" href="<?= base_url('/tamo-ai/eventos/' . $event['id'] . '/fechamento/imprimir') ?>" target="_blank" rel="noopener">Imprimir</a>
            <?php if ($canManageFinance): ?>
                <a class="tamo-button primary" href="<?= base_url('/tamo-ai/eventos/' . $event['id'] . '/comprovantes/novo') ?>">Anexar comprovante</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">
        As tabelas financeiras ainda não foram importadas. Importe o SQL de atualização antes de usar esta tela.
    </div>
<?php else: ?>
    <section class="tamo-stats finance-stats" aria-label="Resumo financeiro">
        <article><span>Status financeiro</span><strong><?= esc($summary['financial_status']) ?></strong></article>
        <article><span>Recebido</span><strong><?= esc($money($summary['income'])) ?></strong></article>
        <article><span>Saídas</span><strong><?= esc($money($summary['out'])) ?></strong></article>
        <article><span>Saldo</span><strong><?= esc($money($summary['final_balance'])) ?></strong></article>
        <article><span>Pagamentos feitos</span><strong><?= esc($money($summary['paid_total'])) ?></strong></article>
        <article><span>Pendências</span><strong><?= esc($summary['pending_payments']) ?></strong></article>
    </section>

    <?php if ($canManageFinance): ?>
        <form class="tamo-form" action="<?= base_url('/tamo-ai/eventos/' . $event['id'] . '/financeiro/atualizar') ?>" method="post">
            <?= csrf_field() ?>
            <section class="form-section">
                <h2>Resumo do fechamento</h2>
                <div class="form-subsection">
                    <h3>Recebimento do contratante</h3>
                    <div class="form-grid compact-grid">
                        <label>Cachê contratado
                            <input name="contracted_amount" value="<?= esc($field('contracted_amount', $event['total_fee'] ?? 0)) ?>">
                        </label>
                        <label>Valor recebido
                            <input name="received_amount" value="<?= esc($field('received_amount')) ?>">
                        </label>
                        <label>Status do recebimento
                            <select name="received_status">
                                <?php foreach ($receivedStatuses as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" <?= $field('received_status', 'pendente') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Data do recebimento
                            <input type="date" name="received_date" value="<?= esc($field('received_date')) ?>">
                        </label>
                        <label>Forma do recebimento
                            <select name="received_method">
                                <option value="">Selecione</option>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?= esc($method) ?>" <?= $field('received_method') === $method ? 'selected' : '' ?>><?= esc($method) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="form-subsection">
                    <h3>Descontos, caixa e divisão</h3>
                    <div class="form-grid compact-grid">
                        <label>Despesas gerais
                            <input name="total_expenses" value="<?= esc($field('total_expenses')) ?>">
                        </label>
                        <label>Total freelancers
                            <input name="total_freelancers" value="<?= esc($field('total_freelancers')) ?>">
                        </label>
                        <label>Valor para caixa
                            <input name="group_cash_amount" value="<?= esc($field('group_cash_amount')) ?>">
                        </label>
                        <label>Ajuste manual
                            <input name="manual_adjustment" value="<?= esc($field('manual_adjustment')) ?>">
                        </label>
                        <label>Valor a dividir
                            <input name="amount_to_split" value="<?= esc($field('amount_to_split')) ?>">
                        </label>
                        <label>Integrantes na divisão
                            <input type="number" min="0" name="split_members_count" value="<?= esc($field('split_members_count', $event['expected_members'] ?? 0)) ?>">
                        </label>
                        <label>Valor por integrante
                            <input name="amount_per_member" value="<?= esc($field('amount_per_member')) ?>">
                        </label>
                    </div>
                </div>

                <div class="form-subsection">
                    <h3>Conferência</h3>
                    <label>Observações do fechamento
                        <textarea name="notes"><?= esc($field('notes')) ?></textarea>
                    </label>
                    <label class="tamo-check">
                        <input type="checkbox" name="close_financial" value="1">
                        Marcar fechamento como conferido
                    </label>
                </div>
                <div class="form-actions">
                    <button class="tamo-button primary" type="submit">Salvar fechamento</button>
                </div>
            </section>
        </form>

        <form class="tamo-form" action="<?= base_url('/tamo-ai/eventos/' . $event['id'] . '/pagamentos/salvar') ?>" method="post">
            <?= csrf_field() ?>
            <section class="form-section">
                <h2>Novo pagamento</h2>
                <div class="form-subsection">
                    <h3>Quem recebe</h3>
                    <div class="form-grid compact-grid">
                        <label>Tipo
                            <select name="person_type">
                                <option value="integrante">Integrante</option>
                                <option value="freelancer">Freelancer</option>
                                <option value="outro">Outro</option>
                            </select>
                        </label>
                        <label>Integrante
                            <select name="member_id">
                                <option value="">Não vincular</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?= esc($member['id']) ?>"><?= esc($member['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Nome
                            <input name="name" placeholder="Nome de quem vai receber" required>
                        </label>
                        <label>Função
                            <input name="role" placeholder="Voz, pandeiro, transporte...">
                        </label>
                    </div>
                </div>
                <div class="form-subsection">
                    <h3>Valores e Pix</h3>
                    <div class="form-grid compact-grid">
                        <label>Valor previsto
                            <input name="expected_amount" placeholder="150,00">
                        </label>
                        <label>Valor pago
                            <input name="paid_amount" placeholder="0,00">
                        </label>
                        <label>Status
                            <select name="status">
                                <?php foreach ($paymentStatuses as $key => $label): ?>
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
                        <label>Pix
                            <input name="pix_key" placeholder="Chave Pix">
                        </label>
                    </div>
                </div>
                <label>Observação
                    <textarea name="notes"></textarea>
                </label>
                <div class="form-actions">
                    <button class="tamo-button primary" type="submit">Adicionar pagamento</button>
                </div>
            </section>
        </form>
    <?php endif; ?>

    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead>
                <tr>
                    <th>Recebedor</th>
                    <th>Tipo</th>
                    <th>Previsto</th>
                    <th>Pago</th>
                    <th>Status</th>
                    <th>Pix</th>
                    <?php if ($canManageFinance): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments === []): ?>
                    <tr><td colspan="<?= $canManageFinance ? 7 : 6 ?>">Nenhum pagamento lançado.</td></tr>
                <?php endif; ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><strong><?= esc($payment['name']) ?></strong><br><small><?= esc($payment['role'] ?: '-') ?></small></td>
                        <td><?= esc($payment['person_type']) ?></td>
                        <td><?= esc($money($payment['expected_amount'])) ?></td>
                        <td><?= esc($money($payment['paid_amount'])) ?></td>
                        <td><span class="mini-status <?= esc($payment['status']) ?>"><?= esc($paymentStatuses[$payment['status']] ?? $payment['status']) ?></span></td>
                        <td><?= esc($payment['pix_key'] ?: '-') ?></td>
                        <?php if ($canManageFinance): ?>
                            <td class="table-actions">
                                <form action="<?= base_url('/tamo-ai/eventos/' . $event['id'] . '/pagamentos/' . $payment['id'] . '/pago') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit">Marcar pago</button>
                                </form>
                                <form action="<?= base_url('/tamo-ai/eventos/' . $event['id'] . '/pagamentos/' . $payment['id'] . '/deletar') ?>" method="post" onsubmit="return confirm('Remover este pagamento?')">
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

    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead>
                <tr>
                    <th>Comprovante</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Forma</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($receipts === []): ?>
                    <tr><td colspan="6">Nenhum comprovante anexado.</td></tr>
                <?php endif; ?>
                <?php foreach ($receipts as $receipt): ?>
                    <tr>
                        <td><strong><?= esc($receipt['title']) ?></strong><br><small><?= esc($receipt['file_original_name']) ?></small></td>
                        <td><?= esc($receipt['type']) ?></td>
                        <td><?= esc($date($receipt['payment_date'])) ?></td>
                        <td><?= esc($money($receipt['amount'])) ?></td>
                        <td><?= esc($receipt['payment_method']) ?></td>
                        <td class="table-actions">
                            <a href="<?= base_url('/tamo-ai/comprovantes/' . $receipt['id'] . '/download') ?>">Baixar</a>
                            <?php if ($canManageFinance): ?>
                                <a href="<?= base_url('/tamo-ai/comprovantes/editar/' . $receipt['id']) ?>">Editar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
