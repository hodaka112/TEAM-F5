<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests - Sun Son Solar</title>
    <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/service.css') ?>">
</head>
<body>
    <header>
        <nav class="navbar section-content">
            <a href="<?= site_url() ?>" class="nav-logo"><h2 class="logo-text"><img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo" width="70" height="50"><span>Sun Son Solar</span></h2></a>
            <ul class="nav-menu">
                <li><a class="nav-link" href="<?= site_url() ?>">Home</a></li>
                <li><span class="nav-link">Employee: <?= esc(session()->get('account_name')) ?></span></li>
                <li><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="nav-link" type="submit">Sign out</button></form></li>
            </ul>
        </nav>
    </header>
    <main class="section-content request-list">
        <p class="section-kicker">EMPLOYEE VIEW</p>
        <h1>Service Requests</h1>
        <?php if (! empty($notice)): ?><p class="site-notice" role="status"><?= esc($notice) ?></p><?php endif; ?>
        <?php if (empty($requests)): ?>
            <p class="sub">There are no service requests yet.</p>
        <?php else: ?>
            <div class="request-table-wrap"><table class="request-table">
                <thead><tr><th>Received</th><th>Customer</th><th>Service</th><th>Preferred date</th><th>Contact</th><th>Address and details</th><th>Status</th></tr></thead>
                <tbody><?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?= esc($request['created_at']) ?></td>
                        <td><?= esc($request['first_name'] . ' ' . $request['last_name']) ?></td>
                        <td><?= esc($request['service_type']) ?></td>
                        <td><?= esc($request['preferred_date'] ?: 'Not specified') ?></td>
                        <td><?= esc($request['email']) ?><br><?= esc($request['phone_number']) ?></td>
                        <td><?= esc($request['address']) ?><?php if (! empty($request['message'])): ?><br><?= esc($request['message']) ?><?php endif; ?></td>
                        <td><?= esc($request['status']) ?></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php endif; ?>
    </main>
</body>
</html>
