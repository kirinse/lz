<?php
/****************************************************************************************
* LiveZilla functions.external.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/

if(!defined("IN_LIVEZILLA"))
	die();

function listen($_user)
{
	global $CONFIG,$GROUPS,$INTERNAL,$USER,$INTLIST,$INTBUSY;
	$USER = $_user;
	if(!IS_FILTERED)
	{
		if($USER->Browsers[0]->ChatId==0)
			$USER->Browsers[0]->SetChatId();
		
		if($USER->Browsers[0]->Status == CHAT_STATUS_OPEN)
		{
			initData(true,false,false,false);
			if(isset($_POST[POST_EXTERN_USER_GROUP]) && empty($USER->Browsers[0]->DesiredChatGroup))
				$USER->Browsers[0]->DesiredChatGroup = AJAXDecode($_POST[POST_EXTERN_USER_GROUP]);
			$USER->Browsers[0]->SetCookieGroup();
			getInternal();
			if((count($INTLIST) + $INTBUSY) > 0)
			{
				$USER->AddFunctionCall("lz_chat_set_id('".$USER->Browsers[0]->ChatId."');",false);
				$chatPosition = getQueuePosition($USER->UserId,$USER->Browsers[0]->DesiredChatGroup);
				$chatWaitingTime = getQueueWaitingTime($chatPosition,$INTBUSY);
				login();
				$USER->Browsers[0]->SetWaiting(!($chatPosition == 1 && count($INTLIST) > 0 && !(!empty($USER->Browsers[0]->DesiredChatPartner) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Status == USER_STATUS_BUSY)));
				if(!$USER->Browsers[0]->Waiting)
				{
					$USER->AddFunctionCall("lz_chat_show_connected();",false);
					$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_ALLOCATED);",false);
					if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || !empty($USER->Browsers[0]->DesiredChatPartner))
					{
						$USER->Browsers[0]->CreateChat($INTERNAL[$USER->Browsers[0]->DesiredChatPartner],$USER);
					}
					else
					{
						foreach($INTLIST as $intid => $am)
							$USER->Browsers[0]->CreateChat($INTERNAL[$intid],$USER);
					}
				}
				else
				{
					$USER->AddFunctionCall("lz_chat_show_queue_position(".$chatPosition.",".min($chatWaitingTime,30).");",false);
				}
			}
		}
		else
		{
			activeListen();
		}
	}
	else
		displayFiltered();
	return $USER;
}

function activeListen($runs=1,$isPost=false)
{
	global $CONFIG,$GROUPS,$INTERNAL,$USER;
	$start = time();
	$USER->Browsers[0]->Typing = isset($_POST[POST_EXTERN_TYPING]);

	if(!(!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->LastActive > (time()-$CONFIG["timeout_clients"])))
		$USER->Browsers[0]->CloseChat(4);
	
	while($runs == 1)
	{
		processForward();
		if($USER->Browsers[0]->Declined)
		{
			displayDeclined();
			return $USER;
		}
		else if($USER->Browsers[0]->Closed)
		{
			displayQuit();
			return $USER;
		}
		else if($USER->Browsers[0]->Activated == CHAT_STATUS_WAITING && !(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
		{
			beginnConversation();
		}
		if($USER->Browsers[0]->Activated >= CHAT_STATUS_WAITING && !(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
		{
			refreshPicture();
			processTyping();
		}

		if($runs == 1 && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]) && !isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]) && !(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
			$USER = $USER->Browsers[0]->RequestFileUpload($USER,AJAXDecode($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]));
		else if($runs == 1 && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]) && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]))
			$USER = $USER->Browsers[0]->AbortFileUpload($USER,namebase(AJAXDecode($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME])),AJAXDecode($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]));

		if($runs++ == 1 && isset($_POST[POST_GLOBAL_SHOUT]))
		{
			processPosts();
		}
		if($USER->Browsers[0]->Activated == CHAT_STATUS_ACTIVE)
		{
			$isPost = getNewPosts();
			$USER->Browsers[0]->SetStatus(CHAT_STATUS_ACTIVE);
		}
			 
		if(isset($_POST[POST_GLOBAL_SHOUT]) || isset($_POST[POST_GLOBAL_NO_LONG_POLL]) || $isPost || (!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
		{
			break;
		}
		else if(md5($USER->Response) != AJAXDecode($_POST[POST_GLOBAL_XMLCLIP_HASH_ALL]))
		{
			$_POST[POST_GLOBAL_XMLCLIP_HASH_ALL] = md5($USER->Response);
			$USER->AddFunctionCall("lz_chat_listen_hash('". md5($USER->Response) . "','".getId(5)."');",false);
			break;
		}
		else
		{
			$USER->Response = "";
			break;
		}
	}
}

function processForward()
{
	global $USER,$CONFIG;
	if(!empty($USER->Browsers[0]->Forward) && !empty($USER->Browsers[0]->Forward->TargetGroupId) && !$USER->Browsers[0]->Forward->Processed)
	{
		$USER->AddFunctionCall("lz_chat_initiate_forwarding('".base64_encode($USER->Browsers[0]->Forward->TargetGroupId)."');",false);
		$USER->Browsers[0]->Forward->Save(true);
		$USER->Browsers[0]->ExternalClose();
		$USER->Browsers[0]->DesiredChatGroup = $USER->Browsers[0]->Forward->TargetGroupId;
		$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->Forward->TargetSessId;
		$USER->Browsers[0]->FirstActive=time();
		$USER->Browsers[0]->Save(true);
		$USER->Browsers[0]->SetCookieGroup();
	}
}

function getNewPosts()
{
	global $USER;
	$isPost = false;
	foreach($USER->Browsers[0]->GetPosts() as $post)
		if($USER->Browsers[0]->DesiredChatPartner == $post->Sender)
		{
			$USER->AddFunctionCall($post->GetCommand(),false);
			$isPost = true;
		}
	return $isPost;
}

function processPosts($counter=0)
{
	global $USER,$STATS;
	while(isset($_POST["p_p" . $counter]))
	{
		if(STATS_ACTIVE)
			$STATS->ProcessAction(ST_ACTION_EXTERNAL_POST);
		$id = md5($USER->Browsers[0]->SystemId . AJAXDecode($_POST[POST_EXTERN_CHAT_ID]) . AJAXDecode($_POST["p_i" . $counter]));
		$post = new Post($id,$USER->Browsers[0]->SystemId,$USER->Browsers[0]->InternalUser->SystemId,AJAXDecode($_POST["p_p" . $counter]),time(),$USER->Browsers[0]->ChatId);

		if(isset($_POST["p_pt" . $counter]))
		{
			$post->Translation = AJAXDecode($_POST["p_pt" . $counter]);
			$post->TranslationISO = AJAXDecode($_POST["p_ptiso" . $counter]);
		}
		
		$post->Save();
		$USER->AddFunctionCall("lz_chat_release_post('".AJAXDecode($_POST["p_i" . $counter])."');",false);
		$counter++;
	}
	
	$counter=0;
	while(isset($_POST["pr_i" . $counter]))
	{
		markPostReceived(AJAXDecode($_POST["pr_i" . $counter]));
		$USER->AddFunctionCall("lz_chat_message_set_received('".AJAXDecode($_POST["pr_i" . $counter])."');",false);
		$counter++;
	}
}

function login()
{
	global $INTERNAL,$USER, $CONFIG;
	if(empty($_POST[POST_EXTERN_USER_NAME]) && !isnull(getCookieValue("form_111")))
		$USER->Browsers[0]->Fullname = cutString(getCookieValue("form_111"),254);
	else
		$USER->Browsers[0]->Fullname = cutString(AJAXDecode($_POST[POST_EXTERN_USER_NAME]),254);

	if(empty($_POST[POST_EXTERN_USER_EMAIL]) && !isnull(getCookieValue("form_112")))
		$USER->Browsers[0]->Email = cutString(getCookieValue("form_112"),254);
	else
		$USER->Browsers[0]->Email = cutString(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),254);
		
	if(empty($_POST[POST_EXTERN_USER_COMPANY]) && !isnull(getCookieValue("form_113")))
		$USER->Browsers[0]->Company = cutString(getCookieValue("form_113"),254);
	else
		$USER->Browsers[0]->Company = cutString(AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]),254);
		
	$USER->Browsers[0]->Question = cutString(AJAXDecode($_POST[POST_EXTERN_USER_QUESTION]),254);
	$USER->Browsers[0]->SaveLoginData();

	for($i=0;$i<=9;$i++)
		if(isset($_POST["p_cf".$i]) && !empty($_POST["p_cf".$i]) && isset($CONFIG["gl_ci_list"][$i]))
		{
			$USER->Browsers[0]->Customs[$i] = AJAXDecode($_POST["p_cf".$i]);
			setCookieValue("cf_".$i,$USER->Browsers[0]->Customs[$i]);
		}

	if(isset($_POST[POST_EXTERN_USER_NAME]) && !empty($_POST[POST_EXTERN_USER_NAME]))
		setCookieValue("form_111",$USER->Browsers[0]->Fullname);
	if(isset($_POST[POST_EXTERN_USER_EMAIL]) && !empty($_POST[POST_EXTERN_USER_EMAIL]))
		setCookieValue("form_112",$USER->Browsers[0]->Email);
	if(isset($_POST[POST_EXTERN_USER_COMPANY]) && !empty($_POST[POST_EXTERN_USER_COMPANY]))
		setCookieValue("form_113",$USER->Browsers[0]->Company);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_INIT);",false);
}

function replaceLoginDetails($_html)
{
	global $CONFIG;
	$data = (isset($_GET[GET_EXTERN_USER_EMAIL]) && !empty($_GET[GET_EXTERN_USER_EMAIL])) ? base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL]) : getCookieValue("form_112");
	$_html = str_replace("<!--login_value_112-->",htmlentities($data,ENT_QUOTES,"UTF-8"),$_html);
	$data = (isset($_GET[GET_EXTERN_USER_NAME]) && !empty($_GET[GET_EXTERN_USER_NAME])) ? base64UrlDecode($_GET[GET_EXTERN_USER_NAME]) : getCookieValue("form_111");
	$_html = str_replace("<!--login_value_111-->",htmlentities($data,ENT_QUOTES,"UTF-8"),$_html);
	$data = (isset($_GET[GET_EXTERN_USER_COMPANY]) && !empty($_GET[GET_EXTERN_USER_COMPANY])) ? base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY]) : getCookieValue("form_113");
	$_html = str_replace("<!--login_value_113-->",htmlentities($data,ENT_QUOTES,"UTF-8"),$_html);
	$_html = str_replace("<!--login_value_114-->",(!isset($_GET[GET_EXTERN_USER_QUESTION])) ? (!isset($_POST["form_114"])) ? "" : $_POST["form_114"] : base64UrlDecode($_GET[GET_EXTERN_USER_QUESTION]),$_html);
	$_html = str_replace("<!--login_value_customs-->",getJSCustomArray(),$_html);
	$customFields = getCustomArray();
	for($i=0;$i<=9;$i++)
		$_html = str_replace("<!--login_value_".$i."-->",htmlentities($customFields[$i],ENT_QUOTES,"UTF-8"),$_html);
	return $_html;
}

function getChatLoginInputs($_html)
{
	global $CONFIG;
	$inputshtml = "";
	$inputsareahtml = "";
	$inputtpl = getFile(TEMPLATE_LOGIN_INPUT);
	$areatpl = str_replace("<!--maxlength-->",254,getFile(TEMPLATE_LOGIN_AREA));
	$custom_inputs = $CONFIG["gl_ci_list"];
	foreach($custom_inputs as $index => $caption)
	{
		$area = false;
		if($index == 114)
		{
			$area = true;
			$input = $areatpl;
		}
		else
			$input = $inputtpl;
		
		$input = str_replace("<!--name-->",$index,$input);
		$input = str_replace("<!--caption-->",$caption,$input);
		if(!$area)
			$inputshtml .= $input;
		else
			$inputsareahtml .= $input;
	}
	return str_replace("<!--chat_login_inputs-->",$inputshtml . $inputsareahtml,$_html);
}

function getTicketInputs($_html)
{
	global $CONFIG;
	$inputshtml = "";
	$inputsareahtml = "";
	$inputtpl = getFile(TEMPLATE_LOGIN_INPUT);
	$areatpl = str_replace("<!--maxlength-->",16777216,getFile(TEMPLATE_LOGIN_AREA));
	
	$CONFIG["gl_ti_list"] = $CONFIG["gl_ci_list"];
	$custom_inputs = $CONFIG["gl_ti_list"];
	foreach($custom_inputs as $index => $caption)
	{
		$area = false;
		if($index == 114)
		{
			$area = true;
			$input = $areatpl;
		}
		else
			$input = $inputtpl;
			
		$input = str_replace("<!--name-->",$index,$input);
		$input = str_replace("<!--caption-->",$caption,$input);
		if(!$area)
			$inputshtml .= $input;
		else
			$inputsareahtml .= $input;
	}
	return str_replace("<!--ticket_inputs-->",$inputshtml . $inputsareahtml,$_html);
}

function refreshPicture()
{
	global $CONFIG,$USER;
	$USER->Browsers[0]->InternalUser->LoadPictures();
	
	if(!empty($USER->Browsers[0]->InternalUser->WebcamPicture))
		$edited = $USER->Browsers[0]->InternalUser->WebcamPictureTime;
	else if(!empty($USER->Browsers[0]->InternalUser->ProfilePicture))
		$edited = $USER->Browsers[0]->InternalUser->ProfilePictureTime;
	else
		$edited = 0;
		
	$USER->AddFunctionCall("lz_chat_set_intern_image(".$edited.",'" . $USER->Browsers[0]->InternalUser->GetOperatorPictureFile() . "',false);",false);
	$USER->AddFunctionCall("lz_chat_set_config(".$CONFIG["timeout_clients"].",".$CONFIG["poll_frequency_clients"].");",false);
}

function processTyping()
{
	global $CONFIG,$USER,$GROUPS;
	$USER->Browsers[0]->InternalUser->LoadProfile();
	$groupname = addslashes($GROUPS[$USER->Browsers[0]->DesiredChatGroup]->Description);
	$USER->AddFunctionCall("lz_chat_set_intern(\"".base64_encode($USER->Browsers[0]->InternalUser->UserId)."\",\"".base64_encode(addslashes($USER->Browsers[0]->InternalUser->Fullname))."\",\"". base64_encode($groupname)."\",\"".strtolower($USER->Browsers[0]->InternalUser->Language)."\",".parseBool($USER->Browsers[0]->InternalUser->Typing).",".parseBool(!empty($USER->Browsers[0]->InternalUser->Profile) && $USER->Browsers[0]->InternalUser->Profile->Public).");",false);
}

function beginnConversation()
{
	global $USER,$CONFIG;
	$USER->Browsers[0]->ExternalActivate();
	if(!empty($CONFIG["gl_save_op"]))
		setCookieValue("internal_user",$USER->Browsers[0]->InternalUser->UserId);
	$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->InternalUser->SystemId;
	
	$USER->AddFunctionCall("lz_chat_add_system_text(1,'".base64_encode($USER->Browsers[0]->InternalUser->Fullname)."');",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_ACTIVE);",false);
	$USER->AddFunctionCall("lz_chat_shout(1);",false);
}

function displayFiltered()
{
	global $FILTERS,$USER;
	$USER->Browsers[0]->CloseChat(0);
	$USER->AddFunctionCall("lz_chat_set_intern('','','','',false,false);",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(2,'".base64_encode("&nbsp;<b>".$FILTERS->Filters[ACTIVE_FILTER_ID]->Reason."</b>")."');",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function displayQuit()
{
	global $GROUPS,$USER;
	$USER->Browsers[0]->CloseChat(1);
	$USER->AddFunctionCall("lz_chat_set_intern('','','','',false,false);",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(3,null);",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function displayDeclined()
{
	global $GROUPS,$USER;
	$USER->Browsers[0]->CloseChat(2);
	$USER->AddFunctionCall("lz_chat_set_intern('','','','',false,false);",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(4,null);",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function buildLoginErrorField($error="",$addition = "")
{
	global $FILTERS,$LZLANG,$CONFIG;
	if(!getAvailability())
		return $LZLANG["client_error_deactivated"];
		
	if(!DB_CONNECTION || !empty($CONFIG["gl_stmo"]))
		return $LZLANG["client_error_unavailable"];

	if(IS_FILTERED)
	{
		$error = $LZLANG["client_error_unavailable"];
		if(isset($FILTERS->Message) && strlen($FILTERS->Message) > 0)
			$addition = "<br><br>" . $FILTERS->Message;
	}
	return $error . $addition;
}

function reloadGroups($_user)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	initData(true,false,false,true);
	$groupbuilder = new GroupBuilder($INTERNAL,$GROUPS,$CONFIG);
	$groupbuilder->Generate();
	
	if(isset($_POST[POST_EXTERN_REQUESTED_INTERNID]) && !empty($_POST[POST_EXTERN_REQUESTED_INTERNID]))
		$_user->Browsers[0]->DesiredChatPartner = getInternalSystemIdByUserId(AJAXDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));

	$_user->AddFunctionCall("top.lz_chat_set_groups(\"" . $groupbuilder->Result . "\" ,". $groupbuilder->ErrorHTML .");",false);
	$_user->AddFunctionCall("lz_chat_release(".parseBool(($groupbuilder->GroupAvailable || (isset($_POST[GET_EXTERN_RESET]) && strlen($groupbuilder->ErrorHTML) <= 2))).",".$groupbuilder->ErrorHTML.");",false);
	return $_user;
}

function getInternal($desired = "",$util = 0,$fromCookie = null)
{
	global $CONFIG,$INTERNAL,$GROUPS,$USER,$INTLIST,$INTBUSY;
	$INTLIST = array();
	$INTBUSY = 0;
	$backup_target = null;
	$fromDepartment = $fromDepartmentBusy = false;
	
	if(!empty($USER->Browsers[0]->DesiredChatPartner) && isset($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Status < USER_STATUS_OFFLINE)
	{
		if(!(!empty($USER->Browsers[0]->DesiredChatGroup) && !in_array($USER->Browsers[0]->DesiredChatGroup,$INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Groups)))
			$desired = $USER->Browsers[0]->DesiredChatPartner;
	}
	else
	{
		$USER->Browsers[0]->DesiredChatPartner = null;
		if(isset($_POST[POST_EXTERN_REQUESTED_INTERNID]) && !empty($_POST[POST_EXTERN_REQUESTED_INTERNID]))
			$desired = getInternalSystemIdByUserId(AJAXDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));
		else if(!isnull(getCookieValue("internal_user")) && !empty($CONFIG["gl_save_op"]))
		{
			$desired = getInternalSystemIdByUserId(getCookieValue("internal_user"));
			if(!(!empty($USER->Browsers[0]->DesiredChatGroup) && !in_array($USER->Browsers[0]->DesiredChatGroup,$INTERNAL[$desired]->Groups)))
			{
				$fromCookie = $desired;
			}
			else
				$desired = "";
		}
	}
	foreach($GROUPS as $id => $group)
		$utilization[$id] = 0;
	foreach($INTERNAL as $sessId => $internal)
	{
		if($internal->LastActive > (time()-$CONFIG["timeout_clients"]))
		{
			$group_chats[$sessId] = $internal->GetExternalChatAmount();
			$group_names[$sessId] = $internal->Fullname;
			$group_available[$sessId] = GROUP_STATUS_UNAVAILABLE;

			if(in_array($USER->Browsers[0]->DesiredChatGroup,$internal->Groups))
			{
				if($internal->Status == USER_STATUS_ONLINE && $internal->LastChatAllocation < (time()-($CONFIG["poll_frequency_clients"]*3)))
					$group_available[$sessId] = GROUP_STATUS_AVAILABLE;
				elseif($internal->Status== USER_STATUS_BUSY || $internal->LastChatAllocation >= (time()-($CONFIG["poll_frequency_clients"]*3)))
				{
					$group_available[$sessId] = GROUP_STATUS_BUSY;
					$INTBUSY++;
					
					if(empty($fromCookie) && $desired == $sessId)
						return;
				}
			}
			else
			{
				if($internal->Status == USER_STATUS_ONLINE)
					$backup_target = $internal;
				else if($internal->Status == USER_STATUS_BUSY && empty($backup_target))
					$backup_target = $internal;
					
				if(!empty($USER->Browsers[0]->DesiredChatPartner) && $USER->Browsers[0]->DesiredChatPartner == $sessId)
					$USER->Browsers[0]->DesiredChatPartner = null;
			}
			for($count=0;$count<count($internal->Groups);$count++)
			{
				if($USER->Browsers[0]->DesiredChatGroup == $internal->Groups[$count])
				{
					if(!is_array($utilization[$internal->Groups[$count]]))
						$utilization[$internal->Groups[$count]] = Array();
					if($group_available[$sessId] == GROUP_STATUS_AVAILABLE)
						$utilization[$internal->Groups[$count]][$sessId] = $group_chats[$sessId];
					
				}
			}
		}
	}
	
	if(isset($utilization[$USER->Browsers[0]->DesiredChatGroup]) && is_array($utilization[$USER->Browsers[0]->DesiredChatGroup]))
	{
		arsort($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		reset($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		$util = end($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		$INTLIST = $utilization[$USER->Browsers[0]->DesiredChatGroup];
	}
	
	if(isset($group_available) && is_array($group_available) && in_array(GROUP_STATUS_AVAILABLE,$group_available))
		$fromDepartment = true;
	elseif(isset($group_available) && is_array($group_available) && in_array(GROUP_STATUS_BUSY,$group_available))
		$fromDepartmentBusy = true;

	if(isset($group_chats) && is_array($group_chats) && isset($fromDepartment) && $fromDepartment)
		foreach($group_chats as $sessId => $amount)
		{
			if(($group_available[$sessId] == GROUP_STATUS_AVAILABLE  && $amount <= $util) || ((!empty($USER->Browsers[0]->Forward) && $USER->Browsers[0]->Forward->Processed) && isset($desired) && $sessId == $desired))
				$available_internals[] = $sessId;
		}

	if($fromDepartment && sizeof($available_internals) > 0)
	{
		if(is_array($available_internals))
		{
			if(!empty($desired) && (in_array($desired,$available_internals) || $INTERNAL[$desired]->Status == USER_STATUS_ONLINE))
				$matching_internal = $desired;
			else
			{
				if(!isnull($inv_sender = $USER->Browsers[0]->GetLastInvitationSender()) && in_array($inv_sender,$available_internals))
				{
					$matching_internal = $inv_sender;
				}
				else
				{
					$matching_internal = array_rand($available_internals,1);
					$matching_internal = $available_internals[$matching_internal];
				}
			}
		}
		if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || $fromCookie == $matching_internal)
			$USER->Browsers[0]->DesiredChatPartner = $matching_internal;
	}
	elseif($fromDepartmentBusy)
	{	
		if(!$USER->Browsers[0]->Waiting)
			$USER->Browsers[0]->Waiting = true;
	}
	else
	{
		$USER->AddFunctionCall("lz_chat_add_system_text(8,null);",false);
		$USER->AddFunctionCall("lz_chat_stop_system();",false);
		$USER->Browsers[0]->CloseChat(3);
		$INTLIST = null;
	}
}

function getSessionId()
{
	global $CONFIG;
	if(!isnull(getCookieValue("userid")))
		$session = getCookieValue("userid");
	else
		setCookieValue("userid",$session = getId(USER_ID_LENGTH));
	return $session;
}

function getQueueWaitingTime($_position,$_intamount,$min=1)
{
	global $CONFIG;
	if($_intamount == 0)
		$_intamount++;
		
	$result = queryDB(true,"SELECT AVG(`endtime`-`time`) AS `waitingtime` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `endtime`>0 AND `endtime`>`time`;");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		if(!empty($row["waitingtime"]))
			$min = ($row["waitingtime"]/60)/$_intamount;
		else
			$min = $min/$_intamount;
		$minb = $min;
		for($i = 1;$i < $_position; $i++)
		{
			$minb *= 0.9;
			$min += $minb;
		}
		$min /= $CONFIG["gl_sim_ch"];
		$min -= (time() - CHAT_START_TIME) / 60;
		if($min <= 0)
			$min = 1;
	}
	return ceil($min);
}

function getQueuePosition($_creatorId,$_targetGroup,$_startTime=0,$_position = 1)
{
	global $CONFIG,$USER;
	$USER->Browsers[0]->SetStatus(CHAT_STATUS_OPEN);
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `qpenalty`=`qpenalty`+60 WHERE `last_active`>".(time()-$CONFIG["timeout_clients"])." AND `status`=0 AND `exit`=0 AND `last_active`<" . @mysql_real_escape_string(time()-max(20,($CONFIG["poll_frequency_clients"]*2))));
	$result = queryDB(true,"SELECT `request_operator`,`request_group`,`chat_id`,`first_active`,`qpenalty`+`first_active` as `sfirst` FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `status`='0' AND `exit`='0' AND `chat_id`>0 AND `last_active`>".(time()-$CONFIG["timeout_clients"])." ORDER BY `sfirst` ASC;");
	if($result)
	{
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if($row["chat_id"] == $USER->Browsers[0]->ChatId)
			{
				$_startTime = $row["sfirst"];
				break;
			}
			else if($row["request_group"]==$_targetGroup && $row["request_operator"]==$USER->Browsers[0]->DesiredChatPartner)
			{
				$_position++;
			}
			else if($row["request_group"]==$_targetGroup && ($row["request_operator"]!=$USER->Browsers[0]->DesiredChatPartner && empty($row["request_operator"])))
			{
				$_position++;
			}
			else if(!empty($USER->Browsers[0]->DesiredChatPartner) && $USER->Browsers[0]->DesiredChatPartner==$row["request_operator"])
			{
				$_position++;
			}
		}
	}
	define("CHAT_START_TIME",$_startTime);
	return $_position;
}

function isRatingFlood()
{
	$result = queryDB(true,"SELECT count(id) as rating_count FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE time>".@mysql_real_escape_string(time()-86400)." AND ip='".@mysql_real_escape_string(getIP())."';");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		return ($row["rating_count"] >= MAX_RATES_PER_DAY);
	}
	else
		return true;
}

function saveRating($_rating)
{
	$time = time();
	while(true)
	{
		queryDB(true,"SELECT time FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE time=".@mysql_real_escape_string($time).";");
		if(@mysql_affected_rows() > 0)
			$time++;
		else
			break;
	}
	queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_RATINGS."` (`id` ,`time` ,`user_id` ,`internal_id` ,`fullname` ,`email` ,`company` ,`qualification` ,`politeness` ,`comment` ,`ip`) VALUES ('".@mysql_real_escape_string($_rating->Id)."', ".@mysql_real_escape_string($time)." , '".@mysql_real_escape_string($_rating->UserId)."', '".@mysql_real_escape_string($_rating->InternId)."', '".@mysql_real_escape_string($_rating->Fullname)."', '".@mysql_real_escape_string($_rating->Email)."', '".@mysql_real_escape_string($_rating->Company)."', '".@mysql_real_escape_string($_rating->RateQualification)."', '".@mysql_real_escape_string($_rating->RatePoliteness)."', '".@mysql_real_escape_string($_rating->RateComment)."', '".@mysql_real_escape_string(getIP())."');");
}

function isTicketFlood()
{
	$result = queryDB(true,"SELECT count(id) as ticket_count FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE time>".@mysql_real_escape_string(time()-86400)." AND ip='".@mysql_real_escape_string(getIP())."';");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		return ($row["ticket_count"] > MAX_MAIL_PER_DAY);
	}
	else
		return true;
}

function getTicketId()
{
	$result = queryDB(true,"SELECT `ticket_id` FROM `".DB_PREFIX.DATABASE_INFO."`");
	$row = mysql_fetch_array($result, MYSQL_BOTH);
	$tid = $row["ticket_id"]+1;
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INFO."` SET ticket_id='".@mysql_real_escape_string($tid)."' WHERE ticket_id='".@mysql_real_escape_string($row["ticket_id"])."'");
	return $tid;
}
?>
