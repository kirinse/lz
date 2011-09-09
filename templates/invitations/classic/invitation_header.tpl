<table cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;border:0px;background-image:url('<!--server-->templates/invitations/classic/background_header.gif');background-repeat:no-repeat;background-position:center left;-moz-box-shadow: 5px 5px 5px #ccc;-webkit-box-shadow: 5px 5px 5px #ccc;box-shadow: 5px 5px 5px #ccc;" width="302" height="302" border="0">
	<tr>
		<td align="center" valign="top" style="margin:0px;padding:0px;border:0px;">
			<table cellspacing="0" cellpadding="0" style="margin:0px;padding:0px;border:0px;">
				<tr>
					<td height="28" style="margin:0px;padding:0px;border:0px;">&nbsp;</td>
				</tr>
				<tr>
					<td align="center" height="80" style="margin:0px;padding:0px;border:0px;"><img src="<!--server-->images/invitation_logo.gif" border="0"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<div style="position:absolute;left:15px;top:9px;font-family:verdana,arial;font-size:10px;font-weight:bold;color:#797979;"><!--lang_client_chat_invitation--></div>
<div style="position:absolute;left:10px;top:120px;"><img alt="<!--intern_name-->" src="<!--server--><!--intern_image-->" border="0"></div>
<div style="width:183px;height:87px;position:absolute;left:106px;top:123px;font-family:verdana,arial;font-size:10px;color:black;line-height:12px;text-align:left;"><B><!--intern_name--></B>:<br><!--invitation_text--></div>
<input type="text" value="<!--username-->" id="lz_invitation_name" maxlength="25" style="position:absolute;left:11px;top:262px;background-image:url('<!--server-->templates/invitations/<!--template-->/textbox.gif');background-repeat:no-repeat;border:0px;height:20px;width:164px;font-size:11px;color:#707070;padding-left:3px;line-height:18px;">
<div onclick="lz_request_window.lz_livebox_chat('<!--user_id-->','<!--group_id-->');lz_tracking_action_result('chat_request',true,<!--close_on_click-->);" style="text-align:center;width:105px;height:15px;border:1px;padding:3px;position:absolute;left:185px;top:263px;cursor:pointer;font-family:verdana,arial;font-size:10px;color:#585858;line-height:12px;"><!--lang_client_start_chat--></div>
<div style="position:absolute;left:12px;top:247px;font-family:verdana,arial;font-size:10px;color:#9f9f9f;"><!--lang_client_your_name-->:</div>
<div style="width:25px;position:absolute;left:272px;top:5px;cursor:pointer;" onclick="lz_request_window.lz_livebox_close('lz_request_window');top.lz_tracking_action_result('chat_request',false,true);return false;">&nbsp;</div>