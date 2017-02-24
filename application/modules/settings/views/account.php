{{> templates/header}}

<form action="/settings/account/" method="post">
	{{#errors}}
	<ul style="border: 1px solid #faa;color:#f33;">
		{{{.}}}
	</ul>
	{{/errors}}
	<p>
		<label>
			First Name:<br />
			<input type="text" name="firstname" placeholder="e.g. John" value="{{firstname}}" />
		</label>
	</p>
	<p>
		<label>
			Last Name:<br />
			<input type="text" name="lastname" placeholder="e.g. McLain" value="{{lastname}}" />
		</label>
	</p>
	<p>
		<label>
			Position:<br />
			<input type="text" name="position" placeholder="e.g. Account Manager" value="{{position}}" />
		</label>
	</p>
	<p>
		<input type="submit" name="submit" value="Update"/>
	</p>
</form>

{{> templates/footer}}
