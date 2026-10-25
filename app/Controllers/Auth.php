<?php

namespace App\Controllers;

use App\Models\CodeModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

class Auth extends BaseController
{
    protected $user;
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    public function getUserIP(): string
    {
        // Honor X-Forwarded-For only for proxies configured as trusted by CI.
        return (string) $this->request->getIPAddress();
    }

    public function index()
    {
        
        $a = $this->userModel->getUser(session('userid'));
        dd($a, session());
    }

    public function login()
    {
        if (session()->has('userid'))
            return redirect()->to('dashboard')->with('msgSuccess', 'Login Successful!');

        if ($this->request->getPost())
            return $this->login_action();
        $data = [
            'title' => 'Login',
            'validation' => Services::validation(),
            'user_ip' => $this->getUserIP(),
        ];
        return view('Auth/login', $data);
    }

    public function register()
    {
        if (session()->has('userid'))
            return redirect()->to('dashboard')->with('msgSuccess', 'Login Successful!');

        if ($this->request->getPost())
            return $this->register_action();
        $data = [
            'title' => 'Register',
            'validation' => Services::validation(),
            'user_ip' => $this->getUserIP(),
        ];
        return view('Auth/register', $data);
    }

    private function login_action()
    {
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $stayLog = $this->request->getPost('stay_log');

        $rules = [
            'username' => 'required|alpha_numeric|min_length[4]|max_length[25]',
            'password' => 'required|min_length[6]|max_length[45]',
            'stay_log' => 'permit_empty|max_length[3]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Failed</strong> Please check the form.');
        }

        $account = $this->userModel->getUser($username, 'username');
        if (! $account || ! password_verify(create_password($password, false), (string) $account->password)) {
            Services::validation()->setError('password', 'Wrong username or password.');
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Failed</strong> Please check the form.');
        }

        $now = \CodeIgniter\I18n\Time::now();
        if ((int) $account->status !== 1 || empty($account->expiration_date)
            || strtotime((string) $account->expiration_date) <= $now->getTimestamp()) {
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Expired</strong> Please Renew Your Account to Login.');
        }

        // Session fixation defense. Keep the existing expiration and IP binding.
        session()->regenerate(true);
        session()->set([
            'userid' => $account->id_users,
            'unames' => $account->username,
            'time_login' => $stayLog ? $now->addHours(10) : $now->addMinutes(120),
            'time_since' => $now,
            'login_at' => $now,
            'stay_log' => $stayLog ? 1 : 0,
            'bind_ip' => $this->getUserIP(),
        ]);
        $this->sendLoginNotice($account);
        return redirect()->to('dashboard')->with('msgSuccess', 'Account Expires on : ' . $account->expiration_date);
    }

    /** Do not allow an optional mail provider outage to block successful logins. */
    private function sendLoginNotice(object $account): void
    {
        $sender = (string) env('email.fromEmail', '');
        if ($sender === '' || ! filter_var($account->email ?? '', FILTER_VALIDATE_EMAIL)) {
            return;
        }
        try {
            $email = Services::email();
            $email->setFrom($sender, 'SHAI Panel');
            $email->setTo((string) $account->email);
            $email->setSubject('SHAI Panel login notification');
            $email->setMessage('A login to your SHAI Panel account was successful.');
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Login notification delivery failed.');
        }
    }

    public function register_action()
    {
        $email = $this->request->getPost('email');
        $userna = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');
        $password = $this->request->getPost('password');
        $referral = $this->request->getPost('referral');
        
        $form_rules = [
            'email' => [
                'label' => 'email',
                'rules' => 'required|min_length[13]|max_length[40]|'
            ],
            'username' => [
                'label' => 'username',
                'rules' => 'required|alpha_numeric|min_length[4]|max_length[25]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'The {field} has been taken.'
                ]
            ],
            'fullname' => [
                'label' => 'fullname',
                'rules' => 'required|alpha_numeric|min_length[4]|max_length[25]|is_unique[users.fullname]',
                'errors' => [
                    'is_unique' => 'The {field} has been taken.'
                ]
            ],
            'password' => [
                'label' => 'password',
                'rules' => 'required|min_length[6]|max_length[45]',
            ],
            'password2' => [
                'label' => 'password',
                'rules' => 'required|min_length[6]|max_length[45]|matches[password]',
                'errors' => [
                    'matches' => '{field} not match, check the {field}.'
                ]
            ],
            'referral' => [
                'label' => 'referral',
                'rules' => 'required|min_length[6]|alpha_numeric',
            ]
        ];

        if (!$this->validate($form_rules)) {
            // Form Invalid
        } else {
            $mCode = new CodeModel();
            $rCheck = $mCode->checkCode($referral);
            $validation = Services::validation();
            if (!$rCheck) {
                $validation->setError('referral', 'Wrong referral, please try again.');
            } else {
                if ($rCheck->used_by) {
                    $validation->setError('referral', "Wrong referral, code has been used &middot; $rCheck->used_by.");
                } else {
                    $hashPassword = create_password($password);
                    $ipaddress = $this->getUserIP();
                    // Referral was already retrieved by the parameterized CodeModel.
                    // Preserve its existing balance, level and expiration semantics.
                    $period    = $rCheck->acc_expiration ?? null;
                    $userLevel = $rCheck->level ?? 1;
                    $data_register = [
                        'email' => $email,
                        'username' => $userna,
                        'fullname' => $fullname,
                        'level' => $userLevel,
                        'status' => 1,
                        'password' => $hashPassword,
                        'saldo' => $rCheck->set_saldo ?: 0,
                        'uplink' => $rCheck->created_by,
                        'user_ip' => $ipaddress,
                        'expiration_date' => $period,
                        'reset_link_token' => '',
                        'exp_date' => '',
                        'logged_in' => 0
                    ];
                    $ids = $this->userModel->insert($data_register, true);
                    if ($ids) {
                        $mCode->useReferral($referral, $userna);
                        $msg = "Register Successfuly!";
                        return redirect()->to('login')->with('msgSuccess', $msg);
                    }
                }
            }
        }
        return redirect()->route('register')->withInput()->with('msgDanger', '<strong>Failed</strong> Please check the form.');
    }

    public function logout()
    {
        if (session()->has('userid')) {
            $unset = ['userid', 'unames', 'time_login', 'time_since', 'login_at', 'stay_log', 'bind_ip'];
            session()->remove($unset);
            session()->setFlashdata('msgSuccess', 'Logout successfuly.');
        }
        return redirect()->to('login');
    }
}
