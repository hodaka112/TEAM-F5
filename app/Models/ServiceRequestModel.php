<?php
namespace App\Models;

use CodeIgniter\Model;

class ServiceRequestModel extends Model
{
    protected $table = 'service_requests';
    protected $primaryKey = 'request_id';
    protected $allowedFields = ['customer_id','first_name','last_name','email','phone_number','service_type','preferred_date','address','message'];
    protected $useTimestamps = false;

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
