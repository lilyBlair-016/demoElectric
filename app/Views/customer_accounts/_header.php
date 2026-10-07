<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 text-primary-custom"><?= esc(explode(' - ', $title)[0]) ?></h1>
        <p class="text-muted mb-0">Signed in as <?= esc(session()->get('first_name') ?? '') ?> (<?= esc(session()->get('email') ?? '') ?>)</p>
    </div>
    <form method="post" action="<?= site_url('logout') ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline-secondary" type="submit">Logout</button>
    </form>
</div>
<?php if ($success = session()->getFlashdata('success')): ?>
    <div class="alert alert-success" role="status"><?= esc($success) ?></div>
<?php endif; ?>
<?php if ($error = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" role="alert"><?= esc($error) ?></div>
<?php endif; ?>
<?php if ($errors = session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger" role="alert">
        <p>Please correct the following:</p>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
