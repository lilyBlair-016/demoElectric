<?php

namespace App\Controllers;

use App\Models\User;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in') === true && session()->get('user_id') > 0) {
            return redirect()->to(site_url('dashboard'));
        }
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return view('login', ['title' => 'Login - Puihaha Electric', 'page' => 'login']);
    }

    public function authenticate()
    {
        $credentials = $this->request->getPost(['email', 'password']);
        if (is_string($credentials['email'] ?? null)) {
            $credentials['email'] = trim($credentials['email']);
        }
        if (! $this->validateData($credentials, [
            'email' => 'required|valid_email|max_length[255]',
            'password' => 'required|max_length[255]',
        ])) {
            return redirect()->to(site_url('login'))->with('error', 'Enter a valid email address and your password.');
        }

        // User is bound to the auth group: electriccompany.users.
        $model = new User();
        $user = $model->findByEmail($credentials['email']);
        $validPassword = $user !== null && $model->verifyPassword($credentials['password'], $user['password']);
        if (! $validPassword || $user['is_active'] !== true) {
            // Do not flash passwords or store the POST body in the session.
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password, or the account is inactive.')
                ->with('email', $credentials['email']);
        }

        session()->regenerate(true);
        session()->remove('username'); // Remove the previous .env administrator identity.
        session()->set([
            'logged_in' => true,
            'isLogged' => true, // Retain the professor's session flag as well.
            'user_id' => (int) $user['id'],
            'first_name' => $user['first_name'],
            'email' => $user['email'],
            'user_type' => $user['user_type'],
        ]);
        return redirect()->to(site_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }
}
