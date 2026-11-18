<?php $date = static fn ($value): string => $value ? date('d/m/Y H:i', strtotime($value)) : '-'; ?>
<header class="tamo-page-head">
    <div>
        <span>Logs</span>
        <h1>Atividade do sistema</h1>
        <p>Registro das ações administrativas e financeiras feitas dentro da área Tamo Aí.</p>
    </div>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">A tabela de logs ainda não foi importada.</div>
<?php else: ?>
    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead><tr><th>Data</th><th>Módulo</th><th>Ação</th><th>Descrição</th><th>IP</th></tr></thead>
            <tbody>
                <?php if ($logs === []): ?><tr><td colspan="5">Nenhum log registrado.</td></tr><?php endif; ?>
                <?php foreach ($logs as $log): ?>
                    <tr><td><?= esc($date($log['created_at'])) ?></td><td><?= esc($log['module']) ?></td><td><?= esc($log['action']) ?></td><td><?= esc($log['description']) ?></td><td><?= esc($log['ip_address']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
