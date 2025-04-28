<?php 
include("template.php");
include("library/database.php");
?>
<div class="col-md-8 border m-auto">
	<table id="userTable" class="display table table-striped table-bordered table-hover" style="width:100%">
		<thead>
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
            url: 'library/get_data.php',   //  The PHP script that returns the JSON
            dataSrc: 'data'       //  Tell DataTables where the data is
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
		scrollX: '100%'
    });
});
</script>
<?php
echo $footer;
$mysqli->close();
?>
