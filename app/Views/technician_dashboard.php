<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sun Son Solar | Technician Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= base_url('css/technician_dashboard.css') ?>"><link rel="stylesheet" href="<?= base_url('css/dashboard-workflows.css') ?>">
</head>
<body data-role="technician"><div class="layout">
    <aside class="sidebar"><div class="brand"><img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo" class="brand-logo"><span class="brand-name">Sun Son Solar</span></div><small>Technician Dashboard</small>
        <nav class="nav" aria-label="Technician navigation"><a href="#jobs">My Assigned Jobs</a><a href="#checkins">Check-In History</a><a href="#announcements">Messages &amp; Announcements</a><a href="#issues">Problem Reports</a></nav>
    </aside>
    <main class="main"><header class="top"><div><h1>Technician Dashboard</h1><p>Hello, <?= esc($employee['first_name']) ?> · <?= esc($employee['department']) ?></p></div><span class="pill">Technician</span></header>
        <?php if (! empty($notice)): ?><p class="status" role="status"><?= esc($notice) ?></p><?php endif; ?><?php if (! empty($errors)): ?><div class="status" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <section class="panel" id="jobs"><h2>My Assigned Jobs</h2><p>Only jobs assigned to your employee account are shown. Customer email and phone details are not included.</p><div class="table-wrap"><table><thead><tr><th>Job</th><th>Customer</th><th>Service address</th><th>Service</th><th>Schedule</th><th>Status / Actions</th></tr></thead><tbody>
        <?php foreach ($assignments as $assignment): ?><tr><td>#<?= esc($assignment['request_id']) ?></td><td><?= esc($assignment['first_name'] . ' ' . $assignment['last_name']) ?></td><td><?= esc($assignment['address']) ?></td><td><?= esc($assignment['service_type']) ?></td><td><?= esc($assignment['scheduled_date'] ?: $assignment['preferred_date'] ?: 'Not set') ?> <?= esc($assignment['scheduled_time'] ? substr($assignment['scheduled_time'], 0, 5) : '') ?></td><td><span class="tag"><?= esc($assignment['status']) ?></span>
            <?php if ($assignment['status'] !== 'Completed'): ?><form action="<?= site_url('technician/assignments/status') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="assignment_id" value="<?= esc($assignment['assignment_id']) ?>"><select name="status"><option value="In Progress" <?= $assignment['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option><option value="Completed">Completed</option></select><button class="btn secondary" type="submit">Update status</button></form>
                <form action="<?= site_url('technician/checkin') ?>" method="post" class="workflow-form geolocation-form"><?= csrf_field() ?><input type="hidden" name="assignment_id" value="<?= esc($assignment['assignment_id']) ?>"><input type="hidden" name="latitude"><input type="hidden" name="longitude"><button class="btn" type="submit">Check in with location</button></form><?php endif; ?>
        </td></tr><?php endforeach; ?><?php if ($assignments === []): ?><tr><td colspan="6"><div class="empty-state">No jobs are assigned to you yet.</div></td></tr><?php endif; ?>
        </tbody></table></div></section>

        <section class="panel" id="checkins"><h2>Check-In History</h2><p>Location is captured only after you press the check-in button and allow browser location access. Coordinates are shown to you and authorized administrators.</p><div class="table-wrap"><table><thead><tr><th>Request</th><th>Checked in</th><th>Coordinates</th></tr></thead><tbody><?php foreach ($checkins as $checkin): ?><tr><td>#<?= esc($checkin['request_id']) ?></td><td><?= esc($checkin['checked_in_at']) ?></td><td><?= esc($checkin['latitude']) ?>, <?= esc($checkin['longitude']) ?></td></tr><?php endforeach; ?><?php if ($checkins === []): ?><tr><td colspan="3">No check-ins recorded.</td></tr><?php endif; ?></tbody></table></div></section>

        <section class="panel" id="announcements"><h2>Messages &amp; Announcements</h2><?php foreach ($notifications as $notification): ?><div class="notice-row notice"><div><strong><?= esc($notification['title']) ?></strong><p><?= esc($notification['message']) ?></p><small><?= esc($notification['created_at']) ?></small></div><?php if (! $notification['read_at']): ?><form action="<?= site_url('technician/notifications/read') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="notification_id" value="<?= esc($notification['notification_id']) ?>"><button class="btn secondary" type="submit">Mark read</button></form><?php else: ?><span>Read</span><?php endif; ?></div><?php endforeach; ?><?php if ($notifications === []): ?><div class="empty-state">No messages or announcements.</div><?php endif; ?></section>

        <section class="panel" id="issues"><h2>Submit a Problem Report</h2><p>Reports are visible to authorized administrators. Do not include unnecessary customer personal information.</p><?php if ($assignments !== []): ?><form action="<?= site_url('technician/reports') ?>" method="post" class="workflow-form"><?= csrf_field() ?><label>Assigned job <select name="assignment_id" required><?php foreach ($assignments as $assignment): ?><option value="<?= esc($assignment['assignment_id']) ?>">Request #<?= esc($assignment['request_id']) ?> — <?= esc($assignment['service_type']) ?></option><?php endforeach; ?></select></label><label>Problem type <select name="category"><option>Equipment</option><option>Customer concern</option><option>Safety</option><option>Other</option></select></label><label>Details <textarea name="description" maxlength="3000" required></textarea></label><button class="btn" type="submit">Send report</button></form><?php else: ?><div class="empty-state">A report must be linked to one of your assigned jobs. There are no assigned jobs yet.</div><?php endif; ?>
            <h3>My submitted reports</h3><div class="table-wrap"><table><thead><tr><th>Request</th><th>Type</th><th>Report</th><th>Submitted</th><th>Status</th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td>#<?= esc($report['request_id']) ?></td><td><?= esc($report['category']) ?></td><td><?= esc($report['description']) ?></td><td><?= esc($report['created_at']) ?></td><td><?= esc($report['status']) ?></td></tr><?php endforeach; ?><?php if ($reports === []): ?><tr><td colspan="5">No reports submitted.</td></tr><?php endif; ?></tbody></table></div>
        </section>

        <footer class="footer-section"><h3>Sun Son Solar</h3><p>Field services</p><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="btn secondary" type="submit">Sign out</button></form></footer>
    </main>
</div>
<script>
document.querySelectorAll('.geolocation-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!navigator.geolocation) { event.preventDefault(); alert('Location is not available in this browser.'); return; }
        if (form.dataset.locationReady === '1') return;
        event.preventDefault();
        navigator.geolocation.getCurrentPosition((position) => {
            form.elements.latitude.value = position.coords.latitude.toFixed(7);
            form.elements.longitude.value = position.coords.longitude.toFixed(7);
            form.dataset.locationReady = '1';
            form.requestSubmit();
        }, () => alert('Location permission is needed for check-in.'), { enableHighAccuracy: true, timeout: 10000 });
    });
});
</script>
</body></html>
