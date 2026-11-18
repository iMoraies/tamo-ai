<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Entrar | Grupo Tamo Aí') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tamoai.css') ?>">
</head>
<body class="tamo-login-body">
    <main class="tamo-login">
        <section class="tamo-login-card">
            <div class="tamo-login-brand">
                <img src="<?= base_url('assets/img/grupo-tamo-ai.png') ?>" alt="Grupo Tamo Aí">
                <small>agenda interna</small>
            </div>
            <div>
                <h1>Acesso do grupo</h1>
                <p>Entre para consultar eventos, cachês, funções do dia e informações da agenda.</p>
            </div>
            <?php if (session('success')): ?><div class="tamo-flash"><?= esc(session('success')) ?></div><?php endif; ?>
            <?php if (session('error')): ?><div class="tamo-flash error"><?= esc(session('error')) ?></div><?php endif; ?>
            <form action="<?= base_url('/tamo-ai/entrar') ?>" method="post" class="tamo-form compact">
                <?= csrf_field() ?>
                <label>
                    E-mail
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required>
                </label>
                <label>
                    Senha
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button class="tamo-button primary" type="submit">Entrar</button>
            </form>
        </section>
    </main>
</body>
</html>
