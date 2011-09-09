<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<META NAME="robots" CONTENT="index,follow">
	<title><!--config_gl_site_name--></title>
	<link rel="stylesheet" type="text/css" href="./templates/style.css">
</head>
<body onload="top.lz_chat_file_reset();<!--response-->" id="lz_chat_body_chat_function">
	<div id="lz_chat_file_frame">
		<span id="lz_chat_file_title"><!--lang_client_file-->:</span>
		<input type="text" id="lz_chat_file_name" readonly>
		<input type="button" id="lz_chat_file_select" value="<!--lang_client_file-->">
		<form action="./<!--file_chat-->?template=lz_chat_frame.3.2.chat.1.0&file=true" method="post" enctype="multipart/form-data" name="lz_file_form">
			<input type="hidden" name="p_request" value="extern">
			<input type="hidden" name="p_action" value="file_upload">
			<input type="hidden" id="lz_chat_upload_form_userid" name="p_extern_userid">
			<input type="hidden" id="lz_chat_upload_form_browser" name="p_extern_browserid">
			<input type="file" name="userfile" id="lz_chat_file_base" onchange="top.lz_chat_file_changed();">
		</form>
		<input type="button" id="lz_chat_file_send" value="<!--lang_client_send-->" onclick="top.lz_chat_file_request_upload();">
		<img id="lz_chat_file_load" src="./images/lz_circle.gif" alt="" width="26" height="27" border="0">
		<img id="lz_chat_file_success" src="./images/icon_file_upload_success.gif" alt="" width="35" height="26" border="0">
		<img id="lz_chat_file_error" src="./images/icon_file_upload_error.gif" alt="" width="35" height="26" border="0">
		<div id="lz_chat_file_status"></div>	
	</div>
</body>
</html>
