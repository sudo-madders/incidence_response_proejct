<?php 
include("template.php");
?>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
		// Based on https://developers.google.com/chart/interactive/docs/quick_start
      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
	fetch('library/get_chart_data.php')
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(responseJson => { 
      const dataFromApi = responseJson.data;
      const dataTable = new google.visualization.DataTable();

      if (dataFromApi && dataFromApi.length > 0) {
        dataTable.addColumn('string', 'Incident Type');
        dataTable.addColumn('number', 'Count');

        dataFromApi.forEach(item => {
          dataTable.addRow([item.incident_type, parseInt(item.count)]);
        });
		
		var options = {
            title: 'Incident Types, %',
            backgroundColor: '#eceff1' // Light gray background for the whole chart
        }

        const chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(dataTable, options);
      } else {
        console.log('No data received from the API.');
      }
    })
    .catch(error => {
      console.error('Error fetching data:', error);
    });
}
    </script>

<div class="col m-auto">
	<div class="row my-3 align-items-center">
    <!--Div that will hold the pie chart-->
    <div id="chart_div" class="m-auto bg-secondary-mono" style="height: 600px; width: 600px;"></div>
</div>
<?php
echo $footer;
?>
