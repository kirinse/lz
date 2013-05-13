<?php
/****************************************************************************************
* LiveZilla functions.global.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function defineURL($_file)
{
	global $CONFIG;
	if(!empty($_SERVER['REQUEST_URI']))
	{
		$parts = parse_url($_SERVER['REQUEST_URI']);
		define("LIVEZILLA_URL",getScheme() . $CONFIG["gl_host"] . str_replace($_file,"",$parts["path"]));
	}
	else
		define("LIVEZILLA_URL",getScheme() . $_SERVER["HTTP_HOST"] . str_replace($_file,"",$_SERVER["PHP_SELF"]));
}

function initStatisticProvider()
{
	global $STATS;
	require(LIVEZILLA_PATH . "_lib/objects.stats.inc.php");
	$STATS = new StatisticProvider();
}

function loadConfig()
{
	global $CONFIG;
	require(LIVEZILLA_PATH . "_config/config.inc.php");
	foreach($CONFIG as $key => $value)
	{
		if(is_array($value))
		{
			foreach($value as $skey => $svalue)
				$CONFIG[$key][$skey]=base64_decode($svalue);
		}
		else
			$CONFIG[$key]=base64_decode($value);
	}
	if(empty($CONFIG["gl_host"]))
		$CONFIG["gl_host"] = $_SERVER["HTTP_HOST"];
		
	if(!empty($CONFIG["gl_stmo"]) && !(defined("SERVERSETUP") && SERVERSETUP))
	{
		$CONFIG["poll_frequency_tracking"] = 86400;
		$CONFIG["timeout_track"] = 0;
	}
	if(function_exists("date_default_timezone_set"))
	{
		if(getSystemTimezone() !== false)
			@date_default_timezone_set(getSystemTimezone());
		else
			@date_default_timezone_set('Europe/Dublin');
	}
}

function handleError($_errno, $_errstr, $_errfile, $_errline)
{
	if(error_reporting()!=0)
		errorLog(date("d.m.y H:i") . " ERR# " . $_errno." ".$_errstr." ".$_errfile." IN LINE ".$_errline."\r");
}

function getAvailability()
{
	return (@file_exists(FILE_SERVER_DISABLED)) ? false : true;
}

function slashesStrip($_value)
{
	if (@get_magic_quotes_gpc() == 1 || strtolower(@get_magic_quotes_gpc()) == "on")
        return stripslashes($_value);
    return $_value; 
}

function getIdle()
{
	if(file_exists(FILE_SERVER_IDLE) && @filemtime(FILE_SERVER_IDLE) < (time()-15))
		@unlink(FILE_SERVER_IDLE);
	return file_exists(FILE_SERVER_IDLE);
}

function getIP($_dontmask=false,$ip="")
{
	global $CONFIG;
	$params = array($CONFIG["gl_sipp"]);
	foreach($params as $param)
		if(!empty($_SERVER[$param]))
		{
			$ipf = $_SERVER[$param];
			if(strpos($ipf,",") !== false)
			{
				$parts = explode(",",$ipf);
				foreach($parts as $part)
					if(substr_count($part,".") == 3)
						$ip = trim($part);
			}
			else if(substr_count($ipf,".") == 3)
				$ip = trim($ipf);
		}
	if(empty($ip))
		$ip = $_SERVER["REMOTE_ADDR"];
	if(!$CONFIG["gl_maskip"] || $_dontmask)
		return $ip;
	else
	{
		$parts = explode(".",$ip);
		return $parts[0].".".$parts[1].".".$parts[2].".xxx";
	}
}

function getHost()
{
	global $CONFIG;
	$ip = getIP(true);
	$host = @utf8_encode(@gethostbyaddr($ip));
	if($CONFIG["gl_maskip"])
	{
		$parts = explode(".",$ip);
		return str_replace($parts[3],"xxx",$host);
	}
	else
		return $host;
}

function getTimeDifference($_time)
{
	$_time = (time() - $_time);
	if(abs($_time) <= 5)
		$_time = 0;
	return $_time;
}

function parseBool($_value,$_toString=true)
{
	if($_toString)
		return ($_value) ? "true" : "false";
	else
		return ($_value) ? "1" : "0";
}

function namebase($_path)
{
	$file = basename($_path);
	if (strpos($file,'\\') !== false)
	{
		$tmp = preg_split("[\\\]",$file);
		$file = $tmp[count($tmp) - 1];
		return $file;
	}
	else
		return $file;
}

function getScheme()
{
	$scheme = SCHEME_HTTP;
	if(!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on")
		$scheme = SCHEME_HTTP_SECURE;
	if(!empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https")
		$scheme = SCHEME_HTTP_SECURE;
	else if(!empty($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443)
		$scheme = SCHEME_HTTP_SECURE;
	return $scheme;
}

function doReplacements($_toReplace)
{
	global $CONFIG,$LZLANG;
	if(!isset($LZLANG))
		languageSelect();
	$to_replace_nam = Array("lang","config");
	$to_replace_con = Array("lang"=>$LZLANG,"config"=>$CONFIG);
	foreach ($to_replace_nam as $nam_e)
		foreach($to_replace_con[$nam_e] as $short => $value)
			if(!is_array($value))
				$_toReplace = str_replace("<!--".$nam_e."_".$short."-->",$value,$_toReplace);
			else
				foreach($value as $subKey => $subValue)
					$_toReplace = str_replace("<!--".$nam_e."_".$subKey."-->",$subValue,$_toReplace);
	return str_replace("<!--file_chat-->",FILE_CHAT,$_toReplace);
}

function getGeoURL()
{
	global $CONFIG;
	if(!empty($CONFIG["gl_pr_ngl"]))
		return CONFIG_LIVEZILLA_GEO_PREMIUM;
	else
		return CONFIG_LIVEZILLA_GEO;
}

function geoReplacements($_toReplace, $jsa = "")
{
	global $CONFIG,$LZLANG;
	$_toReplace = str_replace("<!--geo_url-->",getGeoURL() . "?aid=" . $CONFIG["wcl_geo_tracking"]."&dbp=".$CONFIG["gl_gtdb"],$_toReplace);
	if(!isnull(trim($CONFIG["gl_pr_ngl"])))
	{
		$jsc = "var chars = new Array(";
		$jso = "var order = new Array(";
		$chars = str_split(sha1($CONFIG["gl_pr_ngl"] . date("d"),false));
		$keys = array_keys($chars);
		shuffle($keys);
		foreach($keys as $key)
		{
			$jsc .= "'" . $chars[$key] . "',";
			$jso .= $key . ",";
		}
		$jsa .= $jsc . "0);\r\n";$jsa .= $jso . "0);\r\n";
		$jsa .= "while(oak.length < (chars.length-1))for(var f in order)if(order[f] == oak.length)oak += chars[f];\r\n";
	}
	$_toReplace = str_replace("<!--calcoak-->",$jsa,$_toReplace);
	return $_toReplace;
}

function processHeaderValues()
{
	if(!empty($_SERVER["HTTP_INTERN_AUTHENTICATION_USERID"]))
	{
		$_POST[POST_INTERN_AUTHENTICATION_USERID] = base64_decode($_SERVER["HTTP_INTERN_AUTHENTICATION_USERID"]);
		$_POST[POST_INTERN_AUTHENTICATION_PASSWORD] = base64_decode($_SERVER["HTTP_INTERN_AUTHENTICATION_PASSWORD"]);
		$_POST[POST_INTERN_FILE_TYPE] = $_SERVER["HTTP_INTERN_FILE_TYPE"];
		$_POST[POST_SERVER_REQUEST_TYPE] = $_SERVER["HTTP_SERVER_REQUEST_TYPE"];
		$_POST[POST_INTERN_SERVER_ACTION] = $_SERVER["HTTP_INTERN_SERVER_ACTION"];
	}
	if(!empty($_SERVER["HTTP_ADMINISTRATE"]))
		$_POST[POST_INTERN_ADMINISTRATE] = $_SERVER["HTTP_ADMINISTRATE"];
}

function getServerAddLink($_scheme)
{
	global $CONFIG;
	return PROTOCOL . "://" . base64_encode($_scheme . $CONFIG["gl_host"] . "/" . str_replace("index.php","",$_SERVER["PHP_SELF"])) . "|" . base64_encode($CONFIG["gl_site_name"] . " (" . $CONFIG["gl_host"] .")");
}

function getInternalSystemIdByUserId($_userId)
{
	global $INTERNAL;
	foreach($INTERNAL as $sysId => $intern)
	{
		if($intern->UserId == $_userId)
			return $sysId;
	}
	return null;
}

function md5file($_file)
{
	global $RESPONSE;
	$md5file = @md5_file($_file);
	if(gettype($md5file) != 'boolean' && $md5file != false)
		return $md5file;
}

function getFile($_file,$data="")
{
	if(@file_exists($_file) && strpos($_file,"..") === false)
	{
		$handle = @fopen($_file,"r");
		if($handle)
		{
		   	$data = @fread($handle,@filesize($_file));
			@fclose ($handle);
		}
		return $data;
	}
}

function getParam($_getParam)
{
	if(isset($_GET[$_getParam]))
		return $_GET[$_getParam];
	else
		return null;
}

function getParams($_getParams="")
{
	foreach($_GET as $key => $value)
		if($key != "template")
			$_getParams.=((strlen($_getParams) == 0) ? $_getParams : "&") . urlencode($key) ."=" . urlencode($value);
	return $_getParams;
}

function getCustomParams($_getParams="",$_fromHistory=null)
{
	foreach($_GET as $key => $value)
		if(strlen($key) == 3 && substr($key,0,2) == "cf")
			$_getParams.=  "&" . $key ."=" . htmlentities($value);
			
	if($_getParams=="" && is_array($_fromHistory))
		foreach($_fromHistory as $key => $value)
			if(!empty($value))
				$_getParams.=  "&cf" . $key ."=" . base64UrlEncode($value);
	return $_getParams;
}

function getJSCustomArray($_getCustomParams="",$_fromHistory=null)
{
	for($i=0;$i<=9;$i++)
	{
		if(!empty($_getCustomParams))
			$_getCustomParams .= ",";
		if(isset($_GET["cf".$i]) && !empty($_GET["cf".$i]))
		{
			$_getCustomParams.= "'" . htmlentities($_GET["cf".$i],ENT_QUOTES,"UTF-8") . "'";
		}
		else if(!isnull(getCookieValue("cf_" . $i)))
		{
			$_getCustomParams.= "'" . base64UrlEncode(getCookieValue("cf_" . $i)) . "'";
		}
		else if(is_array($_fromHistory) && isset($_fromHistory[$i]) && !empty($_fromHistory[$i]))
		{
			$_getCustomParams.= "'" . base64UrlEncode($_fromHistory[$i]) . "'";
		}
		else
			$_getCustomParams.= "''";
	}
	return $_getCustomParams;
}

function getCustomArray()
{
	$_getCustomParams = array('','','','','','','','','','');
	for($i=0;$i<=9;$i++)
	{
		if(isset($_GET["cf" . $i]) && !empty($_GET["cf" . $i]))
		{	
			$_getCustomParams[$i] = base64UrlDecode($_GET["cf" . $i]);
		}
		else if(isset($_POST["p_cf" . $i]) && !empty($_POST["p_cf" . $i]))
		{
			$_getCustomParams[$i] = base64UrlDecode($_POST["p_cf" . $i]);
		}
		else if(isset($_POST["form_" . $i]) && !empty($_POST["form_" . $i]))
		{
			$_getCustomParams[$i] = $_POST["form_" . $i];
		}
		else if(!isnull(getCookieValue("cf_" . $i)))
		{
			$_getCustomParams[$i] = getCookieValue("cf_" . $i);
		}
	}
	return $_getCustomParams;
}

function cfgFileSizeToBytes($_configValue) 
{
	$_configValue = strtolower(trim($_configValue));
	$last = substr($_configValue,strlen($_configValue)-1,1);
	switch($last) 
	{
	    case 'g':
	        $_configValue *= 1024;
	    case 'm':
	        $_configValue *= 1024;
	    case 'k':
	        $_configValue *= 1024;
	}
	return floor($_configValue);
}

function AJAXDecode($value="")
{
	return base64UrlDecode($value);
}

function createFile($_filename,$_content,$_recreate)
{
	if(strpos($_filename,"..") === false)
	{
		if(file_exists($_filename))
		{
			if($_recreate)
				@unlink($_filename);
			else
				return 0;
		}
		$handle = @fopen($_filename,"w");
		if(strlen($_content)>0)
			@fputs($handle,$_content);
		@fclose($handle);
		return 1;
	}
	return 0;
}

function b64dcode(&$_a,$_b)
{
	$_a = base64_decode($_a);
}

function base64UrlDecode($_input)
{
    return base64_decode(str_replace(array('_','-',','),array('=','+','/'),$_input));
}

function base64UrlEncode($_input)
{
    return str_replace(array('=','+','/'),array('_','-',','),base64_encode($_input));
}

function cutString($_string,$_maxlength)
{
	if(strlen($_string)>$_maxlength)
		return substr($_string,0,$_maxlength);
	return $_string;
}

function base64ToFile($_filename,$_content)
{
	if(@file_exists($_filename))
		@unlink($_filename);
	$handle = @fopen($_filename,"wb");
	@fputs($handle,base64_decode($_content));
	@fclose($handle);
}

function fileToBase64($_filename)
{
	if(@filesize($_filename) == 0)
		return "";
	$handle = @fopen($_filename,"rb");
	$content = @fread($handle,@filesize($_filename));
	@fclose($handle);
	return base64_encode($content);
}

function initData($_internal=false,$_groups=false,$_visitors=false,$_filters=false,$_events=false,$_languages=false,$_countries=false)
{
	global $INTERNAL,$GROUPS,$LANGUAGES,$COUNTRIES,$FILTERS,$EVENTS,$VISITORS;
	if($_internal && empty($INTERNAL))loadInternals();
	if($_groups && empty($GROUPS))loadGroups();
	if($_languages && empty($LANGUAGES))loadLanguages();
	if($_countries && empty($COUNTRIES))loadCountries();
	if($_filters && empty($FILTERS))loadFilters();
	if($_events && empty($EVENTS))loadEvents();
	if($_visitors && empty($VISITORS))loadVisitors();
}

function getData($_internal,$_groups,$_visitors,$_filters,$_events=false)
{
	if($_internal)loadInternals();
	if($_groups)loadGroups();
	if($_visitors)loadVisitors();
	if($_filters)loadFilters();
	if($_events)loadEvents();
}

function loadLanguages()
{
	global $LANGUAGES;
	require("./_lib/objects.languages.inc.php");
}

function loadCountries()
{
	global $COUNTRIES,$COUNTRY_ALIASES;
	require("./_lib/objects.countries.inc.php");
}

function loadFilters()
{
	global $FILTERS;
	$FILTERS = new FilterList();
}

function loadEvents()
{
	global $EVENTS;
	$EVENTS = new EventList();
	$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENTS."` ORDER BY `priority` DESC;");
	while($row = @mysql_fetch_array($result, MYSQL_BOTH))
	{
		$Event = new Event($row);
		$result_urls = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_URLS."` WHERE `eid`='".@mysql_real_escape_string($Event->Id)."';");
		while($row_url = @mysql_fetch_array($result_urls, MYSQL_BOTH))
		{
			$EventURL = new EventURL($row_url);
			$Event->URLs[$EventURL->Id] = $EventURL;
		}
		
		$result_funnel_urls = queryDB(true,"SELECT `ind`,`uid` FROM `".DB_PREFIX.DATABASE_EVENT_FUNNELS."` WHERE `eid`='".@mysql_real_escape_string($Event->Id)."';");
		while($funnel_url = @mysql_fetch_array($result_funnel_urls, MYSQL_BOTH))
		{
			$Event->FunnelUrls[$funnel_url["ind"]] = $funnel_url["uid"];
		}
		$result_actions = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTIONS."` WHERE `eid`='".@mysql_real_escape_string($Event->Id)."';");
		while($row_action = @mysql_fetch_array($result_actions, MYSQL_BOTH))
		{
			$EventAction = new EventAction($row_action);
			$Event->Actions[$EventAction->Id] = $EventAction;
			
			if($EventAction->Type==2)
			{
				$result_action_invitations = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_INVITATIONS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				$row_invitation = @mysql_fetch_array($result_action_invitations, MYSQL_BOTH);
				$EventAction->Invitation = new Invitation($row_invitation);
				
				$result_senders = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` WHERE `pid`='".@mysql_real_escape_string($EventAction->Invitation->Id)."' ORDER BY `priority` DESC;");
				while($row_sender = @mysql_fetch_array($result_senders, MYSQL_BOTH))
				{
					$InvitationSender = new EventActionSender($row_sender);
					$EventAction->Invitation->Senders[$InvitationSender->Id] = $InvitationSender;
				}
			}
			else if($EventAction->Type==4)
			{
				$result_action_website_pushs = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_WEBSITE_PUSHS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				$row_website_push = @mysql_fetch_array($result_action_website_pushs, MYSQL_BOTH);
				$EventAction->WebsitePush = new WebsitePush($row_website_push,true);
				
				$result_senders = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` WHERE `pid`='".@mysql_real_escape_string($EventAction->WebsitePush->Id)."' ORDER BY `priority` DESC;");
				while($row_sender = @mysql_fetch_array($result_senders, MYSQL_BOTH))
				{
					$WebsitePushSender = new EventActionSender($row_sender);
					$EventAction->WebsitePush->Senders[$WebsitePushSender->Id] = $WebsitePushSender;
				}
			}
			else if($EventAction->Type<2)
			{
				$result_receivers = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				while($row_receiver = @mysql_fetch_array($result_receivers, MYSQL_BOTH))
					$EventAction->Receivers[$row_receiver["receiver_id"]] = new EventActionReceiver($row_receiver);
			}
		}
		if(STATS_ACTIVE)
		{
			$result_goals = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_GOALS."` WHERE `event_id`='".@mysql_real_escape_string($Event->Id)."';");
			while($row_goals = @mysql_fetch_array($result_goals, MYSQL_BOTH))
				$Event->Goals[$row_goals["goal_id"]] = new EventAction($row_goals["goal_id"],5);
		}
		$EVENTS->Events[$Event->Id] = $Event;
	}
}

function loadInternals()
{
	global $CONFIG,$INTERNAL;
	require(PATH_USERS . "internal.inc.php");
	foreach($INTERN as $sysId => $internaluser)
	{
		$INTERNAL[$sysId] = new Operator($sysId,$internaluser["in_id"]);
		$INTERNAL[$sysId]->Email = $internaluser["in_email"];
		$INTERNAL[$sysId]->Webspace = $internaluser["in_websp"];
		$INTERNAL[$sysId]->Level = $internaluser["in_level"];
		$INTERNAL[$sysId]->Description = $internaluser["in_desc"];
		$INTERNAL[$sysId]->Fullname = $internaluser["in_name"];
		$INTERNAL[$sysId]->Language = $internaluser["in_lang"];
		$INTERNAL[$sysId]->Groups = unserialize(base64_decode($internaluser["in_groups"]));
		array_walk($INTERNAL[$sysId]->Groups,"b64dcode");
		$INTERNAL[$sysId]->GroupsArray = $internaluser["in_groups"];
		$INTERNAL[$sysId]->PermissionSet = $internaluser["in_perms"];
		$INTERNAL[$sysId]->CanAutoAcceptChats = (isset($internaluser["in_aac"])) ? $internaluser["in_aac"] : 1;
		$INTERNAL[$sysId]->LoginIPRange = $internaluser["in_lipr"];
	}
}

function loadGroups()
{
	global $GROUPS,$CONFIG;
	require(PATH_GROUPS . "groups.inc.php");
	foreach($GROUPS as $id => $group)
		$GROUPS[$id] = new UserGroup($id,$GROUPS[$id],$CONFIG);
}

function loadVisitors($_fullList=false,$_sqlwhere="",$_limit="",$count=0)
{
	global $VISITOR,$CONFIG,$COUNTRIES;
	$VISITOR = array();
	if(!$_fullList)
		$_sqlwhere = " WHERE `last_active`>".@mysql_real_escape_string(time()-$CONFIG["timeout_track"]);
	
	$result = queryDB(true,"SELECT *,`t1`.`id` AS `id` FROM `".DB_PREFIX.DATABASE_VISITORS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_BROWSERS."` AS `t2` ON `t1`.`browser`=`t2`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_CITIES."` AS `t3` ON `t1`.`city`=`t3`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_REGIONS."` AS `t4` ON `t1`.`region`=`t4`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_ISPS."` AS `t5` ON `t1`.`isp`=`t5`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_SYSTEMS."` AS `t6` ON `t1`.`system`=`t6`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_RESOLUTIONS."` AS `t8` ON `t1`.`resolution`=`t8`.`id`".$_sqlwhere." ORDER BY `entrance` ASC".$_limit.";");
	if($result)
	{
		initData(false,false,false,false,false,false,true);
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if(!isset($VISITOR[$row["id"]]))
			{
				$row["countryname"] = $COUNTRIES[$row["country"]];
				if(!isset($vcount[$row["id"]]))
					$vcount[$row["id"]]=0;
				$vcount[$row["id"]]++;
				$row["dcount"] = $vcount[$row["id"]];
				$index = ($_fullList) ? $count++ : $row["id"];
				$VISITOR[$index] = new Visitor($row["id"]);
				$VISITOR[$index]->Load($row);
				$VISITOR[$index]->LoadBrowsers($_fullList);
			}
		$visitors = $VISITOR;
		$VISITOR = array();
		foreach($visitors as $vid => $visitor)
			if(count($visitor->Browsers) > 0 || $_fullList)
				$VISITOR[$vid] = $visitor;
	}
}

function getTargetParameters()
{
	$parameters = array("exclude"=>null,"include_group"=>null,"include_user"=>null);
	if(isset($_GET[GET_EXTERN_HIDDEN_GROUPS]))
	{
		$groups = base64UrlDecode($_GET[GET_EXTERN_HIDDEN_GROUPS]);
		if(strlen($groups) > 1)
			$parameters["exclude"] = explode("?",$groups);
		else if(isset($_GET[GET_EXTERN_GROUP]))
			$parameters["include_group"] = array(base64UrlDecode($_GET[GET_EXTERN_GROUP]));
		else if(isset($_GET[GET_EXTERN_INTERN_USER_ID]))
			$parameters["include_user"] = base64UrlDecode($_GET[GET_EXTERN_INTERN_USER_ID]);
	}
	return $parameters;
}

function operatorsAvailable($_amount = 0, $_exclude=null, $include_group=null, $include_user=null)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	
	if(!DB_CONNECTION)
		return 0;
	
	initData(true,true);
	
	if(!empty($include_user))
		$include_group = $INTERNAL[getInternalSystemIdByUserId($include_user)]->Groups;
		
	foreach($INTERNAL as $sysId => $internaluser)
		if($internaluser->IsExternal($GROUPS, $_exclude, $include_group) && $internaluser->Status < USER_STATUS_OFFLINE)
			$_amount++;
	return $_amount;
}

function getOperatorList()
{
	global $INTERNAL,$GROUPS;
	$array = array();
	initData(true,true,false,false);
	foreach($INTERNAL as $sysId => $internaluser)
		if($internaluser->IsExternal($GROUPS))
			$array[utf8_decode($internaluser->Fullname)] = $internaluser->Status;
	return $array;
}

function getOperators()
{
	global $INTERNAL,$GROUPS;
	$array = array();
	initData(true,true,false,false);
	foreach($INTERNAL as $sysId => $internaluser)
	{
		$internaluser->IsExternal($GROUPS);
		$array[$sysId] = $internaluser;
	}
	return $array;
}

function isValidUploadFile($_filename)
{
	global $CONFIG;
	$extensions = explode(",",str_replace("*.","",$CONFIG["wcl_upload_blocked_ext"]));
	foreach($extensions as $ext)
		if(strlen($_filename) > strlen($ext) && substr($_filename,strlen($_filename)-strlen($ext),strlen($ext)) == $ext)
			return false;
	return true;
}

function languageSelect()
{
	global $LZLANG,$CONFIG;
	if(!empty($CONFIG["gl_on_def_lang"]) && file_exists(LIVEZILLA_PATH . "_language/lang".$CONFIG["gl_default_language"].".php"))
	{
		define("DEFAULT_BROWSER_LANGUAGE",$CONFIG["gl_default_language"]);
		require(LIVEZILLA_PATH . "_language/lang".$CONFIG["gl_default_language"].".php");
	}
	else if(empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) || (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && strpos($_SERVER["HTTP_ACCEPT_LANGUAGE"],"..") === false))
	{
		if(!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && strlen($_SERVER["HTTP_ACCEPT_LANGUAGE"]) >= 5 && substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],2,1) == "-" && file_exists(LIVEZILLA_PATH . "_language/lang". strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,5)) .".php"))
			require(LIVEZILLA_PATH . "_language/lang".($s_browser_language=strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,5))).".php");
		else if(!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && strlen($_SERVER["HTTP_ACCEPT_LANGUAGE"]) > 1 && file_exists(LIVEZILLA_PATH . "_language/lang".strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2)).".php"))
			require(LIVEZILLA_PATH . "_language/lang".($s_browser_language=strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2))).".php");
		else if(file_exists(LIVEZILLA_PATH . "_language/lang".$CONFIG["gl_default_language"].".php"))
			require(LIVEZILLA_PATH . "_language/lang".($s_browser_language=$CONFIG["gl_default_language"]).".php");
			
		if(isset($s_browser_language))
			define("DEFAULT_BROWSER_LANGUAGE",$s_browser_language);
	}
	else if(file_exists(LIVEZILLA_PATH . "_language/lang".$CONFIG["gl_default_language"].".php"))
		require(LIVEZILLA_PATH . "_language/lang".$CONFIG["gl_default_language"].".php");
	
	if(!defined("DEFAULT_BROWSER_LANGUAGE") && file_exists(LIVEZILLA_PATH . "_language/langen.php"))
	{
		define("DEFAULT_BROWSER_LANGUAGE","en");
		require(LIVEZILLA_PATH . "_language/langen.php");
	}
	
	if(!defined("DEFAULT_BROWSER_LANGUAGE") || (defined("DEFAULT_BROWSER_LANGUAGE") && !@file_exists(LIVEZILLA_PATH . "_language/lang".DEFAULT_BROWSER_LANGUAGE.".php")))
		exit("Localization error: default language is not available.");
}

function getLongPollRuntime()
{
	global $CONFIG;
	if(SAFE_MODE)
		$value = 10;
	else
	{
		$value = $CONFIG["timeout_clients"] - $CONFIG["poll_frequency_clients"] - 55;
		if(!isnull($ini = @ini_get('max_execution_time')) && $ini > $CONFIG["poll_frequency_clients"] && $ini < $value)
			$value = $ini-$CONFIG["poll_frequency_clients"];
		if($value > 20)
			$value = 20;
		if($value < 1)
			$value = 1;
	}
	return $value;
}

function checkPhpVersion($_ist,$_ond,$_ird)
{
	$array = explode(".",phpversion());
	if($array[0] >= $_ist)
	{
		if($array[1] > $_ond || ($array[1] == $_ond && $array[2] >= $_ird))
			return true;
		return false;
	}
	return false;
}

function getAlertTemplate()
{
	global $CONFIG;
	$html = str_replace("<!--server-->",LIVEZILLA_URL,getFile(TEMPLATE_SCRIPT_ALERT));
	$html = str_replace("<!--title-->",$CONFIG["gl_site_name"],$html);
	return $html;
}

function formLanguages($_lang)
{
	if(strlen($_lang) == 0)
		return "";
	$array_lang = explode(",",$_lang);
	foreach($array_lang as $key => $lang)
		if($key == 0)
		{
			$_lang = strtoupper(substr(trim($lang),0,2));
			break;
		}
	return (strlen($_lang) > 0) ? $_lang : "";
}

function logit($_id,$_file=null)
{
	if(empty($_file))
		$_file = LIVEZILLA_PATH . "_log/debug.txt";
	
	if(@file_exists($_file) && @filesize($_file) > 5000000)
		@unlink($_file);
		
	$handle = @fopen($_file,"a+");
	@fputs($handle,$_id."\r\n");
	@fclose($handle);
}

function errorLog($_message)
{
	global $RESPONSE;
	if(defined("FILE_ERROR_LOG"))
	{
		if(file_exists(FILE_ERROR_LOG) && @filesize(FILE_ERROR_LOG) > 500000)
			@unlink(FILE_ERROR_LOG);
		$handle = @fopen (FILE_ERROR_LOG,"a+");
		if($handle)
		{
			@fputs($handle,$_message . "\r");
			@fclose($handle);
		}
		if(!empty($RESPONSE))
		{
			if(!isset($RESPONSE->Exceptions))
				$RESPONSE->Exceptions = "";
			$RESPONSE->Exceptions .= "<val err=\"".base64_encode(trim($_message))."\" />";
		}
	}
	else
		$RESPONSE->Exceptions = "";
}

function getId($_length,$start=0)
{
	$id = md5(uniqid(rand(),1));
	if($_length != 32)
		$start = rand(0,(31-$_length));
	$id = substr($id,$start,$_length);
	return $id;
}

function createFloodFilter($_ip,$_userId)
{
	global $FILTERS;
	initData(false,false,false,true);
	foreach($FILTERS->Filters as $currentFilter)
		if($currentFilter->IP == $_ip && $currentFilter->Activeipaddress == 1 && $currentFilter->Activestate == 1)
			return;
	
	$filter = new Filter(md5(uniqid(rand())));
	$filter->Creator = "SYSTEM";
	$filter->Created = time();
	$filter->Editor = "SYSTEM";
	$filter->Edited = time();
	$filter->IP = $_ip;
	$filter->Expiredate = 172800;
	$filter->Userid = $_userId;
	$filter->Reason = "";
	$filter->Filtername = "AUTO FLOOD FILTER";
	$filter->Activestate = 1;
	$filter->Exertion = 0;
	$filter->Languages = "";
	$filter->Activeipaddress = 1;
	$filter->Activeuserid = 0;
	$filter->Activelanguage = 0;
	$filter->Save();
}

function isFlood($_ip,$_userId,$_chat=false)
{
	global $VISITOR,$FILTERS,$CONFIG;
	if(empty($CONFIG["gl_atflt"]))
		return false;
	$sql = "SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` AS t2 ON t1.id=t2.visitor_id WHERE t1.`ip`='".@mysql_real_escape_string($_ip)."' AND `t2`.`created`>".(time()-FLOOD_PROTECTION_TIME) . " AND `t1`.`visit_latest`=1";
	if($result = queryDB(true,$sql));
		if(@mysql_num_rows($result) >= FLOOD_PROTECTION_SESSIONS)
		{
			createFloodFilter($_ip,$_userId);
			return true;
		}
	return false;
}

function removeSSpanFile($_all)
{
	if($_all || (getSpanValue() < time()))
		setSpanValue(0);
}

function isSSpanFile()
{
	return !isnull(getSpanValue());
}

function getSpanValue()
{
	if(DB_CONNECTION && $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_INFO."`"))
		if($row = mysql_fetch_array($result, MYSQL_BOTH))
			return $row["gtspan"];
	return time();
}

function setSpanValue($_value)
{
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INFO."` SET `gtspan`='".@mysql_real_escape_string($_value)."'");
}

function createSSpanFile($_sspan)
{
	global $CONFIG;
	if($_sspan >= CONNECTION_ERROR_SPAN && empty($CONFIG["gl_pr_ngl"]))
		setSpanValue((time()+$_sspan));
}

function getLocalTimezone($_timezone,$ltz=0)
{
	$template = "%s%s%s:%s%s";
	if(isset($_timezone) && !empty($_timezone))
	{
		$ltz = $_timezone;
		if($ltz == ceil($ltz))
		{
			if($ltz >= 0 && $ltz < 10)
				$ltz = sprintf($template,"+","0",$ltz,"0","0");
			else if($ltz < 0 && $ltz > -10)
				$ltz = sprintf($template,"-","0",$ltz*-1,"0","0");
			else if($ltz >= 10)
				$ltz = sprintf($template,"+",$ltz,"","0","0");
			else if($ltz <= -10)
				$ltz = sprintf($template,"",$ltz,"","0","0");
		}
		else
		{
			$split = explode(".",$ltz);
			$split[1] = (60 * $split[1]) / 100;
			if($ltz >= 0 && $ltz < 10)
				$ltz = sprintf($template,"+","0",$split[0],$split[1],"0");
			else if($ltz < 0 && $ltz > -10)
				$ltz = sprintf($template,"","0",$split[0],$split[1],"0");
				
			else if($ltz >= 10)
				$ltz = sprintf($template,"+",$split[0],"",$split[1],"0");
			
			else if($ltz <= -10)
				$ltz = sprintf($template,"",$split[0],"",$split[1],"0");
		}
	}
	return $ltz;
}

function isValidEmail($_email)
{
	return preg_match('/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $_email);
}

function setCookieValue($_key,$_value)
{
	global $CONFIG;
	if(!isset($_COOKIE["livezilla"]))
		$c_array = Array();
	else
		$c_array = @unserialize(@base64_decode($_COOKIE["livezilla"]));
	if(!isset($c_array[$_key]) || (isset($c_array[$_key]) && $c_array[$_key] != $_value))
	{	
		$c_array[$_key] = $_value;
		setcookie("livezilla",($_COOKIE["livezilla"] = base64_encode(serialize($c_array))),time()+($CONFIG["gl_colt"]*86400));
	}
}

function getCookieValue($_key)
{
	if(isset($_COOKIE["livezilla"]))
		$c_array = @unserialize(base64_decode($_COOKIE["livezilla"]));
	
	if(isset($c_array[$_key]))
		return $c_array[$_key];
	else
		return null;
}

function hashFile($_file)
{
	$enfile = md5(base64_encode(file_get_contents($_file)));
	return $enfile;
}

function mTime()
{
	$time = str_replace(".","",microtime());
	$time = explode(" " , $time);
	return $time[0];
}

function microtimeFloat($_microtime)
{
   list($usec, $sec) = explode(" ", $_microtime);
   return ((float)$usec + (float)$sec);
}

function testDirectory($_dir)
{	
	global $LZLANG,$ERRORS;
	if(!@is_dir($_dir))
		@mkdir($_dir);
	
	if(@is_dir($_dir))
	{
		$fileid = md5(uniqid(rand()));
		$handle = @fopen ($_dir . $fileid ,"a");
		@fputs($handle,$_id."\r\n");
		@fclose($handle);
		
		if(!file_exists($_dir . $fileid))
			return false;
			
		@unlink($_dir . $fileid);
		if(file_exists($_dir . $fileid))
			return false;
			
		return true;
	}
	else
		return false;
}

function sendMail($_receiver,$_sender,$_replyto,$_text,$_subject="")
{
	global $CONFIG;
	$return = "";
	if(strpos($_receiver,",") === false)
	{
		$EOL = (!empty($CONFIG["gl_smtpauth"])) ? "\r\n" : "\n";
		$message  = $_text;
		$headers  = "From: ".$_sender.$EOL;
	    $headers .= "Reply-To: ".$_replyto.$EOL;
		$headers .= "Date: ".date("r").$EOL;
		$headers .= "MIME-Version: 1.0".$EOL;
		$headers .= "Content-Type: text/plain; charset=UTF-8; format=flowed".$EOL;
		$headers .= "Content-Transfer-Encoding: 8bit".$EOL;
    	$headers .= "X-Mailer: LiveZilla.net/" . VERSION.$EOL;
			
		if(!empty($CONFIG["gl_smtpauth"]))
			$return = authMail($CONFIG["gl_smtphost"], $CONFIG["gl_smtpport"], $_receiver, $_subject, $_text, $headers, $_sender, $CONFIG["gl_smtppass"], $CONFIG["gl_smtpuser"], !empty($CONFIG["gl_smtpssl"]));
		else
		{
			if(@mail($_receiver, $_subject, $_text, $headers))
				$return = null;
			else
				$return = "The email could not be sent using PHP mail(). Please try another Return Email Address or use SMTP.";
		}
	}
	else
	{
		$emails = explode(",",$_receiver);
		foreach($emails as $mail)
			if(!empty($mail))
				sendMail(trim($mail), $_sender, $_replyto, $_text, $_subject);
	}
	return $return;
}

function authMail($_server, $_port, $_receiver, $_subject, $_text, $_header, $_from, $_password, $_account, $_secure)
{
	$return = "\r\n\r\n";
	$break = "\r\n";
	$_text = preg_replace("/^\./","..",explode($break,$_text));
	$smtp = array(array("EHLO localhost".$break,"220,250"),array("AUTH LOGIN".$break,"334"),array(base64_encode($_account).$break,"334"),array(base64_encode($_password).$break,"235"));
	
	$smtp[] = array("MAIL FROM: <".$_from.">".$break,"250");
	$smtp[] = array("RCPT TO: <".$_receiver.">".$break,"250");
	$smtp[] = array("DATA".$break,"354");
	$smtp[] = array("Subject: ".$_subject.$break,"");
	$smtp[] = array("To: ".$_receiver.$break,"");
	
	$_header = explode($break,$_header);
	foreach($_header as $value) 
		$smtp[] = array($value.$break,"");

	$smtp[] = array($break,"");
	
	foreach($_text as $line) 
		$smtp[] = array($line.$break,"");

	$smtp[] = array(".".$break,"250");
	$smtp[] = array("QUIT".$break,"221");
	
	$secure = ($_secure) ? "ssl://" : "";

	$fp = @fsockopen($secure . $_server, $_port);
	if($fp)
	{
		$result = @fgets($fp, 1024);
		$return .= $result;
		foreach($smtp as $req)
		{
			@fputs($fp, $req[0]);
			if($req[1])
				while($result = @fgets($fp, 1024))
				{
					$return .= $result;
					if(substr($result,3,1) == " ") 
						break;
				}
		}
		@fclose($fp);
		if(substr($result,0,1) == "2")
			$return = null;
	}
	else 
		return "Cannot connect to " . $secure . $_server;
		
	return $return;
}

function setDataProvider()
{
	global $CONFIG;
	define("DB_PREFIX",$CONFIG["gl_db_prefix"]);
	return createDBConnector();
}

function createDBConnector()
{
	global $CONFIG,$DB_CONNECTOR;
	if(!empty($CONFIG["gl_datprov"]))
	{
		$DB_CONNECTOR = @mysql_connect($CONFIG["gl_db_host"], $CONFIG["gl_db_user"], $CONFIG["gl_db_pass"]);
		if($DB_CONNECTOR)
		{
			mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $DB_CONNECTOR);
			//mysql_set_charset('utf8', $DB_CONNECTOR); 
			//@mysql_query("SET NAMES 'utf8'", $DB_CONNECTOR);
			if(@mysql_select_db($CONFIG["gl_db_name"], $DB_CONNECTOR))
			{
				define("DB_CONNECTION",true);
				return DB_CONNECTION;
			}
		}
	}
	define("DB_CONNECTION",false);
	return DB_CONNECTION;
}

function queryDB($_log,$_sql)
{
	global $CONFIG,$DB_CONNECTOR,$DBA,$QLIST;
	if(!DB_CONNECTION)
		return false;
	$DBA++;
	$result = @mysql_query($_sql, $DB_CONNECTOR);
	$ignore = array("1146","1062","1045","2003");
	if($_log && !$result && !in_array(mysql_errno(),$ignore))
		logit(time() . " - " . mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $_sql,LIVEZILLA_PATH  . "_log/sql.txt");
	return $result;
}

function unloadDataProvider()
{
	global $DB_CONNECTOR;
	if($DB_CONNECTOR)
		@mysql_close($DB_CONNECTOR);
}

function runPeriodicJobs()
{
	global $CONFIG,$VISITOR,$STATS;
	if(rand(0,100) == 1)
	{
		$timeouts = array($CONFIG["poll_frequency_clients"] * 10,86400,86400*7,DATA_LIFETIME);
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE (`html` = '0' OR `html` = '') AND `time` < " . @mysql_real_escape_string(time()-$timeouts[3]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `time` < " . @mysql_real_escape_string(time()-$timeouts[3]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `persistent` = '0' AND `time` < " . @mysql_real_escape_string(time()-$timeouts[1]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE `time` < ".@mysql_real_escape_string(time()-$timeouts[1]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` WHERE `created` < " . @mysql_real_escape_string(time()-$timeouts[0]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `webcam`=1 AND `time` < ".@mysql_real_escape_string(time()-$timeouts[0]));

		if(!STATS_ACTIVE)
		{
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `last_active`<'".@mysql_real_escape_string(time()-$timeouts[1])."';");
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` WHERE `".DB_PREFIX.DATABASE_OPERATOR_STATUS."`.`confirmed`<'".@mysql_real_escape_string(time()-$timeouts[1])."';");
		}
		else
			StatisticProvider::DeleteHTMLReports();
			
		if(!empty($CONFIG["gl_rm_chats"]))
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_chats_time"]));
		if(!empty($CONFIG["gl_rm_rt"]))
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_rt_time"]));
		if(!empty($CONFIG["gl_rm_om"]))
		{
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_EDITORS."` WHERE `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_om_time"]));
			queryDB(true,"DELETE `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`,`".DB_PREFIX.DATABASE_TICKETS."` FROM `".DB_PREFIX.DATABASE_TICKETS."` INNER JOIN `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE `".DB_PREFIX.DATABASE_TICKETS."`.`id` = `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`ticket_id` AND `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_om_time"]));
		}

		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `discarded`=1 AND `type` > 2 AND `edited` < " . @mysql_real_escape_string(time()-$timeouts[3])));
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$resultb = queryDB(true,"SELECT count(value) as linked FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `value`='". @mysql_real_escape_string($row["value"])."';");
				$rowb = mysql_fetch_array($resultb, MYSQL_BOTH);
				if($rowb["linked"] == 1)
					@unlink(PATH_UPLOADS . $row["value"]);
			}
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `discarded`='1' AND `edited` < " . @mysql_real_escape_string(time()-$timeouts[3]));
	}
	else if(rand(0,10) == 1)
	{
		sendChatTranscripts();
	}
}

function getSubject($_chatTranscript,$_email,$_username,$_group,$_chatid)
{
	global $CONFIG;
	if($_chatTranscript)
		$subject = $CONFIG["gl_subjct"];
	else
		$subject = $CONFIG["gl_subjom"];
		
	$subject = str_replace("%SERVERNAME%",$CONFIG["gl_site_name"],$subject);
	$subject = str_replace("%USERNAME%",$_username,$subject);
	$subject = str_replace("%USEREMAIL%",$_email,$subject);
	$subject = str_replace("%TARGETGROUP%",$_group,$subject);
	$subject = str_replace("%CHATID%",$_chatid,$subject);
	return $subject;
}

function sendChatTranscripts()
{
	global $CONFIG,$INTERNAL,$GROUPS;
	$result = queryDB(true,"SELECT `internal_id`,`plain`,`transcript_receiver`,`email`,`chat_id`,`fullname`,`group_id` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `endtime`>0 AND `closed`>0 AND `transcript_sent`=0 LIMIT 1;");
	if($result)
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `transcript_sent`=1 WHERE `chat_id`='". @mysql_real_escape_string($row["chat_id"])."' LIMIT 1;");
			$rcvs = $row["plain"];
			
			$email = (empty($row["transcript_receiver"])) ? $row["email"] : $row["transcript_receiver"];
			$subject = getSubject(true,$email,$row["fullname"],$row["group_id"],$row["chat_id"]);
			
			if(empty($CONFIG["gl_pr_nbl"]))
				$rcvs .= base64_decode("DQoNCg0KcG93ZXJlZCBieSBMaXZlWmlsbGEgTGl2ZSBTdXBwb3J0IFtodHRwOi8vd3d3LmxpdmV6aWxsYS5uZXRd");
			
			if(!empty($CONFIG["gl_soct"]) && !empty($row["transcript_receiver"]))
				sendMail($row["transcript_receiver"],$CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],$rcvs,$subject);
			
			if(!empty($CONFIG["gl_scto"]))
			{
				initData(true);
				sendMail($INTERNAL[$row["internal_id"]]->Email,$CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],$rcvs,$subject);
			}
			
			if(!empty($CONFIG["gl_sctg"]))
			{
				initData(false,true);
				sendMail($GROUPS[$row["group_id"]]->Email,$CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],$rcvs,$subject);
			}
			
			if(!empty($CONFIG["gl_scct"]))
				sendMail($CONFIG["gl_scct"],$CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],$rcvs,$subject);
		}
	if(!empty($CONFIG["gl_rm_chats"]) && $CONFIG["gl_rm_chats_time"] == 0)
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `transcript_sent` = '1';");
}

function getResource($_id)
{
	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `id`='".@mysql_real_escape_string($_id)."' LIMIT 1;"))
		if($row = mysql_fetch_array($result, MYSQL_BOTH))
			return $row;
	return null;
}

function markPostReceived($_id)
{
	queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_POSTS."` SET `received`='1',`persistent`='0' WHERE `id`='".@mysql_real_escape_string($_id)."';");
}

function getPosts($_receiver)
{
	$posts = array();
	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `receiver`='".@mysql_real_escape_string($_receiver)."' AND `received`='0' ORDER BY `time` ASC, `micro` ASC;"))
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$posts[] = $row;
	return $posts;
}

function getDirectory($_dir,$_oddout,$_ignoreSource=false)
{
	$files = array();

	if(!@is_dir($_dir))
		return $files;
	$handle=@opendir($_dir);
	while ($filename = @readdir ($handle)) 
	   	if ($filename != "." && $filename != ".." && ($_oddout == false || !stristr($filename,$_oddout)))
			if($_oddout != "." || ($_oddout == "." && @is_dir($_dir . "/" . $filename)))
	       		$files[]=$filename;
	@closedir($handle);
	return $files;
}


function getValueId($_database,$_column,$_value,$_canBeNumeric=true,$_maxlength=null)
{
	if(!$_canBeNumeric && is_numeric($_value))
		$_value = "";
		
	if($_maxlength != null && strlen($_value) > $_maxlength)
		$_value = substr($_value,0,$_maxlength);

	queryDB(true,"INSERT INTO `".DB_PREFIX.$_database."` (`id`, `".$_column."`) VALUES (NULL, '".@mysql_real_escape_string($_value)."');");
	$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.$_database."` WHERE `".$_column."`='".@mysql_real_escape_string($_value)."';"), MYSQL_BOTH);
	return $row["id"];
}

function getIdValue($_database,$_column,$_id,$_unknown=false)
{
	$row = mysql_fetch_array(queryDB(true,"SELECT `".$_column."` FROM `".DB_PREFIX.$_database."` WHERE `id`='".@mysql_real_escape_string($_id)."' LIMIT 1;"));
	if($_unknown && empty($row[$_column]))
		return "<!--lang_stats_unknown-->";
	return $row[$_column];
}

function compareUrls($_templateUrl,$_comparerUrl)
{
	$_templateUrl=strtolower($_templateUrl);
	$_comparerUrl=strtolower($_comparerUrl);
	$match=true;
	if(strpos($_templateUrl,"*")===false && $_templateUrl != $_comparerUrl)
		$match = false;
	else
	{
		$parts = explode("*",$_templateUrl);
		$index = 0;
		for($i=0;$i<count($parts);$i++)
		{
			if($parts[$i] == "")
				continue;
			if($i == count($parts)-1 && substr($_comparerUrl,(strlen($_comparerUrl)-strlen($parts[$i])),strlen($parts[$i])) != $parts[$i])
			{
				$match = false;
				break;
			}
			else if(($pos = strpos($_comparerUrl,$parts[$i])) !== false)
			{
				if($pos < $index)
				{
					$match = false;
					break;
				}
			}
			else
			{
				$match = false;
				break;
			}
		}
	}
	return $match;
}

function processResource($_userId,$_resId,$_value,$_type,$_title,$_disc,$_parentId,$_rank,$_size=0)
{
	if($_size == 0)
		$_size = strlen($_title);
	$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `id`='".@mysql_real_escape_string($_resId)."'");
	if(@mysql_num_rows($result) == 0)
		queryDB(true,$result = "INSERT INTO `".DB_PREFIX.DATABASE_RESOURCES."` (`id`,`owner`,`editor`,`value`,`edited`,`title`,`created`,`type`,`discarded`,`parentid`,`rank`,`size`) VALUES ('".@mysql_real_escape_string($_resId)."','".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string($_value)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($_title)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($_type)."','0','".@mysql_real_escape_string($_parentId)."','".@mysql_real_escape_string($_rank)."','".@mysql_real_escape_string($_size)."')");
	else
	{
		queryDB(true,$result = "UPDATE `".DB_PREFIX.DATABASE_RESOURCES."` SET `value`='".@mysql_real_escape_string($_value)."',`editor`='".@mysql_real_escape_string($_userId)."',`title`='".@mysql_real_escape_string($_title)."',`edited`='".@mysql_real_escape_string(time())."',`discarded`='".@mysql_real_escape_string(parseBool($_disc,false))."',`parentid`='".@mysql_real_escape_string($_parentId)."',`rank`='".@mysql_real_escape_string($_rank)."',`size`='".@mysql_real_escape_string($_size)."' WHERE id='".@mysql_real_escape_string($_resId)."' LIMIT 1");
		if(!empty($_disc) && ($_type == RESOURCE_TYPE_FILE_INTERNAL || $_type == RESOURCE_TYPE_FILE_EXTERNAL) && @file_exists("./uploads/" . $_value) && strpos($_value,"..")===false)
			@unlink("./uploads/" . $_value);
	}
}

function getBrowserLocalization($country = "")
{
	global $LANGUAGES,$COUNTRIES;
	initData(false,false,false,false,false,true,true);
	$language = str_replace(array(",","_"," "),array(";","-",""),(!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) ? strtoupper($_SERVER["HTTP_ACCEPT_LANGUAGE"]) : "");
	if(strlen($language) > 5 || strpos($language,";") !== false)
	{
		$parts = explode(";",$language);
		if(count($parts) > 0)
			$language = $parts[0];
		else
			$language = substr($language,0,5);
	}
	if(strlen($language) >= 2)
	{
		$parts = explode("-",$language);
		if(!isset($LANGUAGES[$language]))
		{
			$language = $parts[0];
			if(!isset($LANGUAGES[$language]))
			{
				if(DEBUG_MODE)
					logit(@$_SERVER["HTTP_ACCEPT_LANGUAGE"] . " - " . $language,LIVEZILLA_PATH . "_log/missing_language.txt");
				$language = "";
			}
		}
		if(count($parts)>1 && isset($COUNTRIES[$parts[1]]))
			$country = $parts[1];
	}
	else if(strlen($language) < 2)
		$language = "";
	return array($language,$country);
}

function createFileBaseFolders($_owner,$_internal)
{
	if($_internal)
	{
		processResource($_owner,3,"%%_Files_%%",0,"%%_Files_%%",0,1,1);
		processResource($_owner,4,"%%_Internal_%%",0,"%%_Internal_%%",0,3,2);
	}
	else
	{
		processResource($_owner,3,"%%_Files_%%",0,"%%_Files_%%",0,1,1);
		processResource($_owner,5,"%%_External_%%",0,"%%_External_%%",0,3,2);
	}
}

function getSystemTimezone()
{
	global $CONFIG;
	
	if(!empty($CONFIG["gl_tizo"]))
		return $CONFIG["gl_tizo"];

    $iTime = time();
    $arr = @localtime($iTime);
    $arr[5] += 1900;
    $arr[4]++;
    $iTztime = @gmmktime($arr[2], $arr[1], $arr[0], $arr[4], $arr[3], $arr[5], $arr[8]);
    $offset = doubleval(($iTztime-$iTime)/(60*60));
    $zonelist =
    array
    (
        'Kwajalein' => -12.00,
        'Pacific/Midway' => -11.00,
        'Pacific/Honolulu' => -10.00,
        'America/Anchorage' => -9.00,
        'America/Los_Angeles' => -8.00,
        'America/Denver' => -7.00,
        'America/Tegucigalpa' => -6.00,
        'America/New_York' => -5.00,
        'America/Caracas' => -4.30,
        'America/Halifax' => -4.00,
        'America/St_Johns' => -3.30,
        'America/Argentina/Buenos_Aires' => -3.00,
        'America/Sao_Paulo' => -3.00,
        'Atlantic/South_Georgia' => -2.00,
        'Atlantic/Azores' => -1.00,
        'Europe/Dublin' => 0,
        'Europe/Belgrade' => 1.00,
        'Europe/Minsk' => 2.00,
        'Asia/Kuwait' => 3.00,
        'Asia/Tehran' => 3.30,
        'Asia/Muscat' => 4.00,
        'Asia/Yekaterinburg' => 5.00,
        'Asia/Kolkata' => 5.30,
        'Asia/Katmandu' => 5.45,
        'Asia/Dhaka' => 6.00,
        'Asia/Rangoon' => 6.30,
        'Asia/Krasnoyarsk' => 7.00,
        'Asia/Brunei' => 8.00,
        'Asia/Seoul' => 9.00,
        'Australia/Darwin' => 9.30,
        'Australia/Canberra' => 10.00,
        'Asia/Magadan' => 11.00,
        'Pacific/Fiji' => 12.00,
        'Pacific/Tongatapu' => 13.00
    );
    $index = array_keys($zonelist, $offset);
    if(sizeof($index)!=1)
        return false;
    return $index[0];
}

function isnull($_var)
{
	return empty($_var);
}
loadConfig();
?>
