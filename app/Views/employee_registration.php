<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Registration | Sun Son Solar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/registration_employee.css') ?>">
</head>
<body>
    <main class="container">
        <section class="register-card">
            <div class="brand-header">
                <a href="<?= site_url('admin/dashboard') ?>">
                    <img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo" class="brand-logo">
                </a>
            </div>
            <h2>Employee Registration</h2>
            <p class="subtitle">Create an employee account through the administrator dashboard.</p>

            <?php if ($errors !== []): ?>
                <div id="message" role="alert" aria-live="polite">
                    <?php foreach ($errors as $error): ?><p><?= esc($error) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('admin/employees') ?>" method="post" id="employeeForm">
                <?= csrf_field() ?>
                <h3 class="form-title">Employee Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="first_name" value="<?= esc($old['first_name'] ?? '') ?>" maxlength="50" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="last_name" value="<?= esc($old['last_name'] ?? '') ?>" maxlength="50" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middle_name" value="<?= esc($old['middle_name'] ?? '') ?>" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= esc($old['email'] ?? '') ?>" maxlength="100" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Contact Number</label>
                    <input type="tel" id="phone" name="phone_number" value="<?= esc($old['phone_number'] ?? '') ?>" placeholder="09XXXXXXXXX" pattern="09[0-9]{9}" title="Enter an 11-digit Philippine mobile number starting with 09">
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach (['Management', 'IT', 'Installation & Operations', 'Maintenance & Service', 'Sales & Marketing', 'Engineering'] as $department): ?>
                            <option value="<?= esc($department) ?>" <?= ($old['department'] ?? '') === $department ? 'selected' : '' ?>><?= esc($department) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h3 class="form-title">Account Information</h3>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= esc($old['username'] ?? '') ?>" minlength="3" maxlength="50" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Temporary Password</label>
                    <input type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="terms">
                    <label><input type="checkbox" required> I confirm this employee is authorized to receive an account.</label>
                </div>
                <button type="submit" class="register-btn">Register Employee</button>
            </form>
            <p class="back-link"><a href="<?= site_url('admin/dashboard') ?>">Back to Admin Dashboard</a></p>
        </section>
    </main>
    <script>
        const employeeForm = document.getElementById('employeeForm');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirmPassword');
        function checkPasswords() {
            confirmInput.setCustomValidity(passwordInput.value === confirmInput.value ? '' : 'Passwords do not match.');
        }
        passwordInput.addEventListener('input', checkPasswords);
        confirmInput.addEventListener('input', checkPasswords);
        employeeForm.addEventListener('submit', checkPasswords);
    </script>
</body>
</html>
