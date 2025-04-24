<?php 
include("template.php");

$mysqli = new mysqli("localhost", "isacli24", "FV0t2Wgb0b", "isacli24");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>

<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $required = ['username', 'password', 'confirm_password', 'email', 'first_name', 'last_name', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Error: $field is required");
        }
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Error: Passwords do not match");
    }

    $username = $mysqli->real_escape_string(trim($_POST['username']));
    $email = $mysqli->real_escape_string(trim($_POST['email']));
    $first_name = $mysqli->real_escape_string(trim($_POST['first_name']));
    $last_name = $mysqli->real_escape_string(trim($_POST['last_name']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Convert role name to user_role_ID
    $role_name = $mysqli->real_escape_string(trim($_POST['role']));
    $role_query = "SELECT user_role_ID FROM user_role WHERE role = ? LIMIT 1";
    $role_stmt = $mysqli->prepare($role_query);
    $role_stmt->bind_param("s", $role_name);
    $role_stmt->execute();
    $role_result = $role_stmt->get_result();

    if ($role_result->num_rows === 0) {
        die("Error: Invalid role selected");
    }

    $role_row = $role_result->fetch_assoc();
    $role_id = $role_row['user_role_ID'];

    $check_query = "SELECT username FROM user WHERE username = '$username' LIMIT 1";
    $check_result = $mysqli->query($check_query);
    if ($check_result && $check_result->num_rows > 0) {
        die("Error: Username already exists");
    }

    $query = "INSERT INTO user (username, password, email, first_name, last_name, user_role_ID)
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("sssssi", $username, $password, $email, $first_name, $last_name, $role_id);

    if ($stmt->execute()) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
        exit();
    } else {
        die("Error creating user: " . $stmt->error);
    }
}
?>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<div class="alert alert-success text-center">User added successfully!</div>
<?php endif; ?>

<div class="col">
    <div class="row mb-3 border">
        <button type="button" class="btn btn-primary mx-auto" data-bs-toggle="offcanvas" data-bs-target="#addNewUser" aria-controls="addNewUser">
            Add new user
        </button>

        <div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="addNewUser" aria-labelledby="addNewUserLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="addNewUserLabel">Add new user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                            <div class="invalid-feedback">Please provide a first name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                            <div class="invalid-feedback">Please provide a last name.</div>
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
        </div>
    </div>
</div>

<?php 
echo $footer;
?>


    <div class="row mb-3 border">
        <div class="row">
            <div class="col"><p>User ID: 1</p></div>
            <div class="col"><p>Incident Type: DDoS</p></div>
            <div class="col"><p>Severity: Critical</p></div>
        </div>
        <button type="button" class="btn btn-primary mx-auto" style="width: 150px" data-bs-toggle="offcanvas" data-bs-target="#newIncident" aria-controls="newIncident">
            Show more
        </button>

        <div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="newIncident" aria-labelledby="newIncidentLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="newIncidentLabel">User 1</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Placeholder -->
            </div>
        </div>
    </div>
</div>

<?php 
echo $footer;
?>
