<?php $pixLabel = static fn ($user): string => $pixTypes[$user['pix_key_type'] ?? ''] ?? '-'; ?>
<header class="tamo-page-head">
    <div>
        <span>Integrantes</span>
        <h1>Contatos e Pix</h1>
        <p>Lista rápida para conferir integrantes, instrumentos e chaves Pix do grupo.</p>
    </div>
    <a class="tamo-button primary" href="<?= base_url('/tamo-ai/perfil') ?>">Editar meus dados</a>
</header>

<section class="member-grid">
    <?php foreach ($users as $user): ?>
        <article class="member-card">
            <div>
                <span><?= esc($user['main_instrument'] ?: 'Integrante') ?></span>
                <h2><?= esc($user['name']) ?></h2>
                <p><?= esc($user['email']) ?></p>
                <?php if (! empty($user['phone'])): ?><p><?= esc($user['phone']) ?></p><?php endif; ?>
            </div>
            <div class="pix-box">
                <small>Pix · <?= esc($pixLabel($user)) ?></small>
                <strong><?= esc($user['pix_key'] ?: 'Não informado') ?></strong>
            </div>
        </article>
    <?php endforeach; ?>
</section>
