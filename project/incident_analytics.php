<?php 
include("template.php");
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {
  fetch('library/get_chart_data.php')
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(responseJson => { // The API returns an object with a "data" property
      const dataFromApi = responseJson.data;
      const dataTable = new google.visualization.DataTable();

      if (dataFromApi && dataFromApi.length > 0) {
        dataTable.addColumn('string', 'Incident Type');
        dataTable.addColumn('number', 'Count');

        dataFromApi.forEach(item => {
          dataTable.addRow([item.incident_type, parseInt(item.count)]); // Parse count as integer
        });

        const chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(dataTable, { title: 'Incident Types' });
      } else {
        console.log('No data received from the API.');
      }
    })
    .catch(error => {
      console.error('Error fetching data:', error);
    });
}
    </script>

<div class="col-md-8 border m-auto">
	<div class="row border border-primary my-3">
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
</div>
</div>
<?php
echo $footer;
?>
