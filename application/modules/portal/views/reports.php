{{> templates/header}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>

<h2>All views</h2>
<div id="chart1"></div>
<p>&nbsp;</p>

{{#user_data}}
<h2>User Views</h2>
<div id="chart2"></div>
{{/user_data}}

<script>
	var chart1 = c3.generate({
		bindto: '#chart1',
		data: {
			x: 'x',
			type: 'bar',
			xFormat: '%Y%m%d',
			rows: {{{chart_data}}}
		},
		axis: {
			x: {
				type: 'timeseries'
			}
		}
	});

//{{#user_data}}
	var chart1 = c3.generate({
		bindto: '#chart1',
		data: {
			x: 'x',
			type: 'bar',
			xFormat: '%Y%m%d',
			rows: {{{user_data}}}
		},
		axis: {
			x: {
				type: 'timeseries'
			}
		}
	});
//{{/user_data}}

</script>
			
{{> templates/footer}}
