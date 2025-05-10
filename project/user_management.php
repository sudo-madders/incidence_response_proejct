<?php 
require_once("template.php");

if ($_SESSION["role"] != "administrator") {
	header('HTTP/1.0 401 Unauthorized');
    echo 'You must be administrator in to access this page.';
    exit;
}

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
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=add');
        exit();
    } else {
        die("Error creating user: " . $stmt->error);
    }
}
?>




<div class="col">
<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] === 'add'): ?>
        <div class="alert alert-success text-center">User added successfully!</div>
    <?php elseif ($_GET['success'] === 'edit'): ?>
        <div class="alert alert-success text-center">User updated successfully!</div>
    <?php elseif ($_GET['success'] === 'delete'): ?>
        <div class="alert alert-success text-center">User deleted successfully!</div>
    <?php endif; ?>
<?php endif; ?>
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
                            <label for="first_name" class="form-label" >First Name</label>
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

    <div class="row mb-3 border">
        <!-- Users Table -->
		<div class="mt-4">
			<h1>users' table</h1>
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
							<button type="button" class="btn btn-primary mx-auto" data-bs-toggle="offcanvas" data-bs-target="user_<?= htmlspecialchars($row['user_id']) ?>" aria-controls="user_<?= htmlspecialchars($row['user_id']) ?>">
								Edit
							</button>
						
							<!-- Offcanvas, More selection -->
							<div class="offcanvas offcanvas-end offcanvas-md offcanvas_width" tabindex="-1" id="user_<?= htmlspecialchars($row['user_id']) ?>" aria-labelledby="addNewIncidentLabel">
								<div class="offcanvas-header">
									<h5 class="offcanvas-title" id="addNewIncidentLabel">user_<?= htmlspecialchars($row['user_id']) ?></h5>
									<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
								</div>
								<div class="offcanvas-body ">
                                <?php
				$user_id = $row['user_id']; // Directly use current user ID
				if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id']) && $_POST['edit_user_id'] == $user_id) {
				    $new_role = $_POST['role'];
				    $stmt = $mysqli->prepare("SELECT user_role_ID FROM user_role WHERE role = ? LIMIT 1");
				    $stmt->bind_param("s", $new_role);
				    $stmt->execute();
				    $res = $stmt->get_result();
				    $role = $res->fetch_assoc();
				    $role_id = $role['user_role_ID'];
				
				    $update = $mysqli->prepare("UPDATE user SET user_role_ID = ? WHERE user_id = ?");
				    $update->bind_param("ii", $role_id, $user_id);
				    $update->execute();
				
				    header("Location: user_management.php?success=edit");
				    exit();
				}
				
				$user = $mysqli->query("SELECT * FROM user WHERE user_id = $user_id")->fetch_assoc();
				?>


                                $user = $mysqli->query("SELECT * FROM user WHERE user_id = $id")->fetch_assoc();
                                ?>

                                <h3>Edit Role for <?= htmlspecialchars($user['username']) ?></h3>
                                <form method="post">
                                    <label>New Role:</label>
                                    <select name="role" class="form-select" required>
                                        <option value="Administrator">Administrator</option>
                                        <option value="Responder">Responder</option>
                                        <option value="Reporter">Reporter</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-2">Update Role</button>
                                </form>
									<!-- Här börjar själva panelen -->
									
								</div>
							</div>
						<!--	<a href="edit_user.php?id=<?= $row['user_id'] ?>" class="btn btn-sm btn-primary">Edit</a>   -->
							<a href="delete_user.php?id=<?= $row['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
						</td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
    </div>
</div>
</div>
<script>
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
</script>

<?php 
echo $footer;
?>
