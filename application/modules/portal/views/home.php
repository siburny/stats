{{> templates/header}}

<link href="/css/c3.css" rel="stylesheet" type="text/css">
<script src="/js/d3.min.js" charset="utf-8"></script>
<script src="/js/c3.min.js"></script>
<script src="/js/jquery.ajaxMultiQueue.js"></script>

<h2>All views</h2>
<div style="float:right;width:300px;">
	<div style="padding:10px 10px 10px 30px;font-size:1.5em;line-height:200%;">
		{{#totals}}
		Visits: {{sessions}}<br />
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
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_from" id="date_from" value="{{date_from_input}}" /> to <input name="date_to" id="date_to" value="{{date_to_input}}" />
		<button id="go">GO</button>
	</div>

	<div style="margin-left:50px;float:left;" class="search_box">
		<input type="text" placeholder="Enter search here" class="ui-widget ui-corner-all ui-button" value="{{post_search}}" /><input type="button" value="GO" class="ui-button ui-widget ui-corner-all" />
	</div>

	<div style="clear:both"></div>
</div>

<script>
	$(".search_box input[type=button]").on('click', function() {
		window.location = '/portal/?search=' + encodeURIComponent($(".search_box input[type=text]").val());
	});
	$(".search_box input[type=text]").keypress(function(e) {
    	if(e.which == 13) {
			window.location = '/portal/?search=' + encodeURIComponent($(".search_box input[type=text]").val());
    	}
	});

	$("#date_custom #go").on('click', function () {
		if ($("#date_from").val() && $("#date_to").val()) {
			window.location = '/portal/?date_from=' + encodeURIComponent($("#date_from").val()) + '&date_to=' + encodeURIComponent($("#date_to").val()) + '{{#uri_author}}&{{.}}{{/uri_author}}';
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
				window.location = '/portal/?date_from=today{{#uri_author}}&{{.}}{{/uri_author}}{{#uri_search}}&{{.}}{{/uri_search}}';
				break;
			case "yesterday":
				window.location = '/portal/?date_from=yesterday{{#uri_author}}&{{.}}{{/uri_author}}{{#uri_search}}&{{.}}{{/uri_search}}';
				break;
			case "7days":
				window.location = '/portal/?date_from=7days{{#uri_author}}&{{.}}{{/uri_author}}{{#uri_search}}&{{.}}{{/uri_search}}';
				break;
			case "30days":
				window.location = '/portal/?date_from=30days{{#uri_author}}&{{.}}{{/uri_author}}{{#uri_search}}&{{.}}{{/uri_search}}';
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

{{#prev_link}}<a href="{{.}}">{{/prev_link}}&lt;&nbsp;PREV{{#prev_link}}</a>{{/prev_link}}
&nbsp;&nbsp;&nbsp;{{results_count}}&nbsp;&nbsp;&nbsp;
{{#next_link}}<a href="{{.}}">{{/next_link}}NEXT&nbsp;&gt;{{#next_link}}</a>{{/next_link}}

<table style="" id="posts">
	<tr>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th>Sessions</th>
		<th>Pageviews</th>
		<th></th>
	</tr>
{{#rows}}
	<tr class="{{class}}" data-url="{{url}}">
		<td>{{n}}</td>
		<td class="image"><img src="{{image}}" alt=""/></td>
		<td>
			<div class="title" style="clear:both;margin-bottom:5px;">
				<a href="/portal/post/?post_id={{post_id}}{{#date_link}}&{{{.}}}{{/date_link}}" style="font-weight:bold;font-size:125%;text-decoration:none;">{{title}}</a><a href="{{url}}" target="_blank"><img src="/images/ic_open_in_new_black_18dp_1x.png" /></a>
			</div>
			<div style="font-size:90%;">
				<div class="date_published">{{date_published}}{{#author}} by {{^uri_author}}<a style="font-weight:bold;" href="/portal/?author_name={{.}}{{#date_link}}&{{{.}}}{{/date_link}}">{{/uri_author}}{{.}}{{^uri_author}}</a>{{/uri_author}}{{/author}}</div>
			</div>
		</td>
		<td>
			<div id="chart{{n}}" data-url="{{url}}" style="width:100px;height:50px;"></div>
		</td>
		<td>{{sessions}}</td>
		<td>{{pageviews}}</td>
		<td>
			{{#up_arrow}}<img src="/images/arrow_up_green.png" alt="UP" />{{/up_arrow}}
			{{#down_arrow}}<img src="/images/arrow_down_red.png" alt="UP" />{{/down_arrow}}
			<span style="color:{{#up_arrow}}green{{/up_arrow}}{{#down_arrow}}red{{/down_arrow}}">{{up_down_text}}</span>
		</td>
	</tr>
{{/rows}}
</table>
<div style="float:right;">GA last updated: {{last_updated}}</div>

{{#prev_link}}<a href="{{.}}">{{/prev_link}}&lt;&nbsp;PREV{{#prev_link}}</a>{{/prev_link}}
&nbsp;&nbsp;&nbsp;{{results_count}}&nbsp;&nbsp;&nbsp;
{{#next_link}}<a href="{{.}}">{{/next_link}}NEXT&nbsp;&gt;{{#next_link}}</a>{{/next_link}}

<script>

$(function () {
	var chart = c3.generate({
		bindto: '#chart',
		data: { x: 'x', type: 'area', url: '/ajax/get_graph_data/?{{{portal_link}}}', xFormat: '%Y-%m-%d %-H:%M' },
		axis: { x: { type: 'timeseries' }, y: { padding: { top: 20 }, tick: { } }, },
		grid: { y: { show: true } },
		legend: { show: false },
		transition: { duration: 1000 },
		tooltip: { show: false }
	});

	$("#posts tr1:not(:first)").each(function(index, value) {
		$chart = $(value);
		var chart = c3.generate({
			bindto: '#chart'+$chart.find("td:first-child").text(),
			data: {
				x: 'x',
				type: 'bar',
				url: '/ajax/get_post_graph_data?url=' + encodeURIComponent($chart.data("url"))
			},
			axis: { x: { type: 'timeseries', show: false }, y: { show: false } },
			legend: { show: false },
			tooltip: { show: false },
			bar: { width: { ratio: 0.9 } },
		});
	});
});
</script>

{{> templates/footer}}
