<?php
/****************************************************************************************
* LiveZilla functions.data.db.update.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

function updateDatabase($_version,$_link,$_prefix)
{
	global $GROUPS;
	$versions = array("3.1.8.1","3.1.8.2","3.1.8.3","3.1.8.4","3.1.8.5","3.1.8.6","3.2.0.0","3.2.0.1","3.2.0.2","3.2.0.3","3.3.0.0","3.3.1.0","3.3.1.1","3.3.1.2","3.3.1.3","3.3.2.0","3.3.2.1","3.3.2.2");
	if(!in_array($_version,$versions))
		return "Invalid version! (".$_version.")";
	
	while($_version != VERSION)
	{
		if($_version == $versions[0])
			$_version = $versions[1];
		if($_version == $versions[1])
			$_version = $versions[2];
		if($_version == $versions[2])
		{
			$result = up_3183_3184($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[3];
			else
				return $result;
		}
		if($_version == $versions[3])
			$_version = $versions[4];
		if($_version == $versions[4])
			$_version = $versions[5];
		if($_version == $versions[5])
		{
			$result = up_3186_3200($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[6];
			else
				return $result;
		}
		if($_version == $versions[6])
		{
			$result = up_3200_3201($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[7];
			else
				return $result;
		}
		if($_version == $versions[7])
			$_version = $versions[9];
		if($_version == $versions[8])
			$_version = $versions[9];
		if($_version == $versions[9])
		{
			$result = up_3203_3300($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[10];
			else
				return $result;
		}
		if($_version == $versions[10])
		{
			$result = up_3300_3310($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[11];
			else
				return $result;
		}
		if($_version == $versions[11])
		{
			$result = up_3310_3311($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[12];
			else
				return $result;
		}
		if($_version == $versions[12])
		{
			$result = up_3311_3312($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[13];
			else
				return $result;
		}
		if($_version == $versions[13])
			$_version = $versions[14];
		if($_version == $versions[14])
		{
			$result = up_3313_3320($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[15];
			else
				return $result;
		}
		if($_version == $versions[15])
			$_version = $versions[16];
		if($_version == $versions[16])
			$_version = $versions[17];
	}
	@mysql_query("UPDATE `".@mysql_real_escape_string($_prefix)."info` SET `version`='" . VERSION . "'",$_link);
	return true;
}

function processCommandList($_commands,$_link)
{
	foreach($_commands as $key => $parts)
	{
		$result = @mysql_query($parts[1],$_link);
		if(!$result && mysql_errno() != $parts[0] && $parts[0] != 0)
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $parts[1];
	}
	return true;
}

function up_3313_3320($_prefix,$_link)
{
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` TO `".@mysql_real_escape_string($_prefix)."event_action_overlays`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."events` ADD `search_phrase` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	return processCommandList($commands,$_link);
}

function up_3311_3312($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `translation_iso` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `translation`;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `endtime` `endtime` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `closed` `closed` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';");
	return processCommandList($commands,$_link);
}

function up_3310_3311($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD `untouched` text COLLATE utf8_bin NOT NULL;");
	return processCommandList($commands,$_link);
}

function up_3300_3310($_prefix,$_link)
{
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_visitors` CHANGE `js` `js` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD `dtime` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `iso_country` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `iso_language`;");
	$commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_goals` DROP FOREIGN KEY `visitor_goals_ibfk_2`;");
	return processCommandList($commands,$_link);
}

function up_3203_3300($_prefix,$_link)
{
	// DROP TABLES
	$commands[] = array(1051,"DROP TABLE `".@mysql_real_escape_string($_prefix)."chat_rooms`;");
	$commands[] = array(1051,"DROP TABLE `".@mysql_real_escape_string($_prefix)."data`;");
	
	// RENAME
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."chats` TO `".@mysql_real_escape_string($_prefix)."chat_archive`;");
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."internal` TO `".@mysql_real_escape_string($_prefix)."operator_status`;");
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."logins` TO `".@mysql_real_escape_string($_prefix)."operator_logins`;");
	
	// TRUNCATE
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."alerts`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."chat_posts`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."events`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_actions`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_urls`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_triggers`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."operator_status`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."tickets`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."website_pushs`;");
	
	// ENGINES
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."alerts` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."events` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_actions` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."tickets` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_urls` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` ENGINE = InnoDB;");
	
	// FIELDS DROP
	$commands[] = array(1091,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` DROP `id`;");
	$commands[] = array(1091,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` DROP `id`;");
	
	// FIELDS CHANGE
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` CHANGE `sender` `sender` VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` CHANGE `receiver` `receiver` VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `lang_iso` `lang_iso` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."profiles` CHANGE `languages` `languages` VARCHAR( 1024 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` CHANGE `ticket_id` `ticket_id` VARCHAR( 32 ) NOT NULL DEFAULT '';");
	
	$commands[] = array(0,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` ADD `time_confirmed` INT( 11 ) UNSIGNED NOT NULL;");
	$commands[] = array(0,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` CHANGE `time_confirmed` `confirmed` INT( 11 ) UNSIGNED NOT NULL;");
	
	// FIELDS ADD
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `translation` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `text`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD `closed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `declined`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `transcript_receiver` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `transcript_sent`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `customs` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."info` ADD `gtspan` INT UNSIGNED NOT NULL DEFAULT '0';");

	// CREATE TABLES
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."event_funnels` (`eid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `uid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `ind` smallint(5) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`eid`,`uid`),  KEY `uid` (`uid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."event_goals` (`event_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `goal_id` int(10) unsigned NOT NULL DEFAULT '0', UNIQUE KEY `prim` (`event_id`,`goal_id`), KEY `target_id` (`goal_id`),  KEY `event_id` (`event_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."goals` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) COLLATE utf8_bin NOT NULL, `description` text COLLATE utf8_bin NOT NULL, `conversion` tinyint(1) unsigned NOT NULL DEFAULT '0', `ind` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`), UNIQUE KEY `title` (`title`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs` ( `year` smallint(4) unsigned NOT NULL DEFAULT '0',  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',  `time` int(10) unsigned NOT NULL DEFAULT '0',  `mtime` int(10) unsigned NOT NULL DEFAULT '0',`sessions` int(10) unsigned NOT NULL DEFAULT '0',`visitors_unique` int(10) unsigned NOT NULL DEFAULT '0', `conversions` int(10) unsigned NOT NULL DEFAULT '0',  `aggregated` tinyint(1) unsigned NOT NULL DEFAULT '0',  `chats_forwards` int(10) unsigned NOT NULL DEFAULT '0',  `chats_posts_internal` int(10) unsigned NOT NULL DEFAULT '0',  `chats_posts_external` int(10) unsigned NOT NULL DEFAULT '0',  `avg_time_site` double unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`year`,`month`,`day`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_availabilities` ( `year` smallint(5) unsigned NOT NULL DEFAULT '0',  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',  `hour` tinyint(2) unsigned NOT NULL DEFAULT '0',  `user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `status` tinyint(1) unsigned NOT NULL DEFAULT '0',  `seconds` int(4) unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`year`,`month`,`day`,`user_id`,`hour`,`status`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_browsers` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`browser` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`browser`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_chats` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`hour` tinyint(2) unsigned NOT NULL DEFAULT '0',`user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`amount` int(10) unsigned NOT NULL DEFAULT '0',`accepted` int(10) unsigned NOT NULL DEFAULT '0',`declined` int(10) unsigned NOT NULL DEFAULT '0',`avg_duration` double unsigned NOT NULL DEFAULT '0',`avg_waiting_time` double unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`user_id`,`hour`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_cities` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`city` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`city`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_countries` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`country`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_crawlers` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`crawler` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`crawler`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_domains` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`domain` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`domain`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_durations` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`duration` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`duration`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_goals` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`goal` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`goal`),KEY `target` (`goal`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_isps` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`isp` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`isp`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_languages` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`language` varchar(5) COLLATE utf8_bin NOT NULL,`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`language`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_pages` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`url`),KEY `url_id` (`url`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_entrance` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`url`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_exit` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`url`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_queries` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`query` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`query`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_referrers` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`referrer` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`referrer`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_regions` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`region` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`region`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_resolutions` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`resolution` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`resolution`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_search_engines` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`domain` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`domain`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_systems` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`system` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`system`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_visitors` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`hour` tinyint(3) unsigned NOT NULL DEFAULT '0',`visitors_unique` int(10) unsigned NOT NULL DEFAULT '0',`page_impressions` int(10) unsigned NOT NULL DEFAULT '0',`visitors_recurring` int(10) unsigned NOT NULL DEFAULT '0',`bounces` int(10) unsigned NOT NULL DEFAULT '0',`search_engine` int(10) unsigned NOT NULL DEFAULT '0',`from_referrer` int(10) unsigned NOT NULL DEFAULT '0',`browser_instances` int(10) unsigned NOT NULL DEFAULT '0',`js` int(10) unsigned NOT NULL DEFAULT '0',`on_chat_page` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`hour`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_visits` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`visits` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`visits`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitors` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`entrance` int(10) unsigned NOT NULL DEFAULT '0',`last_active` int(10) unsigned NOT NULL DEFAULT '0',`host` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',`system` smallint(5) unsigned NOT NULL DEFAULT '0',`browser` smallint(5) unsigned NOT NULL DEFAULT '0',`visits` smallint(5) unsigned NOT NULL DEFAULT '0',`visit_id` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT '',`visit_latest` tinyint(1) unsigned NOT NULL DEFAULT '1',`visit_last` int(10) unsigned NOT NULL DEFAULT '0',`resolution` smallint(5) unsigned NOT NULL DEFAULT '0',`language` varchar(5) COLLATE utf8_bin NOT NULL,`country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',`city` smallint(5) unsigned NOT NULL DEFAULT '0',`region` smallint(5) unsigned NOT NULL DEFAULT '0',`isp` smallint(5) unsigned NOT NULL DEFAULT '0',`timezone` varchar(24) COLLATE utf8_bin NOT NULL DEFAULT '',`latitude` double NOT NULL DEFAULT '0',`longitude` double NOT NULL DEFAULT '0',`geo_result` int(10) unsigned NOT NULL DEFAULT '0',`js` tinyint(1) unsigned NOT NULL DEFAULT '0',`signature` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`,`entrance`),UNIQUE KEY `visit_id` (`visit_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`visit_id` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`last_active` int(10) unsigned NOT NULL DEFAULT '0',`last_update` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',`is_chat` tinyint(1) unsigned NOT NULL DEFAULT '0',`query` int(10) unsigned NOT NULL DEFAULT '0',`fullname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`customs` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`url_entrance` int(10) unsigned NOT NULL DEFAULT '0',`url_exit` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),KEY `visit_id` (`visit_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` (`browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`entrance` int(10) unsigned NOT NULL DEFAULT '0',`referrer` int(10) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`params` text COLLATE utf8_bin NOT NULL,PRIMARY KEY (`entrance`,`browser_id`),KEY `browser_id` (`browser_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_area_codes` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`area_code` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `area_code` (`area_code`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_browsers` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`browser` varchar(255) COLLATE utf8_bin NOT NULL,`type` tinyint(1) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `browser` (`browser`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_cities` (`id` int(11) NOT NULL AUTO_INCREMENT,`city` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `city` (`city`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_crawlers` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`crawler` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `crawler` (`crawler`),UNIQUE KEY `crawler_2` (`crawler`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_domains` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`domain` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`external` tinyint(1) unsigned NOT NULL DEFAULT '1',`search` tinyint(1) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `domain` (`domain`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_isps` (`id` int(11) NOT NULL AUTO_INCREMENT,`isp` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `isp` (`isp`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_pages` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`domain` int(10) unsigned NOT NULL DEFAULT '0',`path` int(10) unsigned NOT NULL DEFAULT '0',`title` int(10) unsigned NOT NULL DEFAULT '0',`area_code` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `UNIQ` (`domain`,`path`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_paths` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`path` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `path` (`path`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_queries` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`query` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `query` (`query`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_regions` (`id` int(11) NOT NULL AUTO_INCREMENT,`region` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `region` (`region`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_resolutions` (`id` int(11) NOT NULL AUTO_INCREMENT,`resolution` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `resolution` (`resolution`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_systems` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`system` varchar(255) COLLATE utf8_bin NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `os` (`system`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_titles` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`title` varchar(255) COLLATE utf8_bin NOT NULL,`confirmed` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `title` (`title`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_goals` (`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`goal_id` int(10) unsigned NOT NULL DEFAULT '0',`time` int(10) unsigned NOT NULL DEFAULT '0',`first_visit` tinyint(1) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`visitor_id`,`goal_id`),KEY `visitor_id` (`visitor_id`),KEY `target_id` (`goal_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."ticket_customs` (`ticket_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `custom_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `value` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`ticket_id`,`custom_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_chats` (`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `visit_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `chat_id` int(11) unsigned NOT NULL DEFAULT '0', `fullname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `status` tinyint(1) unsigned NOT NULL DEFAULT '0', `typing` tinyint(1) unsigned NOT NULL DEFAULT '0', `waiting` tinyint(1) unsigned NOT NULL DEFAULT '0', `area_code` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `first_active` int(10) unsigned NOT NULL DEFAULT '0', `last_active` int(10) unsigned NOT NULL DEFAULT '0',`qpenalty` int(10) unsigned NOT NULL DEFAULT '0',`request_operator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `request_group` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `question` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`customs` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`allocated` int(11) unsigned NOT NULL DEFAULT '0', `internal_active` tinyint(1) unsigned NOT NULL DEFAULT '0', `internal_closed` tinyint(1) unsigned NOT NULL DEFAULT '0', `internal_declined` tinyint(1) unsigned NOT NULL DEFAULT '0', `external_active` tinyint(1) unsigned NOT NULL DEFAULT '0', `external_close` tinyint(1) unsigned NOT NULL DEFAULT '0', `exit` int(11) unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`visitor_id`,`browser_id`,`visit_id`,`chat_id`),  KEY `chat_id` (`chat_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` (`chat_id` int(10) unsigned NOT NULL DEFAULT '0', `user_id` varchar(32) COLLATE utf8_bin NOT NULL,`declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`user_id`,`chat_id`), KEY `chat_id` (`chat_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."chat_files` ( `id` varchar(64) COLLATE utf8_bin NOT NULL, `created` int(10) unsigned NOT NULL DEFAULT '0',`file_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `file_mask` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `file_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`chat_id` int(10) unsigned NOT NULL DEFAULT '0', `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `error` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `permission` tinyint(1) NOT NULL DEFAULT '-1', `download` tinyint(1) unsigned NOT NULL DEFAULT '0',`closed` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`,`created`), KEY `visitor_id` (`visitor_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."chat_forwards` ( `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `target_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `target_group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `chat_id` int(11) unsigned NOT NULL DEFAULT '0',  `conversation` mediumtext COLLATE utf8_bin NOT NULL,  `info_text` mediumtext COLLATE utf8_bin NOT NULL,  `processed` tinyint(1) unsigned NOT NULL DEFAULT '0',`received` tinyint(1) unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`id`),  KEY `chat_id` (`chat_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."operators` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `login_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `first_active` int(10) unsigned NOT NULL DEFAULT '0', `last_active` int(10) unsigned NOT NULL DEFAULT '0', `password` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `status` tinyint(1) unsigned NOT NULL DEFAULT '0', `level` tinyint(1) unsigned NOT NULL DEFAULT '0', `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '', `typing` tinyint(1) unsigned NOT NULL DEFAULT '0', `visitor_file_sizes` mediumtext COLLATE utf8_bin NOT NULL, `last_chat_allocation` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."filters` ( `creator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `editor` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(10) unsigned NOT NULL DEFAULT '0', `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '', `expiredate` int(10) NOT NULL DEFAULT '0', `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `reason` text COLLATE utf8_bin NOT NULL, `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `active` tinyint(1) unsigned NOT NULL DEFAULT '0', `exertion` tinyint(1) unsigned NOT NULL DEFAULT '0', `languages` text COLLATE utf8_bin NOT NULL, `activeipaddress` tinyint(3) unsigned NOT NULL DEFAULT '0', `activevisitorid` tinyint(3) unsigned NOT NULL DEFAULT '0', `activelanguage` tinyint(3) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	
	// INDEXES
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."alerts` ADD INDEX `receiver_user_id` ( `receiver_user_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_actions` ADD INDEX `event_id` ( `eid` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ADD INDEX `action_id` ( `action_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` ADD INDEX `action_id` ( `action_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ADD INDEX `action_id` ( `action_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` ADD INDEX `receiver_user_id` ( `receiver_user_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ADD INDEX `ticket_id` ( `ticket_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD INDEX `ticket_id` ( `ticket_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD INDEX `chat_id` ( `chat_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD INDEX `receiver_browser_id` ( `receiver_browser_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` ADD INDEX `receiver_browser_id` ( `receiver_browser_id` );");
	
	// PRIMARY
	$commands[] = array(1068,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` ADD PRIMARY KEY ( `time` , `internal_id` , `status` );");
	
	// REFERENCES
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."alerts` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."alerts_ibfk_1` FOREIGN KEY ( `receiver_user_id` ) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_funnels` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_funnels_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_urls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_funnels` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_funnels_ibfk_2` FOREIGN KEY (`eid`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_actions` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_actions_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_action_invitations_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_action_receivers_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_action_website_pushs_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_triggers_ibfk_2` FOREIGN KEY (`receiver_user_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_goals_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_goals_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_urls` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_urls_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."stats_aggs_goals_ibfk_1` FOREIGN KEY (`goal`) REFERENCES `".@mysql_real_escape_string($_prefix)."goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_pages` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_ibfk_1` FOREIGN KEY (`url`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_data_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."ticket_editors_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_customs` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."ticket_customs_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_browsers_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`visit_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_browser_urls_ibfk_1` FOREIGN KEY (`browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_goals_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_chats_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_chat_operators_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_files` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."chat_files_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_forwards` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."chat_forwards_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."chat_requests_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."website_pushs_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	
	return processCommandList($commands,$_link);
}

function up_3200_3201($_prefix,$_link)
{
	$commands = Array();
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `question` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';";
	$allowedecs = Array(1060);
	foreach($commands as $key => $command)
	{
		$result = @mysql_query($command,$_link);
		if(!$result && mysql_errno() != $allowedecs[$key])
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $commands[$key];
	}
	return true;
}

function up_3186_3200($_prefix,$_link)
{
	$commands = Array();
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."alerts` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `invitation_auto` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `invitation`";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `invitation` `invitation_manual` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `website_push` `website_push_manual` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `website_push_auto` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `website_push_manual`";
	$commands[] = "UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `invitation_auto`=`invitation_manual`";
	$commands[] = "UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `website_push_auto`=`website_push_manual`";
 	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."rooms` TO `".@mysql_real_escape_string($_prefix)."chat_rooms`;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_rooms` ADD `creator` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT ''";
 	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."posts`  TO `".@mysql_real_escape_string($_prefix)."chat_posts`;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `chat_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `id` ";
	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."res`  TO `".@mysql_real_escape_string($_prefix)."resources`;";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `group_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `internal_id`";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."logins` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL DEFAULT '0', `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `sender_group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."events` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0', `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(10) unsigned NOT NULL DEFAULT '0', `editor` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pages_visited` int(10) unsigned NOT NULL DEFAULT '0', `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `max_trigger_amount` int(10) unsigned NOT NULL DEFAULT '0', `trigger_again_after` int(10) unsigned NOT NULL DEFAULT '0', `not_declined` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_in_chat` tinyint(1) unsigned NOT NULL DEFAULT '0', `priority` int(10) unsigned NOT NULL DEFAULT '0', `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_actions` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `type` tinyint(2) unsigned NOT NULL DEFAULT '0', `value` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`trigger_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `position` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `speed` tinyint(1) NOT NULL DEFAULT '1', `slide` tinyint(1) NOT NULL DEFAULT '1', `margin_left` int(11) NOT NULL DEFAULT '0', `margin_top` int(11) NOT NULL DEFAULT '0', `margin_right` int(11) NOT NULL DEFAULT '0', `margin_bottom` int(11) NOT NULL DEFAULT '0', `style` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `close_on_click` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `priority` tinyint(2) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `time` int(10) unsigned NOT NULL DEFAULT '0', `triggered` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_urls` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `referrer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `blacklist` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) unsigned NOT NULL DEFAULT '0', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `editable` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `area_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `group_id`;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."profiles` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(11) NOT NULL DEFAULT '0', `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `fax` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `zip` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `department` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `gender` tinyint(1) NOT NULL DEFAULT '0', `languages` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `comments` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `public` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."profile_pictures` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `internal_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time` int(11) NOT NULL DEFAULT '0', `webcam` tinyint(1) NOT NULL DEFAULT '0', `data` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$allowedecs = Array(1050,1054,1054,1054,1060,1060,1060,1050,1060,1050,1060,1050,1060,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1060,1060,1050,1050);
	foreach($commands as $key => $command)
	{
		$result = @mysql_query($command,$_link);
		if(!$result && mysql_errno() != $allowedecs[$key])
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $commands[$key];
	}
	return true;
}

function up_3183_3184($_prefix,$_link)
{
	global $INTERNAL,$GROUPS;
	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."info` ADD `chat_id` INT NOT NULL DEFAULT '11700'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();
		
	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."info` ADD `ticket_id` INT NOT NULL DEFAULT '11700'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();
		
	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `transcript_sent` tinyint(1) unsigned NOT NULL default '1'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();

	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."res` CHANGE `html` `value` longtext character set utf8 collate utf8_bin NOT NULL",$_link);
	if(!$result && mysql_errno() != 1054)
		return mysql_errno() . ": " . mysql_error();

	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."res` ADD `size` bigint(20) unsigned NOT NULL default '0'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();

	$dirs = array(PATH_UPLOADS_INTERNAL,PATH_UPLOADS_EXTERNAL);
	$baseFolderInternal = $baseFolderExternal = false;
	foreach($dirs as $tdir)
	{
		$subdirs = getDirectory($tdir,false,true);
		foreach($subdirs as $dir)
		{
			if(@is_dir($tdir.$dir."/"))
			{
				if($tdir == PATH_UPLOADS_INTERNAL)
					$owner = getInternalSystemIdByUserId($dir);
				else
					$owner = CALLER_SYSTEM_ID;
				
				if(!isset($INTERNAL[$owner]))
					continue;

				$files = getDirectory($tdir.$dir."/",false,true);
				foreach($files as $file)
				{
					if($file != FILE_INDEX && $file != FILE_INDEX_OLD)
					{
						if($tdir == PATH_UPLOADS_INTERNAL)
						{
							$parentId = $owner;
							$type = 3;
							if(!$baseFolderInternal)
							{
								createFileBaseFolders($owner,true);
								$baseFolderInternal = true;
							}
							processResource($owner,$owner,$INTERNAL[$owner]->Fullname,0,$INTERNAL[$owner]->Fullname,0,4,3);
						}
						else
						{
							$parentId = 5;
							$owner = CALLER_SYSTEM_ID;
							$type = 4;
							if(!$baseFolderExternal)
							{
								createFileBaseFolders($owner,false);
								$baseFolderExternal = true;
							}
						}
						$cfile = ($tdir != PATH_UPLOADS_INTERNAL) ? base64_decode($file) : $file;
						$size = filesize($tdir.$dir."/".$file);
						$fid = md5($file . $owner . $size);
						$filename = $owner . "_" . $fid;
						copy($tdir.$dir."/".$file,PATH_UPLOADS . $filename);
						processResource($owner,$fid,$filename,$type,$cfile,0,$parentId,4,$size);
					}
				}
			}
		}
	}
	
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."predefined` (`id` int(11) unsigned NOT NULL,`internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`group_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`lang_iso` varchar(2) character set utf8 collate utf8_bin NOT NULL,`invitation` mediumtext character set utf8 collate utf8_bin NOT NULL,`welcome` mediumtext character set utf8 collate utf8_bin NOT NULL,`website_push` mediumtext character set utf8 collate utf8_bin NOT NULL,`browser_ident` tinyint(1) unsigned NOT NULL default '0',`is_default` tinyint(1) unsigned NOT NULL default '0', `auto_welcome` tinyint(1) unsigned NOT NULL default '0',PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
	else if($result)
	{
		$counter = 0;
		foreach($GROUPS as $gid => $group)
		{
			@mysql_query("INSERT INTO `".@mysql_real_escape_string($_prefix)."predefined` (`id` ,`internal_id`, `group_id` ,`lang_iso` ,`invitation` ,`welcome` ,`website_push` ,`browser_ident` ,`is_default` ,`auto_welcome`) VALUES ('".@mysql_real_escape_string($counter++)."', '', '".@mysql_real_escape_string($gid)."', 'EN', 'Hello, my name is %name%. Do you need help? Start Live-Chat now to get assistance.', 'Hello %external_name%, my name is %name%, how may I help you?', 'Website Operator %name% would like to redirect you to this URL:\r\n\r\n%url%', '1', '1', '1');",$_link);
			@mysql_query("INSERT INTO `".@mysql_real_escape_string($_prefix)."predefined` (`id` ,`internal_id`, `group_id` ,`lang_iso` ,`invitation` ,`welcome` ,`website_push` ,`browser_ident` ,`is_default` ,`auto_welcome`) VALUES ('".@mysql_real_escape_string($counter++)."', '', '".@mysql_real_escape_string($gid)."', 'DE', '".utf8_encode("Guten Tag, meine Name ist %name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat")."', 'Guten Tag %external_name%, mein Name ist %name% wie kann ich Ihnen helfen?', '".utf8_encode("Ein Betreuer dieser Webseite (%name%) möchte Sie auf einen anderen Bereich weiterleiten:\\r\\n\\r\\n%url%")."', '1', '0', '1');",$_link);
		}
	}
	
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."rooms` (`id` int(11) NOT NULL,`time` int(11) NOT NULL,`last_active` int(11) NOT NULL,`status` tinyint(1) NOT NULL default '0',`target_group` varchar(64) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
		
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."posts` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`time` int(10) unsigned NOT NULL default '0',`micro` int(10) unsigned NOT NULL default '0',`sender` varchar(32) character set utf8 collate utf8_bin NOT NULL,`receiver` varchar(32) character set utf8 collate utf8_bin NOT NULL,`receiver_group` varchar(32) character set utf8 collate utf8_bin NOT NULL,`text` mediumtext character set utf8 collate utf8_bin NOT NULL,`received` tinyint(1) unsigned NOT NULL default '0',`persistent` tinyint(1) unsigned NOT NULL default '0', PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
		
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."tickets` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`user_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`target_group_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();

	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` (`ticket_id` int(10) unsigned NOT NULL,`internal_fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL,`status` tinyint(1) unsigned NOT NULL default '1',`time` int(10) unsigned NOT NULL,PRIMARY KEY  (`ticket_id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
			
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` (`id` int(11) unsigned NOT NULL auto_increment,`time` int(11) unsigned NOT NULL,`ticket_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`text` mediumtext character set utf8 collate utf8_bin NOT NULL,`fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL,`email` varchar(50) character set utf8 collate utf8_bin NOT NULL,`company` varchar(50) character set utf8 collate utf8_bin NOT NULL,`ip` varchar(15) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin AUTO_INCREMENT=1;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
	
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."ratings` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `time` int(11) unsigned NOT NULL, `user_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL, `email` varchar(50) character set utf8 collate utf8_bin NOT NULL, `company` varchar(50) character set utf8 collate utf8_bin NOT NULL, `qualification` tinyint(1) unsigned NOT NULL, `politeness` tinyint(1) unsigned NOT NULL, `comment` varchar(400) character set utf8 collate utf8_bin NOT NULL, `ip` varchar(15) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
	return TRUE;
}
?>