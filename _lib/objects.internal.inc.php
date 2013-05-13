<?php
/****************************************************************************************
* LiveZilla objects.internal.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class InternalXMLBuilder
{
	public $Caller;
	public $InternalUsers;
	public $InternalGroups;

	public $XMLProfilePictures = "";
	public $XMLWebcamPictures = "";
	public $XMLProfiles = "";
	public $XMLInternal = "";
	public $XMLTyping = "";
	public $XMLGroups = "";
	
	function InternalXMLBuilder($_caller,$_internalusers,$_internalgroups)
	{
		$this->Caller = $_caller;
		$this->InternalUsers = $_internalusers;
		$this->InternalGroups = $_internalgroups;
	}
	
	function Generate()
	{
		if(SERVERSETUP && defined("HideChatGroupSelection") && HideChatGroupSelection)
			$this->XMLGroups .= "<hcs>".base64_encode(1)."</hcs>\r\n";
		if(SERVERSETUP && defined("HideTicketGroupSelection") && HideTicketGroupSelection)
			$this->XMLGroups .= "<hts>".base64_encode(1)."</hts>\r\n";
			
		foreach($this->InternalGroups as $groupId => $group)
		{
			$this->XMLGroups .= "<v id=\"".base64_encode($group->Id)."\" desc=\"".base64_encode($group->DescriptionArray)."\" created=\"".base64_encode($group->Created)."\"  email=\"".base64_encode($group->Email)."\"  external=\"".base64_encode($group->IsExternal)."\"  internal=\"".base64_encode($group->IsInternal)."\" standard=\"".base64_encode($group->IsStandard)."\" vf=\"".base64_encode($group->VisitorFilters)."\">\r\n";
			foreach($group->PredefinedMessages as $premes)
				$this->XMLGroups .= $premes->GetXML();
				
			foreach($group->OpeningHours as $hour)
				$this->XMLGroups .= "<oh open=\"".base64_encode($hour[1])."\" close=\"".base64_encode($hour[2])."\">".base64_encode($hour[0])."</oh>\r\n";

			if(SERVERSETUP)
			{
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_sm")."\">".base64_encode($group->ChatFunctions[0])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_so")."\">".base64_encode($group->ChatFunctions[1])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_pr")."\">".base64_encode($group->ChatFunctions[2])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_ra")."\">".base64_encode($group->ChatFunctions[3])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_fv")."\">".base64_encode($group->ChatFunctions[4])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_fu")."\">".base64_encode($group->ChatFunctions[5])."</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ci_hidden")."\">\r\n";
				foreach($group->ChatInputsHidden as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ti_hidden")."\">\r\n";
				foreach($group->TicketInputsHidden as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ci_mandatory")."\">\r\n";
				foreach($group->ChatInputsMandatory as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ti_mandatory")."\">\r\n";
				foreach($group->TicketInputsMandatory as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
			}
			$this->XMLGroups .= "</v>\r\n";
		}
		foreach($this->InternalUsers as $sysId => $internaluser)
		{
			$b64sysId = base64_encode($sysId);
			$sessiontime = $this->Caller->LastActive;
			$this->InternalUsers[$sysId]->LoadPictures();
			
			if($sysId != CALLER_SYSTEM_ID && !empty($this->InternalUsers[$sysId]->WebcamPicture))
			{	
				if($_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_PROFILE] == XML_CLIP_NULL || $this->InternalUsers[$sysId]->WebcamPictureTime >= $sessiontime)
					$this->XMLWebcamPictures .= "<v os=\"".$b64sysId."\" content=\"".$this->InternalUsers[$sysId]->WebcamPicture."\" />\r\n";
			}
			else
				$this->XMLWebcamPictures .= "<v os=\"".$b64sysId."\" content=\"".base64_encode("")."\" />\r\n";
				
			if(!empty($this->InternalUsers[$sysId]->ProfilePicture))
			{	
				if(@$_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_PROFILE] == XML_CLIP_NULL || $this->InternalUsers[$sysId]->ProfilePictureTime >= $sessiontime)
					$this->XMLProfilePictures .= "<v os=\"".$b64sysId."\" content=\"".$this->InternalUsers[$sysId]->ProfilePicture."\" />\r\n";
			}
			else
				$this->XMLProfilePictures .= "<v os=\"".$b64sysId."\" content=\"".base64_encode("")."\" />\r\n";
			
			$CPONL = ($this->InternalUsers[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN) ? " cponl=\"".base64_encode(($internaluser->IsPasswordChangeNeeded()) ? 1 : 0)."\"" : "";
			$PASSWORD = (SERVERSETUP) ? " pass=\"".base64_encode($this->InternalUsers[$sysId]->LoadPassword())."\"" : "";
			$this->XMLInternal .= "<v status=\"".base64_encode($this->InternalUsers[$sysId]->Status)."\" id=\"".$b64sysId."\" userid=\"".base64_encode($this->InternalUsers[$sysId]->UserId)."\" lang=\"".base64_encode($this->InternalUsers[$sysId]->Language)."\" email=\"".base64_encode($this->InternalUsers[$sysId]->Email)."\" websp=\"".base64_encode($this->InternalUsers[$sysId]->Webspace)."\" name=\"".base64_encode($this->InternalUsers[$sysId]->Fullname)."\" desc=\"".base64_encode($this->InternalUsers[$sysId]->Description)."\" groups=\"".base64_encode($this->InternalUsers[$sysId]->GroupsArray)."\" perms=\"".base64_encode($this->InternalUsers[$sysId]->PermissionSet)."\" ip=\"".base64_encode($this->InternalUsers[$sysId]->IP)."\" lipr=\"".base64_encode($this->InternalUsers[$sysId]->LoginIPRange)."\" aac=\"".base64_encode($this->InternalUsers[$sysId]->CanAutoAcceptChats)."\" level=\"".base64_encode($this->InternalUsers[$sysId]->Level)."\" ".$CPONL." ".$PASSWORD.">\r\n";
			foreach($internaluser->PredefinedMessages as $premes)
				$this->XMLInternal .= $premes->GetXML();
			$this->XMLInternal .= "</v>\r\n";
			
			if($sysId!=$this->Caller->SystemId && $this->InternalUsers[$sysId]->Status != USER_STATUS_OFFLINE)
				$this->XMLTyping .= "<v id=\"".$b64sysId."\" tp=\"".base64_encode((($this->InternalUsers[$sysId]->Typing)?1:0))."\" />\r\n";
			
			$internaluser->LoadProfile();
			if($internaluser->Profile != null)
				if((isset($_POST[POST_INTERN_XMLCLIP_HASH_PROFILES]) && $_POST[POST_INTERN_XMLCLIP_HASH_PROFILES] == XML_CLIP_NULL) || $internaluser->Profile->LastEdited >= $sessiontime)
					$this->XMLProfiles .= $internaluser->Profile->GetXML($internaluser->SystemId);
				else
					$this->XMLProfiles .= "<p os=\"".$b64sysId."\"/>\r\n";
		}
	}
}

class ExternalXMLBuilder
{
	public $CurrentStatics = array();
	public $ActiveBrowsers = array();
	public $AddedVisitors = array();
	public $SessionFileSizes = array();
	public $StaticReload = array();
	public $DiscardedObjects = array();
	public $IsDiscardedObject = false;
	public $ObjectCounter = 0;
	public $CurrentUser;
	public $CurrentFilesize;
	public $CurrentResponseType = DATA_RESPONSE_TYPE_KEEP_ALIVE;
	
	public $XMLVisitorOpen = false;
	public $XMLCurrentChat = "";
	public $XMLCurrentAliveBrowsers = "";
	public $XMLCurrentVisitor = "";
	public $XMLCurrentVisitorTag = "";
	public $XMLCurrent = "";
	public $XMLTyping = "";
	
	public $Caller;
	public $ExternUsers;
	public $GetAll;
	public $IsExternal;

	function ExternalXMLBuilder($_caller,$_visitors,$_getall,$_external)
	{
		$this->Caller = $_caller;
		$this->Visitors = $_visitors;
		$this->GetAll = $_getall;
		$this->IsExternal = $_external;
	}
	
	function SetDiscardedObject()
	{
		foreach($this->SessionFileSizes as $sfs_userid => $sfs_browsers)
			if(isset($this->Visitors[$sfs_userid]))
			{
				foreach($sfs_browsers as $sfs_bid => $sfs_browser)
				{
					if(!isset($this->Visitors[$sfs_userid]->Browsers[$sfs_bid]))
					{
						if(!isset($this->DiscardedObjects[$sfs_userid]))
							$this->DiscardedObjects[$sfs_userid] = array($sfs_bid);
						else if($this->DiscardedObjects[$sfs_userid] != null)
							$this->DiscardedObjects[$sfs_userid][$sfs_bid] = null;
					}
				}
			}
			else
				$this->DiscardedObjects[$sfs_userid] = null;
	}
	
	function Generate()
	{
		global $BROWSER,$USER,$CONFIG,$INTERNAL;
		$this->SetDiscardedObject();
		foreach($this->Visitors as $userid => $USER)
		{
			if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_MONITORING) == PERMISSION_RELATED && !$USER->IsInChatWith($INTERNAL[CALLER_SYSTEM_ID]))
				continue;
				
			if(!empty($CONFIG["gl_hide_inactive"]) && !$USER->IsActive())
				continue;

			if(!(!empty($CONFIG["gl_hvjd"]) && empty($USER->Javascript)))
			{
				$isactivebrowser = false;
				$this->XMLCurrentAliveBrowsers = 
				$this->XMLCurrentVisitor = "";
				$this->GetStaticInfo();
				$this->CurrentResponseType = ($USER->StaticInformation) ? DATA_RESPONSE_TYPE_STATIC : DATA_RESPONSE_TYPE_KEEP_ALIVE;
		
				foreach($USER->Browsers as $browserId => $BROWSER)
				{
					$this->ObjectCounter++;
					array_push($this->ActiveBrowsers,$BROWSER->BrowserId);
					$this->CurrentFilesize = $BROWSER->LastUpdate;
					
					$BROWSER->LoadChatRequest();
					$this->XMLCurrentChat = null;
					if($BROWSER->Type == BROWSER_TYPE_CHAT)
					{
						$isactivebrowser = true;
						$this->BuildChatXML();
						$this->SessionFileSizes[$userid][$browserId] = $this->CurrentFilesize;
					}
					else if(!isset($this->SessionFileSizes[$userid]) || !empty($BROWSER->ChatRequest) || $this->CurrentResponseType == DATA_RESPONSE_TYPE_STATIC || (isset($this->SessionFileSizes[$userid]) && (!isset($this->SessionFileSizes[$userid][$browserId]) || (isset($this->SessionFileSizes[$userid][$browserId]) && $this->SessionFileSizes[$userid][$browserId] != $this->CurrentFilesize))))
					{
						$isactivebrowser = true;
						if($this->CurrentResponseType == DATA_RESPONSE_TYPE_KEEP_ALIVE)
							$this->CurrentResponseType = DATA_RESPONSE_TYPE_BASIC;
						$this->SessionFileSizes[$userid][$browserId] = $this->CurrentFilesize;
					}
					else
					{
						$this->CurrentResponseType = DATA_RESPONSE_TYPE_KEEP_ALIVE;
					}
					$this->BuildVisitorXML();
					$USER->Browsers[$browserId] = $BROWSER;
				}
				$this->XMLCurrentVisitor .= $this->XMLCurrentAliveBrowsers;
				if($this->XMLVisitorOpen)
				{
					if($this->IsDiscardedObject || $isactivebrowser)
						$this->XMLCurrent .= $this->XMLCurrentVisitorTag . $this->XMLCurrentVisitor . "</v>\r\n";
					$this->XMLVisitorOpen = false;
				}
			}
		}
		$this->RemoveFileSizes($this->ActiveBrowsers);
	}
	
	function BuildVisitorXML()
	{
		global $USER,$BROWSER;
		$visitorDetails = Array("userid" => " id=\"".base64_encode($USER->UserId)."\"","resolution" => null,"ip" => null,"lat" => null,"long" => null,"city" => null,"ctryi2" => null,"region" => null,"system" => null,"language" => null,"ka" => null,"requested" => null,"target" => null,"declined" => null,"accepted" => null,"cname" => null,"cemail" => null,"ccompany" => null,"waiting" => null,"timezoneoffset" => null,"visits" => null,"host"=>null,"grid"=>null,"isp"=>null,"cf0"=>null,"cf1"=>null,"cf2"=>null,"cf3"=>null,"cf4"=>null,"cf5"=>null,"cf6"=>null,"cf7"=>null,"cf8"=>null,"cf9"=>null,"sys"=>null,"bro"=>null,"js"=>null,"visitlast"=>null);
		if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE)
		{
			$visitorDetails["ka"] = " ka=\"".base64_encode(true)."\"";
			$visitorDetails["requested"] = (!empty($BROWSER->ChatRequest) && !$BROWSER->ChatRequest->Accepted && !$BROWSER->ChatRequest->Declined && !$BROWSER->ChatRequest->Closed) ? " req=\"".base64_encode(getInternalSystemIdByUserId($BROWSER->ChatRequest->SenderUserId))."\"" : "";
			$visitorDetails["declined"] = (!empty($BROWSER->ChatRequest) && $BROWSER->ChatRequest->Declined) ? " dec=\"".base64_encode("1")."\"" : "";
			$visitorDetails["accepted"] = (!empty($BROWSER->ChatRequest) && $BROWSER->ChatRequest->Accepted) ? " acc=\"".base64_encode("1")."\"" : "";
			$visitorDetails["target"] = (!empty($BROWSER->ChatRequest)) ? " tbid=\"".base64_encode($BROWSER->BrowserId)."\"" : "";
		}
		if($this->CurrentResponseType == DATA_RESPONSE_TYPE_STATIC)
		{
			$visitorDetails["resolution"] = " res=\"".base64_encode($USER->Resolution)."\"";
			$visitorDetails["ip"] = " ip=\"".base64_encode($USER->IP)."\"";
			$visitorDetails["timezoneoffset"] = " tzo=\"".base64_encode($USER->GeoTimezoneOffset)."\"";
			$visitorDetails["lat"] = " lat=\"".base64_encode($USER->GeoLatitude)."\"";
			$visitorDetails["long"] = " long=\"".base64_encode($USER->GeoLongitude)."\"";
			$visitorDetails["city"] = " city=\"".base64_encode($USER->GeoCity)."\"";
			$visitorDetails["ctryi2"] = " ctryi2=\"".base64_encode($USER->GeoCountryISO2)."\"";
			$visitorDetails["region"] = " region=\"".base64_encode($USER->GeoRegion)."\"";
			$visitorDetails["js"] = " js=\"".base64_encode($USER->Javascript)."\"";
			$visitorDetails["language"] = " lang=\"".base64_encode($USER->Language)."\"";
			$visitorDetails["visits"] = " vts=\"".base64_encode($USER->Visits)."\"";
			$visitorDetails["host"] = " ho=\"".base64_encode($USER->Host)."\"";
			$visitorDetails["grid"] = " gr=\"".base64_encode($USER->GeoResultId)."\"";
			$visitorDetails["isp"] = " isp=\"".base64_encode($USER->GeoISP)."\"";
			$visitorDetails["sys"] = " sys=\"".base64_encode($USER->OperatingSystem)."\"";
			$visitorDetails["bro"] = " bro=\"".base64_encode($USER->Browser)."\"";
			$visitorDetails["visitlast"] = " vl=\"".base64_encode($USER->VisitLast)."\"";
		}
		$visitorDetails["waiting"] = ($BROWSER->Type == BROWSER_TYPE_CHAT && $BROWSER->Waiting && in_array($BROWSER->DesiredChatGroup,$this->Caller->Groups)) ? " w=\"".base64_encode(1)."\"" : "";
		if(!empty($BROWSER->ChatRequest))
		{
			if(empty($USER->ActiveChatRequest) || (!empty($USER->ActiveChatRequest) && $BROWSER->ChatRequest->Created > $USER->ActiveChatRequest->Created))
				$USER->ActiveChatRequest = $BROWSER->ChatRequest;
		}
		
		if(!in_array($USER->UserId,$this->AddedVisitors) || (!empty($BROWSER->ChatRequest) && $BROWSER->ChatRequest == $USER->ActiveChatRequest))
		{
			array_push($this->AddedVisitors, $USER->UserId);
			$this->XMLVisitorOpen = true;
			$this->XMLCurrentVisitorTag =  "<v".$visitorDetails["userid"].$visitorDetails["resolution"].$visitorDetails["ip"].$visitorDetails["lat"].$visitorDetails["long"].$visitorDetails["region"].$visitorDetails["city"].$visitorDetails["ctryi2"].$visitorDetails["visits"].$visitorDetails["declined"].$visitorDetails["accepted"].$visitorDetails["target"].$visitorDetails["system"].$visitorDetails["language"].$visitorDetails["requested"].$visitorDetails["cname"].$visitorDetails["cemail"].$visitorDetails["ccompany"].$visitorDetails["timezoneoffset"].$visitorDetails["host"].$visitorDetails["grid"].$visitorDetails["isp"].$visitorDetails["cf0"].$visitorDetails["cf1"].$visitorDetails["cf2"].$visitorDetails["cf3"].$visitorDetails["cf4"].$visitorDetails["cf5"].$visitorDetails["cf6"].$visitorDetails["cf7"].$visitorDetails["cf8"].$visitorDetails["cf9"].$visitorDetails["sys"].$visitorDetails["bro"].$visitorDetails["js"].$visitorDetails["visitlast"].">\r\n";
		}

		if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE)
		{
			$referrer = ($BROWSER->History[0]->Referrer != null) ? " ref=\"".base64_encode($BROWSER->History[0]->Referrer->GetAbsoluteUrl())."\"" : "";
			
			$personal = " cname=\"".base64_encode($BROWSER->Fullname)."\"";
			$personal .= " cemail=\"".base64_encode($BROWSER->Email)."\"";
			$personal .= " ccompany=\"".base64_encode($BROWSER->Company)."\"";

			for($int=0;$int<=9;$int++)
				if(isset($BROWSER->Customs[$int]) && !empty($BROWSER->Customs[$int]))
					$personal .= " cf".$int."=\"".base64_encode($BROWSER->Customs[$int])."\"";
			
			$this->XMLCurrentVisitor .=  " <b id=\"".base64_encode($BROWSER->BrowserId)."\" ss=\"".base64_encode($BROWSER->Query)."\"".$visitorDetails["ka"].$referrer.$visitorDetails["waiting"].$personal.">\r\n";
				for($i = 0;$i < count($BROWSER->History);$i++)
					$this->XMLCurrentVisitor .=  "  <h time=\"".base64_encode($BROWSER->History[$i]->Entrance)."\" url=\"".base64_encode($BROWSER->History[$i]->Url->GetAbsoluteUrl())."\" title=\"".base64_encode(@$BROWSER->History[$i]->Url->PageTitle)."\" code=\"".base64_encode( ($BROWSER->Type == BROWSER_TYPE_CHAT) ? $BROWSER->Code : $BROWSER->History[$i]->Url->AreaCode )."\" cp=\"".base64_encode($BROWSER->Type)."\" />\r\n";
			if(!empty($this->XMLCurrentChat))
				$this->XMLCurrentVisitor .= $this->XMLCurrentChat;
			$this->XMLCurrentVisitor .=  " </b>\r\n";
		}
	}
	
	function BuildChatXML()
	{
		global $USER,$BROWSER,$GROUPS;
		if($this->CurrentResponseType == DATA_RESPONSE_TYPE_KEEP_ALIVE)
			$this->CurrentResponseType = DATA_RESPONSE_TYPE_BASIC;
		if($this->GetAll)
			$this->CurrentResponseType = DATA_RESPONSE_TYPE_STATIC;

		if(!$BROWSER->Closed && $BROWSER->Status > CHAT_STATUS_OPEN)
		{
			if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE && !empty($BROWSER->Fullname) && !empty($BROWSER->DesiredChatGroup))
			{
				$USER->IsChat = true;
				$this->XMLCurrentChat =  "  <chat id=\"".base64_encode($BROWSER->ChatId)."\" st=\"".base64_encode($BROWSER->Activated)."\" fn=\"" . base64_encode($BROWSER->Fullname) . "\" em=\"" . base64_encode($BROWSER->Email) . "\" eq=\"" . base64_encode($BROWSER->Question) . "\" gr=\"".base64_encode($BROWSER->DesiredChatGroup)."\" co=\"" . base64_encode($BROWSER->Company) . "\">\r\n";
				
				if(is_array($BROWSER->Customs))
					foreach($BROWSER->Customs as $index => $value)
						if(!empty($value))
							$this->XMLCurrentChat .=  "   <cf index=\"" . base64_encode($index) . "\">".base64_encode($value)."</cf>\r\n";
				
				if(!empty($BROWSER->InternalUser))
				{
					$this->XMLCurrentChat .=  "   <pn id=\"" . base64_encode($BROWSER->InternalUser->SystemId) . "\">\r\n";
					if(empty($BROWSER->Activated) && count($BROWSER->ChatRequestReceiptants) > 0)
						foreach($BROWSER->ChatRequestReceiptants as $crr_systemid)
							$this->XMLCurrentChat .=  "    <crr id=\"" . base64_encode($crr_systemid) . "\" />\r\n";
					$this->XMLCurrentChat .=  "   </pn>\r\n";
				}

				if($BROWSER->Activated == 0)
				{
					if(!empty($BROWSER->Forward) && $BROWSER->Forward->Processed && CALLER_SYSTEM_ID == $BROWSER->Forward->TargetSessId)
					{
						$this->XMLCurrentChat .=  "  <forward sender=\"".base64_encode($BROWSER->Forward->SenderSystemId)."\" text=\"".base64_encode($BROWSER->Forward->Text)."\" conversation=\"".base64_encode($BROWSER->Forward->Conversation)."\" />\r\n";
						$BROWSER->Forward->Save(true,true);
					}
				}
				else if(isset($this->Caller->ExternalChats[$BROWSER->SystemId]) && !empty($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest) && $this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->ReceiverUserId == $this->Caller->SystemId)
				{
					if($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Error && $this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Permission != PERMISSION_NONE)
					{
						$this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Close();
						$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Id)."\" cr=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Created)."\" fm=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileMask)."\" fn=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileName)."\" fid=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileId)."\" error=\"".base64_encode(true)."\" />\r\n";
					}
					else if($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Download)
						$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Id)."\" cr=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Created)."\" fm=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileMask)."\" fn=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileName)."\" fid=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileId)."\" download=\"".base64_encode(true)."\" size=\"".base64_encode(@filesize($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->GetFile()))."\" />\r\n";
					else if($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Permission == PERMISSION_VOID)
						$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Id)."\" cr=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Created)."\" fm=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileMask)."\" fn=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileName)."\" fid=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileId)."\" />\r\n";
				}
				
				$this->XMLCurrentChat .=  "  </chat>\r\n";
				$this->XMLTyping .= "<v id=\"".base64_encode($BROWSER->UserId . "~" . $BROWSER->BrowserId)."\" tp=\"".base64_encode((($BROWSER->Typing)?1:0))."\" />\r\n";
			}
			else
				$this->XMLCurrentChat = "  <chat />\r\n";
		}
	}
	
	function GetStaticInfo($found = false)
	{
		global $USER;
		foreach($USER->Browsers as $browserId => $BROWSER)
			if(isset($this->SessionFileSizes[$USER->UserId][$browserId]))
			{
				$found = true;
				break;
			}
		
		if($this->GetAll || isset($this->StaticReload[$USER->UserId]) || !$found || ($this->Caller->LastActive <= $USER->LastActive && !in_array($USER->UserId,$this->CurrentStatics)))
		{
			if(isset($this->StaticReload[$USER->UserId]))
				unset($this->StaticReload[$USER->UserId]);
			
			array_push($this->CurrentStatics,$USER->UserId);
			$USER->StaticInformation = true;
		}
		else
			$USER->StaticInformation = false;
	}

	function RemoveFileSizes($_browsers)
	{
		foreach($this->SessionFileSizes as $userid => $browsers)
			if(is_array($browsers) && count($browsers) > 0)
			{
				foreach($browsers as $BROWSER => $size)
					if(!in_array($BROWSER,$_browsers))
						unset($this->SessionFileSizes[$userid][$BROWSER]);
			}
			else
				unset($this->SessionFileSizes[$userid]);
	}
}
?>
