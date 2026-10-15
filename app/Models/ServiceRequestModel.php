<?php
namespace App\Models;

use CodeIgniter\Model;

class ServiceRequestModel extends Model
{
    protected $table = 'service_requests';
    protected $primaryKey = 'request_id';
    protected $allowedFields = ['first_name','last_name','email','phone_number','service_type','preferred_date','address','message'];
    protected $useTimestamps = false;

    public function recentRequests(): array
    {
        return $this->select('request_id, first_name, last_name, email, phone_number, service_type, preferred_date, address, message, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->findAll(100);
    }
}
