{{> templates/header}}

{{#is_admin}}
<p><a href="/portal/connect/">Google Analytics Account</a></p>
<p><a href="/portal/ga_code/">Get the Code</a></p>
{{/is_admin}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>
<script src="/js/jquery.ajaxMultiQueue.js"></script>

<h2>All views</h2>
<div id="chart" style="height:200px;border:1px solid #ccc;border-radius:5px;">
	<div style="color:#ccc;font-size:40px;line-height:100%;margin-top:80px;text-align:center;">Loading chart ...</div>
</div>
<p>&nbsp;</p>

<style>
	#posts {
		width: 100%;
		border-spacing: 0px;
		border-collapse: collapse;
	}
		#posts tr {
			border: 1px solid #333;
		}
		#posts td {
			padding: 5px;
		}
		#posts td:nth-child(1) {
			width: 20px;
		}
		#posts td:nth-child(2) {
			width: 50px;
			text-align: center;
		}
		#posts td:nth-child(2) img {
			max-width: 100px;
			max-height: 50px;
		}
		#posts td:nth-child(4) {
			width: 100px;
		}
		#posts td:nth-child(5) {
			width: 80px;
			text-align:right;
		}
		#posts td:nth-child(6) {
			width: 80px;
			text-align:right;
		}
</style>
<table style="" id="posts">
{{#rows}}
	<tr class="{{class}}" data-url="{{url}}">
		<td>{{n}}</td>
		<td class="image"><img src="{{image}}" alt=""/></td>
		<td>
			<div class="title" style="clear:both;font-weight:bold;font-size:125%;margin-bottom:5px;">{{title}}</div>
			<div style="font-size:90%;">
				<div class="date_published">{{date_published}}{{#author}} by {{.}}{{/author}}</div>
			</div>
		</td>
		<td>
			<div id="chart{{n}}" data-url="{{url}}" style="width:100px;height:50px;"></div>
		</td>
		<td>{{views}}</td>
		<td>
			{{#up_arrow}}<img src="/images/arrow_up_green.png" alt="UP" />{{/up_arrow}}
			{{#down_arrow}}<img src="/images/arrow_down_red.png" alt="UP" />{{/down_arrow}}
			<span style="color:{{#up_arrow}}green{{/up_arrow}}{{#down_arrow}}red{{/down_arrow}}">{{up_down_text}}</span>
		</td>
	</tr>
{{/rows}}
</table>

<script>
var chart = c3.generate({
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
	}
});

$(function () {
	/*var q = $.ajaxMultiQueue(3);
	$("#posts tr.loading").each(function (index, item) {
		var $item = $(item);
		q.queue({
			url: '/ajax/get_post_cache?url=' + encodeURIComponent($item.data("url")),
			dataType: 'json',
			success: function (data) {
				$item.find(".image img").attr('src', data.image);
				$item.find(".title").html(data.title);
				if (!!data.date_published) {
					$item.find(".date_published").html(data.date_published)
				}
			}
		});
	});*/

	for (var i = 1; i <= 10; i++) {
		$chart = $('#chart' + i);
		var chart = c3.generate({
			bindto: '#chart'+i,
			data: {
				x: 'x',
				type: 'bar',
				xFormat: '%Y%m%d',
				url: '/ajax/get_post_graph_data?url=' + encodeURIComponent($chart.data("url"))
			},
			axis: { x: { type: 'timeseries', show: false }, y: { show: false } },
			legend: { show: false },
			tooltip: { show: false },
			bar: { width: { ratio: 0.9 } }
		});
	}
});
</script>

{{> templates/footer}}
