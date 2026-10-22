-- Select the existing database
USE team3_db;

-- Store customer payment records
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
    payment_method ENUM('Cash','GCash','Bank Transfer','Card','Other') DEFAULT NULL,
    payment_date DATETIME DEFAULT NULL,
    reference_number VARCHAR(100) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Assign service requests to technicians
CREATE TABLE IF NOT EXISTS service_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    employee_id INT NOT NULL,
    assigned_by INT DEFAULT NULL,
    assignment_status ENUM('Assigned','In Progress','Completed','Reassigned','Cancelled') DEFAULT 'Assigned',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_by) REFERENCES employees(employee_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Record technician check-ins and locations
CREATE TABLE IF NOT EXISTS technician_checkins (
    checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    employee_id INT NOT NULL,
    checkin_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (assignment_id) REFERENCES service_assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Store technician problem reports
CREATE TABLE IF NOT EXISTS problem_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT DEFAULT NULL,
    employee_id INT NOT NULL,
    report_type ENUM('Equipment Issue','Customer Concern','Safety Issue','Schedule Issue','Other') DEFAULT 'Other',
    description TEXT NOT NULL,
    report_status ENUM('Open','Under Review','Resolved','Closed') DEFAULT 'Open',
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    resolution_notes TEXT DEFAULT NULL,
    FOREIGN KEY (assignment_id) REFERENCES service_assignments(assignment_id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

SHOW TABLES;