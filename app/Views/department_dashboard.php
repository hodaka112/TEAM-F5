<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sun Son Solar | <?= esc($department) ?> Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/department_dashboard.css') ?>">
</head>
<body>
    <div class="department-shell">
        <header class="department-header">
            <div class="brand"><img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo"><span>Sun Son Solar</span></div>
            <form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button type="submit">Sign out</button></form>
        </header>

        <main>
            <?php if (! empty($notice)): ?><p class="notice" role="status"><?= esc($notice) ?></p><?php endif; ?>
            <section class="welcome-panel">
                <p class="eyebrow">Employee Dashboard</p>
                <h1><?= esc($department) ?></h1>
                <p>This dashboard is for active employees in the <?= esc($department) ?> department.</p>
                <dl class="profile-details">
                    <div><dt>Employee</dt><dd><?= esc(trim($employee['first_name'] . ' ' . ($employee['middle_name'] ?? '') . ' ' . $employee['last_name'])) ?></dd></div>
                    <div><dt>Department</dt><dd><?= esc($employee['department']) ?></dd></div>
                    <div><dt>Position access</dt><dd><?= esc(ucfirst($employee['role'])) ?></dd></div>
                </dl>
            </section>

            <section class="announcements" aria-labelledby="announcements-title">
                <div class="section-heading"><div><p class="eyebrow">Company updates</p><h2 id="announcements-title">Messages &amp; Announcements</h2></div></div>
                <?php foreach ($messages as $message): ?>
                    <article class="message-card"><div class="message-heading"><h3><?= esc($message['title']) ?></h3><time datetime="<?= esc($message['created_at']) ?>"><?= esc($message['created_at']) ?></time></div><p><?= nl2br(esc($message['message'])) ?></p></article>
                <?php endforeach; ?>
                <?php if ($messages === []): ?><p class="empty-state">No announcements yet.</p><?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
