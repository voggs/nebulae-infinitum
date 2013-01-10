<?php
//Hash function for passwords
function nebulae_hash($input){
	return hash("sha256",sha1(md5($input)).md5(sha1($input)));
}

function get_id_from_username($username){
	//Get user info from database
	$result = mysql_query("SELECT * FROM users WHERE username = '$username'") or die(mysql_error());
	if (!$result) {
		return "";
	}
	$user = mysql_fetch_array($result);
	return $user[0];
}

function get_username_from_id($id){
	//Get user info from database
	$result = mysql_query("SELECT * FROM users WHERE id = '$id'") or die(mysql_error());
	if (!$result) {
		return "mysqlError";
	}
	$user = mysql_fetch_array($result);
	//If user ID is not a valid user, die
	if (!$user){
		return "invalidUser";
	}
	return $user[1];
}

function get_creation_from_comment($cid){
	$result = mysql_query("SELECT creationid FROM comments WHERE id = '$cid'") or die(mysql_error());
	if (!$result) {
		return "";
	}
	$creation=mysql_fetch_array($result);
	return $creation[0];
}

function bbcode_parse($text,$writing=false){
	$bbcode = new BBCode;
	$bbcode->RemoveRule('acronym');
	$bbcode->RemoveRule('size');
	$bbcode->RemoveRule('font');
	$bbcode->RemoveRule('wiki');
	$bbcode->RemoveRule('img');
	$bbcode->RemoveRule('rule');
	$bbcode->RemoveRule('br');
	$bbcode->RemoveRule('center');
	$bbcode->RemoveRule('left');
	$bbcode->RemoveRule('right');
	$bbcode->RemoveRule('indent');
	$bbcode->RemoveRule('columns');
	$bbcode->RemoveRule('nextcol');
	$bbcode->RemoveRule('code');
	$bbcode->RemoveRule('list');
	$bbcode->RemoveRule('*');
	if($writing)$bbcode->SetAllowAmpersand(true);
	return $bbcode->Parse($text);
}
function bbcode_parse_description($text){
	$bbcode = new BBCode;
	$bbcode->RemoveRule('acronym');
	$bbcode->RemoveRule('color');
	$bbcode->RemoveRule('size');
	$bbcode->RemoveRule('quote');
	$bbcode->RemoveRule('font');
	$bbcode->RemoveRule('wiki');
	$bbcode->RemoveRule('img');
	$bbcode->RemoveRule('rule');
	$bbcode->RemoveRule('br');
	$bbcode->RemoveRule('center');
	$bbcode->RemoveRule('left');
	$bbcode->RemoveRule('right');
	$bbcode->RemoveRule('indent');
	$bbcode->RemoveRule('columns');
	$bbcode->RemoveRule('nextcol');
	$bbcode->RemoveRule('code');
	$bbcode->RemoveRule('list');
	$bbcode->RemoveRule('*');
	if($writing)$bbcode->SetAllowAmpersand(true);
	return $bbcode->Parse($text);
}
function strip_bbcode($text){
	$bbcode = new BBCode;
	$bbcode->SetPlainMode(true);
	$output=$bbcode->Parse($text);
	return $bbcode->UnHTMLEncode(strip_tags($output));
}
?>