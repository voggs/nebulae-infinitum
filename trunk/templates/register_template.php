<!DOCTYPE html>
<? require_once("config/config.php"); ?>
<html>
<head>
<title>Register | <? echo SITE_NAME ?></title>
<link rel="stylesheet" type="text/css" href="templates/style.php" media="screen" />
</head>

<body>
<? require_once("header.php"); ?>
<div class="container">
<h1>Register</h1>
<div>Fill out the form below to create a new account. <a href="login.php">But I already have an account!</a></div><br/>
<form method="post">
<label style="margin-right:5px;" for="user">Username:</label><input type="text" name="user" /><br/>
<label style="margin-right:8px;" for="pass">Password:</label><input type="password" name="pass" /><br/>
<label style="margin-right:20px;" for="cpass">Confirm:</label><input type="password" name="cpass" /><br/>
<label style="margin-right:32px;" for="email">Email:</label><input type="email" name="email" /><br/>
The following fields are optional.<br/>
<label style="margin-right:43px;" for="age">Age:</label><input type="text" name="age" /><br/>

<label style="margin-right:20px;" for="gender">Gender:</label>
<select name="gender">
<option value=""> </option>
<option value="m">Male</option>
<option value="f">Female</option>
<option value="o">Other</option>
</select><br/>
<label style="margin-right:17px;" for="location">Location:</label><input type="text" name="location" /><br/>
<br/>
<input type="submit" name="rsubmit" value="Register" />
</form>
</body>
</html>