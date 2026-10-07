<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="py-5 bg-light-custom">
    <div class="container">
        <?= $this->include('customer_accounts/_header') ?>
        <div class="bg-white p-4 rounded shadow-sm">
            <dl class="row">
                <?php foreach (['id' => 'ID', 'account_number' => 'Account Number', 'customer_name' => 'Customer Name', 'address' => 'Address', 'email' => 'Email', 'phone' => 'Phone', 'meter_number' => 'Meter Number', 'connection_type' => 'Connection Type', 'status' => 'Status', 'created_at' => 'Created At', 'updated_at' => 'Updated At'] as $field => $label): ?>
                    <dt class="col-sm-3"><?= $label ?></dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap; overflow-wrap: anywhere;"><?= esc($account[$field] ?? '—') ?></dd>
                <?php endforeach; ?>
            </dl>
            <a class="btn btn-primary" href="<?= site_url('customer-accounts/edit/' . $account['id']) ?>">Edit Customer</a>
            <a class="btn btn-outline-secondary" href="<?= site_url('customer-accounts') ?>">Back to list</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
