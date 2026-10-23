<?php
namespace App\Controllers;

use App\Models\AccountModel;
use CodeIgniter\Controller;
use Throwable;

class Auth extends Controller
{
    public function register(): string { return view('register'); }
    public function login(): string { return view('login', ['error' => session()->getFlashdata('error')]); }

    public function create()
    {
        $type = (string) $this->request->getPost('accountType');
        if ($type !== 'customer') {
            return view('register', ['errors' => ['Employee accounts must be created by an authorized administrator. Please register as a customer or contact Sun Son Solar.']]);
        }
        $rules = [
            'accountType' => 'required|in_list[customer]', 'firstnm' => 'required|min_length[2]|max_length[50]',
            'middlenm' => 'permit_empty|max_length[50]', 'lastnm' => 'required|min_length[2]|max_length[50]',
            'birthdate' => 'required|valid_date[Y-m-d]', 'email' => 'required|valid_email|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]', 'password' => 'required|min_length[8]|max_length[72]',
            'confirmPassword' => 'required|matches[password]', 'contactnum' => 'required|regex_match[/^09[0-9]{9}$/]',
            'address' => 'required|min_length[5]', 'gender' => 'permit_empty|in_list[Male,Female,Other,Prefer not to say]',
        ];
        if (! $this->validate($rules)) return view('register', ['errors' => $this->validator->getErrors(), 'old' => $this->request->getPost()]);

        $model = new AccountModel();
        $data = [
            'first_name' => trim((string) $this->request->getPost('firstnm')),
            'middle_name' => trim((string) $this->request->getPost('middlenm')) ?: null,
            'last_name' => trim((string) $this->request->getPost('lastnm')),
            'birthdate' => $this->request->getPost('birthdate'), 'gender' => $this->request->getPost('gender') ?: null,
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'phone_number' => $this->request->getPost('contactnum'), 'address' => trim((string) $this->request->getPost('address')),
            'username' => trim((string) $this->request->getPost('username')),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ];
        try {
            $model->createAccount($data, $type);
            return redirect()->to(site_url('login'))->with('notice', 'Registration successful. Please sign in.');
        } catch (Throwable $e) {
            log_message('error', 'Account registration failed (database error code {code}).', ['code' => (int) $e->getCode()]);
            $duplicate = (int) $e->getCode() === 1062 || str_contains(strtolower($e->getMessage()), 'duplicate');
            return view('register', ['errors' => [$duplicate ? 'That email address or username is already registered.' : 'Unable to complete registration. Please try again.'], 'old' => $this->request->getPost()]);
        }
    }

    public function authenticate()
    {
        $type = (string) $this->request->getPost('accountType');
        $login = trim((string) $this->request->getPost('login'));
        $password = (string) $this->request->getPost('password');
        if (! in_array($type, ['customer', 'employee'], true) || $login === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Enter your account type, email or username, and password.');
        }
        try {
            $account = (new AccountModel())->accountByLogin($login, $type);
        } catch (Throwable $e) {
            log_message('error', 'Sign in lookup failed (database error code {code}).', ['code' => (int) $e->getCode()]);
            return redirect()->back()->withInput()->with('error', 'Sign in is temporarily unavailable. Please try again later.');
        }
        if (! $account || ! password_verify($password, $account['password'])) {
            return redirect()->back()->withInput()->with('error', 'The sign-in details were not recognized.');
        }
        session()->regenerate(true);
        session()->set(['account_id' => $account[$type . '_id'], 'account_type' => $type, 'account_name' => $account['first_name']]);
        return redirect()->to('/')->with('notice', 'Signed in successfully.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
