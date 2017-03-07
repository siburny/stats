{{> templates/header}}


<form action="/settings/preferences/" method="post">

  {{#errors}}
  <ul style="border: 1px solid #faa;color:#f33;">
      {{{.}}}
  </ul>
  {{/errors}}

	<b>Date format:</b><br />
	<label><input type="radio" name="date_format" value="0" {{#date_format_0}}checked="checked"{{/date_format_0}}/> February 28, 2017</label><br />
	<label><input type="radio" name="date_format" value="1" {{#date_format_1}}checked="checked"{{/date_format_1}}/> 02/28/2017</label><br />
	<label><input type="radio" name="date_format" value="2" {{#date_format_2}}checked="checked"{{/date_format_2}}/> 28/02/2017</label><br />
	<label><input type="radio" name="date_format" value="3" {{#date_format_3}}checked="checked"{{/date_format_3}}/> 2017-02-31</label><br />

	<br />

	<b>Default Date Range:</b><br />
	<label><input type="radio" name="date_range" value="0" {{#date_range_0}}checked="checked"{{/date_range_0}}/> 30-days</label><br />
	<label><input type="radio" name="date_range" value="1" {{#date_range_1}}checked="checked"{{/date_range_1}}/> 7-days</label><br />
	<label><input type="radio" name="date_range" value="2" {{#date_range_2}}checked="checked"{{/date_range_2}}/> Yesterday</label><br />
	<label><input type="radio" name="date_range" value="3" {{#date_range_3}}checked="checked"{{/date_range_3}}/> Today</label><br />

	<br />

	<b>Default Sorting:</b><br />
	<label><input type="radio" name="sorting" value="0" {{#sorting_0}}checked="checked"{{/sorting_0}}/> Sessions</label><br />
	<label><input type="radio" name="sorting" value="1" {{#sorting_1}}checked="checked"{{/sorting_1}}/> Pageviews</label><br />

  <br /><input type="submit" name="submit" value="Update" />

</form>

{{> templates/footer}}
