<?php
/****************************************************************************************
* LiveZilla objects.external.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class GroupBuilder
{
	public $InternalGroups;
	public $InternalUsers;
	public $Config;
	public $GroupAvailable = false;
	public $GroupValues = Array();
	public $Result;
	public $ErrorHTML = "''";
	
	function GroupBuilder($_internalUsers,$_internalGroups,$_config)
	{
		$this->InternalUsers = $_internalUsers;
		$this->InternalGroups = $_internalGroups;
		$this->Config = $_config;
		$this->GroupValues["groups_online"] = Array();
		$this->GroupValues["groups_offline"] = Array();
		$this->GroupValues["groups_online_amounts"] = Array();
		$this->GroupValues["groups_output"] = Array();
		$this->GroupValues["groups_hidden"] = Array();
		$this->GroupValues["set_by_get_user"] = null;
		$this->GroupValues["set_by_get_group"] = null;
		$this->GroupValues["set_by_cookie"] = null;
		$this->GroupValues["set_by_standard"] = null;
		$this->GroupValues["set_by_online"] = null;
		$this->GroupValues["req_for_user"] = isset($_GET[GET_EXTERN_INTERN_USER_ID]);
		$this->GroupValues["req_for_group"] = isset($_GET[GET_EXTERN_GROUP]);
		
		$parameters = getTargetParameters();
		if($parameters["include_group"] != null)
		{
			foreach($_internalGroups as $gid => $group)
				if(!in_array($gid,$parameters["include_group"]))
					$this->GroupValues["groups_hidden"][] = $gid;
		}
		else if($parameters["include_user"] != null)
		{
			foreach($_internalGroups as $gid => $group)
				if(!in_array($gid,$_internalUsers[getInternalSystemIdByUserId($parameters["include_user"])]->Groups))
					$this->GroupValues["groups_hidden"][] = $gid;
		}
		else if($parameters["exclude"] != null)
			$this->GroupValues["groups_hidden"] = $parameters["exclude"];
	}
	
	function GetHTML()
	{
		$html_groups = "";
		foreach($this->InternalGroups as $id => $group)
		{
			$name = (strlen($group->Description) > 0) ? $group->Description : $id;
			$selected = (isset($_REQUEST[GET_EXTERN_GROUP]) && $_REQUEST[GET_EXTERN_GROUP] == $id) ? " SELECTED" : "";
			if($group->IsExternal && !in_array($id,$this->GroupValues["groups_hidden"]))
				$html_groups .= "<option value=\"".$id."\"".$selected.">".$name."</option>";
		}
		return $html_groups;
	}
	
	function Generate()
	{
		foreach($this->InternalUsers as $internaluser)
		{
			if($internaluser->LastActive > (time()-$this->Config["timeout_clients"]) && $internaluser->Status < 2)
			{
				for($count=0;$count<count($internaluser->Groups);$count++)
				{
					if(isset($_GET[GET_EXTERN_INTERN_USER_ID]) && !empty($_GET[GET_EXTERN_INTERN_USER_ID]) && $internaluser->UserId == base64URLdecode($_GET[GET_EXTERN_INTERN_USER_ID]))
						if(!($this->GroupValues["req_for_group"] && $internaluser->Groups[$count] != base64UrlDecode($_GET[GET_EXTERN_GROUP])) || (isset($_GET[GET_EXTERN_PREFERENCE]) && $_GET[GET_EXTERN_PREFERENCE] == "user"))
							$this->GroupValues["set_by_get_user"] = $internaluser->Groups[$count];

					if(!isset($this->GroupValues["groups_online_amounts"][$internaluser->Groups[$count]]))
						$this->GroupValues["groups_online_amounts"][$internaluser->Groups[$count]] = 0;
					$this->GroupValues["groups_online_amounts"][$internaluser->Groups[$count]]++;
				}
			}
		}
		$counter = 0;
		foreach($this->InternalGroups as $id => $group)
		{
			if(!$group->IsExternal)
				continue;
				
			$used = false;
			$amount = (isset($this->GroupValues["groups_online_amounts"]) && is_array($this->GroupValues["groups_online_amounts"]) && array_key_exists($id,$this->GroupValues["groups_online_amounts"]) && $group->IsOpeningHour()) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
			$transport = base64_encode($id) . "," . base64_encode($amount) . "," . base64_encode($group->Description) . "," . base64_encode($group->Email);

			if($this->GroupValues["req_for_group"] && $amount > 0 && $id == base64UrlDecode($_GET[GET_EXTERN_GROUP]))
				{$this->GroupValues["set_by_get_group"] = $id;$used=true;}
			elseif($amount > 0 && getCookieValue("login_group") != null && $id == getCookieValue("login_group") && !isset($requested_group))
				{$this->GroupValues["set_by_cookie"] = $id;$used=true;}
			elseif($amount > 0 && $group->IsStandard)
				{$this->GroupValues["set_by_standard"] = $id;$used=true;}
			elseif($amount > 0 && empty($this->GroupValues["set_by_online"]))
				{$this->GroupValues["set_by_online"] = $id;$used=true;}

			if(!in_array($id,$this->GroupValues["groups_hidden"]) && ($group->IsExternal || $used))
			{
				$counter++;
				if($amount > 0)
				{
					$this->GroupAvailable = true;
					$this->GroupValues["groups_online"][$id] = $transport;
				}
				else
					$this->GroupValues["groups_offline"][$id] = $transport;
			}
		}
		if(isset($_GET[GET_EXTERN_PREFERENCE]) && $_GET[GET_EXTERN_PREFERENCE] == "group")
		{
			if(isset($this->GroupValues["groups_online_amounts"][base64UrlDecode($_GET[GET_EXTERN_GROUP])]) && $this->GroupValues["groups_online_amounts"][base64UrlDecode($_GET[GET_EXTERN_GROUP])] > 0)
			{
				$this->GroupValues["set_by_get_user"] = null;
				$this->GroupValues["req_for_user"] = false;
			}
		}

		if(!empty($this->GroupValues["set_by_get_user"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_get_user"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_user"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_get_user"]];
		else if(!empty($this->GroupValues["set_by_get_group"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_get_group"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_group"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_get_group"]];
		else if(!empty($this->GroupValues["set_by_cookie"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_cookie"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_cookie"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_cookie"]];
		else if(!empty($this->GroupValues["set_by_standard"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_standard"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_standard"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_standard"]];
		else if(!empty($this->GroupValues["set_by_online"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_online"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_online"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_online"]];
			
		foreach($this->GroupValues["groups_online"] as $id => $transport)
			if(!isset($this->GroupValues["groups_output"][$id]))
				$this->GroupValues["groups_output"][$id] = $transport;

		$result = array_merge($this->GroupValues["groups_output"],$this->GroupValues["groups_offline"]);
		foreach($result as $key => $value)
		{
			$chat_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->ChatInputsHidden as $index)
			{
				if($count > 0)$chat_input_fields.=",";
				$chat_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($chat_input_fields . ");");
			$chat_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->ChatInputsMandatory as $index)
			{
				if($count > 0)$chat_input_fields.=",";
				$chat_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($chat_input_fields . ");");
		
			$ticket_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->TicketInputsHidden as $index)
			{
				if($count > 0)$ticket_input_fields.=",";
				$ticket_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($ticket_input_fields . ");");
			$ticket_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->TicketInputsMandatory as $index)
			{
				if($count > 0)$ticket_input_fields.=",";
				$ticket_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($ticket_input_fields . ");");

			if(!empty($this->Result))
				$this->Result .= ";" . $value;
			else
				$this->Result = $value;
		}
		if($counter == 0)
			$this->ErrorHTML = "lz_chat_data.Language.ClientErrorGroups";
	}
}

class RatingGenerator
{
	public $Fields;
	
	function RatingGenerator()
	{
		$this->Generate();
	}
	
	function Generate()
	{
		$this->Fields = array(4);
		for($int = 0;$int < 4;$int++)
			$this->Fields[$int]= str_replace("<!--box_id-->",$int,getFile(TEMPLATE_HTML_RATEBOX));
	}
}
?>
