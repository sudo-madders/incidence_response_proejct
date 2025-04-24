<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session and check admin privileges
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrator') {
    header('Location: index.php');
    exit();
}

include('template.php');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Validate required fields
    $required = ['username', 'password', 'confirm_password', 'email', 'fname', 'lname', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Error: $field is required");
        }
    }

    // Validate passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Error: Passwords do not match");
    }

    // Sanitize and validate inputs
    $username = $mysqli->real_escape_string(trim($_POST['username']));
    $email = $mysqli->real_escape_string(trim($_POST['email']));
    $fname = $mysqli->real_escape_string(trim($_POST['fname']));
    $lname = $mysqli->real_escape_string(trim($_POST['lname']));
    $role = $mysqli->real_escape_string($_POST['role']);
    
    // Hash password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $check_query = "SELECT username FROM users WHERE username = '$username' LIMIT 1";
    $check_result = $mysqli->query($check_query);
    if ($check_result->num_rows > 0) {
        die("Error: Username already exists");
    }

    // Insert new user using prepared statement (better security)
    $query = "INSERT INTO users (username, password, email, fname, lname, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $username, $password, $email, $fname, $lname, $role);
    
    if ($stmt->execute()) {
        header('Location: view_user.php?success=1');
        exit();
    } else {
        die("Error creating user: " . $mysqli->error);
    }
}

$content = <<<END
<div class="container mt-4">
    <h2>Add New User</h2>
    <form method="post" action="add_user.php" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fname" class="form-label">First Name</label>
                <input type="text" id="fname" name="fname" class="form-control" required>
                <div class="invalid-feedback">Please provide a first name.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="lname" class="form-label">Last Name</label>
                <input type="text" id="lname" name="lname" class="form-control" required>
                <div class="invalid-feedback">Please provide a last name.</div>
            </div>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">User Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="" selected disabled>Select role</option>
                <option value="Administrator">Administrator</option>
                <option value="Responder">Responder</option>
                <option value="Reporter">Reporter</option>
            </select>
            <div class="invalid-feedback">Please select a user role.</div>
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
            <div class="invalid-feedback">Please choose a username.</div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
            <div class="invalid-feedback">Please provide a valid email.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <div class="invalid-feedback">Password must be at least 8 characters.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <div class="invalid-feedback">Passwords must match.</div>
            </div>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Add User</button>
        <a href="view_user.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
END;

$style = <<<END
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .form-control:invalid, .form-select:invalid {
        border-color: #dc3545;
    }
</style>
END;

$scripts = <<<END
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        
        // Password confirmation validation
        var password = document.getElementById('password');
        var confirm_password = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirm_password.value) {
                confirm_password.setCustomValidity("Passwords don't match");
            } else {
                confirm_password.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
        
        // Bootstrap validation
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
END;

echo $navigation;
echo $content;
echo $style;
echo $scripts;
include('footer.php');
?>