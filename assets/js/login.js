$(document).ready(function() {
    function clearInputs() {
        $('.loginInput').val('');
    }

    $('#login-btn').click(function() {
        const username = $('.logIn-container #usernameInput').val();
        const password = $('.logIn-container #passwordInput').val();
        
        if (username === "" && password === "") {
            NotificationSystem.show("Please enter username and password.", "error");
        } else if (username.length > 0 && password.length < 1) {
            NotificationSystem.show("Please enter your password.", "warning");
        } else if (password.length > 0 && username.length < 1) {
            NotificationSystem.show("Please enter your username.", "warning");
        } else {
            authenticateUser(username, password).then(response => {
                if (response.success) {
                    clearInputs();
                    window.location.href = "/";
                } else {
                    NotificationSystem.show(response.error || "Incorrect username or password.", "error");
                }
            });
        }
    });

    $(document).keydown(function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            if ($('.signUp-container').is(':visible')) {
                $('#signup-btn').click();
            } else {
                $('#login-btn').click();
            }
        }
    });

    $('.password-reveal').mousedown(function() {
        const container = $(this).closest('.logIn-container, .signUp-container');
        const $passwordInput = container.find('#passwordInput');
        const isPassword = $passwordInput.attr('type') === 'password';
        
        $passwordInput.attr('type', isPassword ? 'text' : 'password');
        const imagePath = isPassword ? 'assets/textures/login/eye-open.png' : 'assets/textures/login/eye-closed.png';
        $(this).css('background-image', 'url(' + imagePath + ')');
    });

    // Toggle between login and signup forms
    $('.auth-label').click(function() {
        clearInputs();
        $('.logIn-container, .signUp-container').toggle();
    });

    $('#signup-btn').click(function() {
        const username = $('.signUp-container #usernameInput').val();
        const email = $('.signUp-container #emailInput').val();
        const password = $('.signUp-container #passwordInput').val();

        if (username === "" || email === "" || password === "") {
            NotificationSystem.show("Please fill in all fields to sign up.", "error");
        }
        else if (username.length < 5) {
            NotificationSystem.show("Username must be at least 5 characters long.", "warning");
        }
        else if (password.length < 6) {
            NotificationSystem.show("Password must be at least 6 characters long.", "warning");
        }
        else if (!email.includes("@") || !email.includes(".") || email.indexOf("@") === 0 || email.indexOf("@") === email.length - 1 || email.lastIndexOf(".") === email.length - 1 || email.indexOf("@") >= email.lastIndexOf(".")) {
            NotificationSystem.show("Please enter a valid email address.", "warning");
        }
        else {
            lookForDuplicates(username, email).then(response => {
                if (response.exists) {
                    NotificationSystem.show(response.field === 'username' ? 'Username already exists.' : 'Email already exists.', "error");
                } else {
                    registerUser(username, email, password).then(regResponse => {
                        console.log('Registration response:', regResponse);
                        if (regResponse.success) {
                            clearInputs();
                            console.log('Registration successful, attempting auto-login...');
                            // Auto-login after successful registration
                            authenticateUser(username, password).then(loginResponse => {
                                console.log('Auto-login response:', loginResponse);
                                if (loginResponse.success) {
                                    console.log('Auto-login successful, redirecting...');
                                    window.location.href = "/";
                                } else {
                                    console.log('Auto-login failed:', loginResponse.error);
                                    NotificationSystem.show("Sign up successful! Please log in manually.", "info");
                                    $('.logIn-container, .signUp-container').toggle();
                                }
                            }).catch(error => {
                                console.error('Auto-login error:', error);
                                NotificationSystem.show("Sign up successful! Please log in manually.", "info");
                                $('.logIn-container, .signUp-container').toggle();
                            });
                        } else {
                            NotificationSystem.show('Registration failed: ' + (regResponse.error || 'Unknown error'), "error");
                        }
                    });
                }
            });
        }
    });

    function authenticateUser(username, password) {
        return $.post('/theme/view/auth/authenticate.php', 
            JSON.stringify({username, password}), 
            null, 'json'
        ).fail(function() {
            NotificationSystem.show('Error during login. Please try again.', "error");
        });
    }

    function registerUser(username, email, password) {
        return $.post('/theme/view/auth/register.php', 
            JSON.stringify({username, email, password}), 
            null, 'json'
        ).fail(function() {
            NotificationSystem.show('Error during registration. Please try again.', "error");
        });
    }

    function lookForDuplicates(username, email) {
        return $.post('/theme/view/auth/checkDuplicates.php', 
            JSON.stringify({username, email}), 
            null, 'json'
        ).fail(function() {
            NotificationSystem.show('Error checking for duplicates. Please try again.', "error");
        });
    }

});
