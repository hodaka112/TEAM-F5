<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class SetSolAsFieldTechnician extends Migration
{
    private const SOL_EMAIL = 'sol@sunsonsolar.test';
    private const TECHNICIAN_DEPARTMENT = 'Installation & Operations';

    public function up()
    {
        $sol = $this->db->table('employees')
            ->select('employee_id')
            ->where('email', self::SOL_EMAIL)
            ->get()
            ->getRowArray();

        if ($sol === null) {
            throw new RuntimeException('The existing Sol employee record was not found; no account was changed.');
        }

        // Update only the role and department needed by the existing technician access checks.
        $this->db->table('employees')
            ->where('employee_id', (int) $sol['employee_id'])
            ->update([
                'role' => 'employee',
                'department' => self::TECHNICIAN_DEPARTMENT,
            ]);
    }

    public function down()
    {
        // Do not restore administrator access automatically during rollback.
    }
}
