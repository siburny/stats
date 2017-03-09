{{> templates/header}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>
<script src="/js/date.format.min.js"></script>
<script src="/js/jquery.ajaxMultiQueue.js"></script>

<h2>All views</h2>
<div style="float:right;width:300px;">
	<div style="padding:10px 10px 10px 30px;font-size:1.5em;line-height:200%;">
		{{#totals}}
		Sessions: {{sessions}}<br />
		Pageviews: {{pageviews}}<br />
		{{/totals}}
	</div>
</div>
<div id="chart" style="margin-right:300px;height:200px;border:1px solid #ccc;border-radius:5px;">
	<div style="color:#ccc;font-size:40px;line-height:100%;margin-top:80px;text-align:center;">Loading chart ...</div>
</div>
<div style="clear:both;"></div>
<p>&nbsp;</p>

<style>
	#post {
		width: 100%;
		border-spacing: 0px;
		border-collapse: collapse;
	}
		#post td,#post th {
			border: 1px solid #333;
			padding: 5px;
		}
		#post td:nth-child(2), #post td:nth-child(3) {
			width: 50px;
			text-align: right;
		}
		#action #date_selector {
			width: 200px;
		}
</style>

<h2><img src="{{post_thumb}}" style="float:left;height:90px;margin-right:10px;" />Showing stats for &quot;{{post_title}}&quot;</h2>
<p>Published by <a href="/portal/?author_name={{post_author}}{{#uri_date}}&{{.}}{{/uri_date}}"><b>{{post_author}}</b></a> on <b>{{post_date}}</b></p>
<p>{{post_url}}</p>

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
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_from" id="date_from" value="{{date_from_input}}" /> to <input name="date_to" id="date_to" value="{{date_to_input}}" />
		<button id="go">GO</button>
	</div>

	<div style="clear:both"></div>
</div>

<script>
	$("#date_custom #go").on('click', function () {
		if ($("#date_from").val() && $("#date_to").val()) {
			window.location = '/portal/post/?post_id={{post_id}}&date_from=' + encodeURIComponent($("#date_from").val()) + '&date_to=' + encodeURIComponent($("#date_to").val());
		}
	});

	$item = $("#action #date_selector").find("*[value='{{date_selected}}']");
	if ($item) {
		$item.attr('selected', 'selected');
	}

	$("#action #date_selector").selectmenu({
		change: function (event, ui) {
			$(this).trigger('change');
		}
	});

	$("#action #date_selector").on("change", function (event, param) {
		switch ($(this).val()) {
			case "today":
				window.location = '/portal/post/?post_id={{post_id}}&date_from=today';
				break;
			case "yesterday":
				window.location = '/portal/post/?post_id={{post_id}}&date_from=yesterday';
				break;
			case "7days":
				window.location = '/portal/post/?post_id={{post_id}}&date_from=7days';
				break;
			case "30days":
				window.location = '/portal/post/?post_id={{post_id}}&date_from=30days';
				break;
			case "custom":
				$("#date_custom").show();
				$("#date_from").datepicker({
					dateFormat: "mm-dd-yy",
					numberOfMonths: 2,
					onSelect: function (date) {
						if (date) {
							setTimeout(function() {
								$("#date_to").focus();
							}, 200);
						}
					}
				});
				$("#date_to").datepicker({
					dateFormat: "mm-dd-yy",
					numberOfMonths: 2
				});
				break;
		}
	});

	{{#date_from_input}}
	$("#action #date_selector").trigger("change");
	{{/date_from_input}}
</script>

<br />

<div style="float:right;">Showing {{date_from}} to {{date_to}}</div>
<table id="post" style="width:300px;">
	<tr>
		<th>Channel</th>
		<th style="text-align:right;">Sessions</th>
		<th style="text-align:right;">Pageviews</th>
	</tr>
{{#rows}}
	<tr class="{{class}}">
		<td>{{source}}</td>
		<td>{{sessions}}</td>
		<td>{{pageviews}}</td>
	</tr>
{{/rows}}
</table>

<script>

$(function () {
	var chart = c3.generate({
		bindto: '#chart',
		data: { x: 'x', type: 'area', url: '/ajax/get_graph_data/?date_from={{date_from_ymd}}&date_to={{date_to_ymd}}&{{uri_post_url}}', xFormat: '%Y-%m-%d %-H:%M' },
		axis: { x: { type: 'timeseries', tick: { format: function(x) { return x.format('{{date_format}}'); } } }, y: { padding: { top: 20 }, tick: { } }, },
		grid: { y: { show: true } },
		legend: { show: false },
		transition: { duration: 1000 },
		tooltip: { show: false }
	});
});
</script>

{{> templates/footer}}
