<?php
///Get creation ID from URL
//If creation ID not found or is NaN, die
if ( isset( $_GET["id"] ) ) {
 $creationid = htmlspecialchars( $_GET["id"] );
}
else die( "" );
if ( !$creationid || strcspn( $creationid, "0123456789" ) > 0 ){
	die( "" );
}

//Get creation info from database
$result = $mysqli->query( "SELECT * FROM creations WHERE id = $creationid" );
if ( !$result ) {
    die( "" );
}
$creation = $result->fetch_array;

//If creation ID is not a valid creation, die
if ( !$creation ){
	die( "" );
}

//Get the amount of views of the creation from the database & output it
$views = $mysqli->query( "SELECT * FROM views WHERE creationid=$creationid" )->num_rows;
echo $views;
?>