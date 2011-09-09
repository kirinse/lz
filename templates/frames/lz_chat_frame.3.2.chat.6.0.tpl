<html>
<head>
	<META NAME="robots" CONTENT="index,follow">
	<link rel="stylesheet" type="text/css" href="./templates/style.css">
	<!--translator_api-->
</head>
<body>
	<div id="lz_chat_misc">
		<table cellspacing="1" cellpadding="0" style="display:<!--translation_display-->;">
			<tr>
				<td NOWRAP><input id="lz_translation_service_active" type="checkbox" onclick="document.getElementById('lz_chat_translation_target_language').disabled=!this.checked;">&nbsp;</td>
				<td NOWRAP><!--lang_client_use_auto_translation_service-->&nbsp;</td>
				<td NOWRAP><select id="lz_chat_translation_target_language" DISABLED><!--languages--></select>&nbsp;</td>
			</tr>
		</table>
		<table cellspacing="1" cellpadding="0" style="display:<!--transcript_option_display-->;">
			<tr>
				<td NOWRAP><input id="lz_chat_send_chat_transcript" type="checkbox" value="" onclick="document.getElementById('lz_chat_transcript_email').disabled=!this.checked;">&nbsp;</td>
				<td NOWRAP><!--lang_client_request_chat_transcript-->&nbsp;&nbsp;</td>
				<td NOWRAP><input type="text" id="lz_chat_transcript_email">&nbsp;</td>
			</tr>
		</table>
		<br><br><br>
	</div>
</body>
</html>
