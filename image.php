<?php

/****************************************************************************************
* LiveZilla image.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. 

* 
***************************************************************************************/ 

define("IN_LIVEZILLA",true);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
@set_time_limit(30);

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");

@set_error_handler("handleError");
@error_reporting(E_ALL);

header("Connection: close");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");

setDataProvider();
$parameters = getTargetParameters();

$html = "";
if(!empty($_GET["id"]) && is_numeric($_GET["id"]))
{
	$prefix = ((!empty($_GET["type"]) && $_GET["type"] == "overlay") ? "overlay" : "inlay");
	if(operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
		exit(file_get_contents(getFileById($_GET["id"],true,$prefix)));
	else
		exit(file_get_contents(getFileById($_GET["id"],false,$prefix)));
}
else if(!empty($_GET["tl"]))
{
	$html = base64UrlDecode($_GET["tl"]);
	if(!empty($_GET["tlont"]) && operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
	{
		if(!empty($_GET["tlonc"]))
			$html = str_replace("<!--class-->","class=\\\"".htmlentities(base64UrlDecode($_GET["tlonc"]),ENT_QUOTES,"UTF-8")."\\\"",$html);
		else
			$html = str_replace("<!--class-->","",$html);
		$html = str_replace("<!--text-->",htmlentities(base64UrlDecode($_GET["tlont"]),ENT_QUOTES,"UTF-8"),$html);
	}
	else if(!empty($_GET["tloft"]) && empty($_GET["tloo"]))
	{
		if(!empty($_GET["tlofc"]))
			$html = str_replace("<!--class-->","class=\\\"".htmlentities(base64UrlDecode($_GET["tlofc"]),ENT_QUOTES,"UTF-8")."\\\"",$html);
		else
			$html = str_replace("<!--class-->","",$html);
		$html = str_replace("<!--text-->",htmlentities(base64UrlDecode($_GET["tloft"]),ENT_QUOTES,"UTF-8"),$html);
	}
	else
		$html = "";
	if(!empty($html))
		exit("document.write(\"".$html."\");");
}
else if(!empty($_GET["v"]))
{
	$parts = explode("<!>",base64UrlDecode(str_replace(" ","+",$_GET["v"])));
	if(count($parts) > 3 && strlen($parts[3]) > 0)
		$parts[0] = str_replace("<!--class-->","class=\\\"".$parts[3]."\\\"",$parts[0]);
	else if(count($parts) > 0)
		$parts[0] = str_replace("<!--class-->","",$parts[0]);
		
	if(count($parts) > 1 && operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
		$html = str_replace("<!--text-->",$parts[1],$parts[0]);
	else if(count($parts) > 2)
		$html = str_replace("<!--text-->",$parts[2],$parts[0]);
	exit("document.write(\"".$html."\");");
}

function getFileById($_id,$_online,$_prefix)
{
	$int = ($_online) ? "1" : "0";
	if(($_online && @file_exists("./banner/".$_prefix."_".$_id."_1.gif")) || (!$_online && @file_exists("./banner/".$_prefix."_".$_id."_0.gif")))
	{
		header("Content-Type: image/gif;");
		return "./banner/".$_prefix."_".$_id."_".$int.".gif";
	}
	else if(($_online && @file_exists("./banner/".$_prefix."_".$_id."_1.png")) || (!$_online && @file_exists("./banner/".$_prefix."_".$_id."_0.png")))
	{
		header("Content-Type: image/png;");
		return "./banner/".$_prefix."_".$_id."_".$int.".png";
	}
	else if($_prefix == "inlay")
		return getFileById($_id,$_online,"livezilla");
	else
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
}
unloadDataProvider();
?>