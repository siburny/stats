{{> templates/header}}

<h2>Profile</h2>
<form action="/settings/account/" method="post">
	<input type="hidden" name="type" value="profile" />
	{{#errors_profile}}
	<ul style="border: 1px solid #faa;color:#f33;">
		{{{.}}}
	</ul>
	{{/errors_profile}}
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
		<label>
			<input type="checkbox" name="gravatar" value="1" {{#gravatar}}checked="checked"{{/gravatar}}/>
			Use Gravatar:
		</label>
	</p>
	<p>
		<input type="submit" name="submit" value="Update"/>
	</p>
</form>

<p>&nbsp;</p>

<h2>Account</h2>
<form action="/settings/account/" method="post">
	<input type="hidden" name="type" value="account" />
	{{#errors_account}}
	<ul style="border: 1px solid #faa;color:#f33;">
		{{{.}}}
	</ul>
	{{/errors_account}}
	<p>
		<label>
			Email:<br />
			<input type="text" name="email" placeholder="e.g. user@domain.com" value="{{email}}" />
		</label>
	</p>
	<p>
		<label>
			Password:<br />
			<input type="password" name="password" value="{{password}}" />
		</label>
	</p>
	<p>
		<label>
			Confirm Password:<br />
			<input type="password" name="confirmpassword" value="{{confirmpassword}}" />
		</label>
	</p>
	<p>
		<input type="submit" name="submit" value="Update"/>
	</p>
</form>

{{> templates/footer}}
