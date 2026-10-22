<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Additive storage for dashboard workflows. Existing customer, employee, and
 * service request records are left in place.
 */
class CreateDashboardWorkflows extends Migration
{
    public function up()
    {
        // Widening this enum to VARCHAR preserves existing values and lets admins
        // add service categories without another schema change each time.
        $this->db->query('ALTER TABLE service_requests MODIFY service_type VARCHAR(30) NOT NULL');
        if (! $this->db->fieldExists('is_active', 'employees')) {
            $this->forge->addColumn('employees', [
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => false, 'after' => 'role'],
            ]);
        }

        $this->forge->addField([
            'category_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'service_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'availability' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('category_id', true)->addUniqueKey('service_type')->createTable('service_categories', true);

        foreach (['Quote', 'Consultation', 'Installation', 'Maintenance'] as $serviceType) {
            $this->db->table('service_categories')->ignore(true)->insert(['service_type' => $serviceType]);
        }

        $this->forge->addField([
            'assignment_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'request_id' => ['type' => 'INT', 'constraint' => 11],
            'employee_id' => ['type' => 'INT', 'constraint' => 11],
            'assigned_by' => ['type' => 'INT', 'constraint' => 11],
            'scheduled_date' => ['type' => 'DATE', 'null' => true],
            'scheduled_time' => ['type' => 'TIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Assigned', 'In Progress', 'Completed', 'Cancelled'], 'default' => 'Assigned'],
            'created_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('assignment_id', true)->addUniqueKey('request_id')->addKey('employee_id')->createTable('service_assignments', true);
        $this->addForeignKey('service_assignments', 'fk_assignments_request', 'request_id', 'service_requests', 'request_id', 'RESTRICT');
        $this->addForeignKey('service_assignments', 'fk_assignments_employee', 'employee_id', 'employees', 'employee_id', 'RESTRICT');
        $this->addForeignKey('service_assignments', 'fk_assignments_admin', 'assigned_by', 'employees', 'employee_id', 'RESTRICT');

        $this->forge->addField([
            'notification_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'audience' => ['type' => 'ENUM', 'constraint' => ['all', 'customer', 'employee'], 'default' => 'all'],
            'recipient_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'notification_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'update'],
            'title' => ['type' => 'VARCHAR', 'constraint' => 120],
            'message' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'read_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('notification_id', true)->addKey(['audience', 'recipient_id', 'created_at'])->createTable('notifications', true);

        $this->forge->addField([
            'customer_id' => ['type' => 'INT', 'constraint' => 11],
            'reminders_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'offers_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'preferred_contact' => ['type' => 'ENUM', 'constraint' => ['email', 'phone'], 'default' => 'email'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('customer_id', true)->createTable('customer_preferences', true);
        $this->addForeignKey('customer_preferences', 'fk_preferences_customer', 'customer_id', 'customers', 'customer_id', 'RESTRICT');

        $this->forge->addField([
            'payment_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'request_id' => ['type' => 'INT', 'constraint' => 11],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'method' => ['type' => 'VARCHAR', 'constraint' => 40],
            'reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'receipt_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'paid_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'recorded_by' => ['type' => 'INT', 'constraint' => 11],
        ])->addKey('payment_id', true)->addKey('request_id')->addUniqueKey('receipt_number')->createTable('service_payments', true);
        $this->addForeignKey('service_payments', 'fk_payments_request', 'request_id', 'service_requests', 'request_id', 'RESTRICT');
        $this->addForeignKey('service_payments', 'fk_payments_admin', 'recorded_by', 'employees', 'employee_id', 'RESTRICT');

        $this->forge->addField([
            'ticket_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 11],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 150],
            'status' => ['type' => 'ENUM', 'constraint' => ['Open', 'Answered', 'Closed'], 'default' => 'Open'],
            'created_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('ticket_id', true)->addKey(['customer_id', 'created_at'])->createTable('support_tickets', true);
        $this->addForeignKey('support_tickets', 'fk_tickets_customer', 'customer_id', 'customers', 'customer_id', 'RESTRICT');

        $this->forge->addField([
            'message_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'sender_type' => ['type' => 'ENUM', 'constraint' => ['customer', 'employee']],
            'sender_id' => ['type' => 'INT', 'constraint' => 11],
            'message' => ['type' => 'TEXT'],
            'sent_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ])->addKey('message_id', true)->addKey(['ticket_id', 'sent_at'])->createTable('support_messages', true);
        $this->addForeignKey('support_messages', 'fk_messages_ticket', 'ticket_id', 'support_tickets', 'ticket_id', 'RESTRICT');

        $this->forge->addField([
            'checkin_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'assignment_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,7'],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '10,7'],
            'checked_in_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ])->addKey('checkin_id', true)->addKey(['assignment_id', 'checked_in_at'])->createTable('technician_checkins', true);
        $this->addForeignKey('technician_checkins', 'fk_checkins_assignment', 'assignment_id', 'service_assignments', 'assignment_id', 'RESTRICT');
        $this->addForeignKey('technician_checkins', 'fk_checkins_employee', 'employee_id', 'employees', 'employee_id', 'RESTRICT');

        $this->forge->addField([
            'report_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'assignment_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT'],
            'status' => ['type' => 'ENUM', 'constraint' => ['Open', 'In Review', 'Resolved'], 'default' => 'Open'],
            'created_at' => ['type' => 'DATETIME', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ])->addKey('report_id', true)->addKey(['status', 'created_at'])->createTable('technician_reports', true);
        $this->addForeignKey('technician_reports', 'fk_reports_assignment', 'assignment_id', 'service_assignments', 'assignment_id', 'RESTRICT');
        $this->addForeignKey('technician_reports', 'fk_reports_employee', 'employee_id', 'employees', 'employee_id', 'RESTRICT');
    }

    public function down()
    {
        // Workflow history and account active flags are retained on rollback.
    }

    private function addForeignKey(string $table, string $name, string $column, string $referencedTable, string $referencedColumn, string $onDelete): void
    {
        $exists = $this->db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$table, $name]
        )->getRowArray();
        if ($exists !== null) return;

        $this->db->query("ALTER TABLE {$table} ADD CONSTRAINT {$name} FOREIGN KEY ({$column}) REFERENCES {$referencedTable}({$referencedColumn}) ON DELETE {$onDelete} ON UPDATE CASCADE");
    }
}
