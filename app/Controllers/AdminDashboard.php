<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Throwable;

class AdminDashboard extends Controller
{
    private const DEPARTMENTS = ['Management', 'IT', 'Installation & Operations', 'Maintenance & Service', 'Sales & Marketing', 'Engineering'];
    private const FIELD_DEPARTMENTS = ['Installation & Operations', 'Maintenance & Service'];

    public function index()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $db = db_connect();

        $employees = $db->table('employees')->select('employee_id, first_name, middle_name, last_name, email, phone_number, username, department, role, is_active, created_at')->orderBy('employee_id')->get()->getResultArray();
        $currentAdminId = (int) session()->get('account_id');
        $adminDepartment = '';
        foreach ($employees as $employeeRow) {
            if ((int) $employeeRow['employee_id'] === $currentAdminId) {
                $adminDepartment = (string) $employeeRow['department'];
                break;
            }
        }
        $requests = $db->table('service_requests r')
            ->select('r.request_id, r.customer_id, r.first_name, r.last_name, r.email, r.phone_number, r.service_type, r.preferred_date, r.address, r.message, r.status, r.created_at, a.assignment_id, a.employee_id AS assigned_employee_id, a.scheduled_date, a.scheduled_time, a.status AS assignment_status, e.first_name AS technician_first_name, e.last_name AS technician_last_name')
            ->join('service_assignments a', 'a.request_id = r.request_id', 'left')
            ->join('employees e', 'e.employee_id = a.employee_id', 'left')
            ->orderBy('r.created_at', 'DESC')->get()->getResultArray();
        $monthInput = (string) $this->request->getGet('month');
        if (! preg_match('/^\d{4}-\d{2}$/', $monthInput)) $monthInput = date('Y-m');
        $monthStart = \DateTimeImmutable::createFromFormat('!Y-m-d', $monthInput . '-01') ?: new \DateTimeImmutable('first day of this month');
        if ($monthStart->format('Y-m') !== $monthInput) $monthStart = new \DateTimeImmutable('first day of this month');
        $calendarCells = array_fill(0, (int) $monthStart->format('N') - 1, null);
        $dayEvents = [];
        foreach ($requests as $row) {
            $eventDate = $row['scheduled_date'] ?: $row['preferred_date'];
            if ($eventDate) $dayEvents[$eventDate][] = $row;
        }
        for ($day = 1; $day <= (int) $monthStart->format('t'); $day++) {
            $date = $monthStart->setDate((int) $monthStart->format('Y'), (int) $monthStart->format('m'), $day)->format('Y-m-d');
            $calendarCells[] = ['date' => $date, 'day' => $day, 'events' => $dayEvents[$date] ?? []];
        }
        while (count($calendarCells) % 7 !== 0) $calendarCells[] = null;
        $calendarWeeks = array_chunk($calendarCells, 7);
        $checkins = $db->table('technician_checkins c')->select('c.checked_in_at, c.latitude, c.longitude, a.request_id, e.first_name, e.last_name')
            ->join('service_assignments a', 'a.assignment_id = c.assignment_id')->join('employees e', 'e.employee_id = c.employee_id')
            ->orderBy('c.checked_in_at', 'DESC')->limit(50)->get()->getResultArray();
        $categories = $db->table('service_categories')->orderBy('category_id')->get()->getResultArray();
        $technicians = $db->table('employees')->select('employee_id, first_name, last_name, department')->whereIn('department', self::FIELD_DEPARTMENTS)->whereIn('role', ['employee', 'technician'])->where('is_active', 1)->orderBy('first_name')->get()->getResultArray();
        $reports = $db->table('technician_reports r')->select('r.report_id, r.assignment_id, r.employee_id, r.category, r.description, r.status, r.created_at, e.first_name, e.last_name, s.request_id, s.service_type')
            ->join('employees e', 'e.employee_id = r.employee_id')->join('service_assignments a', 'a.assignment_id = r.assignment_id')->join('service_requests s', 's.request_id = a.request_id')->orderBy('r.created_at', 'DESC')->get()->getResultArray();
        $tickets = $db->table('support_tickets t')->select('t.ticket_id, t.customer_id, t.subject, t.status, t.created_at, t.updated_at, c.first_name, c.last_name')
            ->join('customers c', 'c.customer_id = t.customer_id')->orderBy('t.updated_at', 'DESC')->get()->getResultArray();
        $ticketMessages = [];
        if ($tickets !== []) {
            foreach ($db->table('support_messages')->whereIn('ticket_id', array_column($tickets, 'ticket_id'))->orderBy('sent_at')->get()->getResultArray() as $message) {
                $ticketMessages[$message['ticket_id']][] = $message;
            }
        }
        $payments = $db->table('service_payments p')->select('p.payment_id, p.request_id, p.amount, p.method, p.reference, p.receipt_number, p.paid_at, r.first_name, r.last_name, r.service_type')
            ->join('service_requests r', 'r.request_id = p.request_id')->orderBy('p.paid_at', 'DESC')->limit(50)->get()->getResultArray();
        $monthKeys = [];
        for ($offset = 5; $offset >= 0; $offset--) $monthKeys[] = date('Y-m', strtotime('-' . $offset . ' months'));
        $periodStart = $monthKeys[0] . '-01';
        $requestTrendRows = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM service_requests WHERE created_at >= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month", [$periodStart])->getResultArray();
        $customerTrendRows = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM customers WHERE created_at >= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month", [$periodStart])->getResultArray();
        $revenueTrendRows = $db->query("SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total FROM service_payments WHERE paid_at >= ? GROUP BY DATE_FORMAT(paid_at, '%Y-%m') ORDER BY month", [$periodStart])->getResultArray();
        $requestTrendByMonth = array_column($requestTrendRows, 'total', 'month');
        $customerTrendByMonth = array_column($customerTrendRows, 'total', 'month');
        $revenueTrendByMonth = array_column($revenueTrendRows, 'total', 'month');
        $requestTrend = [];
        $customerTrend = [];
        $monthlyRevenue = [];
        foreach ($monthKeys as $monthKey) {
            $requestTrend[] = ['month' => $monthKey, 'total' => (int) ($requestTrendByMonth[$monthKey] ?? 0)];
            $customerTrend[] = ['month' => $monthKey, 'total' => (int) ($customerTrendByMonth[$monthKey] ?? 0)];
            $monthlyRevenue[] = ['month' => $monthKey, 'total' => (float) ($revenueTrendByMonth[$monthKey] ?? 0)];
        }
        $customerRecords = $db->table('customers')
            ->select('customer_id, first_name, middle_name, last_name, birthdate, gender, email, phone_number, address, username, created_at')
            ->orderBy('customer_id', 'ASC')->get()->getResultArray();
        $serviceUsage = $db->table('service_requests')->select('service_type, COUNT(*) AS total')->groupBy('service_type')->orderBy('service_type')->get()->getResultArray();
        $requestStatusSummary = $db->table('service_requests')->select('status, COUNT(*) AS total')->groupBy('status')->orderBy('status')->get()->getResultArray();
        $customerActivity = $db->table('service_requests r')->select('r.customer_id, r.first_name, r.last_name, COUNT(r.request_id) AS total_requests')
            ->where('r.customer_id >', 0)->groupBy('r.customer_id, r.first_name, r.last_name')->orderBy('total_requests', 'DESC')->limit(10)->get()->getResultArray();
        $technicianWorkload = $db->table('employees e')
            ->select("e.employee_id, e.first_name, e.last_name, e.department, COUNT(a.assignment_id) AS assigned_jobs")
            ->join('service_assignments a', "a.employee_id = e.employee_id AND a.status IN ('Assigned', 'In Progress')", 'left')
            ->whereIn('e.department', self::FIELD_DEPARTMENTS)->whereIn('e.role', ['employee', 'technician'])->where('e.is_active', 1)
            ->groupBy('e.employee_id, e.first_name, e.last_name, e.department')->orderBy('assigned_jobs', 'DESC')->orderBy('e.first_name')->get()->getResultArray();
        $notifications = $db->table('notifications')->orderBy('created_at', 'DESC')->limit(15)->get()->getResultArray();
        $paymentRecordCount = $db->table('service_payments')->countAllResults();
        $recordedRevenue = (float) ($db->table('service_payments')->selectSum('amount', 'total')->get()->getRow()->total ?? 0);

        return view('admin_dashboard', [
            'employees' => $employees, 'requests' => $requests, 'categories' => $categories,
            'calendarWeeks' => $calendarWeeks, 'calendarMonth' => $monthStart->format('F Y'),
            'previousMonth' => $monthStart->modify('-1 month')->format('Y-m'), 'nextMonth' => $monthStart->modify('+1 month')->format('Y-m'),
            'checkins' => $checkins,
            'technicians' => $technicians, 'reports' => $reports, 'tickets' => $tickets,
            'ticketMessages' => $ticketMessages, 'payments' => $payments,
            'monthlyRevenue' => $monthlyRevenue, 'requestTrend' => $requestTrend, 'customerTrend' => $customerTrend,
            'customerRecords' => $customerRecords, 'serviceUsage' => $serviceUsage,
            'requestStatusSummary' => $requestStatusSummary, 'paymentRecordCount' => $paymentRecordCount, 'recordedRevenue' => $recordedRevenue,
            'customerActivity' => $customerActivity, 'technicianWorkload' => $technicianWorkload,
            'notifications' => $notifications, 'customerCount' => $db->table('customers')->countAllResults(),
            'adminName' => (string) session()->get('account_name'), 'adminDepartment' => $adminDepartment,
            'currentAdminId' => $currentAdminId,
            'notice' => session()->getFlashdata('notice'), 'errors' => session()->getFlashdata('errors') ?? [],
            'temporaryPassword' => session()->getFlashdata('temporaryPassword'),
        ]);
    }

    public function registerEmployee()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        return view('employee_registration', ['errors' => [], 'old' => []]);
    }

    public function createEmployee()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $input = $this->request->getPost();
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]', 'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|max_length[100]', 'phone_number' => 'permit_empty|regex_match[/^09[0-9]{9}$/]',
            'department' => 'required', 'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[8]|max_length[72]', 'confirm_password' => 'required|matches[password]',
        ];
        if (! $this->validate($rules) || ! in_array($input['department'] ?? '', self::DEPARTMENTS, true)) {
            $errors = $this->validator->getErrors();
            if (! in_array($input['department'] ?? '', self::DEPARTMENTS, true)) $errors['department'] = 'Select a valid department.';
            return view('employee_registration', ['errors' => array_values($errors), 'old' => $input]);
        }
        try {
            db_connect()->table('employees')->insert([
                'first_name' => trim((string) $input['first_name']), 'middle_name' => trim((string) ($input['middle_name'] ?? '')) ?: null,
                'last_name' => trim((string) $input['last_name']), 'email' => strtolower(trim((string) $input['email'])),
                'phone_number' => trim((string) ($input['phone_number'] ?? '')) ?: null, 'department' => $input['department'],
                'username' => trim((string) $input['username']), 'password' => password_hash((string) $input['password'], PASSWORD_DEFAULT),
                'role' => in_array($input['department'], self::FIELD_DEPARTMENTS, true) ? 'technician' : 'employee', 'is_active' => 1,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Admin employee creation failed (database error code {code}).', ['code' => (int) $e->getCode()]);
            $duplicate = (int) $e->getCode() === 1062 || str_contains(strtolower($e->getMessage()), 'duplicate');
            return view('employee_registration', ['errors' => [$duplicate ? 'That email address or username is already in use.' : 'Unable to create the employee account. Please try again.'], 'old' => $input]);
        }
        return redirect()->to(site_url('admin/dashboard#employees'))->with('notice', 'Employee account created successfully.');
    }

    public function updateEmployee()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $id = (int) $this->request->getPost('employee_id');
        $input = $this->request->getPost();
        if ($id < 1 || ! $this->validate(['first_name' => 'required|min_length[2]|max_length[50]', 'last_name' => 'required|min_length[2]|max_length[50]', 'email' => 'required|valid_email|max_length[100]', 'username' => 'required|min_length[3]|max_length[50]', 'phone_number' => 'permit_empty|regex_match[/^09[0-9]{9}$/]']) || ! in_array($input['department'] ?? '', self::DEPARTMENTS, true)) {
            return $this->backWithErrors($this->validator->getErrors() + ['employee' => 'Check the employee details.']);
        }
        $db = db_connect();
        $target = $db->table('employees')->where('employee_id', $id)->get()->getRowArray();
        if (! $target || $target['role'] === 'admin') return $this->backWithErrors(['employee' => 'Administrator accounts cannot be edited here.']);
        $email = strtolower(trim((string) $input['email']));
        if ($db->table('employees')->groupStart()->where('email', $email)->orWhere('username', trim((string) $input['username']))->groupEnd()->where('employee_id !=', $id)->countAllResults() > 0) {
            return $this->backWithErrors(['employee' => 'That email address or username is already in use.']);
        }
        $db->table('employees')->where('employee_id', $id)->update([
            'first_name' => trim((string) $input['first_name']), 'last_name' => trim((string) $input['last_name']),
            'email' => $email, 'username' => trim((string) $input['username']), 'phone_number' => trim((string) ($input['phone_number'] ?? '')) ?: null,
            'department' => $input['department'],
        ]);
        return redirect()->to(site_url('admin/dashboard#employees'))->with('notice', 'Employee details updated.');
    }

    public function deactivateEmployee()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $id = (int) $this->request->getPost('employee_id');
        if ($id < 1 || $id === (int) session()->get('account_id')) return $this->backWithErrors(['employee' => 'You cannot deactivate your own account.']);
        $employee = db_connect()->table('employees')->where('employee_id', $id)->get()->getRowArray();
        if (! $employee || $employee['role'] === 'admin') return $this->backWithErrors(['employee' => 'Administrator accounts cannot be deactivated here.']);
        db_connect()->table('employees')->where('employee_id', $id)->update(['is_active' => 0]);
        return redirect()->to(site_url('admin/dashboard#employees'))->with('notice', 'Employee account deactivated. Existing records were kept.');
    }

    public function resetEmployeePassword()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;

        $employeeId = (int) $this->request->getPost('employee_id');
        if ($employeeId < 1) return $this->backWithErrors(['employee' => 'Choose a valid employee account.']);

        try {
            $db = db_connect();
            $employee = $db->table('employees')->select('employee_id, role, is_active')
                ->where('employee_id', $employeeId)->get()->getRowArray();
            if (! $employee || (int) $employee['employee_id'] === (int) session()->get('account_id') || (int) $employee['is_active'] !== 1) {
                return $this->backWithErrors(['employee' => 'Password reset is available for other active employee accounts only.']);
            }

            $temporaryPassword = bin2hex(random_bytes(12));
            $saved = $db->table('employees')->where('employee_id', $employeeId)->update([
                'password' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
            ]);
            if (! $saved || $db->affectedRows() !== 1) {
                return $this->backWithErrors(['employee' => 'The new password was not saved. Please try again.']);
            }
        } catch (Throwable $e) {
            log_message('error', 'Employee password reset failed (database error code {code}).', ['code' => (int) $e->getCode()]);
            return $this->backWithErrors(['employee' => 'Unable to reset that employee password. Please try again.']);
        }

        return redirect()->to(site_url('admin/dashboard#employees'))
            ->with('notice', 'Password reset. Copy the new password now; it will not be shown again.')
            ->with('temporaryPassword', $temporaryPassword);
    }

    public function manageRequest()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $db = db_connect();
        $requestId = (int) $this->request->getPost('request_id');
        $request = $db->table('service_requests')->where('request_id', $requestId)->get()->getRowArray();
        if (! $request) return $this->backWithErrors(['request' => 'The service request was not found.']);
        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['Pending', 'Confirmed', 'Completed', 'Cancelled'], true)) return $this->backWithErrors(['request' => 'Choose a valid request status.']);
        $date = trim((string) $this->request->getPost('scheduled_date'));
        $time = trim((string) $this->request->getPost('scheduled_time'));
        if ($date !== '' && ! $this->validate(['scheduled_date' => 'valid_date[Y-m-d]'])) return $this->backWithErrors(['request' => 'Enter a valid service date.']);
        $employeeId = $status === 'Cancelled' ? 0 : (int) $this->request->getPost('employee_id');
        if ($employeeId > 0 && $status === 'Pending') return $this->backWithErrors(['request' => 'Approve the request before assigning a technician.']);
        $technician = null;
        if ($employeeId > 0 && $status !== 'Cancelled') {
            $technician = $db->table('employees')->where('employee_id', $employeeId)->whereIn('role', ['employee', 'technician'])->where('is_active', 1)->whereIn('department', self::FIELD_DEPARTMENTS)->get()->getRowArray();
            if (! $technician) return $this->backWithErrors(['request' => 'Select an active field service employee.']);
        }
        if ($time !== '' && ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) return $this->backWithErrors(['request' => 'Enter a valid schedule time.']);

        $db->transStart();
        $db->table('service_requests')->where('request_id', $requestId)->update([
            'status' => $status, 'preferred_date' => $date !== '' ? $date : ($request['preferred_date'] ?: null),
        ]);
        if ($employeeId > 0) {
            $assignment = $db->table('service_assignments')->where('request_id', $requestId)->get()->getRowArray();
            $assignmentData = [
                'employee_id' => $employeeId, 'assigned_by' => (int) session()->get('account_id'),
                'scheduled_date' => $date !== '' ? $date : ($request['preferred_date'] ?: null),
                'scheduled_time' => $time !== '' ? $time : null,
                'status' => $status === 'Completed' ? 'Completed' : 'Assigned', 'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($assignment) $db->table('service_assignments')->where('assignment_id', $assignment['assignment_id'])->update($assignmentData);
            else $db->table('service_assignments')->insert($assignmentData + ['request_id' => $requestId]);
            $db->table('notifications')->insert(['audience' => 'employee', 'recipient_id' => $employeeId, 'notification_type' => 'assignment', 'title' => 'Service job assigned', 'message' => 'A service request #' . $requestId . ' was assigned to you.']);
        }
        if ($status === 'Completed' || $status === 'Cancelled') {
            $db->table('service_assignments')->where('request_id', $requestId)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        if ($request['customer_id']) {
            $preferences = $db->table('customer_preferences')->where('customer_id', $request['customer_id'])->get()->getRowArray();
            $type = $status === 'Cancelled' ? 'schedule' : ($status === 'Confirmed' ? 'reminder' : 'update');
            if ($type !== 'reminder' || $preferences === null || (int) $preferences['reminders_enabled'] === 1) {
                $db->table('notifications')->insert(['audience' => 'customer', 'recipient_id' => $request['customer_id'], 'notification_type' => $type, 'title' => 'Service request updated', 'message' => 'Request #' . $requestId . ' status: ' . $status . ($date !== '' ? '; scheduled date: ' . $date : '') . '.']);
            }
        }
        $db->transComplete();
        if (! $db->transStatus()) return $this->backWithErrors(['request' => 'The request could not be updated.']);
        return redirect()->to(site_url('admin/dashboard#schedule'))->with('notice', 'Request schedule and status updated.');
    }

    public function recordPayment()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $requestId = (int) $this->request->getPost('request_id');
        $amount = (string) $this->request->getPost('amount');
        $method = (string) $this->request->getPost('method');
        if (! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amount) || (float) $amount <= 0 || ! in_array($method, ['Cash', 'Bank transfer', 'Other'], true)) {
            return $this->backWithErrors(['payment' => 'Enter a valid amount and payment method.']);
        }
        $request = db_connect()->table('service_requests')->where('request_id', $requestId)->get()->getRowArray();
        if (! $request || ! $request['customer_id']) return $this->backWithErrors(['payment' => 'Payments can be recorded only for a request linked to a customer account.']);
        db_connect()->table('service_payments')->insert([
            'request_id' => $requestId, 'amount' => $amount, 'method' => $method,
            'reference' => trim((string) $this->request->getPost('reference')) ?: null,
            'receipt_number' => 'SSS-' . date('YmdHis') . '-' . random_int(100, 999),
            'recorded_by' => (int) session()->get('account_id'),
        ]);
        db_connect()->table('notifications')->insert(['audience' => 'customer', 'recipient_id' => $request['customer_id'], 'notification_type' => 'payment', 'title' => 'Payment receipt recorded', 'message' => 'A payment for service request #' . $requestId . ' has been recorded.']);
        return redirect()->to(site_url('admin/dashboard#analytics'))->with('notice', 'Payment recorded. Verify the payment offline before entering it; no payment was processed by this site.');
    }

    public function sendNotification()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $audience = (string) $this->request->getPost('audience');
        $type = (string) $this->request->getPost('notification_type');
        $title = trim((string) $this->request->getPost('title'));
        $message = trim((string) $this->request->getPost('message'));
        if (! in_array($audience, ['customer', 'employee', 'all'], true) || ! in_array($type, ['reminder', 'offer', 'update'], true) || strlen($title) < 3 || strlen($title) > 120 || strlen($message) < 5 || strlen($message) > 2000) {
            return $this->backWithErrors(['notification' => 'Check the notification audience, type, title, and message.']);
        }
        $db = db_connect();
        if (in_array($audience, ['customer', 'all'], true) && in_array($type, ['offer', 'reminder'], true)) {
            $customers = $db->table('customers c')->select('c.customer_id')->join('customer_preferences p', 'p.customer_id = c.customer_id', 'left');
            $customers->groupStart()->where($type === 'offer' ? 'p.offers_enabled' : 'p.reminders_enabled', 1)->orWhere('p.customer_id', null)->groupEnd();
            foreach ($customers->get()->getResultArray() as $customer) {
                $db->table('notifications')->insert(['audience' => 'customer', 'recipient_id' => $customer['customer_id'], 'notification_type' => $type, 'title' => $title, 'message' => $message]);
            }
            if ($audience === 'all') $db->table('notifications')->insert(['audience' => 'employee', 'notification_type' => 'update', 'title' => $title, 'message' => $message]);
        } else {
            $db->table('notifications')->insert(['audience' => $audience, 'notification_type' => $type, 'title' => $title, 'message' => $message]);
        }
        return redirect()->to(site_url('admin/dashboard#notifications'))->with('notice', 'In-app notification sent. This does not send email or SMS.');
    }

    public function saveServiceCategory()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $id = (int) $this->request->getPost('category_id');
        $price = trim((string) $this->request->getPost('price'));
        $availability = trim((string) $this->request->getPost('availability'));
        $active = (string) $this->request->getPost('is_active');
        if ($id < 1 || ($price !== '' && ! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $price)) || strlen($availability) > 255 || ! in_array($active, ['0', '1'], true)) {
            return $this->backWithErrors(['service' => 'Enter a valid price, availability note, and active setting.']);
        }
        db_connect()->table('service_categories')->where('category_id', $id)->update([
            'price' => $price !== '' ? $price : null, 'availability' => $availability ?: null,
            'is_active' => (int) $active, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('admin/dashboard#settings'))->with('notice', 'Service settings saved.');
    }

    public function createServiceCategory()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $name = trim((string) $this->request->getPost('service_type'));
        if (strlen($name) < 2 || strlen($name) > 30 || ! preg_match('/^[\pL\pN &-]+$/u', $name)) {
            return $this->backWithErrors(['service' => 'Use a 2–30 character service name with letters, numbers, spaces, ampersands, or hyphens.']);
        }
        $exists = db_connect()->table('service_categories')->where('service_type', $name)->countAllResults();
        if ($exists > 0) return $this->backWithErrors(['service' => 'That service category already exists.']);
        db_connect()->table('service_categories')->insert(['service_type' => $name]);
        return redirect()->to(site_url('admin/dashboard#settings'))->with('notice', 'Service category added. Set its price and availability below.');
    }

    public function replyTicket()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $ticketId = (int) $this->request->getPost('ticket_id');
        $message = trim((string) $this->request->getPost('message'));
        $status = (string) $this->request->getPost('status');
        $ticket = db_connect()->table('support_tickets')->where('ticket_id', $ticketId)->get()->getRowArray();
        if (! $ticket || strlen($message) < 1 || strlen($message) > 3000 || ! in_array($status, ['Answered', 'Closed'], true)) return $this->backWithErrors(['ticket' => 'Check the ticket reply and status.']);
        db_connect()->table('support_messages')->insert(['ticket_id' => $ticketId, 'sender_type' => 'employee', 'sender_id' => (int) session()->get('account_id'), 'message' => $message]);
        db_connect()->table('support_tickets')->where('ticket_id', $ticketId)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        db_connect()->table('notifications')->insert(['audience' => 'customer', 'recipient_id' => $ticket['customer_id'], 'notification_type' => 'support', 'title' => 'Support ticket updated', 'message' => 'Your support ticket #' . $ticketId . ' has a reply.']);
        return redirect()->to(site_url('admin/dashboard#support'))->with('notice', 'Ticket reply saved.');
    }

    public function updateReport()
    {
        $denied = $this->requireAdmin();
        if ($denied !== null) return $denied;
        $reportId = (int) $this->request->getPost('report_id');
        $status = (string) $this->request->getPost('status');
        if ($reportId < 1 || ! in_array($status, ['Open', 'In Review', 'Resolved'], true)) return $this->backWithErrors(['report' => 'Choose a valid report status.']);
        db_connect()->table('technician_reports')->where('report_id', $reportId)->update(['status' => $status]);
        return redirect()->to(site_url('admin/dashboard#issues'))->with('notice', 'Technician report status updated.');
    }

    private function requireAdmin()
    {
        if (session()->get('account_type') !== 'employee' || ! session()->get('account_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Sign in with an administrator account to continue.');
        }
        $admin = db_connect()->table('employees')->select('employee_id')->where('employee_id', (int) session()->get('account_id'))->where('role', 'admin')->where('is_active', 1)->get()->getRowArray();
        if ($admin === null) return $this->response->setStatusCode(403)->setBody('You are not authorized to view this page.');
        return null;
    }

    private function backWithErrors(array $errors)
    {
        return redirect()->to(site_url('admin/dashboard'))->withInput()->with('errors', $errors)->with('notice', 'Please check the information and try again.');
    }
}
