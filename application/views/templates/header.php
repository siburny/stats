<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Stats</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	
	.clear {
		clear:both;
	}

	ul#menu {
		border:1px solid #AAF;
		display:inline-block;
		margin:0;
		padding:0;
	}
	ul#menu li {
		display:block;
		float:left;
	}
	ul#menu li a {
		display:block;
		text-align:center;
		width:140px;
		padding: 10px 0px;
		background-color:#EEF;
		text-decoration:none;
	}
	ul#menu li.active a {
		background-color:#CCF;
	}
	ul#menu li a:hover {
		background-color:#DDF;
	}
	</style>
	<script src="/js/jquery-2.2.0.min.js"></script>
	<script src="/js/spin.min.js"></script>
	<script src="/js/jquery.spin.js"></script>

	<link href="/css/jquery-ui.min.css" rel="stylesheet"> 
	<script src="/js/jquery-ui.min.js"></script>
</head>
<body>

<div id="container">
	{{#is_logged_in}}<a href="/auth/logout" style="float:right;margin: 10px 10px 0 0;">Log out</a>{{/is_logged_in}}
	<h1>{{page_title}}</h1>

	<div id="body">
		<ul id="menu">
			<li{{#active_menu_posts}} class="active"{{/active_menu_posts}}><a href="/portal/">Posts</a></li>
			<li{{#active_menu_authors}} class="active"{{/active_menu_authors}}><a href="/portal/authors/">Authors</a></li>
			<li{{#active_menu_settings}} class="active"{{/active_menu_settings}}><a href="/settings/">Settings</a></li>
			<li{{#active_menu_users}} class="active"{{/active_menu_users}}><a href="/portal/invite/">Users</a></li>
		</ul>
		<div class="clear"></div>
