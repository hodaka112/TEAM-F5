CREATE DATABASE IF NOT EXISTS team3_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE team3_db;

-- Public quote, consultation, installation, and maintenance requests.
-- This additive table does not alter existing customer or employee records.
CREATE TABLE IF NOT EXISTS service_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    service_type ENUM('Quote','Consultation','Installation','Maintenance') NOT NULL,
    preferred_date DATE NULL,
    address TEXT NOT NULL,
    message TEXT,
    status ENUM('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_requests_status_created (status, created_at)
);

CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    birthdate DATE,
    gender ENUM('Male','Female','Other','Prefer not to say'),
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(15),
    address TEXT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    birthdate DATE,
    gender ENUM('Male','Female','Other','Prefer not to say'),
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(15),
    address TEXT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE customers
    MODIFY gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') NULL;

ALTER TABLE employees
    MODIFY gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') NULL;

INSERT INTO employees (
    first_name,
    middle_name,
    last_name,
    birthdate,
    gender,
    email,
    phone_number,
    address,
    username,
    password,
    department
)
VALUES (
    'Katherine',
    NULL,
    'Sinagaraw',
    '1988-04-16',
    'Female',
    'katherine@sunsonsolar.test',
    '09170000001',
    '15 Sampaguita Street, Barangay San Antonio, Pasig City',
    'KittyKat16',
    '$2y$10$pVBqD5wWSQkmBfv2uf4M.OkBzOwFdUyaVh516sZAM4YC1D.d3YzoO',
    'Management'
), (
    'Sol',
    NULL,
    'Solis',
    '1993-08-21',
    'Male',
    'sol@sunsonsolar.test',
    '09170000002',
    '28 Narra Street, Barangay Kapitolyo, Pasig City',
    'soladmin',
    '$2y$10$MiVe5ZokbipG8lHCbVEYYuCbb66368dFXi6Q.aFx6GnLksG05giyO',
    'IT'
), (
    'Apollo',
    NULL,
    'Santos',
    '1989-12-05',
    'Male',
    'apollo@sunsonsolar.test',
    '09170000003',
    '42 Acacia Street, Barangay Ugong, Pasig City',
    'apolloheadtech',
    '$2y$10$OXJRZdj3BcnTLfssu0ohZe6J8qa9TsnQ8gG/jOiXdnrz88jv55v.m',
    'Installation & Operations'
), (
    'Adrian',
    'Miguel',
    'Santos',
    '1992-03-18',
    'Male',
    'adrian.santos01@gmail.com',
    '09171234567',
    '24 Emerald Street, Barangay San Antonio, Pasig City',
    'adrians01',
    '$2y$10$YqH/3Bl95OKcD9hN5dtwf.5c1NRykXh70v50X2gAmzVK7zmKEC8Qe',
    'Management'
), (
    'Camille',
    'Rose',
    'Garcia',
    '1995-07-25',
    'Female',
    'camille.garcia02@yahoo.com',
    '09182345678',
    '18 Ortigas Avenue Extension, Barangay Rosario, Pasig City',
    'camilleg02',
    '$2y$10$ajjRCvMBg94zERKNId9CYOQWKlzYhdpACsBozqf/DyzmWrcS5Uq3K',
    'Sales & Marketing'
), (
    'Vincent',
    'Carlo',
    'Mendoza',
    '1991-11-09',
    'Male',
    'vincent.mendoza03@outlook.com',
    '09193456789',
    '37 C. Raymundo Avenue, Barangay Caniogan, Pasig City',
    'vincentm03',
    '$2y$10$yT/vpN12SZuLA7z6zQNYfu3.fYWsANYb6XQJlPE0Ow09JkNRTzYW.',
    'Engineering'
), (
    'Joshua',
    'Daniel',
    'Reyes',
    '1994-05-14',
    'Male',
    'joshua.reyes04@gmail.com',
    '09204567890',
    '52 E. Capitol Drive, Barangay Kapitolyo, Pasig City',
    'joshuar04',
    '$2y$10$AfT8bBQ.J9ThRRCt125ZJ.93cIqZClKERIGuO.6VUDYTpcPQBlNye',
    'Installation & Operations'
), (
    'Patricia',
    'Anne',
    'Cruz',
    '1996-09-21',
    'Female',
    'patricia.cruz05@yahoo.com',
    '09215678901',
    '11 Dr. Sixto Antonio Avenue, Barangay Manggahan, Pasig City',
    'patriciac05',
    '$2y$10$lQclvQTf5qBT8XVWsivLNexCotYOBQYUyWE5g8u.Mw1hL7rc7qL32',
    'Maintenance & Service'
), (
    'Kevin',
    'James',
    'Navarro',
    '1998-01-30',
    'Male',
    'kevin.navarro06@outlook.com',
    '09226789012',
    '29 Amang Rodriguez Avenue, Barangay Dela Paz, Pasig City',
    'kevinn06',
    '$2y$10$qo9.HDjH3XPh2M5dxzg1COxZoqgwE8fYQH4LeySFJ4MpB82iwU1jC',
    'IT'
)
ON DUPLICATE KEY UPDATE
    employee_id = employee_id;

INSERT INTO customers (
    first_name,
    middle_name,
    last_name,
    birthdate,
    gender,
    email,
    phone_number,
    address,
    username,
    password
)
VALUES (
    'Lara',
    'Anne',
    'Morales',
    '1999-01-15',
    'Female',
    'lara.morales01@gmail.com',
    '09361112233',
    '24 Emerald Street, Barangay San Antonio, Pasig City',
    'laram01',
    '$2y$10$FC4Oz/jAftlHNpUNjdWYfuXHkiKtienDTu4Dw3D2pbCnaDg/pZ3fK'
), (
    'Ethan',
    'James',
    'Rodriguez',
    '1998-05-22',
    'Male',
    'ethan.rodriguez02@yahoo.com',
    '09372223344',
    '18 Ortigas Avenue Extension, Barangay Rosario, Pasig City',
    'ethanr02',
    '$2y$10$HB/KBhxz5LR4xhadMIK.4e6BuBENznaR.xZ00QbSUhTmNeH9Ae2UC'
), (
    'Julia',
    'Marie',
    'Navarro',
    '2001-09-09',
    'Female',
    'julia.navarro03@outlook.com',
    '09383334455',
    '37 C. Raymundo Avenue, Barangay Caniogan, Pasig City',
    'julian03',
    '$2y$10$l8Cp06bwSeWl2JLnphGz4OmLpbL0thPIHNjZqv/h6JvIC5SjNwtRq'
), (
    'Marco',
    'Luis',
    'Delos Reyes',
    '1996-11-18',
    'Male',
    'marco.delosreyes04@gmail.com',
    '09394445566',
    '52 E. Capitol Drive, Barangay Kapitolyo, Pasig City',
    'marcod04',
    '$2y$10$oTV4NU68q8E9a.3.jKO6qecGbhhFSJaGBnxhm5TyycnfFVQNRpXJS'
), (
    'Pauline',
    'Grace',
    'Vergara',
    '2000-02-27',
    'Female',
    'pauline.vergara05@yahoo.com',
    '09405556677',
    '11 Dr. Sixto Antonio Avenue, Barangay Manggahan, Pasig City',
    'paulv05',
    '$2y$10$FD1t7C6eSrgnS1PWUjMkaendIkpBQJZTijXeTjlrZUyf.fDblGfj2'
), (
    'Rafael',
    'Miguel',
    'Soriano',
    '1997-07-13',
    'Male',
    'rafael.soriano06@outlook.com',
    '09416667788',
    '29 Amang Rodriguez Avenue, Barangay Dela Paz, Pasig City',
    'rafs06',
    '$2y$10$k2R/3A8zDJ/l1GkJPYclSejilN6Zc2BMGhwGW7uWZJPf7gx0JEwPy'
), (
    'Angelica',
    'Rose',
    'Flores',
    '2002-03-04',
    'Female',
    'angelica.flores07@gmail.com',
    '09427778899',
    '43 E. Rodriguez Jr. Avenue, Barangay Ugong, Pasig City',
    'angelf07',
    '$2y$10$r5tUWu0Ko95xM1IGsox/Se7YIqxaQSaVskqo/Lgdz/qYMrIGJzjyq'
), (
    'Nathaniel',
    'John',
    'Rivera',
    '1995-06-26',
    'Male',
    'nathaniel.rivera08@yahoo.com',
    '09438889900',
    '16 Shaw Boulevard, Barangay San Antonio, Pasig City',
    'nathr08',
    '$2y$10$fVH8XZ9SlUyYZWVSCADzKOsIor5izkP9JwbxsCzuZuTLcz603rmJS'
), (
    'Bianca',
    'Nicole',
    'Dela Cruz',
    '2003-10-11',
    'Female',
    'bianca.delacruz09@outlook.com',
    '09449990011',
    '31 Mercedes Avenue, Barangay San Miguel, Pasig City',
    'biancd09',
    '$2y$10$a5fzrMf/2/BSlgIlvI.Zr.pNkdu2X.ftuSw7h.qK3f3F9rqVMMrWi'
), (
    'Dominic',
    'Carlo',
    'Valdez',
    '1994-12-30',
    'Male',
    'dominic.valdez10@gmail.com',
    '09450001122',
    '45 Jennys Avenue, Barangay Maybunga, Pasig City',
    'domv10',
    '$2y$10$u3W/QEQ0A26vW.4TPks2C.sVstphnOWJbLprxLw.jv6yVpADdvzI.'
)
ON DUPLICATE KEY UPDATE
    customer_id = customer_id;

