<?php
//Include config
require_once(BASE_DIRECTORY."/config/config.php");
error_reporting(E_ALL ^ E_NOTICE); 
session_start();
//Connect to database
$connection = mysql_connect(MYSQL_SERVER,MYSQL_USER,MYSQL_PASS);
if (!$connection){die("Could not connect to database: " . mysql_error());}
mysql_select_db(MYSQL_DATABASE, $connection);
//Get current user info from database
if (!empty($_SESSION['SESS_MEMBER_ID'])){
	$lresult = mysql_query("SELECT * FROM users WHERE id = ".$_SESSION['SESS_MEMBER_ID']);
	if (!$lresult) {
		die(mysql_error());
	}
	$cur_user = mysql_fetch_array($lresult);
	if(mysql_query("SELECT id FROM messages WHERE viewed=0 AND recipientid=".$cur_user['id'])==false)$msg=0;
	else $msg = mysql_num_rows(mysql_query("SELECT id FROM messages WHERE viewed=0 AND recipientid=".$cur_user['id']));
}
?>
<div class="header">
	<a class="headtext" href="<?php echo BASE_URL ?>/">
		<?php echo strtolower(SITE_NAME)?>
	</a>
	<br/>
	<div class="headlinks">
		<a class="head" href="<?php echo BASE_URL ?>/">home</a> &bull; 
		<a class="head" href="<?php echo BASE_URL ?>/creations">creations</a> &bull; 
		<a class="head" href="<? echo BASE_URL ?>/about/">about</a> &bull; 
		<a class="head" href="<? echo BASE_URL ?>/forums/">forums</a> 
		<?php
		if ($cur_user['rank']=="admin" || $cur_user['rank']=="mod"){
			echo ' &bull; <a class="head" href="'.BASE_URL.'/admin">admin</a>';
		}
		
		if (isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) != '')){
			echo '
			<div style="padding-top:5px;">logged in as <a class="head" href="'.BASE_URL.'/user/'.$cur_user['username'].'">'.$cur_user['username'].'</a> (<a href="'.BASE_URL.'/messages" class="head">&#9993;</a>) &bull;
			<a class="head" href="'.BASE_URL.'/upload">upload</a> &bull;
			<a class="head" href="'.BASE_URL.'/logout?returnto='.$_SERVER['REQUEST_URI'].'">logout</a></div>';
		}
		else{
			echo '&bull; <a class="head" href="'.BASE_URL.'/login?returnto='.$_SERVER['REQUEST_URI'].'">login</a>';
		}
		?>
	</div>
</div>
<?php
if ($msg>0){
	echo '<div class="msgalert"><a href="'.BASE_URL.'/messages" class="msgalertlink">You have '.$msg.' new message';
	if ($msg>1){
		echo 's';
	}
	echo '.</a></div>';
}
?>