{{> templates/header}}

<script>
	$(function () {
		$("#manager").on("change", function () {
			if ($(this).val() === "0") {
				$("#row_name").show();
			} else {
				$("#row_name").hide();
			}
		});
	});
</script>

<form action="/portal/invite_user/" method="post">
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
			Email:<br />
			<input type="text" name="email" placeholder="e.g. user@company.com" value="{{email}}" />
		</label>
	</p>
	<p>
		<label>
			Role:<br />
			<select name="manager" id="manager">
				<option></option>
				<option value="1">Manager</option>
				<option value="0">Author</option>
			</select>
		</label>
	</p>
	<p style="display:none;" id="row_name">
		<label>
			Tracker Name:<br />
			<select>
				<option></option>
				{{#names}}
				<option value="{{.}}">{{.}}</option>
				{{/names}}
			</select>
		</label>
	</p>
	<p>
		<label>
			Position:<br />
			<input type="text" name="position" placeholder="e.g. Account Manager" value="{{position}}" />
		</label>
	</p>
	<p>
		<input type="submit" name="submit" value="Invite"/>
	</p>
</form>

{{> templates/footer}}
