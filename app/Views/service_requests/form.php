<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Service | Sun Son Solar</title>
    <link href="https://fonts.googleapis.com/css2?family=Miniver&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/service.css') ?>">
</head>
<body>
    <header>
        <nav class="navbar section-content">
            <a href="<?= site_url() ?>" class="nav-logo"><h2 class="logo-text"><img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo" width="70" height="50"><span>Sun Son Solar</span></h2></a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="<?= site_url() ?>#services" class="nav-link">Services</a></li>
                <li class="nav-item"><a href="<?= site_url() ?>#contact" class="nav-link">Contact</a></li>
                <?php if (session()->get('account_id')): ?>
                    <?php if (session()->get('account_type') === 'customer'): ?><li class="nav-item"><a href="<?= site_url('customer/dashboard') ?>" class="nav-link">My Dashboard</a></li><?php endif; ?>
                    <li class="nav-item"><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="nav-link" type="submit">Sign out</button></form></li>
                <?php else: ?>
                    <li class="nav-item"><a href="<?= site_url('login') ?>" class="nav-link">Sign in</a></li>
                    <li class="nav-item"><a href="<?= site_url('register') ?>" class="nav-link nav-link-signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="section-content request-page">
        <div class="section-heading">
            <p class="section-kicker">LET'S GET STARTED</p>
            <h1><?= $service === 'Maintenance' ? 'Schedule a Service' : 'Request a Free Quote' ?></h1>
            <p class="sub">Tell us how to reach you and what service you need.</p>
        </div>
        <?php if ($notice): ?><p class="form-notice" role="status"><?= esc($notice) ?></p><?php endif; ?>
        <?php if ($errors): ?><div class="form-errors" role="alert"><p>Please correct the following:</p><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form class="request-form" action="<?= site_url('requests') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group"><label for="first-name">First name</label><input id="first-name" name="first_name" value="<?= esc(old('first_name', $customer['first_name'] ?? '')) ?>" maxlength="50" autocomplete="given-name" required></div>
            <div class="form-group"><label for="last-name">Last name</label><input id="last-name" name="last_name" value="<?= esc(old('last_name', $customer['last_name'] ?? '')) ?>" maxlength="50" autocomplete="family-name" required></div>
            <div class="form-group"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= esc(old('email', $customer['email'] ?? '')) ?>" maxlength="100" autocomplete="email" required></div>
            <div class="form-group"><label for="phone">Phone (09xxxxxxxxx)</label><input id="phone" name="phone_number" value="<?= esc(old('phone_number', $customer['phone_number'] ?? '')) ?>" pattern="09[0-9]{9}" maxlength="11" autocomplete="tel" required></div>
            <div class="form-group"><label for="service">Service</label><select id="service" name="service_type" required><?php foreach ($categories as $category): ?><option value="<?= esc($category['service_type']) ?>" <?= old('service_type', $service) === $category['service_type'] ? 'selected' : '' ?>><?= esc($category['service_type']) ?><?= $category['price'] !== null ? ' — PHP ' . esc(number_format((float) $category['price'], 2)) : '' ?><?= $category['availability'] ? ' (' . esc($category['availability']) . ')' : '' ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="date">Preferred date <span id="date-hint">(optional)</span></label><input id="date" type="date" name="preferred_date" value="<?= esc(old('preferred_date')) ?>"></div>
            <div class="form-group"><label for="address">Service address</label><textarea id="address" name="address" minlength="5" maxlength="500" autocomplete="street-address" required><?= esc(old('address', $customer['address'] ?? '')) ?></textarea></div>
            <div class="form-group"><label for="message">Additional details</label><textarea id="message" name="message" maxlength="2000"><?= esc(old('message')) ?></textarea></div>
            <button class="service-button" type="submit">Send request</button>
        </form>
        <p class="request-back"><a href="<?= site_url() ?>">Return to homepage</a></p>
    </main>
    <script>
        const serviceSelect = document.getElementById('service');
        const serviceDate = document.getElementById('date');
        const dateHint = document.getElementById('date-hint');
        function updateDateRequirement() {
            const required = serviceSelect.value === 'Maintenance';
            serviceDate.required = required;
            dateHint.textContent = required ? '(required)' : '(optional)';
        }
        serviceSelect.addEventListener('change', updateDateRequirement);
        updateDateRequirement();
    </script>
</body>
</html>
