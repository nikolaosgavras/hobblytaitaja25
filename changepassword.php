<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();

$success_message = '';
$error_message = '';

if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] == 'staff') {
    header('Location: admin.php');
    exit;
}

// We can utilize the isset() function to check if the form has been submitted
if (isset($_POST['oldpassword'], $_POST['newpassword'], $_POST['confirmpassword'])) {
    // Make sure the submitted values are not empty
    if (empty($_POST['oldpassword']) || empty($_POST['newpassword']) || empty($_POST['confirmpassword'])) {
        $error_message = 'Please complete all fields in the password change form.';
    } 
    // Check if new password matches confirmation
    elseif ($_POST['newpassword'] !== $_POST['confirmpassword']) {
        $error_message = 'New password and confirmation do not match.';
    }
    // Check minimum password length
    elseif (strlen($_POST['newpassword']) < 8) {
        $error_message = 'New password must be at least 8 characters long.';
    }
    // Check if new password is same as old password
    elseif ($_POST['oldpassword'] === $_POST['newpassword']) {
        $error_message = 'New password must be different from your old password.';
    }
    else {
        // Get the current user's information from session
        $id = $_SESSION['account_id'];

        if ($stmt = $link->prepare('SELECT paneladmin_id, password_hash FROM admins WHERE paneladmin_id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc)
            $stmt->bind_param('i', $id);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database
            $stmt->store_result();
            
            // Check if the account exists
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $stored_password_hash);
                $stmt->fetch();
                
                // Verify the old password
                if (password_verify($_POST['oldpassword'], $stored_password_hash)) {
                    // Old password is correct, update with new password
                    $new_password_hash = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);
                    
                    if ($update_stmt = $link->prepare('UPDATE admins SET password_hash = ? WHERE paneladmin_id = ?')) {
                        $update_stmt->bind_param('si', $new_password_hash, $id);
                        $update_stmt->execute();
                        $success_message = 'Password changed successfully! Redirecting...';
                        $update_stmt->close();
                        // Redirect to profile settings after 2 seconds
                        header("refresh:2;url=profilesettings.php");
                    } else {
                        $error_message = 'Could not update password. Please try again.';
                    }
                } else {
                    $error_message = 'Current password is incorrect. Please try again.';
                }
            } else {
                $error_message = 'Account not found. Please contact support.';
            }
            // Close the statement
            $stmt->close();
        } else {
            // Something is wrong with the SQL statement
            $error_message = 'An error occurred. Please try again later.';
        }
    }
}

mysqli_close($link);

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Change Password - Hobbly Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="password-change-container">
            <div class="password-change-card">
                <div class="password-change-header">
                    <i class="bi bi-shield-lock"></i>
                    <h1>Change Your Password</h1>
                    <p>Keep your account secure with a strong password</p>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="password-requirements">
                    <h6><i class="bi bi-info-circle me-1"></i> Password Requirements</h6>
                    <ul id="requirements-list">
                        <li id="req-length">At least 8 characters long</li>
                    </ul>
                </div>

                <form method="post" id="passwordChangeForm" novalidate>
                    <div class="mb-3">
                        <label for="oldpassword" class="form-label">
                            <i class="bi bi-lock me-1"></i> Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="oldpassword" id="oldpassword" 
                                   placeholder="Enter your current password" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('oldpassword')" tabindex="-1">
                                <i class="bi bi-eye" id="oldpassword-icon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="oldpassword-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="newpassword" class="form-label">
                            <i class="bi bi-key me-1"></i> New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="newpassword" id="newpassword" 
                                   placeholder="Enter your new password" required minlength="8" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newpassword')" tabindex="-1">
                                <i class="bi bi-eye" id="newpassword-icon"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-strength-text" id="strengthText"></div>
                        <div class="invalid-feedback" id="newpassword-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirmpassword" class="form-label">
                            <i class="bi bi-check-circle me-1"></i> Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" 
                                   placeholder="Re-enter your new password" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmpassword')" tabindex="-1">
                                <i class="bi bi-eye" id="confirmpassword-icon"></i>
                            </button>
                        </div>
                        <div id="confirmPasswordFeedback" class="mt-2"></div>
                        <div class="invalid-feedback" id="confirmpassword-error"></div>
                    </div>

                    <div class="form-actions">
                        <a href="profilesettings.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function togglePassword(inputId) {
                const passwordInput = document.getElementById(inputId);
                const icon = document.getElementById(inputId + '-icon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }

            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
                };

                // Update requirements list
                document.getElementById('req-length').classList.toggle('met', requirements.length);
                document.getElementById('req-uppercase').classList.toggle('met', requirements.uppercase);
                document.getElementById('req-lowercase').classList.toggle('met', requirements.lowercase);
                document.getElementById('req-number').classList.toggle('met', requirements.number);

                // Calculate strength
                if (requirements.length) strength++;
                if (requirements.uppercase) strength++;
                if (requirements.lowercase) strength++;
                if (requirements.number) strength++;
                if (requirements.special) strength++;

                const strengthBar = document.getElementById('strengthBar');
                const strengthText = document.getElementById('strengthText');

                strengthBar.className = 'password-strength-bar';
                strengthText.className = 'password-strength-text';

                if (password.length === 0) {
                    strengthText.textContent = '';
                } else if (strength <= 2) {
                    strengthBar.classList.add('weak');
                    strengthText.classList.add('weak');
                    strengthText.textContent = 'Weak password';
                } else if (strength <= 3) {
                    strengthBar.classList.add('medium');
                    strengthText.classList.add('medium');
                    strengthText.textContent = 'Medium password';
                } else {
                    strengthBar.classList.add('strong');
                    strengthText.classList.add('strong');
                    strengthText.textContent = 'Strong password';
                }

                return strength >= 3;
            }

            // Password confirmation checker
            function checkPasswordMatch() {
                const newPassword = document.getElementById('newpassword').value;
                const confirmPassword = document.getElementById('confirmpassword').value;
                const feedback = document.getElementById('confirmPasswordFeedback');

                if (confirmPassword.length === 0) {
                    feedback.innerHTML = '';
                    return false;
                }

                if (newPassword === confirmPassword) {
                    feedback.innerHTML = '<small class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</small>';
                    return true;
                } else {
                    feedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match</small>';
                    return false;
                }
            }

            // Event listeners
            document.getElementById('newpassword').addEventListener('input', function() {
                checkPasswordStrength(this.value);
                if (document.getElementById('confirmpassword').value.length > 0) {
                    checkPasswordMatch();
                }
            });

            document.getElementById('confirmpassword').addEventListener('input', checkPasswordMatch);

            // Form validation before submit
            document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Always prevent default first
                
                const newPassword = document.getElementById('newpassword').value;
                const confirmPassword = document.getElementById('confirmpassword').value;
                const oldPassword = document.getElementById('oldpassword').value;
                
                // Clear previous error messages
                document.getElementById('oldpassword-error').style.display = 'none';
                document.getElementById('newpassword-error').style.display = 'none';
                document.getElementById('confirmpassword-error').style.display = 'none';
                
                let hasError = false;
                
                // Validate current password
                if (!oldPassword || oldPassword.trim() === '') {
                    document.getElementById('oldpassword').classList.add('is-invalid');
                    document.getElementById('oldpassword-error').textContent = 'Please enter your current password.';
                    document.getElementById('oldpassword-error').style.display = 'block';
                    hasError = true;
                } else {
                    document.getElementById('oldpassword').classList.remove('is-invalid');
                }
                
                // Validate new password
                if (!newPassword || newPassword.trim() === '') {
                    document.getElementById('newpassword').classList.add('is-invalid');
                    document.getElementById('newpassword-error').textContent = 'Please enter a new password.';
                    document.getElementById('newpassword-error').style.display = 'block';
                    hasError = true;
                } else if (newPassword.length < 8) {
                    document.getElementById('newpassword').classList.add('is-invalid');
                    document.getElementById('newpassword-error').textContent = 'New password must be at least 8 characters long.';
                    document.getElementById('newpassword-error').style.display = 'block';
                    hasError = true;
                } else if (oldPassword === newPassword) {
                    document.getElementById('newpassword').classList.add('is-invalid');
                    document.getElementById('newpassword-error').textContent = 'New password must be different from your current password.';
                    document.getElementById('newpassword-error').style.display = 'block';
                    hasError = true;
                } else {
                    document.getElementById('newpassword').classList.remove('is-invalid');
                }
                
                // Validate confirm password
                if (!confirmPassword || confirmPassword.trim() === '') {
                    document.getElementById('confirmpassword').classList.add('is-invalid');
                    document.getElementById('confirmpassword-error').textContent = 'Please confirm your new password.';
                    document.getElementById('confirmpassword-error').style.display = 'block';
                    hasError = true;
                } else if (newPassword !== confirmPassword) {
                    document.getElementById('confirmpassword').classList.add('is-invalid');
                    document.getElementById('confirmpassword-error').textContent = 'Passwords do not match.';
                    document.getElementById('confirmpassword-error').style.display = 'block';
                    hasError = true;
                } else {
                    document.getElementById('confirmpassword').classList.remove('is-invalid');
                }
                
                // If there are errors, don't submit
                if (hasError) {
                    return false;
                }

                // Disable submit button to prevent double submission
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Changing...';
                
                // Submit the form
                this.submit();
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
