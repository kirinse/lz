<?php
/****************************************************************************************
* LiveZilla objects.global.users.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
require(LIVEZILLA_PATH . "_lib/objects.global.inc.php");

class BaseUser
{
	public $IP;
	public $SessId;
	public $UserId;
	public $Language;
	public $SystemId;
	public $Messages = array();
	public $Status = USER_STATUS_OFFLINE;
	public $Type;
	public $Folder;
	public $SessionFile;
	public $FirstActive;
	public $LastActive;
	public $Fullname;
	public $Company;
	public $Question;
	public $Email;
	public $Typing = false;
	public $Customs;

	function BaseUser($_userid)
   	{
		$this->UserId = $_userid;
   	}
	
	function GetPosts()
	{
		$messageFileCount = 0;
		$rows = getPosts($this->SystemId);
		$posts = array();
		foreach($rows as $row)
		{
			array_push($posts,new Post($row));
			if(++$messageFileCount >= DATA_ITEM_LOADS)
				break;
		}
		return $posts;
	}
	
	function AppendFromCookies()
	{
		if(defined("CALLER_TYPE") && CALLER_TYPE != CALLER_TYPE_INTERNAL)
		{
			if(!isnull(getCookieValue("form_112")))
				$this->Email = (getCookieValue("form_112"));
			if(!isnull(getCookieValue("form_111")))
				$this->Fullname = (getCookieValue("form_111"));
			if(!isnull(getCookieValue("form_113")))
				$this->Company = (getCookieValue("form_113"));
		}
	}
}

class UserGroup
{
	public $Id;
	public $Descriptions;
	public $DescriptionArray;
	public $Description;
	public $IsExternal;
	public $IsInternal;
	public $IsStandard;
	public $PredefinedMessages;
	public $Created;
	public $Email;
	public $ChatFunctions;
	public $VisitorFilters;
	public $ChatInputsHidden;
	public $ChatInputsMandatory;
	public $TicketInputsHidden;
	public $TicketInputsMandatory;
	public $OpeningHours;

	function UserGroup($_id, $_values = null, $_config = null)
	{
		$this->Id = $_id;
		if(!empty($_values))
		{
			$this->Descriptions = unserialize(base64_decode($_values["gr_desc"]));
			$this->DescriptionArray = $_values["gr_desc"];
			
			if(defined("DEFAULT_BROWSER_LANGUAGE") && isset($this->Descriptions[strtoupper(DEFAULT_BROWSER_LANGUAGE)]))
				$this->Description = base64_decode($this->Descriptions[strtoupper(DEFAULT_BROWSER_LANGUAGE)]);
			else if(isset($this->Descriptions[strtoupper($_config["gl_default_language"])]))
				$this->Description = base64_decode($this->Descriptions[strtoupper($_config["gl_default_language"])]);
			else if(isset($this->Descriptions["EN"]))
				$this->Description = base64_decode($this->Descriptions["EN"]);
			else
				$this->Description =  base64_decode(current($this->Descriptions));
		
			$this->IsInternal = ($_values["gr_internal"] == 1);
			$this->IsExternal = ($_values["gr_external"] == 1);
			$this->IsStandard =  ($_values["gr_standard"] == 1);
			$this->Created = $_values["gr_created"];
			$this->OpeningHours = $_values["gr_hours"];
			$this->Email = $_values["gr_email"];
			$this->VisitorFilters = $_values["gr_vfilters"];
			$this->ChatFunctions = array($_values["gr_ex_sm"],$_values["gr_ex_so"],$_values["gr_ex_pr"],$_values["gr_ex_ra"],$_values["gr_ex_fv"],$_values["gr_ex_fu"]);
			$this->ChatInputsHidden = $_values["gr_ci_hidden"];
			$this->ChatInputsMandatory = $_values["gr_ci_mand"];
			$this->TicketInputsHidden = $_values["gr_ti_hidden"];
			$this->TicketInputsMandatory = $_values["gr_ti_mand"];
		}
		$this->LoadPredefinedMessages();
	}
	
	function IsOpeningHour()
	{
		global $INTERNAL;
		initData(true);
		$sofday = time() - mktime(0,0,0);
		foreach($this->OpeningHours as $hour)
			if(date("w") == $hour[0])
				if($sofday >= $hour[1] && $sofday <= $hour[2])
					return true;
		return (count($this->OpeningHours) == 0);
	}
	
	function LoadPredefinedMessages()
	{
		$this->PredefinedMessages = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `group_id`='".@mysql_real_escape_string($this->Id)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->PredefinedMessages[] = new PredefinedMessage($row);
	}
}

class Operator extends BaseUser
{
	public $Level = 0;
	public $Webspace = 0;
	public $LoginId;
	public $Password;
	public $PasswordFile;
	public $PasswordFileTXT;
	public $Description;
	public $LCAFile;
	public $Profile;
	public $ServerSetup = false;
	public $Authenticated = false;
	public $VisitorFileSizes;
	public $VisitorStaticReload;
	public $ExternalChats;
	public $PermissionSet;
	public $Groups;
	public $GroupsArray;
	public $PredefinedMessages;
	public $InExternalGroup;
	public $ProfilePicture;
	public $ProfilePictureTime;
	public $WebcamPicture;
	public $WebcamPictureTime;
	public $LastChatAllocation;
	public $FirstCall = true;
	public $CanAutoAcceptChats;
	public $LoginIPRange = "";
	
	function Operator($_sessid,$_userid)
   	{
		$this->LastActive = 0;
		$this->SystemId = $_sessid;
		$this->UserId = $_userid;
		$this->ExternalChats = array();
		$this->PasswordFile = PATH_USERS . $this->SystemId . FILE_EXTENSION_PASSWORD;
		$this->PasswordFileTXT = PATH_USERS . $this->SystemId . FILE_EXTENSION_PASSWORD_TXT;
		$this->ChangePasswordFile = PATH_USERS . $this->SystemId . FILE_EXTENSION_CHANGE_PASSWORD;
		$this->Type = USER_TYPE_INTERN;
		$this->VisitorFileSizes = array();
		$this->VisitorStaticReload = array();
		$this->Load();
   	}
	
	function Save()
	{
		if($this->FirstCall)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OPERATORS."` (`id`, `login_id`, `first_active`, `last_active`, `password`, `status`, `level`, `ip`, `typing`, `visitor_file_sizes`) VALUES ('".@mysql_real_escape_string($this->SystemId)."','".@mysql_real_escape_string($this->LoginId)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->Password)."', '".@mysql_real_escape_string($this->Status)."', '".@mysql_real_escape_string($this->Level)."', '".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string(($this->Typing)?1:0)."', '".@mysql_real_escape_string(serialize($this->VisitorFileSizes))."');");
		else
		{
			$ca = (count($this->ExternalChats)==0) ? ",`last_chat_allocation`=0":"";
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `first_active`='".@mysql_real_escape_string($this->FirstActive)."',`password`='".@mysql_real_escape_string($this->Password)."',`login_id`='".@mysql_real_escape_string($this->LoginId)."',`visitor_file_sizes`='".@mysql_real_escape_string(serialize($this->VisitorFileSizes))."',`typing`='".@mysql_real_escape_string(($this->Typing)?1:0)."',`level`='".@mysql_real_escape_string($this->Level)."',`status`='".@mysql_real_escape_string($this->Status)."',`ip`='".@mysql_real_escape_string($this->IP)."',`last_active`='".@mysql_real_escape_string(time())."'".$ca." WHERE `id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
		}
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_OPERATORS."` WHERE `id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->SetValues($row);
		$this->LoadPredefinedMessages();
	}
	
	function SetValues($_row)
	{
		global $CONFIG;
		$this->FirstCall = false;
		$this->LoginId = $_row["login_id"];
		$this->FirstActive = ($_row["first_active"]<(time()-$CONFIG["timeout_clients"]))?time():$_row["first_active"];
		$this->Password = $_row["password"];
		$this->Status = ($_row["last_active"]<(time()-$CONFIG["timeout_clients"]))?USER_STATUS_OFFLINE:$_row["status"];
		$this->Level = $_row["level"];
		$this->IP = $_row["ip"];
		$this->Typing = !empty($_row["typing"]);
		$this->VisitorFileSizes = unserialize($_row["visitor_file_sizes"]);
		$this->LastActive = $_row["last_active"];
		$this->LastChatAllocation = $_row["last_chat_allocation"];
	}
	
	function SetLastChatAllocation()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `last_chat_allocation`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATORS."` WHERE `id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
	}

	function GetExternalObjects()
	{
		global $CONFIG;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` AS `t2` ON `t1`.`chat_id`=`t2`.`chat_id` WHERE `t1`.`exit`=0 AND `t2`.`user_id`='".@mysql_real_escape_string($this->SystemId)."';");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$chat = new VisitorChat($row);
				if($chat->LastActive<(time()-$CONFIG["timeout_clients"]))
					$chat->ExternalClose();
				else
					$this->ExternalChats[$chat->SystemId] = $chat;
			}
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` WHERE `operator_id`='".@mysql_real_escape_string($this->SystemId)."' ORDER BY `created` ASC;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$request = new FileUploadRequest($row);
				$rsid = $request->SenderUserId . "~" . $request->SenderBrowserId;
				if(isset($this->ExternalChats[$rsid]) && $this->ExternalChats[$rsid]->Activated == CHAT_STATUS_ACTIVE && $this->ExternalChats[$rsid]->ChatId == $request->ChatId)
					$this->ExternalChats[$rsid]->FileUploadRequest = $request;
			}
	}
	
	function IsExternal($_groupList, $_exclude=null, $_include=null)
	{
		global $GROUPS;
		initData(false,true);
		foreach($this->Groups as $groupid)
		{
			if($GROUPS[$groupid]->IsOpeningHour())
				if($_groupList[$groupid]->IsExternal && !(!empty($_exclude) && in_array($groupid,$_exclude)) && !(!empty($_include) && !in_array($groupid,$_include)))
					return $this->InExternalGroup=true;
		}
		return $this->InExternalGroup=false;
	}
	
	function GetExternalChatAmount($amount=0)
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` AS `t2` ON `t1`.`chat_id`=`t2`.`chat_id` WHERE `t1`.`exit`=0 AND `t2`.`user_id`='".@mysql_real_escape_string($this->SystemId)."';");
		if($result)
			return @mysql_num_rows($results);
		return 0;
	}
	
	function LoadPredefinedMessages()
	{
		$this->PredefinedMessages = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `internal_id`='".@mysql_real_escape_string($this->SystemId)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->PredefinedMessages[] = new PredefinedMessage($row);
	}
	
	function LoadProfile()
	{
		$this->Profile = null;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PROFILES."` WHERE `id`='".@mysql_real_escape_string($this->SystemId)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Profile = new Profile($row);
	}
	
	function LoadPictures($_sessiontime=0)
	{
		$found = false;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `internal_id`='".@mysql_real_escape_string($this->SystemId)."' AND `time` >= ".@mysql_real_escape_string($_sessiontime));
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$found = true;
				if(empty($row["webcam"]))
				{
					$this->ProfilePicture = $row["data"];
					$this->ProfilePictureTime = $row["time"];
				}
				else
				{
					$this->WebcamPicture = $row["data"];
					$this->WebcamPictureTime = $row["time"];
				}
			}
		return $found;
	}

	function SaveLoginAttempt($_password)
	{
		if(!empty($this->LoginIPRange))
		{
			$match = false;
			$ranges = explode(",",$this->LoginIPRange);
			foreach($ranges as $range)
				if(@$_SERVER["REMOTE_ADDR"] == trim($range) || ipIsInRange(@$_SERVER["REMOTE_ADDR"],trim($range)))
					$match = true;
			if(!$match)
				return false;
		}
	
		$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE ip='".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."' AND `time` > '".@mysql_real_escape_string(time()-86400)."';");
		if(@mysql_num_rows($result) >= MAX_LOGIN_ATTEMPTS)
			return false;
		
		$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE ip='".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."' AND `time` > '".@mysql_real_escape_string(time()-86400)."' AND `password`='".@mysql_real_escape_string($_password)."';");
		if(@mysql_num_rows($result) == 0)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` (`id` ,`user_id` ,`ip` ,`time` ,`password`) VALUES ('".@mysql_real_escape_string(getId(32))."', '".@mysql_real_escape_string($this->UserId)."', '".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."', '".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($_password)."');");
		return true;
	}
	
	function DeleteLoginAttempts()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE ip='".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."';");
	}
	
	function LoadPassword()
	{
		$this->Password = null;
		if(@file_exists($this->PasswordFile))
		{
			require($this->PasswordFile);
			$this->Password = $passwd;
		}
		else if(@file_exists($this->PasswordFileTXT))
		{
			$data = getFile($this->PasswordFileTXT);
			$this->Password = $data;
		}
		return $this->Password;
	}
	
	function ChangePassword($_password)
	{
		createFile($this->PasswordFile,"<?php \$passwd=\"".md5($_password)."\"; ?>",true);
		if(@file_exists($this->ChangePasswordFile))
			@unlink($this->ChangePasswordFile);
		if(@file_exists($this->PasswordFileTXT))
			@unlink($this->PasswordFileTXT);
	}
	
	function IsPasswordChangeNeeded()
	{
		return @file_exists($this->ChangePasswordFile);
	}
	
	function SetPasswordChangeNeeded($_needed)
	{
		if($_needed)
			createFile($this->ChangePasswordFile,"",false);
		else if(@file_exists($this->ChangePasswordFile))
			@unlink($this->ChangePasswordFile);
	}
	
	function GetPermission($_type)
	{
		return substr($this->PermissionSet,$_type,1);
	}
	
	function GetOperatorPictureFile()
	{
		return "picture.php?intid=".base64UrlEncode($this->UserId)."&acid=".getId(3);
	}

	function GetLoginReply($_extern,$_time)
	{
		return "<login>\r\n<login_return group=\"".base64_encode($this->GroupsArray)."\" name=\"".base64_encode($this->Fullname)."\" loginid=\"".base64_encode($this->LoginId)."\" level=\"".base64_encode($this->Level)."\" sess=\"".base64_encode($this->SystemId)."\" extern=\"".base64_encode($_extern)."\" timediff=\"".base64_encode($_time)."\" time=\"".base64_encode(time())."\" perms=\"".base64_encode($this->PermissionSet)."\" sm=\"".base64_encode(SAFE_MODE)."\" phpv=\"".base64_encode(@phpversion())."\" sip=\"".base64_encode(@$_SERVER["SERVER_ADDR"])."\" /></login>";
	}
}

class Visitor extends BaseUser
{
	public $Browsers;
	public $Response;
	public $IsChat = false;
	public $ActiveChatRequest;
	public $SystemInfo;
	public $Resolution;
	public $Host;
	public $Email;
	public $Company;
	public $Visits = 1;
	public $VisitsDay = 1;
	public $VisitId;
	public $VisitLast;
	public $GeoCity;
	public $GeoCountryName;
	public $GeoCountryISO2;
	public $GeoRegion;
	public $GeoLongitude= -522;
	public $GeoLatitude= -522;
	public $GeoTimezoneOffset = "+00:00";
	public $GeoISP;
	public $GeoResultId = 0;
	public $StaticInformation = false;
	public $ExitTime;
	public $Browser;
	public $OperatingSystem;
	public $Javascript;
	public $Signature;
	public $SignatureMismatch;
	public $IsCrawler;
	public $ExtendSession = false;
	public $FirstCall = true;
	public $HasAcceptedChatRequest;
	public $HasDeclinedChatRequest;

	// debug
	public $Debug = 0;
	
	function Visitor()
   	{
		$this->VisitId = getId(7);
		$this->Browsers = Array();
		$this->UserId = func_get_arg(0);
		$this->FirstActive = time();
		$this->VisitLast = time();
   	}
	
	function Load()
	{
		if(func_num_args() == 1)
		{
			$this->SetDetails(func_get_arg(0),false);
		}
		else
		{
			$result = queryDB(true,"SELECT *,(SELECT count(*) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id`='".@mysql_real_escape_string($this->UserId)."') as `dcount` FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `entrance` DESC;");
			if(@mysql_num_rows($result) >= 1)
				$this->SetDetails(mysql_fetch_array($result, MYSQL_BOTH),true);
		}
	}
	
	function SetDetails($_data,$_self)
	{
		global $CONFIG;
		$this->FirstCall = ($_data["last_active"] < (time()-((!empty($_data["js"])) ? $CONFIG["timeout_track"] : 7200)) && !$this->ExtendSession);
		$this->VisitId = $_data["visit_id"];
		
		if($_self && $this->FirstCall)
		{
			$this->Visits = $_data["visits"]+1;
			$this->VisitId = $_data["visit_id"]=getId(7);
			$this->VisitsDay = $_data["dcount"]+1;
		}
		else
		{
			$this->Visits =	$_data["visits"];
			$this->VisitsDay = $_data["dcount"];
		}
		$this->VisitLast = $_data["visit_last"];
		$this->ExitTime = $_data["last_active"];
		$this->IP = $_data["ip"];
		$this->SystemInfo = $_data["system"];
		$this->Language = $_data["language"];
		$this->Resolution = $_data["resolution"];
		$this->Host = $_data["host"];
		$this->GeoTimezoneOffset = $_data["timezone"];
		if(!empty($_data["longitude"]))
		{
			$this->GeoLongitude = $_data["longitude"];
			$this->GeoLatitude = $_data["latitude"];
		}
		$this->GeoCity = $_data["city"];
		$this->GeoCountryISO2 = $_data["country"];
		if(isset($_data["countryname"]))
			$this->GeoCountryName = $_data["countryname"];
		$this->GeoRegion = $_data["region"];

		$this->GeoResultId = $_data["geo_result"];
		$this->GeoISP = $_data["isp"];
		$this->FirstActive = $_data["entrance"];
		$this->Browser = $_data["browser"];
		$this->OperatingSystem = $_data["system"];
		$this->Javascript = $_data["js"];
	}
	
	function LoadBrowsers($_outdated=false)
	{
		global $CONFIG;
		$this->Browsers = Array();
		$limiter = (!$_outdated) ? " AND `last_active` > ".(time()-$CONFIG["timeout_track"])." " : "";
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `visit_id`='".@mysql_real_escape_string($this->VisitId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."'".$limiter."ORDER BY `created` ASC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(empty($row["is_chat"]))
				{
					$browser = new VisitorBrowser($row["id"],$row["visitor_id"]);
					$browser->Query = (!empty($row["query"])) ? getIdValue(DATABASE_VISITOR_DATA_QUERIES,"query",$row["query"]) : "";
					$browser->Email = $row["email"];
					$browser->Fullname = $row["fullname"];
					$browser->Company = $row["company"];
					$browser->Customs = @unserialize($row["customs"]);
					$browser->LastUpdate = $row["last_update"];
				}
				else
				{
					$browser = new VisitorChat($row["visitor_id"],$row["id"]);
					$browser->Load();
				}
				if(count($browser->History) > 0)
				{
					$this->Browsers[$row["id"]] = $browser;
					$this->Browsers[$row["id"]]->LastActive = $row["last_active"];
				}
			}
		}
	}
	
	function IsInChatWith($_operator)
	{
		foreach($this->Browsers as $browser)
			if($browser->Type == BROWSER_TYPE_CHAT)
				if(in_array($_operator->SystemId,$browser->ChatRequestReceiptants) || $browser->DesiredChatPartner == $_operator->SystemId || in_array($browser->DesiredChatGroup,$_operator->Groups))
					return true;
		return false;
	}
	
	function KeepAlive()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITORS."` SET `last_active`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `entrance` DESC LIMIT 1;");
	}
	
	function Save($_config,$_resolution,$_color,$_timezone,$_lat,$_long,$_countryiso2,$_city,$_region,$_geotimezone,$_isp,$_geosspan,$_grid,$_js=true,$_fromCookie=false)
	{
		global $STATS,$COUNTRIES,$COUNTRY_ALIASES;
		if(!$this->FirstCall)
		{
			$this->KeepAlive();
		}
		else
		{
			if(!isnull(getCookieValue("visits")) && $this->Visits==1)
				$this->Visits = getCookieValue("visits")+1;
			setCookieValue("visits",$this->Visits);
			if(!isnull(getCookieValue("last_visit")))
				$this->VisitLast = getCookieValue("last_visit");
			setCookieValue("last_visit",time());

			$this->IP = getIP();
			$this->SystemInfo = ((!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : "");
			
			$localization = getBrowserLocalization();
			$this->Language = $localization[0];
			$this->GeoCountryISO2 = $localization[1];
			
			$this->Resolution = (!empty($_resolution) && count($_resolution) == 2 && !empty($_resolution[0]) && !empty($_resolution[1])) ? $_resolution[0] . " x " . $_resolution[1] : "";
			$this->Resolution .= (!empty($_color)) ? " (" . $_color . " Bit)" : "";
			$this->GeoTimezoneOffset = getLocalTimezone($_timezone);
			$this->GeoResult = 0;
			
			if(!empty($_geosspan))
				createSSpanFile($_geosspan);

			if(!empty($_config["gl_pr_ngl"]) && $_js)
			{
				if(!empty($_lat) && base64_decode($_lat) > -180)
				{
					setCookieValue(GEO_LATITUDE,$this->GeoLatitude = base64_decode($_lat));
					setCookieValue(GEO_LONGITUDE,$this->GeoLongitude = base64_decode($_long));
					setCookieValue(GEO_COUNTRY_ISO_2,$this->GeoCountryISO2 = base64_decode($_countryiso2));
					setCookieValue(GEO_CITY,$this->GeoCity = base64_decode($_city));
					setCookieValue(GEO_REGION,$this->GeoRegion = base64_decode($_region));
					setCookieValue(GEO_TIMEZONE,$this->GeoTimezoneOffset = base64_decode($_geotimezone));
					setCookieValue(GEO_ISP,$this->GeoISP = utf8_decode(base64_decode($_isp)));
					setCookieValue("geo_data",time());
				}
				else if(isset($_lat) && !empty($_lat))
				{
					$this->GeoLatitude = base64_decode($_lat);
					$this->GeoLongitude = base64_decode($_long);
				}
				else if(!isnull(getCookieValue("geo_data")) && !isnull(getCookieValue(GEO_LATITUDE)))
				{
					$this->GeoLatitude = getCookieValue(GEO_LATITUDE);
					$this->GeoLongitude = getCookieValue(GEO_LONGITUDE);
					$this->GeoCountryISO2 = getCookieValue(GEO_COUNTRY_ISO_2);
					$this->GeoCity = getCookieValue(GEO_CITY);
					$this->GeoRegion = getCookieValue(GEO_REGION);
					$this->GeoTimezoneOffset = getCookieValue(GEO_TIMEZONE);
					$this->GeoISP = getCookieValue(GEO_ISP);
					$_fromCookie = true;
				}

				removeSSpanFile(false);
				if($_fromCookie)
					$this->GeoResultId = 6;
				else if(!isnull($span=getSpanValue()))
				{
					if($span > (time()+CONNECTION_ERROR_SPAN))
						$this->GeoResultId = 5;
					else
						$this->GeoResultId = 4;
				}
				else
				{
					if(base64_decode($_lat) == -777)
						$this->GeoResultId = 5;
					else if(base64_decode($_lat) == -522)
						$this->GeoResultId = 2;
					else if($_grid != 4)
						$this->GeoResultId = 3;
					else
						$this->GeoResultId = $_grid;
				}
			}
			else
				$this->GeoResultId = 7;
				
			initData(false,false,false,false,false,false,true);
			if(isset($COUNTRY_ALIASES[$this->GeoCountryISO2]))
				$this->GeoCountryISO2 = $COUNTRY_ALIASES[$this->GeoCountryISO2];
			else if(!isset($COUNTRIES[$this->GeoCountryISO2]) && DEBUG_MODE)
				logit($this->GeoCountryISO2,LIVEZILLA_PATH  . "_log/unknown_countries.txt");
			
			$detector = new DeviceDetector();
			$detector->DetectBrowser();

			if($detector->AgentType == AGENT_TYPE_BROWSER || $detector->AgentType == AGENT_TYPE_UNKNOWN)
			{
				$detector->DetectOperatingSystem();
				if(DEBUG_MODE && !empty($_SERVER["HTTP_USER_AGENT"]))
				{
				 	if($detector->OperatingSystemUnknown)
						logit("OS UNKNOWN: ".$_SERVER["HTTP_USER_AGENT"],LIVEZILLA_PATH  . "_log/unknown_os.txt");
					else if($detector->AgentType == AGENT_TYPE_UNKNOWN)
						logit("AGENT UNKNOWN: ".$_SERVER["HTTP_USER_AGENT"],LIVEZILLA_PATH  . "_log/unknown_ag.txt");
				}

				$bid = $this->GetBrowserId($detector->Browser,$detector->AgentType);
				$oid = $this->GetOSId($detector->OperatingSystem);
				$row = $this->CreateSignature();

				if(is_array($row) && $row["id"] != $this->UserId)
				{
					$this->UserId = $row["id"];
					$this->SignatureMismatch = true;
				}
				else
				{	
					queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITORS."` (`id`, `entrance`,`last_active`, `host`,`ip`,`system`,`browser`, `visits`,`visit_id`,`visit_last`,`resolution`, `language`, `country`, `city`, `region`, `isp`, `timezone`, `latitude`, `longitude`, `geo_result`, `js`, `signature`) VALUES ('".@mysql_real_escape_string($this->UserId)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->Host)."', '".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($oid)."','".@mysql_real_escape_string($bid)."', '".@mysql_real_escape_string($this->Visits)."', '".@mysql_real_escape_string($this->VisitId)."','".@mysql_real_escape_string($this->VisitLast)."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_RESOLUTIONS,"resolution",$this->Resolution, false, 32))."', '".@mysql_real_escape_string(substr(strtoupper($this->Language),0,5))."','".@mysql_real_escape_string($this->GeoCountryISO2)."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_CITIES,"city",$this->GeoCity,false))."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_REGIONS,"region",$this->GeoRegion,false))."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_ISPS,"isp",utf8_encode($this->GeoISP),false))."', '".@mysql_real_escape_string($this->GeoTimezoneOffset)."', '".@mysql_real_escape_string($this->GeoLatitude)."', '".@mysql_real_escape_string($this->GeoLongitude)."', '".@mysql_real_escape_string($this->GeoResultId)."', '".@mysql_real_escape_string($_js?1:0)."', '".@mysql_real_escape_string($this->Signature)."');");
					if(mysql_affected_rows() == 1)
						queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITORS."` SET `visit_latest`=0 WHERE `id`='".@mysql_real_escape_string($this->UserId)."' AND `visit_id`!='".@mysql_real_escape_string($this->VisitId)."';");
				}
			}
			else if(STATS_ACTIVE)
			{
				$this->IsCrawler = true;
				$STATS->ProcessAction(ST_ACTION_LOG_CRAWLER_ACCESS,array($this->GetCrawlerId($detector->Browser),null));
			}
		}
	}
	
	function ResolveHost()
	{
		$this->Host = getHost();
		if(!empty($this->Host) && $this->Host != $this->IP)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITORS."` SET `host`='".@mysql_real_escape_string($this->Host)."' WHERE `id`='".@mysql_real_escape_string($this->UserId)."' AND `visit_latest`=1;");
	}
	
	function CreateSignature()
	{
		$this->Signature = (!empty($_SERVER["HTTP_USER_AGENT"])) ? md5(getIP() . $_SERVER["HTTP_USER_AGENT"]) : md5(getIP());
		$row = mysql_fetch_array(queryDB(true,"SELECT `t1`.`id`,`t2`.`customs`,`t2`.`fullname`,`t2`.`email`,`t2`.`company` FROM `".DB_PREFIX.DATABASE_VISITORS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` AS `t2` ON `t1`.`id`=`t2`.`visitor_id` WHERE `t1`.`signature`='".@mysql_real_escape_string($this->Signature)."' ORDER BY `t2`.`fullname` DESC;"), MYSQL_BOTH);
		return $row;
	}
	
	function GetCrawlerId($_crawler)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_CRAWLERS."` (`id`, `crawler`) VALUES (NULL, '".@mysql_real_escape_string($_crawler)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_CRAWLERS."` WHERE `crawler`='".@mysql_real_escape_string($_crawler)."';"), MYSQL_BOTH);
		return $row["id"];
	}

	function GetOSId($_osname)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_SYSTEMS."` (`id`, `system`) VALUES (NULL, '".@mysql_real_escape_string($_osname)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_SYSTEMS."` WHERE `system`='".@mysql_real_escape_string($_osname)."';"), MYSQL_BOTH);
		return $row["id"];
	}
	
	function GetBrowserId($_browser,$_type)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_BROWSERS."` (`id`, `browser`) VALUES (NULL, '".@mysql_real_escape_string($_browser)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_BROWSERS."` WHERE `browser`='".@mysql_real_escape_string($_browser)."';"), MYSQL_BOTH);
		return $row["id"];
	}

	function SaveTicket($_group,$_config)
	{
		$ticket = new UserTicket(getTicketId(),true);
		$ticket->IP = getIP();
		
		setCookieValue("form_111",AJAXDecode($_POST[POST_EXTERN_USER_NAME]));
		setCookieValue("form_112",AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]));
		setCookieValue("form_113",AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]));

		if(!isTicketFlood())
		{
			$ticket->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
			$ticket->UserId = AJAXDecode($_POST[POST_EXTERN_USER_USERID]);
			$ticket->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$ticket->Group = $_group;
			$ticket->Company = AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$ticket->Text = AJAXDecode($_POST[POST_EXTERN_USER_MAIL]);
			
			for($i=0;$i<10;$i++)
				if(isset($_POST["p_cf".$i]) && isset($_config["gl_ci_list"][$i]) && !isset($_group->TicketInputsHidden[$i]))
					$ticket->Customs[$i]=base64UrlDecode($_POST["p_cf".$i]);
			
			if(!(!empty($_config["gl_rm_om"]) && $_config["gl_rm_om_time"] == 0))
				$ticket->Save();
			$this->AddFunctionCall("lz_chat_mail_callback(true);",false);
			return true;
		}
		else
			$this->AddFunctionCall("lz_chat_mail_callback(false);",false);
		return false;
	}
	
	function SendCopyOfMail($_group,$_config,$_groups)
	{
		$message = getFile(TEMPLATE_EMAIL_MAIL);
		if(empty($_config["gl_pr_nbl"]))
			$message .= base64_decode("DQoNCg0KcG93ZXJlZCBieSBMaXZlWmlsbGEgTGl2ZSBTdXBwb3J0IFtodHRwOi8vd3d3LmxpdmV6aWxsYS5uZXRd");
		$message = str_replace("<!--date-->",date("r"),$message);
		$message = str_replace("<!--name-->",AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$message);
		$message = str_replace("<!--email-->",AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message);
		$message = str_replace("<!--company-->",AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]),$message);
		$message = str_replace("<!--mail-->",AJAXDecode($_POST[POST_EXTERN_USER_MAIL]),$message);
		$message = str_replace("<!--group-->",$_groups[$_group]->Description,$message);
		$sender = (!empty($_config["gl_usmasend"]) && isValidEmail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL])) && empty($_config["gl_smtpauth"])) ? AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]) : $_config["gl_mail_sender"];
		if(!empty($_config["gl_scom"]))
			sendMail($_config["gl_scom"],$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,getSubject(false,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$_group,""));
		if(!empty($_config["gl_sgom"]))
			sendMail($_groups[$_group]->Email,$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,getSubject(false,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$_group,""));
		if(!empty($_config["gl_ssom"]) && isValidEmail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL])))
			sendMail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,getSubject(false,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$_group,""));
	}
	
	function StoreFile($_browserId,$_partner,$_fullname)
	{
		$filename = namebase($_FILES['userfile']['name']);
		if(!isValidUploadFile($filename))
			return false;
		$fileid = md5($filename . $this->UserId . $_browserId);
		$fileurid = EX_FILE_UPLOAD_REQUEST . "_" . $fileid;
		$filemask = $this->UserId . "_" . $fileid;
		$request = new FileUploadRequest($fileurid,$_partner);
		$request->Load();

		if($request->Permission == PERMISSION_FULL)
		{
			if(move_uploaded_file($_FILES["userfile"]["tmp_name"], PATH_UPLOADS . $request->FileMask))
			{
				createFileBaseFolders($_partner,false);
				processResource($_partner,$this->UserId,$_fullname,0,$_fullname,0,5,3);
				processResource($_partner,$fileid,$filemask,4,$_FILES["userfile"]["name"],0,$this->UserId,4,$_FILES["userfile"]["size"]);
				
				$request->Download = true;
				$request->Save();
				return true;
			}
			else
			{
				$request->Error = true;
				$request->Save();
			}
		}
		return false;
	}
	
	function SaveRate($_internalId,$_config)
	{
		$rate = new Rating(time() . "_" . getIP());
		if(!$rate->IsFlood())
		{
			$rate->RateComment = AJAXDecode($_POST[POST_EXTERN_RATE_COMMENT]);
			$rate->RatePoliteness = AJAXDecode($_POST[POST_EXTERN_RATE_POLITENESS]);
			$rate->RateQualification = AJAXDecode($_POST[POST_EXTERN_RATE_QUALIFICATION]);
			$rate->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
			$rate->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$rate->Company = AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$rate->UserId = AJAXDecode($_POST[POST_EXTERN_USER_USERID]);
			$rate->InternId = $_internalId;
			if(!(!empty($_config["gl_rm_rt"]) && $_config["gl_rm_rt_time"] == 0))
				saveRating($rate);
			$this->AddFunctionCall("lz_chat_send_rate_callback(true);",false);
		}
		else
			$this->AddFunctionCall("lz_chat_send_rate_callback(false);",false);
	}
	
	function AddFunctionCall($_call,$_overwrite)
	{
		if(empty($this->Response))
			$this->Response = "";
		if($_overwrite)
			$this->Response = $_call;
		else
			$this->Response .= $_call;
	}
	
	function IsActive()
	{
		global $CONFIG;
		$active = false;
		foreach($this->Browsers as $browserId => $BROWSER)
			if($BROWSER->History[count($BROWSER->History)-1]->Entrance >= (time()-($CONFIG["gl_inti"]*60)))
			{
				$active = true;
				break;
			}
			
		if(!$active)
			return $this->IsInChat();
		else
			return true;
	}
	
	function IsInChat()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` AS `t1` WHERE `t1`.`exit`=0 AND `t1`.`visitor_id`='".@mysql_real_escape_string($this->UserId)."';");
		return (@mysql_num_rows($result) > 0);
	}

	function WasInChat()
	{
		$result = queryDB(true,"SELECT `chat_id` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `external_id` = '".@mysql_real_escape_string($this->UserId)."' LIMIT 1");
		if(@mysql_num_rows($result) > 0)
			return true;
		else
			return $this->IsInChat();
	}
	
	function GetChatRequestResponses()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `closed` ASC,`created` DESC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(!empty($row["declined"]))
					$this->HasDeclinedChatRequest = true;
				if(!empty($row["accepted"]))
					$this->HasAcceptedChatRequest = true;
			}
		}
	}
}

class VisitorBrowser extends BaseUser
{
	public $BrowserId;
	public $History;
	public $ChatRequest;
	public $WebsitePush;
	public $Alert;
	public $Type = BROWSER_TYPE_BROWSER;
	public $Query;
	public $VisitId;
	public $LastUpdate;
	private $FirstCall = true;
	
	function VisitorBrowser($_browserid,$_userid)
   	{
		$this->BrowserId = $_browserid;
		$this->UserId = $_userid;
		$this->SystemId = $this->UserId . "~" . $this->BrowserId;
		$this->LoadHistory();
		$this->FirstCall = (count($this->History)==0);
   	}
	
	function IsFirstCall()
	{
		return $this->FirstCall;
	}
	
	function LoadHistory()
	{
		if($result = queryDB(true,"SELECT `trefcode`.`area_code` as `ref_area_code`,`turlcode`.`area_code` as `url_area_code`,`turltitle`.`title` as `url_title`,`treftitle`.`title` as `ref_title`,`turldom`.`domain` as `url_dom`,`turlpath`.`path` as `url_path`,`trefdom`.`domain` as `ref_dom`,`trefpath`.`path` as `ref_path`,`entrance`,`params`,`untouched` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `turl` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`url`=`turl`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `tref` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer`=`tref`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `trefdom` ON `tref`.`domain`=`trefdom`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `turldom` ON `turl`.`domain`=`turldom`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` AS `trefpath` ON `tref`.`path`=`trefpath`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` AS `turlpath` ON `turl`.`path`=`turlpath`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` AS `treftitle` ON `tref`.`title`=`treftitle`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` AS `turltitle` ON `turl`.`title`=`turltitle`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_AREA_CODES."` AS `trefcode` ON `tref`.`area_code`=`trefcode`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_AREA_CODES."` AS `turlcode` ON `turl`.`area_code`=`turlcode`.`id` WHERE `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance` ASC;"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->History[] = new HistoryURL($row);
	}

	function LoadChatRequest()
	{
		$count = 0;
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `closed` ASC,`created` DESC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(!empty($row["declined"]))
					$this->HasDeclinedChatRequest = true;
				if(!empty($row["accepted"]))
					$this->HasAcceptedChatRequest = true;
				if(++$count == count($result))
					$this->ChatRequest = new ChatRequest($row);
			}
		}
	}
	
	function LoadAlerts()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `accepted` ASC,`created` ASC;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Alert = new Alert($row);
	}
	
	function LoadWebsitePush()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `displayed` ASC,`accepted` ASC,`declined` ASC,`created` ASC LIMIT 1;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->WebsitePush = new WebsitePush($row);
	}
	
	function SetQuery($_referrer,$issearchengine=false,$parammatch=false)
	{
		$parts = parse_url(strtolower($_referrer));
		$uparts = explode("&",@$parts["query"]);
		foreach(HistoryUrl::$SearchEngines as $sparam => $engines)
			foreach($uparts as $param)
			{
				$kv = explode("=",$param);
				$parammatch = ($kv[0] == $sparam && !empty($kv[1]));
				
				foreach($engines as $engine)
				{
					if(compareUrls($engine,$parts["host"]))
						$issearchengine = true;
						
					if($issearchengine && $parammatch)
					{
						$this->Query = urldecode(trim($kv[1]));
						queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `query`='".@mysql_real_escape_string($this->GetQueryId($this->Query,$_referrer))."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' LIMIT 1;");
						return true;
					}
				}
			}
		return $issearchengine;
	}
	
	function GetQueryId($_query,$_referrer,$_maxlength=255)
	{
		if($_maxlength != null && strlen($_query) > $_maxlength)
			$_query = substr($_query,0,$_maxlength);
		
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."` (`id`, `query`) VALUES (NULL, '".@mysql_real_escape_string($_query)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."` WHERE `query`='".@mysql_real_escape_string($_query)."';"), MYSQL_BOTH);
		return $row["id"];
	}
	
	function ForceUpdate()
	{
		$this->LastUpdate = substr(md5(time()),0,2);
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `last_update`='".@mysql_real_escape_string($this->LastUpdate)."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' LIMIT 1;");
	}
	
	function Save()
	{
		$_parent = (func_num_args() > 0) ? func_get_arg(0) : null;
		$_url = (func_num_args() > 1) ? func_get_arg(1) : null;
		if(!($this->FirstCall && $res = queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` (`id`, `visitor_id`, `visit_id`, `created`, `last_active`, `last_update`, `is_chat`,`customs`,`fullname`,`email`,`company`) VALUES ('".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->VisitId)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(substr(md5(time()),0,2))."','".@mysql_real_escape_string($this->Type)."','".@mysql_real_escape_string(serialize($this->Customs))."','".@mysql_real_escape_string($this->Fullname)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."');")))
			if(!$this->FirstCall)
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `last_active`=".time()." WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' LIMIT 1;");
	}
	
	function Destroy()
	{
		global $CONFIG;
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `last_active`=`last_active`-".@mysql_real_escape_string($CONFIG["timeout_track"])." WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' LIMIT 1;");
	}
}

class VisitorChat extends VisitorBrowser
{
	public $DesiredChatGroup;
	public $DesiredChatPartner;
	public $Forward;
	public $Waiting;
	public $Chat;
	public $Code = "";
	public $Type = BROWSER_TYPE_CHAT;
	public $ConnectingMessageDisplayed = null;
	public $ChatRequestReceiptants;
	public $TranscriptEmail;
	public $ChatId;
	public $Activated;
	public $Closed;
	public $Declined;
	public $MemberCount;
	public $TargetFileExternal;
	public $TargetFileInternal;
	public $InternalActivation;
	public $ExternalActivation;
	public $ExternalClosed;
	public $InternalClosed;
	public $InternalUser;
	public $FileUploadRequest = null;
	public $LastActive=0;
	public $FirstCall = true;

	function VisitorChat()
   	{
		if(func_num_args() == 2)
		{
			$this->UserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$this->FirstCall = true;
		}
		else if(func_num_args() == 1)
		{
			$this->SetValues(func_get_arg(0));
		}
		parent::__construct($this->BrowserId,$this->UserId);
   	}
	
	function SetCookieGroup()
	{
		setCookieValue("login_group",$this->DesiredChatGroup);
	}
	
	function RequestFileUpload($_user,$_filename)
	{
		$fileid = md5(namebase($_filename) . $this->UserId . $this->BrowserId);
		$filemask = $this->UserId . "_" . $fileid;
		$fileurid = EX_FILE_UPLOAD_REQUEST . "_" . $fileid;
		$request = new FileUploadRequest($fileurid,$this->DesiredChatPartner);
		$request->SenderUserId = $this->UserId;
		$request->FileName = namebase($_filename);
		$request->FileMask = $filemask;
		$request->FileId = $fileid;
		$request->ChatId = $this->ChatId;
		$request->SenderBrowserId = $this->BrowserId;
		$request->Load();
		
		if(!$request->FirstCall && !$request->Closed)
		{
			if($request->Permission == PERMISSION_FULL)
			{
				$_user->AddFunctionCall("top.lz_chat_file_start_upload('".$_filename."');",false);
			}
			else if($request->Permission == PERMISSION_NONE)
			{
				$_user->AddFunctionCall("top.lz_chat_file_stop();",false);
				$_user->AddFunctionCall("top.lz_chat_file_error(1);",false);
				$request->Close();
			}
		}
		else
		{
			$request->FirstCall = true;
			$request->Error = false;
			$request->Closed = false;
			$request->Permission = PERMISSION_VOID;
			if(!isValidUploadFile($_filename))
				$_user->AddFunctionCall("top.lz_chat_file_error(2);",false);
			else
				$request->Save();
		}
		return $_user;
	}
	
	function AbortFileUpload($_user,$_filename,$_error)
	{
		$fileid = md5(namebase($_filename) . $this->UserId . $this->BrowserId);
		$request = new FileUploadRequest(EX_FILE_UPLOAD_REQUEST . "_" . $fileid, $this->DesiredChatPartner);
		$request->Load();
		if(!$request->Closed)
		{
			$request->Error = $_error;
			$request->Save();
		}
		else
		{
			$_user->AddFunctionCall("top.lz_chat_file_reset();",false);
		}
		return $_user;
	}
	
	function Load()
	{
		global $INTERNAL;
		$this->Status = CHAT_STATUS_OPEN;
		$this->AppendFromCookies();
		$this->LastActive = time();
		$this->ChatRequestReceiptants = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `first_active` DESC LIMIT 2;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(empty($row["external_close"]))
				{
					$this->FirstCall = !empty($row["exit"]);
					$this->SetValues($row);
				}
				else if(!empty($row["request_operator"]) && empty($this->DesiredChatPartner))
					$this->DesiredChatPartner = $row["request_operator"];
			}
		initData(true);
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `declined`=0;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if(isset($INTERNAL[$row["user_id"]]))
			{
				$this->ChatRequestReceiptants[] = $row["user_id"];
				$this->InternalUser = $INTERNAL[$row["user_id"]];
			}
	}
	
	function SetValues($row)
	{
		$this->LastActive = $row["last_active"];
		$this->Fullname = $row["fullname"];
		$this->Company = $row["company"];
		$this->Email = $row["email"];
		$this->Waiting = $row["waiting"];
		$this->FirstActive = $row["first_active"];
		$this->Typing = !empty($row["typing"]);
		$this->Code = $row["area_code"];
		$this->ChatId = $row["chat_id"];
		$this->VisitId = $row["visit_id"];
		$this->DesiredChatPartner = $row["request_operator"];
		$this->DesiredChatGroup = $row["request_group"];
		$this->Question = $row["question"];
		$this->SetTranscriptEmail();
		$this->InternalActivation = !empty($row["internal_active"]);
		$this->Declined = !empty($row["internal_declined"]);
		$this->Closed = !empty($row["exit"]);
		$this->ExternalActivation = !empty($row["external_active"]);
		$this->ExternalClosed = !empty($row["external_close"]);
		$this->InternalClosed = !empty($row["internal_closed"]);
		$this->LastActive = $row["last_active"];
		$this->UserId = $row["visitor_id"];
		$this->BrowserId = $row["browser_id"];
		$this->Status = $row["status"];
		$this->Customs = @unserialize($row["customs"]);
		$this->Activated = (($this->ExternalActivation && $this->InternalActivation) ? CHAT_STATUS_ACTIVE : (($this->ExternalActivation || $this->InternalActivation) ? CHAT_STATUS_WAITING : CHAT_STATUS_OPEN));
	
		if(!empty($this->ChatId))
		{
			$this->LoadForward();
		}
	}
	
	function SetChatId()
	{
		if(isset($_POST[POST_EXTERN_CHAT_ID]) && $this->Status != CHAT_STATUS_OPEN)
		{
			$this->ChatId = AJAXDecode($_POST[POST_EXTERN_CHAT_ID]);
		}
		else
		{
			$result = queryDB(true,"SELECT `chat_id` FROM `".DB_PREFIX.DATABASE_INFO."`");
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			$cid = $row["chat_id"]+1;
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INFO."` SET `chat_id`='".@mysql_real_escape_string($cid)."' WHERE `chat_id`='".@mysql_real_escape_string($row["chat_id"])."'");
			if(mysql_affected_rows() == 0)
			{
				$this->ChatId = $this->SetChatId();
				return;
			}
			else
			{
				$this->ChatId = $cid;
			}
		}
		$this->FirstActive = time();
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `last_active`='".@mysql_real_escape_string(time())."',`first_active`='".@mysql_real_escape_string(time())."',`chat_id`='".@mysql_real_escape_string($this->ChatId)."' WHERE `exit`=0 AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `first_active` DESC LIMIT 1;");
		return $this->ChatId;
	}
	
	function SetStatus($_status)
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `last_active`='".@mysql_real_escape_string(time())."',`status`='".@mysql_real_escape_string($_status)."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
	}
	
	function SetWaiting($_waiting)
	{
		$this->Waiting=$_waiting;
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `waiting`='".@mysql_real_escape_string((($_waiting)?1:0))."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
	}
	
	function SetTranscriptEmail()
	{
		global $CONFIG;
		if(isset($_POST["p_tc_declined"]))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `transcript_receiver`='' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
		else if(isset($_POST["p_tc_email"]))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `transcript_receiver`='".@mysql_real_escape_string(base64UrlDecode($_POST["p_tc_email"]))."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
	}
	
	function LoadForward()
	{
		$this->Forward = null;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' AND `received`=0 ORDER BY `created` DESC LIMIT 1;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$this->Forward = new Forward($row);
	}
	
	function CreateChat($_internalUser,$_visitor)
	{
		global $CONFIG;
		$this->InternalUser = $_internalUser;
		$this->InternalUser->SetLastChatAllocation();
		$this->SetStatus(CHAT_STATUS_WAITING);
		
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` (`chat_id`,`user_id`) VALUES ('".@mysql_real_escape_string($this->ChatId)."','".@mysql_real_escape_string($this->InternalUser->SystemId)."');");
		$customs = array();
		if(is_array($this->Customs))
			foreach($this->Customs as $cind => $value)
				if(!empty($value) && isset($CONFIG["gl_cf_list"][$cind]))
					$customs[$CONFIG["gl_cf_list"][$cind]] = $value;
					
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
		if($result && @mysql_num_rows($result) == 0)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` (`time`,`endtime`,`closed`,`chat_id`,`external_id`,`fullname`,`internal_id`,`group_id`,`area_code`,`html`,`plain`,`email`,`company`,`iso_language`,`iso_country`,`host`,`ip`,`gzip`,`transcript_sent`,`transcript_receiver`,`question`,`customs`) VALUES ('".@mysql_real_escape_string($this->FirstActive)."',0,0,'".@mysql_real_escape_string($this->ChatId)."','".@mysql_real_escape_string($this->ExternalUser->UserId)."','','','','".@mysql_real_escape_string($this->Code)."','','','','','".@mysql_real_escape_string($_visitor->Language)."','".@mysql_real_escape_string($_visitor->GeoCountryISO2)."','','',0,0,'".@mysql_real_escape_string($this->ExternalUser->Email)."','','".@mysql_real_escape_string(@serialize($customs))."');");
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `customs`='".@mysql_real_escape_string(serialize($this->Customs))."',`fullname`='".@mysql_real_escape_string($this->Fullname)."',`email`='".@mysql_real_escape_string($this->Email)."',`company`='".@mysql_real_escape_string($this->Company)."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."';");
	}
	
	function GetLastInvitationSender()
	{
		$result = queryDB(true,"SELECT `sender_system_id` FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `created` DESC LIMIT 1");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				return $row["sender_system_id"];
		return null;
	}
	
	function CloseChat($_reason=0)
	{
		$this->ExternalClose();
		$this->Closed=true;
	}
	
	function CloseWindow()
	{
		$this->ExternalClose();
		$this->Destroy();
	}
	
	function Save()
	{
		global $CONFIG;
		$_new = (func_num_args() > 0) ? func_get_arg(0) : false;
		if($_new)
		{
			$this->FirstCall = true;
			$this->Status = CHAT_STATUS_OPEN;
		}
		
		if(empty($this->FirstActive))
			$this->FirstActive = time();

		if($this->FirstCall)
			queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_CHATS."` (`visitor_id` ,`browser_id` ,`visit_id` ,`fullname` ,`email` ,`company` ,`typing` ,`area_code` ,`first_active` ,`last_active` ,`request_operator` ,`request_group` ,`question` ,`customs` ) VALUES ('".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->VisitId)."','".@mysql_real_escape_string($this->Fullname)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."',0,'".@mysql_real_escape_string($this->Code)."','".@mysql_real_escape_string($this->FirstActive)."','".@mysql_real_escape_string($this->LastActive)."','".@mysql_real_escape_string($this->DesiredChatPartner)."','".@mysql_real_escape_string($this->DesiredChatGroup)."','".@mysql_real_escape_string($this->Question)."','".@mysql_real_escape_string(serialize($this->Customs))."');");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `typing`='".@mysql_real_escape_string(($this->Typing)?1:0)."',`customs`='".@mysql_real_escape_string(serialize($this->Customs))."',`request_operator`='".@mysql_real_escape_string($this->DesiredChatPartner)."',`request_group`='".@mysql_real_escape_string($this->DesiredChatGroup)."',`last_active`='".@mysql_real_escape_string(time())."' WHERE `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
		
		parent::Save();
		
		if(count($this->History) == 0)
		{
			$this->History[0] = new HistoryUrl(LIVEZILLA_URL . FILE_CHAT,$this->Code,$CONFIG["gl_site_name"],"",$this->FirstActive);
			$this->History[0]->Save($this->BrowserId);
		}
	}
	
	function SaveLoginData()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `company`='".@mysql_real_escape_string($this->Company)."',`question`='".@mysql_real_escape_string($this->Question)."',`email`='".@mysql_real_escape_string($this->Email)."',`email`='".@mysql_real_escape_string($this->Email)."',`fullname`='".@mysql_real_escape_string($this->Fullname)."',`request_operator`='".@mysql_real_escape_string($this->DesiredChatPartner)."',`last_active`='".@mysql_real_escape_string(time())."',`request_group`='".@mysql_real_escape_string($this->DesiredChatGroup)."' WHERE `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
	}
	
	function Destroy()
	{
		parent::Destroy();
	}
	
	function InternalDecline($_internal)
	{
		if(in_array($_internal,$this->ChatRequestReceiptants))
			queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `declined`=1,`dtime`=".time()." WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internal)."' LIMIT 1;");
		
		if(count($this->ChatRequestReceiptants)==1)
			$this->UpdateUserStatus(false,false,true,false,false);
	}
	
	function InternalClose()
	{
		$this->UpdateUserStatus(false,true,false,false,false);
	}
	
	function InternalActivate()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `time`='".@mysql_real_escape_string(time())."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
		$this->UpdateUserStatus(true,false,false,false,false);
	}
	
	function ExternalActivate()
	{
		$this->UpdateUserStatus(false,false,false,true,false);
	}
		
	function ExternalClose()
	{
		$this->UpdateUserStatus(false,false,false,false,true);
	}
	
	function UpdateUserStatus($_internalActivated,$_internalClosed,$_internalDeclined,$_externalActivated,$_externalClose)
	{
		if(!empty($this->ChatId))
		{
			$this->Status = ($_externalClose || $_internalDeclined || $_internalClosed) ? CHAT_CLOSED : $this->Status;
			if($_internalActivated)
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `internal_active`='1',`allocated`='".@mysql_real_escape_string(time())."' WHERE `internal_active`=0 AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
				if(@mysql_affected_rows() == 1)
					queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."`  WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `user_id`!='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."';");
			}
			else
			{
				if($_externalClose && empty($this->InternalClosed))
					$update = "`external_close`='1',`exit`='".@mysql_real_escape_string(time()+1)."'";
				else if($_externalClose && !empty($this->InternalClosed))
					$update = "`external_close`='1'";
				else if($_internalClosed && empty($this->InternalClosed))
					$update = "`internal_closed`='1',`exit`='".@mysql_real_escape_string(time()+1)."'";
				else if($_internalDeclined && empty($this->InternalDeclined))
					$update = "`internal_declined`='1',`exit`='".@mysql_real_escape_string(time()+1)."'";
				else
					$update = "`external_active`='1'";
				
				// (($_internalClosed) ? "`internal_closed`='1',`exit`='".@mysql_real_escape_string(time()+1)."'" : (($_internalDeclined) ? "`internal_declined`='1',`exit`='".@mysql_real_escape_string(time()+1)."'" : "`external_active`='1'"));
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET ".$update." WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
			}
		}
	}
}
?>