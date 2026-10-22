<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sun Son Solar | Administrator Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/admin_dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard-workflows.css') ?>">
</head>
<body data-role="admin">
<div class="layout">
    <aside class="sidebar">
        <div class="brand"><img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo" class="brand-logo"><span class="brand-name">Sun Son Solar</span></div>
        <small>Administrator Dashboard</small>
        <nav class="nav" aria-label="Administrator navigation">
            <a href="#employees">User Management</a><a href="#schedule">Schedule &amp; Requests</a><a href="#checkins">Technician Check-Ins</a><a href="#analytics">Reports &amp; Analytics</a>
            <a href="#notifications">Notifications</a><a href="#settings">Service Settings</a><a href="#issues">Technician Reports</a><a href="#support">Support Tickets</a>
        </nav>
    </aside>
    <main class="main">
        <header class="top"><div><h1>Administrator Dashboard</h1><p>Hello, <?= esc($adminName) ?>. Department: <?= esc($adminDepartment ?: 'Not set') ?>. Manage the current service workflow.</p></div><span class="pill">Administrator · <?= esc($adminDepartment ?: 'No department') ?></span></header>
        <?php if (! empty($notice)): ?><p class="status" role="status"><?= esc($notice) ?></p><?php endif; ?>
        <?php if (! empty($temporaryPassword)): ?><p class="status" role="status">New password: <code><?= esc($temporaryPassword) ?></code> — copy and share it securely now; it will not be shown again.</p><?php endif; ?>
        <?php if (! empty($errors)): ?><div class="status" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <div class="stats" aria-label="System summary">
            <div class="stat"><strong><?= count($employees) ?></strong><span>Employee accounts</span></div>
            <div class="stat"><strong><?= esc($customerCount) ?></strong><span>Customers</span></div>
            <div class="stat"><strong><?= count($requests) ?></strong><span>Requests</span></div>
            <div class="stat"><strong><?= count(array_filter($requests, static fn ($row) => $row['status'] === 'Pending')) ?></strong><span>Pending requests</span></div>
        </div>

        <section class="panel" id="employees">
            <h2>User Management</h2><p>Administrator accounts are protected. Employee accounts can be edited or deactivated without deleting their records.</p>
            <p><a class="btn" href="<?= site_url('admin/employees/new') ?>">Add Employee</a></p>
            <div class="table-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Email / Username</th><th>Department</th><th>Role</th><th>State</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($employees as $employee): ?>
                <tr><td><?= esc($employee['employee_id']) ?></td><td><?= esc(trim($employee['first_name'] . ' ' . ($employee['middle_name'] ?? '') . ' ' . $employee['last_name'])) ?></td>
                <td><?= esc($employee['email']) ?><br><?= esc($employee['username']) ?></td><td><?= esc($employee['department'] ?: 'Not set') ?></td><td><?= esc(ucfirst($employee['role'])) ?></td><td><?= (int) $employee['is_active'] ? 'Active' : 'Inactive' ?></td>
                <td><?php if ($employee['role'] !== 'admin'): ?><details><summary>Edit</summary>
                    <form action="<?= site_url('admin/employees/update') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="employee_id" value="<?= esc($employee['employee_id']) ?>">
                        <label>First name <input name="first_name" value="<?= esc($employee['first_name']) ?>" required maxlength="50"></label>
                        <label>Last name <input name="last_name" value="<?= esc($employee['last_name']) ?>" required maxlength="50"></label>
                        <label>Email <input type="email" name="email" value="<?= esc($employee['email']) ?>" required maxlength="100"></label>
                        <label>Username <input name="username" value="<?= esc($employee['username']) ?>" required minlength="3" maxlength="50"></label>
                        <label>Phone <input name="phone_number" value="<?= esc($employee['phone_number'] ?? '') ?>" pattern="09[0-9]{9}"></label>
                        <label>Department <select name="department" required><?php foreach (['Management', 'IT', 'Installation & Operations', 'Maintenance & Service', 'Sales & Marketing', 'Engineering'] as $department): ?><option <?= $employee['department'] === $department ? 'selected' : '' ?> value="<?= esc($department) ?>"><?= esc($department) ?></option><?php endforeach; ?></select></label>
                        <button class="btn" type="submit">Save changes</button>
                    </form>
                    <form action="<?= site_url('admin/employees/deactivate') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="employee_id" value="<?= esc($employee['employee_id']) ?>"><button class="btn danger" type="submit" <?= (int) $employee['is_active'] === 0 ? 'disabled' : '' ?>>Deactivate account</button></form>
                    <form action="<?= site_url('admin/employees/reset-password') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="employee_id" value="<?= esc($employee['employee_id']) ?>"><p>Resetting replaces this employee's current password.</p><button class="btn secondary" type="submit" <?= (int) $employee['is_active'] === 0 ? 'disabled' : '' ?>>Reset password and show once</button></form>
                </details><?php else: ?>Protected admin<?php if ((int) $employee['employee_id'] !== $currentAdminId): ?><form action="<?= site_url('admin/employees/reset-password') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="employee_id" value="<?= esc($employee['employee_id']) ?>"><p>Resetting replaces this administrator's current password.</p><button class="btn secondary" type="submit">Reset password and show once</button></form><?php endif; ?><?php endif; ?></td></tr>
            <?php endforeach; ?>
            <?php if ($employees === []): ?><tr><td colspan="7">No employee accounts found.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>

        <section class="panel" id="schedule">
            <h2>Schedule Overview &amp; Request Management</h2><p>Choose request status, service date, time, and an active field technician. Approval updates the existing request status.</p>
            <div class="calendar-heading"><a class="btn secondary" href="<?= site_url('admin/dashboard?month=' . $previousMonth) ?>#schedule">Previous month</a><h3><?= esc($calendarMonth) ?></h3><a class="btn secondary" href="<?= site_url('admin/dashboard?month=' . $nextMonth) ?>#schedule">Next month</a></div>
            <div class="calendar-grid" role="grid" aria-label="Requests by service date"><div class="calendar-weekday">Mon</div><div class="calendar-weekday">Tue</div><div class="calendar-weekday">Wed</div><div class="calendar-weekday">Thu</div><div class="calendar-weekday">Fri</div><div class="calendar-weekday">Sat</div><div class="calendar-weekday">Sun</div>
                <?php foreach ($calendarWeeks as $week): foreach ($week as $cell): ?><div class="calendar-day <?= $cell === null ? 'is-empty' : '' ?>"><?php if ($cell !== null): ?><strong><?= esc($cell['day']) ?></strong><?php foreach ($cell['events'] as $event): ?><a class="calendar-event" href="#request-<?= esc($event['request_id']) ?>">#<?= esc($event['request_id']) ?> <?= esc($event['service_type']) ?> · <?= esc($event['status']) ?></a><?php endforeach; ?><?php endif; ?></div><?php endforeach; endforeach; ?>
            </div>
            <div class="table-wrap booking-table-wrap"><table class="booking-table"><thead><tr><th>Booking</th><th>Customer</th><th>Service</th><th>Date &amp; Time</th><th>Status</th><th>Technician</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($requests as $request): ?>
                <tr id="request-<?= esc($request['request_id']) ?>">
                    <td class="booking-id">#<?= esc($request['request_id']) ?></td>
                    <td><strong><?= esc($request['first_name'] . ' ' . $request['last_name']) ?></strong><small><?= esc($request['email']) ?></small></td>
                    <td><strong><?= esc($request['service_type']) ?></strong><details class="booking-extra"><summary>Details</summary><span><?= esc($request['address']) ?></span><?php if ($request['message']): ?><span><?= esc($request['message']) ?></span><?php endif; ?></details></td>
                    <td><?= esc($request['scheduled_date'] ?: $request['preferred_date'] ?: 'Not scheduled') ?><?= $request['scheduled_time'] ? '<small>' . esc(substr($request['scheduled_time'], 0, 5)) . '</small>' : '' ?></td>
                    <td><span class="booking-status status-<?= esc(strtolower(str_replace(' ', '-', $request['status']))) ?>"><?= esc($request['status']) ?></span></td>
                    <td><?= esc(trim(($request['technician_first_name'] ?? '') . ' ' . ($request['technician_last_name'] ?? '')) ?: 'Unassigned') ?></td>
                    <td class="booking-action-cell">
                        <details class="booking-actions"><summary>Manage</summary>
                            <form action="<?= site_url('admin/requests/manage') ?>" method="post" class="workflow-form booking-manage-form"><?= csrf_field() ?><input type="hidden" name="request_id" value="<?= esc($request['request_id']) ?>">
                                <label>Status <select name="status"><option value="Pending" <?= $request['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option><option value="Confirmed" <?= $request['status'] === 'Confirmed' ? 'selected' : '' ?>>Approve / Confirm</option><option value="Completed" <?= $request['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option><option value="Cancelled" <?= $request['status'] === 'Cancelled' ? 'selected' : '' ?>>Decline / Cancel</option></select></label>
                                <label>Technician <select name="employee_id"><option value="0">Keep current assignment</option><?php foreach ($technicians as $technician): ?><option value="<?= esc($technician['employee_id']) ?>" <?= (int) $request['assigned_employee_id'] === (int) $technician['employee_id'] ? 'selected' : '' ?>><?= esc($technician['first_name'] . ' ' . $technician['last_name'] . ' — ' . $technician['department']) ?></option><?php endforeach; ?></select></label>
                                <label>Date <input type="date" name="scheduled_date" value="<?= esc($request['scheduled_date'] ?: $request['preferred_date']) ?>"></label><label>Time <input type="time" name="scheduled_time" value="<?= esc($request['scheduled_time'] ? substr($request['scheduled_time'], 0, 5) : '') ?>"></label>
                                <button class="btn" type="submit">Save schedule</button>
                            </form>
                            <?php if ($request['customer_id']): ?><form action="<?= site_url('admin/payments') ?>" method="post" class="workflow-form booking-payment-form"><?= csrf_field() ?><input type="hidden" name="request_id" value="<?= esc($request['request_id']) ?>"><strong>Record verified payment</strong><label>Amount (PHP) <input name="amount" inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?" required></label><label>Method <select name="method"><option>Cash</option><option>Bank transfer</option><option>Other</option></select></label><label>Reference <input name="reference" maxlength="100"></label><button class="btn secondary" type="submit">Create receipt record</button></form><?php endif; ?>
                        </details>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($requests === []): ?><tr><td colspan="7">No service requests yet.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>

        <section class="panel" id="checkins"><h2>Technician Check-In Overview</h2><p>Location is visible only to administrators and the technician who submitted it.</p><div class="table-wrap"><table><thead><tr><th>Technician</th><th>Request</th><th>Check-in time</th><th>Coordinates</th></tr></thead><tbody><?php foreach ($checkins as $checkin): ?><tr><td><?= esc($checkin['first_name'] . ' ' . $checkin['last_name']) ?></td><td>#<?= esc($checkin['request_id']) ?></td><td><?= esc($checkin['checked_in_at']) ?></td><td><?= esc($checkin['latitude']) ?>, <?= esc($checkin['longitude']) ?></td></tr><?php endforeach; ?><?php if ($checkins === []): ?><tr><td colspan="4">No technician check-ins recorded.</td></tr><?php endif; ?></tbody></table></div></section>

        <section class="panel analytics-panel" id="analytics">
            <div class="analytics-heading"><div><h2>Reports &amp; Analytics</h2><p>Summary from customer, booking, assignment, and verified payment records.</p></div><span class="analytics-period">Last 6 months + current totals</span></div>
            <?php $pendingRequestCount = (int) array_sum(array_map(static fn ($row) => (int) $row['total'], array_filter($requestStatusSummary, static fn ($row) => $row['status'] === 'Pending'))); ?>
            <div class="analytics-kpis" aria-label="Analytics summary">
                <article class="analytics-kpi"><span>Customer accounts</span><strong><?= esc($customerCount) ?></strong><small>Registered customers</small></article>
                <article class="analytics-kpi"><span>Service requests</span><strong><?= count($requests) ?></strong><small>All recorded requests</small></article>
                <article class="analytics-kpi"><span>Pending requests</span><strong><?= esc($pendingRequestCount) ?></strong><small>Awaiting review</small></article>
                <article class="analytics-kpi"><span>Recorded revenue</span><strong>PHP <?= esc(number_format($recordedRevenue, 2)) ?></strong><small><?= esc($paymentRecordCount) ?> payment records</small></article>
            </div>

            <div class="analytics-grid">
                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Service requests by month</h3><p>Request creation date</p></div><span class="analytics-key">Requests</span></div>
                    <?php $maxRequestTrend = max(array_map(static fn ($row) => (int) $row['total'], $requestTrend) ?: [1]); ?>
                    <div class="analytics-bars" role="list" aria-label="Service requests by month">
                        <?php foreach ($requestTrend as $month): $value = (int) $month['total']; $height = $maxRequestTrend > 0 ? (int) round(($value / $maxRequestTrend) * 100) : 0; ?>
                            <div class="analytics-bar-column" role="listitem" title="<?= esc($month['month']) ?>: <?= esc($value) ?> requests"><strong><?= esc($value) ?></strong><div class="analytics-bar-track"><span class="analytics-bar orange" style="height: <?= $height ?>%"></span></div><small><?= esc(date('M y', strtotime($month['month'] . '-01'))) ?></small></div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Customer registrations by month</h3><p>New accounts from customer records</p></div><span class="analytics-key">Customers</span></div>
                    <?php $maxCustomerTrend = max(array_map(static fn ($row) => (int) $row['total'], $customerTrend) ?: [1]); ?>
                    <div class="analytics-bars" role="list" aria-label="Customer registrations by month">
                        <?php foreach ($customerTrend as $month): $value = (int) $month['total']; $height = $maxCustomerTrend > 0 ? (int) round(($value / $maxCustomerTrend) * 100) : 0; ?>
                            <div class="analytics-bar-column" role="listitem" title="<?= esc($month['month']) ?>: <?= esc($value) ?> new customers"><strong><?= esc($value) ?></strong><div class="analytics-bar-track"><span class="analytics-bar gold" style="height: <?= $height ?>%"></span></div><small><?= esc(date('M y', strtotime($month['month'] . '-01'))) ?></small></div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Verified revenue by month</h3><p>Recorded payments only</p></div><span class="analytics-key">PHP</span></div>
                    <?php $maxRevenue = max(array_map(static fn ($row) => (float) $row['total'], $monthlyRevenue) ?: [1]); ?>
                    <div class="analytics-bars" role="list" aria-label="Verified revenue by month">
                        <?php foreach ($monthlyRevenue as $month): $value = (float) $month['total']; $height = $maxRevenue > 0 ? (int) round(($value / $maxRevenue) * 100) : 0; ?>
                            <div class="analytics-bar-column" role="listitem" title="<?= esc($month['month']) ?>: PHP <?= esc(number_format($value, 2)) ?>"><strong><?= $value >= 1000 ? 'PHP ' . esc(number_format($value / 1000, 1)) . 'k' : 'PHP ' . esc(number_format($value, 0)) ?></strong><div class="analytics-bar-track"><span class="analytics-bar gold" style="height: <?= $height ?>%"></span></div><small><?= esc(date('M y', strtotime($month['month'] . '-01'))) ?></small></div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Requests by service</h3><p>All recorded requests</p></div></div>
                    <?php $maxUsage = max(array_map(static fn ($row) => (int) $row['total'], $serviceUsage) ?: [1]); ?>
                    <div class="analytics-rank-list">
                        <?php foreach ($serviceUsage as $usage): $value = (int) $usage['total']; $width = $maxUsage > 0 ? (int) round(($value / $maxUsage) * 100) : 0; ?>
                            <div class="analytics-rank"><div><span><?= esc($usage['service_type']) ?></span><strong><?= esc($value) ?></strong></div><span class="analytics-meter"><span style="width: <?= $width ?>%"></span></span></div>
                        <?php endforeach; ?>
                        <?php if ($serviceUsage === []): ?><p class="analytics-empty">No service requests have been recorded.</p><?php endif; ?>
                    </div>
                </article>

                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Technician workload</h3><p>Assigned and in-progress jobs only</p></div></div>
                    <?php $maxJobs = max(array_map(static fn ($technician) => (int) $technician['assigned_jobs'], $technicianWorkload) ?: [1]); ?>
                    <?php if ($technicianWorkload !== []): ?><div class="analytics-bars technician-bars" role="list" aria-label="Current assigned jobs by active technician">
                        <?php foreach ($technicianWorkload as $technician): $jobs = (int) $technician['assigned_jobs']; $height = $maxJobs > 0 ? (int) round(($jobs / $maxJobs) * 100) : 0; ?>
                            <div class="analytics-bar-column" role="listitem" title="<?= esc($technician['first_name'] . ' ' . $technician['last_name']) ?>: <?= esc($jobs) ?> active jobs"><strong><?= esc($jobs) ?></strong><div class="analytics-bar-track"><span class="analytics-bar orange" style="height: <?= $height ?>%"></span></div><small><?= esc($technician['first_name'] . ' ' . $technician['last_name']) ?></small></div>
                        <?php endforeach; ?>
                    </div><p class="analytics-footnote">This is a job count, not a utilization percentage. Work hours and technician capacity are not tracked.</p>
                    <?php else: ?><p class="analytics-empty">No active field technicians are available to report.</p><?php endif; ?>
                </article>

                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Request status</h3><p>Current status across all requests</p></div></div>
                    <?php $maxStatus = max(array_map(static fn ($row) => (int) $row['total'], $requestStatusSummary) ?: [1]); ?>
                    <div class="analytics-rank-list">
                        <?php foreach ($requestStatusSummary as $statusRow): $value = (int) $statusRow['total']; $width = $maxStatus > 0 ? (int) round(($value / $maxStatus) * 100) : 0; ?>
                            <div class="analytics-rank"><div><span><?= esc($statusRow['status']) ?></span><strong><?= esc($value) ?></strong></div><span class="analytics-meter"><span style="width: <?= $width ?>%"></span></span></div>
                        <?php endforeach; ?>
                        <?php if ($requestStatusSummary === []): ?><p class="analytics-empty">No request status data available.</p><?php endif; ?>
                    </div>
                </article>

                <article class="analytics-card"><div class="analytics-card-heading"><div><h3>Most active customers</h3><p>Top linked customers by request count</p></div></div>
                    <?php $maxActivity = max(array_map(static fn ($row) => (int) $row['total_requests'], $customerActivity) ?: [1]); ?>
                    <div class="analytics-rank-list">
                        <?php foreach (array_slice($customerActivity, 0, 5) as $activity): $value = (int) $activity['total_requests']; $width = $maxActivity > 0 ? (int) round(($value / $maxActivity) * 100) : 0; ?>
                            <div class="analytics-rank"><div><span><?= esc($activity['first_name'] . ' ' . $activity['last_name']) ?></span><strong><?= esc($value) ?></strong></div><span class="analytics-meter"><span style="width: <?= $width ?>%"></span></span></div>
                        <?php endforeach; ?>
                        <?php if ($customerActivity === []): ?><p class="analytics-empty">No requests linked to customer accounts yet.</p><?php endif; ?>
                    </div>
                </article>
            </div>

            <details class="analytics-details"><summary>Customer records (<?= esc(count($customerRecords)) ?>)</summary><div class="table-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Birthdate</th><th>Gender</th><th>Email</th><th>Phone</th><th>Address</th><th>Username</th><th>Registered</th></tr></thead><tbody>
                <?php foreach ($customerRecords as $customer): ?><tr><td><?= esc($customer['customer_id']) ?></td><td><?= esc(trim($customer['first_name'] . ' ' . ($customer['middle_name'] ?? '') . ' ' . $customer['last_name'])) ?></td><td><?= esc($customer['birthdate'] ?? '') ?></td><td><?= esc($customer['gender'] ?? '') ?></td><td><?= esc($customer['email']) ?></td><td><?= esc($customer['phone_number'] ?? '') ?></td><td><?= esc($customer['address'] ?? '') ?></td><td><?= esc($customer['username']) ?></td><td><?= esc($customer['created_at'] ?? '') ?></td></tr><?php endforeach; ?>
                <?php if ($customerRecords === []): ?><tr><td colspan="9">No customer records available.</td></tr><?php endif; ?>
            </tbody></table></div></details>
            <details class="analytics-details"><summary>Monthly revenue details</summary><div class="table-wrap"><table><thead><tr><th>Month</th><th>Recorded total (PHP)</th></tr></thead><tbody><?php foreach ($monthlyRevenue as $month): ?><tr><td><?= esc($month['month']) ?></td><td><?= esc(number_format((float) $month['total'], 2)) ?></td></tr><?php endforeach; ?></tbody></table></div></details>
            <h3 class="analytics-table-title">Recent payment history and receipts</h3><div class="table-wrap"><table><thead><tr><th>Receipt</th><th>Request</th><th>Customer</th><th>Service</th><th>Amount</th><th>Method / Date</th></tr></thead><tbody><?php foreach ($payments as $payment): ?><tr><td><?= esc($payment['receipt_number']) ?></td><td>#<?= esc($payment['request_id']) ?></td><td><?= esc($payment['first_name'] . ' ' . $payment['last_name']) ?></td><td><?= esc($payment['service_type']) ?></td><td>PHP <?= esc(number_format((float) $payment['amount'], 2)) ?></td><td><?= esc($payment['method']) ?><br><?= esc($payment['paid_at']) ?></td></tr><?php endforeach; ?><?php if ($payments === []): ?><tr><td colspan="6">No payment records available.</td></tr><?php endif; ?></tbody></table></div>
        </section>
        <section class="panel" id="notifications"><h2>Notifications Control</h2><p>These are in-app notices only; this project does not send email or SMS.</p>
            <form action="<?= site_url('admin/notifications') ?>" method="post" class="workflow-form"><?= csrf_field() ?><label>Audience <select name="audience"><option value="customer">Customers</option><option value="employee">Employees</option><option value="all">Customers and employees</option></select></label><label>Type <select name="notification_type"><option value="reminder">Reminder</option><option value="offer">Offer</option><option value="update">System update</option></select></label><label>Title <input name="title" maxlength="120" required></label><label>Message <textarea name="message" maxlength="2000" required></textarea></label><button class="btn" type="submit">Send notification</button></form>
            <h3>Recent notifications</h3><?php foreach ($notifications as $notification): ?><div class="notice"><strong><?= esc($notification['title']) ?></strong> — <?= esc($notification['audience']) ?><p><?= esc($notification['message']) ?></p><small><?= esc($notification['created_at']) ?></small></div><?php endforeach; ?><?php if ($notifications === []): ?><div class="empty-state">No notifications sent.</div><?php endif; ?>
        </section>

        <section class="panel" id="settings"><h2>Service Categories, Price &amp; Availability</h2><p>Leave price blank if it has not been set. Availability is an admin note; actual bookings are scheduled in the calendar above.</p>
            <form action="<?= site_url('admin/services/create') ?>" method="post" class="workflow-form"><?= csrf_field() ?><label>New service category <input name="service_type" maxlength="30" required></label><button class="btn" type="submit">Add category</button></form>
            <?php foreach ($categories as $category): ?><form action="<?= site_url('admin/services') ?>" method="post" class="workflow-form category-form"><?= csrf_field() ?><input type="hidden" name="category_id" value="<?= esc($category['category_id']) ?>"><strong><?= esc($category['service_type']) ?></strong><label>Price (PHP, optional) <input name="price" value="<?= esc($category['price'] ?? '') ?>" inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?"></label><label>Availability <input name="availability" value="<?= esc($category['availability'] ?? '') ?>" maxlength="255" placeholder="e.g. Monday–Saturday"></label><label>Available <select name="is_active"><option value="1" <?= (int) $category['is_active'] ? 'selected' : '' ?>>Yes</option><option value="0" <?= ! (int) $category['is_active'] ? 'selected' : '' ?>>No</option></select></label><button class="btn secondary" type="submit">Save</button></form><?php endforeach; ?>
        </section>

        <section class="panel" id="issues"><h2>Technician Problem Reports</h2><div class="table-wrap"><table><thead><tr><th>Report</th><th>Technician</th><th>Request</th><th>Category / Details</th><th>Submitted</th><th>Status</th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td>#<?= esc($report['report_id']) ?></td><td><?= esc($report['first_name'] . ' ' . $report['last_name']) ?></td><td>#<?= esc($report['request_id']) ?> <?= esc($report['service_type']) ?></td><td><?= esc($report['category']) ?><br><?= esc($report['description']) ?></td><td><?= esc($report['created_at']) ?></td><td><form action="<?= site_url('admin/reports/status') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="report_id" value="<?= esc($report['report_id']) ?>"><select name="status"><option <?= $report['status'] === 'Open' ? 'selected' : '' ?>>Open</option><option <?= $report['status'] === 'In Review' ? 'selected' : '' ?>>In Review</option><option <?= $report['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option></select><button class="btn secondary" type="submit">Update</button></form></td></tr><?php endforeach; ?><?php if ($reports === []): ?><tr><td colspan="6">No technician reports yet.</td></tr><?php endif; ?></tbody></table></div></section>

        <section class="panel" id="support"><h2>Customer Support Tickets</h2><?php foreach ($tickets as $ticket): ?><article class="ticket"><h3>#<?= esc($ticket['ticket_id']) ?> — <?= esc($ticket['subject']) ?></h3><p><?= esc($ticket['first_name'] . ' ' . $ticket['last_name']) ?> · <?= esc($ticket['status']) ?></p><?php foreach (($ticketMessages[$ticket['ticket_id']] ?? []) as $message): ?><div class="notice"><strong><?= esc(ucfirst($message['sender_type'])) ?></strong> · <?= esc($message['sent_at']) ?><p><?= nl2br(esc($message['message'])) ?></p></div><?php endforeach; ?><?php if ($ticket['status'] !== 'Closed'): ?><form action="<?= site_url('admin/tickets/reply') ?>" method="post" class="workflow-form"><?= csrf_field() ?><input type="hidden" name="ticket_id" value="<?= esc($ticket['ticket_id']) ?>"><label>Reply <textarea name="message" maxlength="3000" required></textarea></label><label>Status <select name="status"><option>Answered</option><option>Closed</option></select></label><button class="btn" type="submit">Send reply</button></form><?php endif; ?></article><?php endforeach; ?><?php if ($tickets === []): ?><div class="empty-state">No customer support tickets.</div><?php endif; ?></section>

        <footer class="footer-section"><h3>Sun Son Solar</h3><p>Administrator tools</p><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="btn secondary" type="submit">Sign out</button></form></footer>
    </main>
</div>
</body>
</html>
