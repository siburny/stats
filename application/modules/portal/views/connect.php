{{> templates/header}}

<h2>Status</h2>
<p>{{status}}</p>
{{#hasTokens}}
<ul>
{{#token}}<li>{{{.}}}</li>{{/token}}
</ul>
{{/hasTokens}}
{{#error}}
<p><b>{{.}}</b></p>
{{/error}}
{{#done}}
<p><b>Done!</b> <a href='/portal/'>Continue to Portal</a></p>
{{/done}}

{{> templates/footer}}
