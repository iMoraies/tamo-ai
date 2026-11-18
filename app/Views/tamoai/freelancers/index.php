<?php $money = static fn ($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.'); ?>
<header class="tamo-page-head">
    <div>
        <span>Freelancers</span>
        <h1>Base de apoio</h1>
        <p>Cadastre músicos e apoios recorrentes para consultar Pix, contato e valor padrão.</p>
    </div>
</header>

<?php if (empty($tableReady)): ?>
    <div class="tamo-flash error">A tabela de freelancers ainda não foi importada.</div>
<?php else: ?>
    <form class="tamo-form" action="<?= base_url('/tamo-ai/freelancers/salvar') ?>" method="post">
        <?= csrf_field() ?>
        <section class="form-section">
            <h2>Novo freelancer</h2>
            <div class="form-grid">
                <label>Nome <input name="name" required></label>
                <label>Função
                    <select name="role"><?php foreach ($roles as $role): ?><option value="<?= esc($role) ?>"><?= esc($role) ?></option><?php endforeach; ?></select>
                </label>
                <label>Telefone <input name="phone"></label>
                <label>E-mail <input type="email" name="email"></label>
                <label>Pix <input name="pix_key"></label>
                <label>Valor padrão <input name="default_amount" placeholder="150,00"></label>
            </div>
            <label>Observação <textarea name="notes"></textarea></label>
            <div class="form-actions"><button class="tamo-button primary" type="submit">Cadastrar freelancer</button></div>
        </section>
    </form>

    <section class="tamo-table-card">
        <table class="tamo-table">
            <thead><tr><th>Freelancers cadastrados</th></tr></thead>
            <tbody>
                <?php if ($freelancers === []): ?><tr><td>Nenhum freelancer cadastrado.</td></tr><?php endif; ?>
                <?php foreach ($freelancers as $freelancer): ?>
                    <tr>
                        <td>
                            <form class="inline-edit-grid" action="<?= base_url('/tamo-ai/freelancers/atualizar/' . $freelancer['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <label>Nome <input name="name" value="<?= esc($freelancer['name']) ?>"></label>
                                <label>Função <input name="role" value="<?= esc($freelancer['role']) ?>"></label>
                                <label>Telefone <input name="phone" value="<?= esc($freelancer['phone']) ?>"></label>
                                <label>E-mail <input name="email" value="<?= esc($freelancer['email']) ?>"></label>
                                <label>Pix <input name="pix_key" value="<?= esc($freelancer['pix_key']) ?>"></label>
                                <label>Valor <input name="default_amount" value="<?= esc($freelancer['default_amount']) ?>" aria-label="Valor padrão <?= esc($money($freelancer['default_amount'])) ?>"></label>
                                <label>Status
                                    <select name="status">
                                        <option value="ativo" <?= $freelancer['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= $freelancer['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </label>
                                <div class="table-actions">
                                    <button type="submit">Salvar</button>
                                </div>
                            </form>
                            <form class="inline-delete-form table-actions" action="<?= base_url('/tamo-ai/freelancers/deletar/' . $freelancer['id']) ?>" method="post" onsubmit="return confirm('Remover este freelancer?')">
                                <?= csrf_field() ?><button type="submit">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
