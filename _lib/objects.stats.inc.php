<?php
/****************************************************************************************
* LiveZilla objects.stats.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class StatisticProvider
{
	public $CurrentDay;
	public $CurrentMonth;
	public $CurrentYear;
	public static $Rendertimes;
	public static $DayItemAmount;
	public static $MaxUsersAmount;
	public static $Dereferrer;
	public static $AggregationStorageTime;
	public static $AggregateDomains;
	public static $HiddenFilenames;
	public static $AllowedParameters;
	public static $TimeoutTrack;
	public static $Domains;
	public static $Blacklist;
	public static $SearchEngines;
	public static $RoundPrecision;
	public static $AllGoalsRequiredForConversion;
	public static $AutoUpdateTime = 3600;
	public static $StatisticKey;
	public static $Durations;
	public static $UpdateInterval;
	
	function StatisticProvider()
   	{
		StatisticProvider::DefineConfiguration();
		if($this->CloseAggregations())
			$this->CreateItems();
	}
	
	static function DeleteHTMLReports()
	{
		global $CONFIG;
		if(!empty($CONFIG["gl_st_ders"]) && is_numeric($CONFIG["gl_st_derd"]))
		{
			foreach(array(STATISTIC_PERIOD_TYPE_MONTH,STATISTIC_PERIOD_TYPE_YEAR,STATISTIC_PERIOD_TYPE_DAY) as $type)
			{
				$files = getDirectory(PATH_STATS . $type,"");
				foreach($files as $file)
				{
					$mtime = @filemtime(PATH_STATS . $type . "/" . $file);
					if(!empty($mtime) && $mtime < (time()-(86400*$CONFIG["gl_st_derd"])))
						@unlink(PATH_STATS . $type . "/" . $file);
				}
			}
			$tables = array(DATABASE_STATS_AGGS_GOALS,DATABASE_STATS_AGGS_PAGES_ENTRANCE,DATABASE_STATS_AGGS_PAGES_EXIT,DATABASE_STATS_AGGS_CRAWLERS,DATABASE_STATS_AGGS_DOMAINS,DATABASE_STATS_AGGS_BROWSERS,DATABASE_STATS_AGGS_RESOLUTIONS,DATABASE_STATS_AGGS_COUNTRIES,DATABASE_STATS_AGGS_VISITS,DATABASE_STATS_AGGS_SYSTEMS,DATABASE_STATS_AGGS_LANGUAGES,DATABASE_STATS_AGGS_CITIES,DATABASE_STATS_AGGS_REGIONS,DATABASE_STATS_AGGS_ISPS,DATABASE_STATS_AGGS_QUERIES,DATABASE_STATS_AGGS_PAGES,DATABASE_STATS_AGGS_REFERRERS,DATABASE_STATS_AGGS_AVAILABILITIES,DATABASE_STATS_AGGS_DURATIONS,DATABASE_STATS_AGGS_CHATS,DATABASE_STATS_AGGS_SEARCH_ENGINES,DATABASE_STATS_AGGS_VISITORS);
			$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `year`<".date("Y")." AND `aggregated`=1 AND `time`<".(time()-(86400*$CONFIG["gl_st_derd"]))." LIMIT 1;");
			if($result)
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
				{
					foreach($tables as $table)
						queryDB(true,"DELETE FROM `".DB_PREFIX.$table."` WHERE `year`<".date("Y")." AND day=".$row["day"]." AND month=".$row["month"]." AND year=".$row["year"]);
					queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `year`<".date("Y")." AND `aggregated`=1 AND day=".$row["day"]." AND month=".$row["month"]." AND year=".$row["year"]." LIMIT 1;");
				}
		}
	}
	
	static function DefineConfiguration()
	{
		global $CONFIG;
		StatisticProvider::$DayItemAmount = $CONFIG["gl_st_toam"];
		StatisticProvider::$MaxUsersAmount = $CONFIG["gl_st_muvl"];
		StatisticProvider::$Dereferrer = $CONFIG["gl_st_dere"];
		StatisticProvider::$AggregationStorageTime = 45;
		StatisticProvider::$AggregateDomains = !empty($CONFIG["gl_st_agdo"]);
		StatisticProvider::$HiddenFilenames = explode(",",$CONFIG["gl_st_hifi"]);
		StatisticProvider::$AllowedParameters = explode(",",$CONFIG["gl_st_getp"]);
		StatisticProvider::$UpdateInterval = $CONFIG["gl_st_upin"];
		StatisticProvider::$TimeoutTrack = $CONFIG["timeout_track"];
		StatisticProvider::$RoundPrecision = $CONFIG["gl_st_ropr"];
		StatisticProvider::$AllGoalsRequiredForConversion = $CONFIG["gl_st_atrc"];
		StatisticProvider::$StatisticKey = substr(md5($CONFIG["gl_lzid"]),0,12);
		StatisticProvider::$Durations = array(1=>"00 - 01 min",2=>"01 - 05 min",3=>"05 - 10 min",4=>"10 - 15 min",5=>"15 - 30 min",6=>"30 - 60 min",7=>"> 60 min");
	}

	function CreateItems()
	{
		$this->CurrentDay = new StatisticDay(date("Y"),date("n"),date("j"));
		if($this->CurrentDay->CreateReport)
			$this->CurrentDay->Save();
			
		$this->CurrentMonth = new StatisticMonth(date("Y"),date("n"),0);
		if($this->CurrentMonth->CreateReport)
			$this->CurrentMonth->Save();
			
		$this->CurrentYear = new StatisticYear(date("Y"),0,0);
		if($this->CurrentYear->CreateReport)
			$this->CurrentYear->Save();
	}

	function CloseAggregations()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE ((`day`<'".@mysql_real_escape_string(date("d"))."' AND `month`='".@mysql_real_escape_string(date("n"))."' AND `year`='".@mysql_real_escape_string(date("Y"))."') OR (`year`<'".@mysql_real_escape_string(date("Y"))."') OR (`month`<'".@mysql_real_escape_string(date("n"))."')) AND `aggregated`=0 AND `month`>0 AND `day`>0 ORDER BY `year` ASC,`month` ASC,`day` ASC LIMIT 1;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `aggregated`=1 WHERE `year`='".$row["year"]."' AND `month`='".$row["month"]."' AND `day`='".$row["day"]."' LIMIT 1;");
				$time=mktime(1,1,1,$row["month"],$row["day"],$row["year"]);
				$this->AggregateDay(date("Y",$time),date("n",$time),date("d",$time));
				return false;
			}
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `month`>0 AND ((`year`='".@mysql_real_escape_string(date("Y"))."' AND `month`<'".@mysql_real_escape_string(date("n"))."') OR (`year`<'".@mysql_real_escape_string(date("Y"))."')) AND `aggregated`=0 AND `day`=0 ORDER BY `year` ASC,`month` ASC LIMIT 1;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `aggregated`=1 WHERE `year`=".$row["year"]." AND `month`=".$row["month"]." AND `day`=0 LIMIT 1;");
				$this->AggregateMonth($row["year"],$row["month"]);
				return false;
			}
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `year`>0 AND `year`<'".date("Y")."' AND `day`=0  AND `month`=0 AND `aggregated`=0 ORDER BY `year` ASC LIMIT 1;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `aggregated`=1 WHERE `year`=".$row["year"]." AND `month`=0 AND `day`=0 LIMIT 1;");
				$this->AggregateYear($row["year"]);
				return false;
			}
		return true;
	}
	
	function AggregateMonth($_year,$_month)
	{
		$month = new StatisticMonth($_year,$_month,0);
		$month->Close();
	}
	
	function AggregateYear($_year)
	{
		$month = new StatisticYear($_year,0,0);
		$month->Close();
	}
	
	function AggregateDay($_year,$_month,$_day)
	{
		$day = new StatisticDay($_year,$_month,$_day);
		$day->Close();
	}
	
	function ProcessAction($_actionType,$_params=null)
	{
		if($_actionType == ST_ACTION_FORWARDED_CHAT)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `chats_forwards`=`chats_forwards`+1 WHERE".$this->CurrentDay->GetDateMatch(true,true,true).";");
		else if($_actionType == ST_ACTION_INTERNAL_POST)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `chats_posts_internal`=`chats_posts_internal`+1 WHERE".$this->CurrentDay->GetDateMatch(true,true,true).";");
		else if($_actionType == ST_ACTION_EXTERNAL_POST)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `chats_posts_external`=`chats_posts_external`+1 WHERE".$this->CurrentDay->GetDateMatch(true,true,true).";");
		else if($_actionType == ST_ACTION_LOG_STATUS)
			$this->LogStatus($_params[0]);
		else if($_actionType == ST_ACTION_LOG_CRAWLER_ACCESS)
			$this->LogCrawlerAccess($_params[0]);
		else if($_actionType == ST_ACTION_GOAL)
			$this->MarkGoalReached($_params[0],$_params[1],$_params[2]);
	}
	
	function MarkGoalReached($_visitorId,$_goalId,$_firstVisit)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_GOALS."` (`visitor_id`,`goal_id`,`time`,`first_visit`) VALUES ('".@mysql_real_escape_string($_visitorId)."','".@mysql_real_escape_string($_goalId)."','".time()."','".@mysql_real_escape_string($_firstVisit)."');");
	}
	
	function LogCrawlerAccess($_crawlerId)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_CRAWLERS."` (`year`,`month`,`day`,`crawler`) VALUES (".$this->CurrentDay->GetSQLDateValues().",'".@mysql_real_escape_string($_crawlerId)."');");
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS_CRAWLERS."` SET amount=amount+1 WHERE crawler='".@mysql_real_escape_string($_crawlerId)."' AND".$this->CurrentDay->GetDateMatch().";");
	}
	
	function LogStatus($_user)
	{
		global $INTERNAL;
		foreach($INTERNAL as $user)
			if($user->Status != USER_STATUS_OFFLINE)
				$states[] = $user->Status;
		
		$identities = array($_user->SystemId=>$_user->Status,GROUP_EVERYONE_INTERN=>((isset($states)) ? min($states) : USER_STATUS_OFFLINE));
		foreach($identities as $userid => $status)
		{
			$result = queryDB(true,"SELECT `status`,`time`,`confirmed` FROM `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` WHERE `internal_id`='".@mysql_real_escape_string($userid)."' ORDER BY `time` DESC LIMIT 1;");
			if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$openUserSpan = (@mysql_num_rows($result) == 1);
				if(date("z",$row["time"])>date("z") || (date("z",$row["time"])==date("z") && date("H",$row["time"])>date("H")))
					return;
				
				if($openUserSpan && $row["status"] == $status && date("z",$row["time"])==date("z") && date("H",$row["time"])==date("H"))
					queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` SET `confirmed`='".@mysql_real_escape_string(time())."' WHERE `internal_id`='".@mysql_real_escape_string($userid)."' ORDER BY `time` DESC LIMIT 1;");
				else
				{
					$time = time()-1;
					if($openUserSpan)
					{
						$time = ($row["status"] == $status) ? mktime(date("H",$row["time"])+1,0,0,date("n",$row["time"]),date("d",$row["time"]),date("Y",$row["time"])) : time();
						queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` SET `confirmed`='".@mysql_real_escape_string($time)."' WHERE `internal_id`='".@mysql_real_escape_string($userid)."' ORDER BY `time` DESC LIMIT 1;");
					}
					queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` (`time` ,`confirmed` ,`internal_id` ,`status`) VALUES ('".@mysql_real_escape_string($time)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($userid)."','".@mysql_real_escape_string($status)."');");
				}
			}
			else
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` (`time` ,`confirmed` ,`internal_id` ,`status`) VALUES ('".@mysql_real_escape_string(time()-1)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($userid)."','".@mysql_real_escape_string($status)."');");
		}
	}
	
	function ResetData()
	{
		$tables = array(DATABASE_VISITOR_GOALS,DATABASE_VISITOR_DATA_AREA_CODES,DATABASE_VISITOR_DATA_CRAWLERS,DATABASE_VISITOR_DATA_BROWSERS,DATABASE_VISITOR_DATA_TITLES,DATABASE_VISITOR_DATA_PATHS,DATABASE_VISITOR_DATA_CITIES,DATABASE_VISITOR_DATA_REGIONS,DATABASE_VISITOR_DATA_ISPS,DATABASE_VISITOR_DATA_PAGES,DATABASE_VISITOR_DATA_DOMAINS,DATABASE_VISITOR_DATA_QUERIES,DATABASE_VISITOR_DATA_SYSTEMS,DATABASE_VISITOR_DATA_RESOLUTIONS);
		foreach($tables as $table)
			queryDB(true,"DELETE FROM `".DB_PREFIX.$table."`;");
	}
	
	function ResetDays()
	{
		$tables = array(DATABASE_STATS_AGGS_GOALS,DATABASE_STATS_AGGS_PAGES_ENTRANCE,DATABASE_STATS_AGGS_PAGES_EXIT,DATABASE_STATS_AGGS,DATABASE_STATS_AGGS_CRAWLERS,DATABASE_STATS_AGGS_AVAILABILITIES,DATABASE_STATS_AGGS_DOMAINS,DATABASE_STATS_AGGS_BROWSERS,DATABASE_STATS_AGGS_RESOLUTIONS,DATABASE_STATS_AGGS_COUNTRIES,DATABASE_STATS_AGGS_VISITS,DATABASE_STATS_AGGS_SYSTEMS,DATABASE_STATS_AGGS_LANGUAGES,DATABASE_STATS_AGGS_CITIES,DATABASE_STATS_AGGS_REGIONS,DATABASE_STATS_AGGS_ISPS,DATABASE_STATS_AGGS_QUERIES,DATABASE_STATS_AGGS_PAGES,DATABASE_STATS_AGGS_REFERRERS,DATABASE_STATS_AGGS_DURATIONS,DATABASE_STATS_AGGS_CHATS,DATABASE_STATS_AGGS_SEARCH_ENGINES,DATABASE_STATS_AGGS_VISITORS);
		foreach($tables as $table)
			queryDB(true,"DELETE FROM `".DB_PREFIX.$table."`;");
	}
	
	function ResetAll()
	{
		$tables = array(DATABASE_VISITOR_BROWSERS,DATABASE_VISITOR_BROWSER_URLS,DATABASE_VISITORS,DATABASE_OPERATOR_STATUS);
		foreach($tables as $table)
			queryDB(true,"DELETE FROM `".DB_PREFIX.$table."`;");
		$this->ResetDays();
		$this->ResetData();
	}
	
	static function LogProcess($_process)
	{
		//logit($_process);
	}
	
	static function SetExecutionPoint($_partId)
	{
		self::$Rendertimes[] = array($_partId,microtimeFloat(microtime()));
	}
	
	static function GetExecutionTime()
	{
		$parts = "(" . self::$Rendertimes[0][0] . ")";
		for($int=1;$int<count(self::$Rendertimes);$int++)
			$parts .= " | " . round(self::$Rendertimes[$int][1]-self::$Rendertimes[$int-1][1],3) . " (" . self::$Rendertimes[$int][0] . ")";
		return $parts;
	}
}

class StatisticYear extends StatisticPeriod
{
	function StatisticYear($_year,$_month,$_day)
	{
		parent::__construct($_year,$_month,$_day);
		$this->DayCount = date("z", mktime(0,0,0,12,31,$_year))+1;
		$this->Type = STATISTIC_PERIOD_TYPE_YEAR;
    	$this->StartTime = mktime(0,0,0,1,1,$_year);
		$this->EndTime = strtotime("12/31/".$_year." 23:59:59");
		$this->Delimiters = array($this->StartTime,$this->EndTime);
		$this->Closed = ($_year != date("Y"));
		$this->DefineConfiguration();
	}
	
	function DefineConfiguration()
	{
		global $CONFIG;
		$this->CreateReport = !empty($CONFIG["gl_st_yarp"]);
		$this->IncludeBHVisitors = !empty($CONFIG["gl_st_ybhv"]);
		$this->IncludeBHChats = !empty($CONFIG["gl_st_ybhc"]);
		$this->IncludeBMVisitors = !empty($CONFIG["gl_st_ybmv"]);
		$this->IncludeBMChats = !empty($CONFIG["gl_st_ybmc"]);
		$this->IncludeTOPSystems = !empty($CONFIG["gl_st_ytsy"]);
		$this->IncludeTOPOrigins = !empty($CONFIG["gl_st_ytor"]);
		$this->IncludeTOPVisits = !empty($CONFIG["gl_st_ytvi"]);
		$this->IncludeTOPISPs = !empty($CONFIG["gl_st_ytis"]);
		$this->IncludeTOPPages = !empty($CONFIG["gl_st_ytpa"]);
		$this->IncludeTOPEntranceExit = !empty($CONFIG["gl_st_ytee"]);
		$this->IncludeTOPSearch = !empty($CONFIG["gl_st_ytse"]);
		$this->IncludeTOPReferrers = !empty($CONFIG["gl_st_ytre"]);
		$this->IncludeTOPDomains = !empty($CONFIG["gl_st_ytdo"]);
		$this->IncludeBOAvailability = !empty($CONFIG["gl_st_yboa"]);
		$this->IncludeBOChats = !empty($CONFIG["gl_st_yboc"]);
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT SUM(`on_chat_page`) AS `spages`,SUM(`browser_instances`) AS `bi`,SUM(`from_referrer`) AS `ref`,SUM(`search_engine`) AS `se`,SUM(`bounces`) AS `bounc`,SUM(`page_impressions`) AS `pi`,SUM(`visitors_unique`) AS `cvunique`,SUM(`js`) AS `json`,SUM(`visitors_recurring`) AS `rec` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` WHERE".$this->GetDateMatch(true,false,false).";");
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		$this->VisitorsTotal = (!empty($row["cvunique"]))?$row["cvunique"]:0;
		$this->ChatPagesTotal = (!empty($row["spages"]))?$row["spages"]:0;
		$this->JavascriptTotal = $row["json"];
		$this->VisitorsRecurringTotal = $row["rec"];
		$this->PageImpressionsTotal = ((!empty($row["pi"]))?$row["pi"]:0) - $this->ChatPagesTotal;
		$this->VisitorBouncesTotal = $row["bounc"];
		$this->FromSearchEngineTotal = $row["se"];
		$this->FromReferrerTotal = $row["ref"];
		$this->BrowserInstancesTotal = $row["bi"];
		return parent::Load();
	}
	
	function LoadComparer()
	{
		StatisticProvider::LogProcess("LOAD COMPARER YEAR: " . $this->Year);
		$this->Comparer = new StatisticYear($this->Year-1,0,0);
		$this->Comparer->LoadMonths();
		if(!$this->Comparer->Load())
			$this->Comparer = null;
	}
	
	function LoadMonths()
	{
		for($i=1;$i<=12;$i++)
		{
			$time = mktime(0,0,0,$i,1,$this->Year);
			$this->Months[$i] = new StatisticMonth(date("Y",$time),$i,0);
			if(date("Y",$time)==date("Y") && date("n",$time) == date("n"))
				$this->Months[$i]->SaveReportToFile();
			else
				$this->Months[$i]->Load();
		}
		ksort($this->Months);
	}
	
	function GetHTML()
	{
		$html = parent::GetHTML();
		$html = str_replace("<!--header_span_overview-->",$this->Year . " (".date("d.m.Y",$this->StartTime) . " - " . date("d.m.Y",$this->EndTime).")",$html);
		return $html;
	}
	
	function Aggregate($_daySQL = "")
	{
		StatisticProvider::LogProcess("AGG YEAR: " . $this->Year);
		for($int=1;$int<$this->DayCount+1;$int++)
		{
			if(date("Y",$this->StartTime+(86400*($int-1))) == $this->Year)
			{
				$day = date("d",$this->StartTime+(86400*($int-1)));
				if(empty($_daySQL))
					$_daySQL .= " AND (`day`=".@mysql_real_escape_string($day);
				else
					$_daySQL .= " OR `day`=".@mysql_real_escape_string($day);
			}
		}
		
		if(!empty($_daySQL))
		{
			$_daySQL .= ") ";
			foreach(array($this->TopVisitorTables,$this->TopBrowserTables,$this->TopBrowserURLTables) as $tables)
				foreach($tables as $table => $field)
					$this->AggregateValueCount(DB_PREFIX.$table,$field,$_daySQL);
		}
		$this->AggregateValueCount(DB_PREFIX.DATABASE_STATS_AGGS_CRAWLERS,"crawler",$_daySQL);
		$this->AggregateValueCount(DB_PREFIX.DATABASE_STATS_AGGS_GOALS,"goal",$_daySQL);
		$this->AggregateValueCount(DB_PREFIX.DATABASE_STATS_AGGS_DOMAINS,"domain",$_daySQL);
		$this->AggregateSingleValues($_daySQL);
		$this->AggregateDurations($_daySQL);
	}

	function AggregateSingleValues($_daySQL)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT SUM(`conversions`) AS `sconversions`,SUM(`sessions`) AS `svisitors`, SUM(`visitors_unique`) AS `uvisitors`,AVG(`avg_time_site`) AS `aavg_time_site`, SUM(`chats_posts_external`) AS `schats_posts_external`, SUM(`chats_posts_internal`) AS `schats_posts_internal`,SUM(`chats_forwards`) as `schats_forwards` FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE".$this->GetDateMatch(true,false,false).$_daySQL.";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS."` (`year`, `month`, `day`,`time`,`mtime`,`sessions`, `visitors_unique`,`conversions`, `aggregated`,`chats_forwards`, `chats_posts_internal`, `chats_posts_external`, `avg_time_site`) VALUES (".$this->GetSQLDateValues().",".time().",".mTime().",'".@mysql_real_escape_string($row["svisitors"])."','".@mysql_real_escape_string($row["uvisitors"])."','".@mysql_real_escape_string($row["sconversions"])."','".@mysql_real_escape_string(($this->Closed)?1:0)."','".@mysql_real_escape_string($row["schats_forwards"])."','".@mysql_real_escape_string($row["schats_posts_internal"])."','".@mysql_real_escape_string($row["schats_posts_external"])."','".@mysql_real_escape_string($row["aavg_time_site"])."')");
	}
	
	function AggregateDurations($_daySQL)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT `duration`,SUM(`amount`) AS `samount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` WHERE".$this->GetDateMatch(true,false,false).$_daySQL." GROUP BY `duration`;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` ( `year` ,`month` ,`day` ,`duration` ,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row["duration"])."','".@mysql_real_escape_string($row["samount"])."')");
	}
	
	function AggregateValueCount($_table,$_fields,$_daySQL,$count=0)
	{
		StatisticProvider::LogProcess("AGGREGATE YEAR: " . $this->Year);
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		if(DATABASE_STATS_AGGS_PAGES==$_table)
			$result = queryDB(true,"SELECT *,SUM(`amount`) AS `sumfield` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_PAGES."`  INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS t1 ON `".DB_PREFIX.DATABASE_STATS_AGGS_PAGES."`.`url`=t1.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` AS `t2` ON `t1`.`path` = t2.`id` WHERE".$this->GetDateMatch(true,false,false).$_daySQL." GROUP BY `t1`.`id` ORDER BY `sumfield` DESC,`t1`.`id` DESC");
		else
			$result = queryDB(true,"SELECT *,SUM(`amount`) AS `sumfield` FROM `".$_table."` WHERE".$this->GetDateMatch(true,false,false).$_daySQL." GROUP BY `".$_fields."` ORDER BY `sumfield` DESC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if($row["sumfield"] > 0 && $count++ < StatisticProvider::$DayItemAmount)
				queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`".$_fields."`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row[$_fields])."','".@mysql_real_escape_string($row["sumfield"])."')");
	}
}

class StatisticMonth extends StatisticPeriod
{
	function StatisticMonth($_year,$_month,$_day)
	{
		parent::__construct($_year,$_month,$_day);
		$this->DayCount = date("t", strtotime($_year . "-" . $_month . "-01"));
		$this->Type = STATISTIC_PERIOD_TYPE_MONTH;
    	$this->StartTime = mktime(0,0,0,$_month,1,$_year);
		$this->EndTime = strtotime("-1 second",strtotime("+1 month",strtotime($_month."/01/".$_year." 00:00:00")));
		$this->Delimiters = array($this->StartTime,$this->EndTime);
		$this->DefineConfiguration();
		$this->Closed = ($_month != date("n"));
	}
	
	function DefineConfiguration()
	{
		global $CONFIG;
		$this->CreateReport = !empty($CONFIG["gl_st_marp"]);
		$this->IncludeBHVisitors = !empty($CONFIG["gl_st_mbhv"]);
		$this->IncludeBHChats = !empty($CONFIG["gl_st_mbhc"]);
		$this->IncludeBDVisitors = !empty($CONFIG["gl_st_mbdv"]);
		$this->IncludeBDChats = !empty($CONFIG["gl_st_mbdc"]);
		$this->IncludeTOPSystems = !empty($CONFIG["gl_st_mtsy"]);
		$this->IncludeTOPOrigins = !empty($CONFIG["gl_st_mtor"]);
		$this->IncludeTOPVisits = !empty($CONFIG["gl_st_mtvi"]);
		$this->IncludeTOPISPs = !empty($CONFIG["gl_st_mtis"]);
		$this->IncludeTOPPages = !empty($CONFIG["gl_st_mtpa"]);
		$this->IncludeTOPEntranceExit = !empty($CONFIG["gl_st_mtee"]);
		$this->IncludeTOPSearch = !empty($CONFIG["gl_st_mtse"]);
		$this->IncludeTOPReferrers = !empty($CONFIG["gl_st_mtre"]);
		$this->IncludeTOPDomains = !empty($CONFIG["gl_st_mtdo"]);
		
		$this->IncludeBOAvailability = !empty($CONFIG["gl_st_mboa"]);
		$this->IncludeBOChats = !empty($CONFIG["gl_st_mboc"]);
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT SUM(`on_chat_page`) AS `spages`,SUM(`browser_instances`) AS `bi`,SUM(`from_referrer`) AS `ref`,SUM(`search_engine`) AS `se`,SUM(`bounces`) AS `bounc`,SUM(`page_impressions`) AS `pi`,SUM(`visitors_unique`) AS `cvunique`,SUM(`js`) AS `json`,SUM(`visitors_recurring`) AS `rec` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` WHERE".$this->GetDateMatch(true,true,false).";");
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		$this->VisitorsTotal = (!empty($row["cvunique"]))?$row["cvunique"]:0;
		$this->ChatPagesTotal = (!empty($row["spages"]))?$row["spages"]:0;
		$this->JavascriptTotal = $row["json"];
		$this->VisitorsRecurringTotal = $row["rec"];
		$this->PageImpressionsTotal = ((!empty($row["pi"]))?$row["pi"]:0) - $this->ChatPagesTotal;
		$this->VisitorBouncesTotal = $row["bounc"];
		$this->FromSearchEngineTotal = $row["se"];
		$this->FromReferrerTotal = $row["ref"];
		$this->BrowserInstancesTotal = $row["bi"];
		return parent::Load();
	}
	
	function LoadComparer()
	{
		StatisticProvider::LogProcess("LOAD COMPARER MONTH: " . $this->Month);
		$time = strtotime("-1 month",mktime(0,0,0,$this->Month,1,$this->Year));
		$this->Comparer = new StatisticMonth(date("Y",$time),date("n",$time),0);
		$this->Comparer->LoadDays();
		if(!$this->Comparer->Load())
			$this->Comparer = null;
	}
	
	function LoadDays()
	{
		for($i=0;$i<$this->DayCount;$i++)
		{
			$time = $this->StartTime+($i*86400);
			$day = date("d",$time);
			if(!isset($this->Days[$day]))
			{
				$this->Days[$day] = new StatisticDay(date("Y",$time),date("n",$time),$day);
				
				if(date("Y",$time)==date("Y") && date("n",$time) == date("n") && $day == date("d"))
					$this->Days[$day]->SaveReportToFile();
				else
					$this->Days[$day]->Load();
			}
		}
		ksort($this->Days);
	}
	
	function GetHTML()
	{
		$html = parent::GetHTML();
		$html = str_replace("<!--header_span_overview-->",$this->Month . " / " . $this->Year . " (".date("d.m.Y",$this->StartTime) . " - " . date("d.m.Y",$this->EndTime).")",$html);
		return $html;
	}
	
	function Aggregate($_daySQL = "")
	{
		StatisticProvider::LogProcess("AGG MONTH: " . $this->Month);
		for($int=1;$int<$this->DayCount+1;$int++)
		{
			if(date("n",$this->StartTime+(86400*($int-1))) == $this->Month)
			{
				$day = date("d",$this->StartTime+(86400*($int-1)));
				if(empty($_daySQL))
					$_daySQL .= " AND (`day`=".@mysql_real_escape_string($day);
				else
					$_daySQL .= " OR `day`=".@mysql_real_escape_string($day);
			}
		}
		
		if(!empty($_daySQL))
		{
			$_daySQL .= ") ";
			foreach(array($this->TopVisitorTables,$this->TopBrowserTables,$this->TopBrowserURLTables) as $tables)
				foreach($tables as $table => $field)
					$this->AggregateValueCount(DB_PREFIX.$table,$field,$_daySQL);
		}
		$this->AggregateValueCount(DB_PREFIX.DATABASE_STATS_AGGS_CRAWLERS,"crawler",$_daySQL);
		$this->AggregateValueCount(DB_PREFIX.DATABASE_STATS_AGGS_GOALS,"goal",$_daySQL);
		$this->AggregateValueCount(DB_PREFIX.DATABASE_STATS_AGGS_DOMAINS,"domain",$_daySQL);
		$this->AggregateSingleValues($_daySQL);
		$this->AggregateDurations($_daySQL);
	}

	function AggregateSingleValues($_daySQL)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT SUM(`conversions`) AS `sconversions`,SUM(`sessions`) AS `svisitors`,SUM(`visitors_unique`) AS `uvisitors`,AVG(`avg_time_site`) AS `aavg_time_site`, SUM(`chats_posts_external`) AS `schats_posts_external`, SUM(`chats_posts_internal`) AS `schats_posts_internal`,SUM(`chats_forwards`) as `schats_forwards` FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE".$this->GetDateMatch(true,true,false).$_daySQL.";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS."` (`year`, `month`, `day`,`time`,`mtime`,`sessions`,`visitors_unique`,`conversions`,`aggregated`, `chats_forwards`, `chats_posts_internal`, `chats_posts_external`, `avg_time_site`) VALUES (".$this->GetSQLDateValues().",".time().",".mTime().",'".@mysql_real_escape_string($row["svisitors"])."','".@mysql_real_escape_string($row["uvisitors"])."','".@mysql_real_escape_string($row["sconversions"])."','".@mysql_real_escape_string(($this->Closed)?1:0)."','".@mysql_real_escape_string($row["schats_forwards"])."','".@mysql_real_escape_string($row["schats_posts_internal"])."','".@mysql_real_escape_string($row["schats_posts_external"])."','".@mysql_real_escape_string($row["aavg_time_site"])."')");
	}
	
	function AggregateDurations($_daySQL)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT `duration`,SUM(`amount`) AS `samount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` WHERE".$this->GetDateMatch(true,true,false).$_daySQL." GROUP BY `duration`;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` ( `year` ,`month`,`day` ,`duration` ,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row["duration"])."','".@mysql_real_escape_string($row["samount"])."')");
	}
	
	function AggregateValueCount($_table,$_fields,$_daySQL,$count=0)
	{
		StatisticProvider::LogProcess("AGGREGATE MONTH: " . $this->Month);
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		if(DATABASE_STATS_AGGS_PAGES==$_table)
			$result = queryDB(true,"SELECT *,SUM(`amount`) AS `sumfield` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_PAGES."`  INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS t1 ON `".DB_PREFIX.DATABASE_STATS_AGGS_PAGES."`.`url`=t1.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` AS `t2` ON `t1`.`path` = t2.`id` WHERE".$this->GetDateMatch(true,true,false).$_daySQL." GROUP BY `t1`.`id` ORDER BY `sumfield` DESC,`t1`.`id` DESC");
		else
			$result = queryDB(true,"SELECT *,SUM(`amount`) AS `sumfield` FROM `".$_table."` WHERE".$this->GetDateMatch(true,true,false).$_daySQL." GROUP BY `".$_fields."` ORDER BY `sumfield` DESC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if($row["sumfield"] > 0 && $count++ < StatisticProvider::$DayItemAmount)
				queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`".$_fields."`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row[$_fields])."','".@mysql_real_escape_string($row["sumfield"])."')");
	}
}

class StatisticDay extends StatisticPeriod
{
	function StatisticDay()
	{
		$this->Type = STATISTIC_PERIOD_TYPE_DAY;
		$this->DayCount = 1;
		if(func_num_args() >= 3)
		{
			$year = func_get_arg(0);
			$months = func_get_arg(1);
			$day = func_get_arg(2);
			
			parent::__construct($year,$months,$day);
			$this->Delimiters = array(mktime(0,0,0,$this->Month,$this->Day,$this->Year),mktime(23,59,59,$this->Month,$this->Day,$this->Year));
			if($day != date("d"))
				$this->Closed = true;
		}
		else
		{
			$time = func_get_arg(0);
			parent::__construct(date("Y",$time),date("n",$time),date("d",$time));
		}
		$this->DefineConfiguration();
	}
	
	function DefineConfiguration()
	{
		global $CONFIG;
		$this->CreateReport = !empty($CONFIG["gl_st_darp"]);
		$this->CreateVisitorList = !empty($CONFIG["gl_st_davl"]);
		$this->IncludeBHVisitors = !empty($CONFIG["gl_st_dbhv"]);
		$this->IncludeBHChats = !empty($CONFIG["gl_st_dbhc"]);
		$this->IncludeTOPSystems = !empty($CONFIG["gl_st_dtsy"]);
		$this->IncludeTOPOrigins = !empty($CONFIG["gl_st_dtor"]);
		$this->IncludeTOPVisits = !empty($CONFIG["gl_st_dtvi"]);
		$this->IncludeTOPISPs = !empty($CONFIG["gl_st_dtis"]);
		$this->IncludeTOPPages = !empty($CONFIG["gl_st_dtpa"]);
		$this->IncludeTOPEntranceExit = !empty($CONFIG["gl_st_dtee"]);
		$this->IncludeTOPSearch = !empty($CONFIG["gl_st_dtse"]);
		$this->IncludeTOPReferrers = !empty($CONFIG["gl_st_dtre"]);
		$this->IncludeTOPDomains = !empty($CONFIG["gl_st_dtdo"]);
		
		$this->IncludeBOAvailability = !empty($CONFIG["gl_st_dboa"]);
		$this->IncludeBOChats = !empty($CONFIG["gl_st_dboc"]);
	}
	
	function LoadComparer()
	{
		$this->Comparer = new StatisticDay($this->Delimiters[0]-86400);
		if(!$this->Comparer->Load())
			$this->Comparer = null;
	}
	
	function Load()
	{
		StatisticProvider::LogProcess("LOAD DAY: " . $this->Day);
		$row = mysql_fetch_array(queryDB(true,"SELECT SUM(`on_chat_page`) AS `spages`,SUM(`browser_instances`) AS `bi`,SUM(`from_referrer`) AS `ref`,SUM(`search_engine`) AS `se`,SUM(`bounces`) AS `bounc`,SUM(`page_impressions`) AS `pi`,SUM(`visitors_unique`) AS `cvunique`,SUM(`js`) AS `json`,SUM(`visitors_recurring`) AS `rec` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` WHERE".$this->GetDateMatch().";"), MYSQL_BOTH);
		$this->VisitorsTotal = (!empty($row["cvunique"]))?$row["cvunique"]:0;
		$this->ChatPagesTotal = (!empty($row["spages"]))?$row["spages"]:0;
		$this->JavascriptTotal = $row["json"];
		$this->VisitorsRecurringTotal = $row["rec"];
		$this->PageImpressionsTotal = ((!empty($row["pi"]))?$row["pi"]:0) - $this->ChatPagesTotal;
		$this->VisitorBouncesTotal = $row["bounc"];
		$this->FromSearchEngineTotal = $row["se"];
		$this->FromReferrerTotal = $row["ref"];
		$this->BrowserInstancesTotal = $row["bi"];
		return parent::Load();
	}

	function GetUsersHTML()
	{
		$html = parent::GetUsersHTML();
		$html = str_replace("<!--header_span_overview-->",date("d.m.Y",mktime(0,0,0,$this->Month,$this->Day,$this->Year)),$html);
		return $html;
	}
	
	function GetHTML()
	{
		$html = parent::GetHTML();
		$html = str_replace("<!--header_span_overview-->",date("d.m.Y",mktime(0,0,0,$this->Month,$this->Day,$this->Year)),$html);
		return $html;
	}
	
	function Aggregate()
	{
		$this->AggregateSingleValues("");
		$this->AggregateOperatorTime("");
		$this->AggregateDurations("");
		$this->AggregateChats("");
		$this->AggregateTOPS();
	}
	
	function AggregateTOPS()
	{
		foreach($this->TopVisitorTables as $table => $field)
			$this->AggregateValueCount(DB_PREFIX.$table,DB_PREFIX.DATABASE_VISITORS,$field,"entrance",true);
		foreach($this->TopBrowserTables as $table => $field)
			$this->AggregateValueCount(DB_PREFIX.$table,DB_PREFIX.DATABASE_VISITOR_BROWSERS,$field,"last_active");
			
		$this->AggregateDayPageCount(DB_PREFIX.DATABASE_STATS_AGGS_DOMAINS,"domain","`t1`.`domain`","`t1`.`domain`");
		$this->AggregateDayPageCount(DB_PREFIX.DATABASE_STATS_AGGS_PAGES,"url","`".DB_PREFIX."visitor_browser_urls`.`url`",((StatisticProvider::$AggregateDomains)?"`t1`.`path`":"`".DB_PREFIX."visitor_browser_urls`.`url`"));
		$this->AggregateDayEntranceExitPageCount(DB_PREFIX.DATABASE_STATS_AGGS_PAGES_ENTRANCE,false,",`t1`.`domain`");
		$this->AggregateDayEntranceExitPageCount(DB_PREFIX.DATABASE_STATS_AGGS_PAGES_EXIT,true,",`t1`.`domain`");
		$this->AggregateDayReferrerCount(DB_PREFIX.DATABASE_STATS_AGGS_REFERRERS);
		$this->AggregateDaySearchEngineCount(DB_PREFIX.DATABASE_STATS_AGGS_SEARCH_ENGINES);
		$this->AggregateDayGoals();
	}
	
	function AggregateSingleValues($_empty)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` WHERE".$this->GetDateMatch().";");
		
		$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(DISTINCT(`id`)) `uvisitors` FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).";"), MYSQL_BOTH);
		$this->VisitorsUnique = $row["uvisitors"];
		
		for($i=0;$i<24;$i++)
		{
			$hour_delimiters = array(mktime($i,0,0,$this->Month,$this->Day,$this->Year),mktime($i,59,59,$this->Month,$this->Day,$this->Year));
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(`id`) as `cvunique`,(SELECT COUNT(DISTINCT(`id`)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `js`=0 AND `entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($hour_delimiters[1]).") as `json` FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($hour_delimiters[1]).";"), MYSQL_BOTH);
			$this->VisitorsUniqueHour = $row["cvunique"];
			$this->JavascriptHour = $row["cvunique"]-$row["json"];
			
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(`id`) as `cvrec` FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `visits`>1 AND `entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($hour_delimiters[1]).";"), MYSQL_BOTH);
			$this->VisitorsRecurringHour = $row["cvrec"];
			
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(`browser_id`) as `urls` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `t1` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`url`=`t1`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `t2` ON `t1`.`domain` = `t2`.`id` WHERE `t2`.`search`=0 AND `t2`.`external`=0 AND `entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($hour_delimiters[1]).";"), MYSQL_BOTH);
			$this->PageImpressionsHour = $row["urls"];
			
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(`id`) as `browsers`,(SELECT COUNT(`id`) FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE is_chat=1 AND `created`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `created`<=".@mysql_real_escape_string($hour_delimiters[1]).") as cpages FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `created`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `created`<=".@mysql_real_escape_string($hour_delimiters[1]).";"), MYSQL_BOTH);
			$this->BrowserInstancesHour = $row["browsers"];
			$this->ChatPagesHour = $row["cpages"];
			$this->VisitorBouncesHour = mysql_num_rows(queryDB(true,"SELECT COUNT(`visitor_id`) as `bvisitors` FROM `".DB_PREFIX.DATABASE_VISITORS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` ON `".DB_PREFIX.DATABASE_VISITORS."`.`id`=`".DB_PREFIX.DATABASE_VISITOR_BROWSERS."`.`visitor_id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."`.`id`=`".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id` WHERE `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($hour_delimiters[1])." GROUP BY `visitor_id` HAVING `bvisitors`=1;"));
			
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(*) AS `fseh` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`  INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `t1` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer`=`t1`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `t2` ON `t1`.`domain` = `t2`.`id` WHERE `t2`.`search`=1 AND `t2`.`external`=1 AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`<=".@mysql_real_escape_string($hour_delimiters[1])." AND `t2`.`domain`!='';"), MYSQL_BOTH);
			$this->FromSearchEngineHour = $row["fseh"];
			
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(*) AS `frh` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`  INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `t1` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer`=`t1`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `t2` ON `t1`.`domain` = `t2`.`id` WHERE `t2`.`search`=0 AND `t2`.`external`=1 AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`>=".@mysql_real_escape_string($hour_delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`<=".@mysql_real_escape_string($hour_delimiters[1])." AND `t2`.`domain`!='';"), MYSQL_BOTH);
			$this->FromReferrerHour = $row["frh"];
			
			if($this->ChatPagesHour > 0 || $this->VisitorsUniqueHour > 0 || $this->PageImpressionsHour > 0 || $this->VisitorsRecurringHour > 0 || $this->VisitorBouncesHour > 0 || $this->FromSearchEngineHour > 0 || $this->FromReferrerHour > 0)
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` (`year`,`month`,`day`,`hour`,`visitors_unique`,`page_impressions`,`visitors_recurring`,`bounces`,`search_engine`,`from_referrer`,`browser_instances`,`js`,`on_chat_page`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($i)."','".@mysql_real_escape_string($this->VisitorsUniqueHour)."','".@mysql_real_escape_string($this->PageImpressionsHour)."','".@mysql_real_escape_string($this->VisitorsRecurringHour)."','".@mysql_real_escape_string($this->VisitorBouncesHour)."','".@mysql_real_escape_string($this->FromSearchEngineHour)."','".@mysql_real_escape_string($this->FromReferrerHour)."','".@mysql_real_escape_string($this->BrowserInstancesHour)."','".@mysql_real_escape_string($this->JavascriptHour)."','".@mysql_real_escape_string($this->ChatPagesHour)."');");
		}
		$row = mysql_fetch_array(queryDB(true,"SELECT AVG(`last_active`-`entrance`) as `avgs`,COUNT(`id`) as `cv` FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).";"), MYSQL_BOTH);
		$this->AVGTimeOnSiteTotal = ($row["avgs"]!=null) ? $row["avgs"] : 0;
		$this->VisitorsTotal = $row["cv"];
		
		if(!StatisticProvider::$AllGoalsRequiredForConversion)
			$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(DISTINCT(`t1`.`visitor_id`)) as `cvconv` FROM `".DB_PREFIX.DATABASE_VISITOR_GOALS."` as `t1` INNER JOIN `".DB_PREFIX.DATABASE_GOALS."` as `t2` ON `t1`.`goal_id`=`t2`.`id` WHERE `t2`.`conversion`=1 AND `t1`.`time`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `t1`.`time`<=".@mysql_real_escape_string($this->Delimiters[1]).";"), MYSQL_BOTH);
		else
			$row = mysql_fetch_array(queryDB(true,"SELECT (SELECT COUNT(*) FROM `".DB_PREFIX.DATABASE_GOALS."` WHERE `conversion`=1) AS `tcount`, (SELECT COUNT(`visitor_id`) FROM (SELECT `visitor_id`, COUNT(`visitor_id`) as `vtcount` FROM `".DB_PREFIX.DATABASE_VISITOR_GOALS."` as `t1` INNER JOIN `".DB_PREFIX.DATABASE_GOALS."` as `t2` ON `t1`.`goal_id`=`t2`.`id` WHERE `t2`.`conversion`=1 AND `t1`.`time`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `t1`.`time`<=".@mysql_real_escape_string($this->Delimiters[1])." GROUP BY `t1`.`visitor_id`) AS `t3` WHERE `t3`.`vtcount`=`tcount`) as `cvconv`;"), MYSQL_BOTH);

		$this->ConversionsTotal = $row["cvconv"];
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_STATS_AGGS."` SET `time`=".time().",`mtime`=".mTime().",`conversions`='".@mysql_real_escape_string($this->ConversionsTotal)."',`sessions`='".@mysql_real_escape_string($this->VisitorsTotal)."',`visitors_unique`='".@mysql_real_escape_string($this->VisitorsUnique)."',`avg_time_site`='".@mysql_real_escape_string($this->AVGTimeOnSiteTotal)."' WHERE".$this->GetDateMatch().";");
	}
	
	function AggregateChats($_empty)
	{
		$values = array();
		$ids = array();
		//$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` ON `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`chat_id`=`".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."`.`chat_id` WHERE `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`status`>0 " . /*AND `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`exit`>0*/ . "AND `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`first_active`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`first_active`<=".@mysql_real_escape_string($this->Delimiters[1])." ORDER BY `dtime` DESC;");
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` ON `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`chat_id`=`".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."`.`chat_id` WHERE `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`status`>0 AND `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`first_active`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`first_active`<=".@mysql_real_escape_string($this->Delimiters[1])." ORDER BY `dtime` DESC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!isset($values[$row["user_id"]]))
				$values[$row["user_id"]] = array();
			if(!isset($values[$row["user_id"]][date("G",$row["first_active"])]))
				$values[$row["user_id"]][date("G",$row["first_active"])] = array(0,0,0,0,0,0);

			if(isset($ids[$row["chat_id"]]) || $row["chat_id"]==0)
			{
				//$values[$row["user_id"]][date("G",$row["first_active"])][2]+=$row["internal_declined"];
				continue;
			}
			
			$ids[$row["chat_id"]] = $row["chat_id"];
				
			$values[$row["user_id"]][date("G",$row["first_active"])][0]+=1;
			$values[$row["user_id"]][date("G",$row["first_active"])][1]+=$row["internal_active"];
			$values[$row["user_id"]][date("G",$row["first_active"])][2]+=$row["internal_declined"];
			$values[$row["user_id"]][date("G",$row["first_active"])][3]+=($row["allocated"]>0) ? $row["allocated"]-$row["first_active"] : $row["exit"]-$row["first_active"];
			$values[$row["user_id"]][date("G",$row["first_active"])][4]+=($row["allocated"]>0) ? $row["exit"]-$row["allocated"] : 0;
			$values[$row["user_id"]][date("G",$row["first_active"])][5]+=1;
		}
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetDateMatch().";");
		foreach($values as $userid => $hours)
			foreach($hours as $hour => $amount)
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` (`year`,`month`,`day`,`hour`,`user_id`,`amount`,`accepted`,`declined`,`avg_duration`,`avg_waiting_time`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($hour)."','".@mysql_real_escape_string($userid)."','".@mysql_real_escape_string($amount[0])."','".@mysql_real_escape_string($amount[1])."','".@mysql_real_escape_string($amount[2])."','".@mysql_real_escape_string(($amount[5]>0)?round($amount[4]/$amount[5],4):0)."','".@mysql_real_escape_string(($amount[0]>0)?round($amount[3]/$amount[0],4):0)."');");
	}
	
	function AggregateOperatorTime($_empty,$cend=0,$cstart=0)
	{
		$ontime = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` WHERE `time`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `time`<=".@mysql_real_escape_string($this->Delimiters[1])." AND `status`<".USER_STATUS_OFFLINE." ORDER BY `time`,`confirmed` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!isset($ontime[$row["internal_id"]]))
			{
				$ontime[$row["internal_id"]][USER_STATUS_ONLINE] = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0,20=>0,21=>0,22=>0,23=>0);
				$ontime[$row["internal_id"]][USER_STATUS_BUSY] = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0,20=>0,21=>0,22=>0,23=>0);
			}
			$ontime[$row["internal_id"]][$row["status"]][date("G",$row["time"])]+=max($row["confirmed"]-$row["time"],0);
		}
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` WHERE".$this->GetDateMatch().";");
		foreach($ontime as $userid => $states)
			foreach($states as $status => $hours)
				foreach($hours as $hour => $amount)
					if($amount > 0)
						queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` (`year`,`month`,`day`,`hour`,`user_id`,`status`,`seconds`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($hour)."','".@mysql_real_escape_string($userid)."','".@mysql_real_escape_string($status)."','".@mysql_real_escape_string($amount)."');");
	}
	
	function AggregateValueCount($_table,$_sourceTable,$_valueField,$_delimiterField,$_unique=false)
	{
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT `".$_valueField."` , count( `".$_valueField."` ) AS `vamount` FROM `".$_sourceTable."` WHERE `".$_valueField."`!='0' AND `".$_delimiterField."`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".$_delimiterField."`<=".@mysql_real_escape_string($this->Delimiters[1])." GROUP BY `".$_valueField."` ORDER BY `vamount` DESC LIMIT ".StatisticProvider::$DayItemAmount.";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`".$_valueField."`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row[$_valueField])."','".@mysql_real_escape_string($row["vamount"])."');");
	}
	
	function AggregateDayPageCount($_table,$_field,$_countField,$_groupField)
	{
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT COUNT( ".$_countField." ) AS `vamount`,`".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`url`,`t1`.`domain` AS `domain` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` AS `vb` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id`=`vb`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS t1 ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`url`=t1.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS t2 ON t1.`domain` = t2.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` AS t3 ON t1.`title` = t3.`id` WHERE `vb`.`is_chat`=0 AND t2.`search`=0 AND t2.`external`=0 AND t2.`domain`!='' AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1])." GROUP BY ".$_groupField." ORDER BY `vamount` DESC,`".DB_PREFIX."visitor_browser_urls`.`url` LIMIT ".(StatisticProvider::$DayItemAmount).";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`".$_field."`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row[$_field])."','".@mysql_real_escape_string($row["vamount"])."');");
	}
	
	function AggregateDayEntranceExitPageCount($_table,$_exit=false,$_secondaryGroupField="")
	{
		if(StatisticProvider::$AggregateDomains)$_secondaryGroupField="";
		StatisticProvider::LogProcess("AggregateDayEntranceExitPageCount DAY: " . $this->Day);
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT COUNT(`urli`) as `pamount`,`urli` FROM (SELECT DISTINCT(`browser_id`) as `bid`,(SELECT `url` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` WHERE `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id`=`bid` ORDER BY `entrance` ".(($_exit) ? "DESC":"ASC")." LIMIT 1) as `urli` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` AS `vb` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id`=`vb`.`id` WHERE `vb`.`is_chat`=0 AND ".(($_exit) ? "`vb`.`last_active` < ". (time()-StatisticProvider::$TimeoutTrack) . " AND " : "") . "`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") AS `subt` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS t1 ON urli=t1.`id` GROUP BY `t1`.`path`".$_secondaryGroupField." ORDER BY `pamount` DESC LIMIT ".(StatisticProvider::$DayItemAmount).";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`url`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row["urli"])."','".@mysql_real_escape_string($row["pamount"])."');");
	}
	
	function AggregateDayReferrerCount($_table)
	{
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT COUNT( `t1`.`path` ) AS `vamount`,`".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`  INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS t1 ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer`=t1.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS t2 ON t1.`domain` = t2.`id` WHERE t2.`search`=0 AND t2.`external`=1 AND t2.`domain`!='' AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1])." GROUP BY `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer` ORDER BY `vamount` DESC LIMIT ".(StatisticProvider::$DayItemAmount).";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`referrer`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row["referrer"])."','".@mysql_real_escape_string($row["vamount"])."');");
	}
	
	function AggregateDaySearchEngineCount($_table)
	{
		queryDB(true,"DELETE FROM `".$_table."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT COUNT( `t1`.`path` ) AS `vamount`,`t1`.`domain` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`  INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `t1` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer`=`t1`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `t2` ON `t1`.`domain`=`t2`.`id` WHERE `t2`.`search`=1 AND `t2`.`external`=1 AND `t2`.`domain`!='' AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1])." GROUP BY `t1`.`domain` ORDER BY `vamount` DESC LIMIT ".(StatisticProvider::$DayItemAmount).";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".$_table."` (`year`,`month`,`day`,`domain`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row["domain"])."','".@mysql_real_escape_string($row["vamount"])."');");
	}
	
	function AggregateDayGoals()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_GOALS."` WHERE".$this->GetDateMatch().";");
		$result = queryDB(true,"SELECT goal_id,COUNT(goal_id) as tamount FROM `".DB_PREFIX.DATABASE_VISITOR_GOALS."` GROUP BY `goal_id` ORDER BY tamount DESC LIMIT ".StatisticProvider::$DayItemAmount.";");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_GOALS."` (`year`,`month`,`day`,`goal`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($row["goal_id"])."','".@mysql_real_escape_string($row["tamount"])."');");
	}

	function AggregateDurations($_empty)
	{
		$_unique = "";
		$result = queryDB(true,"SELECT 
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)>=3600 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A7`,
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)<3600 AND (`last_active`-`entrance`)>=1800 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A6`,
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)<1800 AND (`last_active`-`entrance`)>=900 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A5`,
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)<900 AND (`last_active`-`entrance`)>=600 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A4`,
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)<600 AND (`last_active`-`entrance`)>=300 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A3`,
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)<300 AND (`last_active`-`entrance`)>=60 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A2`,
		(SELECT count((id)) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE ".$_unique."(`last_active`-`entrance`)<60 AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`>=".@mysql_real_escape_string($this->Delimiters[0])." AND `".DB_PREFIX.DATABASE_VISITORS."`.`entrance`<=".@mysql_real_escape_string($this->Delimiters[1]).") as `A1` 
		FROM `".DB_PREFIX.DATABASE_VISITORS."`;");
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` WHERE".$this->GetDateMatch().";");
		for($int=1;$int<8;$int++)
			if(!empty($row["A".$int]))
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS_DURATIONS."` (`year`,`month`,`day`,`duration`,`amount`) VALUES (".$this->GetSQLDateValues().",'".@mysql_real_escape_string($int)."','".@mysql_real_escape_string($row["A".$int])."');");
	}
}

class StatisticPeriod
{
	public $DayCount;
	public $Delimiters;
	public $Day = 0;
	public $Month = 0;
	public $Year;
	public $ConversionsTotal;
	public $VisitorsUniqueHour;
	public $VisitorsTotal;
	public $VisitorsUnique;
	public $VisitorsRecurringHour;
	public $VisitorsRecurringTotal;
	public $PageImpressionsHour = 0;
	public $PageImpressionsTotal = 0;
	public $AVGTimeOnSiteTotal = 0;
	public $AVGTimeOnPageTotal = 0;
	public $AVGTimeInChat = 0;
	public $AVGWaitingTime = 0;
	public $AVGPagesTotal = 0;
	public $VisitorBouncesHour = 0;
	public $VisitorBouncesTotal = 0;
	public $ChatsTotal = 0;
	public $ChatsPagesTotal = 0;
	public $ChatsPagesHour = 0;
	public $ChatsDeclinedTotal = 0;
	public $ChatsAcceptedTotal = 0;
	public $FromSearchEngineHour = 0;
	public $FromSearchEngineTotal = 0;
	public $FromReferrerHour = 0;
	public $FromReferrerTotal = 0;
	public $ChatAvailabilitySpans;
	public $ChatAvailabilityTotal = 0;
	public $BrowserInstancesTotal = 0;
	public $BrowserInstancesHour = 0;
	public $AVGBrowserInstances = 0;
	public $JavascriptTotal = 0;
	public $JavascriptHour = 0;
	public $QueryAmountTotal = 0;
	public $DirectAccessTotal = 0;
	public $CrawlerAccessTotal = 0;
	public $TCP = 0;
	public $Tops;
	public $Type;
	public $TopVisitorTables;
	public $TopBrowserTables;
	public $TopBrowserURLTables;
	public $Comparer;
	public $Closed;
	public $Days;
	public $Months;
	
	public $CreateReport;
	public $CreateVisitorList;
	public $IncludeBHVisitors;
	public $IncludeBHChats;
	public $IncludeBDVisitors;
	public $IncludeBDChats;
	public $IncludeBMVisitors;
	public $IncludeBMChats;
	public $IncludeBOAvailability;
	public $IncludeBOChats;
	public $IncludeTOPSystems;
	public $IncludeTOPOrigins;
	public $IncludeTOPVisits;
	public $IncludeTOPISPs;
	public $IncludeTOPPages;
	public $IncludeTOPEntranceExit;
	public $IncludeTOPSearch;
	public $IncludeTOPReferrers;
	public $IncludeTOPDomains;
	
	function StatisticPeriod($_year,$_month,$_day)
	{
		$this->Year = $_year;
		$this->Month = $_month;
		$this->Day = $_day;
		$this->TopVisitorTables = array(DATABASE_STATS_AGGS_BROWSERS=>"browser",DATABASE_STATS_AGGS_RESOLUTIONS=>"resolution",DATABASE_STATS_AGGS_VISITS=>"visits",DATABASE_STATS_AGGS_COUNTRIES=>"country",DATABASE_STATS_AGGS_SYSTEMS=>"system",DATABASE_STATS_AGGS_LANGUAGES=>"language",DATABASE_STATS_AGGS_CITIES=>"city",DATABASE_STATS_AGGS_REGIONS=>"region",DATABASE_STATS_AGGS_ISPS=>"isp");
		$this->TopBrowserTables = array(DATABASE_STATS_AGGS_QUERIES=>"query");
		$this->TopBrowserURLTables = array(DATABASE_STATS_AGGS_REFERRERS=>"referrer",DATABASE_STATS_AGGS_PAGES=>"url",DATABASE_STATS_AGGS_PAGES_EXIT=>"url",DATABASE_STATS_AGGS_PAGES_ENTRANCE=>"url",DATABASE_STATS_AGGS_SEARCH_ENGINES=>"domain");
	}
	
	function GetNoneAggregatedDateMatch()
	{
		if($this->Type == STATISTIC_PERIOD_TYPE_DAY)
			$_sql = " `day`='".@mysql_real_escape_string($this->Day)."' AND `month`='".@mysql_real_escape_string($this->Month)."' AND `year`='".@mysql_real_escape_string($this->Year)."'";
		else if($this->Type == STATISTIC_PERIOD_TYPE_MONTH)
			$_sql = " `month`='".@mysql_real_escape_string($this->Month)."' AND `year`='".@mysql_real_escape_string($this->Year)."'";
		else
			$_sql = " `year`='".@mysql_real_escape_string($this->Year)."'";
		return $_sql;
	}
	
	function GetDateMatch($_year=true,$_month=true,$_day=true,$_sql="")
	{
		if($_day)$_sql .= " `day`='".@mysql_real_escape_string(($this->Type == STATISTIC_PERIOD_TYPE_DAY) ? $this->Day : 0)."'";
		if($_month)$_sql .= ((!empty($_sql))?" AND":"") . " `month`='".@mysql_real_escape_string(($this->Type != STATISTIC_PERIOD_TYPE_YEAR) ? $this->Month : 0)."'";
		if($_year)$_sql .= ((!empty($_sql))?" AND":"") . " `year`='".@mysql_real_escape_string($this->Year)."'";
		return $_sql;
	}
	
	function GetSQLDateValues()
	{
		if($this->Type == STATISTIC_PERIOD_TYPE_DAY)
			return "'".@mysql_real_escape_string($this->Year)."','".@mysql_real_escape_string($this->Month)."','".@mysql_real_escape_string($this->Day)."'";
		else if($this->Type == STATISTIC_PERIOD_TYPE_MONTH)
			return "'".@mysql_real_escape_string($this->Year)."','".@mysql_real_escape_string($this->Month)."','".@mysql_real_escape_string(0)."'";
		else
			return "'".@mysql_real_escape_string($this->Year)."','".@mysql_real_escape_string(0)."','".@mysql_real_escape_string(0)."'";
	}

	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE".$this->GetDateMatch().";");
		if(mysql_num_rows($result)===1)
		{
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			$this->AVGTimeOnSiteTotal = $row["avg_time_site"];
			$this->VisitorsTotal = $row["sessions"];
			$this->VisitorsUnique = $row["visitors_unique"];
			$this->ConversionsTotal = $row["conversions"];
			
			$resultc = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch().";");
			while($rowc = mysql_fetch_array($resultc, MYSQL_BOTH))
			{
				$this->ChatsTotal += $rowc["amount"];
				$this->ChatsDeclinedTotal += $rowc["declined"];
				$this->ChatsAcceptedTotal += $rowc["accepted"];
				$this->AVGTimeInChat += $rowc["avg_duration"]*$rowc["amount"];
				$this->AVGWaitingTime += $rowc["avg_waiting_time"]*$rowc["amount"];
			}
			if(!empty($this->ChatsTotal))
			{
				$this->AVGWaitingTime = floor($this->AVGWaitingTime / $this->ChatsTotal);
				$this->AVGTimeInChat = floor($this->AVGTimeInChat / $this->ChatsTotal);
			}
			
			$row = mysql_fetch_array(queryDB(true,"SELECT SUM(`seconds`) AS `avail` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` WHERE `user_id`='everyoneintern' AND".$this->GetNoneAggregatedDateMatch().";"), MYSQL_BOTH);
			$this->ChatAvailabilityTotal = $row["avail"];
			
			$row = mysql_fetch_array(queryDB(true,"SELECT SUM(`amount`) AS `queries` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_QUERIES."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."` ON `".DB_PREFIX.DATABASE_STATS_AGGS_QUERIES."`.query=`".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."`.id WHERE`".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."`.query!='' AND".$this->GetDateMatch().";"), MYSQL_BOTH);
			$this->QueryAmountTotal = $row["queries"];
			
			$row = mysql_fetch_array(queryDB(true,"SELECT SUM(`amount`) AS `crawlers` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CRAWLERS."` WHERE".$this->GetDateMatch().";"), MYSQL_BOTH);
			$this->CrawlerAccessTotal = $row["crawlers"];
			
			if($this->VisitorsTotal > 0)
			{
				$this->AVGPagesTotal = @round($this->PageImpressionsTotal / $this->VisitorsTotal,StatisticProvider::$RoundPrecision);
				$this->AVGBrowserInstances = @round($this->BrowserInstancesTotal / $this->VisitorsTotal,StatisticProvider::$RoundPrecision);
			}
			if($this->AVGTimeOnSiteTotal > 0 && $this->AVGPagesTotal > 0)
				$this->AVGTimeOnPageTotal = round($this->AVGTimeOnSiteTotal / $this->AVGPagesTotal,4);

			$this->DirectAccessTotal = $this->VisitorsTotal-$this->FromReferrerTotal-$this->FromSearchEngineTotal;
			$this->TCP = round($this->PageImpressionsTotal*0.50 / 1000,4);
			$this->LoadTopTable(3,100,array(DATABASE_STATS_AGGS_QUERIES,DATABASE_VISITOR_DATA_QUERIES),array("query","query"),false,$this->QueryAmountTotal);
			$this->LoadTopTable(0,32,array(DATABASE_STATS_AGGS_BROWSERS,DATABASE_VISITOR_DATA_BROWSERS),array("browser","browser"),true,$this->VisitorsTotal);
			$this->LoadTopTable(1,32,array(DATABASE_STATS_AGGS_SYSTEMS,DATABASE_VISITOR_DATA_SYSTEMS),array("system","system"),true,$this->VisitorsTotal);
			$this->LoadTopTable(2,32,array(DATABASE_STATS_AGGS_COUNTRIES),array("country","country"),true,$this->VisitorsTotal);
			$this->LoadTopTable(4,32,array(DATABASE_STATS_AGGS_CITIES,DATABASE_VISITOR_DATA_CITIES),array("city","city"),true,$this->VisitorsTotal);
			$this->LoadTopTable(5,32,array(DATABASE_STATS_AGGS_RESOLUTIONS,DATABASE_VISITOR_DATA_RESOLUTIONS),array("resolution","resolution"),true,$this->VisitorsTotal);
			$this->LoadTopTable(6,32,array(DATABASE_STATS_AGGS_LANGUAGES),array("language","language"),true,$this->VisitorsTotal);
			$this->LoadTopTable(7,32,array(DATABASE_STATS_AGGS_REGIONS,DATABASE_VISITOR_DATA_REGIONS),array("region","region"),true,$this->VisitorsTotal);
			$this->LoadTopTable(11,32,array(DATABASE_STATS_AGGS_VISITS),array("visits","visits"),true,$this->VisitorsTotal,false);
			$this->LoadTopTable(12,100,array(DATABASE_STATS_AGGS_ISPS,DATABASE_VISITOR_DATA_ISPS),array("isp","isp"),true,$this->VisitorsTotal,false);
			$this->LoadTopTable(16,100,array(DATABASE_STATS_AGGS_CRAWLERS,DATABASE_VISITOR_DATA_CRAWLERS),array("crawler","crawler"),true,$this->CrawlerAccessTotal,false);
			$this->LoadTopTable(13,32,array(DATABASE_STATS_AGGS_DURATIONS),array("duration","duration"),true,$this->VisitorsTotal,false,"amount","duration","ASC");
			$this->LoadTopTable(14,100,array(DATABASE_STATS_AGGS_DOMAINS,DATABASE_VISITOR_DATA_DOMAINS),array("domain","domain"),true,$this->PageImpressionsTotal);
			$this->LoadURLTable(DATABASE_STATS_AGGS_PAGES,9,90);
			$this->LoadURLTable(DATABASE_STATS_AGGS_PAGES,10,90,true);
			$this->LoadURLTable(DATABASE_STATS_AGGS_PAGES_ENTRANCE,17,90);
			$this->LoadURLTable(DATABASE_STATS_AGGS_PAGES_ENTRANCE,18,90,true);
			$this->LoadURLTable(DATABASE_STATS_AGGS_PAGES_EXIT,19,90);
			$this->LoadURLTable(DATABASE_STATS_AGGS_PAGES_EXIT,20,90,true);
			$this->LoadReferrerTable(30,80,DATABASE_STATS_AGGS_REFERRERS,true,$this->FromReferrerTotal);
			$this->LoadReferrerTable(31,80,DATABASE_STATS_AGGS_REFERRERS,false,$this->FromReferrerTotal,"`".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain`");
			$this->LoadSearchEngineTable(15,80,$this->FromSearchEngineTotal);
			
			$this->Tops[21]=array();
			$result = queryDB(true,"SELECT `title`,`description`,`id` AS `gid`,(SELECT SUM(`amount`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS_GOALS."` WHERE".$this->GetDateMatch()." AND `goal`=`gid`) AS `gcount` FROM `".DB_PREFIX.DATABASE_GOALS."` ORDER BY `ind` ASC;");
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if($this->VisitorsTotal > 0 && !empty($row["gcount"]))
					$this->Tops[21][$row["gid"]] = array($row["gcount"],round((100*$row["gcount"])/$this->VisitorsTotal,StatisticProvider::$RoundPrecision),100-floor((100*$row["gcount"])/$this->VisitorsTotal),$row["title"],$row["description"]);
				else
					$this->Tops[21][$row["gid"]] = array(0,0,100,$row["title"],$row["description"]);
				
			}
			return true;
		}
		return false;
	}
	
	function GetUsersHTML()
	{
		global $VISITOR,$QCOUNT;
		$html_users="";
		$html_user = getFile(TEMPLATE_HTML_STATS_USERS_USER);
		$html_url = getFile(TEMPLATE_HTML_STATS_USERS_URL);
		$html_body = getFile(TEMPLATE_HTML_STATS_USERS_BODY);
		$html_body = str_replace("<!--status-->",($this->Closed) ? "<!--lang_stats_status_closed-->" : "<!--lang_stats_status_open-->",$html_body);
		@set_time_limit(600);
		loadVisitors(true," WHERE `entrance`>='".@mysql_real_escape_string($this->Delimiters[0])."' AND `entrance`<='".@mysql_real_escape_string($this->Delimiters[1])."'"," LIMIT " . StatisticProvider::$MaxUsersAmount);
		$count=1;
		foreach($VISITOR as $visitor)
		{
			$user = $html_user;
			$user = str_replace("<!--entrance-->",date("H:i:s",$visitor->FirstActive),$user);
			$user = str_replace("<!--exit-->",date("H:i:s",$visitor->ExitTime),$user);
			$user = str_replace("<!--visits-->",max($visitor->VisitsDay,$visitor->Visits),$user);
			$user = str_replace("<!--visits_day-->",$visitor->VisitsDay,$user);
			$user = str_replace("<!--system-->",((!empty($visitor->OperatingSystem)) ? $visitor->OperatingSystem : "<!--lang_stats_unknown-->"),$user);
			$user = str_replace("<!--browser-->",((!empty($visitor->Browser)) ? $visitor->Browser : "<!--lang_stats_unknown-->"),$user);
			$user = str_replace("<!--country_name-->",((!empty($visitor->GeoCountryName)) ? $visitor->GeoCountryName : "<!--lang_stats_unknown-->"),$user);
			$user = str_replace("<!--city-->",((!empty($visitor->GeoCity)) ? $visitor->GeoCity : "<!--lang_stats_unknown-->"),$user);
			$user = str_replace("<!--region-->",$visitor->GeoRegion,$user);
			$user = str_replace("<!--isp-->",str_replace("'","",$visitor->GeoISP),$user);
			$user = str_replace("<!--ip-->",$visitor->IP,$user);
			$user = str_replace("<!--host-->",str_replace("'","",$visitor->Host),$user);
			$user = str_replace("<!--id-->",$visitor->UserId,$user);
			$user = str_replace("<!--vclass-->",(max($visitor->VisitsDay,$visitor->Visits)==1)?"vn" :(($visitor->VisitsDay>1) ? "vm" : "vs"),$user);
			$urls="";$id=1;$oid=1;$bcount=1;
			$lastActivity = $visitor->ExitTime;

			foreach($visitor->Browsers as $bid => $browser)
			{
				foreach($browser->History as $hurl)
				{
					if($id == 1 && !isnull($hurl->Referrer->GetAbsoluteUrl()) && $hurl->Referrer->GetAbsoluteUrl() != $hurl->Url->GetAbsoluteUrl())
					{
						$url = $html_url;
						$url = str_replace("<!--page-->","Referrer: <a href=\"" . htmlentities(StatisticProvider::$Dereferrer . $hurl->Referrer->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8')."\" target=\"_blank\">" . htmlentities($hurl->Referrer->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8') ."</a>",$url);
						$url = str_replace("<!--entrance-->","&nbsp;",$url);
						$url = str_replace("<!--id-->","&nbsp;",$url);
						$url = str_replace("<!--oid-->",$oid++,$url);
						$urls .= $url;
					}
					$urls = str_replace("<!--exit-->",date("H:i:s",$hurl->Entrance),$urls);
					$url = $html_url;
					$url = str_replace("<!--page-->","<!--lang_stats_browser--> ".$bcount.": <a href=\"" . htmlentities(StatisticProvider::$Dereferrer . $hurl->Url->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8')."\" target=\"_blank\">" . htmlentities($hurl->Url->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8') . "</a>",$url);
					$url = str_replace("<!--entrance-->",date("H:i:s",$hurl->Entrance),$url);
					$url = str_replace("<!--id-->",$id++,$url);
					$url = str_replace("<!--oid-->",$oid++,$url);
					$urls .= $url;
				}
				$bcount++;
				$urls = str_replace("<!--exit-->",date("H:i:s",$browser->LastActive),$urls);
			}
			
			$user = str_replace("<!--pages-->",$id-1 . ((($id-1)==0)?"?":""),$user);
			$html_users .= $user . str_replace("<!--exit-->",date("H:i:s",$visitor->ExitTime),$urls);
			$html_users = str_replace("<!--number-->",$count++,$html_users);
		}
		$html_body = str_replace("<!--visitors-->",$html_users,$html_body);
		$html_body = str_replace("<!--amount-->",count($VISITOR),$html_body);
		return $html_body;
	}
	
	function GetHTML()
	{
		global $QCOUNT;
		$this->LoadComparer();
		$html = getFile(TEMPLATE_HTML_STATS_BODY);
		$html_ov_general = getFile(TEMPLATE_HTML_STATS_BASE_TABLE);
		$html = str_replace("<!--quick_value_11-->",number_format($this->VisitorsTotal,0,".","."),$html);
		$html = str_replace("<!--quick_value_17-->",number_format($this->VisitorsUnique,0,".","."),$html);
		$html = str_replace("<!--quick_value_18-->",@round((100*$this->VisitorsUnique)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision),$html);
		$html = str_replace("<!--quick_value_12-->",$this->GetTotalTrend("VisitorsTotal"),$html);
		$html = str_replace("<!--quick_value_13-->",number_format($this->VisitorsTotal-$this->VisitorsRecurringTotal,0,".","."),$html);
		$html = str_replace("<!--quick_value_14-->",@round((100*($this->VisitorsTotal-$this->VisitorsRecurringTotal))/$this->VisitorsTotal,StatisticProvider::$RoundPrecision),$html);
		$html = str_replace("<!--quick_value_15-->",number_format($this->VisitorsRecurringTotal,0,".","."),$html);
		$html = str_replace("<!--quick_value_16-->",@round((100*$this->VisitorsRecurringTotal)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision),$html);
		$html = str_replace("<!--quick_value_21-->",@round((100*$this->VisitorBouncesTotal)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision),$html);
		$html = str_replace("<!--quick_value_22-->",$this->GetTotalTrend("VisitorBouncesTotal","VisitorsTotal",true),$html);
		$html = str_replace("<!--quick_value_23-->",number_format($this->VisitorBouncesTotal,0,".","."),$html);
		$html = str_replace("<!--quick_value_31-->",number_format($this->ChatsTotal,0,".","."),$html);
		$html = str_replace("<!--quick_value_32-->",$this->GetTotalTrend("ChatsTotal"),$html);
		$html = str_replace("<!--quick_value_33-->",$this->FormatTimespan($this->AVGWaitingTime),$html);
		$html = str_replace("<!--quick_value_41-->",@round((100*$this->ChatAvailabilityTotal)/($this->Delimiters[1]-$this->Delimiters[0]+1),StatisticProvider::$RoundPrecision),$html);
		$html = str_replace("<!--quick_value_42-->",$this->GetTotalTrend("ChatAvailabilityTotal"),$html);
		$html = str_replace("<!--quick_value_43-->",$this->FormatTimespan($this->ChatAvailabilityTotal),$html);
		$html = str_replace("<!--quick_value_44-->",$this->FormatTimespan($this->Delimiters[1]-$this->Delimiters[0]+1),$html);
		$html = str_replace("<!--quick_value_51-->",@round((100*$this->ConversionsTotal)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision),$html);
		$html = str_replace("<!--quick_value_52-->",$this->GetTotalTrend("ConversionsTotal","VisitorsTotal"),$html);
		$html = str_replace("<!--quick_value_53-->",number_format($this->ConversionsTotal,0,".","."),$html);
		$html = str_replace("<!--status-->",($this->Closed) ? "<!--lang_stats_status_closed-->" : "<!--lang_stats_status_open-->",$html);
		$html = str_replace("<!--stat_type-->",$this->Type,$html);

		$html = str_replace("<!--stats_top_browsers-->",(($this->IncludeTOPSystems) ? $this->RenderTopTable(0,"<!--lang_stats_browsers-->","<!--lang_stats_browser-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_systems-->",(($this->IncludeTOPSystems) ? $this->RenderTopTable(1,"<!--lang_stats_systems-->","<!--lang_stats_system-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_resolutions-->",(($this->IncludeTOPSystems) ? $this->RenderTopTable(5,"<!--lang_stats_resolutions-->","<!--lang_stats_resolution-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_countries-->",(($this->IncludeTOPOrigins) ? $this->RenderTopTable(2,"<!--lang_stats_countries-->","<!--lang_stats_country-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_languages-->",(($this->IncludeTOPOrigins) ? $this->RenderTopTable(6,"<!--lang_stats_languages-->","<!--lang_stats_language-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_regions-->",(($this->IncludeTOPOrigins) ? $this->RenderTopTable(7,"<!--lang_stats_regions-->","<!--lang_stats_region-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_cities-->",(($this->IncludeTOPOrigins) ? $this->RenderTopTable(4,"<!--lang_stats_cities-->","<!--lang_stats_city-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_visits-->",(($this->IncludeTOPVisits) ? $this->RenderTopTable(11,"<!--lang_stats_visits-->","<!--lang_stats_visits-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_isps-->",(($this->IncludeTOPISPs) ? $this->RenderTopTable(12,"<!--lang_stats_isps-->","<!--lang_stats_isp-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_duration-->",(($this->IncludeTOPVisits) ? $this->RenderTopTable(13,"<!--lang_stats_time_on_page-->","<!--lang_stats_duration-->")."<br>" : ""),$html);

		$html = str_replace("<!--topvs-->",(($this->IncludeTOPSystems) ? "" : "none"),$html);
		$html = str_replace("<!--topvso-->",(($this->IncludeTOPSystems && $this->IncludeTOPOrigins) ? "" : "none"),$html);
		$html = str_replace("<!--topvo-->",(($this->IncludeTOPOrigins) ? "" : "none"),$html);
		$html = str_replace("<!--topvov-->",(($this->IncludeTOPVisits && ($this->IncludeTOPSystems || $this->IncludeTOPOrigins)) ? "" : "none"),$html);
		$html = str_replace("<!--topvv-->",(($this->IncludeTOPVisits) ? "" : "none"),$html);
		$html = str_replace("<!--topvvi-->",(($this->IncludeTOPISPs && ($this->IncludeTOPSystems || $this->IncludeTOPOrigins || $this->IncludeTOPVisits)) ? "" : "none"),$html);
		$html = str_replace("<!--topvi-->",(($this->IncludeTOPISPs) ? "" : "none"),$html);
		$html = str_replace("<!--topv-->",(($this->IncludeTOPSystems || $this->IncludeTOPOrigins || $this->IncludeTOPVisits || $this->IncludeTOPISPs) ? "" : "none"),$html);
		
		$html = str_replace("<!--stats_top_queries-->",(($this->IncludeTOPSearch) ? $this->RenderTopTable(3,"<!--lang_stats_search_phrases-->","<!--lang_stats_search_phrase-->") : ""),$html);
		$html = str_replace("<!--stats_top_referrers-->",(($this->IncludeTOPReferrers) ? $this->RenderTopURLTable(30,"<!--lang_stats_referrers-->","<!--lang_stats_referrer-->",true,"URL","Domains")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_pages-->",(($this->IncludeTOPPages) ? $this->RenderTopURLTable(9,"<!--lang_stats_pages-->","<!--lang_stats_page-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_pages_entrance-->",(($this->IncludeTOPEntranceExit) ? $this->RenderTopURLTable(17,"<!--lang_stats_entrance_pages-->","<!--lang_stats_page-->") : ""),$html);
		$html = str_replace("<!--stats_top_pages_exit-->",(($this->IncludeTOPEntranceExit) ? "<br>".$this->RenderTopURLTable(19,"<!--lang_stats_exit_pages-->","<!--lang_stats_page-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_search_engines-->",(($this->IncludeTOPSearch) ? "<br>".$this->RenderTopTable(15,"<!--lang_stats_search_engines-->","<!--lang_stats_search_engines-->") : ""),$html);
		$html = str_replace("<!--stats_top_domains-->",(($this->IncludeTOPDomains) ? $this->RenderTopTable(14,"<!--lang_stats_domains-->","<!--lang_stats_domain-->")."<br>" : ""),$html);
		$html = str_replace("<!--stats_top_crawlers-->",(($this->IncludeTOPSearch) ? "<br>".$this->RenderTopTable(16,"<!--lang_stats_crawlers-->","<!--lang_stats_crawler-->")."<br>" : ""),$html);
		
		$html = str_replace("<!--toppp-->",(($this->IncludeTOPPages) ? "" : "none"),$html);
		$html = str_replace("<!--topppe-->",(($this->IncludeTOPPages && $this->IncludeTOPEntranceExit) ? "" : "none"),$html);
		$html = str_replace("<!--toppe-->",(($this->IncludeTOPEntranceExit) ? "" : "none"),$html);
		$html = str_replace("<!--toppes-->",(($this->IncludeTOPSearch && ($this->IncludeTOPPages || $this->IncludeTOPEntranceExit)) ? "" : "none"),$html);
		$html = str_replace("<!--topps-->",(($this->IncludeTOPSearch) ? "" : "none"),$html);
		$html = str_replace("<!--toppsr-->",(($this->IncludeTOPReferrers && ($this->IncludeTOPSearch || $this->IncludeTOPEntranceExit || $this->IncludeTOPPages)) ? "" : "none"),$html);
		$html = str_replace("<!--toppr-->",(($this->IncludeTOPReferrers) ? "" : "none"),$html);
		$html = str_replace("<!--topprd-->",(($this->IncludeTOPDomains  && ($this->IncludeTOPSearch || $this->IncludeTOPEntranceExit || $this->IncludeTOPPages || $this->IncludeTOPReferrers)) ? "" : "none"),$html);
		$html = str_replace("<!--toppd-->",(($this->IncludeTOPDomains) ? "" : "none"),$html);
		$html = str_replace("<!--topp-->",(($this->IncludeTOPPages || $this->IncludeTOPReferrers || $this->IncludeTOPDomains || $this->IncludeTOPSearch || $this->IncludeTOPEntranceExit) ? "" : "none"),$html);
		
		$html_hours = str_replace("<!--stats_hours_visitors-->",(($this->IncludeBHVisitors) ? $this->GetVisitorsByHour("visitors_unique","<!--lang_stats_visitors-->")."<br>" : ""),getFile(TEMPLATE_HTML_STATS_HOURS));
		$html_hours = str_replace("<!--stats_hours_impressions-->",(($this->IncludeBHVisitors) ? $this->GetVisitorsByHour("page_impressions","<!--lang_stats_page_impressions-->")."<br>" : ""),$html_hours);
		$html_hours = str_replace("<!--stats_hours_chats-->",(($this->IncludeBHChats) ? $this->GetChatsByHour()."<br>" : ""),$html_hours);
		$html_hours = str_replace("<!--stats_hours_operators-->",(($this->IncludeBHChats) ? $this->GetOperatorsByHour()."<br>" : ""),$html_hours);
		$html_hours = str_replace("<!--bhvv-->",(($this->IncludeBHVisitors) ? "" : "none"),$html_hours);
		$html_hours = str_replace("<!--bhvcv-->",(($this->IncludeBHChats && $this->IncludeBHVisitors) ? "" : "none"),$html_hours);
		$html_hours = str_replace("<!--bhcv-->",(($this->IncludeBHChats) ? "" : "none"),$html_hours);
		$html = str_replace("<!--hours-->",(($this->IncludeBHVisitors || $this->IncludeBHChats) ? $html_hours : ""),$html);
		
		$html_operators = str_replace("<!--stats_operator_chats-->",(($this->IncludeBOChats) ? $this->GetChatsByOperator()."<br>" : ""),getFile(TEMPLATE_HTML_STATS_OPERATORS));
		$html_operators = str_replace("<!--stats_operator_chat_duration-->",(($this->IncludeBOChats) ? $this->GetChatDurationByOperator()."<br>" : ""),$html_operators);
		$html_operators = str_replace("<!--stats_operator_availability-->",(($this->IncludeBOAvailability) ? $this->GetAvailabilityByOperator()."<br>" : ""),$html_operators);
		$html_operators = str_replace("<!--boav-->",(($this->IncludeBOAvailability) ? "" : "none"),$html_operators);
		$html_operators = str_replace("<!--boavcv-->",(($this->IncludeBOChats && $this->IncludeBOAvailability) ? "" : "none"),$html_operators);
		$html_operators = str_replace("<!--bocv-->",(($this->IncludeBOChats) ? "" : "none"),$html_operators);
		$html = str_replace("<!--operators-->",(($this->IncludeBOAvailability || $this->IncludeBOChats) ? $html_operators : ""),$html);
		
		if($this->Type == STATISTIC_PERIOD_TYPE_MONTH)
		{
			$html_days = str_replace("<!--stats_days_visitors-->",(($this->IncludeBDVisitors) ? $this->GetVisitorsByDays()."<br>" : ""),getFile(TEMPLATE_HTML_STATS_DAYS));
			$html_days = str_replace("<!--stats_days_impressions-->",(($this->IncludeBDVisitors) ? $this->GetVisitorsByDays(true)."<br>" : ""),$html_days);
			$html_days = str_replace("<!--stats_days_chats-->",(($this->IncludeBDChats) ? $this->GetChatsByDays()."<br>" : ""),$html_days);
			$html_days = str_replace("<!--stats_days_operators-->",(($this->IncludeBDChats) ? $this->GetOperatorsByDays()."<br>" : ""),$html_days);
			$html_days = str_replace("<!--bdvv-->",(($this->IncludeBDVisitors) ? "" : "none"),$html_days);
			$html_days = str_replace("<!--bdvcv-->",(($this->IncludeBDChats && $this->IncludeBDVisitors) ? "" : "none"),$html_days);
			$html_days = str_replace("<!--bdcv-->",(($this->IncludeBDChats) ? "" : "none"),$html_days);
			$html = str_replace("<!--days-->",(($this->IncludeBDVisitors || $this->IncludeBDChats) ? $html_days : ""),$html);
			$html = str_replace("<!--months-->","",$html);
		}
		else if($this->Type == STATISTIC_PERIOD_TYPE_YEAR)
		{
			$html_months = str_replace("<!--stats_months_visitors-->",(($this->IncludeBMVisitors) ? $this->GetVisitorsByMonths()."<br>" : ""),getFile(TEMPLATE_HTML_STATS_MONTHS));
			$html_months = str_replace("<!--stats_months_impressions-->",(($this->IncludeBMVisitors) ? $this->GetVisitorsByMonths(true)."<br>" : ""),$html_months);
			$html_months = str_replace("<!--stats_months_chats-->",(($this->IncludeBMChats) ? $this->GetChatsByMonths()."<br>" : ""),$html_months);
			$html_months = str_replace("<!--stats_months_operators-->",(($this->IncludeBMChats) ? $this->GetOperatorsByMonths()."<br>" : ""),$html_months);
			$html_months = str_replace("<!--bmvv-->",(($this->IncludeBMVisitors) ? "" : "none"),$html_months);
			$html_months = str_replace("<!--bmvcv-->",(($this->IncludeBMChats && $this->IncludeBMVisitors) ? "" : "none"),$html_months);
			$html_months = str_replace("<!--bmcv-->",(($this->IncludeBMChats) ? "" : "none"),$html_months);
			$html = str_replace("<!--months-->",(($this->IncludeBMVisitors || $this->IncludeBMChats) ? $html_months : ""),$html);
			$html = str_replace("<!--days-->","",$html);
		}
		else
			$html = str_replace("<!--days-->","",$html);
			
		$html = str_replace("<!--goals-->",$this->GetGoalTable(),$html);
		
		$html = str_replace("<!--stats_base_table_general-->",$this->GetBaseTable("<!--lang_stats_visitors-->", array("<!--lang_stats_page_impressions-->"=>array(number_format($this->PageImpressionsTotal,0,".","."),$this->GetTotalTrend("PageImpressionsTotal")),"<!--lang_stats_pages_per_visitor-->"=>array($this->AVGPagesTotal,$this->GetTotalTrend("AVGPagesTotal")),"<!--lang_stats_browser_instances-->"=>array(number_format($this->BrowserInstancesTotal,0,".","."),$this->GetTotalTrend("BrowserInstancesTotal")),"<!--lang_stats_browser_instances_per_visitor-->"=>array($this->AVGBrowserInstances,$this->GetTotalTrend("AVGBrowserInstances")),"<!--lang_stats_average_time_on_site-->"=>array($this->FormatTimespan($this->AVGTimeOnSiteTotal),$this->GetTotalTrend("AVGTimeOnSiteTotal")),"<!--lang_stats_average_time_on_page-->"=>array($this->FormatTimespan($this->AVGTimeOnPageTotal),$this->GetTotalTrend("AVGTimeOnPageTotal")))),$html);
		$html = str_replace("<!--stats_base_table_chat-->",$this->GetBaseTable("Chats", array("<!--lang_stats_chat_page_opened-->"=>array(number_format($this->ChatPagesTotal,0,".","."),$this->GetTotalTrend("ChatPagesTotal")),"Chats"=>array(number_format($this->ChatsTotal,0,".","."),$this->GetTotalTrend("ChatsTotal")),"Chats <!--lang_stats_accepted-->"=>array(number_format($this->ChatsAcceptedTotal,0,".",".") . " (".@round((100*$this->ChatsAcceptedTotal)/$this->ChatsTotal,StatisticProvider::$RoundPrecision)."%)",$this->GetTotalTrend("ChatsAcceptedTotal","ChatsTotal")),"Chats <!--lang_stats_declined-->"=>array(number_format($this->ChatsDeclinedTotal,0,".",".") ."  (".@round((100*$this->ChatsDeclinedTotal)/$this->ChatsTotal,StatisticProvider::$RoundPrecision)."%)",$this->GetTotalTrend("ChatsDeclinedTotal","ChatsTotal")),"<!--lang_stats_chat_average_time-->"=>array($this->FormatTimespan($this->AVGTimeInChat),$this->GetTotalTrend("AVGTimeInChat","",true)),"<!--lang_stats_chat_average_waiting_time-->"=>array($this->FormatTimespan($this->AVGWaitingTime),$this->GetTotalTrend("AVGWaitingTime","",true)))),$html);
		$html = str_replace("<!--stats_base_table_sources-->",$this->GetBaseTable("<!--lang_stats_traffic_sources-->",array("<!--lang_stats_search_engines-->"=>array(@round((100*$this->FromSearchEngineTotal)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision)."%",$this->GetTotalTrend("FromSearchEngineTotal","VisitorsTotal")),"<!--lang_stats_referrer-->"=>array(@round((100*$this->FromReferrerTotal)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision)."%",$this->GetTotalTrend("FromReferrerTotal","VisitorsTotal")),"<!--lang_stats_direct_access-->"=>array(@round((100*($this->DirectAccessTotal))/$this->VisitorsTotal,StatisticProvider::$RoundPrecision)."%",$this->GetTotalTrend("DirectAccessTotal","VisitorsTotal")))),$html);
		$html = str_replace("<!--stats_base_table_js-->",$this->GetBaseTable("Javascript",array("<!--lang_stats_status_activated-->"=>array(number_format($this->JavascriptTotal,0,".",".") . " (".@round((100*$this->JavascriptTotal)/$this->VisitorsTotal,StatisticProvider::$RoundPrecision)."%)",$this->GetTotalTrend("JavascriptTotal","VisitorsTotal")))),$html);
		$html = str_replace("<!--stats_base_table_av-->",$this->GetBaseTable("<!--lang_stats_advertising_value-->",array("0,50 EUR"=>array(number_format($this->TCP,2)." EUR",$this->GetTotalTrend("TCP")))),$html);
		$html = str_replace("<!--trend-->","&nbsp;",$html);
		return $html;
	}

	private function GetChatsByOperator($counter=0,$hrows="")
	{
		global $INTERNAL;
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$rows = array();
		
		$result = queryDB(true,"SELECT *,SUM(`amount`) AS `am`,SUM(`declined`) AS `dec`,(SELECT SUM(`amount`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch().") AS total,(SELECT `amount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `amount` DESC LIMIT 1) AS `mamount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id` ORDER BY `am` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if($row["user_id"] != GROUP_EVERYONE_INTERN)
			{
				$rows[$row["user_id"]] = str_replace("<!--title-->",((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$trow);
				$rows[$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["am"])/$row["total"],StatisticProvider::$RoundPrecision),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["am"])/$row["total"]),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--abs_amount-->",$row["am"] . " (" . $row["dec"] . ")",$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--count-->",++$counter,$rows[$row["user_id"]]);
			}
		}
		foreach($INTERNAL as $id => $user)
			if(!isset($rows[$id]))
			{
				$rows[$id] = str_replace("<!--title-->",$user->Fullname,$trow);
				$rows[$id] = str_replace("<!--rel_amount-->",0,$rows[$id]);
				$rows[$id] = str_replace("<!--rel_floor_amount-->",100,$rows[$id]);
				$rows[$id] = str_replace("<!--abs_amount-->",0 . " (0)",$rows[$id]);
				$rows[$id] = str_replace("<!--count-->",++$counter,$rows[$id]);
			}
		foreach($rows as $hrow)
			$hrows .= $hrow;
		$html = str_replace("<!--rows-->",$hrows,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","none",$html);
		$html = str_replace("<!--expand_all_block-->","",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_operator-->",$html);
		return str_replace("<!--title-->","Chats",$html);
	}
	
	private function GetChatDurationByOperator($counter=0,$hrows="")
	{
		global $INTERNAL;
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$rows = array();
		
		$result = queryDB(true,"SELECT *,SUM(`avg_duration`) AS `am`,(SELECT SUM(`avg_duration`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch().") AS total,(SELECT `amount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `avg_duration` DESC LIMIT 1) AS `mamount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id` ORDER BY `am` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if($row["user_id"] != GROUP_EVERYONE_INTERN)
			{
				$rows[$row["user_id"]] = str_replace("<!--title-->",((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$trow);
				$rows[$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["am"])/$row["total"],StatisticProvider::$RoundPrecision),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["am"])/$row["total"]),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--abs_amount-->",$this->FormatTimespan($row["am"]),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--count-->",++$counter,$rows[$row["user_id"]]);
			}
		}
		foreach($INTERNAL as $id => $user)
			if(!isset($rows[$id]))
			{
				$rows[$id] = str_replace("<!--title-->",$user->Fullname,$trow);
				$rows[$id] = str_replace("<!--rel_amount-->",0,$rows[$id]);
				$rows[$id] = str_replace("<!--rel_floor_amount-->",100,$rows[$id]);
				$rows[$id] = str_replace("<!--abs_amount-->",$this->FormatTimespan(0),$rows[$id]);
				$rows[$id] = str_replace("<!--count-->",++$counter,$rows[$id]);
			}
		foreach($rows as $hrow)
			$hrows .= $hrow;
			
		$html = str_replace("<!--rows-->",$hrows,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","none",$html);
		$html = str_replace("<!--expand_all_block-->","",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_operator-->",$html);
		return str_replace("<!--title-->","<!--lang_stats_chat_average_duration-->",$html);
	}
	
	private function GetAvailabilityByOperator($counter=0,$hrows="")
	{
		global $INTERNAL;
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$rows = array();
		
		$result = queryDB(true,"SELECT *,SUM(`seconds`) as `am` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id` ORDER BY `am` DESC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$secondsInPeriod = 3600 * 24 * $this->DayCount;
			if($row["user_id"] != GROUP_EVERYONE_INTERN)
			{
				$rows[$row["user_id"]] = str_replace("<!--title-->",((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$trow);
				$rows[$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["am"])/$secondsInPeriod,StatisticProvider::$RoundPrecision),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["am"])/$secondsInPeriod),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--abs_amount-->",$this->FormatTimespan($row["am"]),$rows[$row["user_id"]]);
				$rows[$row["user_id"]] = str_replace("<!--count-->",++$counter,$rows[$row["user_id"]]);
			}
		}
		foreach($INTERNAL as $id => $user)
			if(!isset($rows[$id]))
			{
				$rows[$id] = str_replace("<!--title-->",$user->Fullname,$trow);
				$rows[$id] = str_replace("<!--rel_amount-->",0,$rows[$id]);
				$rows[$id] = str_replace("<!--rel_floor_amount-->",100,$rows[$id]);
				$rows[$id] = str_replace("<!--abs_amount-->",$this->FormatTimespan(0),$rows[$id]);
				$rows[$id] = str_replace("<!--count-->",++$counter,$rows[$id]);
			}
		foreach($rows as $hrow)
			$hrows .= $hrow;
		$html = str_replace("<!--rows-->",$hrows,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","none",$html);
		$html = str_replace("<!--expand_all_block-->","",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_operator-->",$html);
		return str_replace("<!--title-->","<!--lang_stats_operator_availability-->",$html);
	}
	
	private function GetGoalTable($counter=0,$rows="",$row="")
	{
		$html = str_replace("<!--title-->","<!--lang_stats_goals-->",getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$rowt = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		foreach($this->Tops[21] as $id => $values)
		{
			$counter++;
			$trend = ($this->Comparer!=null) ? (($this->Comparer->Tops[21][$id][0]<$values[0])?"up":(($this->Comparer->Tops[21][$id][0]>$values[0])?"down":"const")) : "const";
			$row = str_replace("<!--trend-->",$trend,str_replace("<!--title-->","<b>".$values[3]."</b><br>".$values[4],str_replace("<!--value-->",$values[0],$rowt)));
			$row = str_replace("<!--rel_amount-->",$values[1],$row);
			$row = str_replace("<!--rel_floor_amount-->",$values[2],$row);
			$row = str_replace("<!--abs_amount-->",number_format($values[0],0,".","."),$row);
			$rows .= str_replace("<!--count-->",$counter,$row);
		}
		if(count($this->Tops[21])==0)
		{
			$rows .= str_replace("<!--trend-->","const",str_replace("<!--title-->","<!--lang_stats_none-->",str_replace("<!--count-->","",str_replace("<!--abs_amount-->",0,str_replace("<!--rel_floor_amount-->",100,str_replace("<!--rel_amount-->",0,str_replace("<!--value-->","-",$rowt)))))));
		}
		$html = str_replace("<!--trend-->","<!--lang_stats_trend-->",$html);
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","none",$html);
		$html = str_replace("<!--expand_all_block-->","",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_goals-->",$html);
		return str_replace("<!--rows-->",$rows,$html);
	}
	
	function GetBaseTable($_title,$_values,$rows="")
	{
		$table = str_replace("<!--title-->",$_title,getFile(TEMPLATE_HTML_STATS_BASE_TABLE));
		$row = getFile(TEMPLATE_HTML_STATS_BASE_ROW);
		foreach($_values as $title => $values)
			$rows .= str_replace("<!--trend_value-->",$values[1],str_replace("<!--title-->",$title,str_replace("<!--value-->",$values[0],$row)));
		return str_replace("<!--rows-->",$rows,$table);
	}
	
	function GetVisitorsByMonths($_impressions=false,$html = "")
	{
		$hrow = getFile(TEMPLATE_HTML_STATS_SPAN_ROW);
		$total = 0;
		
		foreach($this->Months as $month)
			$total = max($total,($_impressions) ? $month->PageImpressionsTotal : $month->VisitorsTotal);
		foreach($this->Months as $month)
		{
			$value = ($_impressions) ? $month->PageImpressionsTotal : $month->VisitorsTotal;
			$html .= str_replace("<!--title-->","<!--lang_stats_month_" . strtolower(date("F",mktime(0,0,0,$month->Month,1,$month->Year))) . "-->",$hrow);
			if($total > 0)
			{
				$html = str_replace("<!--rel_floor_amount-->",100-floor((100*$value)/$total),$html);
			}
			else
			{
				$html = str_replace("<!--rel_floor_amount-->",100,$html);
			}
			$html = str_replace("<!--abs_amount-->",number_format($value,0,".","."),$html);
			$html = str_replace("<!--count-->",date("m",mktime(0,0,0,$month->Month,1,$month->Year))."/".$month->Year,$html);
		}
		$html = str_replace("<!--rows-->",$html,getFile(TEMPLATE_HTML_STATS_SPAN_TABLE));
		$html = str_replace("<!--column_count_width-->","40",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_by_days-->",$html);
		return str_replace("<!--title-->",($_impressions) ? "<!--lang_stats_page_impressions-->" : "<!--lang_stats_visitors-->",$html);
	}

	function GetOperatorsByMonths($counter=0,$html = "",$crow="")
	{
		global $INTERNAL;
		$hrow = getFile(TEMPLATE_HTML_STATS_HOURS_HIDDEN_ROW);
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$rows = array();
		
		foreach($this->Months as $month)
			$months[intval($month->Month)] = 0;

		$result = queryDB(true,"SELECT *,SUM(`seconds`) as `am` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id`,`year`,`month` ORDER BY `user_id`,`year`,`month` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$secondsInPeriod = 3600 * 24 * $this->Months[$month->Month]->DayCount;
			if($row["user_id"] != GROUP_EVERYONE_INTERN)
			{
				$rows[$row["month"]][$row["user_id"]] = str_replace("<!--title-->"," &raquo; " . ((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$hrow);
				$rows[$row["month"]][$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["am"])/$secondsInPeriod,StatisticProvider::$RoundPrecision),$rows[$row["month"]][$row["user_id"]]);
				$rows[$row["month"]][$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["am"])/$secondsInPeriod),$rows[$row["month"]][$row["user_id"]]);
				$rows[$row["month"]][$row["user_id"]] = str_replace("<!--abs_amount-->",$this->FormatTimespan($row["am"]),$rows[$row["month"]][$row["user_id"]]);
				$rows[$row["month"]][$row["user_id"]] = str_replace("<!--count-->","",$rows[$row["month"]][$row["user_id"]]);
				$rows[$row["month"]][$row["user_id"]] = str_replace("<!--id-->","ma_".$row["month"]."<!--number-->",$rows[$row["month"]][$row["user_id"]]);
			}
			else
				$months[$row["month"]] += $row["am"];
		}
		
		foreach($this->Months as $month)
		{
			$secondsInPeriod = 3600 * 24 * $this->Months[$month->Month]->DayCount;
			$monthnumb = intval($month->Month);
			$counter=1;
			$orows = "";
			if(isset($rows[$monthnumb]))
			{
				$crow .= str_replace("<!--title-->","<a href=\"javascript:switchRowVisibility('ma_".$monthnumb."');\"><b><!--lang_stats_month_" . strtolower(date("F",mktime(0,0,0,$month->Month,1,$month->Year))) . "--></b></a>",$trow);
				foreach($rows[$monthnumb] as $row)
					$orows .= str_replace("<!--number-->",$counter++,$row);
			}
			else
				$crow .= str_replace("<!--title-->","<!--lang_stats_month_" . strtolower(date("F",mktime(0,0,0,$month->Month,1,$month->Year))) . "-->",$trow);
				
			$crow = str_replace("<!--rel_amount-->",@round((100*$months[$monthnumb])/$secondsInPeriod,StatisticProvider::$RoundPrecision),$crow);
			$crow = str_replace("<!--rel_floor_amount-->",100-floor((100*$months[$monthnumb])/$secondsInPeriod),$crow);
			$crow = str_replace("<!--abs_amount-->",$this->FormatTimespan($months[$monthnumb]),$crow);
			$crow = str_replace("<!--count-->",date("m",mktime(0,0,0,$month->Month,1,$month->Year))."/".$month->Year,$crow);
			$crow .= $orows;
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","40",$html);
		$html = str_replace("<!--expand_all-->","",$html);
		$html = str_replace("<!--expand_all_block-->","ma",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->","<!--lang_stats_operator_availability-->",$html);
	}
	
	function GetChatsByMonths($counter=0,$html="",$crow="")
	{
		global $INTERNAL;
		$rows = array();
		
		foreach($this->Months as $month)
			$months[intval($month->Month)] = array(0,0);
		
		$hrow = getFile(TEMPLATE_HTML_STATS_HOURS_HIDDEN_ROW);
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$result = queryDB(true,"SELECT *,SUM(`amount`) AS `amount`,SUM(`declined`) AS `dec`,(SELECT SUM(`amount`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch().") AS total,(SELECT `amount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `amount` DESC LIMIT 1) AS `mamount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id`,`year`,`month` ORDER BY `user_id`,`year`,`month` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$rows[$row["month"]][$row["user_id"]] = str_replace("<!--title-->"," &raquo; " . ((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$hrow);
			$rows[$row["month"]][$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["amount"])/$row["total"],StatisticProvider::$RoundPrecision),$rows[$row["month"]][$row["user_id"]]);
			$rows[$row["month"]][$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["amount"])/$row["total"]),$rows[$row["month"]][$row["user_id"]]);
			$rows[$row["month"]][$row["user_id"]] = str_replace("<!--abs_amount-->",$row["amount"] . " (" . $row["dec"] . ")",$rows[$row["month"]][$row["user_id"]]);
			$rows[$row["month"]][$row["user_id"]] = str_replace("<!--count-->","",$rows[$row["month"]][$row["user_id"]]);
			$rows[$row["month"]][$row["user_id"]] = str_replace("<!--id-->","cbd_".$row["month"]."<!--number-->",$rows[$row["month"]][$row["user_id"]]);
			$months[$row["month"]][0]+= $row["amount"];
			$months[$row["month"]][1]+= $row["dec"];
			$max = $row["total"];
		}
		
		foreach($this->Months as $month)
		{
			$int = intval($month->Month);
			$counter=1;
			$orows = "";
			if(isset($rows[$int]))
			{
				$crow .= str_replace("<!--title-->","<a href=\"javascript:switchRowVisibility('cbd_".$int."');\"><b><!--lang_stats_month_" . strtolower(date("F",mktime(0,0,0,$month->Month,1,$month->Year))) . "--></b></a>",$trow);
				foreach($rows[$int] as $row)
					$orows .= str_replace("<!--number-->",$counter++,$row);
			}
			else
				$crow .= str_replace("<!--title-->","<!--lang_stats_month_" . strtolower(date("F",mktime(0,0,0,$month->Month,1,$month->Year))) . "-->",$trow);
				
			$crow = str_replace("<!--rel_amount-->",@round((100*$months[$int][0])/$max,StatisticProvider::$RoundPrecision),$crow);
			$crow = str_replace("<!--rel_floor_amount-->",100-@floor((100*$months[$int][0])/$max),$crow);
			$crow = str_replace("<!--abs_amount-->",$months[$int][0] . " (" . $months[$int][1]. ")",$crow);
			$crow = str_replace("<!--count-->",date("m",mktime(0,0,0,$month->Month,1,$month->Year))."/".$month->Year,$crow);
			$crow .= $orows;
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","40",$html);
		$html = str_replace("<!--expand_all-->","",$html);
		$html = str_replace("<!--expand_all_block-->","cbd",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->","Chats",$html);
	}
	
	function GetVisitorsByDays($_impressions=false,$html = "")
	{
		$hrow = getFile(TEMPLATE_HTML_STATS_SPAN_ROW);
		$total = 0;
		foreach($this->Days as $day)
			$total = max($total,($_impressions) ? $day->PageImpressionsTotal : $day->VisitorsTotal);
		foreach($this->Days as $day)
		{
			$value = ($_impressions) ? $day->PageImpressionsTotal : $day->VisitorsTotal;
			$html .= str_replace("<!--title-->","<!--lang_stats_day_" . strtolower(date("l",mktime(0,0,0,$day->Month,$day->Day,$day->Year))) . "-->",$hrow);
			if($total > 0)
			{
				$html = str_replace("<!--rel_floor_amount-->",100-floor((100*$value)/$total),$html);
			}
			else
			{
				$html = str_replace("<!--rel_floor_amount-->",100,$html);
			}
			$html = str_replace("<!--abs_amount-->",number_format($value,0,".","."),$html);
			$html = str_replace("<!--count-->",date("d.m.Y",mktime(0,0,0,$day->Month,$day->Day,$day->Year)),$html);
		}
		$html = str_replace("<!--rows-->",$html,getFile(TEMPLATE_HTML_STATS_SPAN_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_by_days-->",$html);
		return str_replace("<!--title-->",($_impressions) ? "<!--lang_stats_page_impressions-->" : "<!--lang_stats_visitors-->",$html);
	}
	
	function GetOperatorsByDays($counter=0,$html = "",$crow = "")
	{
		global $INTERNAL;
		$hrow = getFile(TEMPLATE_HTML_STATS_HOURS_HIDDEN_ROW);
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$rows = array();
		
		foreach($this->Days as $day)
			$days[intval($day->Day)] = 0;
			
		$secondsInPeriod = 3600 * 24;
		$result = queryDB(true,"SELECT *,SUM(`seconds`) as `am` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id`,`year`,`month`,`day` ORDER BY `user_id`,`day` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if($row["user_id"] != GROUP_EVERYONE_INTERN)
			{
				$rows[$row["day"]][$row["user_id"]] = str_replace("<!--title-->"," &raquo; " . ((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$hrow);
				$rows[$row["day"]][$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["am"])/$secondsInPeriod,StatisticProvider::$RoundPrecision),$rows[$row["day"]][$row["user_id"]]);
				$rows[$row["day"]][$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["am"])/$secondsInPeriod),$rows[$row["day"]][$row["user_id"]]);
				$rows[$row["day"]][$row["user_id"]] = str_replace("<!--abs_amount-->",$this->FormatTimespan($row["am"]),$rows[$row["day"]][$row["user_id"]]);
				$rows[$row["day"]][$row["user_id"]] = str_replace("<!--count-->","",$rows[$row["day"]][$row["user_id"]]);
				$rows[$row["day"]][$row["user_id"]] = str_replace("<!--id-->","da_".$row["day"]."<!--number-->",$rows[$row["day"]][$row["user_id"]]);
			}
			else
				$days[$row["day"]] += $row["am"];
		}
		
		foreach($this->Days as $day)
		{
			$daynumb = intval($day->Day);
			$counter=1;
			$orows = "";
			if(isset($rows[$daynumb]))
			{
				$crow .= str_replace("<!--title-->","<a href=\"javascript:switchRowVisibility('da_".$daynumb."');\"><b><!--lang_stats_day_" . strtolower(date("l",mktime(0,0,0,$day->Month,$day->Day,$day->Year))) . "--></b></a>",$trow);
				foreach($rows[$daynumb] as $row)
					$orows .= str_replace("<!--number-->",$counter++,$row);
			}
			else
				$crow .= str_replace("<!--title-->","<!--lang_stats_day_" . strtolower(date("l",mktime(0,0,0,$day->Month,$day->Day,$day->Year))) . "-->",$trow);
				
			$crow = str_replace("<!--rel_amount-->",@round((100*$days[$daynumb])/$secondsInPeriod,StatisticProvider::$RoundPrecision),$crow);
			$crow = str_replace("<!--rel_floor_amount-->",100-floor((100*$days[$daynumb])/$secondsInPeriod),$crow);
			$crow = str_replace("<!--abs_amount-->",$this->FormatTimespan($days[$daynumb]),$crow);
			$crow = str_replace("<!--count-->",date("d.m.Y",mktime(0,0,0,$day->Month,$day->Day,$day->Year)),$crow);
			$crow .= $orows;
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","55",$html);
		$html = str_replace("<!--expand_all-->","",$html);
		$html = str_replace("<!--expand_all_block-->","da",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->","<!--lang_stats_operator_availability-->",$html);
	}
	
	function GetChatsByDays($counter=0,$html="",$crow="")
	{
		global $INTERNAL;
		$rows = array();
		
		foreach($this->Days as $day)
			$days[intval($day->Day)] = array(0,0);
		
		$hrow = getFile(TEMPLATE_HTML_STATS_HOURS_HIDDEN_ROW);
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$result = queryDB(true,"SELECT *,SUM(`amount`) AS `amount`,SUM(`declined`) AS `dec`,(SELECT SUM(`amount`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch().") AS total,(SELECT `amount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `amount` DESC LIMIT 1) AS `mamount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id`,`year`,`month`,`day` ORDER BY `user_id`,`day` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$rows[$row["day"]][$row["user_id"]] = str_replace("<!--title-->"," &raquo; " . ((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$hrow);
			$rows[$row["day"]][$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["amount"])/$row["total"],StatisticProvider::$RoundPrecision),$rows[$row["day"]][$row["user_id"]]);
			$rows[$row["day"]][$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["amount"])/$row["total"]),$rows[$row["day"]][$row["user_id"]]);
			$rows[$row["day"]][$row["user_id"]] = str_replace("<!--abs_amount-->",$row["amount"] . " (" . $row["dec"] . ")",$rows[$row["day"]][$row["user_id"]]);
			$rows[$row["day"]][$row["user_id"]] = str_replace("<!--count-->","",$rows[$row["day"]][$row["user_id"]]);
			$rows[$row["day"]][$row["user_id"]] = str_replace("<!--id-->","cbd_".$row["day"]."<!--number-->",$rows[$row["day"]][$row["user_id"]]);
			$days[$row["day"]][0]+= $row["amount"];
			$days[$row["day"]][1]+= $row["dec"];
			$max = $row["total"];
		}
		
		foreach($this->Days as $day)
		{
			$int = intval($day->Day);
			$counter=1;
			$orows = "";
			if(isset($rows[$int]))
			{
				$crow .= str_replace("<!--title-->","<a href=\"javascript:switchRowVisibility('cbd_".$int."');\"><b><!--lang_stats_day_" . strtolower(date("l",mktime(0,0,0,$day->Month,$day->Day,$day->Year))) . "--></b></a>",$trow);
				foreach($rows[$int] as $row)
					$orows .= str_replace("<!--number-->",$counter++,$row);
			}
			else
				$crow .= str_replace("<!--title-->","<!--lang_stats_day_" . strtolower(date("l",mktime(0,0,0,$day->Month,$day->Day,$day->Year))) . "-->",$trow);
				
			$crow = str_replace("<!--rel_amount-->",@round((100*$days[$int][0])/$max,StatisticProvider::$RoundPrecision),$crow);
			$crow = str_replace("<!--rel_floor_amount-->",100-@floor((100*$days[$int][0])/$max),$crow);
			$crow = str_replace("<!--abs_amount-->",$days[$int][0] . " (" . $days[$int][1]. ")",$crow);
			$crow = str_replace("<!--count-->",date("d.m.Y",mktime(0,0,0,$day->Month,$day->Day,$day->Year)),$crow);
			$crow .= $orows;
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","55",$html);
		$html = str_replace("<!--expand_all-->","",$html);
		$html = str_replace("<!--expand_all_block-->","cbd",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->","Chats",$html);
	}

	function GetVisitorsByHour($_field,$_title,$counter=0,$crow = "",$total=0)
	{
		$hours = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0,20=>0,21=>0,22=>0,23=>0);
		$hrow = getFile(TEMPLATE_HTML_STATS_SPAN_ROW);
		$result = queryDB(true,"SELECT *,(SELECT SUM(`".$_field."`) AS `maxval` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `hour` ORDER BY `maxval` DESC LIMIT 1) as `mtotal` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_VISITORS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `hour` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$hours[$row["hour"]] += $row[$_field];
			$total = max($total,$row["mtotal"]);
		}
		foreach($hours as $hour => $amount)
		{
			$crow .= str_replace("<!--title-->",date("H:i",mktime($hour,0,0)),$hrow);
			if($total > 0)
				$crow = str_replace("<!--rel_floor_amount-->",100-floor((100*$amount)/$total),$crow);
			else
				$crow = str_replace("<!--rel_floor_amount-->",100,$crow);
			$crow = str_replace("<!--abs_amount-->",number_format($amount,0,".","."),$crow);
			$crow = str_replace("<!--count-->","&nbsp;",$crow);
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_SPAN_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->",$_title,$html);
	}
	
	function GetChatsByHour($counter=0,$crow = "",$max=0)
	{
		global $INTERNAL;
		$rows = array();
		$hours = array(array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0));
		$hrow = getFile(TEMPLATE_HTML_STATS_HOURS_HIDDEN_ROW);
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$result = queryDB(true,"SELECT *,(SELECT SUM(`amount`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch().") AS total,(SELECT `amount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `amount` DESC LIMIT 1) AS `mamount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE".$this->GetNoneAggregatedDateMatch()." ORDER BY `user_id`,`hour` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--title-->"," &raquo; " . ((isset($INTERNAL[$row["user_id"]]))?$INTERNAL[$row["user_id"]]->Fullname:"<!--lang_stats_unknown-->"),$hrow);
			$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$row["amount"])/$row["total"],StatisticProvider::$RoundPrecision),$rows[$row["hour"]][$row["user_id"]]);
			$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$row["amount"])/$row["total"]),$rows[$row["hour"]][$row["user_id"]]);
			$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--abs_amount-->",$row["amount"] . " (" . $row["declined"] . ")",$rows[$row["hour"]][$row["user_id"]]);
			$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--count-->","",$rows[$row["hour"]][$row["user_id"]]);
			$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--id-->","cbh_".$row["hour"]."<!--number-->",$rows[$row["hour"]][$row["user_id"]]);
			$hours[$row["hour"]][0]+= $row["amount"];
			$hours[$row["hour"]][1]+= $row["declined"];
			$max = $row["total"];
		}
		
		for($int=0;$int<24;$int++)
		{
			$counter=1;
			$orows = "";
			if(isset($rows[$int]))
			{
				$crow .= str_replace("<!--title-->","<a href=\"javascript:switchRowVisibility('cbh_".$int."');\"><b>".date("H:i",mktime($int,0,0))."</b></a>",$trow);
				foreach($rows[$int] as $row)
					$orows .= str_replace("<!--number-->",$counter++,$row);
			}
			else
				$crow .= str_replace("<!--title-->",date("H:i",mktime($int,0,0)),$trow);
				
			$crow = str_replace("<!--rel_amount-->",@round((100*$hours[$int][0])/$max,StatisticProvider::$RoundPrecision),$crow);
			$crow = str_replace("<!--rel_floor_amount-->",100-@floor((100*$hours[$int][0])/$max),$crow);
			$crow = str_replace("<!--abs_amount-->",$hours[$int][0] . " (" . $hours[$int][1]. ")",$crow);
			$crow = str_replace("<!--count-->","&nbsp;",$crow);
			$crow .= $orows;
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","",$html);
		$html = str_replace("<!--expand_all_block-->","cbh",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->","Chats",$html);
	}

	function GetOperatorsByHour($crow = "")
	{
		global $INTERNAL;
		$hrow = getFile(TEMPLATE_HTML_STATS_HOURS_HIDDEN_ROW);
		$trow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$rows = array();
		$hours = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		$ontime = array();
		$secondsInPeriod = 3600 * $this->DayCount;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_STATS_AGGS_AVAILABILITIES."` WHERE".$this->GetNoneAggregatedDateMatch()." GROUP BY `user_id`,`hour`,`status` ORDER BY `user_id` ASC,`hour` ASC,`status` ASC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$row["seconds"] = min($row["seconds"],3600);
			if($row["user_id"] != GROUP_EVERYONE_INTERN)
			{
				$secval = (!empty($ontime[$row["user_id"]][$row["hour"]])) ? $row["seconds"]+$ontime[$row["user_id"]][$row["hour"]] : $row["seconds"];
				$busystr = (!empty($ontime[$row["user_id"]][$row["hour"]]) || $row["status"] == USER_STATUS_BUSY) ? "&nbsp;(<!--lang_stats_status_busy-->: " . $this->FormatTimespan($row["seconds"]) . ")" : "";
				$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--title-->"," &raquo; " . ((isset($INTERNAL[$row["user_id"]]))?($INTERNAL[$row["user_id"]]->Fullname.$busystr):"<!--lang_stats_unknown-->"),$hrow);
				$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--rel_amount-->",@round((100*$secval)/$secondsInPeriod,StatisticProvider::$RoundPrecision),$rows[$row["hour"]][$row["user_id"]]);
				$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--rel_floor_amount-->",100-@floor((100*$secval)/$secondsInPeriod),$rows[$row["hour"]][$row["user_id"]]);
				$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--abs_amount-->",$this->FormatTimespan($secval),$rows[$row["hour"]][$row["user_id"]]);
				$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--count-->","",$rows[$row["hour"]][$row["user_id"]]);
				$rows[$row["hour"]][$row["user_id"]] = str_replace("<!--id-->","oa_".$row["hour"]."<!--number-->",$rows[$row["hour"]][$row["user_id"]]);
				$ontime[$row["user_id"]][$row["hour"]] = ($row["status"] == USER_STATUS_ONLINE) ? $row["seconds"] : 0;
			}
			else
				$hours[$row["hour"]] += $row["seconds"];
		}
		
		for($int=0;$int<24;$int++)
		{
			$counter=1;
			$orows = "";
			if(isset($rows[$int]))
			{
				$crow .= str_replace("<!--title-->","<a href=\"javascript:switchRowVisibility('oa_".$int."');\"><b>".date("H:i",mktime($int,0,0))."</b></a>",$trow);
				foreach($rows[$int] as $row)
					$orows .= str_replace("<!--number-->",$counter++,$row);
			}
			else
				$crow .= str_replace("<!--title-->",date("H:i",mktime($int,0,0)),$trow);
				
			$crow = str_replace("<!--rel_amount-->",@round((100*$hours[$int])/$secondsInPeriod,StatisticProvider::$RoundPrecision),$crow);
			$crow = str_replace("<!--rel_floor_amount-->",100-floor((100*$hours[$int])/$secondsInPeriod),$crow);
			$crow = str_replace("<!--abs_amount-->",$this->FormatTimespan($hours[$int]),$crow);
			$crow = str_replace("<!--count-->","&nbsp;",$crow);
			$crow .= $orows;
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","",$html);
		$html = str_replace("<!--expand_all_block-->","oa",$html);
		$html = str_replace("<div class=\"ico_trend_<!--trend-->\">","&nbsp;",$html);
		$html = str_replace("<!--column_value_title-->","<!--lang_stats_hour-->",$html);
		return str_replace("<!--title-->","<!--lang_stats_operator_availability-->",$html);
	}
	
	function LoadURLTable($_table,$_id,$_maxlength,$_title=false,$counter=0)
	{
		$result = queryDB(true,"SELECT `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`domain` as did,`".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`path` as pid,`".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain`,`".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."`.`path`,`amount` as abs FROM `".DB_PREFIX.$_table."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` ON `".DB_PREFIX.$_table."`.`url` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` ON `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`domain` = `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` ON `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`path` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."`.`id` WHERE".$this->GetDateMatch()." AND `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain`!='' ORDER BY `abs` DESC,`title` DESC LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";");
		while(($row = mysql_fetch_array($result, MYSQL_BOTH)))
		{
			if($row["abs"] > 0 && $this->PageImpressionsTotal > 0)
			{
				$url = new BaseURL($row["domain"],$row["path"],"","");
				
				$results = queryDB(true,"SELECT dbt.`title` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` INNER JOIN (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."`) AS `dbt` ON `dbt`.`id` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`title` WHERE `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`path`='".@mysql_real_escape_string($row["pid"])."' AND `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`domain`='".@mysql_real_escape_string($row["did"])."';");
				$rows = mysql_fetch_array($results, MYSQL_BOTH);

				$url->Params = "";
				$title = ((!$_title) ? substr(((StatisticProvider::$AggregateDomains)?$url->Path:$url->GetAbsoluteUrl()),0,$_maxlength) : ($rows["title"]));
				if(empty($title))
					$title = "<!--lang_stats_unknown-->";
				$title = "<a href=\"" . htmlentities(StatisticProvider::$Dereferrer . $url->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8')."\" target=\"_blank\">" . $title . "</a>";
				$this->Tops[$_id][++$counter] = array($title,@round((100*$row["abs"])/$this->PageImpressionsTotal,StatisticProvider::$RoundPrecision),100-floor((100*$row["abs"])/$this->PageImpressionsTotal),$row["abs"]);
			}
		}
		if($counter==0)
			$this->Tops[$_id][++$counter] = array("<!--lang_stats_none-->",0,100,0);
	}
	
	function LoadSearchEngineTable($_id,$_maxlength,$_total,$counter=0)
	{
		$row = mysql_fetch_array(queryDB(true,"SELECT SUM(`amount`) AS `total` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_SEARCH_ENGINES."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` ON `".DB_PREFIX.DATABASE_STATS_AGGS_SEARCH_ENGINES."`.`domain` = `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`id` WHERE `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain` != '' AND".$this->GetDateMatch()." LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";"), MYSQL_BOTH);
		$_total = $row["total"];
		
		$result = queryDB(true,"SELECT `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain`,`amount` AS `tam` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_SEARCH_ENGINES."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` ON `".DB_PREFIX.DATABASE_STATS_AGGS_SEARCH_ENGINES."`.`domain` = `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`id` WHERE".$this->GetDateMatch()." ORDER BY `tam` DESC LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";");
		while(($row = mysql_fetch_array($result, MYSQL_BOTH)))
		{
			if(!empty($row["domain"]))
			{
				$url = new BaseURL($row["domain"],"","","");
				$title = "<a href=\"" . htmlentities(StatisticProvider::$Dereferrer . $url->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8')."\" target=\"_blank\">".htmlentities(substr($url->GetAbsoluteUrl(),0,$_maxlength),ENT_QUOTES,'UTF-8')."</a>";
				$this->Tops[$_id][++$counter] = array($title,@round((100*$row["tam"])/$_total,StatisticProvider::$RoundPrecision),100-floor((100*$row["tam"])/$_total),$row["tam"]);
			}
		}
		if($counter==0)
			$this->Tops[$_id][++$counter] = array("<!--lang_stats_none-->",0,100,0);
	}
	
	function LoadReferrerTable($_id,$_maxlength,$_table,$_fullUrl,$_total,$_group="",$counter=0)
	{
		if(!empty($_group))
		{
			$amount = "SUM(`amount`)";
			$groupBy = " GROUP BY " . $_group;
		}
		else
		{
			$amount = "`amount`";
			$groupBy = "";
		}
	
		$row = mysql_fetch_array(queryDB(true,"SELECT SUM(`amount`) as `total` FROM `".DB_PREFIX.$_table."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` ON `".DB_PREFIX.$_table."`.`referrer` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` ON `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`domain` = `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` ON `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`path` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."`.`id` AND `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain` != '' WHERE".$this->GetDateMatch()." LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";"), MYSQL_BOTH);
		$_total = $row["total"];
		
		$result = queryDB(true,"SELECT `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`domain`,`".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."`.`path`,".$amount." AS `tam` FROM `".DB_PREFIX.$_table."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` ON `".DB_PREFIX.$_table."`.`referrer` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` ON `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`domain` = `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` ON `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."`.`path` = `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."`.`id` WHERE".$this->GetDateMatch()."".$groupBy." ORDER BY `tam` DESC LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";");
		while(($row = mysql_fetch_array($result, MYSQL_BOTH)))
		{
			
			if(!empty($row["domain"]))
			{
				$url = new BaseURL($row["domain"],(($_fullUrl)?$row["path"]:""),"","");
				$title = "<a href=\"" . htmlentities(StatisticProvider::$Dereferrer . $url->GetAbsoluteUrl(),ENT_QUOTES,'UTF-8')."\" target=\"_blank\">".htmlentities(substr($url->GetAbsoluteUrl(),0,$_maxlength),ENT_QUOTES,'UTF-8')."</a>";
				$this->Tops[$_id][++$counter] = array($title,@round((100*$row["tam"])/$_total,StatisticProvider::$RoundPrecision),100-floor((100*$row["tam"])/$_total),$row["tam"]);
			}
		}
		if($counter==0)
			$this->Tops[$_id][++$counter] = array("<!--lang_stats_none-->",0,100,0);
	}
	
	function LoadTopTable($_id,$_maxlength,$_tables,$_fields,$_blanks,$_relSource,$_isURL=false,$_countField="amount",$_orderField="amount",$_direction="DESC")
	{
		$counter=0;
		if($_relSource > 0)
		{
			if(count($_tables) == 2)
			{
				$_blanks = ($_blanks) ? "" : " AND `".DB_PREFIX.@mysql_real_escape_string($_tables[1])."`.`".@mysql_real_escape_string($_fields[0])."`!=''";
				$result = queryDB(true,"SELECT `".DB_PREFIX.@mysql_real_escape_string($_tables[1])."`.`".@mysql_real_escape_string($_fields[0])."`,`".$_countField."` FROM `".DB_PREFIX.@mysql_real_escape_string($_tables[0])."` INNER JOIN `".DB_PREFIX.@mysql_real_escape_string($_tables[1])."` ON `".DB_PREFIX.@mysql_real_escape_string($_tables[0])."`.`".@mysql_real_escape_string($_fields[1])."`=`".DB_PREFIX.@mysql_real_escape_string($_tables[1])."`.`id` WHERE ".$this->GetDateMatch().$_blanks."  ORDER BY `".$_orderField."` ".$_direction."  LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";");
			}
			else
				$result = queryDB(true,"SELECT `".DB_PREFIX.@mysql_real_escape_string($_tables[0])."`.`".@mysql_real_escape_string($_fields[0])."`,`".$_countField."` FROM `".DB_PREFIX.@mysql_real_escape_string($_tables[0])."` WHERE ".$this->GetDateMatch()." ORDER BY `".$_orderField."` ".$_direction."  LIMIT ".@mysql_real_escape_string(StatisticProvider::$DayItemAmount).";");

			$values = array();
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$title = "";
				if(!isnull(trim($row[$_fields[0]])))
				{
					if($_id != 6)
						$row[$_fields[0]] = ($_isURL) ? "<a href=\"".htmlentities(StatisticProvider::$Dereferrer . $row[$_fields[0]],ENT_QUOTES,'UTF-8')."\" target=\"_blank\">" . htmlentities(substr($row[$_fields[0]],0,$_maxlength),ENT_QUOTES,'UTF-8')."</a>" : htmlentities(substr($row[$_fields[0]],0,$_maxlength),ENT_QUOTES,'UTF-8');
					else
						$row[$_fields[0]] = $row[$_fields[0]];
					$title = $row[$_fields[0]];
				}
				else
					$title = "<!--lang_stats_unknown-->";
				
				if(!isset($values[$title]))
					$values[$title]=0;
				$values[$title]+=$row[$_countField];
			}
			foreach($values as $title => $amount)
				$this->Tops[$_id][++$counter] = array($title,@round((100*$amount)/$_relSource,StatisticProvider::$RoundPrecision),100-((100*$amount)/$_relSource),$amount);
		}
		if($counter==0)
			$this->Tops[$_id][++$counter] = array("<!--lang_stats_none-->",0,100,0);
	}
	
	function RenderTopURLTable($_id,$_title,$_column,$_trend=true,$_tabTitle1="URL",$_tabTitle2="<!--lang_stats_titles-->",$html="")
	{
		$hrow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
		$html = getFile(TEMPLATE_HTML_STATS_TOP_URL_TABLE);
		for($i=1;$i<=2;$i++)
		{
			$crow = "";
			foreach($this->Tops[$_id+($i-1)] as $count => $values)
			{
				$crow .= str_replace("<!--title-->",$values[0],$hrow);
				$crow = str_replace("<!--rel_amount-->",$values[1],$crow);
				$crow = str_replace("<!--rel_floor_amount-->",$values[2],$crow);
				$crow = str_replace("<!--abs_amount-->",number_format($values[3],0,".","."),$crow);
				$crow = str_replace("<!--trend-->",$this->GetTopTrend($_id+($i-1),$values[0],$count),$crow);
				$crow = str_replace("<!--count-->",$count,$crow);
			}
			$html = str_replace("<!--rows_".$i."-->",$crow,$html);
		}
		
		if($_trend)
			$html = str_replace("<!--trend-->","<!--lang_stats_trend-->",$html);
		$html = str_replace("<!--column_value_title-->",$_column,$html);
		$html = str_replace("<!--id-->",$_id,$html);
		$html = str_replace("<!--tab_title_1-->",$_tabTitle1,$html);
		$html = str_replace("<!--tab_title_2-->",$_tabTitle2,$html);
		$html = str_replace("<!--id-->",$_id,$html);
		return str_replace("<!--title-->",$_title,$html);
	}

	function RenderTopTable($_id,$_title,$_column,$_trend=true,$crow = "")
	{
		global $LANGUAGES,$COUNTRIES;
		$hrow = getFile(TEMPLATE_HTML_STATS_TOP_ROW);
			
		if($_id==6)
			initData(false,false,false,false,false,true);
		else if($_id==2)
			initData(false,false,false,false,false,false,true);
		
		foreach($this->Tops[$_id] as $count => $values)
		{
			if($_id==6 && isset($LANGUAGES[strtoupper($values[0])][0]))
				$crow .= str_replace("<!--title-->",$LANGUAGES[strtoupper($values[0])][0],$hrow);
			else if($_id==2 && isset($COUNTRIES[strtoupper($values[0])]))
				$crow .= str_replace("<!--title-->",$COUNTRIES[strtoupper($values[0])],$hrow);
			else if($_id==13 && isset(StatisticProvider::$Durations[$values[0]]))
				$crow .= str_replace("<!--title-->",StatisticProvider::$Durations[$values[0]],$hrow);
			else
				$crow .= str_replace("<!--title-->",$values[0],$hrow);
				
			$crow = str_replace("<!--rel_amount-->",$values[1],$crow);
			$crow = str_replace("<!--rel_floor_amount-->",$values[2],$crow);
			$crow = str_replace("<!--abs_amount-->",number_format($values[3],0,".","."),$crow);
			
			if($_id==13)
				$crow = str_replace("<!--trend-->",$this->GetTopTrendValue($_id,$values[0],$values[2]),$crow);
			else
				$crow = str_replace("<!--trend-->",$this->GetTopTrend($_id,$values[0],$count),$crow);
				
			$crow = str_replace("<!--count-->",$count,$crow);
		}
		$html = str_replace("<!--rows-->",$crow,getFile(TEMPLATE_HTML_STATS_TOP_TABLE));
		$html = str_replace("<!--column_count_width-->","12",$html);
		$html = str_replace("<!--expand_all-->","none",$html);
		$html = str_replace("<!--expand_all_block-->","",$html);
		if($_trend)
			$html = str_replace("<!--trend-->","<!--lang_stats_trend-->",$html);
		$html = str_replace("<!--column_value_title-->",$_column,$html);
		return str_replace("<!--title-->",$_title,$html);
	}
	
	function GetTopTrendValue($_id,$_value,$_relative)
	{
		if($this->Comparer == null)
			return "const";

		foreach($this->Comparer->Tops[$_id] as $count => $values)
		{
			if($values[0] == $_value)
			{
				if($values[2] < $_relative)
					return "down";
				else if($values[2] > $_relative)
					return "up";
			}
		}
		return "const";
	}
	
	function GetTopTrend($_id,$_value,$_number)
	{
		if($this->Comparer == null)
			return "const";
	
		foreach($this->Comparer->Tops[$_id] as $count => $values)
		{
			if($values[0] == $_value)
			{
				if($count < $_number)
					return "down";
				else if($count > $_number)
					return "up";
				else
					return "const";
			}
		}
		return "new";
	}
	
	function GetTotalTrend($_property,$_relProp=null,$_reverse=false)
	{
		$_reverse = ($_reverse) ? "_r" : "";
		if($this->Comparer == null)
			return "const";
		
		$current_value = $this->$_property;
		$comparer_value = $this->Comparer->$_property;
		
		if(!empty($_relProp))
		{
			$comparer_value = @round((100*$this->Comparer->$_property)/$this->Comparer->$_relProp,StatisticProvider::$RoundPrecision);
			$current_value = @round((100*$this->$_property)/$this->$_relProp,StatisticProvider::$RoundPrecision);
		}
		
		if($comparer_value < $current_value)
			return "up".$_reverse;
		else if($comparer_value == $current_value)
			return "const";
		else
			return "down".$_reverse;
	}
	
	function FormatTimespan($_span)
	{
		$formatted = "";
		$hrsmins = array(3600,60);
		foreach($hrsmins as $val)
			if($_span >= $val)
			{
				if(floor($_span / $val) >= 10)
					$formatted .= floor($_span / $val) . ":";
				else
					$formatted .= "0" . floor($_span / $val) . ":";
				$_span -= floor($_span / $val)*$val;
			}
			else
				$formatted .= "00:";
		if($_span >= 10)
			$formatted .= floor($_span);
		else if($_span > 0)
			$formatted .= "0" . floor($_span);
		else
			$formatted .= "00";
		return $formatted;
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_STATS_AGGS."` (`year`,`month`,`day`,`time`,`mtime`) VALUES (".$this->GetSQLDateValues().",".(time()).",".mTime().");");
	}
	
	function Update($_optional=true)
	{
		global $INTERNAL;
		if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_REPORTS) == PERMISSION_FULL)
		{
			if(!$_optional)
				$result = queryDB(true,"SELECT `time` FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `year`='".@mysql_real_escape_string($this->Year)."' AND `month`='".@mysql_real_escape_string($this->Month)."' AND `day`='".@mysql_real_escape_string($this->Day)."' AND `aggregated`=0 LIMIT 1;");
			else if(is_numeric(StatisticProvider::$UpdateInterval))
				$result = queryDB(true,"SELECT `time` FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `year`='".@mysql_real_escape_string($this->Year)."' AND `month`='".@mysql_real_escape_string($this->Month)."' AND `day`='".@mysql_real_escape_string($this->Day)."' AND `aggregated`=0 AND `time`<".(time()-StatisticProvider::$UpdateInterval)." LIMIT 1;");
			if($result)
			{
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
				{
					if($this->Type == STATISTIC_PERIOD_TYPE_DAY)
						$this->SaveVisitorListToFile();
					$this->SaveReportToFile();
				}
				else if(@file_exists($this->GetFilename(true,false)))
				{
					$result = queryDB(true,"SELECT `time`,`mtime` FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `year`='".@mysql_real_escape_string($this->Year)."' AND `month`='".@mysql_real_escape_string($this->Month)."' AND `day`='".@mysql_real_escape_string($this->Day)."' LIMIT 1;");
					if($result)
					{
						if($row = mysql_fetch_array($result, MYSQL_BOTH))
						{
							$parts = explode("_",$_POST[POST_INTERN_XMLCLIP_REPORTS_END_TIME]);
							if($parts[0] > $row["time"])
								$_POST[POST_INTERN_XMLCLIP_REPORTS_END_TIME] = $row["time"] . "_" . ($row["mtime"]-1);
						}
					}
				}
			}
		}
	}
	
	function GetFilename($_fullPath,$_visitorList)
	{
		$month = ($this->Type != STATISTIC_PERIOD_TYPE_YEAR) ? "_" . date("m",mktime(0,0,0,$this->Month,1,$this->Year)) : "";
		$day = ($this->Type == STATISTIC_PERIOD_TYPE_DAY) ? "_" . date("d",mktime(0,0,0,$this->Month,$this->Day,$this->Year)) : "";
		if(!$_fullPath)
			return $this->Year . $month . $day . (($_visitorList)?"_u":"") . "_" . StatisticProvider::$StatisticKey;
		else
			return PATH_STATS . $this->Type . "/" . $this->Year . $month . $day . (($_visitorList)?"_u":"") . "_" . StatisticProvider::$StatisticKey;
	}
	
	function SaveReportToFile()
	{
		if($this->Type == STATISTIC_PERIOD_TYPE_MONTH)
			$this->LoadDays();
		else if($this->Type == STATISTIC_PERIOD_TYPE_YEAR)
			$this->LoadMonths();
		$this->Aggregate();
		$this->Load();
		createFile($this->GetFilename(true,false),$this->GetHTML(),true);
	}
	
	function SaveVisitorListToFile()
	{
		createFile($this->GetFilename(true,true),$this->GetUsersHTML(),true);
	}
	
	function Close()
	{
		global $INTERNAL,$CONFIG;
		initData(true);
		if($this->Type == STATISTIC_PERIOD_TYPE_DAY)
		{
			if($this->CreateReport)
				$this->SaveReportToFile();
			if($this->CreateVisitorList)
				$this->SaveVisitorListToFile();
				
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `last_active`<'".@mysql_real_escape_string($this->Delimiters[1]-$CONFIG["timeout_clients"])."';");
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `last_active`<'".@mysql_real_escape_string($this->Delimiters[1]-$CONFIG["timeout_clients"])."';");
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` WHERE `".DB_PREFIX.DATABASE_OPERATOR_STATUS."`.`confirmed`<'".@mysql_real_escape_string($this->Delimiters[1])."';");
		}
		else if($this->CreateReport)
			$this->SaveReportToFile();
	}
}
?>