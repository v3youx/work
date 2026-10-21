<?php
$response_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gmail_user   = isset($_POST['gmail_user']) ? trim($_POST['gmail_user']) : '';
    $gmail_pass   = isset($_POST['gmail_pass']) ? trim($_POST['gmail_pass']) : '';
    $from_name    = isset($_POST['from_name']) ? trim($_POST['from_name']) : '';
    $subject      = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message      = isset($_POST['message']) ? $_POST['message'] : '';
    $link_url     = isset($_POST['link_url']) ? trim($_POST['link_url']) : '';
    $link_text    = isset($_POST['link_text']) ? trim($_POST['link_text']) : '';
    $send_type    = isset($_POST['send_type']) ? $_POST['send_type'] : 'individual';
    
    $recipients = [];

    if ($send_type == "individual") {
        $single_email = isset($_POST['single_email']) ? trim($_POST['single_email']) : '';
        if (filter_var($single_email, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $single_email;
        }
    } else {
        if (isset($_FILES['recipient_file']) && $_FILES['recipient_file']['error'] == 0) {
            $file_tmp = $_FILES['recipient_file']['tmp_name'];
            $file_lines = file($file_tmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($file_lines as $line) {
                $clean_email = trim($line);
                if (filter_var($clean_email, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = $clean_email;
                }
            }
        }
    }

    // Message Body & Hyperlink preparation
    $full_body = nl2br(htmlspecialchars($message));
    if (!empty($link_url)) {
        $display_text = !empty($link_text) ? htmlspecialchars($link_text) : htmlspecialchars($link_url);
        $full_body .= "<br><br><a href='" . htmlspecialchars($link_url) . "' style='color: #0d6efd; font-weight: bold; text-decoration: underline;'>" . $display_text . "</a>";
    }

    if (empty($recipients)) {
        $response_msg = "<div class='alert alert-danger'>❌ Error: Valid recipient email nahi mili!</div>";
    } else {
        $success_count = 0;
        $fail_count = 0;

        // Native Gmail SMTP function using Sockets
        function sendGmailSMTP($to, $from_email, $from_name, $subject, $html_body, $username, $password) {
            $smtp_server = "tls://smtp.gmail.com";
            $port = 587;
            
            $socket = @fsockopen($smtp_server, $port, $errno, $errstr, 10);
            if (!$socket) return false;

            $get_server_response = function($socket) {
                $response = "";
                while ($str = fgets($socket, 515)) {
                    $response .= $str;
                    if (substr($str, 3, 1) == " ") break;
                }
                return $response;
            };

            $get_server_response($socket);

            // EHLO
            fwrite($socket, "EHLO localhost\r\n");
            $get_server_response($socket);

            // STARTTLS
            fwrite($socket, "STARTTLS\r\n");
            $res = $get_server_response($socket);
            if (strpos($res, "220") === false) {
                fclose($socket);
                return false;
            }

            // Enable Crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }

            // EHLO again after TLS
            fwrite($socket, "EHLO localhost\r\n");
            $get_server_response($socket);

            // AUTH LOGIN
            fwrite($socket, "AUTH LOGIN\r\n");
            $get_server_response($socket);

            fwrite($socket, base64_encode($username) . "\r\n");
            $get_server_response($socket);

            fwrite($socket, base64_encode($password) . "\r\n");
            $auth_res = $get_server_response($socket);
            if (strpos($auth_res, "235") === false) {
                fclose($socket);
                return false;
            }

            // MAIL FROM
            fwrite($socket, "MAIL FROM: <$username>\r\n");
            $get_server_response($socket);

            // RCPT TO
            fwrite($socket, "RCPT TO: <$to>\r\n");
            $get_server_response($socket);

            // DATA
            fwrite($socket, "DATA\r\n");
            $get_server_response($socket);

            // Headers & Body
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . "=?UTF-8?B?" . base64_encode($from_name) . "?=" . " <$username>\r\n";
            $headers .= "To: <$to>\r\n";
            $headers .= "Subject: " . "=?UTF-8?B?" . base64_encode($subject) . "?=" . "\r\n\r\n";
            
            fwrite($socket, $headers . $html_body . "\r\n.\r\n");
            $data_res = $get_server_response($socket);

            // QUIT
            fwrite($socket, "QUIT\r\n");
            fclose($socket);

            return (strpos($data_res, "250") !== false);
        }

        foreach ($recipients as $to) {
            $sent = sendGmailSMTP($to, $gmail_user, $from_name, $subject, $full_body, $gmail_user, $gmail_pass);
            if ($sent) {
                $success_count++;
            } else {
                $fail_count++;
            }
            usleep(300000); // 0.3s delay
        }

        $response_msg = "<div class='alert alert-success'>✅ Mail Processed! Total Sent: $success_count | Failed: $fail_count</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail SMTP Email Sender</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; padding: 30px 10px; margin: 0; }
        .container { max-width: 650px; background: #ffffff; padding: 25px; margin: auto; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: 600; color: #333; display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="url"], textarea, select {
            width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; box-sizing: border-box; font-size: 14px;
        }
        textarea { height: 100px; resize: vertical; }
        .link-group { background: #eef6ff; padding: 12px; border-radius: 5px; border: 1px solid #b6d4fe; margin-bottom: 15px; }
        button { background-color: #0d6efd; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background-color: #0b5ed7; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; }
        .alert-danger { background-color: #f8d7da; color: #842029; }
        .small-note { font-size: 12px; color: #6c757d; margin-top: 3px; }
    </style>
    <script>
        function toggleRecipientType(val) {
            if (val === 'individual') {
                document.getElementById('individual_field').style.display = 'block';
                document.getElementById('bulk_field').style.display = 'none';
            } else {
                document.getElementById('individual_field').style.display = 'none';
                document.getElementById('bulk_field').style.display = 'block';
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Gmail SMTP Email Sender</h2>
    
    <?php echo $response_msg; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Gmail Address (Sender Email):</label>
            <input type="email" name="gmail_user" placeholder="example@gmail.com" required>
        </div>

        <div class="form-group">
            <label>Google App Password:</label>
            <input type="password" name="gmail_pass" placeholder="16-digit App Password" required>
            <div class="small-note">Note: App Password use karein, apna normal Gmail password nahi.</div>
        </div>

        <div class="form-group">
            <label>Sender Name:</label>
            <input type="text" name="from_name" placeholder="John Doe / Company Name" required>
        </div>

        <div class="form-group">
            <label>Subject:</label>
            <input type="text" name="subject" placeholder="Enter Email Subject" required>
        </div>

        <div class="form-group">
            <label>Send Option:</label>
            <select name="send_type" onchange="toggleRecipientType(this.value)">
                <option value="individual">Individual Email</option>
                <option value="bulk">Bulk Email (Upload File)</option>
            </select>
        </div>

        <div class="form-group" id="individual_field">
            <label>Target Email Address:</label>
            <input type="email" name="single_email" placeholder="recipient@example.com">
        </div>

        <div class="form-group" id="bulk_field" style="display: none;">
            <label>Upload Email List (.txt or .csv - 1 email per line):</label>
            <input type="file" name="recipient_file" accept=".txt,.csv">
        </div>

        <div class="form-group">
            <label>Message Content:</label>
            <textarea name="message" placeholder="Write your message here..."></textarea>
        </div>

        <div class="link-group">
            <label style="color: #084298;">Add Hyperlink / Click Here Link (Optional):</label>
            <div class="form-group" style="margin-top: 8px;">
                <input type="url" name="link_url" placeholder="https://www.yourdomain.com">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="link_text" placeholder="Clickable Text (e.g., Click Here)">
            </div>
        </div>

        <button type="submit">Send Email</button>
    </form>
</div>

</body>
</html>