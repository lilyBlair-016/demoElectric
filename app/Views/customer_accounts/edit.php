<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="py-5 bg-light-custom">
    <div class="container">
        <?= $this->include('customer_accounts/_header') ?>
        <?= $this->include('customer_accounts/_form') ?>
    </div>
</section>
<?= $this->endSection() ?>
