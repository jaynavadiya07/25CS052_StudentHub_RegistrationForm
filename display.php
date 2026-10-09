<?php
/* =============================================================
   display.php – View Stored Student Records (CSV / JSON)
   ============================================================= */

$jsonFile = __DIR__ . "/data/registrations.json";
$records  = [];

if (file_exists($jsonFile)) {
    $records = json_decode(file_get_contents($jsonFile), true) ?? [];
}

$total = count($records);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stored Student Records</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }

    :root {
      --accent:   #0d9488;
      --accent-h: #0f766e;
      --accent-l: #ccfbf1;
      --dark:     #0f172a;
      --text:     #0f172a;
      --text-2:   #475569;
      --text-3:   #94a3b8;
      --border:   #e2e8f0;
      --bg:       #f8fafc;
      --card:     #ffffff;
      --r:        12px;
    }

    body {
      font-family: 'DM Sans', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      padding: 40px 24px;
    }

    .container {
      max-width: 960px;
      margin: 0 auto;
    }

    /* ── HEADER ── */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 28px;
    }

    .page-header .left h1 {
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: -.4px;
      color: var(--text);
      margin-bottom: 4px;
    }
    .page-header .left p {
      font-size: .86rem;
      color: var(--text-2);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--accent-l);
      color: var(--accent-h);
      padding: 6px 14px;
      border-radius: 100px;
      font-size: .8rem;
      font-weight: 700;
    }
    .badge .num {
      background: var(--accent);
      color: #fff;
      width: 22px; height: 22px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: .72rem;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 20px;
      background: var(--accent);
      color: #fff;
      text-decoration: none;
      border-radius: var(--r);
      font-size: .86rem;
      font-weight: 600;
      transition: .2s;
    }
    .back-link:hover {
      background: var(--accent-h);
      box-shadow: 0 4px 16px rgba(13,148,136,.25);
      transform: translateY(-1px);
    }

    /* ── TABLE CARD ── */
    .table-card {
      background: var(--card);
      border-radius: var(--r);
      border: 1px solid var(--border);
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 6px 16px rgba(0,0,0,.04);
    }

    .table-wrap {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: .88rem;
    }

    thead {
      background: var(--dark);
      color: #fff;
    }
    thead th {
      padding: 14px 18px;
      font-weight: 600;
      font-size: .78rem;
      text-transform: uppercase;
      letter-spacing: .5px;
      text-align: left;
      white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .15s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f1f5f9; }

    tbody td {
      padding: 14px 18px;
      color: var(--text);
      vertical-align: middle;
    }

    /* row number */
    .row-num {
      color: var(--text-3);
      font-weight: 600;
      font-size: .82rem;
      text-align: center;
      width: 40px;
    }

    /* name cell */
    .name-cell {
      font-weight: 600;
      color: var(--text);
    }
    .name-cell .avatar {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px; height: 32px;
      border-radius: 50%;
      background: var(--accent-l);
      color: var(--accent);
      font-weight: 700;
      font-size: .78rem;
      margin-right: 10px;
      vertical-align: middle;
    }

    /* email */
    .email-cell {
      color: var(--accent);
      font-weight: 500;
    }

    /* course badge */
    .course-tag {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 6px;
      background: #f1f5f9;
      color: var(--text-2);
      font-size: .78rem;
      font-weight: 600;
      white-space: nowrap;
    }

    /* year + gender */
    .meta-tag {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 5px;
      font-size: .76rem;
      font-weight: 600;
    }
    .meta-yr { background: #ede9fe; color: #6d28d9; }
    .meta-gn { background: #fef3c7; color: #92400e; }

    /* timestamp */
    .ts-cell {
      color: var(--text-3);
      font-size: .78rem;
      white-space: nowrap;
    }

    /* ── EMPTY STATE ── */
    .empty {
      text-align: center;
      padding: 60px 20px;
    }
    .empty .icon {
      font-size: 3rem;
      margin-bottom: 12px;
      opacity: .5;
    }
    .empty h3 {
      font-size: 1.1rem;
      color: var(--text);
      margin-bottom: 6px;
    }
    .empty p {
      color: var(--text-3);
      font-size: .88rem;
      margin-bottom: 20px;
    }

    /* ── FOOTER ── */
    .foot {
      text-align: center;
      margin-top: 24px;
      font-size: .76rem;
      color: var(--text-3);
    }

    @media (max-width: 600px) {
      .page-header { flex-direction: column; align-items: flex-start; }
      tbody td, thead th { padding: 10px 12px; font-size: .82rem; }
    }
  </style>
</head>
<body>

<div class="container">

  <!-- HEADER -->
  <div class="page-header">
    <div class="left">
      <h1>Stored Student Records</h1>
      <p>Data from <strong>registrations.json</strong> &amp; <strong>registrations.csv</strong></p>
    </div>
    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
      <div class="badge"><span class="num"><?php echo $total; ?></span> Total Records</div>
      <a href="index.php" class="back-link">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 12H5m6-6l-6 6 6 6" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Back to Form
      </a>
    </div>
  </div>

  <!-- TABLE -->
  <div class="table-card">
    <?php if ($total > 0): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Course</th>
            <th>Year</th>
            <th>Gender</th>
            <th>Registered At</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $i => $r): ?>
          <tr>
            <td class="row-num"><?php echo $i + 1; ?></td>
            <td class="name-cell">
              <span class="avatar"><?php echo strtoupper(substr($r['fullName'] ?? '', 0, 1)); ?></span>
              <?php echo htmlspecialchars($r['fullName'] ?? ''); ?>
            </td>
            <td class="email-cell"><?php echo htmlspecialchars($r['email'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($r['mobile'] ?? ''); ?></td>
            <td><span class="course-tag"><?php echo htmlspecialchars($r['course'] ?? ''); ?></span></td>
            <td><span class="meta-tag meta-yr">Year <?php echo htmlspecialchars($r['year'] ?? ''); ?></span></td>
            <td><span class="meta-tag meta-gn"><?php echo htmlspecialchars($r['gender'] ?? ''); ?></span></td>
            <td class="ts-cell"><?php echo htmlspecialchars($r['timestamp'] ?? ''); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty">
      <div class="icon">📭</div>
      <h3>No records yet</h3>
      <p>Submit the registration form to see student data here.</p>
      <a href="index.php" class="back-link">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 12H5m6-6l-6 6 6 6" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Go to Registration Form
      </a>
    </div>
    <?php endif; ?>
  </div>

  <p class="foot">Practical 7 &middot; CSV / JSON Stored Data View</p>
</div>

</body>
</html>
