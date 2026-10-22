<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddAdminRolesToEmployees extends Migration
{
    private const ADMIN_EMAILS = [
        'katherine@sunsonsolar.test',
        'sol@sunsonsolar.test',
    ];

    public function up()
    {
        $matchingAccounts = $this->db->table('employees')
            ->select('employee_id')
            ->whereIn('email', self::ADMIN_EMAILS)
            ->get()
            ->getResultArray();

        if (count($matchingAccounts) !== count(self::ADMIN_EMAILS)) {
            throw new RuntimeException('Expected existing Kat and Sol employee records before assigning administrator roles.');
        }

        if (! $this->db->fieldExists('role', 'employees')) {
            $this->forge->addColumn('employees', [
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'employee',
                    'null'       => false,
                    'after'      => 'department',
                ],
            ]);
        }

        $this->db->table('employees')
            ->whereIn('email', self::ADMIN_EMAILS)
            ->update(['role' => 'admin']);
    }

    public function down()
    {
        // Do not remove authorization data during a routine rollback.
        // Revoke roles explicitly before dropping this column, if ever needed.
    }
}
