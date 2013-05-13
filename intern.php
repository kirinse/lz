<?php
/****************************************************************************************
* LiveZilla intern.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

define("LOGIN",($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_LOGIN));
define("LOGOFF",(isset($_POST[POST_INTERN_USER_STATUS]) && $_POST[POST_INTERN_USER_STATUS] == USER_STATUS_OFFLINE));
define("SERVERSETUP",(isset($_POST[POST_INTERN_ADMINISTRATE]) || $_POST[POST_INTERN_SERVER_ACTION] == INTERN_ACTION_GET_BANNER_LIST || $_POST[POST_INTERN_SERVER_ACTION] == INTERN_ACTION_DOWNLOAD_TRANSLATION));
define("DB_ACCESS_REQUIRED",(DB_CONNECTION && isset($_POST[POST_INTERN_GET_MANAGEMENT]) && !empty($_POST[POST_INTERN_GET_MANAGEMENT])));
define("NO_CLIPPING",(LOGIN || (isset($_POST[POST_INTERN_XMLCLIP_HASH_TRACKING]) && $_POST[POST_INTERN_XMLCLIP_HASH_TRACKING] == XML_CLIP_NULL)));

getData(true,true,DB_ACCESS_REQUIRED,true,DB_ACCESS_REQUIRED);
require(LIVEZILLA_PATH . "_lib/functions.internal.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.internal.inc.php");
validate();

if(defined("VALIDATED"))
{
	if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_LISTEN || $_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_LOGIN)
	{
		listenXML();
		if(STATS_ACTIVE && !LOGIN)
			$STATS->ProcessAction(ST_ACTION_LOG_STATUS,array($INTERNAL[CALLER_SYSTEM_ID]));
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_INIT_UPLOAD)
		initUpload();
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SEND_FILE)
		receiveFile();
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_REMOVE_FILE)
		removeFile();
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SET_IDLE)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		setIdle($_POST[POST_INTERN_SERVER_IDLE]);
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SEND_RESOURCES)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.process.inc.php");
		processUpdateReport();
		processArchiveChats();
		processResources();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_REPORTS)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.process.inc.php");
		require(LIVEZILLA_PATH . "_lib/functions.internal.build.inc.php");
		processUpdateReport();
		buildReports();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_DATABASE_TEST)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		dataBaseTest();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SEND_TEST_MAIL)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		sendTestMail();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_CREATE_TABLES)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		createTables();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SET_MANAGEMENT)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		setManagement();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SET_CONFIG)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		setConfig();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_SET_AVAILABILITY)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		setAvailability($_POST[POST_INTERN_SERVER_AVAILABILITY]);
	}
	else
	{
		file_put_contents('./_log/a.txt', var_export($_POST, true)."\n", FILE_APPEND);
	}
}
else
{
	if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_GET_BANNER_LIST)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		getBannerList();
	}
	else if($_POST[POST_INTERN_SERVER_ACTION]==INTERN_ACTION_DOWNLOAD_TRANSLATION)
	{
		require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
		getTranslationData();
	}
	else
		$RESPONSE->SetValidationError(AUTH_RESULT);
}

if(defined("VALIDATED") && LOGOFF)
{
	$INTERNAL[CALLER_SYSTEM_ID]->GetExternalObjects();
	foreach($INTERNAL[CALLER_SYSTEM_ID]->ExternalChats as $chat)
		$chat->InternalClose();
}

if(defined("VALIDATED") && !SERVERSETUP)
{
	if(isset($_POST[POST_GLOBAL_TYPING]))
		$INTERNAL[CALLER_SYSTEM_ID]->Typing = $_POST[POST_GLOBAL_TYPING];
	$INTERNAL[CALLER_SYSTEM_ID]->Save();
}

if(LOGIN && DB_ACCESS_REQUIRED)
{
	require(LIVEZILLA_PATH . "_lib/functions.internal.man.inc.php");
	$res = testDataBase($CONFIG["gl_db_host"],$CONFIG["gl_db_user"],$CONFIG["gl_db_pass"],$CONFIG["gl_db_name"],$CONFIG["gl_db_prefix"]);
	if(!empty($res))
		$RESPONSE->SetValidationError(LOGIN_REPLY_DB,$res);
}

$RESPONSE->GlobalHash = (empty($RESPONSE->Messages) && empty($RESPONSE->Ratings) && empty($RESPONSE->Resources) && empty($RESPONSE->Archive)) ? substr(md5($RESPONSE->XML),0,5) : "";
$RESPONSE->XML = (($_POST[POST_INTERN_SERVER_ACTION] != INTERN_ACTION_LISTEN || (isset($_POST[POST_GLOBAL_XMLCLIP_HASH_ALL]) && $_POST[POST_GLOBAL_XMLCLIP_HASH_ALL] != $RESPONSE->GlobalHash)) ? str_replace("<!--gl_all-->",base64_encode(substr(md5($RESPONSE->XML),0,5)),$RESPONSE->XML) : "" );
$response = (strlen($RESPONSE->XML) > 0) ? $RESPONSE->GetXML() : "";
$response = str_replace("<!--execution_time-->",base64_encode(floor(((microtimeFloat(microtime())-microtimeFloat(ACCESSTIME))*1000))),$response);
?>
