<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sun Son Solar - Registration</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="<?= base_url('css/registration.css') ?>">
</head>

<body>

    <main class="container">

        <div class="register-card">

            <header class="brand-header">
                <img
                    class="brand-logo"
                    src="<?= base_url('images/Sun Son Solar.png') ?>"
                    alt="Sun Son Solar logo"
                >
            </header>

            <h2>Register Account</h2>

            <p id="message" role="status"><?= ! empty($errors) ? esc(implode(" ", $errors)) : '' ?></p>

            <p class="subtitle">
                Please fill in the details to register.
            </p>

            <form id="registrationForm" action="<?= site_url('register') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <input type="hidden" id="accountType" name="accountType" value="customer">
                    <p>Customer registration</p>
                </div>

                <div class="form-group">
                    <label for="firstnm">First Name:</label>
                    <input
                        type="text"
                        id="firstnm"
                        name="firstnm"
                        placeholder="Enter your First Name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="middlenm">Middle Name:</label>
                    <input
                        type="text"
                        id="middlenm"
                        name="middlenm"
                        placeholder="Enter your Middle Name"
                    >
                </div>

                <div class="form-group">
                    <label for="lastnm">Last Name:</label>
                    <input
                        type="text"
                        id="lastnm"
                        name="lastnm"
                        placeholder="Enter your Last Name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                </div>

                <div class="form-group" id="departmentGroup" hidden>
                    <label for="department">Department:</label>
                    <select id="department" name="department" disabled>
                        <option value="">Select department</option>
                        <option value="Management">Management</option>
                        <option value="Sales &amp; Marketing">Sales &amp; Marketing</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Installation &amp; Operations">Installation &amp; Operations</option>
                        <option value="Maintenance &amp; Service">Maintenance &amp; Service</option>
                        <option value="IT">IT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="birthdate">Birthdate:</label>
                    <input
                        type="date"
                        id="birthdate"
                        name="birthdate"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="Confirm your password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contactnum">Phone Number:</label>
                    <input
                        type="text"
                        id="contactnum"
                        name="contactnum"
                        placeholder="Enter your phone number"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        placeholder="Enter your complete address"
                        required
                    ></textarea>
                </div>

                <button type="submit" class="register-btn">
                    Register
                </button>


            </form>

        </div>

    </main>

    <script src="<?= base_url('js/registration.js') ?>"></script>
</body>
</html>


