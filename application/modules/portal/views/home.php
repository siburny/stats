{{> templates/header}}

{{#is_admin}}<p><a href="/portal/connect/">Check status of Google Analytics</a></p>{{/is_admin}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>

<h2>All views</h2>
<div id="chart"></div>
<p>&nbsp;</p>

<script>
var chart1 = c3.generate({
	bindto: '#chart',
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
</script>


{{> templates/footer}}
