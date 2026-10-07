<?php

namespace App\Controllers;

use App\Models\User;

class Register extends BaseController
{
    protected $userModel;
    public function __construct()
    {
        $this->userModel = new User();
    }
    public function index(): string
    {
        $data = [
            'title' => 'Register - PowerFlow Electric',
            'page' => 'register',
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'validation' => session()->getFlashdata('validation')
        ];
        return view('register', $data);
    }

    public function create()
    {
        // Registration and login must use the same users database.
        if (! db_connect('auth')->tableExists('users')) {
            return redirect()->to(site_url('register'))->with('error',
                'Online registration is currently unavailable. Please contact us for assistance.');
        }
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'phone' => 'required|min_length[10]|max_length[20]',
            'address' => 'required|min_length[5]|max_length[255]',
            'city' => 'required|min_length[2]|max_length[100]',
            'state' => 'required|min_length[2]|max_length[50]',
            'zip_code' => 'required|min_length[5]|max_length[10]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
            'terms' => 'required'
        ]);
        // Pass the group explicitly so is_unique checks auth.users, not CRUD.
        if (!$validation->run($this->request->getPost(), null, 'auth')) {
            session()->setFlashdata('validation', $validation->getErrors());
            return redirect()->to(site_url('register'))->withInput();
        }
        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code'),
            'password' => $this->request->getPost('password'),
            'user_type' => 'customer',
            'is_active' => true,
            'email_verified' => false
        ];
        try {
            $userId = $this->userModel->insert($userData);
            if ($userId) {
                session()->setFlashdata('success', 'Registration successful! Welcome to PowerFlow
Electric. You can now contact us for your electrical needs.');
                return redirect()->to(site_url('register'));
            } else {
                session()->setFlashdata('error', 'Registration failed. Please try again.');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Registration failed: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
