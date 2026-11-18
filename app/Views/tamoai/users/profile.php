<?php
    $p = static fn ($key, $default = '') => old($key, $user[$key] ?? $default);
    $select = static function (string $value, string $label, string $current): string {
        return '<option value="' . esc($value) . '" ' . ($current === $value ? 'selected' : '') . '>' . esc($label) . '</option>';
    };
?>
<header class="tamo-page-head">
    <div>
        <span>Meu perfil</span>
        <h1>Atualize seus dados</h1>
        <p>Essas informações aparecem para o grupo na tela de integrantes.</p>
    </div>
    <a class="tamo-button ghost" href="<?= base_url('/tamo-ai/integrantes') ?>">Ver integrantes</a>
</header>

<form class="tamo-form" action="<?= base_url('/tamo-ai/perfil/atualizar') ?>" method="post">
    <?= csrf_field() ?>
    <section class="form-section">
        <h2>Informações pessoais</h2>
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
            <label>Nova senha
                <input type="password" name="password" placeholder="Preencha somente se quiser trocar">
            </label>
        </div>
    </section>

    <div class="form-actions">
        <button class="tamo-button primary" type="submit">Salvar meu perfil</button>
        <a class="tamo-button ghost" href="<?= base_url('/tamo-ai') ?>">Cancelar</a>
    </div>
</form>
