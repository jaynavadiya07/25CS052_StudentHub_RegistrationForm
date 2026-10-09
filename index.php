<?php
/* =============================================================
   Practical 7 – Student Registration Form (PHP)
   ---------------------------------------------------------
   1. PHP Form        – Registration form
   2. POST Method     – method="POST"
   3. Validation      – Empty / invalid input checks
   4. Sanitization    – htmlspecialchars + trim + stripslashes
   5. File Storage    – CSV + JSON
   6. Success Message – On successful submission
   7. Error Message   – Per-field validation errors
   ============================================================= */

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

$errors  = [];
$success = false;
$fullName = $email = $mobile = $course = $year = $gender = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = sanitize($_POST["fullName"] ?? "");
    if (empty($fullName))
        $errors["fullName"] = "Full name is required.";
    elseif (!preg_match("/^[A-Za-z\s]{3,50}$/", $fullName))
        $errors["fullName"] = "Only letters & spaces (3–50 chars).";

    $email = sanitize($_POST["email"] ?? "");
    if (empty($email))
        $errors["email"] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors["email"] = "Enter a valid email address.";

    $mobile = sanitize($_POST["mobile"] ?? "");
    if (empty($mobile))
        $errors["mobile"] = "Mobile number is required.";
    elseif (!preg_match("/^[6-9]\d{9}$/", $mobile))
        $errors["mobile"] = "Valid 10-digit number required (starts 6–9).";

    $allowed = ["CSE","IT","ECE","ME"];
    $course  = sanitize($_POST["course"] ?? "");
    if (empty($course))                   $errors["course"] = "Select a course.";
    elseif (!in_array($course, $allowed)) $errors["course"] = "Invalid course.";

    $allowedY = ["1","2","3","4"];
    $year     = sanitize($_POST["year"] ?? "");
    if (empty($year))                    $errors["year"] = "Select a year.";
    elseif (!in_array($year, $allowedY)) $errors["year"] = "Invalid year.";

    $allowedG = ["Male","Female","Other"];
    $gender   = sanitize($_POST["gender"] ?? "");
    if (empty($gender))                   $errors["gender"] = "Select gender.";
    elseif (!in_array($gender, $allowedG)) $errors["gender"] = "Invalid gender.";

    if (empty($errors)) {
        $ts  = date("Y-m-d H:i:s");
        $rec = ["timestamp"=>$ts] + compact('fullName','email','mobile','course','year','gender');

        $dir = __DIR__."/data";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // CSV
        $csv = "$dir/registrations.csv";
        $fp  = fopen($csv, "a");
        if (!file_exists($csv) || filesize($csv) === 0) fputcsv($fp, array_keys($rec));
        fputcsv($fp, array_values($rec));
        fclose($fp);

        // JSON
        $jf  = "$dir/registrations.json";
        $all = file_exists($jf) ? (json_decode(file_get_contents($jf), true) ?? []) : [];
        $all[] = $rec;
        file_put_contents($jf, json_encode($all, JSON_PRETTY_PRINT));

        $success  = true;
        $fullName = $email = $mobile = $course = $year = $gender = "";
    }
}

$yearLabels   = ["1"=>"1st Year","2"=>"2nd Year","3"=>"3rd Year","4"=>"4th Year"];
$courseLabels  = ["CSE"=>"Computer Science & Engg.","IT"=>"Information Technology","ECE"=>"Electronics & Comm.","ME"=>"Mechanical Engineering"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Practical 7 – Student Registration</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }

    :root {
      --accent:    #0d9488;
      --accent-h:  #0f766e;
      --accent-l:  #ccfbf1;
      --accent-xl: #f0fdfa;
      --dark:      #0f172a;
      --dark-2:    #1e293b;
      --text:      #0f172a;
      --text-2:    #475569;
      --text-3:    #94a3b8;
      --border:    #e2e8f0;
      --bg:        #f8fafc;
      --card:      #ffffff;
      --err:       #e11d48;
      --err-bg:    #fff1f2;
      --ok:        #059669;
      --ok-bg:     #ecfdf5;
      --r:         12px;
    }

    html { font-size: 15px; }
    body {
      font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
    }

    /* ═══════════  LAYOUT  ═══════════ */
    .shell { display: flex; min-height: 100vh; }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 400px;
      background: var(--dark);
      color: #fff;
      padding: 52px 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }

    /* decorative mesh */
    .sidebar::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse 600px 400px at 0% 0%,   rgba(13,148,136,.25), transparent),
        radial-gradient(ellipse 500px 500px at 100% 100%, rgba(6,182,212,.15), transparent);
      pointer-events: none;
    }

    .sb-top, .sb-mid, .sb-bot { position: relative; z-index: 1; }

    .sb-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(13,148,136,.2);
      border: 1px solid rgba(13,148,136,.3);
      padding: 6px 14px;
      border-radius: 100px;
      font-size: .78rem;
      font-weight: 600;
      color: #5eead4;
      letter-spacing: .4px;
      text-transform: uppercase;
      margin-bottom: 28px;
    }
    .sb-badge .dot {
      width: 7px; height: 7px;
      background: #2dd4bf;
      border-radius: 50%;
      animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%     { opacity: .4; transform: scale(.7); }
    }

    .sb-title {
      font-size: 1.85rem;
      font-weight: 700;
      line-height: 1.25;
      letter-spacing: -.5px;
      margin-bottom: 14px;
    }
    .sb-title span { color: #2dd4bf; }

    .sb-desc {
      color: #94a3b8;
      font-size: .9rem;
      line-height: 1.65;
    }

    /* features */
    .features { margin-top: 0; }
    .feat {
      display: flex;
      gap: 16px;
      padding: 18px 0;
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .feat:last-child { border-bottom: none; }

    .feat-icon {
      width: 42px; height: 42px;
      border-radius: 10px;
      background: rgba(13,148,136,.12);
      border: 1px solid rgba(13,148,136,.18);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 1.15rem;
    }
    .feat h4 {
      font-size: .88rem;
      font-weight: 600;
      color: #e2e8f0;
      margin-bottom: 2px;
    }
    .feat p {
      font-size: .78rem;
      color: #64748b;
      line-height: 1.45;
    }

    .sb-bot {
      font-size: .76rem;
      color: #475569;
      border-top: 1px solid rgba(255,255,255,.06);
      padding-top: 20px;
    }

    /* ── MAIN ── */
    .main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px 44px;
      overflow-y: auto;
    }

    .form-wrap { width: 100%; max-width: 500px; }

    .heading {
      font-size: 1.55rem;
      font-weight: 700;
      letter-spacing: -.4px;
      margin-bottom: 4px;
    }
    .subheading {
      color: var(--text-2);
      font-size: .88rem;
      margin-bottom: 30px;
    }

    /* ── ALERTS ── */
    .alert {
      display: flex; align-items: flex-start; gap: 12px;
      padding: 14px 18px;
      border-radius: var(--r);
      margin-bottom: 24px;
      font-size: .86rem;
      font-weight: 500;
      line-height: 1.5;
      animation: fadeIn .4s ease both;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .alert-ok  { background: var(--ok-bg); color: #064e3b; border: 1px solid #a7f3d0; }
    .alert-err { background: var(--err-bg); color: #9f1239; border: 1px solid #fecdd3; }
    .alert-icon {
      width: 22px; height: 22px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      font-size: .72rem;
      font-weight: 700;
      color: #fff;
      margin-top: 1px;
    }
    .alert-ok  .alert-icon { background: var(--ok); }
    .alert-err .alert-icon { background: var(--err); }

    /* ── FIELDS ── */
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .fld { margin-bottom: 18px; }

    .fld label {
      display: block;
      font-size: .8rem;
      font-weight: 600;
      color: var(--text-2);
      margin-bottom: 6px;
      letter-spacing: .2px;
    }
    .fld label .star { color: var(--err); }

    .fld input, .fld select {
      width: 100%;
      padding: 11px 14px;
      font-size: .9rem;
      font-family: inherit;
      color: var(--text);
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      outline: none;
      transition: .2s;
    }
    .fld input::placeholder { color: var(--text-3); }
    .fld select { cursor: pointer; }

    .fld input:focus, .fld select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3.5px rgba(13,148,136,.12);
    }

    .fld.err input, .fld.err select {
      border-color: var(--err);
      background: var(--err-bg);
    }
    .fld .e-msg {
      display: flex; align-items: center; gap: 4px;
      color: var(--err);
      font-size: .76rem;
      font-weight: 500;
      margin-top: 5px;
    }

    /* select arrow */
    .sw { position: relative; }
    .sw select { appearance: none; padding-right: 34px; }
    .sw::after {
      content: '';
      position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%) rotate(45deg);
      width: 7px; height: 7px;
      border-right: 2px solid var(--text-3);
      border-bottom: 2px solid var(--text-3);
      pointer-events: none;
    }

    /* ── GENDER CARDS ── */
    .g-row { display: flex; gap: 10px; }

    .g-opt { flex: 1; }
    .g-opt input { display: none; }
    .g-opt label {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      padding: 12px 8px;
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      cursor: pointer;
      font-size: .86rem;
      font-weight: 500;
      color: var(--text-2);
      background: var(--card);
      transition: .2s;
      margin-bottom: 0;
    }
    .g-opt label:hover {
      border-color: var(--accent);
      color: var(--accent);
      background: var(--accent-xl);
    }
    .g-opt input:checked + label {
      border-color: var(--accent);
      background: var(--accent-l);
      color: var(--accent-h);
      font-weight: 700;
      box-shadow: 0 0 0 3.5px rgba(13,148,136,.1);
    }
    .g-opt .circle {
      width: 16px; height: 16px;
      border-radius: 50%;
      border: 2px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      transition: .2s;
      flex-shrink: 0;
    }
    .g-opt .circle::after {
      content: '';
      width: 7px; height: 7px;
      border-radius: 50%;
      background: transparent;
      transition: .2s;
    }
    .g-opt input:checked + label .circle {
      border-color: var(--accent);
    }
    .g-opt input:checked + label .circle::after {
      background: var(--accent);
    }

    /* ── SEPARATOR ── */
    .sep { height: 1px; background: var(--border); margin: 6px 0 24px; }

    /* ── BUTTON ── */
    .submit-btn {
      width: 100%;
      padding: 14px;
      font-family: inherit;
      font-size: .94rem;
      font-weight: 700;
      color: #fff;
      background: var(--accent);
      border: none;
      border-radius: var(--r);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: .25s;
      position: relative;
      overflow: hidden;
    }
    .submit-btn:hover {
      background: var(--accent-h);
      box-shadow: 0 8px 24px rgba(13,148,136,.28);
      transform: translateY(-1px);
    }
    .submit-btn:active { transform: translateY(0); }
    .submit-btn svg { transition: transform .2s; }
    .submit-btn:hover svg { transform: translateX(3px); }

    .view-link {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 13px;
      margin-top: 12px;
      font-family: inherit;
      font-size: .88rem;
      font-weight: 600;
      color: var(--accent);
      background: transparent;
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      text-decoration: none;
      transition: .2s;
    }
    .view-link:hover {
      border-color: var(--accent);
      background: var(--accent-l);
      color: var(--accent-h);
    }

    .footer {
      text-align: center;
      margin-top: 22px;
      font-size: .76rem;
      color: var(--text-3);
    }

    /* ═══════════  RESPONSIVE  ═══════════ */
    @media (max-width: 900px) {
      .sidebar { display: none; }
      .main { padding: 32px 20px; }
    }
    @media (max-width: 520px) {
      .grid { grid-template-columns: 1fr; gap: 0; }
      .g-row { flex-direction: column; }
    }
  </style>
</head>
<body>

<div class="shell">

  <!-- ═══════  SIDEBAR  ═══════ -->
  <aside class="sidebar">
    <div class="sb-top">
      <div class="sb-badge"><span class="dot"></span> Practical 7</div>
      <h1 class="sb-title">Student<br><span>Registration</span><br>Portal</h1>
      <p class="sb-desc">A complete PHP-powered registration system with server-side security and persistent file storage.</p>
    </div>

    <div class="sb-mid features">
      <div class="feat">
        <div class="feat-icon">📮</div>
        <div><h4>POST Method</h4><p>Secure HTTP POST form submission</p></div>
      </div>
      <div class="feat">
        <div class="feat-icon">🛡️</div>
        <div><h4>Server Validation</h4><p>Regex & filter checks on every field</p></div>
      </div>
      <div class="feat">
        <div class="feat-icon">🧹</div>
        <div><h4>Sanitization</h4><p>trim · stripslashes · htmlspecialchars</p></div>
      </div>
      <div class="feat">
        <div class="feat-icon">💾</div>
        <div><h4>Dual Storage</h4><p>CSV & JSON file persistence</p></div>
      </div>
    </div>

    <div class="sb-bot">
      Web Development Fundamentals &middot; PHP
    </div>
  </aside>

  <!-- ═══════  FORM  ═══════ -->
  <main class="main">
    <div class="form-wrap">

      <h1 class="heading">Create your account</h1>
      <p class="subheading">Enter your details below to complete registration.</p>

      <?php if ($success): ?>
        <div class="alert alert-ok">
          <div class="alert-icon">✓</div>
          <div><strong>Registration successful!</strong><br>Your data has been saved to CSV &amp; JSON files.</div>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-err">
          <div class="alert-icon">!</div>
          <div><strong>Submission failed.</strong><br>Please fix the highlighted fields below.</div>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">

        <!-- Full Name -->
        <div class="fld <?php echo isset($errors['fullName'])?'err':''; ?>">
          <label for="fullName">Full Name <span class="star">*</span></label>
          <input type="text" id="fullName" name="fullName" placeholder="Jay Navadiya" value="<?php echo $fullName; ?>">
          <?php if(isset($errors['fullName'])): ?><div class="e-msg">⚠ <?php echo $errors['fullName']; ?></div><?php endif; ?>
        </div>

        <!-- Email + Mobile -->
        <div class="grid">
          <div class="fld <?php echo isset($errors['email'])?'err':''; ?>">
            <label for="email">Email Address <span class="star">*</span></label>
            <input type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo $email; ?>">
            <?php if(isset($errors['email'])): ?><div class="e-msg">⚠ <?php echo $errors['email']; ?></div><?php endif; ?>
          </div>
          <div class="fld <?php echo isset($errors['mobile'])?'err':''; ?>">
            <label for="mobile">Mobile Number <span class="star">*</span></label>
            <input type="tel" id="mobile" name="mobile" placeholder="9876543210" maxlength="10" value="<?php echo $mobile; ?>">
            <?php if(isset($errors['mobile'])): ?><div class="e-msg">⚠ <?php echo $errors['mobile']; ?></div><?php endif; ?>
          </div>
        </div>

        <!-- Course + Year -->
        <div class="grid">
          <div class="fld <?php echo isset($errors['course'])?'err':''; ?>">
            <label for="course">Course <span class="star">*</span></label>
            <div class="sw">
              <select id="course" name="course">
                <option value="">Select course</option>
                <?php foreach($courseLabels as $k=>$v): ?>
                  <option value="<?php echo $k; ?>" <?php echo $course===$k?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if(isset($errors['course'])): ?><div class="e-msg">⚠ <?php echo $errors['course']; ?></div><?php endif; ?>
          </div>
          <div class="fld <?php echo isset($errors['year'])?'err':''; ?>">
            <label for="year">Academic Year <span class="star">*</span></label>
            <div class="sw">
              <select id="year" name="year">
                <option value="">Select year</option>
                <?php foreach($yearLabels as $k=>$v): ?>
                  <option value="<?php echo $k; ?>" <?php echo $year===$k?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if(isset($errors['year'])): ?><div class="e-msg">⚠ <?php echo $errors['year']; ?></div><?php endif; ?>
          </div>
        </div>

        <!-- Gender -->
        <div class="fld <?php echo isset($errors['gender'])?'err':''; ?>">
          <label>Gender <span class="star">*</span></label>
          <div class="g-row">
            <div class="g-opt">
              <input type="radio" name="gender" value="Male" id="gM" <?php echo $gender==='Male'?'checked':''; ?>>
              <label for="gM"><span class="circle"></span> Male</label>
            </div>
            <div class="g-opt">
              <input type="radio" name="gender" value="Female" id="gF" <?php echo $gender==='Female'?'checked':''; ?>>
              <label for="gF"><span class="circle"></span> Female</label>
            </div>
            <div class="g-opt">
              <input type="radio" name="gender" value="Other" id="gO" <?php echo $gender==='Other'?'checked':''; ?>>
              <label for="gO"><span class="circle"></span> Other</label>
            </div>
          </div>
          <?php if(isset($errors['gender'])): ?><div class="e-msg">⚠ <?php echo $errors['gender']; ?></div><?php endif; ?>
        </div>

        <div class="sep"></div>

        <button type="submit" class="submit-btn">
          Register Student
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 12h14m-6-6l6 6-6 6" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </form>

      <a href="display.php" class="view-link">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        View Stored Records
      </a>

      <p class="footer">Practical 7 &middot; PHP Form &middot; POST &middot; Validation &middot; Sanitization &middot; CSV/JSON Storage</p>
    </div>
  </main>
</div>

</body>
</html>
