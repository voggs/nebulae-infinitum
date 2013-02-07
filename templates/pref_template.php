<!DOCTYPE html>
<? require_once("config/config.php");?>
<html>
<head>
<title><? echo $user['1'] ?>'s Preferences | <? echo SITE_NAME ?></title>
<link rel="stylesheet" type="text/css" href="templates/style.php" media="screen" />
<script src="data/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
// from lessthanweb.com thx guys
/* <![CDATA[ */
$(document).ready(function(){
	$("#tabs li").click(function() {
		//	First remove class "active" from currently active tab
		$("#tabs li").removeClass('active');

		//	Now add class "active" to the selected/clicked tab
		$(this).addClass("active");

		//	Hide all tab content
		$(".tab_content").hide();

		//	Here we get the href value of the selected tab
		var selected_tab = $(this).find("a").attr("href");

		//	Show the selected tab content
		$(selected_tab).fadeIn();

		//	At the end, we add return false so that the click on the link is not executed
		if ($(this).find("a").attr("href")!=<?echo '"user.php?id='.$_GET["id"].'"'?>) return false;
	});
});
/* ]]> */
</script>
</head>

<body>
<? require_once("header.php"); ?>
<div class="container">
	<div><? echo $user['1'] ?>'s Preferences</div><br/>
	<div id="tabs_wrapper" style="width:800px;float:none;">
		<div id="tabs_container">
			<ul id="tabs">
				<li class="active"><a href="#general">General</a></li>
				<li><a href="#userpage">Userpage</a></li>
				<? if ($cur_user['rank'] == "admin" || $cur_user['rank'] == "mod") echo '<li><a href="#admin">Admin</a></li>' ?>
				<? echo '<li><a href="user.php?id='.$_GET["id"].'">Back</a></li>' ?>
			</ul>
		</div>
		<div id="tabs_content_container" style="width:778px;min-height:355px;">
			<div id="general" class="tab_content" style="display: block;">
				<div style="text-decoration:underline;">Change password</div>
				<form method="post">
				<label for="curpass">Current:</label> <input type="password" name="curpass" style="margin-left:5px"/><br/>
				<label for="newpass">New:</label> <input type="password" name="newpass" style="margin-left:21px" /><br/>
				<label for="cnewpass">Confirm:</label> <input type="password" name="cnewpass" />
				<br/>
				<input type="submit" class="submitbutton" name="passchange" value="Submit" /><br/>
				</form>
				<br/>
				<div style="text-decoration:underline;">Change email</div>
				<form method="post">
				<label for="newemail">New:</label> <input type="email" name="newemail" style="margin-left:21px" /><br/>
				<input type="submit" name="emailchange" value="Submit" /><br/>
				</form><br/>
				<div style="text-decoration:underline;">Notifications settings</div>
				<form method="post">
				Send a notifications when...<br/>
				<input type="checkbox" name="notifications[0]" id="comments" value="comments"/><label for="comments">...someone comments on one of my creations</label><br/>
				<input type="checkbox" name="notifications[1]" id="replies" value="replies"/><label for="replies">...someone replies to a comment I've made</label><br/>
				<input type="submit" name="notificationchange" value="Submit" />
				</form>
				<br/>
				<div style="text-decoration:underline;">Change icon</div>
				<div class="prefsicon">Current icon:<br/>
				<?
				if (!empty($user['icon'])) echo '<img class="prefsicon" src="data/usericons/'.$user['icon'].'"/>';
				else echo '<img class="prefsicon" src="data/usericons/default.png"/>';
				?>
				</div>
				<div style="position:relative; left:10px;">
				<form method="post" enctype="multipart/form-data">
				Upload a new icon:<br/>
				<input type="file" name="newicon" accept="image/*"><br/>
				<input type="submit" name="iconchange" value="Submit" />
				<div style="clear:both;"></div>
				</form>
				</div>
			</div>
			<div id="userpage" class="tab_content">
				<div>User information:</div>
				<form method="post">
				<label for="age">Age:</label> <input type="text" name="age" style="margin-left:26px" value="<?= $user['age'] ?>"/><br/>
				<label style="margin-right:4px;" for="gender">Gender:</label>
				<select name="gender">
				<option value=""> </option>
				<?
				if ($user['gender'] == "m") $mlselected = 'selected="selected"';
				else if ($user['gender'] == "f") $fselected = 'selected="selected"';
				else if ($user['gender'] == "o") $oselected = 'selected="selected"';
				?>
				<option value="m" <?= $mlselected ?>>Male</option>
				<option value="f" <?= $fselected ?>>Female</option>
				<option value="o" <?= $oselected ?>>Other</option>
				</select><br/>
				<label for="location">Location:</label> <input type="text" name="location" style="margin-left:0px" value="<?= $user['location'] ?>"/><br/>
				<label for="about">About Me:</label><br/>
				<textarea name="about" rows="10" cols="30" style="max-width:500px;max-height:200px;"><?= $user['about'] ?></textarea>
				<br/>
				<input type="submit" name="userchange" value="Submit" /><br/>
				</form>
				<br/>
			</div>
			<? if ($cur_user['rank'] == "admin" || $cur_user['rank'] == "mod") echo '<div id="admin" class="tab_content">
				<div>Admin preferences:</div><br/>
				<form method="post">'; 
				$banlength = date("z",strtotime($user['banneduntil'])-strtotime($user['bandate'])) + ((date("Y",strtotime($user['banneduntil'])-strtotime($user['bandate']))-1970)*365);
				if ($banlength==-15592) $banlength=0;
				if ($user['rank'] == "user") $uselected = 'selected="selected"';
				else if ($user['rank'] == "mod") $mselected = 'selected="selected"';
				else if ($user['rank'] == "admin") $aselected = 'selected="selected"';
				if ($cur_user['rank'] == "admin") echo '<label for="rank">Rank:</label>
				<select name="rank">
				<option value="user" '.$uselected.'>User</option>
				<option value="mod" '.$mselected.'>Mod</option>
				<option value="admin" '.$aselected.'>Admin</option>
				</select><br/>';
				if ($user['banstatus'] == "unbanned") $usselected = 'checked="checked"';
				else if ($user['banstatus'] == "banned") $bselected = 'checked="checked"';
				else if ($user['banstatus'] == "deleted") $dselected = 'checked="checked"';
				if ($cur_user['rank'] == "admin" || $cur_user['rank'] == "mod") echo '
				<label for="ban">Ban status:</label><br/>
				<input type="radio" name="ban" value="unbanned" '.$usselected.'/> Unbanned<br/>
				<input type="radio" name="ban" value="banned" '.$bselected.'/> Banned<br/>
				<input type="radio" name="ban" value="deleted" '.$dselected.'/> Deleted (hidden)<br/>
				<label for="banneduntil">Days banned for:</label><br/> <input type="text" name="banneduntil" style="margin-left:0px" value="'.$banlength.'"/><br/>
				(expires on '.date("M d Y",strtotime($user['banneduntil'])).')
				<br/>
				<label for="banreason">Ban reason:</label><br/>
				<textarea name="banreason" rows="5" cols="30" style="max-width:500px;max-height:200px;">'.$user[15].'</textarea>
				<br/>
				<input type="submit" name="adminchange" value="Submit" /><br/>
				</form>
				<br/>
			</div>
			';
			?></div>
		</div>
</body>
</html>