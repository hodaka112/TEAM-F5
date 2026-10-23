<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerOwnershipToServiceRequests extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('customer_id', 'service_requests')) {
            $this->forge->addColumn('service_requests', [
                'customer_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'request_id',
                ],
            ]);
        }

        $index = $this->db->query("SHOW INDEX FROM service_requests WHERE Key_name = 'idx_service_requests_customer_id'")->getResultArray();
        if ($index === []) {
            $this->db->query('CREATE INDEX idx_service_requests_customer_id ON service_requests (customer_id)');
        }

        $foreignKey = $this->db->query(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_requests'
             AND COLUMN_NAME = 'customer_id' AND REFERENCED_TABLE_NAME = 'customers'"
        )->getResultArray();
        if ($foreignKey === []) {
            $this->db->query(
                'ALTER TABLE service_requests ADD CONSTRAINT fk_service_requests_customer_id '
                . 'FOREIGN KEY (customer_id) REFERENCES customers (customer_id) ON DELETE SET NULL ON UPDATE CASCADE'
            );
        }
    }

    public function down()
    {
        // Keep the nullable ownership column and its values to avoid losing request links.
        $foreignKey = $this->db->query(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_requests'
             AND CONSTRAINT_NAME = 'fk_service_requests_customer_id'"
        )->getResultArray();
        if ($foreignKey !== []) {
            $this->db->query('ALTER TABLE service_requests DROP FOREIGN KEY fk_service_requests_customer_id');
        }

        $index = $this->db->query("SHOW INDEX FROM service_requests WHERE Key_name = 'idx_service_requests_customer_id'")->getResultArray();
        if ($index !== []) {
            $this->db->query('DROP INDEX idx_service_requests_customer_id ON service_requests');
        }
    }
}
