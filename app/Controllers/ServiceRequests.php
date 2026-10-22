<?php
namespace App\Controllers;

use App\Models\ServiceRequestModel;
use App\Models\AccountModel;
use CodeIgniter\Controller;
use Throwable;

class ServiceRequests extends Controller
{
    public function form(): string
    {
        $requested = (string) $this->request->getGet('service');
        $categories = db_connect()->table('service_categories')->where('is_active', 1)->orderBy('category_id')->get()->getResultArray();
        $allowed = array_column($categories, 'service_type');
        $customer = null;
        if (session()->get('account_type') === 'customer' && session()->get('account_id')) {
            $customer = (new AccountModel())->customerById((int) session()->get('account_id'));
        }
        return view('service_requests/form', [
            'service' => in_array($requested, $allowed, true) ? $requested : ($allowed[0] ?? ''),
            'categories' => $categories,
            'customer' => $customer,
            'notice' => session()->getFlashdata('notice'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function create()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]', 'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|max_length[100]', 'phone_number' => 'required|regex_match[/^09[0-9]{9}$/]',
            'service_type' => 'required|in_list[' . implode(',', array_column(db_connect()->table('service_categories')->where('is_active', 1)->get()->getResultArray(), 'service_type')) . ']',
            'preferred_date' => 'permit_empty|valid_date[Y-m-d]', 'address' => 'required|min_length[5]', 'message' => 'permit_empty|max_length[2000]',
        ];
        if ($this->request->getPost('service_type') === 'Maintenance') {
            $rules['preferred_date'] = 'required|valid_date[Y-m-d]';
        }
        if (! $this->validate($rules)) {
            return redirect()->to(site_url('requests'))->withInput()->with('notice', 'Please check the request form and correct the highlighted details.')->with('errors', $this->validator->getErrors());
        }
        $date = $this->request->getPost('preferred_date');
        if ($date && $date < date('Y-m-d')) {
            return redirect()->to(site_url('requests'))->withInput()->with('notice', 'Choose today or a future date for your service request.');
        }
        try {
            $model = new ServiceRequestModel();
            $submittedEmail = strtolower(trim((string) $this->request->getPost('email')));
            $customerId = null;
            if (session()->get('account_type') === 'customer'
                && session()->get('account_email') !== null
                && $submittedEmail === strtolower((string) session()->get('account_email'))) {
                $customerId = (int) session()->get('account_id');
            }
            $saved = $model->insert([
                'customer_id' => $customerId,
                'first_name' => trim((string) $this->request->getPost('first_name')),
                'last_name' => trim((string) $this->request->getPost('last_name')),
                'email' => $submittedEmail,
                'phone_number' => $this->request->getPost('phone_number'), 'service_type' => $this->request->getPost('service_type'),
                'preferred_date' => $date ?: null, 'address' => trim((string) $this->request->getPost('address')),
                'message' => trim((string) $this->request->getPost('message')) ?: null,
            ]);
            if (! $saved) throw new \RuntimeException('Database rejected the service request.');
            return redirect()->to(site_url('requests'))->with('notice', 'Your request has been submitted successfully. Our team will contact you.');
        } catch (Throwable $e) {
            log_message('error', 'Service request could not be saved (database error code {code}).', ['code' => (int) $e->getCode()]);
            return redirect()->to(site_url('requests'))->withInput()->with('notice', 'We could not save your request. Please try again later.');
        }
    }
}
