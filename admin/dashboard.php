<?php
/**
 * Caraway — Visitor Intelligence Dashboard
 * افتح هذا الملف في المتصفح لرؤية بيانات الزوار
 */

// ============================================================
//  حماية بسيطة بكلمة مرور — غيّرها!
// ============================================================
define('DASH_PASS', 'caraway2026');

session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['pass'] ?? '') === DASH_PASS) {
        $_SESSION['auth'] = true;
    } else {
        $error = 'كلمة المرور غير صحيحة';
    }
}

if (!($_SESSION['auth'] ?? false)) { ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Caraway</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Tajawal',sans-serif;background:#0f1117;color:#e8eaed;min-height:100vh;display:flex;align-items:center;justify-content:center}
.box{background:#1e1f26;border:1px solid rgba(138,180,248,.2);border-radius:16px;padding:40px;width:360px;text-align:center}
.logo{font-size:1.4rem;font-weight:700;color:#8ab4f8;margin-bottom:24px}
.logo span{color:#34a853}
input{width:100%;padding:11px 14px;border:1px solid rgba(255,255,255,.12);border-radius:10px;background:rgba(255,255,255,.06);color:#fff;font-family:inherit;font-size:.95rem;outline:none;margin-bottom:14px;text-align:center;letter-spacing:3px}
input:focus{border-color:#8ab4f8}
button{width:100%;padding:12px;border:none;border-radius:10px;background:#1a73e8;color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer}
.err{color:#f28b82;font-size:.85rem;margin-top:10px}
</style>
</head>
<body>
<div class="box">
  <div class="logo">Caraway <span>Intel</span></div>
  <form method="POST">
    <input type="password" name="pass" placeholder="كلمة المرور" autofocus>
    <button type="submit">دخول</button>
    <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  </form>
</div>
</body>
</html>
<?php
    exit;
}

// ============================================================
//  LOAD DATA
// ============================================================
$indexFile = __DIR__ . '/../data/visitors_index.json';
$visitors  = [];

if (file_exists($indexFile)) {
    $visitors = json_decode(file_get_contents($indexFile), true) ?: [];
}

// Stats
$total      = count($visitors);
$todayStr   = date('Y-m-d');
$todayCount = 0;
$browsers   = [];
$devices    = [];
$countries  = [];
$oss        = [];

foreach ($visitors as $v) {
    if (str_starts_with($v['ts'] ?? '', $todayStr)) $todayCount++;
    $br = explode(' ', $v['browser'] ?? '')[0] ?: 'Unknown';
    $browsers[$br] = ($browsers[$br] ?? 0) + 1;
    $dt = $v['device_type'] ?? 'Unknown';
    $devices[$dt] = ($devices[$dt] ?? 0) + 1;
    $co = $v['country'] ?: 'Unknown';
    $countries[$co] = ($countries[$co] ?? 0) + 1;
    $os = $v['os'] ?? 'Unknown';
    $oss[$os] = ($oss[$os] ?? 0) + 1;
}

arsort($browsers); arsort($devices); arsort($countries); arsort($oss);

function pct($n, $total) { return $total ? round($n/$total*100) : 0; }
function bar($n, $total, $color='#1a73e8') {
    $p = pct($n,$total);
    return "<div class='bar-wrap'><div class='bar-fill' style='width:{$p}%;background:{$color}'></div></div>";
}

// Handle detail view
$detail = null;
if (isset($_GET['id'])) {
    $detailFile = __DIR__ . '/../data/visits/' . preg_replace('/[^a-zA-Z0-9_]/','',$_GET['id']) . '.json';
    if (file_exists($detailFile)) $detail = json_decode(file_get_contents($detailFile), true);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Visitor Intelligence — Caraway</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f1117;--surface:#1e1f26;--surface2:#2a2b35;--text:#e8eaed;--muted:#9aa0a6;--border:rgba(255,255,255,.09);--blue:#8ab4f8;--green:#81c995;--yellow:#fdd663;--red:#f28b82;--orange:#ffb04c}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Tajawal','Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
a{color:inherit;text-decoration:none}

/* TOPBAR */
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;backdrop-filter:blur(10px)}
.brand{font-size:1.1rem;font-weight:700;color:var(--blue)}.brand span{color:var(--green)}
.logout{font-size:.82rem;color:var(--muted);padding:5px 14px;border:1px solid var(--border);border-radius:20px;cursor:pointer;background:none;color:var(--muted);font-family:inherit;transition:all .2s}
.logout:hover{border-color:var(--red);color:var(--red)}

/* LAYOUT */
.wrap{max-width:1400px;margin:0 auto;padding:28px 24px}

/* STAT CARDS */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px}
.stat{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 22px}
.stat-label{font-size:.78rem;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.6px}
.stat-val{font-size:2rem;font-weight:700;line-height:1}
.stat-sub{font-size:.78rem;color:var(--muted);margin-top:5px}
.c-blue{color:var(--blue)}.c-green{color:var(--green)}.c-yellow{color:var(--yellow)}.c-red{color:var(--red)}.c-orange{color:var(--orange)}

/* GRID 3 COL */
.grid3{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:24px}
.panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px}
.panel-title{font-size:.82rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;margin-bottom:16px}

/* BAR LIST */
.bar-item{margin-bottom:10px}
.bar-header{display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:4px}
.bar-wrap{height:6px;background:rgba(255,255,255,.07);border-radius:99px;overflow:hidden}
.bar-fill{height:100%;border-radius:99px;transition:width .4s ease}

/* TABLE */
.table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px}
.table-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.table-head h3{font-size:.9rem;font-weight:600}
.badge-count{background:rgba(138,180,248,.15);color:var(--blue);font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px}
table{width:100%;border-collapse:collapse}
th{font-size:.74rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:11px 16px;text-align:right;border-bottom:1px solid var(--border);white-space:nowrap}
td{padding:11px 16px;font-size:.84rem;border-bottom:1px solid rgba(255,255,255,.04);white-space:nowrap;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,.03)}
.tag{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;font-size:.74rem;font-weight:600}
.tag-mobile{background:rgba(251,188,4,.12);color:var(--yellow)}
.tag-desktop{background:rgba(138,180,248,.12);color:var(--blue)}
.tag-tablet{background:rgba(129,201,149,.12);color:var(--green)}
.detail-link{color:var(--blue);font-size:.78rem;padding:4px 10px;border:1px solid rgba(138,180,248,.3);border-radius:6px;cursor:pointer;transition:all .2s}
.detail-link:hover{background:rgba(138,180,248,.1)}

/* MODAL */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200;overflow-y:auto;padding:40px 20px}
.modal-bg.open{display:flex;align-items:flex-start;justify-content:center}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:860px;overflow:hidden}
.modal-top{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-top h3{font-size:1rem;font-weight:700}
.close-btn{background:none;border:1px solid var(--border);border-radius:50%;width:32px;height:32px;color:var(--muted);cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;transition:all .2s}
.close-btn:hover{border-color:var(--red);color:var(--red)}
.modal-body{padding:22px}
.sec{margin-bottom:20px}
.sec-title{font-size:.78rem;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.8px;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border)}
.kv-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.kv{background:var(--surface2);border-radius:8px;padding:10px 12px}
.kv-key{font-size:.73rem;color:var(--muted);margin-bottom:3px}
.kv-val{font-size:.88rem;font-weight:600;word-break:break-all}
.kv-val.hi{color:var(--blue)}

/* EMPTY */
.empty{text-align:center;padding:60px;color:var(--muted)}
.empty svg{width:56px;height:56px;margin:0 auto 16px;display:block;opacity:.3}
.empty h3{font-size:1.1rem;margin-bottom:8px}
.empty p{font-size:.88rem}

@media(max-width:600px){.kv-grid{grid-template-columns:1fr}th,td{padding:9px 10px;font-size:.78rem}}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">Caraway <span>Intel</span></div>
  <form method="POST" action="?logout=1" style="display:inline">
    <button class="logout" onclick="<?php if(isset($_GET['logout'])){ session_destroy(); header('Location: dashboard.php'); exit; } ?>">
      تسجيل الخروج
    </button>
  </form>
</div>

<?php if (isset($_GET['logout'])) { session_destroy(); header('Location: dashboard.php'); exit; } ?>

<div class="wrap">

  <!-- STATS -->
  <div class="stats">
    <div class="stat"><div class="stat-label">إجمالي الزيارات</div><div class="stat-val c-blue"><?= number_format($total) ?></div><div class="stat-sub">منذ البداية</div></div>
    <div class="stat"><div class="stat-label">اليوم</div><div class="stat-val c-green"><?= $todayCount ?></div><div class="stat-sub"><?= $todayStr ?></div></div>
    <div class="stat"><div class="stat-label">المتصفح الأكثر</div><div class="stat-val c-yellow" style="font-size:1.3rem"><?= array_key_first($browsers) ?: '—' ?></div><div class="stat-sub"><?= pct(reset($browsers)?:0, $total) ?>%</div></div>
    <div class="stat"><div class="stat-label">الجهاز الأكثر</div><div class="stat-val c-orange" style="font-size:1.3rem"><?= array_key_first($devices) ?: '—' ?></div><div class="stat-sub"><?= pct(reset($devices)?:0, $total) ?>%</div></div>
    <div class="stat"><div class="stat-label">الدولة الأكثر</div><div class="stat-val c-red" style="font-size:1.3rem"><?= array_key_first($countries) ?: '—' ?></div><div class="stat-sub"><?= pct(reset($countries)?:0, $total) ?>%</div></div>
  </div>

  <!-- CHARTS ROW -->
  <div class="grid3">
    <!-- Browsers -->
    <div class="panel">
      <div class="panel-title">المتصفحات</div>
      <?php foreach (array_slice($browsers,0,6,true) as $b=>$c): ?>
      <div class="bar-item">
        <div class="bar-header"><span><?= htmlspecialchars($b) ?></span><span><?= $c ?> (<?= pct($c,$total) ?>%)</span></div>
        <?= bar($c,$total,'#8ab4f8') ?>
      </div>
      <?php endforeach; if(!$browsers): echo '<p style="color:var(--muted);font-size:.85rem">لا توجد بيانات بعد</p>'; endif; ?>
    </div>
    <!-- Devices -->
    <div class="panel">
      <div class="panel-title">نوع الجهاز</div>
      <?php $dColors=['Desktop'=>'#8ab4f8','Mobile'=>'#fdd663','Tablet'=>'#81c995'];
      foreach (array_slice($devices,0,5,true) as $d=>$c): ?>
      <div class="bar-item">
        <div class="bar-header"><span><?= htmlspecialchars($d) ?></span><span><?= $c ?> (<?= pct($c,$total) ?>%)</span></div>
        <?= bar($c,$total,$dColors[$d]??'#8ab4f8') ?>
      </div>
      <?php endforeach; if(!$devices): echo '<p style="color:var(--muted);font-size:.85rem">لا توجد بيانات بعد</p>'; endif; ?>
    </div>
    <!-- OS -->
    <div class="panel">
      <div class="panel-title">أنظمة التشغيل</div>
      <?php foreach (array_slice($oss,0,6,true) as $o=>$c): ?>
      <div class="bar-item">
        <div class="bar-header"><span><?= htmlspecialchars($o) ?></span><span><?= $c ?> (<?= pct($c,$total) ?>%)</span></div>
        <?= bar($c,$total,'#81c995') ?>
      </div>
      <?php endforeach; if(!$oss): echo '<p style="color:var(--muted);font-size:.85rem">لا توجد بيانات بعد</p>'; endif; ?>
    </div>
    <!-- Countries -->
    <div class="panel">
      <div class="panel-title">الدول</div>
      <?php foreach (array_slice($countries,0,6,true) as $c=>$n): ?>
      <div class="bar-item">
        <div class="bar-header"><span><?= htmlspecialchars($c) ?></span><span><?= $n ?> (<?= pct($n,$total) ?>%)</span></div>
        <?= bar($n,$total,'#ffb04c') ?>
      </div>
      <?php endforeach; if(!$countries): echo '<p style="color:var(--muted);font-size:.85rem">لا توجد بيانات بعد</p>'; endif; ?>
    </div>
  </div>

  <!-- TABLE -->
  <div class="table-wrap">
    <div class="table-head">
      <h3>آخر الزيارات</h3>
      <span class="badge-count"><?= $total ?> زيارة</span>
    </div>
    <?php if ($visitors): ?>
    <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th>الوقت</th>
          <th>IP</th>
          <th>الموقع</th>
          <th>الجهاز</th>
          <th>نظام التشغيل</th>
          <th>المتصفح</th>
          <th>GPU</th>
          <th>الشاشة</th>
          <th>الاتصال</th>
          <th>مدة / تمرير</th>
          <th>التفاصيل</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (array_slice($visitors,0,100) as $v):
          $dt = $v['device_type'] ?? 'Desktop';
          $tagClass = 'tag-' . strtolower($dt);
          $ts = $v['ts'] ?? '';
          $tsShort = $ts ? date('d/m H:i', strtotime($ts)) : '—';
      ?>
        <tr>
          <td><?= htmlspecialchars($tsShort) ?></td>
          <td style="font-family:monospace;font-size:.78rem;color:var(--blue)"><?= htmlspecialchars($v['ip'] ?? '—') ?></td>
          <td><?= htmlspecialchars(trim(($v['city']??'').', '.($v['country']??''), ', ')) ?: '—' ?></td>
          <td><span class="tag <?= $tagClass ?>"><?= htmlspecialchars($dt) ?></span></td>
          <td><?= htmlspecialchars($v['os'] ?? '—') ?></td>
          <td><?= htmlspecialchars($v['browser'] ?? '—') ?></td>
          <td style="font-size:.76rem;color:var(--muted);max-width:140px;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars(explode('(',$v['gpu']??'')[0]) ?: '—' ?></td>
          <td><?= htmlspecialchars($v['screen'] ?? '—') ?></td>
          <td><?= htmlspecialchars($v['connection'] ?? '—') ?></td>
          <td><?= ($v['dwell_sec']??0) ?>ث / <?= ($v['scroll_pct']??0) ?>%</td>
          <td><a class="detail-link" href="?id=<?= urlencode($v['file']??'') ?>">عرض ▸</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
      <h3>لا توجد بيانات بعد</h3>
      <p>تأكد أن الـ analytics.js محمّل في صفحات موقعك وأن الـ PHP backend يعمل</p>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /.wrap -->

<?php if ($detail): ?>
<!-- DETAIL MODAL -->
<div class="modal-bg open" id="detail-modal">
  <div class="modal">
    <div class="modal-top">
      <h3>تفاصيل الزيارة — <?= htmlspecialchars($detail['page']['localTime'] ?? '') ?></h3>
      <button class="close-btn" onclick="location.href='dashboard.php'">✕</button>
    </div>
    <div class="modal-body">

      <div class="sec">
        <div class="sec-title">🌐 IP والموقع</div>
        <div class="kv-grid">
          <?php $ip = $detail['ip'] ?? []; ?>
          <div class="kv"><div class="kv-key">IP (Client)</div><div class="kv-val hi"><?= htmlspecialchars($ip['ip']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">IP (Server)</div><div class="kv-val hi"><?= htmlspecialchars($detail['server']['ip_server_side']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">المدينة</div><div class="kv-val"><?= htmlspecialchars($ip['city']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">الدولة</div><div class="kv-val"><?= htmlspecialchars($ip['country']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">ISP</div><div class="kv-val"><?= htmlspecialchars($ip['isp']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">الإحداثيات</div><div class="kv-val"><?= htmlspecialchars(($ip['lat']??'').', '.($ip['lon']??'')) ?></div></div>
        </div>
      </div>

      <div class="sec">
        <div class="sec-title">💻 الجهاز والمعالج</div>
        <div class="kv-grid">
          <?php $d = $detail['device'] ?? []; ?>
          <div class="kv"><div class="kv-key">نوع الجهاز</div><div class="kv-val"><?= htmlspecialchars($d['deviceType']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">نظام التشغيل</div><div class="kv-val"><?= htmlspecialchars($d['os']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">الماركة</div><div class="kv-val"><?= htmlspecialchars($d['brand']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">المعالج</div><div class="kv-val"><?= htmlspecialchars($d['cpu']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">عدد الأنوية</div><div class="kv-val"><?= htmlspecialchars($d['cores']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">الذاكرة RAM</div><div class="kv-val"><?= htmlspecialchars($d['ram']??'—') ?></div></div>
        </div>
      </div>

      <div class="sec">
        <div class="sec-title">🖥️ الشاشة والعرض</div>
        <div class="kv-grid">
          <?php $sc = $detail['screen'] ?? []; ?>
          <div class="kv"><div class="kv-key">دقة الشاشة</div><div class="kv-val"><?= htmlspecialchars($sc['screenResolution']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">دقة فعلية</div><div class="kv-val"><?= htmlspecialchars($sc['physicalResolution']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">Viewport</div><div class="kv-val"><?= htmlspecialchars($sc['viewport']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">DPR</div><div class="kv-val"><?= htmlspecialchars($sc['devicePixelRatio']??'—') ?>x</div></div>
          <div class="kv"><div class="kv-key">Color Gamut</div><div class="kv-val"><?= htmlspecialchars($sc['colorGamut']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">Dark Mode</div><div class="kv-val"><?= ($sc['prefersDark']??false) ? '🌙 نعم' : '☀️ لا' ?></div></div>
          <div class="kv"><div class="kv-key">Touch</div><div class="kv-val"><?= ($sc['isTouch']??false) ? 'نعم ('.($sc['touchPoints']??0).' نقاط)' : 'لا' ?></div></div>
          <div class="kv"><div class="kv-key">الاتجاه</div><div class="kv-val"><?= htmlspecialchars($sc['orientation']??'—') ?></div></div>
        </div>
      </div>

      <div class="sec">
        <div class="sec-title">🌍 المتصفح والاتصال</div>
        <div class="kv-grid">
          <?php $br = $detail['browser'] ?? []; $conn = $br['connection'] ?? []; ?>
          <div class="kv"><div class="kv-key">المتصفح</div><div class="kv-val"><?= htmlspecialchars(($br['name']??'').' '.($br['version']??'')) ?></div></div>
          <div class="kv"><div class="kv-key">المحرك</div><div class="kv-val"><?= htmlspecialchars($br['engine']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">GPU</div><div class="kv-val" style="font-size:.78rem"><?= htmlspecialchars($br['gpu']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">اللغة</div><div class="kv-val"><?= htmlspecialchars($br['language']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">المنطقة الزمنية</div><div class="kv-val"><?= htmlspecialchars(($br['timezone']??'—').' ('.$br['tzStr'].')') ?></div></div>
          <div class="kv"><div class="kv-key">نوع الاتصال</div><div class="kv-val"><?= htmlspecialchars($conn['effectiveType']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">السرعة</div><div class="kv-val"><?= htmlspecialchars($conn['downlink']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">RTT</div><div class="kv-val"><?= htmlspecialchars($conn['rtt']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">Canvas Hash</div><div class="kv-val hi" style="font-size:.78rem;font-family:monospace"><?= htmlspecialchars($br['canvasHash']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">Audio Hash</div><div class="kv-val" style="font-size:.78rem;font-family:monospace"><?= htmlspecialchars($br['audioHash']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">DNT</div><div class="kv-val"><?= htmlspecialchars($br['dnt']??'—') ?></div></div>
        </div>
      </div>

      <div class="sec">
        <div class="sec-title">🎯 سلوك الزائر</div>
        <div class="kv-grid">
          <?php $bh = $detail['behavior'] ?? []; ?>
          <div class="kv"><div class="kv-key">مدة البقاء</div><div class="kv-val"><?= ($bh['dwellSec']??0) ?> ثانية</div></div>
          <div class="kv"><div class="kv-key">أقصى تمرير</div><div class="kv-val"><?= ($bh['maxScrollPct']??0) ?>%</div></div>
          <div class="kv"><div class="kv-key">النقرات</div><div class="kv-val"><?= ($bh['clicks']??0) ?></div></div>
          <div class="kv"><div class="kv-key">ضغطات المفاتيح</div><div class="kv-val"><?= ($bh['keystrokes']??0) ?></div></div>
          <div class="kv"><div class="kv-key">مغادرة التبويب</div><div class="kv-val"><?= ($bh['focusLostCount']??0) ?> مرة</div></div>
          <div class="kv"><div class="kv-key">نسخ المحتوى</div><div class="kv-val"><?= ($bh['copyEvents']??0) ?></div></div>
        </div>
      </div>

      <div class="sec">
        <div class="sec-title">📄 الصفحة والمصدر</div>
        <div class="kv-grid">
          <?php $pg = $detail['page'] ?? []; ?>
          <div class="kv"><div class="kv-key">الصفحة</div><div class="kv-val"><?= htmlspecialchars($pg['path']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">المصدر (Referrer)</div><div class="kv-val" style="font-size:.78rem"><?= htmlspecialchars($pg['referrer']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">وقت الزيارة</div><div class="kv-val"><?= htmlspecialchars($pg['localTime']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">اليوم</div><div class="kv-val"><?= htmlspecialchars($pg['dayOfWeek']??'—') ?></div></div>
          <div class="kv"><div class="kv-key">Session ID</div><div class="kv-val hi" style="font-size:.73rem;font-family:monospace"><?= htmlspecialchars($pg['sessionId']??'—') ?></div></div>
        </div>
      </div>

      <!-- User Agent -->
      <div class="sec">
        <div class="sec-title">🔍 User Agent الكامل</div>
        <div class="kv"><div class="kv-key">User Agent</div><div class="kv-val" style="font-size:.76rem;line-height:1.6;word-break:break-all"><?= htmlspecialchars($br['userAgent']??'—') ?></div></div>
      </div>

    </div>
  </div>
</div>
<?php endif; ?>

</body>
</html>