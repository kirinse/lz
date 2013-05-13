<?php
/****************************************************************************************
* LiveZilla functions.internal.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function processAcceptedConversations()
{
	if(isset($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_va"]))
		appendAcceptedConversations();
}

function processUpdateReport()
{
	$count = 0;
	while(isset($_POST[POST_INTERN_PROCESS_UPDATE_REPORT . "_va_" . $count]))
	{
		$parts = explode("_",$_POST[POST_INTERN_PROCESS_UPDATE_REPORT . "_va_" . $count]);
		if($parts[1]==0)
			$report = new StatisticYear($parts[0],0,0);
		else if($parts[2]==0)
			$report = new StatisticMonth($parts[0],$parts[1],0);
		else
			$report = new StatisticDay($parts[0],$parts[1],$parts[2]);
		$report->Update(!empty($_POST[POST_INTERN_PROCESS_UPDATE_REPORT . "_vb_" . $count]));
		$count++;
	}
}

function processAuthentications()
{
	if(isset($_POST[POST_INTERN_PROCESS_AUTHENTICATIONS . "_va"]))
		appendAuthentications();
}

function processStatus()
{
	global $INTERNAL;
	if(isset($_POST[POST_INTERN_USER_STATUS]))
		appendStatus();
}

function processAlerts()
{
	if(isset($_POST[POST_INTERN_PROCESS_ALERTS . "_va"]))
	{
		$alerts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_ALERTS . "_va"]));
		$visitors = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ALERTS . "_vb"]);
		$browsers = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ALERTS . "_vc"]);
		foreach($alerts as $key => $text)
		{
			$alert = new Alert($visitors[$key],$browsers[$key],$alerts[$key]);
			$alert->Save();
		}
	}
}

function processEvents()
{
	if(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_va_0"]))
		appendEvents();
}

function processClosures()
{
	global $INTERNAL,$CONFIG;
	if(isset($_POST[POST_INTERN_PROCESS_CLOSURES . "_va"]))
	{
		$users = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_va"]));
		$types = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_vb"]));
		$browsers = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_vc"]));
		$ids = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_vd"]));
		foreach($users as $key => $userid)
		{
			$chat = new VisitorChat($userid,$browsers[$key]);
			$chat->Load();
			$chat->ChatId = $ids[$key];
			if($types[$key] == CHAT_CLOSED)
				$chat->InternalClose();
			else if($types[$key] == CHAT_DECLINED)
				$chat->InternalDecline(CALLER_SYSTEM_ID);
		}
	}
}

function processPosts()
{
	global $INTERNAL,$GROUPS,$VISITOR,$CONFIG,$STATS;
	$time = time();
	$count = -1;
	while(isset($_POST[POST_INTERN_PROCESS_POSTS . "_va" . ++$count]))
	{
		$post = slashesStrip($_POST[POST_INTERN_PROCESS_POSTS . "_va" . $count]);
		if($_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count] == GROUP_EVERYONE_INTERN || isset($GROUPS[$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count]]))
		{
			foreach($INTERNAL as $internal)
				if($internal->SystemId != CALLER_SYSTEM_ID)
					if($_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count] == GROUP_EVERYONE_INTERN || in_array($_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count],$internal->Groups))
						if($internal->Status != USER_STATUS_OFFLINE || !empty($CONFIG["gl_ogcm"]))
						{
							$npost = new Post(getId(32),CALLER_SYSTEM_ID,$internal->SystemId,$post,$time,"");
							$npost->Translation = $_POST[POST_INTERN_PROCESS_POSTS . "_vd" . $count];
							$npost->TranslationISO = $_POST[POST_INTERN_PROCESS_POSTS . "_ve" . $count];
							$npost->Persistent = true;
							if($_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count] == GROUP_EVERYONE_INTERN || in_array($_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count],$INTERNAL[CALLER_SYSTEM_ID]->Groups))
								$npost->ReceiverGroup = $_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count];
							$npost->Save();
						}
		}
		else if($_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count] == GROUP_EVERYONE_EXTERN)
		{
			foreach($INTERNAL[CALLER_SYSTEM_ID]->ExternalChats as $chat)
			{
				$npost = new Post(getId(32),CALLER_SYSTEM_ID,$chat->SystemId,$post,$time,"");
				$npost->Translation = $_POST[POST_INTERN_PROCESS_POSTS . "_vd" . $count];
				$npost->TranslationISO = $_POST[POST_INTERN_PROCESS_POSTS . "_ve" . $count];
				$npost->Save();
			}
		}
		else
		{
			if(!isset($INTERNAL[$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count]]))
			{
				if(STATS_ACTIVE)
					$STATS->ProcessAction(ST_ACTION_INTERNAL_POST);
				if(isset($INTERNAL[CALLER_SYSTEM_ID]->ExternalChats[$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count]]))
					$npost = new Post($_POST[POST_INTERN_PROCESS_POSTS . "_vc" . $count],CALLER_SYSTEM_ID,$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count],$post,$time,$INTERNAL[CALLER_SYSTEM_ID]->ExternalChats[$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count]]->ChatId);
				else
					continue;
			}
			else
			{
				$npost = new Post($_POST[POST_INTERN_PROCESS_POSTS . "_vc" . $count],CALLER_SYSTEM_ID,$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count],$post,$time,"");
			}
			$npost->Persistent = isset($INTERNAL[$_POST[POST_INTERN_PROCESS_POSTS . "_vb" . $count]]);
			$npost->Translation = $_POST[POST_INTERN_PROCESS_POSTS . "_vd" . $count];
			$npost->TranslationISO = $_POST[POST_INTERN_PROCESS_POSTS . "_ve" . $count];
			$npost->Save();
		}
	}
}

function processForwards($count=0)
{
	global $INTERNAL,$STATS;
	while(isset($_POST[POST_INTERN_PROCESS_FORWARDS . "_va_".$count]))
	{
		if(STATS_ACTIVE)
			$STATS->ProcessAction(ST_ACTION_FORWARDED_CHAT);
		$forward = new Forward($_POST[POST_INTERN_PROCESS_FORWARDS . "_va_".$count],$INTERNAL[CALLER_SYSTEM_ID]->SystemId);
		$forward->ReceiverUserId = $_POST[POST_INTERN_PROCESS_FORWARDS . "_vf_".$count];
		$forward->ReceiverBrowserId = $_POST[POST_INTERN_PROCESS_FORWARDS . "_vg_".$count];
		$forward->TargetSessId = $_POST[POST_INTERN_PROCESS_FORWARDS . "_vb_".$count];
		$forward->TargetGroupId = $_POST[POST_INTERN_PROCESS_FORWARDS . "_ve_".$count];
		
		if(strlen($_POST[POST_INTERN_PROCESS_FORWARDS . "_vc_".$count]) > 0)
			$forward->Text = $_POST[POST_INTERN_PROCESS_FORWARDS . "_vc_".$count];
		if(strlen($_POST[POST_INTERN_PROCESS_FORWARDS . "_vd_".$count]) > 0)
			$forward->Conversation = $_POST[POST_INTERN_PROCESS_FORWARDS . "_vd_".$count];
		$forward->Save();
		$count++;
	}
}

function processRequests()
{
	if(isset($_POST[POST_INTERN_PROCESS_REQUESTS . "_va"]))
		appendChatRequests();
}

function processWebsitePushs()
{
	if(isset($_POST[POST_INTERN_PROCESS_GUIDES . "_va"]))
		appendWebsitePushs();
}

function processFilters()
{
	if(isset($_POST[POST_INTERN_PROCESS_FILTERS . "_va"]))
		appendFilters();
}

function processProfile()
{
	if(isset($_POST[POST_INTERN_PROCESS_PROFILE . "_va"]))
		appendProfile();
}

function processProfilePictures()
{
	if(isset($_POST[POST_INTERN_PROCESS_PICTURES]))
		appendProfilePictures();
}

function processWebcamPictures()
{
	if(isset($_POST[POST_INTERN_PROCESS_PICTURES_WEBCAM]))
		appendWebcamPictures();
}

function processBannerPictures()
{
	if(isset($_POST[POST_INTERN_PROCESS_BANNERS]))
		appendBannerPictures();
}

function processPermissions()
{
	if(isset($_POST[POST_INTERN_PROCESS_PERMISSIONS . "_va"]))
		appendPermissions();
}

function processExternalReloads()
{
	if(isset($_POST[POST_INTERN_PROCESS_EXTERNAL_RELOADS]))
		appendExternalReloads();
}

function processClosedTickets()
{
	if(isset($_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_va"]))
		appendClosedTickets();
}

function processResources()
{
	if(isset($_POST[POST_INTERN_PROCESS_RESOURCES]))
		appendResources();
}

function processReceivedPosts()
{
	if(isset($_POST[POST_INTERN_PROCESS_RECEIVED_POSTS]))
		appendReceivedPosts();
}

function processCancelInvitation()
{
	if(isset($_POST[POST_INTERN_PROCESS_CANCEL_INVITATION]))
	{
		$users = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CANCEL_INVITATION]));
		foreach($users as $uid)
		{
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `closed`=1,`declined`=1 WHERE `receiver_user_id`='".@mysql_real_escape_string($uid)."';");
		}
	}
}

function processGoals($count = 0)
{
	global $RESPONSE;
	if(isset($_POST[POST_INTERN_PROCESS_GOALS . "_va_" .$count]))
	{
		$goallinks = array();
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_GOALS."`"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$goallinks[] = array($row["event_id"],$row["goal_id"]);
	
		queryDB(true,"TRUNCATE TABLE `".DB_PREFIX.DATABASE_GOALS."`;");
		while(isset($_POST[POST_INTERN_PROCESS_GOALS . "_va_" .$count]))
		{
			if($_POST[POST_INTERN_PROCESS_GOALS . "_vb_" .$count] != "-1")
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_GOALS."` (`id`, `title`, `description`, `conversion`, `ind`) VALUES ('". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_GOALS . "_vb_" .$count])."', '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_GOALS . "_vd_" .$count])."', '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_GOALS . "_vc_" .$count])."', '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_GOALS . "_ve_" .$count])."','". @mysql_real_escape_string($count)."');");
			$count++;
		}
		foreach($goallinks as $lpair)
			queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_GOALS."` (`event_id`, `goal_id`) VALUES ('". @mysql_real_escape_string($lpair[0])."', '". @mysql_real_escape_string($lpair[1])."');");
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function processArchiveChats($xml="",$count = 0,$postcount=0,$details="")
{
	global $RESPONSE,$INTERNAL,$CONFIG,$VISITOR;
	while(isset($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count]))
	{
		$etpl = getFile(TEMPLATE_EMAIL_TRANSCRIPT);
		$etpl = str_replace("<!--chat_id-->",$_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count],$etpl);
		$etpl = str_replace("<!--website_name-->",$CONFIG["gl_site_name"],$etpl);
		
		if(!empty($_POST[POST_INTERN_PROCESS_CHATS . "_vg_" .$count]))
			$details .= "Name: " . $_POST[POST_INTERN_PROCESS_CHATS . "_vg_" .$count] . "\r\n";
		if(!empty($_POST[POST_INTERN_PROCESS_CHATS . "_vh_" .$count]))
			$details .= "Email: " . $_POST[POST_INTERN_PROCESS_CHATS . "_vh_" .$count] . "\r\n";
		if(!empty($_POST[POST_INTERN_PROCESS_CHATS . "_vi_" .$count]))
			$details .= "Company: " . $_POST[POST_INTERN_PROCESS_CHATS . "_vi_" .$count] . "\r\n";
		if(!empty($_POST[POST_INTERN_PROCESS_CHATS . "_vo_" .$count]))
			$details .= "Question: " . $_POST[POST_INTERN_PROCESS_CHATS . "_vo_" .$count] . "\r\n";

		$etpl = str_replace("<!--details-->",$details,$etpl);
		
		$entries = array();
		$result_posts = queryDB(true,"SELECT `text`,`sender`,`time`,`micro`,`translation` FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE (`sender`='". @mysql_real_escape_string(CALLER_SYSTEM_ID)."' OR `sender` LIKE '%".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vf_" .$count])."%') AND `chat_id` = '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count])."' ORDER BY `time` ASC, `micro` ASC;");
		while($row_post = mysql_fetch_array($result_posts, MYSQL_BOTH))
		{
			$postcount++;
			$post = (empty($row_post["translation"])) ? html_entity_decode(strip_tags($row_post["text"]),ENT_COMPAT,"UTF-8") : html_entity_decode(strip_tags($row_post["translation"]),ENT_COMPAT,"UTF-8")." (".html_entity_decode(strip_tags($row_post["text"]),ENT_COMPAT,"UTF-8").")";
			$sender = (CALLER_SYSTEM_ID==$row_post["sender"]) ? $INTERNAL[CALLER_SYSTEM_ID]->Fullname : $_POST[POST_INTERN_PROCESS_CHATS . "_vg_" .$count];
			$entries[$row_post["time"]."apost".$row_post["micro"]] = "| " . date("d.m.Y H:i:s",$row_post["time"]) . " | " . $sender .  ": " . $post;
		}
		
		$result_files = queryDB(true,"SELECT `created`,`file_name`,`permission` FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` WHERE `chat_id` = '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count])."' ORDER BY `created` ASC;");
		while($row_file = mysql_fetch_array($result_files, MYSQL_BOTH))
		{
			$postcount++;
			$result = " / " . (($row_file["permission"]==PERMISSION_VOID)?"NOT ACCEPTED":($row_file["permission"]==PERMISSION_NONE)?"DECLINED":"ACCEPTED") . ")";
			$entries[$row_file["created"]."bfile"] = "| " . date("d.m.Y H:i:s",$row_file["created"]) . " | " . $_POST[POST_INTERN_PROCESS_CHATS . "_vg_" .$count] .  ": FILE UPLOAD REQUEST (" . html_entity_decode(strip_tags($row_file["file_name"]),ENT_COMPAT,"UTF-8") . $result;
		}
		$result_forwards = queryDB(true,"SELECT `target_operator_id`,`created` FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `chat_id` = '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count])."' ORDER BY `created` ASC;");
		while($row_forward = mysql_fetch_array($result_forwards, MYSQL_BOTH))
			$entries[$row_forward["created"]."zforward"] = "| " . date("d.m.Y H:i:s",$row_forward["created"]) . " | FORWARDING TO " . $INTERNAL[$row_forward["target_operator_id"]]->Fullname . " ...";
		
		$plainText = "";
		ksort($entries);
		foreach($entries as $row)
		{
			if(!empty($plainText))
				$plainText .= "\r\n";
			$plainText .= $row;
		}
		if(!isnull(trim($plainText)))
		{
			$etpl = str_replace("<!--date-->",date("r",time()),$etpl);
			$etpl = str_replace("<!--chat-->",$plainText,$etpl);
		}
		else
			$etpl = "";
			
		$result_time = queryDB(true,"SELECT `exit`,`allocated` FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `chat_id` = '". @mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count])."' LIMIT 1;");
		$row_time = mysql_fetch_array($result_time, MYSQL_BOTH);
		$endtime = (!empty($row_time["exit"])) ? $row_time["exit"] : time();
		$allocated = (!empty($row_time["allocated"])) ? ",`time`='".@mysql_real_escape_string($row_time["allocated"])."'" : ",`time`='".@mysql_real_escape_string($endtime)."'";
		
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `external_id`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vf_" .$count])."',`endtime`='".@mysql_real_escape_string($endtime)."'".$allocated.",`closed`='".@mysql_real_escape_string(time())."',`fullname`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vg_" .$count])."',`internal_id`='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."',`group_id`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vm_" .$count])."',`html`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vb_" .$count])."',`plain`='".@mysql_real_escape_string($etpl)."',`email`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vh_" .$count])."',`company`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vi_" .$count])."',`host`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vl_" .$count])."',`ip`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vk_" .$count])."',`gzip`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vd_" .$count])."',`transcript_sent`='".@mysql_real_escape_string((((empty($CONFIG["gl_soct"]) && empty($CONFIG["gl_scct"])) || empty($etpl) || $postcount==0) ? "1" : "0"))."',`question`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_vo_" .$count])."' WHERE `chat_id`='".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count])."' LIMIT 1;");
		$xml .= "<c cid=\"".base64_encode($_POST[POST_INTERN_PROCESS_CHATS . "_va_" .$count])."\" te=\"".base64_encode($_POST[POST_INTERN_PROCESS_CHATS . "_vc_" .$count])."\" />\r\n";
		$count++;
	}
	$RESPONSE->SetStandardResponse(1,$xml);
}

function appendResources($xml="")
{
	global $RESPONSE;
	$rids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_va"]);
	$html = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_RESOURCES . "_vb"]));
	$type = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vc"]);
	$title = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_RESOURCES . "_vd"]));
	$disc = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_ve"]);
	$parent = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vf"]);
	$rank = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vg"]);
	$size = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vh"]);
	foreach($rids as $key => $id)
	{
		processResource(CALLER_SYSTEM_ID,$rids[$key],base64_decode($html[$key]),$type[$key],base64_decode($title[$key]),$disc[$key],$parent[$key],$rank[$key],$size[$key]);
		$xml .= "<r rid=\"".base64_encode($rids[$key])."\" disc=\"".base64_encode($disc[$key])."\" />\r\n";
	}
	$RESPONSE->SetStandardResponse(1,$xml);
}

function appendClosedTickets()
{
	global $INTERNAL;
	$msgnames = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_va"]);
	$msgids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_vb"]);
	$msgstatus = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_vc"]);
	foreach($msgids as $key => $id)
	{
		$ticket = new ClosedTicket($id);
		$ticket->Sender = $msgnames[$key];
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_TICKET_EDITORS."` SET `internal_fullname`='".@mysql_real_escape_string($ticket->Sender)."',`status`='".@mysql_real_escape_string($msgstatus[$key])."',`time`='".@mysql_real_escape_string(time())."' WHERE `ticket_id`='".@mysql_real_escape_string($ticket->Id)."';");
		if(@mysql_affected_rows() <= 0)
			queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_TICKET_EDITORS."` (`ticket_id` ,`internal_fullname` ,`status`,`time`) VALUES ('".@mysql_real_escape_string($ticket->Id)."', '".@mysql_real_escape_string($ticket->Sender)."', '".@mysql_real_escape_string($msgstatus[$key])."', '".@mysql_real_escape_string(time())."');");
	}
}

function appendReceivedPosts()
{
	global $INTERNAL;
	$pids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RECEIVED_POSTS]);
	foreach($pids as $id)
		markPostReceived($id);
}

function appendExternalReloads()
{
	global $INTERNAL;
	$INTERNAL[CALLER_SYSTEM_ID]->ExternalReloads = Array();
	$userids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_EXTERNAL_RELOADS]);
	foreach($userids as $id)
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload[$id] = true;
}

function appendPermissions()
{
	$ids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_PERMISSIONS . "_va"]);
	$results = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_PERMISSIONS . "_vb"]);

	foreach($ids as $key => $id)
	{
		$fur = new FileUploadRequest($ids[$key],CALLER_SYSTEM_ID);
		$fur->Permission = $results[$key];
		$fur->Save();
	}
}

function appendAuthentications()
{
	global $INTERNAL,$RESPONSE;
	$users = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_AUTHENTICATIONS . "_va"]);
	$passwords = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_AUTHENTICATIONS . "_vb"]);
	foreach($users as $key => $user)
	{
		$INTERNAL[$user]->ChangePassword($passwords[$key]);
		$RESPONSE->Authentications = "<val userid=\"".base64_encode($user)."\" pass=\"".base64_encode($passwords[$key])."\" />\r\n";
	}
}

function appendWebsitePushs()
{
	global $INTERNAL;
	$guides = Array();
	
	$visitors = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_GUIDES . "_va"]);
	$asks = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_GUIDES . "_vb"]);
	$urls = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_GUIDES . "_vc"]));
	$browids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_GUIDES . "_vd"]);
	$texts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_GUIDES . "_ve"]));
	$groups = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_GUIDES . "_vf"]));
	
	foreach($visitors as $key => $visitor)
	{
		$guide = new WebsitePush(CALLER_SYSTEM_ID,$groups[$key],$visitors[$key],$browids[$key],$texts[$key],$asks[$key],$urls[$key]);
		$guide->Save();
	}
}

function appendEvents()
{
	global $VISITOR;
	$count = 0;
	while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_va_" . $count]))
	{
		$event = new Event($_POST[POST_INTERN_PROCESS_EVENTS . "_va_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vb_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vc_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vd_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_ve_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vf_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vg_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vh_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vk_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vl_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vm_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vn_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vo_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vp_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vq_" . $count],$_POST[POST_INTERN_PROCESS_EVENTS . "_vs_" . $count]);
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_EVENTS."` WHERE `id`='".@mysql_real_escape_string($event->Id)."' LIMIT 1;");

		if(!isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vx_" . $count]))
		{
			queryDB(true,$event->GetSQL());
			$counturl = 0;
			while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_a_" .$counturl]))
			{
				$eventURL = new EventURL($_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_f_" .$counturl],$event->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_a_" .$counturl],$_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_b_" .$counturl],$_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_c_" .$counturl],$_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_d_" .$counturl]);
				queryDB(true,$eventURL->GetSQL());

				if(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_e_" .$counturl]))
					queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_FUNNELS."` (`eid`,`uid`,`ind`) VALUES ('".@mysql_real_escape_string($event->Id)."','".@mysql_real_escape_string($eventURL->Id)."','".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_EVENTS . "_vi_" . $count . "_e_" .$counturl])."');");
				
				$counturl++;
			}
			
			$countgoals = 0;
			while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vs_" . $count . "_a_" .$countgoals]))
			{
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_GOALS."` (`event_id`,`goal_id`) VALUES ('".@mysql_real_escape_string($event->Id)."','".@mysql_real_escape_string($_POST[POST_INTERN_PROCESS_EVENTS . "_vs_" . $count . "_a_" .$countgoals])."');");
				$countgoals++;
			}
			
			$countaction = 0;
			while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_a_" .$countaction]))
			{
				$eventAction = new EventAction($event->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_a_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_b_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_c_" .$countaction]);
				queryDB(true,$eventAction->GetSQL());
				if($eventAction->Type == 2 && isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_a_" .$countaction]))
				{
					$eventActionInvitation = new Invitation($eventAction->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_a_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_b_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_c_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_d_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_e_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_f_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_g_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_h_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_i_" .$countaction]);
					queryDB(true,$eventActionInvitation->GetSQL());
					
					$countsender = 0;
					while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_i_a_" .$countaction . "_" . $countsender]))
					{
						$eventActionInvitationSender = new EventActionSender($eventActionInvitation->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_i_a_" .$countaction . "_" . $countsender],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_i_b_" .$countaction . "_" . $countsender],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_inv_i_c_" .$countaction . "_" . $countsender]);
						$eventActionInvitationSender->SaveSender();
						$countsender++;
					}
				}
				else if($eventAction->Type == 4 && isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_a_" .$countaction]))
				{
					$eventActionWebsitePush = new WebsitePush($eventAction->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_a_" .$countaction],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_b_" .$countaction]);
					$eventActionWebsitePush->SaveEventConfiguration();
					
					$countsender = 0;
					while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_c_a_" .$countaction . "_" . $countsender]))
					{
						$eventActionWebsitePushSender = new EventActionSender($eventActionWebsitePush->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_c_a_" .$countaction . "_" . $countsender],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_c_b_" .$countaction . "_" . $countsender],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_wp_c_c_" .$countaction . "_" . $countsender]);
						$eventActionWebsitePushSender->SaveSender();
						$countsender++;
					}
				}
				else if($eventAction->Type < 2)
				{
					$countreceiver = 0;
					while(isset($_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_d_" .$countaction . "_" . $countreceiver]))
					{
						$eventActionReceiver = new EventActionReceiver($eventAction->Id,$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_d_" .$countaction . "_" . $countreceiver],$_POST[POST_INTERN_PROCESS_EVENTS . "_vj_" . $count . "_e_" .$countaction. "_" . $countreceiver]);
						queryDB(true,$eventActionReceiver->GetSQL());
						$countreceiver++;
					}
				}
				$countaction++;
			}
		}
		$count++;
	}
}

function appendChatRequests()
{
	global $INTERNAL,$VISITOR;
	$visitors = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_REQUESTS . "_va"]);
	$browids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_REQUESTS . "_vb"]);
	$reqnames = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_REQUESTS . "_vc"]));
	$reqids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_REQUESTS . "_vd"]);
	$reqtexts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_REQUESTS . "_ve"]));
	$sendergroup = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_REQUESTS . "_vf"]));
	foreach($reqids as $key => $requestid)
		if(isset($VISITOR[$visitors[$key]]))
		{
			$skip = false;
			foreach($VISITOR[$visitors[$key]]->Browsers as $browser)
			{
				$browser->LoadChatRequest();
				if(!empty($browser->ChatRequest) && !$browser->ChatRequest->Closed)
				{
					$skip = true;
					continue;
				}
			}
			if($skip)
				continue;
			$request = new ChatRequest(CALLER_SYSTEM_ID,$sendergroup[$key],$visitors[$key],$browids[$key],$reqtexts[$key]);
			$request->Save();
		}
}

function appendFilters()
{
	$creators = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_va"]);
	$createds = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vb"]);
	$editors = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FILTERS . "_vc"]));
	$ips = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vd"]);
	$expiredates = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_ve"]);
	$userids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vf"]);
	$filternames = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vg"]);
	$reasons = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FILTERS . "_vh"]));
	$filterids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vi"]);
	$activestates = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vj"]);
	$actiontypes = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vk"]);
	$exertions = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vl"]);
	$languages = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vm"]);
	$activeuserids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vn"]);
	$activeipaddresses = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vo"]);
	$activelanguages = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vp"]);
	
	foreach($filterids as $key => $id)
	{
		$filter = new Filter($filterids[$key]);
		$filter->Creator = $creators[$key];
		$filter->Created = ($createds[$key] != "0") ? $createds[$key] : time();
		$filter->Editor = $editors[$key];
		$filter->Edited = time();
		$filter->IP = $ips[$key];
		$filter->Expiredate = $expiredates[$key];
		$filter->Userid = $userids[$key];
		$filter->Reason = $reasons[$key];
		$filter->Filtername = $filternames[$key];
		$filter->Activestate = $activestates[$key];
		$filter->Exertion = $exertions[$key];
		$filter->Languages = $languages[$key];
		$filter->Activeipaddress = $activeipaddresses[$key];
		$filter->Activeuserid = $activeuserids[$key];
		$filter->Activelanguage = $activelanguages[$key];
		
		if($actiontypes[$key] == POST_ACTION_ADD || $actiontypes[$key] == POST_ACTION_EDIT)
			$filter->Save();
		else if($actiontypes[$key] == POST_ACTION_REMOVE)
			$filter->Destroy();
	}
}

function appendStatus()
{
	global $INTERNAL,$CONFIG;
	if(!LOGIN)
		$INTERNAL[CALLER_SYSTEM_ID]->Status = $_POST[POST_INTERN_USER_STATUS];
	else
		$INTERNAL[CALLER_SYSTEM_ID]->Status = USER_STATUS_OFFLINE;
}

function appendProfilePictures()
{
	$pictures = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_PICTURES]));
	foreach($pictures as $key => $item)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `webcam`='0' AND `internal_id`='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."' LIMIT 1;");
		if(!empty($item))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` (`id` ,`internal_id`,`time` ,`webcam` ,`data`) VALUES ('".@mysql_real_escape_string(getId(32))."','".@mysql_real_escape_string(CALLER_SYSTEM_ID)."','".@mysql_real_escape_string(time())."',0,'".@mysql_real_escape_string($item)."');");
	}
}

function appendWebcamPictures()
{
	$pictures = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_PICTURES_WEBCAM]));
	foreach($pictures as $key => $item)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `webcam`='1' AND `internal_id`='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."' LIMIT 1;");
		if(!empty($item))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` (`id` ,`internal_id`,`time` ,`webcam` ,`data`) VALUES ('".@mysql_real_escape_string(getId(32))."','".@mysql_real_escape_string(CALLER_SYSTEM_ID)."','".@mysql_real_escape_string(time())."',1,'".@mysql_real_escape_string($item)."');");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` SET `time`='".@mysql_real_escape_string(time())."' WHERE `webcam`='0' AND `internal_id`='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."' LIMIT 1;");
	}
}

function appendBannerPictures()
{
	if(strpos($_POST[POST_INTERN_PROCESS_BANNERS . "_vb"],"..") === false && strpos($_POST[POST_INTERN_PROCESS_BANNERS . "_vd"],"..") === false)
	{
		$fexonline = substr(strtolower($_POST[POST_INTERN_PROCESS_BANNERS . "_vb"]),strlen($_POST[POST_INTERN_PROCESS_BANNERS . "_vb"])-4,4);
		$fexoffline = substr(strtolower($_POST[POST_INTERN_PROCESS_BANNERS . "_vd"]),strlen($_POST[POST_INTERN_PROCESS_BANNERS . "_vd"])-4,4);
		
		if($fexonline == ".png" || $fexonline == ".gif")
		{
			$file = PATH_BANNER . substr($_POST[POST_INTERN_PROCESS_BANNERS . "_vb"],0,strlen($_POST[POST_INTERN_PROCESS_BANNERS . "_vb"])-4);
			@unlink($file . ".png");
			@unlink($file . ".gif");
			base64ToFile($file . $fexonline,$_POST[POST_INTERN_PROCESS_BANNERS . "_va"]);
		}
		if($fexoffline == ".png" || $fexoffline == ".gif")
		{
			$file = PATH_BANNER . substr($_POST[POST_INTERN_PROCESS_BANNERS . "_vd"],0,strlen($_POST[POST_INTERN_PROCESS_BANNERS . "_vd"])-4);
			@unlink($file . ".png");
			@unlink($file . ".gif");
			base64ToFile($file . $fexoffline,$_POST[POST_INTERN_PROCESS_BANNERS . "_vc"]);
		}
	}
}

function appendProfile()
{
	queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PROFILES."` WHERE `id`='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."';");
	$profile = new Profile($_POST[POST_INTERN_PROCESS_PROFILE . "_va"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vb"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vc"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vd"],$_POST[POST_INTERN_PROCESS_PROFILE . "_ve"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vf"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vg"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vh"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vi"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vj"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vk"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vl"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vm"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vn"],$_POST[POST_INTERN_PROCESS_PROFILE . "_vo"]);
	$profile->Save(CALLER_SYSTEM_ID);
}

function appendAcceptedConversations()
{
	$users = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_va"]));
	$browsers = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_vb"]));
	$ids = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_vc"]));
	foreach($users as $key => $userid)
	{
		if(strlen($browsers[$key]) > 0 && strlen($userid) > 0)
		{
			$chat = new VisitorChat($userid,$browsers[$key]);
			$chat->ChatId = $ids[$key];
			$chat->InternalActivate();
		}
	}
}
?>