<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Agenda | Grupo Tamo Aí') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tamoai.css') ?>">
</head>
<body class="tamo-body">
    <?php
        $currentPath = trim(parse_url(current_url(), PHP_URL_PATH) ?? '', '/');
        $basePath = trim(parse_url(base_url(), PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
            $currentPath = trim(substr($currentPath, strlen($basePath)), '/');
        }
        if (str_starts_with($currentPath, 'index.php/')) {
            $currentPath = trim(substr($currentPath, strlen('index.php/')), '/');
        }
        $isActive = static fn (string $path): bool => $currentPath === trim($path, '/');
        $startsWith = static fn (string $path): bool => str_starts_with($currentPath, trim($path, '/'));
        $role = (string) session('tamo_ai_role');
        $canFinance = in_array($role, ['admin', 'financeiro'], true);
        $roleLabels = [
            'admin' => 'Administrador',
            'financeiro' => 'Financeiro',
            'member' => 'Integrante',
        ];
    ?>
    <div class="tamo-shell">
        <aside class="tamo-sidebar">
            <a class="tamo-brand" href="<?= base_url('/tamo-ai') ?>" aria-label="Agenda Grupo Tamo Aí">
                <img src="<?= base_url('assets/img/grupo-tamo-ai.png') ?>" alt="Grupo Tamo Aí">
            </a>
            <div class="tamo-user-card">
                <span>logado como</span>
                <strong><?= esc(session('tamo_ai_name') ?: 'Integrante') ?></strong>
                <small><?= esc($roleLabels[$role] ?? 'Integrante') ?></small>
            </div>
            <nav class="tamo-nav" aria-label="Navegação da agenda">
                <a class="<?= $isActive('tamo-ai') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai') ?>">Agenda</a>
                <?php if ($role === 'admin'): ?>
                    <a href="<?= base_url('/tamo-ai/eventos/novo') ?>">Novo evento</a>
                <?php endif; ?>
                <a class="<?= $isActive('tamo-ai/caixa') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/caixa') ?>">Caixa</a>
                <a class="<?= $startsWith('tamo-ai/comprovantes') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/comprovantes') ?>">Comprovantes</a>
                <a class="<?= $startsWith('tamo-ai/materiais') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/materiais') ?>">Materiais</a>
                <a class="<?= $startsWith('tamo-ai/indisponibilidades') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/indisponibilidades') ?>">Indisponibilidades</a>
                <?php if ($canFinance): ?>
                    <a class="<?= $startsWith('tamo-ai/freelancers') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/freelancers') ?>">Freelancers</a>
                <?php endif; ?>
                <a class="<?= $isActive('tamo-ai/integrantes') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/integrantes') ?>">Integrantes</a>
                <a class="<?= $isActive('tamo-ai/perfil') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/perfil') ?>">Meu perfil</a>
                <?php if ($role === 'admin'): ?>
                    <a class="<?= $startsWith('tamo-ai/usuarios') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/usuarios') ?>">Usuários</a>
                    <a class="<?= $startsWith('tamo-ai/logs') ? 'active' : '' ?>" href="<?= base_url('/tamo-ai/logs') ?>">Logs</a>
                <?php endif; ?>
                <a href="<?= base_url('/tamo-ai/sair') ?>">Sair</a>
            </nav>
        </aside>

        <main class="tamo-main">
            <?php if (session('success')): ?><div class="tamo-flash"><?= esc(session('success')) ?></div><?php endif; ?>
            <?php if (session('error')): ?><div class="tamo-flash error"><?= esc(session('error')) ?></div><?php endif; ?>
            <?= $content ?? '' ?>
        </main>
    </div>
</body>
</html>
