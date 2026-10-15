<?php
namespace App\Models;

use CodeIgniter\Model;
use InvalidArgumentException;
use RuntimeException;

class AccountModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $allowedFields = ['first_name','middle_name','last_name','birthdate','gender','email','phone_number','address','username','password'];
    protected $returnType = 'array';
    protected $useTimestamps = false;

    public function accountByLogin(string $login, string $type): ?array
    {
        $table = $type === 'employee' ? 'employees' : 'customers';
        return $this->db->table($table)->groupStart()->where('email', $login)->orWhere('username', $login)->groupEnd()->get()->getRowArray() ?: null;
    }

    public function createAccount(array $data, string $type): bool
    {
        if ($type !== 'customer') {
            throw new InvalidArgumentException('Employee account creation is disabled until an authorized provisioning policy is implemented.');
        }
        unset($data['department']);
        if (! $this->db->table('customers')->insert($data)) {
            $error = $this->db->error();
            throw new RuntimeException($error['message'] ?? 'Account insert failed.', (int) ($error['code'] ?? 0));
        }
        return true;
    }
}
