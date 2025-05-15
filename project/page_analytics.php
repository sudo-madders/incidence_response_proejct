<?php 
require_once("template.php");

if ($_SESSION["role"] != "administrator") {
    header('Location:incident_dashboard.php');
    exit;
}
?>

<div class="col border m-auto bg-white">
	<div class="row">
		<table class=" table table-striped table-bordered table-hover" style="width:100%">
			<thead class="table-dark">
				<tr>
					<th>Page</th>
					<th>Visits</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$query = "SELECT * FROM page_views";
					$result = $mysqli->query($query);
					while ($row = $result->fetch_assoc()) {
						$visits = $row['Visits'];
						$page = $row['Page'];
						echo '<tr>';
						echo '<td>' . $page . '</td>';
						echo '<td>' . $visits . '</td>';
						echo '</tr>';
					}	
				?>
			</tbody>
		</table>
	</div>
	
	<div class="row border border-primary mb-3">
		<table class=" table table-striped table-bordered table-hover" style="width:100%">
			<thead class="table-dark">
				<tr>
					<th>IP</th>
					<th>Visits</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$query = "SELECT * FROM ip_visits";
					$result = $mysqli->query($query);
					while ($row = $result->fetch_assoc()) {
						$visits = $row['Visits'];
						$IP = $row['IP'];
						
						echo '<tr>';
						if ($IP == "83.251.155.142") {
							$user = "David";
							echo '<td>' . $IP . " - " . $user . '</td>';
						} elseif ($IP == "89.233.192.111") {
							$user = "Isac";
							echo '<td>' . $IP . " - " . $user . '</td>';
						} elseif ($IP == "84.218.126.196") {
							$user = "Mahdi";
							echo '<td>' . $IP . " - " . $user . '</td>';
						} else {
							echo '<td>' . $IP . '</td>';
						}
						
						
						echo '<td>' . $visits . '</td>';
						echo '</tr>';
					}	
				?>
			</tbody>
		</table>
	</div>
	<div class="row border border-primary">
		<table id="userTable" class="display table table-striped table-bordered table-hover" style="width:100%">
			<thead class="table-dark">
				<tr>
					<th>ID</th>
					<th>Page</th>
					<th>Browser</th>
					<th>IP</th>
					<th>Username</th>
					<th>Timestamp</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

<script>
$(document).ready(function() {
    $('#userTable').DataTable({
        ajax: {
            url: 'library/get_tracking_data.php',  
            dataSrc: 'data'       
        },
        columns: [
            { data: 'visit_ID'},
            { data: 'page'},
            { data: 'browser'},
            { data: 'ip'},
			{ data: 'username'},
            { data: 'timestamp'}
        ],
        scrollCollapse: true,
        scrollY: '400px',
		scrollX: '100%',
		order: [[0, 'desc']]
    });
});
</script>
<?php
echo $footer;
$mysqli->close();
?>
