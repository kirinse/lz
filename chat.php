<?php
/****************************************************************************************
* LiveZilla chat.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);
if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
@ini_set('session.use_cookies', '0');
@error_reporting(E_ALL);
$content_frames = array("lz_chat_frame.3.2.login.1.0","lz_chat_frame.4.1","lz_chat_frame.3.2.login.0.0","lz_chat_frame.3.2.mail","lz_chat_frame.1.1","lz_chat_frame.3.2.chat","lz_chat_frame.3.2.chat.0.0","lz_chat_frame.3.2.chat.1.0","lz_chat_frame.3.2.chat.2.0","lz_chat_frame.3.2.chat.4.0");
$html = "";

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");

if(!(isset($_GET[GET_EXTERN_TEMPLATE]) && !in_array($_GET[GET_EXTERN_TEMPLATE],$content_frames)))
{
	require(LIVEZILLA_PATH . "_lib/functions.external.inc.php");
	require(LIVEZILLA_PATH . "_lib/objects.external.inc.php");
	require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
	
	defineURL(FILE_CHAT);
	setDataProvider();
	@set_time_limit($CONFIG["timeout_clients"]);
	if(!isset($_GET["file"]))
		@set_error_handler("handleError");
	
	$browserId = getId(USER_ID_LENGTH);
	define("SESSION",getSessionId());
	header("Content-Type: text/html; charset=utf-8");
	languageSelect();
	
	if(empty($CONFIG["gl_om_pop_up"]) && $CONFIG["gl_om_mode"] == 1)
	{
		initData(true,true,false,true);
		$groupbuilder = new GroupBuilder($INTERNAL,$GROUPS,$CONFIG);
		$groupbuilder->Generate();
		if(!$groupbuilder->GroupAvailable)
			exit("<html><script language=\"JavaScript\">if(typeof(window.opener != null) != 'undefined')window.opener.location = \"".$CONFIG["gl_om_http"]."\";window.close();</script></html>");
	}
	else
		initData(false,false,false,true);
	
	if((isset($_POST["company"]) && !empty($_POST["company"])) || (isset($_POST["email"]) && !empty($_POST["email"])) || (isset($_POST["name"]) && !empty($_POST["name"])) || (isset($_POST["text"]) && !empty($_POST["text"])))
		exit(createFloodFilter(getIP(),null));
}

if(!isset($_GET[GET_EXTERN_TEMPLATE]))
{
	define("IS_FLOOD",isFlood(getIP(),null,true));
	define("IS_FILTERED",$FILTERS->Match($_SERVER["REMOTE_ADDR"],formLanguages(((!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "")),SESSION));
	initData(true,false,false,false);
	$html = getFile(TEMPLATE_HTML_EXTERN);
	$html = str_replace("<!--extern_script-->",getFile(TEMPLATE_SCRIPT_EXTERN).getFile(TEMPLATE_SCRIPT_DATA).getFile(TEMPLATE_SCRIPT_CHAT).getFile(TEMPLATE_SCRIPT_FRAME),$html);
	$html = str_replace("<!--server_id-->",substr(md5($CONFIG["gl_lzid"]),5,5),$html);
	$html = str_replace("<!--connector_script-->",getFile(TEMPLATE_SCRIPT_CONNECTOR),$html);
	$html = str_replace("<!--group_script-->",getFile(TEMPLATE_SCRIPT_GROUPS),$html);
	$html = str_replace("<!--global_script-->",getFile(TEMPLATE_SCRIPT_GLOBAL),$html);
	$html = str_replace("<!--browser_id-->",$browserId,$html);
	$html = str_replace("<!--extern_timeout-->",$CONFIG["timeout_clients"],$html);
	$html = str_replace("<!--chat_transcript_form_visible-->",parseBool($CONFIG["gl_uret"] && $CONFIG["gl_soct"]),$html);
	$html = str_replace("<!--translation_service_visible-->",parseBool($CONFIG["gl_otrs"]),$html);
	$html = str_replace("<!--extern_frequency-->",$CONFIG["poll_frequency_clients"],$html);
	$html = str_replace("<!--cbcd-->",parseBool($CONFIG["gl_cbcd"]),$html);
	$html = str_replace("<!--bookmark_name-->",base64_encode($CONFIG["gl_site_name"]),$html);
	$html = str_replace("<!--user_id-->",SESSION,$html);
	$html = str_replace("<!--connection_error_span-->",CONNECTION_ERROR_SPAN,$html);
	$html = replaceLoginDetails($html);
	$html = geoReplacements($html);
	$html = str_replace("<!--requested_intern_userid-->",((!empty($_GET[GET_EXTERN_INTERN_USER_ID]) && isset($INTERNAL[getInternalSystemIdByUserId(base64UrlDecode($_GET[GET_EXTERN_INTERN_USER_ID]))])) ? (base64UrlDecode($_GET[GET_EXTERN_INTERN_USER_ID])):""),$html);
	$html = str_replace("<!--geo_resolute-->",parseBool(!isSSpanFile() && !empty($CONFIG["gl_pr_ngl"]) && !(getCookieValue("geo_data") != null && getCookieValue("geo_data") > (time()-2592000))),$html);
	$html = str_replace("<!--area_code-->",((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? "&code=" . getParam(GET_TRACK_SPECIAL_AREA_CODE) : ""),$html);
	$html = str_replace("<!--template_message_intern-->",base64_encode(getFile(TEMPLATE_HTML_MESSAGE_INTERN)),$html);
	$html = str_replace("<!--template_message_extern-->",base64_encode(getFile(TEMPLATE_HTML_MESSAGE_EXTERN)),$html);
	$html = str_replace("<!--template_message_add-->",base64_encode(getFile(TEMPLATE_HTML_MESSAGE_ADD)),$html);
	$html = str_replace("<!--template_message_add_alt-->",base64_encode(getFile(TEMPLATE_HTML_MESSAGE_ADD_ALTERNATE)),$html);
	$html = str_replace("<!--direct_login-->",parseBool((isset($_GET[GET_EXTERN_USER_NAME]) && !isset($_GET[GET_EXTERN_RESET]))),$html);
	$html = str_replace("<!--is_ie-->",parseBool((!empty($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))),$html);
	$html = str_replace("<!--setup_error-->",base64_encode(buildLoginErrorField()),$html);
	$html = str_replace("<!--offline_message_mode-->",$CONFIG["gl_om_mode"],$html);
	$html = str_replace("<!--offline_message_http-->",$CONFIG["gl_om_http"],$html);
	$html = str_replace("<!--offline_message_pre_chat-->",parseBool($CONFIG["gl_no_om_sp"]==1),$html);
	$html = str_replace("<!--lang_client_queue_message-->",(($CONFIG["gl_sho_qu_inf"]==1)?$LZLANG["client_queue_message"]:$LZLANG["client_ints_are_busy"]),$html);
}
else
{
	if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.login.1.0")
	{
		initData(true,true,false,false);
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$html = (isset($CONFIG["gl_site_name"])) ? str_replace("<!--config_name-->",$CONFIG["gl_site_name"],$html) : str_replace("<!--config_name-->","LiveZilla",$html);
		$html = getChatLoginInputs($html);
		$html = replaceLoginDetails($html);
		$html = str_replace("<!--alert-->",getAlertTemplate(),$html);
		$html = str_replace("<!--info_text-->",$CONFIG["gl_info"],$html);
		$html = str_replace("<!--login_trap-->",getFile(TEMPLATE_LOGIN_TRAP),$html);
		$html = str_replace("<!--group_select_visibility-->",((defined("HideChatGroupSelection") || count($GROUPS)<2) ? "display:none;" : ""),$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.login.0.0")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$html = str_replace("<!--button_message-->",($CONFIG["gl_no_om_sp"]) ? "" : getFile(TEMPLATE_HTML_BUTTON_MESSAGE),$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.chat" && isset($_POST[GET_EXTERN_GROUP]))
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$html = str_replace("<!--intgroup-->",base64_encode($_POST[GET_EXTERN_GROUP]),$html);
		$html = str_replace("<!--misc_frame_height-->",(($CONFIG["gl_uret"] && $CONFIG["gl_otrs"]) ? 52 : (($CONFIG["gl_uret"] || $CONFIG["gl_otrs"]) ? 31 : 0)),$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.mail")
	{
		initData(false,true,false,false);
		$groupbuilder = new GroupBuilder(NULL,$GROUPS,NULL);
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$html = getTicketInputs($html);
		if(isset($_POST["form_111"]) && !empty($_POST["form_111"]))
			setCookieValue("form_111",$_POST["form_111"]);
		if(isset($_POST["form_112"]) && !empty($_POST["form_112"]))
			setCookieValue("form_112",$_POST["form_112"]);
		if(isset($_POST["form_113"]) && !empty($_POST["form_113"]))
			setCookieValue("form_113",$_POST["form_113"]);
		if(isset($_POST["form_114"]) && !empty($_POST["form_114"]))
			setCookieValue("form_114",$_POST["form_114"]);
		
		$html = str_replace("<!--alert-->",getAlertTemplate(),$html);
		$html = str_replace("<!--direct_send-->",(isset($_GET["ds"])) ? "top.lz_chat_check_ticket_inputs();" : "document.getElementById('lz_chat_loading').style.display='none';document.getElementById('lz_ticket_elements').style.display='';",$html);
		$html = replaceLoginDetails($html);
		$html = str_replace("<!--groups-->",$groupbuilder->GetHTML(),$html);
		$html = str_replace("<!--login_trap-->",getFile(TEMPLATE_LOGIN_TRAP),$html);
		$html = str_replace("<!--group_select_visibility-->",((defined("HideTicketGroupSelection") || count($GROUPS)<2) ? "display:none;" : ""),$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.1.1")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		if(isset($_GET[GET_EXTERN_USER_HEADER]) && !empty($_GET[GET_EXTERN_USER_HEADER]))
			$html = str_replace("<!--logo-->","<img src=\"".base64UrlDecode($_GET[GET_EXTERN_USER_HEADER])."\" alt=\"\" border=\"0\"><br>",$html);
		else
			$html = str_replace("<!--logo-->",((file_exists(FILE_CARRIERLOGO)) ? "<img src=\"".FILE_CARRIERLOGO."\" alt=\"livezilla.net\" border=\"0\"><br>" : ""),$html);
		$html = str_replace("<!--background-->",((file_exists(FILE_CARRIERHEADER)) ? "<img src=\"".FILE_CARRIERHEADER."\" alt=\"livezilla.net\" border=\"0\"><br>" : ""),$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.chat.0.0" && isset($_GET[GET_EXTERN_GROUP]))
	{
		initData(false,true,false,false);
		$groupid = base64_decode($_GET[GET_EXTERN_GROUP]);
		if(!isnull(trim($groupid)) && isset($GROUPS[$groupid]))
		{
			$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
			$html = str_replace("<!--SM_HIDDEN-->",((empty($GROUPS[$groupid]->ChatFunctions[0])) ? " style=\"display:none;\"" : ""),$html);
			$html = str_replace("<!--SO_HIDDEN-->",((empty($GROUPS[$groupid]->ChatFunctions[1])) ? " style=\"display:none;\"" : ""),$html);
			$html = str_replace("<!--PR_HIDDEN-->",((empty($GROUPS[$groupid]->ChatFunctions[2])) ? " style=\"display:none;\"" : ""),$html);
			$html = str_replace("<!--RA_HIDDEN-->",((empty($GROUPS[$groupid]->ChatFunctions[3])) ? " style=\"display:none;\"" : ""),$html);
			$html = str_replace("<!--FV_HIDDEN-->",((empty($GROUPS[$groupid]->ChatFunctions[4])) ? " style=\"display:none;\"" : ""),$html);
			$html = str_replace("<!--FU_HIDDEN-->",((empty($GROUPS[$groupid]->ChatFunctions[5])) ? " style=\"display:none;\"" : ""),$html);
		}
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.chat.1.0")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		if(isset($_POST[POST_EXTERN_USER_USERID]))
		{
			if(STATS_ACTIVE)
				initStatisticProvider();
			$externalUser = new Visitor($_POST[POST_EXTERN_USER_USERID]);
			$externalChat = new VisitorChat($externalUser->UserId,$_POST[POST_EXTERN_USER_BROWSERID]);
			$externalChat->Load();
			if(isset($_FILES["userfile"]) && $externalUser->StoreFile($_POST[POST_EXTERN_USER_BROWSERID],$externalChat->DesiredChatPartner,$externalChat->Fullname))
				$command = "top.lz_chat_file_ready();";
			else if(isset($_FILES['userfile']))
				$command = "top.lz_chat_file_error(2);";
			else
				$command = "";
		}
		else if(isset($_GET["file"]))
			$command = "top.lz_chat_file_error(2);";
		else
			$command = "";
		$html = str_replace("<!--response-->",$command,$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.chat.2.0")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$rate = new RatingGenerator();
		$html = str_replace("<!--rate_1-->",$rate->Fields[0],$html);
		$html = str_replace("<!--rate_2-->",$rate->Fields[1],$html);
		$html = str_replace("<!--rate_3-->",$rate->Fields[2],$html);
		$html = str_replace("<!--rate_4-->",$rate->Fields[3],$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.chat.4.0")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$html = str_replace("<!--alert-->",getAlertTemplate(),$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.4.1")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$html = str_replace("<!--param-->",$CONFIG["gl_c_param"],$html);
	}
	else if($_GET[GET_EXTERN_TEMPLATE] == "lz_chat_frame.3.2.chat.6.0")
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
		$tlanguages = "";
		if(!empty($CONFIG["gl_otrs"]))
		{
			initData(false,false,false,false,false,true);
			require("./_lib/functions.external.inc.php");
			$mylang = getBrowserLocalization();
			foreach($LANGUAGES as $iso => $langar)
				if($langar[1])
					$tlanguages .= "<option value=\"".strtolower($iso)."\"".(($mylang[0]==$iso || (strtolower($iso) == strtolower($CONFIG["gl_default_language"]) && (empty($mylang[0]) || (!empty($mylang[0]) && isset($LANGUAGES[$mylang[0]]) && !$LANGUAGES[$mylang[0]][1]))))?" SELECTED":"").">".$langar[0]."</option>";
			$html = str_replace("<!--translator_api-->","<script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script><script type=\"text/javascript\">top.lz_translator = google;top.lz_translator.load(\"language\", \"1\");</script>",$html);
		}
		$html = str_replace("<!--translation_display-->",(($CONFIG["gl_otrs"])?"":"none"),$html);
		$html = str_replace("<!--transcript_option_display-->",(($CONFIG["gl_uret"])?"":"none"),$html);
		$html = str_replace("<!--languages-->",$tlanguages,$html);
	}
	else
	{
		$html = getFile(PATH_FRAMES.$_GET[GET_EXTERN_TEMPLATE].".tpl");
	}
}
$html = str_replace("<!--server-->",".",$html);
$html = str_replace("<!--url_get_params-->",getParams(),$html);
unloadDataProvider();
exit(doReplacements($html));
?>
