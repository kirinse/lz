<?php
/****************************************************************************************
* LiveZilla visitcard.php
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

if(isset($_GET["intid"]) && setDataProvider())
{
	getData(true,false,false,false);
	$id = getInternalSystemIdByUserId($_GET["intid"]);
	if(isset($INTERNAL[$id]))
	{
		$INTERNAL[$id]->LoadProfile();
		$INTERNAL[$id]->LoadPictures();
		if(!empty($INTERNAL[$id]->Profile))
		{
			header("Content-Type: application/vcard;");
			header("Content-Disposition: attachment; filename=" . utf8_decode($_GET["intid"]) . ".vcf");
			$vcard = getFile("./templates/vcard.tpl");
			$vcard = str_replace("<!--Name-->",qp_encode($INTERNAL[$id]->Profile->Name),$vcard);
			$vcard = str_replace("<!--Firstname-->",qp_encode($INTERNAL[$id]->Profile->Firstname),$vcard);
			$vcard = str_replace("<!--Company-->",qp_encode($INTERNAL[$id]->Profile->Company),$vcard);
			$vcard = str_replace("<!--Comments-->",qp_encode($INTERNAL[$id]->Profile->Comments),$vcard);
			$vcard = str_replace("<!--Phone-->",qp_encode($INTERNAL[$id]->Profile->Phone),$vcard);
			$vcard = str_replace("<!--Fax-->",qp_encode($INTERNAL[$id]->Profile->Fax),$vcard);
			$vcard = str_replace("<!--Street-->",qp_encode($INTERNAL[$id]->Profile->Street),$vcard);
			$vcard = str_replace("<!--City-->",qp_encode($INTERNAL[$id]->Profile->City),$vcard);
			$vcard = str_replace("<!--ZIP-->",qp_encode($INTERNAL[$id]->Profile->ZIP),$vcard);
			$vcard = str_replace("<!--Country-->",qp_encode($INTERNAL[$id]->Profile->Country),$vcard);
			$vcard = str_replace("<!--URL-->",qp_encode("http://" . $CONFIG["gl_host"] . str_replace("visitcard.php",FILE_CHAT . "?intid=".base64UrlEncode($_GET["intid"]),$_SERVER["PHP_SELF"])),$vcard);
			$vcard = str_replace("<!--Languages-->",qp_encode($INTERNAL[$id]->Profile->Languages),$vcard);
			$vcard = str_replace("<!--Email-->",$INTERNAL[$id]->Profile->Email,$vcard);
			$vcard = str_replace("<!--Gender-->",qp_encode($INTERNAL[$id]->Profile->Gender),$vcard);
			$vcard = str_replace("<!--Picture-->",(!empty($INTERNAL[$id]->ProfilePicture)) ? "\r\nPHOTO;TYPE=JPEG;ENCODING=BASE64:\r\n" . $INTERNAL[$id]->ProfilePicture : "",$vcard);
			exit($vcard);
		}
	}
}
header("HTTP/1.0 404 Not Found");

function qp_encode($string) 
{
	$string = str_replace(array('%20', '%0D%0A', '%'), array(' ', "\r\n", '='), rawurlencode($string));
	$string = preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n", $string);
	return $string;
}
?>
