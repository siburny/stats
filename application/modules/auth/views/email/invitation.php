<html>
<body>
  <p>Hi <?php echo $name; ?></p>
	<p>You have been invited to <a href="http://www.oodash.com" target="_blank">oodash.com</a>.</p>
	<p><?php echo $custom_message; ?></p>
  <p>Please click this link to <?php echo anchor('auth/invitation/'. $activation, 'Activate Your Account');?></p>
</body>
</html>