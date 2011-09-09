<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<META NAME="robots" CONTENT="index,follow">
	<title><!--config_gl_site_name--></title>
	<link rel="stylesheet" type="text/css" href="./templates/style.css">
</head>
<body leftmargin="0" topmargin="0" id="lz_form_floor" onload="top.lz_ticket_change_group(document.getElementById('lz_ticket_form_groups'));<!--direct_send-->">
	<!--alert-->
	<div class="lz_chat_navigation">
		<table width="100%" height="100%" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td width="10"></td>
				<td id="lz_chat_site_title">&nbsp;&nbsp;<!--config_gl_site_name--></td>
				<td align="right">
					<table cellspacing="0" cellpadding="0">
						<tr>
							<td><img src="./images/chat_bg_navigation_left.gif" alt="" width="10" height="30" border="0"></td>
							<td><img class="lz_chat_clickable_image" onclick="top.lz_chat_mail_print();" src="./images/button_print.gif" border="0" title="<!--lang_client_print-->" alt=""></td>
							<td><img src="./images/chat_bg_navigation_dev.gif" alt="" width="10" height="30" border="0"></td>
							<td><img class="lz_chat_clickable_image" onclick="top.close();" src="./images/button_close.gif" border="0" title="<!--lang_client_close_window-->" alt=""></td>
							<td><img src="./images/chat_bg_navigation_right.gif" alt="" width="10" height="30" border="0"></td>
						</tr>
					</table>
				</td>
				<td width="5"></td>
			</tr>
		</table>
	</div>
	<div id="lz_chat_navigation_sub"></div>
	<div id="lz_chat_loading"><br><br><br><br><!--lang_client_loading--> ...</div>
	<br>
	<form name="lz_login_form" method="post" target="lz_chat_frame.3.2" style="padding:0px;margin:0px;">
	<table align="center" cellpadding="0" cellspacing="0" width="100%" id="lz_ticket_elements" style="display:none;">
		<tr>
			<td align="center" valign="top">	
				<table cellpadding="0" id="lz_chat_ticket_header" cellspacing="0" class="lz_input">
					<tr>
						<td class="lz_input_header"><strong><!--lang_client_ticket_header--></strong><br><div id="lz_form_info_field"><!--lang_client_ticket_information--></div></td>
					</tr>
				</table>
				<div id="lz_chat_mail_values"><!--login_trap--></div>
				<div id="lz_chat_ticket_details">
					<!--ticket_inputs-->
					<table cellpadding="0" cellspacing="0" class="lz_input" style="<!--group_select_visibility-->">
						<tr>
							<td class="lz_form_field"><!--lang_client_group-->:</td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td valign="middle">
								<table cellpadding="0" cellspacing="0">
									<tr>
										<td><select id="lz_ticket_form_groups" class="lz_input_groups" name="intgroup" onChange="top.lz_ticket_change_group(this);"><!--groups--></select></td>
										
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<table cellpadding="2" cellspacing="2" style="display:block;width:410px;">
						<tr>
							<td class="lz_form_field_empty">&nbsp;</td>
							<td><input type="button" onclick="top.lz_chat_check_ticket_inputs();" id="lz_chat_ticket_button" value="<!--lang_client_send_message-->"></td>
						</tr>
						<tr>
							<td class="lz_form_field_empty">&nbsp;</td>
							<td><span class="lz_index_red" id="lz_form_mandatory" style="display:none;">* <!--lang_client_required_field--></span></td>
						</tr>
					</table>
				</div>
				<div id="lz_chat_ticket_success" style="display:none;"><br><br><br><br><br><br><br><br><b><!--lang_client_message_received--></b></div>
			</td>
		</tr>
	</table>
	</form>
</body>
</html>
