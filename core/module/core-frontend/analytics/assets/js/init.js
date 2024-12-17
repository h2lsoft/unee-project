google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(function(){

	// chart visitors **************************************************************************************************
	var data = new google.visualization.DataTable();
	data.addColumn('string', '');
	data.addColumn('number', 'Total visitors');
	data.addColumn('number', 'Total visits');
	// data.addColumn('number', 'Average');
	data_visitors_array = $('#total_visitors_chart').data('json');
	data.addRows(data_visitors_array);

	const c_options = {};

	var chart = new google.charts.Bar(document.getElementById('total_visitors_chart'));
	chart.draw(data, google.charts.Bar.convertOptions(c_options));

	// chart website ***************************************************************************************************
	data = new google.visualization.DataTable();
	data.addColumn('string', 'Website');
	data.addColumn('number', 'Visitors');

	data_websites_array = $('#total_websites_chart').data('json');
	data.addRows(data_websites_array);

	const options2 = {
		legend: {
			position: 'bottom',
		},
		chartArea: {
			top: '40',
		},
	};
	const chart2 = new google.visualization.PieChart(document.getElementById('total_websites_chart'));
	chart2.draw(data, google.charts.Bar.convertOptions(options2));

	// chart top pages *************************************************************************************************
	data = new google.visualization.DataTable();
	data.addColumn('string', 'Url');
	data.addColumn('number', 'Total');

	data_top_pages_array = $('#top_pages_chart').data('json');
	data.addRows(data_top_pages_array);

	const options3 = {
		legend: 'none',
		chartArea: {
			left: '350',
			top: '20',
		},
		vAxis: {
			textStyle: {
				fontSize: 11
			}
		}
	};

	const chart3 = new google.visualization.BarChart(document.getElementById('top_pages_chart'));
	chart3.draw(data, google.charts.Bar.convertOptions(options3));

	// chart devices ***************************************************************************************************
	data = new google.visualization.DataTable();
	data.addColumn('string', 'Device');
	data.addColumn('number', 'Visitors');

	data_devices_array = $('#total_devices_chart').data('json');
	data.addRows(data_devices_array);

	const options4 = {
		legend: {
			position: 'bottom',
		},
	};
	const chart4 = new google.visualization.PieChart(document.getElementById('total_devices_chart'));
	chart4.draw(data,  google.charts.Bar.convertOptions(options4));


	// chart top referers *************************************************************************************************
	data = new google.visualization.DataTable();
	data.addColumn('string', 'Referer');
	data.addColumn('number', 'Total');

	data_top_referers_array = $('#top_referers_chart').data('json');
	data.addRows(data_top_referers_array);

	const options5 = {

		chartArea: {
			left: '350',
			top: '20',
		},

		legend: {
			textStyle: {
				fontSize: 12
			}
		}

	};

	const chart5 = new google.visualization.PieChart(document.getElementById('top_referers_chart'));
	chart5.draw(data,  google.charts.Bar.convertOptions(options5));

});

$('form[name="form_analytics"]').on('submit', function(e) {
	e.preventDefault();
	uri = '?'+$(this).serialize();

	if(swup)
	{
		swup.navigate(uri);
	}
	else
	{
		http_redirect(uri);
	}


});

$('#period').on('change', function() {

	v = $(this).val();
	if(v === '')return;

	vs = v.split('@');

	$("input[name='date_start']").val(vs[0]);
	$("input[name='date_end']").val(vs[1]);

	$('form[name="form_analytics"]').submit();

});