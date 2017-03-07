{{> templates/header}}

<form action="/settings/preferences/" method="post">

	<b>Date format:</b><br />
	<label><input type="radio" name="date_format" value="0" /> February 28, 2017</label><br />
	<label><input type="radio" name="date_format" value="1" /> 02/28/2017</label><br />
	<label><input type="radio" name="date_format" value="2" /> 28/02/2017</label><br />
	<label><input type="radio" name="date_format" value="3" /> 2017-02-31</label><br />

	<br />

	<b>Default Date Range:</b><br />
	<label><input type="radio" name="default_range" value="0" /> 30-days</label><br />
	<label><input type="radio" name="default_range" value="1" /> 7-days</label><br />
	<label><input type="radio" name="default_range" value="2" /> Yesterday</label><br />
	<label><input type="radio" name="default_range" value="3" /> Today</label><br />

	<br />

	<b>Default Sorting:</b><br />
	<label><input type="radio" name="default_sorting" value="0" /> Sessions</label><br />
	<label><input type="radio" name="default_sorting" value="1" /> Pageviews</label><br />

</form>

{{> templates/footer}}
