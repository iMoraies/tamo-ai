<?php
    $p = static fn ($key, $default = '') => old($key, $user[$key] ?? $default);
    $select = static function (string $value, string $label, string $current): string {
        return '<option value="' . esc($value) . '" ' . ($current === $value ? 'selected' : '') . '>' . esc($label) . '</option>';
    };
?>
<header class="tamo-page-head">
    <div>
        <span>Acesso</span>
        <h1><?= esc($title) ?></h1>
        <p>Admins criam acessos. Cada integrante pode completar telefone, instrumento e Pix no próprio perfil.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/usuarios') ?>">Voltar</a>
</header>

<form class="tamo-form" action="<?= base_url($action) ?>" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Dados do usuário</h2>
        <div class="form-grid">
            <label>Nome
                <input name="name" value="<?= esc($p('name')) ?>" required>
            </label>
            <label>E-mail
                <input type="email" name="email" value="<?= esc($p('email')) ?>" required>
            </label>
            <label>Telefone
                <input name="phone" value="<?= esc($p('phone')) ?>" placeholder="(21) 99999-9999">
            </label>
            <label>Instrumento principal
                <select name="main_instrument">
                    <option value="">Selecione</option>
                    <?php foreach ($instruments as $item): ?><?= $select($item, $item, $p('main_instrument')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Tipo da chave Pix
                <select name="pix_key_type">
                    <option value="">Selecione</option>
                    <?php foreach ($pixTypes as $key => $label): ?><?= $select($key, $label, $p('pix_key_type')) ?><?php endforeach; ?>
                </select>
            </label>
            <label>Chave Pix
                <input name="pix_key" value="<?= esc($p('pix_key')) ?>">
            </label>
            <label>Perfil
                <select name="role">
                    <option value="member" <?= $p('role', 'member') === 'member' ? 'selected' : '' ?>>Integrante</option>
                    <option value="financeiro" <?= $p('role') === 'financeiro' ? 'selected' : '' ?>>Financeiro</option>
                    <option value="admin" <?= $p('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </label>
            <label>Senha
                <input type="password" name="password" placeholder="Preencha para criar ou trocar">
            </label>
        </div>
        <label class="tamo-check">
            <input type="checkbox" name="active" value="1" <?= $p('active', 1) ? 'checked' : '' ?>>
            Usuário ativo
        </label>
    </section>

    <div class="form-actions">
        <button class="tamo-button primary" type="submit">Salvar usuário</button>
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/usuarios') ?>">Cancelar</a>
    </div>
</form>
