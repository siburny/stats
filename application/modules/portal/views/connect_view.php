{{> templates/header}}

{{#selection}}<h2>Choose a {{.}}</h2>{{/selection}}
<p>{{{status}}}</p>
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
