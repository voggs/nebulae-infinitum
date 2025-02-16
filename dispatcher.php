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
if ( !empty( $_SESSION['SESS_MEMBER_ID'] ) ) {
	$cur_user_query = $mysqli->query( "SELECT * FROM users WHERE id=" . $_SESSION['SESS_MEMBER_ID'] );
	if ( $mysqli->errno ) {
		die( "Could not read user data from database: " . $mysqli->error );
	}
	$cur_user = $cur_user_query->fetch_array();
	unset( $cur_user_query );
}

//Get URL and explode into pieces for determination of desired page
if ( BASE_FOLDER != "" ){
	$url = str_replace( "/" . BASE_FOLDER, "", $_SERVER['REQUEST_URI'] ); //use if there's a base folder
}
else {
	$url = $_SERVER['REQUEST_URI']; // use if the base is root
}

$url_array = explode( "/", $url );

if ( $cur_user['banstatus'] == "banned" && $url != "/include/style.css" && $url != "/data/errors/ban.png" && $url != "/data/fonts/kabel_bold.ttf" && $url != "/data/header.png" ) {
	include_once( "errors/ban.php" );
	exit();
}

else if ( $cur_user['banstatus'] == "deleted"  && $url != "/include/style.css" && $url != "/data/errors/delete.png" && $url != "/data/fonts/kabel_bold.ttf" && $url != "/data/header.png" ) {
	include_once( "errors/delete.php" );
	exit();
}

//Determine which page to load based on the URL
if ( $url != "/") {
	switch( $url_array[1] ) {
		// If the URL starts with user (e.g. example.com/user/username), do this
		case "user":
			// Escape the URL for added security
			$escaped_url_chunk[0] = addslashes( $url_array[2] );
			// If there's something after the user part of the URL (e.g. username in the URL above)
			if ( isset( $escaped_url_chunk[0] ) && $escaped_url_chunk[0] != "") {
				// Set the ID of the user the page should display information from based on the username in the URL
				$_GET['id'] = get_id_from_username( $escaped_url_chunk[0], $mysqli );
				// Escape the URL for added security
				$escaped_url_chunk[1] = addslashes( $url_array[3] );
				// If there's something after the username part (e.g. example.com/user/username/action)...
				if ( isset( $escaped_url_chunk[1] ) && $escaped_url_chunk[1] != "") {
					switch ( $url_array[3] ) {
						// If the URL is of the format (e.g. example.com/user/username/preferences), display their preferences page rather than the userpage
						// This is done to keep URLs organised & structured
						case "preferences":
							require_once( "pref.php" );
						break;
						
						// If there's something besides "preferences" there (or other actions I may add), spit out a 404 error
						default:
							require_once( "errors/404.php" );
					}
				}
				// Otherwise, if the URL is simply like example.com/user/username...
				else {
					// If the page ends in a / (e.g. example.com/user/username/), redirect them to the page w/out the slash so it doesn't mess up the CSS
					if ( substr( $_SERVER['REQUEST_URI'], strlen( $escaped_url_chunk[1] ) - 1, 1 ) == "/") {
						header( "Location: ../" . $escaped_url_chunk[0] );
					}
					// Otherwise, just output the regular userpage
					else {
						require_once( "user.php" );
					}
				}
			}
			// If there's nothing after the user part (e.g. example.com/user/), give a 404 error
			// There may in the future be a userlist here
			else {
				require_once( "errors/404.php" );
			}
		break;
		
		case "creation":
			if ( isset( $url_array[2] ) && $url_array[2] != "" ) {
				$_GET['id'] = $url_array[2];
				if ( isset ( $url_array[3] ) && $url_array[3] != "" ) {
					switch ( $url_array[3] ) {
						case "versions":
							$_GET['mode'] = "version";
						case "edit":
							require_once( "edit.php" );
						break;
						
						case "version":
							if ( isset( $url_array[5] ) && $url_array[5] != "" ) {
								$_GET['mode'] = "version";
								switch ( $url_array[5] ) {
									case "revert":
									case "delete":
										$_GET['action'] = $url_array[5];
										$_GET['aid'] = $url_array[4];
										require_once( "edit.php" );
									break;
									
									default:
										require_once( "errors/404.php" );
								}
							}
							else{
								require_once( "errors/404.php" );
							}
						break;
						
						case "license":
							require_once( "license.php" );
						break;
						
						case "flag":
							require_once( "flag.php" );
						break;
						
						case "favourite":
							$_GET['action'] = "favourite";
							require_once( "creation.php" );
						break;
						
						case "rate":
							if ( isset( $url_array[4] ) && $url_array[4] > 0 && $url_array[4] < 6 ) {
								$_GET['action'] = "rate";
								$_GET['rating'] = $url_array[4];
								require_once( "creation.php" );
							}
							else{
								require_once( "errors/404.php" );
							}
						break;
						
						case "viewer":
							if ( isset( $url_array[4] ) && $url_array[4] == "play" ) {
								$_GET['flash'] = "play";
							}
							require_once( "viewer.php" );
						break;
						
						case "player":
							$_GET['action'] = "player";
							require_once( "creation.php" );
						break;
						
						default:
							require_once( "errors/404.php" );
					}
				}
				else {
					if ( substr( $_SERVER['REQUEST_URI'], strlen( $_SERVER['REQUEST_URI'] ) - 1, 1 ) == "/") {
						header( "Location: " . BASE_URL . "/creation/" . $url_array[2] );
					}
					else {
						require_once( "creation.php" );
					}
				}
			}
			else{
				header( "Location: " . BASE_URL . "/creations/newest/1");
			}
		break;
		
		case "creations":
			if ( isset( $url_array[2] ) && $url_array[2] != "" ) {
				if ( !isset( $url_array[3] ) || $url_array[3] == "" ) {
					$url_array[3] = 1;
				}
				if ( isset ( $url_array[4] ) ) {
					$_GET['id'] = $url_array[3];
					$_GET['action'] = $url_array[4];
					$_GET['mode'] = "action";
				}
				else {
					$_GET['page'] = $url_array[3];
					$_GET['mode'] = $url_array[2];
				}
				require_once( "creations.php" );
			}
			else{
				header( "Location: " . BASE_URL . "/creations/newest/1");
			}
		break;
		
		case "comment":
			if ( isset( $url_array[2] ) && $url_array[2] != "" ){
				if ( isset( $url_array[3] ) ){
					switch( $url_array[3] ){
						case "flag":
							$_GET['id'] = $url_array[2];
							require_once( "flag.php" );
						break;
					}
				}
				else{
					$comment = $mysqli->query("SELECT * FROM comments WHERE id = " . addslashes($url_array[2]) )->fetch_array();
					if ( isset( $comment ) ) {
						if ( $comment['status'] == "censored" ) {
							if ( $cur_user['rank'] == "admin" || $cur_user['rank'] == "mod") {
								header( "Location: creation/" . $comment['creationid'] . "#". $comment['id'] );
							}
							else {
								require_once( "errors/403.php" );
							}
						}
						else if ( $comment['status'] == "shown" || $comment['status'] == "approved" ) {
							header( "Location: ../creation/" . $comment['creationid'] . "#". $comment['id'] );
						}
						else {
							require_once( "errors/404.php" );
						}
					}
					else {
						require_once( "errors/404.php" );
					}
				}
			}
			else{
				require_once( "errors/404.php" );
			}
		break;
		
		case "admin":
			if ( isset( $url_array[2] ) ) {
				switch ( $url_array[2] ) {
					case "flags":
						$_GET['mode'] = "flags";
						if ( isset( $url_array[4] ) ) {
							switch ( $url_array[4] ) {
								case "delete":
									$_GET['action'] = "delete";
									$_GET['id'] = addslashes($url_array[3]);
									require_once( "admin.php");
								
								default:
									require_once( "admin.php");
							}
						}
						else {
							require_once( "admin.php");
						}
					break;
					
					default:
						header( "Location: ../admin" );
				}
			}
			else {
				require_once( "admin.php" );
			}
		
		break;
		
		case "message":
			if ( isset( $url_array[3] ) ) {
				switch ( $url_array[3] ) {
					case "delete":
						if ( isset( $url_array[2] ) ) {
							$_GET['id'] = $url_array[2];
							$_GET['action'] = "delete";
							require_once( "messages.php" );
						}
						else {
							require_once( "errors/404.php" );
						}
					break;
					
					case "flag":
						if ( isset( $url_array[2] ) ) {
							$_GET['id'] = $url_array[2];
							$_GET['type'] = "message";
							require_once( "flag.php" );
						}
						else {
							require_once( "errors/404.php" );
						}
					break;
					
					default:
						require_once( "errors/404.php" );
				}
			}
			else {
				require_once( "errors/404.php");
			}
		break;
		
		case "messages":
			if ( substr( $_SERVER['REQUEST_URI'], strlen( $_SERVER['REQUEST_URI'] ) - 1, 1 ) == "/" ) {
				if ( BASE_FOLDER != "") {
					header( "Location: ../" . str_replace( "/" . BASE_FOLDER . "/", "", substr( $_SERVER['REQUEST_URI'], 0, strlen( $_SERVER['REQUEST_URI'] ) - 1 ) ) );
				}
				else {
					header( "Location: /" . substr( $_SERVER['REQUEST_URI'], 0, strlen( $_SERVER['REQUEST_URI'] ) - 1 ) );
				}
			}
			else {
				if ( isset( $url_array[2] ) ) {
					$visitinguser = get_username_from_id( $url_array[2], $mysqli );
				}
				require_once( "messages.php" );
			}
		break;
		
		case "tools":
			if ( isset( $url_array[2] ) ) {
				switch ( $url_array[2] ) {
					case "api":
						if (  file_exists( "api/" . $url_array[3] . ".php" ) ) {
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
			require_once( "upload.php" );
		break;
		
		case "about":
			if ( isset ( $url_array[2] ) && $url_array[2] != "" ) {
				if ( file_exists( "info/" . addslashes( $url_array[2] ) . ".php" ) ) {
					require_once( "info/" . addslashes( $url_array[2] ) . ".php" );
				}
			}
			else {
				require_once( "info/index.php" );
			}
		break;
		
		case "login":
			require_once( "login.php" );
		break;
		
		case "login?returnto=":
			$return_to = "";
			for ( $i = 2; $i < count( $url_array ); $i++ ) {
				$return_to .= "/".$url_array[$i];
			}
			require_once( "login.php" );
		break;
		
		case "logout":
			$_GET['action'] = "logout";
			require_once( "login.php" );
		break;
		
		case "logout?returnto=":
			$return_to = "";
			for ( $i = 2; $i < count( $url_array ); $i++ ) {
				$return_to .= "/".$url_array[$i];
			}
			$_GET['action'] = "logout";
			require_once( "login.php" );
		break;
		
		case "register":
			$_GET['action'] = "register";
			require_once( "login.php" );
		break;
		
		case "register?returnto=":
			$return_to = "";
			for ( $i = 2; $i < count( $url_array ); $i++ ) {
				$return_to .= "/".$url_array[$i];
			}
			$_GET['returnto'] = "/" . BASE_FOLDER . $return_to;
			$_GET['action'] = "register";
			require_once( "login.php" );
		break;
		
		case "include":
			if ( isset( $url_array[2] ) ) {
				switch ( $url_array[2] ) {
					case "style.css":
						require_once( "templates/style.php" );
					break;
					
					case "creation.js":
						header( "Content-Type: text/plain" );
						echo file_get_contents( "templates/creation.js" );
					break;
					
					default:
						require_once( "errors/403.php" );
				}
			}
			else {
				require_once( "errors/403.php" );
			}
		break;
		
		case "data":
			if ( isset( $url_array[2] ) ) {
				if ( $url_array[2] == "errors" ) {
					$extension = explode( ".", addslashes( $url_array[3] ) );
					if ( file_exists( "errors/" . addslashes( $url_array[3] ) ) && $extension[1] == "png" ) {
						header( "Content-type: image/png" );
						echo file_get_contents( "errors/" .  $url_array[3] );
					}
				}
				else {
					$data_path = "";
					for ( $i = 2; $i < count( $url_array ); $i++ ) {
						$data_path .= "/".$url_array[$i];
					}
					$extension = explode( ".", addslashes( $data_path ) );
					if ( file_exists( "data" . addslashes( $data_path ) ) ) {
						$go = true;
						switch ( $extension[1] ) {
							case "png":
								header( "Content-type: image/gif" );
							break;
							
							case "gif":
								header( "Content-type: image/gif" );
							break;
							
							case "svg":
								header( "Content-type: image/svg+xml" );
							break;
							
							case "jpg":
							case "jpeg":
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
					else {
						require_once( "errors/404.php" );
					}
				}
			}
			else {
				require_once( "errors/404.php" );
			}
		break;
		
		case "robots.txt":
			header( "Content-Type: text/plain" );
			echo file_get_contents( "data/robots.txt" );
		break;
		
		case "favicon.ico":
			header( "Content-Type: image/png" );
			echo file_get_contents( "data/favicon.ico" );
		break;
		
		default:
			require_once( "errors/404.php" );
	}
}
else {
	require_once( "index.php" );
}

?>