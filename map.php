<?php
/****************************************************************************************
* LiveZilla map.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);
define("LIVEZILLA_PATH","./");

require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");

$map = getFile(TEMPLATE_HTML_MAP);
if(isset($_GET["lat"]))
	$map = str_replace("<!--dlat-->",floatval($_GET["lat"]),$map);
else
	$map = str_replace("<!--dlat-->","25",$map);

if(isset($_GET["lng"]))
	$map = str_replace("<!--dlng-->",floatval($_GET["lng"]),$map);
else
	$map = str_replace("<!--dlng-->","10",$map);

if(isset($_GET["zom"]))
	$map = str_replace("<!--dzom-->",floatval($_GET["zom"]),$map);
else
	$map = str_replace("<!--dzom-->","1",$map);
	
$map = str_replace("<!--key-->",$CONFIG["gl_api_key"],$map);
exit($map);
?>