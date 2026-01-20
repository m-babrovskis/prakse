<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="/assets/css/energyP.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
</head>
<body>
    <div class = "login-backgrounds">        

        <div class = "login-wood_board">
            <div class = "logIn-container">
                <input type="text" placeholder="ENTER USERNAME" id = "usernameInput" class="loginInput" autocomplete="off">
                
                <input type="password" placeholder="ENTER PASSWORD" id="passwordInput" class="loginInput" autocomplete="off">
                <div class="password-reveal"></div>
                
                <div id = "login-btn" class = "authButton"></div>
                
                <div class="auth-label" >Don't have an account?</div>
            </div>

            <div class="signUp-container">
                <input type="text" placeholder="ENTER USERNAME" id = "usernameInput" class="loginInput" autocomplete="off">

                <input type="text" placeholder="ENTER EMAIL" id = "emailInput" class="loginInput" autocomplete="off">

                <input type="password" placeholder="ENTER PASSWORD" id="passwordInput" class="loginInput" autocomplete="off">
                <div class="password-reveal"></div>
                
                <div id = "signup-btn" class = "authButton"></div>
                
                <div class="auth-label">Already have an account?</div>
            </div>

            <div id = "login-bottom_left" class="login-bottom"></div>
            <div id = "login-bottom_middle" class="login-bottom"></div>
            <div id = "login-bottom_right" class="login-bottom"></div>
        </div>
    </div>

    <div id="notification-container"></div>

    <script src="/assets/js/NotificationSystem.js"></script>
    <script src="/assets/js/login.js"></script>
</body>
</html>