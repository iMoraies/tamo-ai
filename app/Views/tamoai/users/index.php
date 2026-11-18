<header class="tamo-page-head">
    <div>
        <span>Acesso</span>
        <h1>Usuários da agenda</h1>
        <p>Controle quem pode ver e editar as informações do grupo.</p>
    </div>
    <a class="tamo-button primary" href="<?= base_url('/tamo-ai/usuarios/novo') ?>">Novo usuário</a>
</header>

<section class="tamo-table-card">
    <table class="tamo-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Instrumento</th>
                <th>Pix</th>
                <th>Perfil</th>
                <th>Status</th>
                <th>Último acesso</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= esc($user['name']) ?></td>
                    <td><?= esc($user['email']) ?></td>
                    <td><?= esc($user['main_instrument'] ?: '-') ?></td>
                    <td><?= esc($user['pix_key'] ?: '-') ?></td>
                    <td><?= esc($user['role'] === 'admin' ? 'Admin' : 'Integrante') ?></td>
                    <td><?= $user['active'] ? 'Ativo' : 'Inativo' ?></td>
                    <td><?= esc($user['last_login_at'] ?: '-') ?></td>
                    <td class="table-actions">
                        <a href="<?= base_url('/tamo-ai/usuarios/editar/' . $user['id']) ?>">Editar</a>
                        <form action="<?= base_url('/tamo-ai/usuarios/deletar/' . $user['id']) ?>" method="post" onsubmit="return confirm('Remover este usuário?')">
                            <?= csrf_field() ?>
                            <button type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
