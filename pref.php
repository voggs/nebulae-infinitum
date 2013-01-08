<?php
//Include config, connect to database, and start session
require_once("config/config.php");
error_reporting(E_ALL ^ E_NOTICE);

$connection = mysql_connect(MYSQL_SERVER,MYSQL_USER,MYSQL_PASS);
if (!$connection){die("Could not connect to database: " . mysql_error());}
mysql_select_db(MYSQL_DATABASE, $connection);

session_start();

//Initialise variable
$userid = null;
$passupdate = null;

$result = mysql_query("SELECT * FROM users WHERE id = ".$_SESSION['SESS_MEMBER_ID']);
if (!$result) {
	die(require_once("errors/403.php"));
}
$luserdata = mysql_fetch_row($result);

//Check if user is admin or has same ID as specified in URL
//If not, die
if (isset($_GET["id"])) {	
	$userid = $_GET["id"];	

	//Get user info from database
	$result = mysql_query("SELECT * FROM users WHERE id = $userid");
	if (!$result) {
		require_once("errors/404.php");
		exit();
	}
	$userdata = mysql_fetch_row($result);
	
	if ( mysql_num_rows($result) != 1 || !$userdata || strcspn($userid,"0123456789")>0){
		require_once("errors/404.php");
		exit();
	}
	
	if ($_SESSION['SESS_MEMBER_ID'] != $userid && $luserdata['3'] != "admin" && $luserdata['3'] != "mod" ){
		require_once("errors/403.php");
		exit();
	}
	if ($luserdata[6] == "banned") {
		include_once("errors/ban.php");
		exit();
	}
	else if ($luserdata[6] == "deleted") {
		include_once("errors/delete.php");
		exit();
	}
	
	require_once("templates/pref_template.php");
}
else{
	require_once("errors/404.php");
	exit();
}

if (isset($_POST['passchange'])) {
	$result = mysql_query("SELECT * FROM users WHERE id='$userid' AND password='".nebulae_hash($_POST['curpass'])."'");
	if (mysql_num_rows($result) != 1){
		die("Invalid current password.");
	}
	if (nebulae_hash($_POST['newpass']) != nebulae_hash($_POST['cnewpass'])){
		die("The new passwords do not match.");
	}
	mysql_query("UPDATE users SET password='".nebulae_hash($_POST['newpass'])."' WHERE id='$userid'") or die(mysql_error());
	echo "<meta http-equiv='Refresh' content='0; URL=pref.php?id=$userid'>";
}
if (isset($_POST['emailchange'])) {
	mysql_query("UPDATE users SET email='".addslashes($_POST['newemail'])."' WHERE id='$userid'") or die(mysql_error());
	echo "<meta http-equiv='Refresh' content='0; URL=pref.php?id=$userid'>";
}
if (isset($_POST['iconchange'])) {
	if (!empty($_FILES['newicon']) && file_exists($_FILES['newicon']['tmp_name'])) {
		$thumbimg = imagecreatefromstring(file_get_contents($_FILES['newicon']['tmp_name']));
		$rzthumbimg = imagecreatetruecolor(180,180);
		imagecopyresampled($rzthumbimg, $thumbimg, 0, 0, 0, 0, 180, 180, imagesx($thumbimg), imagesy($thumbimg));
		imagepng($rzthumbimg,"data/usericons/".$userid.".png",9);
		mysql_query("UPDATE users SET icon='".$userid.".png' WHERE id='$userid'") or die(mysql_error());
		echo "<meta http-equiv='Refresh' content='0; URL=pref.php?id=$userid'>";
		exit();
	}
	else die(mysql_error());
}
if (isset($_POST['userchange'])) {
	mysql_query("UPDATE users SET age='".htmlspecialchars(addslashes($_POST['age']))."' WHERE id='$userid'") or die(mysql_error());
	mysql_query("UPDATE users SET gender='".htmlspecialchars(addslashes($_POST['gender']))."' WHERE id='$userid'") or die(mysql_error());
	mysql_query("UPDATE users SET location='".htmlspecialchars(addslashes($_POST['location']))."' WHERE id='$userid'") or die(mysql_error());
	mysql_query("UPDATE users SET about='".htmlspecialchars(addslashes($_POST['about']))."' WHERE id='$userid'") or die(mysql_error());
	echo "<meta http-equiv='Refresh' content='0; URL=pref.php?id=$userid'>";
	exit();
}
if (isset($_POST['adminchange'])) {
	if ( $luserdata[3] == "admin" && ($_POST['rank']=='admin'||$_POST['rank']=='mod'||$_POST['rank']=='user')) mysql_query("UPDATE users SET rank='".$_POST['rank']."' WHERE id='$userid'") or die(mysql_error());
	if ( $luserdata[3] == "admin" || $luserdata['3'] == "mod" ){
		mysql_query("UPDATE users SET banstatus='".$_POST['ban']."' WHERE id='$userid'") or die(mysql_error());
		$bandate=mysql_fetch_row(mysql_query("SELECT DATE_ADD(CURDATE(),INTERVAL ".addslashes(abs((int) $_POST['banneduntil']))." DAY)"));
		//catch for year 2038 problem
		//due to a limitation in strtotime function... is 64-bit machine needed?
		if (strtotime($bandate[0])<strtotime("2038/01/19")){
			mysql_query("UPDATE users SET bandate=CURDATE(), banneduntil=date_add(CURDATE(),INTERVAL ".addslashes(abs((int) $_POST['banneduntil']))." DAY) WHERE id='$userid'") or die(mysql_error());
		}
		else {
			mysql_query("UPDATE users SET bandate=CURDATE(), banneduntil='2038-01-19' WHERE id='$userid'") or die(mysql_error());
		}
		mysql_query("UPDATE users SET banreason='".addslashes($_POST['banreason'])."' WHERE id='$userid'") or die(mysql_error());
	}
	echo "<meta http-equiv='Refresh' content='0; URL=pref.php?id=$userid'>";
	exit();
}?>