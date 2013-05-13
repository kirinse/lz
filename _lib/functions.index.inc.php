<?php
/****************************************************************************************
* LiveZilla functions.index.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function getFolderPermissions()
{
	global $CONFIG;
	$mfile = $message = "";
	
	$directories = Array(PATH_UPLOADS,PATH_IMAGES,PATH_BANNER,PATH_CONFIG,PATH_USERS,PATH_GROUPS,PATH_LOG,PATH_STATS,PATH_STATS."day/",PATH_STATS."month/",PATH_STATS."year/");
	foreach($directories as $key => $dir)
	{
		$result = testDirectory($dir);
			if(!$result)
				$message .= "Insufficient Write Access" . " (" . $dir . ")<br>";
	}
	if(!empty($message))
		$message = "<span class=\"lz_index_error_cat\">Write Access:<br></span> <span class=\"lz_index_red\">" . $message . "</span><a href=\"".CONFIG_LIVEZILLA_FAQ."en/#changepermissions\" class=\"lz_index_helplink\" target=\"_blank\">Learn how to fix this problem ..</a>";
	
	if(!file_exists(PATH_GROUPS . "groups.inc.php"))
		$mfile .= "System file missing: " . PATH_GROUPS . "groups.inc.php<br>";
	if(!file_exists(PATH_USERS . "internal.inc.php"))
		$mfile .= "System file missing: " . PATH_USERS . "internal.inc.php<br>";
	if(!empty($mfile))
		$message = "<span class=\"lz_index_error_cat\">Incomplete installation:<br></span> <span class=\"lz_index_red\">" . $mfile . "</span>";
		
	return $message;
}

function getMySQL()
{
	if(!function_exists("mysql_real_escape_string"))
		return "<span class=\"lz_index_error_cat\">MySQL:<br></span> <span class=\"lz_index_red\">MySQL or the MySQL PHP extension is missing on this server!</span>";
	else
		return null;
}

function getPhpVersion()
{
	$message = null;
	if(!checkPhpVersion(PHP_NEEDED_MAJOR,PHP_NEEDED_MINOR,PHP_NEEDED_BUILD))
		$message = "<span class=\"lz_index_error_cat\">PHP-Version:<br></span> <span class=\"lz_index_red\">" . str_replace("<!--version-->",PHP_NEEDED_MAJOR . "." . PHP_NEEDED_MINOR . "." . PHP_NEEDED_BUILD,"LiveZilla requires <!--version--> or greater.<br>Installed version is " . @phpversion()) . ".</span>";
	return $message;
}
?>
