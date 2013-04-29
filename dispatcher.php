<?php
/*
Nebulae Infinitum dispatcher file
Used to display pages; all URLs redirect here to be processed based on the URL entered
*/

require_once( "config/config.php" );
error_reporting( E_ALL ^ E_NOTICE ); 
session_start();

//Connect to database
$mysqli = new mysqli( MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE );
if ( $mysqli->connect_errno ) {
	die( "Could not connect to database: " . $mysqli->connect_error );
}

//Get current user info from database
if ( !empty( $_SESSION['SESS_MEMBER_ID'] ) ){
	$cur_user_query = $mysqli->query( "SELECT * FROM users WHERE id=" . $_SESSION['SESS_MEMBER_ID'] );
	if ( $mysqli->errno ) {
		die( "Could not read user data from database: " . $mysqli->error );
	}
	$cur_user = $cur_user_query->fetch_array();
	unset($cur_user_query);
}

if ($cur_user['banstatus'] == "banned") {
	include_once("errors/ban.php");
	exit();
}

else if ($cur_user['banstatus'] == "deleted") {
	include_once("errors/delete.php");
	exit();
}

//Get URL
$url = str_replace( "/" . BASE_FOLDER, "", $_SERVER['REQUEST_URI'] );
$url_array = explode( "/", $url );

//Determine which page to load based on the URL
if ( $url != "/"){
	switch( $url_array[1] ){
		case "user":
		
		break;
		
		case "creation":
		
		break;
		
		case "creations":
		
		break;
		
		case "comment":
		
		break;
		
		case "admin":
		
		break;
		
		case "message":
		
		break;
		
		case "messages":
		
		break;
		
		case "tools":
			if( isset( $url_array[2] ) ){
				switch( $url_array[2] ){
					case "api":
						if( file_exists( "api/" . $url_array[3] . ".php" ) ){
							require_once( "api/" . $url_array[3] . ".php" );
						}
						else {
							require_once( "errors/404.php" );
						}
					break;
					
					case "encrypt":
						require_once( "encrypt.php" );
					break;
					
					default:
						require_once( "errors/404.php" );
				}
			}
			else{
				require_once( "about/tools.php" );
			}
		break;
		
		case "upload":
		
		break;
		
		case "about":
		
		break;
		
		case "login":
			require_once( "login.php" );
		break;
		
		case "logout":
		
		break;
		
		case "register":
		
		break;
		
		case "include":
			if( isset( $url_array[2] ) ){
				switch( $url_array[2] ){
					case "style.css":
						require_once( "templates/style.php" );
					break;
					
					default:
						require_once( "errors/403.php" );
				}
			}
			else{
				require_once( "errors/403.php" );
			}
		break;
		
		case "data":
			if( isset( $url_array[2] ) ){
				if( $url_array[2] == "errors" ){
					$extension = explode( ".", addslashes( $url_array[3] ) );
					if( file_exists( "errors/" . addslashes( $url_array[3] ) ) && $extension[1] == "png" ){
						header( "Content-type: image/png" );
						echo file_get_contents( "errors/" .  $url_array[3] );
					}
				}
				else{
					$data_path = "";
					for ( $i = 2; $i < count( $url_array ); $i++ ){
						$data_path .= "/".$url_array[$i];
						
					}
					//echo $data_path;
					$extension = explode( ".", addslashes( $data_path ) );
					if( file_exists( "data" . addslashes( $data_path ) ) ){
						$go = true;
						switch( $extension[1] ){
							case "png":
								header( "Content-type: image/gif" );
							break;
							
							case "gif":
								header( "Content-type: image/gif" );
							break;
							
							case "jpg":
								header( "Content-type: image/jpeg" );
							break;
							
							case "swf":
								header( "Content-type: application/x-shockwave-flash" );
							break;
							
							case "zip":
							case "sb":
							case "sb2":
								header( "Content-type: application/octet-stream" );
							break;
							
							case "mp3":
								header( "Content-type: audio/mpeg" );
							break;
							
							case "txt":
								header( "Content-type: text/plain" );
							break;
							
							case "ttf":
								header( "Content-type: application/x-font-ttf" );
							break;
							
							case "js":
								header( "Content-type: application/javascript" );
							break;
							
							default:
								$go = false;
						}
						if ( $go == true ){
							echo file_get_contents( "data" .  $data_path );
						}
					}
					
					else{
						require_once( "errors/404.php" );
					}
				}
			}
			else{
				require_once( "errors/404.php" );
			}
		break;
		
		default:
			require_once( "errors/404.php" );
	}
}
else {
	require_once( "index.php" );
}

?>