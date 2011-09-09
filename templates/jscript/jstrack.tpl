window.onerror=lz_global_handle_exception;

if(document.getElementById('livezilla_style') == null)
{
	var lz_poll_id = 0;
	var lz_poll_url = "<!--server-->server.php";
	var lz_poll_frequency = <!--poll_frequency-->;
	var lz_referrer = document.referrer;
	var lz_stopped = false;
	var lz_request_window = null;
	var lz_alert_window = null;
	var lz_request_active = null;
	var lz_floating_button = null;
	var lz_overlay_box = null
	var lz_alert_active = null;
	var lz_website_push_active = null;
	var lz_session;
	var lz_style = document.createElement('LINK');
	var lz_area_code = "<!--area_code-->";
	var lz_user_name = "<!--user_name-->";
	var lz_user_email = "<!--user_email-->";
	var lz_user_company = "<!--user_company-->";
	var lz_user_question = "<!--user_question-->";
	var lz_user_customs = new Array(<!--user_customs-->);
	var lz_timer = null;
	var lz_timezone_offset = (new Date().getTimezoneOffset() / 60) * -1;
	var lz_geo_data_count = 6;
	var lz_alert_html = '<!--alert_html-->';
	var lz_geo_resolution = new lz_geo_resolver();
	var lz_chat_windows = new Array();
	var lz_check_cw = null;
	
	lz_tracking_load_style();
	lz_tracking_start_system();
}

function lz_is_geo_resolution_needed()
{
	return (lz_geo_resolution_needed && lz_session.GeoResolved.length != 7 && lz_session.GeoResolutions < 5);
}

function lz_tracking_remove_chat_window(_browserId)
{
	try
	{
		for(var browser in lz_chat_windows)
		{
			if(lz_chat_windows[browser].BrowserId == _browserId)
			{
				lz_chat_windows[browser].Deleted =
				lz_chat_windows[browser].Closed = true;
			}
		}
	}
	catch(ex)
	{
	  // domain restriction
	}
}

function lz_tracking_add_chat_window(_browserId,_parent)
{
	if(<!--is_ie-->)
		return;
		
	var bfound, bdelete, bactive = false;
	for(var browser in lz_chat_windows)
	{
		if(lz_chat_windows[browser].BrowserId == _browserId || _parent)
		{
			if(!_parent)
			{
				lz_chat_windows[browser].LastActive = lz_global_timestamp();
				lz_chat_windows[browser].Deleted = false;
				lz_chat_windows[browser].Closed = false;
			}
			else if(!lz_chat_windows[browser].Deleted && !lz_chat_windows[browser].Closed && (lz_chat_windows[browser].LastActive <= (lz_global_timestamp()-4)))
			{
				lz_chat_windows[browser].Closed = true;
				bdelete = true;
			}
			bfound = true;
		}
		
		if(!lz_chat_windows[browser].Closed)
			bactive = true;
	}
	if(!bfound && !_parent)
	{
		var chatWindow = new lz_chat_window();
		chatWindow.BrowserId = _browserId;
		chatWindow.LastActive = lz_global_timestamp();
		lz_chat_windows.push(chatWindow);
		bactive = true;
	}
	else if(_parent && bdelete)
	{
		lz_tracking_poll_server();
	}

	if(bactive && lz_check_cw == null)
		lz_check_cw = setTimeout("lz_check_cw=null;lz_tracking_add_chat_window('"+_browserId+"',true);",2000);
}

function lz_tracking_load_style()
{
	lz_style.id = "livezilla_style";
	lz_style.href = "<!--server-->templates/style.css";
	lz_style.rel = 'stylesheet';
	lz_style.type = 'text/css';
	lz_document_head.appendChild(lz_style);
}

function lz_tracking_start_system()
{
	if(location.search.indexOf("lzcobrowse") != -1)
		return;
		
	lz_session = new lz_jssess();
	lz_session.Load();
	
	try
	{
		if(window.opener != null && typeof(window.opener.lz_get_session) != 'undefined')
		{
			lz_session.UserId = window.opener.lz_get_session().UserId;
			lz_session.GeoResolved = window.opener.lz_get_session().GeoResolved;
		}
	}
	catch(ex)
	{
		// ACCESS DENIED
	}
	
	lz_session.Save();
	
	if(!lz_tracking_geo_resolute())
		lz_tracking_poll_server();
}

function lz_get_session()
{
	return lz_session;
}

function lz_tracking_server_request(_get)
{	
	if(lz_stopped)
		return;
		
	var lastScript = document.getElementById("livezilla_pollscript");
	if(lastScript == null) 
	{
		for(var index in lz_chat_windows)
			if(!lz_chat_windows[index].Deleted && lz_chat_windows[index].Closed)
			{
				lz_chat_windows[index].Deleted = true;
				_get += "&clch=" + lz_chat_windows[index].BrowserId;
			}

		_get = "?request=track&start=" + lz_global_microstamp() + _get;
		
		var newScript = document.createElement("script");
		newScript.id = "livezilla_pollscript";
		newScript.src = lz_poll_url + _get;
		lz_document_head.appendChild(newScript);
	}
}

function lz_tracking_callback(_freq)
{
	if(lz_poll_frequency != _freq)
	{
		lz_poll_frequency = _freq;
		clearTimeout(lz_timer);
		lz_timer = setTimeout("lz_tracking_poll_server();",(lz_poll_frequency*1000));
	}
	var lastScript = document.getElementById("livezilla_pollscript");
	if(lastScript != null)
		lz_document_head.removeChild(lastScript);
}

function lz_tracking_poll_server()
{
	var getValues = "&browid="+lz_session.BrowserId+"&url="+lz_global_base64_url_encode(window.location.href);
	getValues += (lz_session.UserId != null) ? "&livezilla="+ lz_session.UserId : "";
	getValues += "&cd="+window.screen.colorDepth+"&rh="+screen.height+"&rw="+screen.width+"&rf="+lz_global_base64_url_encode(lz_referrer)+"&tzo="+lz_timezone_offset;
	getValues += "&code="+lz_area_code+"&en="+lz_user_name+"&ee="+lz_user_email+"&ec="+lz_user_company+"&dc="+lz_global_base64_url_encode(document.title);
		
	if(lz_user_customs.length>0)
		for(var i=0;i<=9;i++)
			getValues += "&cf" + i + "=" + lz_user_customs[i];
		
	if(lz_geo_resolution_needed && lz_session.GeoResolved.length == 7)
		getValues += "&geo_lat=" + lz_session.GeoResolved[0] + "&geo_long=" + lz_session.GeoResolved[1] + "&geo_region=" + lz_session.GeoResolved[2] + "&geo_city=" + lz_session.GeoResolved[3] + "&geo_tz=" + lz_session.GeoResolved[4] + "&geo_ctryiso=" + lz_session.GeoResolved[5] + "&geo_isp=" + lz_session.GeoResolved[6];

	getValues += "&geo_rid=" + lz_geo_resolution.Status;
	
	if(lz_geo_resolution.Span > 0)
		getValues += "&geo_ss=" + lz_geo_resolution.Span;
		
	++lz_poll_id;
		
	if(lz_request_active != null)
		getValues += "&actreq=1";
	
	lz_tracking_server_request(getValues,true);
	
	if(!lz_stopped)
	{
		clearTimeout(lz_timer);
		lz_timer = setTimeout("lz_tracking_poll_server();",(lz_poll_frequency*1000));
	}
}

function lz_tracking_set_sessid(_userId, _browId)
{
	lz_session.UserId = _userId;
	lz_session.BrowserId = _browId;
	lz_session.Save();
}

function lz_tracking_request_chat(_reqId,_text,_template,_width,_height,_mleft,_mtop,_mright,_mbottom,_position,_speed,_slide)
{
	if(lz_request_active == null)
	{
		lz_request_active = _reqId;
		_template = lz_global_utf8_decode(lz_global_base64_decode(_template)).replace("<!--invitation_text-->",lz_global_utf8_decode(lz_global_base64_decode(_text)));
		lz_request_window = new lz_livebox("lz_request_window",_template,_width,_height,_mleft,_mtop,_mright,_mbottom,_position,_speed,_slide);
		lz_request_window.lz_livebox_show();
		window.focus();
	}
}

function lz_tracking_send_alert(_alertId,_text)
{
	if(lz_alert_active == null)
	{
		lz_alert_active = _alertId;
		lz_alert_window = new lz_livebox("lz_alert_window",lz_global_utf8_decode(lz_global_base64_decode(lz_alert_html)),350,110,0,0,0,0,11,1,0);
		lz_alert_window.lz_livebox_show();
		
		document.getElementById("lz_chat_alert_box").style.display = 'inline';
		document.getElementById("lz_chat_alert_button").onclick = function(){if(lz_alert_window != null){document.body.removeChild(document.getElementById('lz_alert_window'));lz_alert_window=null;lz_tracking_action_result("alert",true,true);lz_alert_active=null;}};
		document.getElementById("lz_chat_alert_box_text").innerHTML = lz_global_utf8_decode(lz_global_base64_decode(_text));
		window.focus();
	}
}

function lz_tracking_check_request(_reqId)
{
	if(lz_request_window == null && lz_request_active != null)
		lz_tracking_declined_request();
}

function lz_tracking_close_request()
{
	if(lz_request_active != null)
		lz_request_active = null;
		
	if(lz_request_window != null)
		lz_request_window.lz_livebox_close('lz_request_window');	
}

function lz_tracking_init_website_push(_text,_id)
{	
	if(lz_website_push_active == null)
	{
		lz_website_push_active = _id;
		var exec = confirm(lz_global_utf8_decode(lz_global_base64_decode(_text)));
		setTimeout("lz_tracking_action_result('website_push',"+exec+",true);",100);
		lz_website_push_active = null;
	}
}

function lz_tracking_exec_website_push(_url)
{	
	window.location.href = lz_global_utf8_decode(lz_global_base64_decode(_url));
}

function lz_tracking_stop_tracking()
{
	lz_stopped = true;
}

function lz_tracking_geo_result(_lat,_long,_region,_city,_tz,_ctryi2,_isp)
{	
	lz_session.GeoResolved = Array(_lat,_long,_region,_city,_tz,_ctryi2,_isp);
	lz_session.Save();
	lz_tracking_poll_server();
}

function lz_tracking_set_geo_span(_timespan)
{
	lz_geo_resolution.SetSpan(_timespan);
}

function lz_tracking_geo_resolute()
{
	if(lz_is_geo_resolution_needed())
	{
		lz_session.GeoResolutions++;
		lz_session.Save();

		lz_geo_resolution.SetStatus(1);
		if(lz_session.GeoResolutions < 4)
		{
			lz_geo_resolution.OnEndEvent = "lz_tracking_geo_result";
			lz_geo_resolution.OnSpanEvent = "lz_tracking_set_geo_span";
			lz_geo_resolution.OnTimeoutEvent = lz_tracking_geo_resolute;
			lz_geo_resolution.ResolveAsync();
		}
		else
			lz_tracking_geo_failure();
		return true;
	}
	else
	{
		lz_geo_resolution.SetStatus(7);
		return false;
	}
}

function lz_tracking_geo_failure()
{
	lz_tracking_set_geo_span(<!--connection_error_span-->);
	lz_geo_resolution.SetStatus(4);
	lz_session.GeoResolved = Array('LTUyMg==','LTUyMg==','','','','','');
	lz_session.Save();
	lz_tracking_poll_server();
}

function lz_tracking_action_result(_action,_result,_closeOnClick)
{
	var getValues = "&browid="+lz_session.BrowserId+"&url="+lz_global_base64_url_encode(window.location.href);
	getValues += (lz_session.UserId != null) ? "&livezilla="+lz_session.UserId : "";
	
	if(_action=="alert")
		getValues += "&confalert="+lz_alert_active;
	else if(_action=="chat_request" && !_result)
		getValues += "&decreq="+lz_request_active;
	else if(_action=="chat_request" && _result)
		getValues += "&accreq="+lz_request_active;
	else if(_action=="website_push" && _result)
		getValues += "&accwp="+lz_website_push_active;
	else
		getValues += "&decwp="+lz_website_push_active;
		
	if(_closeOnClick)
		getValues += "&clreq=1";

	lz_tracking_server_request(getValues);
}

function lz_tracking_add_floating_button(_pos,_sh,_shblur,_shx,_shy,_shcolor,_ml,_mt,_mr,_mb,_width,_height)
{
	var fbdiv = document.getElementById("chat_button_image");
	lz_floating_button = new lz_livebox("lz_floating_button",fbdiv.parentNode.parentNode.innerHTML,_width,_height,_ml,_mt,_mr,_mb,_pos,0,false);
	if(_sh)
		lz_floating_button.lz_livebox_shadow(_shblur,_shx,_shy,'#'+_shcolor);
	lz_floating_button.lz_livebox_show();
}