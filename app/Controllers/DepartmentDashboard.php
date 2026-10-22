<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DepartmentDashboard extends Controller
{
    private const ROUTES = [
        'Management' => 'management/dashboard',
        'IT' => 'it/dashboard',
        'Sales & Marketing' => 'sales-marketing/dashboard',
        'Engineering' => 'engineering/dashboard',
        'Installation & Operations' => 'technician/dashboard',
        'Maintenance & Service' => 'technician/dashboard',
    ];

    public function management() { return $this->show('Management'); }
    public function it() { return $this->show('IT'); }
    public function salesMarketing() { return $this->show('Sales & Marketing'); }
    public function engineering() { return $this->show('Engineering'); }

    private function show(string $expectedDepartment)
    {
        if (session()->get('account_type') !== 'employee' || ! session()->get('account_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Sign in with your employee account to continue.');
        }

        $employee = db_connect()->table('employees')
            ->select('employee_id, first_name, middle_name, last_name, email, department, role, is_active')
            ->where('employee_id', (int) session()->get('account_id'))
            ->get()->getRowArray();

        if (! $employee || (int) $employee['is_active'] !== 1) {
            session()->destroy();
            return redirect()->to(site_url('login'))->with('error', 'Your employee account is inactive or unavailable.');
        }

        if ($employee['role'] === 'admin') return redirect()->to(site_url('admin/dashboard'));

        if (! in_array($employee['role'], ['employee', 'technician'], true)) {
            return $this->response->setStatusCode(403)->setBody('This dashboard is not available for your account.');
        }

        if ($employee['department'] !== $expectedDepartment) {
            $destination = self::ROUTES[$employee['department']] ?? null;
            if ($destination !== null) return redirect()->to(site_url($destination));
            return $this->response->setStatusCode(403)->setBody('No dashboard is configured for your department.');
        }

        $messages = db_connect()->table('notifications')->groupStart()
            ->where('audience', 'all')
            ->orGroupStart()->where('audience', 'employee')
                ->groupStart()->where('recipient_id', (int) $employee['employee_id'])->orWhere('recipient_id', null)->groupEnd()
            ->groupEnd()->groupEnd()
            ->orderBy('created_at', 'DESC')->limit(30)->get()->getResultArray();

        return view('department_dashboard', [
            'employee' => $employee,
            'department' => $expectedDepartment,
            'messages' => $messages,
            'notice' => session()->getFlashdata('notice'),
        ]);
    }
}
