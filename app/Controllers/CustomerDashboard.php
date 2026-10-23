<?php

namespace App\Controllers;

use App\Models\AccountModel;
use CodeIgniter\Controller;

class CustomerDashboard extends Controller
{
    public function index()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $customer;
        }

        $db = db_connect();
        $id = (int) $customer['customer_id'];
        $requests = $db->table('service_requests')->where('customer_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();
        $payments = $db->table('service_payments p')
            ->select('p.payment_id, p.request_id, p.amount, p.method, p.reference, p.receipt_number, p.paid_at, r.service_type')
            ->join('service_requests r', 'r.request_id = p.request_id')
            ->where('r.customer_id', $id)->orderBy('p.paid_at', 'DESC')->get()->getResultArray();
        $notifications = $db->table('notifications')
            ->groupStart()->where('audience', 'all')->orGroupStart()->where('audience', 'customer')->groupStart()->where('recipient_id', $id)->orWhere('recipient_id', null)->groupEnd()->groupEnd()->groupEnd()
            ->orderBy('created_at', 'DESC')->limit(50)->get()->getResultArray();
        $preferences = $db->table('customer_preferences')->where('customer_id', $id)->get()->getRowArray();
        if ($preferences === null) {
            $preferences = ['reminders_enabled' => 1, 'offers_enabled' => 1, 'preferred_contact' => 'email'];
        }
        $tickets = $db->table('support_tickets')->where('customer_id', $id)->orderBy('updated_at', 'DESC')->get()->getResultArray();
        $messages = [];
        if ($tickets !== []) {
            $ticketIds = array_column($tickets, 'ticket_id');
            foreach ($db->table('support_messages')->whereIn('ticket_id', $ticketIds)->orderBy('sent_at', 'ASC')->get()->getResultArray() as $message) {
                $messages[$message['ticket_id']][] = $message;
            }
        }

        return view('customer_dashboard', [
            'customer' => $customer,
            'requests' => $requests,
            'payments' => $payments,
            'notifications' => $notifications,
            'preferences' => $preferences,
            'tickets' => $tickets,
            'ticketMessages' => $messages,
            'notice' => session()->getFlashdata('notice'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function updateProfile()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        $id = (int) $customer['customer_id'];
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'middle_name' => 'permit_empty|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|max_length[100]',
            'phone_number' => 'required|regex_match[/^09[0-9]{9}$/]',
            'address' => 'required|min_length[5]|max_length[1000]',
        ];
        if (! $this->validate($rules)) return $this->backWithErrors($this->validator->getErrors());
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $duplicate = db_connect()->table('customers')->where('email', $email)->where('customer_id !=', $id)->countAllResults();
        if ($duplicate > 0) return $this->backWithErrors(['email' => 'That email address is already in use.']);
        db_connect()->table('customers')->where('customer_id', $id)->update([
            'first_name' => trim((string) $this->request->getPost('first_name')),
            'middle_name' => trim((string) $this->request->getPost('middle_name')) ?: null,
            'last_name' => trim((string) $this->request->getPost('last_name')),
            'email' => $email,
            'phone_number' => trim((string) $this->request->getPost('phone_number')),
            'address' => trim((string) $this->request->getPost('address')),
        ]);
        session()->set('account_name', trim((string) $this->request->getPost('first_name')));
        session()->set('account_email', $email);
        return redirect()->to(site_url('customer/dashboard#profile'))->with('notice', 'Your profile was updated.');
    }

    public function cancelRequest()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        $requestId = (int) $this->request->getPost('request_id');
        $db = db_connect();
        $request = $db->table('service_requests')->where('request_id', $requestId)->where('customer_id', (int) $customer['customer_id'])->where('status', 'Pending')->get()->getRowArray();
        if (! $request) return $this->backWithErrors(['request' => 'Only your own pending requests can be cancelled here. Contact support for a confirmed service.']);
        $db->table('service_requests')->where('request_id', $requestId)->where('customer_id', (int) $customer['customer_id'])->update(['status' => 'Cancelled']);
        $db->table('service_assignments')->where('request_id', $requestId)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
        foreach ($db->table('employees')->select('employee_id')->where('role', 'admin')->where('is_active', 1)->get()->getResultArray() as $admin) {
            $db->table('notifications')->insert(['audience' => 'employee', 'recipient_id' => $admin['employee_id'], 'notification_type' => 'schedule', 'title' => 'Customer cancelled a request', 'message' => 'A customer cancelled service request #' . $requestId . '.']);
        }
        return redirect()->to(site_url('customer/dashboard#services'))->with('notice', 'Your pending request was cancelled.');
    }

    public function changePassword()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        $model = new AccountModel();
        $stored = $model->customerPasswordById((int) $customer['customer_id']);
        $current = (string) $this->request->getPost('current_password');
        $new = (string) $this->request->getPost('new_password');
        if (! $stored || ! password_verify($current, $stored) || strlen($new) < 8 || strlen($new) > 72 || $new !== (string) $this->request->getPost('confirm_password')) {
            return $this->backWithErrors(['password' => 'Check your current password and make sure the new password matches and is at least 8 characters.']);
        }
        db_connect()->table('customers')->where('customer_id', (int) $customer['customer_id'])->update(['password' => password_hash($new, PASSWORD_DEFAULT)]);
        return redirect()->to(site_url('customer/dashboard#profile'))->with('notice', 'Your password was changed.');
    }

    public function savePreferences()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        $reminders = (string) $this->request->getPost('reminders_enabled');
        $offers = (string) $this->request->getPost('offers_enabled');
        $contact = (string) $this->request->getPost('preferred_contact');
        if (! in_array($reminders, ['0', '1'], true) || ! in_array($offers, ['0', '1'], true) || ! in_array($contact, ['email', 'phone'], true)) {
            return $this->backWithErrors(['preferences' => 'Choose valid notification preferences.']);
        }
        db_connect()->table('customer_preferences')->replace([
            'customer_id' => (int) $customer['customer_id'], 'reminders_enabled' => (int) $reminders,
            'offers_enabled' => (int) $offers, 'preferred_contact' => $contact, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('customer/dashboard#profile'))->with('notice', 'Your preferences were saved.');
    }

    public function createTicket()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        $rules = ['subject' => 'required|min_length[3]|max_length[150]', 'message' => 'required|min_length[5]|max_length[3000]'];
        if (! $this->validate($rules)) return $this->backWithErrors($this->validator->getErrors());
        $db = db_connect();
        $db->transStart();
        $db->table('support_tickets')->insert(['customer_id' => (int) $customer['customer_id'], 'subject' => trim((string) $this->request->getPost('subject')), 'updated_at' => date('Y-m-d H:i:s')]);
        $ticketId = (int) $db->insertID();
        $db->table('support_messages')->insert(['ticket_id' => $ticketId, 'sender_type' => 'customer', 'sender_id' => (int) $customer['customer_id'], 'message' => trim((string) $this->request->getPost('message'))]);
        foreach ($db->table('employees')->select('employee_id')->where('role', 'admin')->where('is_active', 1)->get()->getResultArray() as $admin) {
            $db->table('notifications')->insert(['audience' => 'employee', 'recipient_id' => $admin['employee_id'], 'notification_type' => 'support', 'title' => 'New customer support ticket', 'message' => 'A customer submitted a support ticket #' . $ticketId . '.']);
        }
        $db->transComplete();
        if (! $db->transStatus()) return $this->backWithErrors(['ticket' => 'The support ticket could not be saved. Please try again.']);
        return redirect()->to(site_url('customer/dashboard#support'))->with('notice', 'Your support ticket was sent.');
    }

    public function replyTicket()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        $ticketId = (int) $this->request->getPost('ticket_id');
        $message = trim((string) $this->request->getPost('message'));
        $ticket = db_connect()->table('support_tickets')->where('ticket_id', $ticketId)->where('customer_id', (int) $customer['customer_id'])->get()->getRowArray();
        if (! $ticket || $ticket['status'] === 'Closed' || strlen($message) < 1 || strlen($message) > 3000) {
            return $this->backWithErrors(['ticket' => 'This ticket cannot be updated.']);
        }
        db_connect()->table('support_messages')->insert(['ticket_id' => $ticketId, 'sender_type' => 'customer', 'sender_id' => (int) $customer['customer_id'], 'message' => $message]);
        db_connect()->table('support_tickets')->where('ticket_id', $ticketId)->update(['status' => 'Open', 'updated_at' => date('Y-m-d H:i:s')]);
        foreach (db_connect()->table('employees')->select('employee_id')->where('role', 'admin')->where('is_active', 1)->get()->getResultArray() as $admin) {
            db_connect()->table('notifications')->insert(['audience' => 'employee', 'recipient_id' => $admin['employee_id'], 'notification_type' => 'support', 'title' => 'Customer replied to a support ticket', 'message' => 'A customer replied to ticket #' . $ticketId . '.']);
        }
        return redirect()->to(site_url('customer/dashboard#support'))->with('notice', 'Your reply was sent.');
    }

    public function markNotificationRead()
    {
        $customer = $this->currentCustomer();
        if ($customer instanceof \CodeIgniter\HTTP\ResponseInterface) return $customer;
        db_connect()->table('notifications')->where('notification_id', (int) $this->request->getPost('notification_id'))
            ->groupStart()->where('audience', 'all')->orGroupStart()->where('audience', 'customer')->groupStart()->where('recipient_id', (int) $customer['customer_id'])->orWhere('recipient_id', null)->groupEnd()->groupEnd()->groupEnd()
            ->update(['read_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('customer/dashboard#notifications'));
    }

    private function currentCustomer()
    {
        if (session()->get('account_type') !== 'customer' || ! session()->get('account_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Please sign in with a customer account to continue.');
        }
        $customer = (new AccountModel())->customerById((int) session()->get('account_id'));
        if ($customer === null) {
            session()->destroy();
            return redirect()->to(site_url('login'))->with('error', 'Your account could not be found. Please sign in again.');
        }
        return $customer;
    }

    private function backWithErrors(array $errors)
    {
        return redirect()->to(site_url('customer/dashboard'))->withInput()->with('errors', $errors)->with('notice', 'Please check the information and try again.');
    }
}
