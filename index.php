<!DOCTYPE html>
<html>
	<head>
		<title>
			<?php echo SITE_NAME ?>
		
		</title>
		<link rel="stylesheet" type="text/css" href="include/style.css" media="screen" />
	</head>
	<body style="background-color:black;">
		<a style="background-image:url('<?php echo substr( HEADER_IMG, 3 ); ?>');display:block;width:214px;height:125px;position:absolute;top:25%;left:50%;margin-left:-107px;" href="login">
			<div style="color:white;font-size:20px;font-family:'Courier New',Courier,monospaced;text-decoration:none;position:absolute;bottom:0;">
				<?php echo strtolower( SITE_NAME ); ?>
			
			</div>
			<div style ="color:white;font-size:12px;font-family:'Courier New',Courier,monospaced;text-decoration:none;position:absolute;bottom:-20px;right:0px;">
			alpha (<?php echo VERSION_NUMBER ?>)
			</div>
		</a>
	</body>
</html>