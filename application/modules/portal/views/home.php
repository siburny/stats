{{> templates/header}}

{{#is_admin}}<p><a href="/portal/connect/">Check status of Google Analytics</a></p>{{/is_admin}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>

<h2>All views</h2>
<div id="chart" style="height:200px;border:1px solid #ccc;border-radius:5px;">
	<div style="color:#ccc;font-size:40px;line-height:100%;margin-top:80px;text-align:center;">Loading chart ...</div>
</div>
<p>&nbsp;</p>

<script>
var chart1 = c3.generate({
	bindto: '#chart',
	data: {
		x: 'x',
		type: 'bar',
		xFormat: '%Y%m%d',
		url: '/ajax/get_graph_data'
	},
	axis: {
		x: {
			type: 'timeseries'
		}
	},
	legend: {
		show: false
	},
	tooltip: {
		show: false
}});
</script>

{{> templates/footer}}
