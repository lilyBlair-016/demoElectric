<?php

namespace App\Controllers;

use App\Models\CustomerAccountModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CustomerAccounts extends BaseController
{
    public function index(): string
    {
        $model = new CustomerAccountModel();

        return view('customer_accounts/index', [
            'title' => 'Customer Accounts - Puihaha Electric',
            'page' => 'dashboard',
            'summary' => $model->getSummaryCounts(),
            'accounts' => $model->getAccountsPaginated(10),
            'pager' => $model->pager,
        ]);
    }

    public function create(): string
    {
        return view('customer_accounts/create', $this->formData());
    }

    public function store()
    {
        return $this->saveAccount();
    }

    public function view($id): string
    {
        return view('customer_accounts/view', [
            'title' => 'Customer Details - Puihaha Electric',
            'page' => 'dashboard',
            'account' => $this->findAccount($id),
        ]);
    }

    public function edit($id): string
    {
        return view('customer_accounts/edit', $this->formData($this->findAccount($id)));
    }

    public function update($id)
    {
        $account = $this->findAccount($id);
        return $this->saveAccount((int) $account['id']);
    }

    public function delete($id)
    {
        $account = $this->findAccount($id);
        $model = new CustomerAccountModel();
        if (! $model->delete($account['id'])) {
            return redirect()->to(site_url('customer-accounts'))->with('error', 'Unable to delete this account.');
        }

        return redirect()->to(site_url('customer-accounts'))->with('success', 'Customer account deleted successfully.');
    }

    private function findAccount($id): array
    {
        $account = (new CustomerAccountModel())->find($id);
        if ($account === null) {
            throw PageNotFoundException::forPageNotFound('Customer account not found.');
        }
        return $account;
    }

    private function formData(array $account = []): array
    {
        return [
            'title' => ($account ? 'Edit Customer' : 'Add Customer') . ' - Puihaha Electric',
            'page' => 'dashboard',
            'account' => $account,
            'connectionTypes' => CustomerAccountModel::CONNECTION_TYPES,
            'statuses' => CustomerAccountModel::STATUSES,
        ];
    }

    private function saveAccount(?int $id = null)
    {
        $model = new CustomerAccountModel();
        $data = [];
        foreach (['account_number', 'customer_name', 'address', 'email', 'meter_number', 'phone', 'connection_type', 'status'] as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : '';
        }

        // The excluded ID comes from the route/database, never a hidden form field.
        $unique = 'is_unique[customer_accounts.account_number' . ($id === null ? ']' : ',id,' . $id . ']');
        $rules = [
            'account_number' => ['label' => 'Account number', 'rules' => 'required|max_length[50]|' . $unique],
            'customer_name' => ['label' => 'Customer name', 'rules' => 'required|max_length[150]'],
            'address' => ['label' => 'Address', 'rules' => 'required|max_length[16000]'],
            'email' => ['label' => 'Email', 'rules' => 'permit_empty|valid_email|max_length[100]'],
            'phone' => ['label' => 'Phone', 'rules' => 'permit_empty|max_length[20]'],
            'meter_number' => ['label' => 'Meter number', 'rules' => 'permit_empty|max_length[50]'],
            'connection_type' => ['label' => 'Connection type', 'rules' => 'permit_empty|in_list[' . implode(',', CustomerAccountModel::CONNECTION_TYPES) . ']'],
            'status' => ['label' => 'Status', 'rules' => 'permit_empty|in_list[' . implode(',', CustomerAccountModel::STATUSES) . ']'],
        ];
        $formUrl = site_url($id === null ? 'customer-accounts/create' : 'customer-accounts/edit/' . $id);
        if (! $this->validateData($data, $rules)) {
            return redirect()->to($formUrl)->with('formInput', $data)->with('errors', $this->validator->getErrors());
        }

        // Empty optional fields match SQL NULL; select defaults are provided by the form.
        foreach (['email', 'phone', 'meter_number', 'connection_type', 'status'] as $field) {
            $data[$field] = $data[$field] === '' ? null : $data[$field];
        }

        try {
            $saved = $id === null ? $model->insert($data) : $model->update($id, $data);
        } catch (DatabaseException $exception) {
            // Also handle a duplicate created between validation and saving.
            if ((int) $exception->getCode() !== 1062) {
                throw $exception;
            }
            return redirect()->to($formUrl)->with('formInput', $data)
                ->with('errors', ['account_number' => 'This account number is already in use.']);
        }
        if ($saved === false) {
            return redirect()->to($formUrl)->with('formInput', $data)
                ->with('errors', $model->errors() ?: ['save' => 'Unable to save this account.']);
        }

        return redirect()->to(site_url('customer-accounts'))
            ->with('success', $id === null ? 'Customer account created successfully.' : 'Customer account updated successfully.');
    }
}
