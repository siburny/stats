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
		#action #date_selector {
			width: 200px;
		}
</style>

<div id="action">
	<div style="float:left">
		<select name="date_selector" id="date_selector">
			<option value="today">Today</option>
			<option value="yesterday">Yesterday</option>
			<option value="7days">Last 7 days</option>
			<option value="30days" selected="selected">Last 30 days</option>
			<option value="custom">Custom</option>
		</select>
	</div>

	<div name="date_custom" id="date_custom" style="float:left;display:none;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_from" id="date_from" /> to <input name="date_to" id="date_to" />
		<button id="go">GO</button>
	</div>

	<div style="clear:both"></div>
</div>

<script>
	$("#date_custom #go").on('click', function () {
		if ($("#date_from").val() && $("#date_to").val()) {

			window.location = '/portal/page1/' + encodeURIComponent($("#date_from").val()) + '/' + encodeURIComponent($("#date_to").val()) + '/';
		}
	});

	$item = $("#action #date_selector").find("*[value='{{date_selected}}']");
	if ($item) {
		$item.attr('selected', 'selected');
	}

	$("#action #date_selector").selectmenu({
		change: function (event, ui) {
			switch (ui.item.value) {
				case "today":
					window.location = '/portal/page1/today/';
					break;
				case "yesterday":
					window.location = '/portal/page1/yesterday/';
					break;
				case "7days":
					window.location = '/portal/page1/7days/';
					break;
				case "30days":
					window.location = '/portal/page1/30days/';
					break;
				case "custom":
					$("#date_custom").show();
					$("#date_from").datepicker({
						dateFormat: "mm-dd-yy",
						numberOfMonths: 2,
						onClose: function (date) {
							if (date) {
								$("#date_to").focus();
							}
						}
					});
					$("#date_to").datepicker({
						dateFormat: "mm-dd-yy",
						numberOfMonths: 2
					});
					break;
			}
		}
	});
</script>

<br />

Showing {{date_from}} to {{date_to}}
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
		<td>{{sessions}}</td>
		<td>
			{{#up_arrow}}<img src="/images/arrow_up_green.png" alt="UP" />{{/up_arrow}}
			{{#down_arrow}}<img src="/images/arrow_down_red.png" alt="UP" />{{/down_arrow}}
			<span style="color:{{#up_arrow}}green{{/up_arrow}}{{#down_arrow}}red{{/down_arrow}}">{{up_down_text}}</span>
		</td>
	</tr>
{{/rows}}
</table>

<script>

$(function () {
	var chart = c3.generate({
		bindto: '#chart',
		data: { x: 'x', type: 'bar', url: '/ajax/get_graph_data' },
		axis: { x: { type: 'timeseries' } },
		legend: { show: false },
		transition: { duration: 1000 },
		tooltip: { show: false }
	});

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
