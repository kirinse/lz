<?php
/****************************************************************************************
* LiveZilla picture.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
header("Content-Type: image/jpg;");
if(isset($_GET["intid"]) && setDataProvider())
{
	getData(true,false,false,false);
	$id = getInternalSystemIdByUserId(base64UrlDecode($_GET["intid"]));
	if(isset($INTERNAL[$id]))
	{
		if($INTERNAL[$id]->LoadPictures())
		{
			if(!empty($INTERNAL[$id]->WebcamPicture))
				exit(base64_decode($INTERNAL[$id]->WebcamPicture));
			else
				exit(base64_decode($INTERNAL[$id]->ProfilePicture));
		}
	}
}
exit(getFile("./images/nopic.jpg"));
?>
