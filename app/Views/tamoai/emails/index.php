<?php $date = static fn ($value): string => $value ? date('d/m/Y H:i', strtotime($value)) : '-'; ?>
<header class="tamo-page-head">
    <div>
        <span>E-mails</span>
        <h1>Destinatários e envios</h1>
        <p>Cadastre quem deve receber fechamentos e acompanhe o histórico de envio.</p>
    </div>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">As tabelas de e-mail ainda não foram importadas.</div>
<?php else: ?>
    <form class="tamo-form" action="<?= base_url('/tamo-ai/emails/salvar') ?>" method="post">
        <?= csrf_field() ?>
        <section class="form-section">
            <h2>Novo destinatário</h2>
            <div class="form-grid">
                <label>Nome <input name="name" required></label>
                <label>E-mail <input type="email" name="email" required></label>
                <label>Tipo
                    <select name="type"><?php foreach ($types as $type): ?><option value="<?= esc($type) ?>"><?= esc(ucfirst($type)) ?></option><?php endforeach; ?></select>
                </label>
                <label>Status
                    <select name="status"><option value="ativo">Ativo</option><option value="inativo">Inativo</option></select>
                </label>
            </div>
            <label>Observação <textarea name="notes"></textarea></label>
            <div class="form-actions"><button class="tamo-button primary" type="submit">Salvar destinatário</button></div>
        </section>
    </form>

    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead><tr><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if ($recipients === []): ?><tr><td colspan="5">Nenhum destinatário cadastrado.</td></tr><?php endif; ?>
                <?php foreach ($recipients as $recipient): ?>
                    <tr>
                        <td><?= esc($recipient['name']) ?></td>
                        <td><?= esc($recipient['email']) ?></td>
                        <td><?= esc($recipient['type']) ?></td>
                        <td><?= esc($recipient['status']) ?></td>
                        <td class="table-actions"><form action="<?= base_url('/tamo-ai/emails/deletar/' . $recipient['id']) ?>" method="post" onsubmit="return confirm('Remover destinatário?')"><?= csrf_field() ?><button type="submit">Remover</button></form></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead><tr><th>Data</th><th>Assunto</th><th>Status</th><th>Erro</th></tr></thead>
            <tbody>
                <?php if ($logs === []): ?><tr><td colspan="4">Nenhum e-mail enviado por aqui ainda.</td></tr><?php endif; ?>
                <?php foreach ($logs as $log): ?>
                    <tr><td><?= esc($date($log['sent_at'] ?? $log['created_at'])) ?></td><td><?= esc($log['subject']) ?></td><td><?= esc($log['status']) ?></td><td><?= esc($log['error_message'] ?: '-') ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
