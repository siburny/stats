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
	#posts {
		width: 400px;
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
			width: 25px;
		}
		#posts td:nth-child(3) {
			width: 80px;
			text-align:right;
		}
		#posts td:nth-child(4) {
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
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_from" id="date_from" value="{{date_from_input}}" /> to <input name="date_to" id="date_to" value="{{date_to_input}}" />
		<button id="go">GO</button>
	</div>

	<div style="clear:both"></div>
</div>

<script>
	$("#date_custom #go").on('click', function () {
		if ($("#date_from").val() && $("#date_to").val()) {
			window.location = '/portal/?date_from=' + encodeURIComponent($("#date_from").val()) + '&date_to=' + encodeURIComponent($("#date_to").val());
		}
	});

	$item = $("#action #date_selector").find("*[value='{{date_selected}}']");
	if ($item) {
	    $("#action #date_selector option").removeAttr('selected', '');
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
				window.location = '/portal/authors/?date_from=today';
				break;
			case "yesterday":
				window.location = '/portal/authors/?date_from=yesterday';
				break;
			case "7days":
				window.location = '/portal/authors/?date_from=7days';
				break;
			case "30days":
				window.location = '/portal/authors/?date_from=30days';
				break;
			case "custom":
				$("#date_custom").show();
				$("#date_from").datepicker({
					dateFormat: "mm-dd-yy",
					minDate: -365,
					maxDate: 0,
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
					minDate: -365,
					maxDate: 0,
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

<div style="float:right;">Showing stats {{^date_to}}for{{/date_to}}{{#date_to}}from{{/date_to}} {{date_from}}{{#date_to}} to {{.}}{{/date_to}}</div>

<table style="" id="posts">
	<tr>
		<th></th>
		<th>Author</th>
		<th>Sessions</th>
		<th>Pageviews</th>
	</tr>
{{#rows}}
	<tr class="{{class}}" data-url="{{url}}">
		<td>{{n}}</td>
		<td><a href="/portal/?author_name={{author}}">{{author}}</a></td>
		<td>{{sessions}}</td>
		<td>{{pageviews}}</td>
	</tr>
{{/rows}}
</table>

<script>

$(function () {
	var chart = c3.generate({
		bindto: '#chart',
		data: { x: 'x', type: 'area', url: '/ajax/get_graph_data/?date_from={{date_from_ymd}}&date_to={{date_to_ymd}}', xFormat: '%Y-%m-%d %-H:%M' },
		axis: { x: { type: 'timeseries', tick: { format: function(x) { return x.format('{{date_format}}'); } } }, y: { padding: { top: 20 }, tick: { } }, },
		grid: { y: { show: true } },
		legend: { show: false },
		transition: { duration: 1000 },
		tooltip: { show: false }
	});
});
</script>

{{> templates/footer}}
