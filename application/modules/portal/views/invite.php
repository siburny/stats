{{> templates/header}}

<style>
	.users {
		width: 100%;
		border-spacing: 0px;
		border-collapse: collapse;
	}
		.users tr {
			border: 1px solid #333;
		}
		.users td, .users th {
			padding: 5px;
			text-align:center;
		}
</style>

<h2><a href="/portal/invite_user/">Invite User</a></h2>

<h2>Active</h2>
<table class="users">
	<tr>
		<th>Name</th>
		<th>Email</th>
		<th>Tracker</th>
		<th>Role</th>
		<th>Joined</th>
		<td>&nbsp;</td>
	</tr>
{{#active_users}}
	<tr>
		<td>{{first_name}} {{last_name}}</td>
		<td>{{email}}</td>
		<td></td>
		<td>{{role}}</td>
		<td>{{created_on_format}}</td>
		<td><a href="#">Delete</a></td>
	</tr>
{{/active_users}}
{{^active_users}}
	<tr>
		<td colspan="7" style="text-align:center;padding:30px;">No users found.</td>
	</tr>
{{/active_users}}
</table>

<h2>Invited</h2>
<table class="users">
	<tr>
		<th></th>
		<th>Name</th>
		<th>Email</th>
		<th>Tracker</th>
		<th>Role</th>
		<th>Joined</th>
		<td>&nbsp;</td>
	</tr>
	{{#invited_users}}
	<tr>
		<td>{{first_name}} {{last_name}}</td>
		<td>{{email}}</td>
		<td></td>
		<td>{{role}}</td>
		<td>{{created_on_format}}</td>
		<td><a href="#">Cancel</a></td>
	</tr>
	{{/invited_users}}
	{{^invited_users}}
	<tr>
		<td colspan="7" style="text-align:center;padding:30px;">No users found.</td>
	</tr>
	{{/invited_users}}
</table>

{{> templates/footer}}
