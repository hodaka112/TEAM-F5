<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;
use Throwable;

class UpdateNamedEmployeeAccounts extends Migration
{
    public function up()
    {
        $passwords = [
            'Katherine' => getenv('TEAMF5_KATHERINE_PASSWORD'),
            'Sol' => getenv('TEAMF5_SOL_PASSWORD'),
            'Apollo' => getenv('TEAMF5_APOLLO_PASSWORD'),
        ];
        foreach ($passwords as $password) {
            if (! is_string($password) || $password === '') {
                throw new RuntimeException('Set the three TEAMF5 account password environment variables before running this migration.');
            }
        }

        $accounts = [
            [
                'label' => 'Katherine', 'lookup' => ['email' => 'katherine@sunsonsolar.test'],
                'email' => 'KittyKat16@gmail.com', 'role' => 'admin', 'department' => 'Management',
                'password' => $passwords['Katherine'],
            ],
            [
                'label' => 'Sol', 'lookup' => ['email' => 'sol@sunsonsolar.test'],
                'email' => 'Solsolis@gmail.com', 'role' => 'admin', 'department' => 'IT',
                'password' => $passwords['Sol'],
            ],
            [
                'label' => 'Apollo', 'lookup' => ['first_name' => 'Apollo', 'last_name' => 'Santos', 'department' => 'Installation & Operations'],
                'email' => 'apollo@gmail.com', 'role' => 'technician', 'department' => 'Installation & Operations',
                'password' => $passwords['Apollo'],
            ],
        ];

        foreach ($accounts as &$account) {
            $query = $this->db->table('employees')->select('employee_id');
            foreach ($account['lookup'] as $field => $value) $query->where($field, $value);
            $matches = $query->get()->getResultArray();
            if (count($matches) !== 1) {
                throw new RuntimeException('Expected exactly one existing employee record for ' . $account['label'] . '; no account changes were made.');
            }
            $account['employee_id'] = (int) $matches[0]['employee_id'];
            $account['password_hash'] = password_hash($account['password'], PASSWORD_DEFAULT);
            unset($account['password'], $account['lookup']);

            $emailInUse = $this->db->table('employees')->where('email', $account['email'])->where('employee_id !=', $account['employee_id'])->countAllResults()
                + $this->db->table('customers')->where('email', $account['email'])->countAllResults();
            $usernameInUse = $this->db->table('employees')->where('username', $account['email'])->where('employee_id !=', $account['employee_id'])->countAllResults();
            if ($emailInUse > 0 || $usernameInUse > 0) {
                throw new RuntimeException('A requested email or username is already in use; no account changes were made.');
            }
        }
        unset($account);

        $this->db->transBegin();
        try {
            foreach ($accounts as $account) {
                $this->db->table('employees')->where('employee_id', $account['employee_id'])->update([
                    'email' => $account['email'],
                    'username' => $account['email'],
                    'password' => $account['password_hash'],
                    'role' => $account['role'],
                    'department' => $account['department'],
                ]);
            }
            if (! $this->db->transStatus()) throw new RuntimeException('Database rejected an employee account update.');
            $this->db->transCommit();
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw new RuntimeException('Employee account updates failed and were rolled back.');
        }
    }

    public function down()
    {
        // Do not restore old credentials or revoke administrator access automatically.
    }
}
