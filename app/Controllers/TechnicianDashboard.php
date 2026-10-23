<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TechnicianDashboard extends Controller
{
    private const TECHNICIAN_DEPARTMENTS = ['Installation & Operations', 'Maintenance & Service'];

    public function index()
    {
        $employee = $this->currentTechnician();
        if ($employee instanceof \CodeIgniter\HTTP\ResponseInterface) return $employee;
        $db = db_connect();
        $employeeId = (int) $employee['employee_id'];
        $assignments = $db->table('service_assignments a')
            ->select('a.assignment_id, a.request_id, a.scheduled_date, a.scheduled_time, a.status, r.first_name, r.last_name, r.address, r.service_type, r.preferred_date')
            ->join('service_requests r', 'r.request_id = a.request_id')
            ->where('a.employee_id', $employeeId)->where('a.status !=', 'Cancelled')->orderBy('a.scheduled_date', 'ASC')->get()->getResultArray();
        $checkins = $db->table('technician_checkins c')->select('c.checkin_id, c.assignment_id, c.latitude, c.longitude, c.checked_in_at, a.request_id')
            ->join('service_assignments a', 'a.assignment_id = c.assignment_id')->where('c.employee_id', $employeeId)->orderBy('c.checked_in_at', 'DESC')->limit(30)->get()->getResultArray();
        $reports = $db->table('technician_reports r')->select('r.report_id, r.assignment_id, r.category, r.description, r.status, r.created_at, a.request_id')->join('service_assignments a', 'a.assignment_id = r.assignment_id')->where('r.employee_id', $employeeId)->orderBy('r.created_at', 'DESC')->limit(30)->get()->getResultArray();
        $notifications = $db->table('notifications')->groupStart()->where('audience', 'all')->orGroupStart()->where('audience', 'employee')->groupStart()->where('recipient_id', $employeeId)->orWhere('recipient_id', null)->groupEnd()->groupEnd()->groupEnd()
            ->orderBy('created_at', 'DESC')->limit(40)->get()->getResultArray();
        return view('technician_dashboard', [
            'employee' => $employee, 'assignments' => $assignments, 'checkins' => $checkins,
            'reports' => $reports, 'notifications' => $notifications,
            'notice' => session()->getFlashdata('notice'), 'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function checkIn()
    {
        $employee = $this->currentTechnician();
        if ($employee instanceof \CodeIgniter\HTTP\ResponseInterface) return $employee;
        $assignmentId = (int) $this->request->getPost('assignment_id');
        $lat = filter_var($this->request->getPost('latitude'), FILTER_VALIDATE_FLOAT);
        $lng = filter_var($this->request->getPost('longitude'), FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) return $this->backWithErrors(['checkin' => 'Location was not available. Allow location access and try again.']);
        $assignment = db_connect()->table('service_assignments a')->select('a.assignment_id, a.status')->join('service_requests r', 'r.request_id = a.request_id')->where('a.assignment_id', $assignmentId)->where('a.employee_id', (int) $employee['employee_id'])->whereIn('a.status', ['Assigned', 'In Progress'])->where('r.status !=', 'Cancelled')->get()->getRowArray();
        if (! $assignment) return $this->backWithErrors(['checkin' => 'Check-in is available only for your active assigned jobs.']);
        db_connect()->table('technician_checkins')->insert(['assignment_id' => $assignmentId, 'employee_id' => (int) $employee['employee_id'], 'latitude' => $lat, 'longitude' => $lng]);
        if ($assignment['status'] === 'Assigned') db_connect()->table('service_assignments')->where('assignment_id', $assignmentId)->update(['status' => 'In Progress', 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('technician/dashboard#checkins'))->with('notice', 'Check-in saved with your browser location and current time.');
    }

    public function createReport()
    {
        $employee = $this->currentTechnician();
        if ($employee instanceof \CodeIgniter\HTTP\ResponseInterface) return $employee;
        $assignmentId = (int) $this->request->getPost('assignment_id');
        $category = (string) $this->request->getPost('category');
        $description = trim((string) $this->request->getPost('description'));
        if (! in_array($category, ['Equipment', 'Customer concern', 'Safety', 'Other'], true) || strlen($description) < 5 || strlen($description) > 3000) return $this->backWithErrors(['report' => 'Choose a report type and enter at least five characters.']);
        $assignment = db_connect()->table('service_assignments a')->select('a.assignment_id, a.request_id')->join('service_requests r', 'r.request_id = a.request_id')->where('a.assignment_id', $assignmentId)->where('a.employee_id', (int) $employee['employee_id'])->where('r.status !=', 'Cancelled')->get()->getRowArray();
        if (! $assignment) return $this->backWithErrors(['report' => 'Choose one of your assigned jobs.']);
        db_connect()->table('technician_reports')->insert(['assignment_id' => $assignmentId, 'employee_id' => (int) $employee['employee_id'], 'category' => $category, 'description' => $description]);
        foreach (db_connect()->table('employees')->select('employee_id')->where('role', 'admin')->where('is_active', 1)->get()->getResultArray() as $admin) {
            db_connect()->table('notifications')->insert(['audience' => 'employee', 'recipient_id' => $admin['employee_id'], 'notification_type' => 'report', 'title' => 'Technician problem report', 'message' => 'A technician submitted a report for request #' . $assignment['request_id'] . '.']);
        }
        return redirect()->to(site_url('technician/dashboard#issues'))->with('notice', 'Problem report sent to the administrators.');
    }

    public function updateAssignmentStatus()
    {
        $employee = $this->currentTechnician();
        if ($employee instanceof \CodeIgniter\HTTP\ResponseInterface) return $employee;
        $assignmentId = (int) $this->request->getPost('assignment_id');
        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['In Progress', 'Completed'], true)) return $this->backWithErrors(['assignment' => 'Choose a valid job status.']);
        $db = db_connect();
        $assignment = $db->table('service_assignments a')->select('a.assignment_id, a.request_id, r.customer_id, r.status AS request_status')->join('service_requests r', 'r.request_id = a.request_id')->where('a.assignment_id', $assignmentId)->where('a.employee_id', (int) $employee['employee_id'])->where('r.status !=', 'Cancelled')->get()->getRowArray();
        if (! $assignment) return $this->backWithErrors(['assignment' => 'That job is not assigned to your account.']);
        $db->table('service_assignments')->where('assignment_id', $assignmentId)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($status === 'Completed') $db->table('service_requests')->where('request_id', $assignment['request_id'])->update(['status' => 'Completed']);
        if ($assignment['customer_id']) $db->table('notifications')->insert(['audience' => 'customer', 'recipient_id' => $assignment['customer_id'], 'notification_type' => 'update', 'title' => 'Service progress updated', 'message' => 'Your service request #' . $assignment['request_id'] . ' is now ' . strtolower($status) . '.']);
        return redirect()->to(site_url('technician/dashboard#jobs'))->with('notice', 'Job status updated.');
    }

    public function markNotificationRead()
    {
        $employee = $this->currentTechnician();
        if ($employee instanceof \CodeIgniter\HTTP\ResponseInterface) return $employee;
        db_connect()->table('notifications')->where('notification_id', (int) $this->request->getPost('notification_id'))
            ->groupStart()->where('audience', 'all')->orGroupStart()->where('audience', 'employee')->groupStart()->where('recipient_id', (int) $employee['employee_id'])->orWhere('recipient_id', null)->groupEnd()->groupEnd()->groupEnd()
            ->update(['read_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('technician/dashboard#announcements'));
    }

    private function currentTechnician()
    {
        if (session()->get('account_type') !== 'employee' || ! session()->get('account_id')) return redirect()->to(site_url('login'))->with('error', 'Sign in with an employee account to continue.');
        $employee = db_connect()->table('employees')->select('employee_id, first_name, department, role, is_active')->where('employee_id', (int) session()->get('account_id'))->get()->getRowArray();
        if ($employee === null || (int) $employee['is_active'] !== 1) {
            session()->destroy();
            return redirect()->to(site_url('login'))->with('error', 'Your employee account is inactive or unavailable.');
        }
        if (! in_array($employee['role'], ['employee', 'technician'], true) || ! in_array($employee['department'], self::TECHNICIAN_DEPARTMENTS, true)) return $this->response->setStatusCode(403)->setBody('This dashboard is available to active field service employees.');
        return $employee;
    }

    private function backWithErrors(array $errors)
    {
        return redirect()->to(site_url('technician/dashboard'))->withInput()->with('errors', $errors)->with('notice', 'Please check the information and try again.');
    }
}
