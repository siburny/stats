{{> templates/header}}

{{#is_admin}}
<p><a href="/portal/connect/">Google Analytics Account</a></p>
<p><a href="/portal/ga_code/">Get the Code</a></p>
{{/is_admin}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>

<h2>All views</h2>
<div id="chart" style="height:200px;border:1px solid #ccc;border-radius:5px;">
	<div style="color:#ccc;font-size:40px;line-height:100%;margin-top:80px;text-align:center;">Loading chart ...</div>
</div>
<p>&nbsp;</p>

<style>
	#posts {
		width:100%;
		border-spacing:0px;
		border-collapse:collapse;
	}
	#posts td {
		border:1px solid;
		padding:5px;
	}
	#posts td:nth-child(1) {
		width:20px;
	}
	#posts td:nth-child(2) {
		width:50px;
	}
	#posts td:nth-child(4) {
		width:50px;
	}
	#posts td:nth-child(5) {
		width:50px;
	}
	#posts td:nth-child(6) {
		width:50px;
	}
</style>
<table style="" id="posts">
{{#rows}}
	<tr>
		<td>{{n}}</td>
		<td><img src="{{picture}}" alt=""/></td>
		<td>{{title}}</td>
		<td>graph</td>
		<td>{{views}}</td>
		<td>{{up_down}}</td>
	</tr>
{{/rows}}
</table>

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
	transition: {
		duration: 1000
	},
	tooltip: {
		show: false
}});
</script>

{{> templates/footer}}
