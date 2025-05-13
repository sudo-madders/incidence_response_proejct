<?php 
ob_start();
require_once("template.php");

// checks if the user is logged in, if not, the rest of the page won't be loaded
if ($_SESSION["role"] != "administrator") {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You must be administrator to access this page.';
    exit;
}

// Handle all form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new user logic
    if (isset($_POST['submit'])) {
        $errors = [];
        $required = ['username', 'password', 'confirm_password', 'email', 'first_name', 'last_name', 'role'];

        // Check if any required fields are empty
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "$field is required";
            }
        }

        // Validate email format
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Validate password strength
        if (!empty($_POST['password']) && strlen($_POST['password']) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }

        // Confirm passwords match
        if (!empty($_POST['password']) && !empty($_POST['confirm_password']) && 
            $_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "Passwords do not match";
        }

        // If there are errors, display them
        if (!empty($errors)) {
            $error_message = "Error: " . implode(", ", $errors);
            die($error_message);
        }

        $username = $mysqli->real_escape_string(trim($_POST['username']));
        $email = $mysqli->real_escape_string(trim($_POST['email']));
        $first_name = $mysqli->real_escape_string(trim($_POST['first_name']));
        $last_name = $mysqli->real_escape_string(trim($_POST['last_name']));
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Get role ID
        $role_name = $mysqli->real_escape_string(trim($_POST['role']));
        $role_query = "SELECT user_role_ID FROM user_role WHERE role = ? LIMIT 1";
        $role_stmt = $mysqli->prepare($role_query);
        $role_stmt->bind_param("s", $role_name);
        $role_stmt->execute();
        $role_result = $role_stmt->get_result();

        // Check if the role exists
        if ($role_result->num_rows === 0) {
            die("Error: Invalid role selected");
        }

        $role_row = $role_result->fetch_assoc();
        $role_id = $role_row['user_role_ID'];

        // Check if username already exists
        $check_query = "SELECT username FROM user WHERE username = ? LIMIT 1";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result && $check_result->num_rows > 0) {
            die("Error: Username already exists");
        }

        // Check if email already exists
        $email_check_query = "SELECT email FROM user WHERE email = ? LIMIT 1";
        $email_check_stmt = $mysqli->prepare($email_check_query);
        $email_check_stmt->bind_param("s", $email);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();
        if ($email_check_result && $email_check_result->num_rows > 0) {
            die("Error: Email already exists");
        }

        // Insert new user
        $query = "INSERT INTO user (username, password, email, first_name, last_name, user_role_ID)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param("sssssi", $username, $password, $email, $first_name, $last_name, $role_id);

        if ($stmt->execute()) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=add');
            exit();
        } else {
            die("Error creating user: " . $stmt->error);
        }
    }
    // Edit user logic
    elseif (isset($_POST['edit_user'])) {
        
        $user_id = intval($_POST['user_id']);
        $first_name = $mysqli->real_escape_string(trim($_POST['first_name']));
        $last_name = $mysqli->real_escape_string(trim($_POST['last_name']));
        $email = $mysqli->real_escape_string(trim($_POST['email']));
        $username = $mysqli->real_escape_string(trim($_POST['username']));

        // Check email uniqueness
        $email_check = $mysqli->prepare("SELECT user_id FROM user WHERE email = ? AND user_id != ?");
        $email_check->bind_param("si", $email, $user_id);
        $email_check->execute();
        if ($email_check->get_result()->num_rows > 0) {
            die("Error: Email already exists");
        }

        // Check username uniqueness
        $username_check = $mysqli->prepare("SELECT user_id FROM user WHERE username = ? AND user_id != ?");
        $username_check->bind_param("si", $username, $user_id);
        $username_check->execute();
        if ($username_check->get_result()->num_rows > 0) {
            die("Error: Username already exists");
        }

        // Handle password change if requested
        $password_update = "";
        $params = [];
        if (!empty($_POST['change_password']) && !empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                die("Error: Passwords do not match");
            }
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_update = ", password = ?";
            $params[] = $new_password;
        }

        // Get role ID
        $role_id = 1; // Default to admin
        if (!empty($_POST['role'])) {
            $role_stmt = $mysqli->prepare("SELECT user_role_ID FROM user_role WHERE role = ?");
            $role_stmt->bind_param("s", $_POST['role']);
            $role_stmt->execute();
            $role_result = $role_stmt->get_result();
            if ($role_result->num_rows > 0) {
                $role_row = $role_result->fetch_assoc();
                $role_id = $role_row['user_role_ID'];
            }
        }

        // Update user
        $query = "UPDATE user SET 
                  username = ?,
                  email = ?,
                  first_name = ?,
                  last_name = ?,
                  user_role_ID = ?
                  $password_update
                  WHERE user_id = ?";
        
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $mysqli->error);
        }
        
        // Bind parameters based on whether password is being updated
        if (!empty($password_update)) {
            $stmt->bind_param("sssssii", $username, $email, $first_name, $last_name, $role_id, $new_password, $user_id);
        } else {
            $stmt->bind_param("ssssii", $username, $email, $first_name, $last_name, $role_id, $user_id);
        }
        
        if ($stmt->execute()) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=edit');
            exit();
        } else {
            die("Error updating user: " . $stmt->error);
        }
    }
}
?>

<!-- Success messages -->
<?php if (isset($_GET['success'])): ?>
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-mono text-white">
                    <h5 class="bg-primary-mono">Success!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <?php if ($_GET['success'] === 'add'): ?>
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                        <p>User added successfully!</p>
                    <?php elseif ($_GET['success'] === 'edit'): ?>
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                        <p>User updated successfully!</p>
                    <?php elseif ($_GET['success'] === 'delete'): ?>
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                        <p>User deleted successfully!</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-accent" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- showing the success message -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
    </script>
<?php endif; ?>

<div class="col">
    <div class="row mb-3 border">
        <!-- add new user button -->
        <button type="button" class="btn btn-lg btn-accent mx-auto" data-bs-toggle="offcanvas" data-bs-target="#addNewUser" aria-controls="addNewUser">
            Add new user
        </button>

        <div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="addNewUser" aria-labelledby="addNewUserLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="addNewUserLabel">Add new user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <!-- offcanvas body -->
            <div class="offcanvas-body">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="needs-validation">
                    
                    <div class="mb-3 text-muted small">
                        <span class="text-danger">*</span> Required fields
                    </div>
                        <label for="user_role" class="form-label">Select User Role</label>
                        <select class="form-select" id="user_role" name="user_role" required>
                            <option value="" selected disabled>Select role</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Responder">Responder</option>
                            <option value="Reporter">Reporter</option>
                        </select>
                        <div class="invalid-user-role">Please select a user role.</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                            <div class="invalid-first-name">Please provide a first name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                            <div class="invalid-last-name">Please provide a last name.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Select User Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="" selected disabled>Select role</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Responder">Responder</option>
                            <option value="Reporter">Reporter</option>
                        </select>
                        <div class="invalid-user-role">Please select a user role.</div>
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
                    <a href="user_management.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="row mb-3 border">
        <!-- Users Table -->
        <div class="mt-4">
            <h1>Users Table</h1>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all users from the database
                    $query = "SELECT u.user_id, u.username, u.email, u.first_name, u.last_name, r.role 
                              FROM user u 
                              JOIN user_role r ON u.user_role_ID = r.user_role_ID 
                              ORDER BY u.last_name, u.first_name";
                    $result = $mysqli->query($query);
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <button type="button" class="btn btn-accent mx-auto" 
                                    data-bs-toggle="offcanvas" 
                                    data-bs-target="#editUser_<?= $row['user_id'] ?>" 
                                    aria-controls="editUser_<?= $row['user_id'] ?>">
                                Edit
                            </button>
                            
                            <!-- Edit User Offcanvas -->
                            <div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" 
                                 id="editUser_<?= $row['user_id'] ?>" 
                                 aria-labelledby="editUserLabel_<?= $row['user_id'] ?>">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="editUserLabel_<?= $row['user_id'] ?>">
                                        Edit User: <?= htmlspecialchars($row['username']) ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <?php
                                    $user_query = $mysqli->prepare("SELECT * FROM user WHERE user_id = ?");
                                    $user_query->bind_param("i", $row['user_id']);
                                    $user_query->execute();
                                    $user = $user_query->get_result()->fetch_assoc();
                                    ?>
                                    
                                    <form method="post" class="needs-validation" novalidate>
                                        <input type="hidden" name="edit_user" value="1">
                                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control" 
                                                   value="<?= htmlspecialchars($user['username']) ?>" required pattern="[A-Za-z0-9_]+" minlength="4" maxlength="30">
                                            <div class="invalid-feedback">Username must be 4-30 characters (letters, numbers, underscores only).</div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" name="first_name" class="form-control" 
                                                       value="<?= htmlspecialchars($user['first_name']) ?>" required pattern="[A-Za-z\s]+" maxlength="50">
                                                <div class="invalid-feedback">Please provide a valid first name (letters only).</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" name="last_name" class="form-control" 
                                                       value="<?= htmlspecialchars($user['last_name']) ?>" required pattern="[A-Za-z\s]+" maxlength="50">
                                                <div class="invalid-feedback">Please provide a valid last name (letters only).</div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" 
                                                   value="<?= htmlspecialchars($user['email']) ?>" required maxlength="100">
                                            <div class="invalid-feedback">Please provide a valid email.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select class="form-select" name="role">
                                                <option value="Administrator" <?= $row['role'] == 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                                                <option value="Reporter" <?= $row['role'] == 'Reporter' ? 'selected' : '' ?>>Reporter</option>
                                                <option value="Responder" <?= $row['role'] == 'Responder' ? 'selected' : '' ?>>Responder</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="changePassword_<?= $row['user_id'] ?>" name="change_password">
                                            <label class="form-check-label" for="changePassword_<?= $row['user_id'] ?>">Change Password</label>
                                        </div>
                                        
                                        <div id="passwordFields_<?= $row['user_id'] ?>" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="new_password" class="form-control" minlength="8" maxlength="100">
                                                <div class="invalid-feedback">Password must be at least 8 characters.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Confirm Password</label>
                                                <input type="password" name="confirm_password" class="form-control" minlength="8" maxlength="100">
                                                <div class="invalid-feedback">Passwords must match.</div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                                    </form>
                                </div>
                            </div>
                            
                            <a href="delete_user.php?id=<?= $row['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


<script>
// Toggle password fields when checkbox is clicked
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="changePassword_"]').forEach(checkbox => {
        const userId = checkbox.id.split('_')[1];
        checkbox.addEventListener('change', function() {
            const passwordFields = document.getElementById('passwordFields_' + userId);
            if (passwordFields) {
                passwordFields.style.display = this.checked ? 'block' : 'none';
            }
        });
    });

    // Existing offcanvas handler
    document.addEventListener('click', function(event) {
        if (event.target.matches('[data-bs-toggle="offcanvas"]')) {
            const targetId = event.target.getAttribute('data-bs-target');
            const offcanvasElement = document.getElementById(targetId.startsWith('#') ? targetId.substring(1) : targetId);
            if (offcanvasElement) {
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement) || new bootstrap.Offcanvas(offcanvasElement);
                offcanvas.show();
            }
        }
    });

    // Form validation
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    // Custom password confirmation validation
                    const password = form.querySelector('[name="password"], [name="new_password"]');
                    const confirmPassword = form.querySelector('[name="confirm_password"]');
                    
                    if (password && confirmPassword && password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity("Passwords must match");
                        confirmPassword.classList.add('is-invalid');
                    } else if (confirmPassword) {
                        confirmPassword.setCustomValidity("");
                    }

                    form.classList.add('was-validated')
                }, false)
            })

        // Password confirmation live validation
        document.addEventListener('input', function(e) {
            if (e.target.name === 'password' || e.target.name === 'new_password' || e.target.name === 'confirm_password') {
                const form = e.target.closest('form');
                if (form) {
                    const password = form.querySelector('[name="password"], [name="new_password"]');
                    const confirmPassword = form.querySelector('[name="confirm_password"]');
                    
                    if (password && confirmPassword) {
                        if (password.value !== confirmPassword.value) {
                            confirmPassword.setCustomValidity("Passwords must match");
                            confirmPassword.classList.add('is-invalid');
                        } else {
                            confirmPassword.setCustomValidity("");
                            confirmPassword.classList.remove('is-invalid');
                        }
                    }
                }
            }
        });
    })()
>>>>>>> 9c72d3d9d8283ae47e2b3f8c5af15462c8ec8e92
});
</script>

<?php echo $footer; 
ob_end_flush();?>