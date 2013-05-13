<?php
/****************************************************************************************
* LiveZilla objects.global.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class BaseObject
{
	public $Id;
	public $Created;
	public $Edited;
	public $Creator;
	public $Editor;
	public $FirstCall;
}

class Action
{
	public $Id;
	public $Folder;
	public $ReceiverUserId;
	public $ReceiverBrowserId;
	public $SenderSystemId;
	public $SenderUserId;
	public $SenderGroupId;
	public $Text;
	public $BrowserId;
	public $Status;
	public $TargetFile;
	public $Extension;
	public $Created;
	public $Displayed;
	public $Accepted;
	public $Declined;
	public $Closed;
	public $Exists;
	public $EventActionId = "";
}

class Post extends BaseObject
{
	public $Receiver;
	public $ReceiverGroup;
	public $Sender;
	public $Persistent = false;
	public $ChatId;
	public $Translation = "";
	public $TranslationISO = "";
	
	function Post()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Id = $row["id"];
			$this->Sender = $row["sender"];
			$this->Receiver = $row["receiver"];
			$this->ReceiverGroup = $row["receiver_group"];
			$this->Text = $row["text"];
			$this->Created = $row["time"];
			$this->ChatId = $row["chat_id"];
			$this->Translation = $row["translation"];
			$this->TranslationISO = $row["translation_iso"];
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Sender = func_get_arg(1);
			$this->Receiver = func_get_arg(2);
			$this->Text = func_get_arg(3);
			$this->Created = func_get_arg(4);
			$this->ChatId = func_get_arg(5);
		}
   	}
	
	function GetXml()
	{
		$receiver = (!empty($this->ReceiverGroup)) ? $this->ReceiverGroup : $this->Receiver;
		$translation = (!empty($this->Translation)) ? " tr=\"".base64_encode($this->Translation)."\" triso=\"".base64_encode($this->TranslationISO)."\"" : "";
		return "<val id=\"".base64_encode($this->Id)."\" sen=\"".base64_encode($this->Sender)."\" rec=\"".base64_encode($receiver)."\" date=\"".base64_encode($this->Created)."\"".$translation.">".base64_encode($this->Text)."</val>\r\n";
	}
	
	function GetCommand()
	{
		if(!empty($this->Translation))
			return "lz_chat_add_internal_text(\"".base64_encode($this->Translation."<div class=\"lz_message_translation\">".$this->Text."</div>")."\" ,\"".base64_encode($this->Id)."\");";
		else
			return "lz_chat_add_internal_text(\"".base64_encode($this->Text)."\" ,\"".base64_encode($this->Id)."\");";
	}
	
	function Save()
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_POSTS."` (`id`,`chat_id`,`time`,`micro`,`sender`,`receiver`,`receiver_group`,`text`,`translation`,`translation_iso`,`received`,`persistent`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ChatId)."',".@mysql_real_escape_string($this->Created).",".@mysql_real_escape_string(mTime()).",'".@mysql_real_escape_string($this->Sender)."','".@mysql_real_escape_string($this->Receiver)."','".@mysql_real_escape_string($this->ReceiverGroup)."','".@mysql_real_escape_string($this->Text)."','".@mysql_real_escape_string($this->Translation)."','".@mysql_real_escape_string($this->TranslationISO)."','0','".@mysql_real_escape_string(parseBool($this->Persistent,false))."');");
	}
}

class FilterList
{
	public $Filters;
	public $Message;
	
	function FilterList()
   	{
		$this->Filters = Array();
		$this->Populate();
   	}
	
	function Populate()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_FILTERS."`;"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$filter = new Filter($row["id"]);
				$filter->SetValues($row);
				$this->Filters[$filter->Id] = $filter;
			}
	}
	
	function Match($_ip,$_languages,$_userid)
	{
		foreach($this->Filters as $filterid => $filter)
		{
			if($filter->Activestate == FILTER_TYPE_INACTIVE)
				continue;
			
			$this->Message = $filter->Reason;
			$compare["match_ip"] = $this->IpCompare($_ip,$filter->IP);
			$compare["match_lang"] = $this->LangCompare($_languages,$filter->Languages);
			$compare["match_id"] = ($filter->Userid == $_userid);
			if($compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if(!$compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if($compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if(!$compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if($compare["match_id"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if(!$compare["match_id"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			if(defined("ACTIVE_FILTER_ID"))
				return true;
		}
		return false;
	}
	
	function IpCompare($_ip, $_comparer)
	{
		$array_ip = explode(".",$_ip);
		$array_comparer = explode(".",$_comparer);
		if(count($array_ip) == 4 && count($array_comparer) == 4)
		{
			foreach($array_ip as $key => $octet)
			{
				if($array_ip[$key] != $array_comparer[$key])
				{
					if($array_comparer[$key] == -1)
						return true;
					return false;
				}
			}
			return true;
		}
		else
			return false;
	}
	
	function LangCompare($_lang, $_comparer)
	{
		$array_lang = explode(",",$_lang);
		$array_comparer = explode(",",$_comparer);
		foreach($array_lang as $key => $lang)
			foreach($array_comparer as $keyc => $langc)
				if(strtoupper($array_lang[$key]) == strtoupper($langc))
					return true;
		return false;
	}
}

class EventList
{
	public $Events;
	
	function EventList()
   	{
		$this->Events = Array();
   	}
	function GetActionById($_id)
	{
		foreach($this->Events as $event)
			foreach($event->Actions as $action)
				if($action->Id == $_id)
					return $action;
		return null;
	}
}

class HistoryUrl
{
	public $Url;
	public $Referrer;
	public $Entrance;
	public static $SearchEngines = array("s"=>array("*nigma*"),"q"=>array("*search.*","*searchatlas*","*suche.*","*google.*","*bing.*","*ask*","*alltheweb*","*altavista*","*gigablast*"),"p"=>array("*search.yahoo*"),"query"=>array("*hotbot*","*lycos*"),"key"=>array("*looksmart*"),"text"=>array("*yandex*"),"wd"=>array("*baidu.*"),"searchTerm"=>array("*search.*"),"debug"=>array("*127.0.0.1*"));
	
	function HistoryURL()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Url = new BaseURL($_row["url_dom"],$_row["url_path"],$_row["url_area_code"],$_row["url_title"]);
			$this->Url->Params = $_row["params"];
			$this->Url->Untouched = $_row["untouched"];
			$this->Url->IsExternal = false;
			$this->Referrer = new BaseURL($_row["ref_dom"],$_row["ref_path"],$_row["ref_area_code"],$_row["ref_title"]);
			$this->Entrance = $_row["entrance"];
		}
		else
		{
			$this->Url = new BaseURL(func_get_arg(0));
			$this->Url->AreaCode = func_get_arg(1);
			$this->Url->PageTitle = func_get_arg(2);
			$this->Url->IsExternal = false;
			$this->Referrer = new BaseURL(func_get_arg(3));
			$this->Entrance = func_get_arg(4);
		}
	}
	
	function Destroy($_browserId)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` WHERE `browser_id`='".@mysql_real_escape_string($_browserId)."' AND `entrance`='".@mysql_real_escape_string($this->Entrance)."' LIMIT 1;");
	}
	
	function Save($_browserId)
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` (`browser_id`, `entrance`, `referrer`, `url`, `params`, `untouched`) VALUES ('".@mysql_real_escape_string($_browserId)."', '".@mysql_real_escape_string($this->Entrance)."', '".@mysql_real_escape_string($this->Referrer->Save())."', '".@mysql_real_escape_string($this->Url->Save())."', '".@mysql_real_escape_string($this->Url->Params)."', '".@mysql_real_escape_string($this->Url->Untouched)."');");
	}
}

class BaseURL
{
	public $Path = "";
	public $Params = "";
	public $Domain = "";
	public $AreaCode = "";
	public $PageTitle = "";
	public $IsExternal = true;
	public $IsSearchEngine = false;
	public $Excluded;
	public $Untouched = "";

	function BaseURL($_url)
	{
		global $CONFIG;
		if(func_num_args() == 1)
		{
			if(!isnull(func_get_arg(0)))
			{
				$this->Untouched = func_get_arg(0);
				$parts = $this->ParseURL($this->Untouched);
				$this->Domain = $parts[0];
				$this->Path = substr($parts[1],0,255);
				$this->Params = $parts[2];
			}
			else
				$this->IsExternal = false;
		}
		else
		{
			$this->Domain = func_get_arg(0);
			$this->Path = func_get_arg(1);
			$this->AreaCode = func_get_arg(2);
			$this->PageTitle = func_get_arg(3);
		}
		
		$domains = explode(",",$CONFIG["gl_doma"]);
		if(!empty($CONFIG["gl_doma"]) && !empty($this->Domain) && is_array($domains))
		{
			foreach($domains as $bldom)
			{
				$match = compareUrls($bldom,$this->Domain);
				if((!empty($CONFIG["gl_bldo"]) && $match) || (empty($CONFIG["gl_bldo"]) && !$match))
				{
					$this->Excluded = true;
					break;
				}
			}
		}
	}
	
	function GetAbsoluteUrl()
	{
		if(!empty($this->Untouched))
			return $this->Untouched;
		else
			return $this->Domain . $this->Path;
	}

	function Save()
	{
		if($this->IsExternal)
			$pid = getValueId(DATABASE_VISITOR_DATA_PATHS,"path",$this->Path.$this->Params,false,255);
		else
			$pid = getValueId(DATABASE_VISITOR_DATA_PATHS,"path",$this->Path,false,255);
		$did = $this->GetDomainId();
		$cid = getValueId(DATABASE_VISITOR_DATA_AREA_CODES,"area_code",$this->AreaCode);
		$tid = $this->GetTitleId($did,$pid,$cid);
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` (`id`, `path`, `domain`,  `title`, `area_code`) VALUES (NULL, '".@mysql_real_escape_string($pid)."',  '".@mysql_real_escape_string($did)."',  '".@mysql_real_escape_string($tid)."', '".@mysql_real_escape_string($cid)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id`,`title` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` WHERE `path`='".@mysql_real_escape_string($pid)."' AND `domain`='".@mysql_real_escape_string($did)."';"), MYSQL_BOTH);
		if(STATS_ACTIVE && $tid != $row["title"])
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` SET `title`=(SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` WHERE id='".@mysql_real_escape_string($tid)."' OR id='".@mysql_real_escape_string($row["title"])."' ORDER BY `confirmed` DESC LIMIT 1) WHERE `path`='".@mysql_real_escape_string($pid)."' AND `domain`='".@mysql_real_escape_string($did)."';");
		return $row["id"];
	}
	
	function MarkSearchEngine()
	{
		$this->IsSearchEngine = true;
		$this->Params =
		$this->Path = "";
	}
	
	function GetTitleId()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` (`id`, `title`) VALUES (NULL, '".@mysql_real_escape_string($this->PageTitle)."');");
		if(STATS_ACTIVE && !empty($this->PageTitle))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` SET `confirmed`=`confirmed`+1 WHERE `title`='".@mysql_real_escape_string($this->PageTitle)."' LIMIT 1;");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` WHERE `title`='".@mysql_real_escape_string($this->PageTitle)."';"), MYSQL_BOTH);
		return $row["id"];
	}
	
	function GetDomainId($_value)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` (`id`, `domain`, `search`) VALUES (NULL, '".@mysql_real_escape_string($this->Domain)."', '".@mysql_real_escape_string((!$this->IsExternal && $this->IsSearchEngine)?1:0)."');");
		if(!$this->IsExternal)
		{
			$row = mysql_fetch_array(queryDB(true,"SELECT `id`,`external`,`search` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';"), MYSQL_BOTH);
			if(!empty($row["external"]))
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` SET `external`=0 WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';");
			}
		}
		else
		{
			$row = mysql_fetch_array(queryDB(true,"SELECT `id`,`search` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';"), MYSQL_BOTH);
		}
		if($this->IsExternal && $this->IsSearchEngine && empty($row["search"]))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` SET `search`=1 WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';");
		return $row["id"];
	}
	
	function IsInternalDomain()
	{
		$row = mysql_fetch_array($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';"), MYSQL_BOTH);
		if(mysql_num_rows($result) == 1 && empty($row["external"]))
			return true;
		return false;
	}
	
	function ParseURL($_url,$allowedParams="",$cutParams="",$domain="",$path="")
	{
		$allowed = (STATS_ACTIVE) ? StatisticProvider::$AllowedParameters : array();
		$igfilenames = (STATS_ACTIVE) ? StatisticProvider::$HiddenFilenames : array();
		$parts = parse_url($_url);
		$uparts = explode("?",$_url);
		if(count($allowed)>0 && count($uparts)>1)
		{
			$pparts = explode("&",$uparts[1]);
			foreach($pparts as $part)
			{
				$paramparts = explode("=",$part);
				if(in_array(strtolower($paramparts[0]),$allowed))
				{
					if(empty($allowedParams))
						$allowedParams .= "?";
					else
						$allowedParams .= "&";
						
					$allowedParams .= $paramparts[0];
					if(count($paramparts)>1)
						$allowedParams .= "=".$paramparts[1];
				}
				else
				{
					if(!empty($cutParams))
						$cutParams .= "&";
					$cutParams .= $paramparts[0];
					if(count($paramparts)>1)
						$cutParams .= "=".$paramparts[1];
				}
			}
		}
		if(!empty($cutParams) && empty($allowedParams))
			$cutParams = "?" . $cutParams;
		else if(!empty($cutParams) && !empty($allowedParams))
			$cutParams = "&" . $cutParams;
		else if(empty($cutParams) && empty($allowedParams) && count($uparts) > 1)
			$cutParams = "?" . $uparts[1];
			
		$partsb = @explode($parts["host"],$_url);
		
		if(!isset($parts["host"]))
			$parts["host"] = "localhost";
		
		$domain = $partsb[0].$parts["host"];
		$path = substr($uparts[0],strlen($domain),strlen($uparts[0])-strlen($domain));
		$path = str_replace($igfilenames,"",$path);
		return array($domain,$path.$allowedParams,$cutParams);
	}
}

class Filter extends BaseObject
{
	public $IP;
	public $Expiredate;
	public $Userid;
	public $Reason;
	public $Filtername;
	public $Activestate;
	public $Exertion;
	public $Languages;
	public $Activeipaddress;
	public $Activeuserid;
	public $Activelanguage;
	
	function Filter($_id)
   	{
		$this->Id = $_id;
		$this->Edited = time();
   	}
	
	function GetXML()
	{
		return "<val active=\"".base64_encode($this->Activestate)."\" edited=\"".base64_encode($this->Edited)."\" editor=\"".base64_encode($this->Editor)."\" activeipaddresses=\"".base64_encode($this->Activeipaddress)."\" activeuserids=\"".base64_encode($this->Activeuserid)."\" activelanguages=\"".base64_encode($this->Activelanguage)."\" expires=\"".base64_encode($this->Expiredate)."\" creator=\"".base64_encode($this->Creator)."\" created=\"".base64_encode($this->Created)."\" userid=\"".base64_encode($this->Userid)."\" ip=\"".base64_encode($this->IP)."\" filtername=\"".base64_encode($this->Filtername)."\" filterid=\"".base64_encode($this->Id)."\" reason=\"".base64_encode($this->Reason)."\" exertion=\"".base64_encode($this->Exertion)."\" languages=\"".base64_encode($this->Languages)."\" />\r\n";
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_FILTERS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
			$this->SetValues($row);
	}
	
	function SetValues($_row)
	{
		$this->Creator = $_row["creator"];
		$this->Created = $_row["created"];
		$this->Editor = $_row["editor"];
		$this->Edited = $_row["edited"];
		$this->IP = $_row["ip"];
		$this->Expiredate = $_row["expiredate"];
		$this->Userid = $_row["visitor_id"];
		$this->Reason = $_row["reason"];
		$this->Filtername = $_row["name"];
		$this->Id = $_row["id"];
		$this->Activestate = $_row["active"];
		$this->Exertion = $_row["exertion"];
		$this->Languages = $_row["languages"];
		$this->Activeipaddress = $_row["activeipaddress"];
		$this->Activeuserid = $_row["activevisitorid"];
		$this->Activelanguage = $_row["activelanguage"];
	}
	
	function Save()
	{
		$this->Destroy();
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_FILTERS."` (`creator`, `created`, `editor`, `edited`, `ip`, `expiredate`, `visitor_id`, `reason`, `name`, `id`, `active`, `exertion`, `languages`, `activeipaddress`, `activevisitorid`, `activelanguage`) VALUES ('".@mysql_real_escape_string($this->Creator)."', '".@mysql_real_escape_string($this->Created)."','".@mysql_real_escape_string($this->Editor)."', '".@mysql_real_escape_string($this->Edited)."','".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($this->Expiredate)."','".@mysql_real_escape_string($this->Userid)."', '".@mysql_real_escape_string($this->Reason)."','".@mysql_real_escape_string($this->Filtername)."', '".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Activestate)."', '".@mysql_real_escape_string($this->Exertion)."','".@mysql_real_escape_string($this->Languages)."', '".@mysql_real_escape_string($this->Activeipaddress)."','".@mysql_real_escape_string($this->Activeuserid)."', '".@mysql_real_escape_string($this->Activelanguage)."');");
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_FILTERS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class Rating extends Action
{
	public $Fullname = "";
	public $Email="";
	public $Company="";
	public $InternId="";
	public $UserId="";
	public $RateQualification=0;
	public $RatePoliteness=0;
	public $RateComment=0;

	function Rating()
	{
		$this->Id = func_get_arg(0);
		if(func_num_args() == 2)
		{
			$row = func_get_arg(1);
			$this->RateComment = $row["comment"];
			$this->RatePoliteness = $row["politeness"];
			$this->RateQualification = $row["qualification"];
			$this->Fullname = $row["fullname"];
			$this->Email = $row["email"];
			$this->Company = $row["company"];
			$this->InternId = $row["internal_id"];
			$this->UserId = $row["user_id"];
			$this->Created = $row["time"];
		}
	}
	
	function IsFlood()
	{
		return isRatingFlood();
	}
	
	function GetXML($_internal,$_full)
	{
		if($_full)
		{
			$intern = (isset($_internal[getInternalSystemIdByUserId($this->InternId)])) ? $_internal[getInternalSystemIdByUserId($this->InternId)]->Fullname : $this->InternId;
			return "<val id=\"".base64_encode($this->Id)."\" cr=\"".base64_encode($this->Created)."\" rc=\"".base64_encode($this->RateComment)."\" rp=\"".base64_encode($this->RatePoliteness)."\" rq=\"".base64_encode($this->RateQualification)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ii=\"".base64_encode($intern)."\" ui=\"".base64_encode($this->UserId)."\" />\r\n";
		}
		else
			return "<val id=\"".base64_encode($this->Id)."\" cr=\"".base64_encode($this->Created)."\" />\r\n";
	}
}

class ClosedTicket extends Action
{
	function ClosedTicket()
	{
		$this->Id = func_get_arg(0);
		if(func_num_args() == 2)
		{
			$row = func_get_arg(1);
			$this->Sender = $row["internal_fullname"];
		}
	}
	function GetXML($_time,$_status)
	{
		return "<cl id=\"".base64_encode($this->Id)."\" st=\"".base64_encode($_status)."\" ed=\"".base64_encode($this->Sender)."\" ti=\"".base64_encode($_time)."\"/>\r\n";
	}
}

class UserTicket extends Action
{
	public $Fullname = "";
	public $Email="";
	public $Group="";
	public $Company="";
	public $IP="";
	public $UserId="";
	public $Customs="";
	
	function UserTicket()
	{
		if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
		}
		else
		{
			$row = func_get_arg(0);
			$this->Text = $row["text"];
			$this->Fullname = $row["fullname"];
		 	$this->Email = $row["email"];
			$this->Company = $row["company"];
			$this->Group = $row["target_group_id"];
			$this->IP = $row["ip"];
			$this->Id = $row["ticket_id"];
			$this->UserId = $row["user_id"];
			$this->Created = $row["time"];
		}
	}

	function GetXML($_groups,$_full)
	{
		if($_full)
		{
			$xml = "<val id=\"".base64_encode($this->Id)."\" ct=\"".base64_encode($this->Created)."\" gr=\"".base64_encode($this->Group)."\" mt=\"".base64_encode($this->Text)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ui=\"".base64_encode($this->UserId)."\" ip=\"".base64_encode($this->IP)."\">\r\n";
			if(is_array($this->Customs))
				foreach($this->Customs as $i => $value)
					$xml .= "<c id=\"".base64_encode($i)."\">".base64_encode($value)."</c>\r\n";
			$xml .= "</val>";
		}
		else
			$xml = "<val id=\"".base64_encode($this->Id)."\" ct=\"".base64_encode($this->Created)."\" />\r\n";
		return $xml;
	}
	
	function Save()
	{
		$time = time();
		while(true)
		{
			queryDB(true,"SELECT time FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE time=".@mysql_real_escape_string($time).";");
			if(@mysql_affected_rows() > 0)
				$time++;
			else
				break;
		}
		if(queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_TICKETS."` (`id` ,`user_id` ,`target_group_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->UserId)."', '".@mysql_real_escape_string($this->Group)."');"))
			if(queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` (`id` ,`time` ,`ticket_id` ,`text` ,`fullname` ,`email` ,`company` ,`ip`) VALUES (NULL, ".@mysql_real_escape_string($time).", '".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->Text)."', '".@mysql_real_escape_string($this->Fullname)."', '".@mysql_real_escape_string($this->Email)."', '".@mysql_real_escape_string($this->Company)."', '".@mysql_real_escape_string($this->IP)."');"))
				if(is_array($this->Customs))
					foreach($this->Customs as $i => $value)
						if(!empty($value))
							queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."` (`ticket_id` ,`custom_id` ,`value`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($i)."', '".@mysql_real_escape_string($value)."');");
	}
}

class Response
{
	public $XML = "";
	public $Internals="";
	public $Groups="";
	public $InternalProfilePictures="";
	public $InternalWebcamPictures="";
	public $InternalVcards="";
	public $Typing="";
	public $Exceptions="";
	public $Filter="";
	public $Events="";
	public $EventTriggers="";
	public $Authentications="";
	public $Posts="";
	public $Login;
	public $Ratings="";
	public $Messages="";
	public $Archive="";
	public $Resources="";
	public $GlobalHash;
	public $Actions="";
	public $Goals="";
	
	function SetStandardResponse($_code,$_sub)
	{
		$this->XML = "<response><value id=\"".base64_encode($_code)."\" />" . $_sub . "</response>";
	}
	
	function SetValidationError($_code,$_addition="")
	{
		if(!empty($_addition))
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" error=\"".base64_encode($_addition)."\" />";
		else
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" />";
	}
	
	function GetXML()
	{
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><livezilla_xml><livezilla_version>".base64_encode(VERSION)."</livezilla_version>" . $this->XML . "</livezilla_xml>";
	}
}

class FileEditor
{
	public $Result;
	public $TargetFile;
	
	function FileEditor($_file)
	{
		$this->TargetFile = $_file;
	}
	
	function Load()
	{
		if(file_exists($this->TargetFile))
		{
			$handle = @fopen ($this->TargetFile, "r");
			while (!@feof($handle))
	   			$this->Result .= @fgets($handle, 4096);
			
			$length = strlen($this->Result);
			$this->Result = @unserialize($this->Result);
			@fclose($handle);
		}
	}

	function Save($_data)
	{
		if(strpos($this->TargetFile,"..") === false)
		{
			$handle = @fopen($this->TargetFile, "w");
			if(!empty($_data))
				$length = @fputs($handle,serialize($_data));
			@fclose($handle);
		}
	}
}

class FileUploadRequest extends Action
{
	public $Error = false;
	public $Download = false;
	public $FileName;
	public $FileMask;
	public $FileId;
	public $Permission = PERMISSION_VOID;
	public $FirstCall = true;
	public $ChatId;
	public $Closed;
	
	
	function FileUploadRequest()
	{
		if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
			$this->ReceiverUserId = func_get_arg(1);
			$this->Load();
		}
		else if(func_num_args() == 1)
		{
			$this->SetValues(func_get_arg(0));
		}
	}
	    
	function Save()
	{
		if($this->FirstCall)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_FILES."`  (`id` ,`created`,`file_name` ,`file_mask` ,`file_id` ,`chat_id`,`visitor_id` ,`browser_id` ,`operator_id`,`error` ,`permission` ,`download`,`closed`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->FileName)."', '".@mysql_real_escape_string($this->FileMask)."', '".@mysql_real_escape_string($this->FileId)."', '".@mysql_real_escape_string($this->ChatId)."', '".@mysql_real_escape_string($this->SenderUserId)."', '".@mysql_real_escape_string($this->SenderBrowserId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."','".@mysql_real_escape_string($this->Error)."', '".@mysql_real_escape_string($this->Permission)."', '".@mysql_real_escape_string(($this->Download)?1:0)."', ".@mysql_real_escape_string(($this->Closed)?1:0).");");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FILES."` SET `download`='".@mysql_real_escape_string(($this->Download)?1:0)."',`error`='".@mysql_real_escape_string(($this->Error) ? 1 : 0)."',`permission`='".@mysql_real_escape_string($this->Permission)."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' ORDER BY `created` DESC LIMIT 1; ");
	}
	
	function Close()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FILES."` SET `closed`=1 WHERE `id`='".@mysql_real_escape_string($this->Id)."';");
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' ORDER BY `created` DESC LIMIT 1;");
		if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$this->SetValues($row);
		}
		else
			$this->FirstCall = true;
	}	
	
	function SetValues($row)
	{	
		$this->FirstCall = false;
		$this->Id = $row["id"];
		$this->FileName = $row["file_name"];
		$this->FileMask = $row["file_mask"];
		$this->FileId = $row["file_id"];
		$this->ChatId = $row["chat_id"];
		$this->SenderUserId = $row["visitor_id"];
		$this->SenderBrowserId = $row["browser_id"];
		$this->ReceiverUserId = $row["operator_id"];
		$this->Error = !empty($row["error"]);
		$this->Permission = $row["permission"];
		$this->Download = !empty($row["download"]);
		$this->Closed = !empty($row["closed"]);
		$this->Created = $row["created"];
	}
	
	function GetFile()
	{
		return PATH_UPLOADS . $this->FileMask;
	}
}

class Forward extends Action
{
	public $Conversation;
	public $TargetSessId;
	public $TargetGroupId;
	public $Processed = false;
	public $ChatId;
	
	function Forward()
	{
		$this->Id = getId(5);
		if(func_num_args() == 2)
		{
			$this->ChatId = func_get_arg(0);
			$this->SenderSystemId = func_get_arg(1);
			$this->Load();
		}
		else if(func_num_args() == 1)
		{
			$this->SetValues(func_get_arg(0));
		}
	} 
	
	function Save($_processed=false,$_received=false)
	{
		if(!$_processed)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` (`id`, `created`, `sender_operator_id`, `target_operator_id`, `target_group_id`, `chat_id`,`visitor_id`,`browser_id`, `conversation`, `info_text`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."', '".@mysql_real_escape_string($this->TargetSessId)."', '".@mysql_real_escape_string($this->TargetGroupId)."', '".@mysql_real_escape_string($this->ChatId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->ReceiverBrowserId)."', '".@mysql_real_escape_string($this->Conversation)."', '".@mysql_real_escape_string($this->Text)."');");
		else if($_received)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` SET `received`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1; ");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` SET `processed`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1; ");
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' AND `received`=0 LIMIT 1;");
		if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
			$this->SetValues($row);
	}
	
	function SetValues($_row)
	{
		$this->Id = $_row["id"];
		$this->SenderSystemId = $_row["sender_operator_id"];
		$this->TargetSessId = $_row["target_operator_id"];
		$this->TargetGroupId = $_row["target_group_id"];
		$this->ChatId = $_row["chat_id"];
		$this->Conversation = $_row["conversation"];
		$this->Text = $_row["info_text"];
		$this->Processed = !empty($_row["processed"]);
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class WebsitePush extends Action
{
	public $TargetURL;
	public $Ask;
	public $ActionId;
	public $Senders;
	
	function WebsitePush()
	{
		if(func_num_args() == 7)
		{
			$this->Id = getId(32);
			$this->SenderSystemId = func_get_arg(0);
			$this->SenderGroupId = func_get_arg(1);
			$this->ReceiverUserId = func_get_arg(2);
			$this->BrowserId = func_get_arg(3);
			$this->Text = func_get_arg(4);
			$this->Ask = func_get_arg(5);
			$this->TargetURL = func_get_arg(6);
			$this->Senders = array();
		}
		else if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->TargetURL = func_get_arg(1);
			$this->Ask = func_get_arg(2);
			$this->Senders = array();
		}
		else if(func_num_args() == 2)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->Ask = $_row["ask"];
			$this->TargetURL = $_row["target_url"];
			$this->Senders = array();
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->SenderSystemId = $_row["sender_system_id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->Text = $_row["text"];
			$this->Ask = $_row["ask"];
			$this->TargetURL = $_row["target_url"];
			$this->Accepted = $_row["accepted"];
			$this->Declined = $_row["declined"];
			$this->Displayed = $_row["displayed"];
			$this->Senders = array();
		}
	}

	function SaveEventConfiguration()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_WEBSITE_PUSHS."` (`id`, `action_id`, `target_url`,`ask`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."','".@mysql_real_escape_string($this->TargetURL)."','".@mysql_real_escape_string($this->Ask)."');");
	}
	
	function SetStatus($_displayed,$_accepted,$_declined)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='0',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='1',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_declined)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='0',`declined`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` (`id`, `created`, `sender_system_id`, `receiver_user_id`, `receiver_browser_id`, `text`, `ask`, `target_url`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->Text)."','".@mysql_real_escape_string($this->Ask)."','".@mysql_real_escape_string($this->TargetURL)."');");
	}

	function GetInitCommand()
	{
		return "lz_tracking_init_website_push('".base64_encode(str_replace("%target_url%",$this->TargetURL,$this->Text))."',".time().");";
	}
	
	function GetExecCommand()
	{
		return "lz_tracking_exec_website_push('".base64_encode($this->TargetURL)."');";
	}
	
	function GetXML()
	{
		$xml = "<evwp id=\"".base64_encode($this->Id)."\" url=\"".base64_encode($this->TargetURL)."\" ask=\"".base64_encode($this->Ask)."\">\r\n";
		
		foreach($this->Senders as $sender)
			$xml .= $sender->GetXML();

		return $xml . "</evwp>\r\n";
	}
}

class EventActionInternal extends Action
{
	public $TriggerId;
	function EventActionInternal()
	{
		if(func_num_args() == 2)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->TriggerId = func_get_arg(1);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->TriggerId = $_row["trigger_id"];
			$this->EventActionId = $_row["action_id"];
		}
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` (`id`, `created`, `trigger_id`, `receiver_user_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->TriggerId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."');");
	}

	function GetXml()
	{
		return "<ia time=\"".base64_encode(time())."\" aid=\"".base64_encode($this->EventActionId)."\" />\r\n";
	}
}

class Alert extends Action
{
	function Alert()
	{
		if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$this->Text = func_get_arg(2);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->Text = $_row["text"];
			$this->EventActionId = $_row["event_action_id"];
			$this->Displayed = !empty($_row["displayed"]);
			$this->Accepted = !empty($_row["accepted"]);
		}
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_ALERTS."` (`id`, `created`, `receiver_user_id`, `receiver_browser_id`,`event_action_id`, `text`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->Text)."');");
	}
	
	function SetStatus($_displayed,$_accepted)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_ALERTS."` SET `displayed`='1',`accepted`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_ALERTS."` SET `displayed`='1',`accepted`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function GetCommand()
	{
		return "lz_tracking_send_alert('".$this->Id."','".base64_encode($this->Text)."');";
	}
}

class ChatRequest extends Action
{
	public $Invitation;
	function ChatRequest()
   	{
		if(func_num_args() == 5)
		{
			$this->Id = getId(32);
			$this->SenderSystemId = func_get_arg(0);
			$this->SenderGroupId = func_get_arg(1);
			$this->ReceiverUserId = func_get_arg(2);
			$this->BrowserId = func_get_arg(3);
			$this->Text = func_get_arg(4);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->SenderSystemId = $_row["sender_system_id"];
			$this->SenderUserId = $_row["sender_system_id"];
			$this->SenderGroupId = $_row["sender_group_id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->EventActionId = $_row["event_action_id"];
			$this->Text = $_row["text"];
			$this->Displayed = !empty($_row["displayed"]);
			$this->Accepted = !empty($_row["accepted"]);
			$this->Declined = !empty($_row["declined"]);
			$this->Closed = !empty($_row["closed"]);
		}
   	}
	
	function SetStatus($_displayed,$_accepted,$_declined,$_closed=false)
	{
		$_closed = ($_accepted || $_declined || $_closed);
	
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='0',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='1' WHERE `declined`=0 AND `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_declined)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`declined`='1' WHERE `accepted`=0 AND `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_closed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `closed`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` (`id`, `created`, `sender_system_id`, `sender_group_id`,`receiver_user_id`, `receiver_browser_id`,`event_action_id`, `text`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."','".@mysql_real_escape_string($this->SenderGroupId)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->Text)."');");
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function CreateInvitationTemplate($_style,$_siteName,$_cwWidth,$_cwHeight,$_serverURL,$_sender,$_closeOnClick)
	{
		$template = (@file_exists(FILE_INVITATIONLOGO) && @file_exists(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation_header.tpl")) ? getFile(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation_header.tpl") : getFile(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation.tpl");
		$template = str_replace("<!--site_name-->",$_siteName,$template);
		$template = str_replace("<!--intern_name-->",$_sender->Fullname,$template);
		$template = str_replace("<!--template-->",$_style,$template);
		$template = str_replace("<!--group_id-->",base64UrlEncode($this->SenderGroupId),$template);
		$template = str_replace("<!--user_id-->",base64UrlEncode($_sender->UserId),$template);
		$template = str_replace("<!--width-->",$_cwWidth,$template);
		$template = str_replace("<!--height-->",$_cwHeight,$template);
		$template = str_replace("<!--server-->",$_serverURL,$template);
		$template = str_replace("<!--intern_image-->",$_sender->GetOperatorPictureFile(),$template);
		$template = str_replace("<!--close_on_click-->",$_closeOnClick,$template);
		return $template;
	}
}

class Invitation
{
	public $Id;
	public $ActionId;
	public $Style = "classic";
	public $DisplayPosition = "11";
	public $Speed = 1;
	public $Slide = true;
	public $Margin;
	public $Senders;
	public $Width;
	public $Height;
	public $HTML;
	public $Text;
	public $CloseOnClick;
	
	function Invitation()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Style = $_row["style"];
			$this->Id = $_row["id"];
			$this->Position = $_row["position"];
			$this->Margin = Array($_row["margin_left"],$_row["margin_top"],$_row["margin_right"],$_row["margin_bottom"]);
			$this->Speed = $_row["speed"];
			$this->Slide = $_row["slide"];
			$this->CloseOnClick = $_row["close_on_click"];
		}
		else if(func_num_args() == 10)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->Position = func_get_arg(1);
			$this->Margin = Array(func_get_arg(2),func_get_arg(3),func_get_arg(4),func_get_arg(5));
			$this->Speed = func_get_arg(6);
			$this->Style = func_get_arg(7);
			$this->Slide = func_get_arg(8);
			$this->CloseOnClick = func_get_arg(9);
		}
		else
		{
			$this->HTML = func_get_arg(0);
			$this->Position = func_get_arg(1);
			$this->Margin = Array(func_get_arg(2),func_get_arg(3),func_get_arg(4),func_get_arg(5));
			$this->Speed = func_get_arg(6);
			$this->Style = func_get_arg(7);
			$this->Slide = func_get_arg(8);
			$this->Text = func_get_arg(9);
			$this->CloseOnClick = func_get_arg(10);
		}
		
		if(!empty($this->Style))
		{
			$dimensions = (@file_exists(FILE_INVITATIONLOGO) && @file_exists(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions_header.txt")) ? explode(",",getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions_header.txt")) : explode(",",getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions.txt"));
			$this->Width = @$dimensions[0];
			$this->Height = @$dimensions[1];
		}
		$this->Senders = Array();
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_INVITATIONS."` (`id`, `action_id`, `position`, `speed`, `slide`, `margin_left`, `margin_top`, `margin_right`, `margin_bottom`, `style`, `close_on_click`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."','".@mysql_real_escape_string($this->Position)."', '".@mysql_real_escape_string($this->Speed)."', '".@mysql_real_escape_string($this->Slide)."', '".@mysql_real_escape_string($this->Margin[0])."', '".@mysql_real_escape_string($this->Margin[1])."', '".@mysql_real_escape_string($this->Margin[2])."', '".@mysql_real_escape_string($this->Margin[3])."', '".@mysql_real_escape_string($this->Style)."', '".@mysql_real_escape_string($this->CloseOnClick)."');";
	}

	function GetXML()
	{
		$xml = "<evinv id=\"".base64_encode($this->Id)."\" ml=\"".base64_encode($this->Margin[0])."\" mt=\"".base64_encode($this->Margin[1])."\" mr=\"".base64_encode($this->Margin[2])."\" mb=\"".base64_encode($this->Margin[3])."\" pos=\"".base64_encode($this->Position)."\" speed=\"".base64_encode($this->Speed)."\" slide=\"".base64_encode($this->Slide)."\" style=\"".base64_encode($this->Style)."\" coc=\"".base64_encode($this->CloseOnClick)."\">\r\n";
		
		foreach($this->Senders as $sender)
			$xml .= $sender->GetXML();
			
		return $xml . "</evinv>\r\n";
	}
	
	function GetCommand()
	{
		return "lz_tracking_request_chat('" . base64_encode($this->Id) . "','". base64_encode($this->Text) ."','". base64_encode($this->HTML) ."',".$this->Width.",".$this->Height.",".$this->Margin[0].",".$this->Margin[1].",".$this->Margin[2].",".$this->Margin[3].",'" . $this->Position . "',".$this->Speed."," . parseBool($this->Slide) . ");";
	}
}

class EventTrigger
{
	public $Id;
	public $ActionId;
	public $ReceiverUserId;
	public $ReceiverBrowserId;
	public $Triggered;
	public $TriggerTime;
	public $Exists = false;
	
	function EventTrigger()
	{
		if(func_num_args() == 5)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->ReceiverBrowserId = func_get_arg(1);
			$this->ActionId = func_get_arg(2);
			$this->TriggerTime = func_get_arg(3);
			$this->Triggered = func_get_arg(4);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->ReceiverBrowserId = $_row["receiver_browser_id"];
			$this->ActionId = $_row["action_id"];
			$this->Triggered = $_row["triggered"];
			$this->TriggerTime = $_row["time"];
		}
	}
	
	function Load()
	{
		$this->Exists = false;
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->ReceiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->ReceiverBrowserId)."' AND `action_id`='".@mysql_real_escape_string($this->ActionId)."' ORDER BY `time` ASC;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$this->Id = $row["id"];
				$this->TriggerTime = $row["time"];
				$this->Triggered = $row["triggered"];
				$this->Exists = true;
			}
	}
	
	function Update()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` SET `time`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function Save($_eventId)
	{
		if(!$this->Exists)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` (`id`, `receiver_user_id`, `receiver_browser_id`, `action_id`, `time`, `triggered`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->ReceiverBrowserId)."','".@mysql_real_escape_string($this->ActionId)."', '".@mysql_real_escape_string($this->TriggerTime)."','".@mysql_real_escape_string($this->Triggered)."');");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` SET `triggered`=`triggered`+1, `time`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class EventAction
{
	public $Id = "";
	public $EventId = "";
	public $Type = "";
	public $Value = "";
	public $Invitation;
	public $WebsitePush;
	public $Receivers;
	
	function EventAction()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->EventId = $_row["eid"];
			$this->Type = $_row["type"];
			$this->Value = $_row["value"];
		}
		else if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
			$this->Type = func_get_arg(1);
		}
		else
		{
			$this->EventId = func_get_arg(0);
			$this->Id = func_get_arg(1);
			$this->Type = func_get_arg(2);
			$this->Value = func_get_arg(3);
		}
		$this->Receivers = Array();
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTIONS."` (`id`, `eid`, `type`, `value`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->EventId)."','".@mysql_real_escape_string($this->Type)."', '".@mysql_real_escape_string($this->Value)."');";
	}

	function GetXML()
	{
		$xml =  "<evac id=\"".base64_encode($this->Id)."\" type=\"".base64_encode($this->Type)."\" val=\"".base64_encode($this->Value)."\">\r\n";
		
		if(!empty($this->Invitation))
			$xml .= $this->Invitation->GetXML();
			
		if(!empty($this->WebsitePush))
			$xml .= $this->WebsitePush->GetXML();
			
		foreach($this->Receivers as $receiver)
			$xml .= $receiver->GetXML();
			
		return $xml . "</evac>\r\n";
	}
	
	function Exists($_receiverUserId,$_receiverBrowserId)
	{
		if($this->Type == 2)
		{
			if($result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_receiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($_receiverBrowserId)."' AND `event_action_id`='".@mysql_real_escape_string($this->Id)."' AND `accepted`='0' AND `declined`='0' LIMIT 1;"))
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
					return true;
		}
		else if($this->Type == 3)
		{
			if($result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_receiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($_receiverBrowserId)."' AND `event_action_id`='".@mysql_real_escape_string($this->Id)."' AND `accepted`='0' LIMIT 1;"))
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
					return true;
		}
		return false;
	}
	
	function GetInternalReceivers()
	{
		$receivers = array();
		if($result = queryDB(true,"SELECT `receiver_id` FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` WHERE `action_id`='".@mysql_real_escape_string($this->Id)."';"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$receivers[]=$row["receiver_id"];
		return $receivers;
	}
}

class EventActionSender
{
	public $Id = "";
	public $ParentId = "";
	public $UserSystemId = "";
	public $GroupId = "";
	public $Priority = "";
	
	function EventActionSender()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ParentId = $_row["pid"];
			$this->UserSystemId = $_row["user_id"];
			$this->GroupId = $_row["group_id"];
			$this->Priority = $_row["priority"];
		}
		else if(func_num_args() == 4)
		{
			$this->Id = getId(32);
			$this->ParentId = func_get_arg(0);
			$this->UserSystemId = func_get_arg(1);
			$this->GroupId = func_get_arg(2);
			$this->Priority = func_get_arg(3);
		}
	}
	
	function SaveSender()
	{
		return queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` (`id`, `pid`, `user_id`, `group_id`, `priority`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ParentId)."','".@mysql_real_escape_string($this->UserSystemId)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->Priority)."');");
	}

	function GetXML()
	{
		return "<evinvs id=\"".base64_encode($this->Id)."\" userid=\"".base64_encode($this->UserSystemId)."\" groupid=\"".base64_encode($this->GroupId)."\" priority=\"".base64_encode($this->Priority)."\" />\r\n";
	}
}

class EventActionReceiver
{
	public $Id = "";
	public $ReceiverId = "";
	
	function EventActionReceiver()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ActionId = $_row["action_id"];
			$this->ReceiverId = $_row["receiver_id"];
		}
		else
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->ReceiverId = func_get_arg(1);
		}
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` (`id`, `action_id`, `receiver_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."', '".@mysql_real_escape_string($this->ReceiverId)."');";
	}

	function GetXML()
	{
		return "<evr id=\"".base64_encode($this->Id)."\" rec=\"".base64_encode($this->ReceiverId)."\" />\r\n";
	}
}

class EventURL
{
	public $Id = "";
	public $EventId = "";
	public $URL = "";
	public $Referrer = "";
	public $TimeOnSite = "";
	public $Blacklist;
	
	function EventURL()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->URL = $_row["url"];
			$this->Referrer = $_row["referrer"];
			$this->TimeOnSite = $_row["time_on_site"];
			$this->Blacklist = !empty($_row["blacklist"]);
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->EventId = func_get_arg(1);
			$this->URL = strtolower(func_get_arg(2));
			$this->Referrer = strtolower(func_get_arg(3));
			$this->TimeOnSite = func_get_arg(4);
			$this->Blacklist = func_get_arg(5);
		}
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_URLS."` (`id`, `eid`, `url`, `referrer`, `time_on_site`, `blacklist`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->EventId)."','".@mysql_real_escape_string($this->URL)."', '".@mysql_real_escape_string($this->Referrer)."', '".@mysql_real_escape_string($this->TimeOnSite)."', '".@mysql_real_escape_string($this->Blacklist)."');";
	}

	function GetXML()
	{
		return "<evur id=\"".base64_encode($this->Id)."\" url=\"".base64_encode($this->URL)."\" ref=\"".base64_encode($this->Referrer)."\" tos=\"".base64_encode($this->TimeOnSite)."\" bl=\"".base64_encode($this->Blacklist)."\" />\r\n";
	}
}

class Event extends BaseObject
{
	public $Name = "";
	public $PagesVisited = "";
	public $TimeOnSite = "";
	public $Receivers;
	public $URLs;
	public $Actions;
	public $NotAccepted;
	public $NotDeclined;
	public $TriggerTime;
	public $SearchPhrase = "";
	public $TriggerAmount;
	public $NotInChat;
	public $Priority;
	public $IsActive;
	public $Goals;
	public $FunnelUrls;
	
	function Event()
	{
		$this->FunnelUrls = array();
		$this->Goals = array();
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			
			$this->Id = $_row["id"];
			$this->Name = $_row["name"];
			$this->Edited = $_row["edited"];
			$this->Editor = $_row["editor"];
			$this->Created = $_row["created"];
			$this->Creator = $_row["creator"];
			$this->TimeOnSite = $_row["time_on_site"];
			$this->PagesVisited = $_row["pages_visited"];
			$this->NotAccepted = $_row["not_accepted"];
			$this->NotDeclined = $_row["not_declined"];
			$this->NotInChat = $_row["not_in_chat"];
			$this->TriggerAmount = $_row["max_trigger_amount"];
			$this->TriggerTime = $_row["trigger_again_after"];
			$this->SearchPhrase = $_row["search_phrase"];
			$this->Priority = $_row["priority"];
			$this->IsActive = !empty($_row["is_active"]);
			$this->URLs = array();
			$this->Actions = array();
			$this->Receivers = array();
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Name = func_get_arg(1);
			$this->Edited = func_get_arg(2);
			$this->Created = func_get_arg(3);
			$this->Editor = func_get_arg(4);
			$this->Creator = func_get_arg(5);
			$this->TimeOnSite = func_get_arg(6);
			$this->PagesVisited = func_get_arg(7);
			$this->NotAccepted = func_get_arg(8);
			$this->NotDeclined = func_get_arg(9);
			$this->TriggerTime = func_get_arg(10);
			$this->TriggerAmount = func_get_arg(11);
			$this->NotInChat = func_get_arg(12);
			$this->Priority = func_get_arg(13);
			$this->IsActive = func_get_arg(14);
			$this->SearchPhrase = func_get_arg(15);
		}
	}
	
	function MatchesTriggerCriterias($_trigger)
	{
		$match = true;
		if($this->TriggerTime > 0 && $_trigger->TriggerTime >= (time()-$this->TriggerTime))
			$match = false;
		else if($this->TriggerAmount == 0 || ($this->TriggerAmount > 0 && $_trigger->Triggered > $this->TriggerAmount))
			$match = false;
		return $match;
	}
	
	function MatchesGlobalCriterias($_pageCount,$_timeOnSite,$_invAccepted,$_invDeclined,$_inChat,$_searchPhrase="")
	{
		$match = true;
		
		if($_timeOnSite<0)
			$_timeOnSite = 0;
		
		if($this->PagesVisited > 0 && $_pageCount < $this->PagesVisited)
			$match = false;
		else if($this->TimeOnSite > 0 && $_timeOnSite < $this->TimeOnSite)
			$match = false;
		else if(!empty($this->NotAccepted) && $_invAccepted)
			$match = false;
		else if(!empty($this->NotDeclined) && $_invDeclined)
			$match = false;
		else if(!empty($this->NotInChat) && $_inChat)
			$match = false;
			
		if(!empty($this->SearchPhrase))
		{
			if(empty($_searchPhrase))
				$match = false;
			else
			{
				$spmatch=false;
				$phrases = explode(",",$this->SearchPhrase);
				foreach($phrases as $phrase)
					if(compareUrls($phrase,$_searchPhrase))
					{
						$spmatch = true;
						break;
					}
				if(!$spmatch)
					$match = false;
			}
		}
		return $match;
	}
	
	function MatchesURLFunnelCriterias($_history)
	{
		$startpos = -1;
		$count = 0;
		$pos = 0;
		foreach($_history as $hpos => $hurl)
		{
			$fuid = "";
			$fcount = 0;
			$fuid = $this->FunnelUrls[$count];
			
			if($this->MatchUrls($this->URLs[$fuid],$hurl->Url->GetAbsoluteUrl(),$hurl->Referrer->GetAbsoluteUrl(),time()-($hurl->Entrance)) === true)
			{
				if($startpos==-1)
					$startpos = $pos;
					
				if($startpos+$count==$pos)
					$count++;
				else
				{
					$count = 0;
					$startpos=-1;
				}
				if($count==count($this->FunnelUrls))
					break;
			}
			else
			{
				$count = 0;
				$startpos=-1;
			}
			$pos++;
		}
		return $count==count($this->FunnelUrls);
	}
	
	function MatchesURLCriterias($_url,$_referrer,$_previous,$_timeOnUrl)
	{
		if(count($this->URLs) == 0)
			return true;
		$_url = @strtolower($_url);
		$_referrer = @strtolower($_referrer);
		$_previous = @strtolower($_previous);
		foreach($this->URLs as $url)
		{
			$match = $this->MatchUrls($url,$_url,$_referrer,$_timeOnUrl);
			if($match !== -1)
				return $match;
				
			$match = $this->MatchUrls($url,$_url,$_previous,$_timeOnUrl);
			if($match !== -1)
				return $match;
		}
		return false;
	}
	
	function MatchUrls($_eurl,$_url,$_referrer,$_timeOnUrl)
	{
		if($_eurl->TimeOnSite > 0 && $_eurl->TimeOnSite > $_timeOnUrl)
			return -1;
		$valid = true;
		if(!empty($_eurl->URL))
			$valid=compareUrls($_eurl->URL,$_url);
		if((!empty($_eurl->URL) && $valid || empty($_eurl->URL)) && !empty($_eurl->Referrer))
			$valid=compareUrls($_eurl->Referrer,$_referrer);
		if($valid)
			return !$_eurl->Blacklist;
		else
			return -1;
	}

	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENTS."` (`id`, `name`, `created`, `creator`, `edited`, `editor`, `pages_visited`, `time_on_site`, `max_trigger_amount`, `trigger_again_after`, `not_declined`, `not_accepted`, `not_in_chat`, `priority`, `is_active`, `search_phrase`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Created)."','".@mysql_real_escape_string($this->Creator)."','".@mysql_real_escape_string($this->Edited)."', '".@mysql_real_escape_string($this->Editor)."', '".@mysql_real_escape_string($this->PagesVisited)."','".@mysql_real_escape_string($this->TimeOnSite)."','".@mysql_real_escape_string($this->TriggerAmount)."','".@mysql_real_escape_string($this->TriggerTime)."', '".@mysql_real_escape_string($this->NotDeclined)."', '".@mysql_real_escape_string($this->NotAccepted)."', '".@mysql_real_escape_string($this->NotInChat)."', '".@mysql_real_escape_string($this->Priority)."', '".@mysql_real_escape_string($this->IsActive)."', '".@mysql_real_escape_string($this->SearchPhrase)."');";
	}

	function GetXML()
	{
		$xml = "<ev id=\"".base64_encode($this->Id)."\" nacc=\"".base64_encode($this->NotAccepted)."\" ndec=\"".base64_encode($this->NotDeclined)."\" name=\"".base64_encode($this->Name)."\" prio=\"".base64_encode($this->Priority)."\" created=\"".base64_encode($this->Created)."\" nic=\"".base64_encode($this->NotInChat)."\" creator=\"".base64_encode($this->Creator)."\" editor=\"".base64_encode($this->Editor)."\" edited=\"".base64_encode($this->Edited)."\" tos=\"".base64_encode($this->TimeOnSite)."\" ta=\"".base64_encode($this->TriggerAmount)."\" tt=\"".base64_encode($this->TriggerTime)."\" pv=\"".base64_encode($this->PagesVisited)."\" ia=\"".base64_encode($this->IsActive)."\" sp=\"".base64_encode($this->SearchPhrase)."\">\r\n";
		
		foreach($this->Actions as $action)
			$xml .= $action->GetXML();
		
		foreach($this->URLs as $url)
			$xml .= $url->GetXML();
			
		foreach($this->Goals as $act)
			$xml .= "<evg id=\"".base64_encode($act->Id)."\" />";
			
		foreach($this->FunnelUrls as $ind => $uid)
			$xml .= "<efu id=\"".base64_encode($uid)."\">".base64_encode($ind)."</efu>";

		return $xml . "</ev>\r\n";
	}
}

class Goal
{
	public $Id;
	public $Title;
	public $Description;
	public $Conversion;
	
	function Goal()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->Title = $_row["title"];
			$this->Description = $_row["description"];
			$this->Conversion = !empty($_row["conversion"]);
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Title = func_get_arg(1);
			$this->Description = func_get_arg(2);
			$this->Conversion = func_get_arg(3);
		}
	}
	
	function Save()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_GOALS."` (`id`, `title`, `description`, `conversion`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->Title)."','".@mysql_real_escape_string($this->Description)."', '".@mysql_real_escape_string($this->Conversion)."');";
	}

	function GetXML()
	{
		return "<tgt id=\"".base64_encode($this->Id)."\" title=\"".base64_encode($this->Title)."\" desc=\"".base64_encode($this->Description)."\" conv=\"".base64_encode($this->Conversion)."\" />\r\n";
	}
}

class PredefinedMessage
{
	public $Id = 0;
	public $LangISO = "";
	public $InvitationAuto = "";
	public $InvitationManual = "";
	public $Welcome = "";
	public $WebsitePushAuto = "";
	public $WebsitePushManual = "";
	public $BrowserIdentification = "";
	public $IsDefault;
	public $AutoWelcome;
	var	$GroupId = "";
	var	$UserId = "";
	public $Editable;
	
	function PredefinedMessage()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->LangISO = $_row["lang_iso"];
			$this->InvitationAuto = @$_row["invitation_auto"];
			$this->InvitationManual = @$_row["invitation_manual"];
			$this->Welcome = $_row["welcome"];
			$this->WebsitePushAuto = @$_row["website_push_auto"];
			$this->WebsitePushManual = @$_row["website_push_manual"];
			$this->BrowserIdentification = !empty($_row["browser_ident"]);
			$this->IsDefault = !empty($_row["is_default"]);
			$this->AutoWelcome = !empty($_row["auto_welcome"]);
			$this->Editable = !empty($_row["editable"]);
		}
	}
	
	function XMLParamAlloc($_param,$_value)
	{
		if($_param =="inva")
			$this->InvitationAuto = $_value;
		if($_param =="invm")
			$this->InvitationManual = $_value;
		if($_param =="wpa")
			$this->WebsitePushAuto = $_value;
		if($_param =="wpm")
			$this->WebsitePushManual = $_value;
		if($_param =="bi")
			$this->BrowserIdentification = $_value;
		if($_param =="wel")
			$this->Welcome = $_value;
		if($_param =="def")
			$this->IsDefault = $_value;
		if($_param =="aw")
			$this->AutoWelcome = $_value;
		if($_param =="edit")
			$this->Editable = $_value;
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`,`invitation_auto` ,`welcome` ,`website_push_manual` ,`website_push_auto` ,`browser_ident` ,`is_default` ,`auto_welcome`,`editable`)VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->LangISO)."', '".@mysql_real_escape_string($this->InvitationManual)."', '".@mysql_real_escape_string($this->InvitationAuto)."','".@mysql_real_escape_string($this->Welcome)."', '".@mysql_real_escape_string($this->WebsitePushManual)."', '".@mysql_real_escape_string($this->WebsitePushAuto)."', '".@mysql_real_escape_string($this->BrowserIdentification)."', '".@mysql_real_escape_string($this->IsDefault)."', '".@mysql_real_escape_string($this->AutoWelcome)."', '".@mysql_real_escape_string($this->Editable)."');");
	}

	function GetXML()
	{
		return "<pm lang=\"".base64_encode($this->LangISO)."\" invm=\"".base64_encode($this->InvitationManual)."\" inva=\"".base64_encode($this->InvitationAuto)."\" wel=\"".base64_encode($this->Welcome)."\" wpa=\"".base64_encode($this->WebsitePushAuto)."\" wpm=\"".base64_encode($this->WebsitePushManual)."\" bi=\"".base64_encode($this->BrowserIdentification)."\" def=\"".base64_encode($this->IsDefault)."\" aw=\"".base64_encode($this->AutoWelcome)."\" edit=\"".base64_encode($this->Editable)."\" />\r\n";
	}
}

class Profile
{
	public $LastEdited;
	public $Firstname;
	public $Name;
	public $Email;
	public $Company;
	public $Phone;
	public $Fax;
	public $Department;
	public $Street;
	public $City;
	public $ZIP;
	public $Country;
	public $Languages;
	public $Comments;
	public $Public;
	
	function Profile()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
            $this->Firstname = $row["first_name"];
            $this->Name = $row["last_name"];
            $this->Email = $row["email"];
            $this->Company = $row["company"];
            $this->Phone = $row["phone"];
            $this->Fax = $row["fax"];
            $this->Department = $row["department"];
            $this->Street = $row["street"];
            $this->City = $row["city"];
            $this->ZIP = $row["zip"];
            $this->Country = $row["country"];
            $this->Languages = $row["languages"];
            $this->Gender = $row["gender"];
            $this->Comments = $row["comments"];
			$this->Public = $row["public"];
			$this->LastEdited = $row["edited"];
		}
		else
		{
            $this->Firstname = func_get_arg(0);
            $this->Name = func_get_arg(1);
            $this->Email = func_get_arg(2);
            $this->Company = func_get_arg(3);
            $this->Phone = func_get_arg(4);
            $this->Fax = func_get_arg(5);
            $this->Department = func_get_arg(6);
            $this->Street = func_get_arg(7);
            $this->City = func_get_arg(8);
            $this->ZIP = func_get_arg(9);
            $this->Country = func_get_arg(10);
            $this->Languages = func_get_arg(11);
            $this->Gender = func_get_arg(12);
            $this->Comments = func_get_arg(13);
			$this->Public = func_get_arg(14);
		}
   	}
	
	function GetXML($_userId)
	{
		return "<p os=\"".base64_encode($_userId)."\" fn=\"".base64_encode($this->Firstname)."\" n=\"".base64_encode($this->Name)."\" e=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" p=\"".base64_encode($this->Phone)."\" f=\"".base64_encode($this->Fax)."\" d=\"".base64_encode($this->Department)."\" s=\"".base64_encode($this->Street)."\" z=\"".base64_encode($this->ZIP)."\" c=\"".base64_encode($this->Country)."\" l=\"".base64_encode($this->Languages)."\" ci=\"".base64_encode($this->City)."\" g=\"".base64_encode($this->Gender)."\" com=\"".base64_encode($this->Comments)."\" pu=\"".base64_encode($this->Public)."\" />\r\n";
	}

	function Save($_userId)
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_PROFILES."` (`id` ,`edited` ,`first_name` ,`last_name` ,`email` ,`company` ,`phone`  ,`fax` ,`street` ,`zip` ,`department` ,`city` ,`country` ,`gender` ,`languages` ,`comments` ,`public`) VALUES ('".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->Firstname)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."','".@mysql_real_escape_string($this->Phone)."','".@mysql_real_escape_string($this->Fax)."','".@mysql_real_escape_string($this->Street)."','".@mysql_real_escape_string($this->ZIP)."','".@mysql_real_escape_string($this->Department)."','".@mysql_real_escape_string($this->City)."','".@mysql_real_escape_string($this->Country)."','".@mysql_real_escape_string($this->Gender)."','".@mysql_real_escape_string($this->Languages)."','".@mysql_real_escape_string($this->Comments)."','".@mysql_real_escape_string($this->Public)."');");
	}
}
?>