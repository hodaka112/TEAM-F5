<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Throwable;

class Home extends Controller
{
    public function index()
    {
        // Keep the public homepage available to visitors and customers, but
        // send signed-in staff to the dashboard that matches their access.
        if (session()->get('account_type') === 'employee') {
            $employeeId = (int) session()->get('account_id');
            try {
                $employee = db_connect()->table('employees')
                    ->select('role, department, is_active')
                    ->where('employee_id', $employeeId)
                    ->get()->getRowArray();
            } catch (Throwable $e) {
                log_message('error', 'Homepage staff access check failed.');
                $employee = null;
            }

            if (! $employee || (int) ($employee['is_active'] ?? 0) !== 1) {
                session()->destroy();
                return redirect()->to(site_url('login'))
                    ->with('error', 'Your staff session is no longer active. Please sign in again.');
            }

            if (($employee['role'] ?? '') === 'admin') {
                return redirect()->to(site_url('admin/dashboard'));
            }

            if (in_array($employee['role'] ?? '', ['employee', 'technician'], true)
                && in_array($employee['department'] ?? '', ['Installation & Operations', 'Maintenance & Service'], true)) {
                return redirect()->to(site_url('technician/dashboard'));
            }

            if (in_array($employee['role'] ?? '', ['employee', 'technician'], true)) {
                $departmentRoutes = [
                    'Management' => 'management/dashboard',
                    'IT' => 'it/dashboard',
                    'Sales & Marketing' => 'sales-marketing/dashboard',
                    'Engineering' => 'engineering/dashboard',
                ];
                $route = $departmentRoutes[$employee['department'] ?? ''] ?? null;
                if ($route !== null) return redirect()->to(site_url($route));
            }
        }

        return view('home', ['notice' => session()->getFlashdata('notice')]);
    }
}
