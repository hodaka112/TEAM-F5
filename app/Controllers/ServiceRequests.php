<?php
namespace App\Controllers;

use App\Models\ServiceRequestModel;
use CodeIgniter\Controller;
use Throwable;

class ServiceRequests extends Controller
{
    public function form(): string
    {
        $requested = (string) $this->request->getGet('service');
        $allowed = ['Quote', 'Consultation', 'Installation', 'Maintenance'];
        return view('service_requests/form', [
            'service' => in_array($requested, $allowed, true) ? $requested : 'Quote',
            'notice' => session()->getFlashdata('notice'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function index()
    {
        if (session()->get('account_type') !== 'employee') {
            return redirect()->to(site_url('login'))->with('error', 'Sign in with an employee account to view service requests.');
        }

        return view('service_requests/index', [
            'requests' => (new ServiceRequestModel())->recentRequests(),
            'notice' => session()->getFlashdata('notice'),
        ]);
    }

    public function create()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]', 'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|max_length[100]', 'phone_number' => 'required|regex_match[/^09[0-9]{9}$/]',
            'service_type' => 'required|in_list[Quote,Consultation,Installation,Maintenance]',
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
            $saved = $model->insert([
                'first_name' => trim((string) $this->request->getPost('first_name')),
                'last_name' => trim((string) $this->request->getPost('last_name')),
                'email' => strtolower(trim((string) $this->request->getPost('email'))),
                'phone_number' => $this->request->getPost('phone_number'), 'service_type' => $this->request->getPost('service_type'),
                'preferred_date' => $date ?: null, 'address' => trim((string) $this->request->getPost('address')),
                'message' => trim((string) $this->request->getPost('message')) ?: null,
            ]);
            if (! $saved) throw new \RuntimeException('Database rejected the service request.');
            return redirect()->to(site_url('requests'))->with('notice', 'Your request has been received. Our team will contact you.');
        } catch (Throwable $e) {
            log_message('error', 'Service request could not be saved (database error code {code}).', ['code' => (int) $e->getCode()]);
            return redirect()->to(site_url('requests'))->withInput()->with('notice', 'We could not save your request. Please try again later.');
        }
    }
}
