<?php
/****************************************************************************************
* LiveZilla track.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

if(!getAvailability() || empty($CONFIG["gl_vmac"]))
	die();
	
require(LIVEZILLA_PATH . "_lib/functions.tracking.inc.php");

define("JAVASCRIPT",!(isset($_GET[GET_TRACK_OUTPUT_TYPE]) && $_GET[GET_TRACK_OUTPUT_TYPE] == "nojcrpt") && strpos($_SERVER["QUERY_STRING"],"nojcrpt") === false);

if(isset($_GET[GET_TRACK_USERID]) && !empty($_GET[GET_TRACK_USERID]))
{
	define("CALLER_BROWSER_ID",getParam(GET_TRACK_BROWSERID));
	define("CALLER_USER_ID",getParam(GET_TRACK_USERID));

	if(isnull(getCookieValue("userid")) || (!isnull(getCookieValue("userid")) && getCookieValue("userid") != CALLER_USER_ID))
		setCookieValue("userid",CALLER_USER_ID);
}
else if(!isnull(getCookieValue("userid")))
{
	define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	define("CALLER_USER_ID",getCookieValue("userid"));
}
if(!defined("CALLER_USER_ID"))
{
	if(!JAVASCRIPT)
	{
		define("CALLER_USER_ID",substr(md5(getIP()),0,USER_ID_LENGTH));
		define("CALLER_BROWSER_ID",substr(strrev(md5(getIP())),0,USER_ID_LENGTH));
	}
	else
	{
		define("CALLER_USER_ID",getId(USER_ID_LENGTH));
		define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	}
}

$EXTERNALUSER = new Visitor(CALLER_USER_ID);
$EXTERNALUSER->Load();

if(isset($_GET[GET_TRACK_OUTPUT_TYPE]) && ($_GET[GET_TRACK_OUTPUT_TYPE] == "jscript" || $_GET[GET_TRACK_OUTPUT_TYPE] == "jcrpt"))
{
	$fullname = getParam(GET_EXTERN_USER_NAME);
	$email = getParam(GET_EXTERN_USER_EMAIL);
	$company = getParam(GET_EXTERN_USER_COMPANY);
	$customs = array();
	
	if(empty($_GET[GET_TRACK_NO_SEARCH_ENGINE]))
		exit(getFile(TEMPLATE_HTML_SUPPORT));

	$row = $EXTERNALUSER->CreateSignature();
	if(is_array($row) && $row["id"] != CALLER_USER_ID)
	{
		$EXTERNALUSER->UserId = $row["id"];
		$fullname = (empty($fullname)) ? base64UrlEncode($row["fullname"]) : "";
		$email = (empty($email)) ? base64UrlEncode($row["email"]) : "";
		$company = (empty($company)) ? base64UrlEncode($row["company"]) : "";
		$customs = @unserialize($row["customs"]);
	}
	
	$TRACKINGSCRIPT = getFile(TEMPLATE_SCRIPT_GLOBAL) . getFile(TEMPLATE_SCRIPT_TRACK);
	$TRACKINGSCRIPT .= str_replace("<!--file_chat-->",FILE_CHAT,getFile(TEMPLATE_SCRIPT_BOX));
	$TRACKINGSCRIPT = str_replace("<!--server_id-->",substr(md5($CONFIG["gl_lzid"]),5,5),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--server-->",LIVEZILLA_URL,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--area_code-->",(isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? htmlentities($_GET[GET_TRACK_SPECIAL_AREA_CODE],ENT_QUOTES,"UTF-8") : "",$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--browser_id-->",htmlentities(CALLER_BROWSER_ID,ENT_QUOTES,"UTF-8"),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_id-->",htmlentities($EXTERNALUSER->UserId,ENT_QUOTES,"UTF-8"),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--connection_error_span-->",CONNECTION_ERROR_SPAN,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--poll_frequency-->",getPollFrequency(),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--height-->",$CONFIG["wcl_window_height"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--width-->",$CONFIG["wcl_window_width"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = geoReplacements($TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--geo_resolute-->",parseBool(!isSSpanFile() && $EXTERNALUSER->FirstCall && !empty($CONFIG["gl_pr_ngl"]) && !(!isnull(getCookieValue("geo_data")) && getCookieValue("geo_data") > time()-2592000)),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--alert_html-->",base64_encode(getAlertTemplate()),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_name-->",$fullname,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_email-->",$email,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_company-->",$company,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_question-->",getParam(GET_EXTERN_USER_QUESTION),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_header-->",getParam(GET_EXTERN_USER_HEADER),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_customs-->",getJSCustomArray("",$customs),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--custom_params-->",getCustomParams("",$customs),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--is_ie-->",parseBool((!empty($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))),$TRACKINGSCRIPT);

	if(isset($fullname))
		setCookieValue("form_111",cutString(base64UrlDecode($fullname),254));
	if(isset($_GET[GET_EXTERN_USER_EMAIL]))
		setCookieValue("form_112",cutString(base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL]),254));
	if(isset($_GET[GET_EXTERN_USER_COMPANY]))
		setCookieValue("form_113",cutString(base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY]),254));
		
	for($i=0;$i<=9;$i++)
		if(isset($_GET["cf".$i]) && !empty($_GET["cf".$i]))
			setCookieValue("cf_".$i,cutString(base64UrlDecode($_GET["cf".$i]),254));
			
	if(!empty($_GET["fbpos"]) && is_numeric($_GET["fbpos"]) && !empty($_GET["fbw"]) && is_numeric($_GET["fbw"]) && !empty($_GET["fbh"]) && is_numeric($_GET["fbh"]))
	{
		$shadow=(!empty($_GET["fbshx"]) && is_numeric($_GET["fbshx"]) && !empty($_GET["fbshy"]) && is_numeric($_GET["fbshy"]) && !empty($_GET["fbshb"]) && is_numeric($_GET["fbshb"]) && !empty($_GET["fbshc"]) && ctype_alnum($_GET["fbshc"])) ? "true,".$_GET["fbshb"].",".$_GET["fbshx"].",".$_GET["fbshy"].",'".$_GET["fbshc"]."'" : "false,0,0,0,''";
		$margin=(isset($_GET["fbmt"]) && is_numeric($_GET["fbmt"]) && isset($_GET["fbmr"]) && is_numeric($_GET["fbmr"]) && isset($_GET["fbmb"]) && is_numeric($_GET["fbmb"]) && isset($_GET["fbml"]) && is_numeric($_GET["fbml"])) ? (",".$_GET["fbml"].",".$_GET["fbmt"].",".$_GET["fbmr"].",'".$_GET["fbmb"]."'") : ",0,0,0,0";
		$online=true;
		if(!empty($_GET["fboo"]))
		{
			$parameters = getTargetParameters();
			if(!operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
				$online = false;
		}
		if($online)
			$TRACKINGSCRIPT .= "lz_tracking_add_floating_button(".$_GET["fbpos"].",".$shadow.$margin.",".$_GET["fbw"].",".$_GET["fbh"].");";
	}
}
else
{
	$TRACKINGSCRIPT = "lz_tracking_set_sessid(\"".htmlentities(CALLER_USER_ID)."\",\"".htmlentities(CALLER_BROWSER_ID)."\");";
	if(isset($_GET[GET_TRACK_URL]) && strpos(base64UrlDecode($_GET[GET_TRACK_URL]),GET_INTERN_COBROWSE) !== false)
		abortTracking(1);
		
	$BROWSER = new VisitorBrowser(CALLER_BROWSER_ID,CALLER_USER_ID);
	
	if($EXTERNALUSER->FirstCall && !$BROWSER->IsFirstCall())
		$EXTERNALUSER->FirstCall = false;
		
	initData(false,false,false,true,true);
	define("IS_FILTERED",$FILTERS->Match(getIP(),formLanguages(((!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "")),CALLER_USER_ID));
	define("IS_FLOOD",$BROWSER->IsFirstCall() && isFlood(getIP(),CALLER_USER_ID));

	if(!getAvailability() || IS_FILTERED || IS_FLOOD)
	{
		$BROWSER->Destroy();
		exit("lz_tracking_stop_tracking();");
	}
	
	$BROWSER->Customs = getCustomArray();
	
	if(isset($_GET[GET_EXTERN_USER_NAME]) && !empty($_GET[GET_EXTERN_USER_NAME]))
		$BROWSER->Fullname = cutString(base64UrlDecode($_GET[GET_EXTERN_USER_NAME]),254);
	else
		$BROWSER->Fullname = getCookieValue("form_111");
	
	if(isset($_GET[GET_EXTERN_USER_EMAIL]) && !empty($_GET[GET_EXTERN_USER_EMAIL]))
		$BROWSER->Email = cutString(base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL]),254);
	else
		$BROWSER->Email = getCookieValue("form_112");
		
	if(isset($_GET[GET_EXTERN_USER_COMPANY]) && !empty($_GET[GET_EXTERN_USER_COMPANY]))
		$BROWSER->Company = cutString(base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY]),254);
	else
		$BROWSER->Company = getCookieValue("form_113");
		
	if(isset($_GET[GET_EXTERN_USER_QUESTION]) && !empty($_GET[GET_EXTERN_USER_QUESTION]))
		$BROWSER->Question = base64UrlDecode($_GET[GET_EXTERN_USER_QUESTION]);

	$referrer = (isset($_GET[GET_TRACK_REFERRER]) ? trim(slashesStrip(base64UrlDecode($_GET[GET_TRACK_REFERRER]))) : "");
	if(JAVASCRIPT)
	{
		if(isset($_GET[GET_TRACK_RESOLUTION_WIDTH]))
		{
			if(!isset($_GET[GET_TRACK_URL]))
				abortTracking(9);
			else if(empty($_GET[GET_TRACK_URL]))
				abortTracking(3);
			$currentURL = new HistoryURL(substr(base64UrlDecode($_GET[GET_TRACK_URL]),0,2083),((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? base64UrlDecode($_GET[GET_TRACK_SPECIAL_AREA_CODE]) : ""),base64UrlDecode(@$_GET[GET_EXTERN_DOCUMENT_TITLE]),$referrer,time());
			
			if($currentURL->Referrer->IsInternalDomain())
				$currentURL->Referrer = new BaseUrl("");
			
			if($currentURL->Url->Excluded)
				abortTracking(4);
			$EXTERNALUSER->Save($CONFIG,array($_GET[GET_TRACK_RESOLUTION_WIDTH],$_GET[GET_TRACK_RESOLUTION_HEIGHT]),$_GET[GET_TRACK_COLOR_DEPTH],$_GET[GET_TRACK_TIMEZONE_OFFSET],((isset($_GET[GEO_LATITUDE]))?$_GET[GEO_LATITUDE]:""),((isset($_GET[GEO_LONGITUDE]))?$_GET[GEO_LONGITUDE]:""),((isset($_GET[GEO_COUNTRY_ISO_2]))?$_GET[GEO_COUNTRY_ISO_2]:""),((isset($_GET[GEO_CITY]))?$_GET[GEO_CITY]:""),((isset($_GET[GEO_REGION]))?$_GET[GEO_REGION]:""),((isset($_GET[GEO_TIMEZONE]))?$_GET[GEO_TIMEZONE]:""),((isset($_GET[GEO_ISP]))?$_GET[GEO_ISP]:""),((isset($_GET[GEO_SSPAN]))?$_GET[GEO_SSPAN]:""),((isset($_GET[GEO_RESULT_ID]))?$_GET[GEO_RESULT_ID]:""));
		}
	}
	else if(!empty($_SERVER["HTTP_REFERER"]))
	{
		$currentURL = new HistoryURL(substr($_SERVER["HTTP_REFERER"],0,2083),((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? base64UrlDecode($_GET[GET_TRACK_SPECIAL_AREA_CODE]) : ""),"","",time());
		if($currentURL->Url->Excluded)
			abortTracking(5);
		else if(!$currentURL->Url->IsInternalDomain())
			abortTracking(6);
		$EXTERNALUSER->Save($CONFIG,null,"","",-522,-522,"","","","","","","",false);
	}
	else
		abortTracking(-1);

	if($EXTERNALUSER->IsCrawler)
		abortTracking(8);
	else if($EXTERNALUSER->SignatureMismatch)
	{
		$TRACKINGSCRIPT = "lz_tracking_set_sessid(\"".htmlentities($EXTERNALUSER->UserId)."\",\"".htmlentities(CALLER_BROWSER_ID)."\");";
		$TRACKINGSCRIPT .= "lz_tracking_callback(1);";
		$TRACKINGSCRIPT .= "lz_tracking_poll_server();";
	}
	else
	{
		if(isset($_GET[GET_TRACK_CLOSE_CHAT_WINDOW]))
		{
			$chat = new VisitorChat($EXTERNALUSER->UserId,$_GET[GET_TRACK_CLOSE_CHAT_WINDOW]);
			$chat->Load();
			$chat->ExternalClose();
			$chat->Destroy();
		}
		$BROWSER->LastActive = time();
		$BROWSER->VisitId = $EXTERNALUSER->VisitId;
		$BROWSER->Save($EXTERNALUSER,@$_GET[GET_TRACK_URL]);

		if(isset($currentURL) && (count($BROWSER->History) == 0 || (count($BROWSER->History) > 0 && $BROWSER->History[count($BROWSER->History)-1]->Url->GetAbsoluteUrl() != $currentURL->Url->GetAbsoluteUrl())))
		{
			$BROWSER->History[] = $currentURL;
			if(!isnull($BROWSER->History[count($BROWSER->History)-1]->Referrer->GetAbsoluteUrl()))
				if($BROWSER->SetQuery($BROWSER->History[count($BROWSER->History)-1]->Referrer->GetAbsoluteUrl()))
					$BROWSER->History[count($BROWSER->History)-1]->Referrer->MarkSearchEngine();
			$BROWSER->History[count($BROWSER->History)-1]->Save(CALLER_BROWSER_ID);
			$BROWSER->ForceUpdate();
		}
		else if(count($BROWSER->History) == 0)
			abortTracking(11);

		$BROWSER->LoadWebsitePush();
		$BROWSER->LoadChatRequest();
		$BROWSER->LoadAlerts();

		$TRACKINGSCRIPT .= triggerEvents();
		$TRACKINGSCRIPT .= processActions();

		if(isset($_GET[GET_TRACK_START]) && is_numeric($_GET[GET_TRACK_START]))
			$TRACKINGSCRIPT .= "lz_tracking_callback(" . getPollFrequency() . ");";
		if(empty($EXTERNALUSER->Host) && $EXTERNALUSER->FirstCall)
			$EXTERNALUSER->ResolveHost();
	}
}
?>
