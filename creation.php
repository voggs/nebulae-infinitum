<?php
//Include config
require_once("config/config.php");
error_reporting(E_ALL ^ E_NOTICE); 
session_start();

//Connect to database
$connection = mysql_connect(MYSQL_SERVER,MYSQL_USER,MYSQL_PASS);
if (!$connection){die("Could not connect to database: " . mysql_error());}
mysql_select_db(MYSQL_DATABASE, $connection);

//Initialise variable
$creationid = null;

//Get current user info from database
if (!empty($_SESSION['SESS_MEMBER_ID'])){
	$lresult = mysql_query("SELECT * FROM users WHERE id = ".$_SESSION['SESS_MEMBER_ID']);
	if (!$lresult) {
		echo "Could not run query: " . mysql_error() and die;
	}
	$luserdata = mysql_fetch_row($lresult);
}

//Get creation ID from URL
//If creation ID not found or is NaN, die
if (isset($_GET["id"])) $creationid = htmlspecialchars($_GET["id"]);
if (!$creationid || strcspn($creationid,"0123456789")>0){
	include_once("errors/404.php");
	exit();
}

//Get creation info from database
$result = mysql_query("SELECT * FROM creations WHERE id = $creationid");
if (!$result) {
    die(mysql_error());
}
$creationdata = mysql_fetch_row($result);

//If creation ID is not a valid creation, die
if (!$creationdata){
	include_once("errors/404.php");
	exit();
}

//Test if the creation has enough flags to be auto-censored and censor it if it does
//If creation is marked as alright even after three flags, the creation still shows
$i=0;
$fresult = mysql_query("SELECT * FROM flags WHERE creationid = $creationid") or die(mysql_error());
while($row = mysql_fetch_array($fresult)){
	$flags[$i] = $row[2];
	$i++;
}
$farray=mysql_fetch_row(mysql_query("SELECT hidden FROM creations WHERE id = ".$creationdata[0]));
if (!empty($flags)){
	if (count(array_unique($flags))>=FLAGS_REQUIRED&&$farray[0]=="no") {
		mysql_query("UPDATE creations SET hidden='flagged' WHERE id='$creationdata[0]'") or die(mysql_error());
		mysql_query("DELETE FROM flags WHERE creationid=".$creationdata[0]." AND type='creation'");
	}
}


if ($luserdata[6] == "banned") {
	include_once("errors/ban.php");
	exit();
}
else if ($luserdata[6] == "deleted") {
	include_once("errors/delete.php");
	exit();
}
if ($creationdata[6] == "byowner" && $luserdata[0] != $userdata[0] && $luserdata[3] != "admin" && $luserdata[3] != "mod") {
	include_once("errors/creation_hidden.php");
	exit();
}
if (($creationdata[6] == "censored" || $creationdata[6] == "flagged")&& $luserdata[3] != "admin" && $luserdata[3] != "mod") {
	include_once("errors/creation_censored.php");
	exit();
}
//If creation is deleted and user isn't admin or mod, die
if ($creationdata[6] == "deleted" && $luserdata[3] != "admin" && $luserdata[3] != "mod") {
	include_once("errors/404.php");
	exit();
}

if (!empty($_SESSION['SESS_MEMBER_ID'])){
	if (mysql_num_rows(mysql_query("SELECT * FROM views WHERE viewip='$_SERVER[REMOTE_ADDR]' AND creationid=$creationdata[0]"))==0){
		mysql_query("INSERT INTO views (creationid, viewip) VALUES ($creationdata[0], '$_SERVER[REMOTE_ADDR]')");
	}

	if (mysql_num_rows(mysql_query("SELECT * FROM favourites WHERE creationid=$creationdata[0] AND userid=$luserdata[0]"))!=0){
		$favourited = true;
	}
	else $favourited = false;
}

//Get creation owner info from database
$result = mysql_query("SELECT * FROM users WHERE id = $creationdata[3]");
if (!$result) {
    die(mysql_error());
}
$userdata = mysql_fetch_row($result);

//Get if the action is favouriting
if (isset($_GET["action"])) if ($_GET["action"] == "favourite") {
	if (empty($_SESSION['SESS_MEMBER_ID'])){
		header("location: creation.php?id=$creationid");
		exit();
	}
	if (!$favourited){
		mysql_query("INSERT INTO favourites (creationid, userid) VALUES ($creationdata[0], $luserdata[0])");
		$favourited = true;
		header("location: creation.php?id=$creationid");
		exit();
	}
	else if ($favourited){
		mysql_query("DELETE FROM favourites WHERE creationid=$creationdata[0] AND userid=$luserdata[0]");
		$favourited = false;
		header("location: creation.php?id=$creationid");
		exit();
	}
}

//Get if the action is rating
if (isset($_GET["action"])) if ($_GET["action"] == "rate") {
	if (empty($_SESSION['SESS_MEMBER_ID'])){
		header("location: creation.php?id=$creationid");
		exit();
	}
	if (empty($_GET["rating"])){
		header("location: creation.php?id=$creationid");
		exit();
	}
	if ($_GET["rating"]<1 || $_GET["rating"]>5){
		header("location: creation.php?id=$creationid");
		exit();
	}
	if (mysql_num_rows(mysql_query("SELECT * FROM ratings WHERE userid='$luserdata[0]' AND creationid='$creationdata[0]'"))==0){
		mysql_query("INSERT INTO ratings (creationid, userid, rating) VALUES ($creationdata[0], $luserdata[0], ".$_GET["rating"].")") or die(mysql_error());
		header("location: creation.php?id=$creationid");
	}
	mysql_query("UPDATE ratings SET rating='".$_GET["rating"]."' WHERE userid='$luserdata[0]' AND creationid='$creationdata[0]'") or die(mysql_error());
	header("location: creation.php?id=$creationid");
	exit();
}

//Get if the action is changing the player
if (isset($_GET["action"])) if ($_GET["action"] == "player"){
	if (empty($_SESSION['SESS_MEMBER_ID'])){
		header("location: creation.php?id=$creationid");
		exit();
	}
	if (empty($_GET["player"])){
		header("location: creation.php?id=$creationid");
		exit();
	}
	if ($_GET["player"]!="js" && $_GET["player"]!="flash"){
		header("location: creation.php?id=$creationid");
		exit();
	}
	mysql_query("UPDATE users SET sb2player='".$_GET["player"]."' WHERE id='$luserdata[0]'") or die(mysql_error());
	header("location: creation.php?id=$creationid");
	exit();
}

$views = mysql_num_rows(mysql_query("SELECT * FROM views WHERE creationid=$creationdata[0]"));
mysql_query("UPDATE creations SET views=".$views." WHERE id=$creationdata[0]");
$favourites = mysql_num_rows(mysql_query("SELECT * FROM favourites WHERE creationid=$creationdata[0]"));
mysql_query("UPDATE creations SET favourites=".$favourites." WHERE id=$creationdata[0]");
$i = 0;
//Get ratings
$result = mysql_query("SELECT rating FROM ratings WHERE creationid=$creationdata[0]");
while($row = mysql_fetch_array($result)){
	$ratings[$i] = $row[0];
	$i++;
}
if (empty($ratings[0])) $ratings[0] = 0;
$lrating = mysql_fetch_row(mysql_query("SELECT rating FROM ratings WHERE creationid=$creationdata[0]"));
$comments = mysql_query("SELECT * FROM comments WHERE creationid=$creationdata[0] ORDER BY timestamp DESC,userid DESC");

//If creation ID is a number and corresponds to valid data in the database, display creation
if ($creationdata[2] == "artwork") require_once("templates/artwork_template.php");
if ($creationdata[2] == "scratch") require_once("templates/scratch_template.php");
if ($creationdata[2] == "flash") require_once("templates/flash_template.php");
if ($creationdata[2] == "writing") require_once("templates/writing_template.php");
if ($creationdata[2] == "audio") require_once("templates/audio_template.php");

if (isset($_POST['newcomment'])) {
	if (!empty($_POST['commenttext']) && strlen(trim($_POST['commenttext']))>0) {
		if (!empty($_SESSION['SESS_MEMBER_ID'])){
			mysql_query("INSERT INTO comments (creationid, userid, comment) VALUES ($creationdata[0], $luserdata[0], '".strip_tags(trim(addslashes($_POST[commenttext]))." "."')")) or die(mysql_error());
			$commentid=mysql_insert_id();
			//send notification about the comment
			$notificationmessage='You have received a new comment by [url=user.php?id='.$luserdata[0].']'.$luserdata[1].'[/url] on your creation [url=creation.php?id='.$creationdata[0].'#'.$commentid.']'.$creationdata[1].'[/url]!';
			mysql_query("INSERT INTO messages (recipientid,senderid,message,type) VALUES (".$creationdata[3].",".$luserdata[0].",'".addslashes($notificationmessage)."','notification')");
			echo "<meta http-equiv='Refresh' content='0; URL=creation.php?id=$creationid'>";
			exit();
		}
	}
}
if (isset($_POST['reply'])){
	mysql_data_seek($comments,0);
	while ($commentdata=mysql_fetch_row($comments)){
		if (isset($_POST['msgsubmit'.$commentdata[4]])&&strlen(trim($_POST['msgsubmit'.$commentdata[4]]))>0){
			if (!empty($_SESSION['SESS_MEMBER_ID'])){
				mysql_query("INSERT INTO comments (creationid, userid, comment) VALUES ($creationdata[0], $luserdata[0], '".trim(addslashes($_POST["msgbody".$commentdata[4]]))." "."')") or die(mysql_error());
				$commentid=mysql_insert_id();
				//send notification about the comment
				$notificationmessage='You have received a new comment by [url=user.php?id='.$luserdata[0].']'.$luserdata[1].'[/url] on your creation [url=creation.php?id='.$creationdata[0].'#'.$commentid.']'.addslashes($creationdata[1]).'[/url]!';
				mysql_query("INSERT INTO messages (recipientid,senderid,message,type) VALUES (".$creationdata[3].",".$luserdata[0].",'".$notificationmessage."','notification')");
				$cuserdata = mysql_fetch_row(mysql_query("SELECT * FROM users WHERE id=$commentdata[4]"));
				if($cuserdata[0]!=$creationdata[3]){
					$notificationmessage='Your comment on the creation [url=creation.php?id='.$creationdata[0].'#'.$commentid.']'.addslashes($creationdata[1]).'[/url] has been replied to by [url=user.php?id='.$luserdata[0].']'.$luserdata[1].'[/url]!';
					mysql_query("INSERT INTO messages (recipientid,senderid,message,type) VALUES (".$cuserdata[0].",".$luserdata[0].",'".$notificationmessage."','notification')");
				}
				echo "<meta http-equiv='Refresh' content='0; URL=creation.php?id=$creationid'>";
				exit();
			}
		}
	}
}

//function used to set the light-up rating globes to their default
function globesToCurrentRating($crating_arr){
	$crating=$crating_arr[0];
	if ($crating>=1) echo '$("#rating1").css("background-image","url(\'data/icons/prostar.png\')");';
	else echo '$("#rating1").css("background-image","url(\'data/icons/antistar.png\')");';
	if ($crating>=2) echo '$("#rating2").css("background-image","url(\'data/icons/prostar.png\')");';
	else echo '$("#rating2").css("background-image","url(\'data/icons/antistar.png\')");';
	if ($crating>=3) echo '$("#rating3").css("background-image","url(\'data/icons/prostar.png\')");';
	else echo '$("#rating3").css("background-image","url(\'data/icons/antistar.png\')");';
	if ($crating>=4) echo '$("#rating4").css("background-image","url(\'data/icons/prostar.png\')");';
	else echo '$("#rating4").css("background-image","url(\'data/icons/antistar.png\')");';
	if ($crating>=5) echo '$("#rating5").css("background-image","url(\'data/icons/prostar.png\')");';
	else echo '$("#rating5").css("background-image","url(\'data/icons/antistar.png\')");';
}
?>