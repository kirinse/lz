<?php
/****************************************************************************************
* LiveZilla api.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);
if(!defined("LIVEZILLA_PATH"))
	exit("Error: 'LIVEZILLA_PATH' is not defined. Please define the constant 'LIVEZILLA_PATH'.");

class LiveZillaAPI
{
	function LiveZillaAPI()
	{
		global $CONFIG;
		require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
		require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
		require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
		require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");

		if(!setDataProvider())
			exit("Error: Database connection failed.");
		@register_shutdown_function('unloadDataProvider');
	}
	
	function IsOperatorAvailable()
	{
		return (operatorsAvailable() > 0);
	}
	
	function IsDeactivated()
	{
		return !getAvailability();
	}
	
	/** DEPRECATED **/
	function GetOperatorList()
	{
		return getOperatorList();
	}
	
	function GetOperators()
	{
		return getOperators();
	}
	
	function Base64UrlEncode($_value)
	{
		return base64UrlEncode($_value);
	}
}
?>