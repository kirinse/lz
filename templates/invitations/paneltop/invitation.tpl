<table cellspacing="0" cellpadding="0" style="background-image:url('<!--server-->templates/invitations/<!--template-->/background.gif');background-repeat:no-repeat;background-position:center left;-moz-box-shadow: 5px 5px 5px #ccc;-webkit-box-shadow: 5px 5px 5px #ccc;box-shadow: 5px 5px 5px #ccc;" width="450" height="31" border="0">
	<tr>
		<td>
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td style="width:42px;height:31px;"></td>
					<td style="width:275px;font-family:arial,verdana;font-size:12px;font-weight:bold;color:#525252;"><!--invitation_text--></td>
					<td valign="middle" style="text-align:center;width:92px;cursor:pointer;font-family:arial,verdana;font-size:12px;color:#525252;" onclick="lz_request_window.lz_livebox_chat('<!--user_id-->','<!--group_id-->');lz_tracking_action_result('chat_request',true,<!--close_on_click-->);"><!--lang_client_start_chat--></td>
					<td style="width:7px;"></td>
					<td style="width:22px;cursor:pointer;" onclick="lz_request_window.lz_livebox_close('lz_request_window');top.lz_tracking_action_result('chat_request',false,<!--close_on_click-->);return false;">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>