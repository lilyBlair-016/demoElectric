<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="py-5 bg-light-custom">
    <div class="container-fluid px-lg-5">
        <?= $this->include('customer_accounts/_header') ?>
        <a class="btn btn-primary mb-4" href="<?= site_url('customer-accounts/create') ?>">Add Customer</a>
        <section class="mb-4" aria-label="Customer account summary">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3">
                <?php foreach ([
                    'total' => 'Total Customer Accounts',
                    'residential' => 'Residential Customers',
                    'commercial' => 'Commercial Customers',
                    'industrial' => 'Industrial Customers',
                    'active' => 'Active Customers',
                ] as $key => $label): ?>
                    <div class="col">
                        <div class="card h-100 p-3 text-center">
                            <h2 class="h6 text-primary-custom mb-2"><?= esc($label) ?></h2>
                            <p class="h2 fw-bold text-primary-custom mb-0"><?= (int) $summary[$key] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th><th>Account Number</th><th>Customer Name</th><th>Email</th>
                        <th>Phone</th><th>Meter Number</th><th>Connection Type</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($accounts === []): ?>
                        <tr><td colspan="9" class="text-center py-4">No customer accounts found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <?php foreach (['id', 'account_number', 'customer_name', 'email', 'phone', 'meter_number', 'connection_type', 'status'] as $field): ?>
                                <td><?= esc($account[$field] ?? '—') ?></td>
                            <?php endforeach; ?>
                            <td>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('customer-accounts/view/' . $account['id']) ?>">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('customer-accounts/edit/' . $account['id']) ?>">Edit</a>
                                    <form method="post" action="<?= site_url('customer-accounts/delete/' . $account['id']) ?>" onsubmit="return confirm('Delete this customer account? This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 account-pagination">
            <p class="mb-0"><?= $pager->getTotal() ?> accounts · Page <?= $pager->getCurrentPage() ?> of <?= max(1, $pager->getPageCount()) ?></p>
            <?= $pager->only([])->links() ?>
        </div>
    </div>
</section>
<style>
    .account-pagination ul { display: flex; flex-wrap: wrap; gap: .5rem; list-style: none; padding: 0; margin: 0; }
    .account-pagination a { display: block; padding: .4rem .8rem; border: 1px solid #1e40af; border-radius: .25rem; text-decoration: none; }
    .account-pagination .active a { color: white; background: #1e40af; }
</style>
<?= $this->endSection() ?>
