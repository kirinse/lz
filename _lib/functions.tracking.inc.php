<?php
/****************************************************************************************
* LiveZilla functions.tracking.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
function abortTracking($_code)
{
	global $EXTERNALUSER;
	exit("lz_tracking_stop_tracking(".$_code.");");
}

function triggerEvents($chatRequest=false,$alert=false,$websitePush=false)
{
	global $BROWSER,$CONFIG,$EVENTS,$INTERNAL,$EXTERNALUSER,$STATS,$GROUPS;
	
	if(count($EVENTS)==0)
		return;

	$actionData = "";
	$url = $BROWSER->History[count($BROWSER->History)-1];
	$previous = (count($BROWSER->History) > 1) ? $BROWSER->History[count($BROWSER->History)-2]->Url->GetAbsoluteUrl() : "";
	$EXTERNALUSER->GetChatRequestResponses();
	
	foreach($EVENTS->Events as $event)
	{
		$match = false;
		if(!$event->IsActive || empty($url))
			continue;

		$urlor = (count($event->FunnelUrls) == 0 && $event->MatchesURLCriterias($url->Url->GetAbsoluteUrl(),$url->Referrer->GetAbsoluteUrl(),$previous,time()-($url->Entrance)));
		$urlfunnel = (count($event->FunnelUrls) > 0 && $event->MatchesURLFunnelCriterias($BROWSER->History));
		$global = $event->MatchesGlobalCriterias(count($BROWSER->History),($EXTERNALUSER->ExitTime-$EXTERNALUSER->FirstActive),$EXTERNALUSER->HasAcceptedChatRequest,$EXTERNALUSER->HasDeclinedChatRequest,($EXTERNALUSER->IsInChat() || $EXTERNALUSER->WasInChat()),$BROWSER->Query);

		if($global && ($urlfunnel || $urlor))
		{
			foreach(array($event->Goals,$event->Actions) as $elements)
				foreach($elements as $action)
				{
					$EventTrigger = new EventTrigger(CALLER_USER_ID,CALLER_BROWSER_ID,$action->Id,time(),1);
					$EventTrigger->Load();
					$aexists = $action->Exists(CALLER_USER_ID,CALLER_BROWSER_ID);
					if(!$EventTrigger->Exists || ($EventTrigger->Exists && $event->MatchesTriggerCriterias($EventTrigger)))
					{
						if(!$aexists)
						{
							$EventTrigger->Save($event->Id);
							if($action->Type < 2)
							{
								foreach($action->GetInternalReceivers() as $user_id)
								{
									$intaction = new EventActionInternal($user_id, $EventTrigger->Id);
									$intaction->Save();
								}
							}
							else if($action->Type == 2 && !defined("EVENT_INVITATION"))
							{
								$sender = getActionSender($action->Invitation->Senders,true);
								initData(false,true);
								if(!empty($sender) && $GROUPS[$sender->GroupId]->IsOpeningHour())
								{
									define("EVENT_INVITATION",true);
									$chatrequest = new ChatRequest($sender->UserSystemId,$sender->GroupId,CALLER_USER_ID,CALLER_BROWSER_ID,getActionText($sender,$action));
									$chatrequest->EventActionId = $action->Id;
									$chatrequest->Save();
									$BROWSER->LoadChatRequest();
									$chatRequest = true;
								}
							}
							else if($action->Type == 3 && !defined("EVENT_ALERT"))
							{
								define("EVENT_ALERT",true);
								$alert = new Alert(CALLER_USER_ID,CALLER_BROWSER_ID,$action->Value);
								$alert->EventActionId = $action->Id;
								$alert->Save();
								$BROWSER->LoadAlerts();
							}
							else if($action->Type == 4 && !defined("EVENT_WEBSITE_PUSH"))
							{
								define("EVENT_WEBSITE_PUSH",true);
								$sender = getActionSender($action->WebsitePush->Senders,false);
								$websitepush = new WebsitePush($sender->UserSystemId,$sender->GroupId,CALLER_USER_ID,CALLER_BROWSER_ID,getActionText($sender,$action),$action->WebsitePush->Ask,$action->WebsitePush->TargetURL);
								$websitepush->EventActionId = $action->Id;
								$websitepush->Save();
								$BROWSER->LoadWebsitePush();
							}
							else if($action->Type == 5 && STATS_ACTIVE)
							{
								$STATS->ProcessAction(ST_ACTION_GOAL,array(CALLER_USER_ID,$action->Id,($EXTERNALUSER->Visits==1)?1:0));
							}
						}
					}
					if($EventTrigger->Exists && $aexists)
						$EventTrigger->Update();
				}
		}
	}
	return $actionData;
}

function getActionSender($_senders,$_checkOnline,$hpriority=0)
{
	global $CONFIG,$INTERNAL;
	initData(true);
	foreach($_senders as $sender)
		if(isset($INTERNAL[$sender->UserSystemId]) && (!$_checkOnline || (($INTERNAL[$sender->UserSystemId]->LastActive > (time()-$CONFIG["timeout_clients"])) && $INTERNAL[$sender->UserSystemId]->Status == USER_STATUS_ONLINE)))
			if($hpriority <= $sender->Priority)
			{
				$hpriority = $sender->Priority;
				$asenders[] = $sender;
			}
			else
				break;
	if(!isset($asenders))
		return null;
	else
		return $asenders[array_rand($asenders,1)];
}

function getActionText($_sender,$_action)
{
	global $EXTERNALUSER,$INTERNAL;
	if(!empty($_action->Value))
		return $_action->Value;

	$sel_message = null;
	$group = new UserGroup($_sender->GroupId);
	foreach(array_merge($INTERNAL[$_sender->UserSystemId]->PredefinedMessages,$group->PredefinedMessages) as $message)
	{
		if(($message->IsDefault && (!$message->BrowserIdentification || empty($EXTERNALUSER->Language))) || ($message->BrowserIdentification && $EXTERNALUSER->Language == $message->LangISO))
		{
			$sel_message = $message;
			break;
		}
		else if($message->IsDefault && empty($_action->Value))
		{
			$sel_message = $message;
		}
	}
	
	if($_action->Type == 2 && $sel_message != null)
		$_action->Value = $sel_message->InvitationAuto;
	else if($_action->Type == 4)
	{
		$_action->Value = $sel_message->WebsitePushAuto;
		$_action->Value = str_replace("%url%",$_action->WebsitePush->TargetURL,$_action->Value);
	}

	$_action->Value = str_replace("%name%",$INTERNAL[$_sender->UserSystemId]->Fullname,$_action->Value);
	$_action->Value = str_replace("%email%",$INTERNAL[$_sender->UserSystemId]->Email,$_action->Value);
	$_action->Value = str_replace("%external_ip%",$_SERVER["REMOTE_ADDR"],$_action->Value);
	return $_action->Value;
}

function processActions($actionData = "")
{
	global $BROWSER,$CONFIG,$INTERNAL,$EVENTS,$GROUPS;
	if(!empty($BROWSER->ChatRequest))
	{
		initData(true,true);
		if(($INTERNAL[$BROWSER->ChatRequest->SenderSystemId]->LastActive < (time()-$CONFIG["timeout_clients"])) || $INTERNAL[$BROWSER->ChatRequest->SenderSystemId]->Status != USER_STATUS_ONLINE || !$GROUPS[$BROWSER->ChatRequest->SenderGroupId]->IsOpeningHour() || $BROWSER->ChatRequest->Closed)
		{
			$BROWSER->ChatRequest->SetStatus(false,false,true,true);
			$actionData .= "lz_tracking_close_request();";
			$BROWSER->ForceUpdate();
		}
		else if(isset($_GET[GET_TRACK_REQUEST_DECLINED]) || isset($_GET[GET_TRACK_REQUEST_ACCEPTED]))
		{
			if(isset($_GET[GET_TRACK_REQUEST_DECLINED]))
				$BROWSER->ChatRequest->SetStatus(false,false,true,true);

			if(isset($_GET[GET_TRACK_REQUEST_CLOSE]))
			{
				if(isset($_GET[GET_TRACK_REQUEST_ACCEPTED]))
					$BROWSER->ChatRequest->SetStatus(false,true,false,true);
				$actionData .= "lz_tracking_close_request();";
			}
			$BROWSER->ForceUpdate();
		}
		else if(!$BROWSER->ChatRequest->Accepted && !$BROWSER->ChatRequest->Declined)
		{
			if(empty($BROWSER->ChatRequest->EventActionId))
			{
				$invitationHTML = doReplacements($BROWSER->ChatRequest->CreateInvitationTemplate($CONFIG["gl_inv_template"],$CONFIG["gl_site_name"],$CONFIG["wcl_window_width"],$CONFIG["wcl_window_height"],LIVEZILLA_URL,$INTERNAL[$BROWSER->ChatRequest->SenderSystemId],$CONFIG["gl_inv_coc"]));
				$BROWSER->ChatRequest->Invitation = new Invitation($invitationHTML,$CONFIG["gl_inv_position"],$CONFIG["gl_inv_margin"][0],$CONFIG["gl_inv_margin"][1],$CONFIG["gl_inv_margin"][2],$CONFIG["gl_inv_margin"][3],$CONFIG["gl_inv_scroll_speed"],$CONFIG["gl_inv_template"],empty($CONFIG["gl_inv_no_scroll"]),$BROWSER->ChatRequest->Text,$CONFIG["gl_inv_coc"]);
			}
			else if(!isnull($action = $EVENTS->GetActionById($BROWSER->ChatRequest->EventActionId)))
			{
				$invitationHTML = doReplacements($BROWSER->ChatRequest->CreateInvitationTemplate($action->Invitation->Style,$CONFIG["gl_site_name"],$CONFIG["wcl_window_width"],$CONFIG["wcl_window_height"],LIVEZILLA_URL,$INTERNAL[$BROWSER->ChatRequest->SenderSystemId],$action->Invitation->CloseOnClick));
				$BROWSER->ChatRequest->Invitation = $action->Invitation;
				$BROWSER->ChatRequest->Invitation->Text = $BROWSER->ChatRequest->Text;
				$BROWSER->ChatRequest->Invitation->HTML = $invitationHTML;
			}
			$BROWSER->ChatRequest->SetStatus(true,false,false,false);
			$actionData .= $BROWSER->ChatRequest->Invitation->GetCommand();
			$BROWSER->ForceUpdate();
		}
	}
	if(!empty($BROWSER->WebsitePush))
	{
		if(isset($_GET[GET_TRACK_WEBSITE_PUSH_DECLINED]))
		{
			$BROWSER->WebsitePush->SetStatus(false,false,true);
		}
		else if(isset($_GET[GET_TRACK_WEBSITE_PUSH_ACCEPTED]) || (!$BROWSER->WebsitePush->Ask && !$BROWSER->WebsitePush->Displayed))
		{
			$BROWSER->WebsitePush->SetStatus(false,true,false);
			$actionData .= $BROWSER->WebsitePush->GetExecCommand();
		}
		else if($BROWSER->WebsitePush->Ask && !$BROWSER->WebsitePush->Accepted && !$BROWSER->WebsitePush->Declined)
		{
			$BROWSER->WebsitePush->SetStatus(true,false,false);
			$actionData .= $BROWSER->WebsitePush->GetInitCommand();
		}
	}
	if(!empty($BROWSER->Alert) && !$BROWSER->Alert->Accepted)
	{
		if(isset($_GET[GET_TRACK_ALERT_CONFIRMED]))
			$BROWSER->Alert->SetStatus(false,true);
		else
			$actionData .= $BROWSER->Alert->GetCommand();
	}
	return $actionData;
}

function getPollFrequency()
{
	global $CONFIG,$INTERNAL;
	if(!empty($CONFIG["gl_stmo"]))
		return $CONFIG["poll_frequency_tracking"];
	$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(*) AS `ocount` FROM `".DB_PREFIX.DATABASE_OPERATORS ."` WHERE `last_active` > ".(time()-$CONFIG["timeout_clients"])), MYSQL_BOTH);
	if($row["ocount"] > 0)
		return $CONFIG["poll_frequency_tracking"];
	return $CONFIG["timeout_track"]-10;
}
?>