<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="w-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card p-4">
                    <h1 class="h2 text-primary-custom">Account Login</h1>
                    <p class="text-muted">Sign in to manage customer accounts.</p>
                    <?php if ($error = session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger" role="alert"><?= esc($error) ?></div>
                    <?php endif; ?>
                    <form action="<?= site_url('login') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" maxlength="255" autocomplete="username" value="<?= esc(session()->getFlashdata('email') ?? '') ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" maxlength="255" autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 text-center mt-2">Login</button>
                        <p class="text-muted mt-3 mb-0">
                            Not registered? <a href="<?= site_url('register') ?>">Register here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
