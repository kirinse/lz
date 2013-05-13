<?php
/****************************************************************************************
* LiveZilla functions.internal.man.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function setAvailability($_available)
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		if($_available=="1" && file_exists(FILE_SERVER_DISABLED))
			@unlink(FILE_SERVER_DISABLED);
		else if($_available=="0")
			createFile(FILE_SERVER_DISABLED,time(),true);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function setIdle($_idle)
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		if($_idle=="0" && file_exists(FILE_SERVER_IDLE))
			@unlink(FILE_SERVER_IDLE);
		else if($_idle=="1")
			createFile(FILE_SERVER_IDLE,time(),true);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function getBannerList($list = "")
{
	global $VISITOR,$CONFIG,$RESPONSE;
	$banners = getDirectory(PATH_BANNER,".php",true);
	sort($banners);
	foreach($banners as $banner)
	{
		if(@is_dir(PATH_BANNER . $banner) || ((strpos($banner,"_0.png") === false && strpos($banner,"_1.png") === false) && (strpos($banner,"_0.gif") === false && strpos($banner,"_1.gif") === false)))
			continue;
		$list .= "<banner name=\"".base64_encode($banner)."\" hash=\"".base64_encode(hashFile(PATH_BANNER . $banner))."\"/>\r\n";
	}
	$RESPONSE->SetStandardResponse(1,"<banner_list>".$list."</banner_list>");
}

function getTranslationData($translation = "")
{
	global $LZLANG,$RESPONSE;
	if(!(isset($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO]) && (strlen($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO])==2||strlen($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO])==5)))
	{
		$RESPONSE->SetStandardResponse(1,"");
		return;
	}
	include("./_language/lang" . strtolower($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO]) . ".php");
	$translation .= "<language key=\"".base64_encode($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO])."\">\r\n";
	foreach($LZLANG as $key => $value)
		$translation .= "<val key=\"".base64_encode($key)."\">".base64_encode($value)."</val>\r\n";
	$translation .= "</language>\r\n";
	$RESPONSE->SetStandardResponse(1,$translation);
}

function updatePredefinedMessages($_counter = 0)
{
	global $GROUPS,$INTERNAL;
	clearPredefinedMessages();

	$tpm_types = array("g"=>$GROUPS,"u"=>$INTERNAL);
	$pms = array();
	foreach($tpm_types as $type => $objectlist)
		foreach($objectlist as $id => $object)
		{
			$pms[$type.$id] = array();
			foreach($_POST as $key => $value)
				if(strpos($key,"p_db_pm_".$type."_" . $id . "_")===0)
				{
					$parts = explode("_",$key);
					if(!isset($pms[$type.$id][$parts[5]]))
					{
						$pms[$type.$id][$parts[5]] = new PredefinedMessage();
						$pms[$type.$id][$parts[5]]->GroupId = ($type=="g") ? $id : "";
						$pms[$type.$id][$parts[5]]->UserId = ($type=="u") ? $id : "";
						$pms[$type.$id][$parts[5]]->LangISO = $parts[5];
					}
					$pms[$type.$id][$parts[5]]->XMLParamAlloc($parts[6],$value);
				}
		}

	foreach($pms as $oid => $messages)
		foreach($messages as $iso => $message)
		{
			$message->Id = $_counter++;
			$message->Save();
		}
}

function setManagement()
{
	global $INTERNAL,$RESPONSE,$GROUPS;
	if(!DB_CONNECTION)
	{
		$res = testDataBase($CONFIG["gl_db_host"],$CONFIG["gl_db_user"],$CONFIG["gl_db_pass"],$CONFIG["gl_db_name"],$CONFIG["gl_db_prefix"]);
			if(!empty($res))
				$RESPONSE->SetValidationError(LOGIN_REPLY_DB,$res);
		return;
	}
	
	if($INTERNAL[CALLER_SYSTEM_ID]->Level == USER_LEVEL_ADMIN)
	{
		createFile(PATH_USERS . "internal.inc.php",base64_decode($_POST[POST_INTERN_FILE_INTERN]),true);
		createFile(PATH_GROUPS . "groups.inc.php",base64_decode($_POST[POST_INTERN_FILE_GROUPS]),true);
		getData(true,true,true,false);
		updatePredefinedMessages();

		if(isset($_POST[POST_INTERN_EDIT_USER]))
		{
			$combos = explode(";",$_POST[POST_INTERN_EDIT_USER]);
			for($i=0;$i<count($combos);$i++)
				if(strpos($combos[$i],",") !== false)
				{
					$vals = explode(",",$combos[$i]);
					if(strlen($vals[1])>0)
						$INTERNAL[$vals[0]]->ChangePassword($vals[1]);
					$INTERNAL[$vals[0]]->SetPasswordChangeNeeded(($vals[2] == 1));
				}
		}

		$datafiles = getDirectory(PATH_USERS,".htm",true);
		foreach($datafiles as $datafile)
			if(strpos($datafile, FILE_EXTENSION_PASSWORD) !== false || strpos($datafile, FILE_EXTENSION_CHANGE_PASSWORD) !== false)
			{
				$parts = explode(".",$datafile);
				if(!isset($INTERNAL[$parts[0]]))
					@unlink(PATH_USERS . $datafile);
			}
		setIdle(0);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function setConfig($id = 0)
{
	global $INTERNAL,$RESPONSE,$STATS;
	if(SERVERSETUP && $INTERNAL[CALLER_SYSTEM_ID]->Level == USER_LEVEL_ADMIN)
	{
		if(STATS_ACTIVE && isset($_POST[POST_INTERN_RESET_STATS]) && $_POST[POST_INTERN_RESET_STATS]=="1")
			$STATS->ResetAll();
	
		$id = createFile(FILE_CONFIG,base64_decode($_POST[POST_INTERN_UPLOAD_VALUE]),true);
		if(isset($_POST[POST_INTERN_SERVER_AVAILABILITY]))
			setAvailability($_POST[POST_INTERN_SERVER_AVAILABILITY]);
		
		if(isset($_POST[POST_INTERN_FILE_CARRIER_LOGO]) && strlen($_POST[POST_INTERN_FILE_CARRIER_LOGO]) > 0)
			base64ToFile(FILE_CARRIERLOGO,$_POST[POST_INTERN_FILE_CARRIER_LOGO]);
		else if(isset($_POST[POST_INTERN_FILE_CARRIER_LOGO]) && file_exists(FILE_CARRIERLOGO))
			@unlink(FILE_CARRIERLOGO);
			
		if(isset($_POST[POST_INTERN_FILE_CARRIER_HEADER]) && strlen($_POST[POST_INTERN_FILE_CARRIER_HEADER]) > 0)
			base64ToFile(FILE_CARRIERHEADER,$_POST[POST_INTERN_FILE_CARRIER_HEADER]);
		else if(isset($_POST[POST_INTERN_FILE_CARRIER_HEADER]) && file_exists(FILE_CARRIERHEADER))
			@unlink(FILE_CARRIERHEADER);
			
		if(isset($_POST[POST_INTERN_FILE_INVITATION_LOGO]) && strlen($_POST[POST_INTERN_FILE_INVITATION_LOGO]) > 0)
			base64ToFile(FILE_INVITATIONLOGO,$_POST[POST_INTERN_FILE_INVITATION_LOGO]);
		else if(isset($_POST[POST_INTERN_FILE_INVITATION_LOGO]) && file_exists(FILE_INVITATIONLOGO))
			@unlink(FILE_INVITATIONLOGO);
			
		$int = 1;
		while(isset($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int]) && strpos($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int],"..") === false)
		{
			if(!isset($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_DELETE . "_" . $int]))
				createFile("./_language/lang" . strtolower($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int]) . ".php", slashesStrip($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_CONTENT . "_" . $int]), true);
			else
				@unlink("./_language/lang" . strtolower($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int]) . ".php");
			$int++;
		}
	}
	removeSSpanFile(true);
	setIdle(0);
	$RESPONSE->SetStandardResponse($id,"");
}

function dataBaseTest($id=0)
{
	global $RESPONSE;
	$res = testDataBase($_POST[POST_INTERN_DATABASE_HOST],$_POST[POST_INTERN_DATABASE_USER],$_POST[POST_INTERN_DATABASE_PASS],$_POST[POST_INTERN_DATABASE_NAME],$_POST[POST_INTERN_DATABASE_PREFIX]);
	if(empty($res))
		$RESPONSE->SetStandardResponse(1,base64_encode(""));
	else
		$RESPONSE->SetStandardResponse(2,base64_encode($res));
}

function sendTestMail()
{
	global $RESPONSE,$CONFIG;
	$return = sendMail($CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],"LiveZilla Test Mail","LiveZilla Test Mail");
	if(empty($return))
		$RESPONSE->SetStandardResponse(1,base64_encode(""));
	else
		$RESPONSE->SetStandardResponse(2,base64_encode($return));
}

function createTables($id=0)
{
	global $RESPONSE,$GROUPS,$INTERNAL;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		$connection = @mysql_connect($_POST[POST_INTERN_DATABASE_HOST],$_POST[POST_INTERN_DATABASE_USER],$_POST[POST_INTERN_DATABASE_PASS]);
		//mysql_query("SET NAMES 'utf8'", $connection);
		if(!$connection)
		{
			$error = mysql_error();
			$RESPONSE->SetStandardResponse($id,base64_encode("Can't connect to database. Invalid host or login! (" . mysql_errno() . ((!empty($error)) ? ": " . $error : "") . ")"));
		}
		else
		{
			mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $connection);
			$db_selected = mysql_select_db(@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_NAME]),$connection);
			if (!$db_selected) 
	    		$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
			else
			{
				$resultv = @mysql_query("SELECT VERSION() as `mysql_version`",$connection);
				if(!$resultv)
				{
					$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
					return;
				}
				else
				{
					$mrow = @mysql_fetch_array($resultv, MYSQL_BOTH);
					$mversion = explode(".",$mrow["mysql_version"]);
					if(count($mversion) > 0 && $mversion[0] < MYSQL_NEEDED_MAJOR)
					{
						$RESPONSE->SetStandardResponse($id,base64_encode("LiveZilla requires MySQL version ".MYSQL_NEEDED_MAJOR." or greater. The MySQL version installed on your server is " . $mrow["mysql_version"]."."));
						return;
					}
				}
			
				$commands = explode("###",str_replace("<!--prefix-->",$_POST[POST_INTERN_DATABASE_PREFIX],base64_decode($_POST["p_db_sql"])));
				foreach($commands as $sql)
				{
					$result = mysql_query(trim($sql),$connection);
					if(!$result && mysql_errno() != 1050 && mysql_errno() != 1005)
					{
						$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
						return;
					}
				}
				
				$counter=0;
				foreach($GROUPS as $gid => $group)
				{
					@mysql_query("INSERT INTO `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`, `invitation_auto` ,`welcome` ,`website_push_manual`, `website_push_auto` ,`browser_ident` ,`is_default` ,`auto_welcome`)VALUES ('".@mysql_real_escape_string($counter++)."', '','".@mysql_real_escape_string($gid)."', 'EN', 'Hello, my name is %name%. Do you need help? Start Live-Chat now to get assistance.', 'Hello, my name is %name%. Do you need help? Start Live-Chat now to get assistance.','Hello %external_name%, my name is %name%, how may I help you?', 'Website Operator %name% would like to redirect you to this URL:\r\n\r\n%url%', 'Website Operator %name% would like to redirect you to this URL:\r\n\r\n%url%', '1', '1', '1');",$connection);
					@mysql_query("INSERT INTO `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`, `invitation_auto` ,`welcome` ,`website_push_manual`, `website_push_auto` ,`browser_ident` ,`is_default` ,`auto_welcome`)VALUES ('".@mysql_real_escape_string($counter++)."', '','".@mysql_real_escape_string($gid)."', 'DE', '".utf8_encode("Guten Tag, meine Name ist %name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat.")."', '".utf8_encode("Guten Tag, meine Name ist %name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat.")."','Guten Tag %external_name%, mein Name ist %name% wie kann ich Ihnen helfen?', '".utf8_encode("Ein Betreuer dieser Webseite (%name%) möchte Sie auf einen anderen Bereich weiterleiten:\\r\\n\\r\\n%url%")."','".utf8_encode("Ein Betreuer dieser Webseite (%name%) möchte Sie auf einen anderen Bereich weiterleiten:\\r\\n\\r\\n%url%")."', '1', '0', '1');",$connection);
				}
				
				$sql = "INSERT INTO `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INFO."` (`version`,`chat_id`,`ticket_id`) VALUES ('".VERSION."',11700,11700);";
				$result = mysql_query($sql,$connection);
				if(!$result && mysql_errno() != 1062)
				{
					$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
					return;
				}
				$RESPONSE->SetStandardResponse(1,base64_encode(""));
			}
		}
	}
}

function createTable($_sql,$_connection)
{
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).$_sql;
	$result = mysql_query($sql,$_connection);
	if(!$result && mysql_errno() != 1050)
	{
		$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
		return false;
	}
	return true;
}

function testDataBase($_host,$_user,$_pass,$_dbname,$_prefix)
{
	if(!function_exists("mysql_connect"))
		return "PHP/MySQL extension is missing (php_mysql.dll)";
		
	$connection = @mysql_connect($_host,$_user,$_pass);
	@mysql_query("SET NAMES 'utf8'", $connection);
	if(!$connection)
	{
		$error = mysql_error();
		return "Can't connect to database. Invalid host or login! (" . mysql_errno() . ((!empty($error)) ? ": " . $error : "") . ")";
	}
	else
	{
		$db_selected = @mysql_select_db(@mysql_real_escape_string($_dbname),$connection);
		if (!$db_selected) 
    		return mysql_errno() . ": " . mysql_error();
		else
		{
			$resultv = @mysql_query("SELECT VERSION() as `mysql_version`",$connection);
			if(!$resultv)
				return mysql_errno() . ": " . mysql_error();
			else
			{
				$mrow = @mysql_fetch_array($resultv, MYSQL_BOTH);
				$mversion = explode(".",$mrow["mysql_version"]);
				if(count($mversion) > 0 && $mversion[0] < MYSQL_NEEDED_MAJOR)
					return "LiveZilla requires MySQL version ".MYSQL_NEEDED_MAJOR." or greater. The MySQL version installed on your server is " . $mrow["mysql_version"].".";
			}
		
			$tables = 
			array(
				DATABASE_INFO=>array("`version`","`chat_id`","`ticket_id`","`gtspan`"),
				DATABASE_RESOURCES=>array("`id`","`owner`","`editor`","`value`","`edited`","`title`","`created`","`type`","`discarded`","`parentid`","`rank`","`size`"),
				DATABASE_PREDEFINED=>array("`id`","`internal_id`","`group_id`","`lang_iso`","`invitation_manual`","`invitation_auto`","`welcome`","`website_push_manual`","`website_push_auto`","`browser_ident`","`is_default`","`auto_welcome`","`editable`"),
				DATABASE_TICKETS=>array("`id`","`user_id`","`target_group_id`"),
				DATABASE_TICKET_MESSAGES=>array("`id`","`time`","`ticket_id`","`text`","`fullname`","`email`","`company`","`ip`"),
				DATABASE_TICKET_EDITORS=>array("`ticket_id`","`internal_fullname`","`status`","`time`"),
				DATABASE_POSTS=>array("`id`","`chat_id`","`time`","`micro`","`sender`","`receiver`","`receiver_group`","`text`","`translation`","`translation_iso`","`received`","`persistent`"),
				DATABASE_EVENT_ACTION_INVITATIONS=>array("`id`","`action_id`","`position`","`speed`","`slide`","`margin_left`","`margin_top`","`margin_right`","`margin_bottom`","`style`","`close_on_click`"),
				DATABASE_EVENT_TRIGGERS=>array("`id`","`receiver_user_id`","`receiver_browser_id`","`action_id`","`time`","`triggered`"),
				DATABASE_PROFILES=>array("`id`" ,"`edited`" ,"`first_name`" ,"`last_name`" ,"`email`" ,"`company`" ,"`phone`" ,"`fax`" ,"`street`" ,"`zip`" ,"`department`" ,"`city`" ,"`country`" ,"`gender`" ,"`languages`" ,"`comments`" ,"`public`"),
				DATABASE_PROFILE_PICTURES=>array("`id`","`internal_id`","`time`","`webcam`","`data`")
			);
			
			$result = @mysql_query("SELECT `version` FROM `".@mysql_real_escape_string($_prefix).DATABASE_INFO."`",$connection);
			$row = @mysql_fetch_array($result, MYSQL_BOTH);
			$version = $row["version"];
			if(!$result || empty($version))
				return "Cannot read the LiveZilla Database version. Please try to recreate the table structure.";
			
			if($version != VERSION)
			{
				require_once("./_lib/functions.data.db.update.inc.php");
				$upres = updateDatabase($version,$connection,$_prefix);
				if($upres !== true)
					return "Cannot update database structure from [".$version."] to [".VERSION."]. Please make sure that the user " . $_user . " has the MySQL permission to ALTER tables in " . $_dbname .".\r\n\r\nError: " . $upres;
			}
			
			foreach($tables as $tblName => $fieldlist)
			{
				$result = @mysql_query("SHOW COLUMNS FROM `".@mysql_real_escape_string($_prefix.$tblName)."`",$connection);
				if(!$result)
					return mysql_errno() . ": " . mysql_error();
				else if(@mysql_num_rows($result) != count($fieldlist))
					return "Invalid field count for " . $_prefix.$tblName . ". Delete " . $_prefix.$tblName. " manually and try to recreate the tables.";
			}
			return null;
		}
	}
}


?>
