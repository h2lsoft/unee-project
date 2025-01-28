$(function() {

	$('#analytics_widget_chart').data('json');

	google.charts.load('current', {'packages':['corechart', 'bar']});
	google.charts.setOnLoadCallback(function(){


		// chart visitors **************************************************************************************************
		var data = new google.visualization.DataTable();
		data.addColumn('string', '');
		data.addColumn('number', 'Total visitors');

		data_visitors_array = $('#total_visitors_chart').data('json');
		data.addRows(data_visitors_array);

		const c_options = {
			width:'100%',
			// curveType: 'function',
			legend: { position: 'none' }
		};

		var chart = new google.visualization.LineChart(document.getElementById('total_visitors_chart'));
		chart.draw(data, c_options);

	});



});
