<?php
$values = array_replace($account, session()->getFlashdata('formInput') ?? []);
$action = isset($account['id']) ? 'customer-accounts/update/' . $account['id'] : 'customer-accounts/store';
?>
<form method="post" action="<?= site_url($action) ?>" class="bg-white p-4 rounded shadow-sm">
    <?= csrf_field() ?>
    <p class="text-muted">Fields marked * are required.</p>
    <div class="row g-3">
        <?php foreach (['account_number' => ['Account Number', 50], 'customer_name' => ['Customer Name', 150], 'email' => ['Email', 100], 'phone' => ['Phone', 20], 'meter_number' => ['Meter Number', 50]] as $field => [$label, $length]): ?>
            <?php $required = in_array($field, ['account_number', 'customer_name'], true); ?>
            <div class="col-md-6">
                <label class="form-label" for="<?= $field ?>"><?= $label ?><?= $required ? ' *' : '' ?></label>
                <input class="form-control" type="<?= $field === 'email' ? 'email' : 'text' ?>" id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $length ?>" value="<?= esc($values[$field] ?? '') ?>" <?= $required ? 'required' : '' ?>>
            </div>
        <?php endforeach; ?>
        <div class="col-12">
            <label class="form-label" for="address">Address *</label>
            <textarea class="form-control" id="address" name="address" rows="3" maxlength="16000" required><?= esc($values['address'] ?? '') ?></textarea>
        </div>
        <?php foreach (['connection_type' => ['Connection Type', $connectionTypes, 'residential'], 'status' => ['Status', $statuses, 'active']] as $field => [$label, $options, $default]): ?>
            <?php $selected = array_key_exists($field, $values) ? ($values[$field] ?? '') : $default; ?>
            <div class="col-md-6">
                <label class="form-label" for="<?= $field ?>"><?= $label ?></label>
                <select class="form-select" name="<?= $field ?>" id="<?= $field ?>">
                    <option value="" <?= $selected === '' ? 'selected' : '' ?>>Not specified</option>
                    <?php foreach ($options as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $selected === $option ? 'selected' : '' ?>><?= esc(ucfirst($option)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-4 d-flex gap-3">
        <button type="submit" class="btn btn-primary">Save Customer</button>
        <a class="btn btn-outline-secondary<?= isset($account['id']) ? ' d-inline-flex align-items-center justify-content-center' : '' ?>" href="<?= site_url('customer-accounts') ?>">Cancel</a>
    </div>
</form>
