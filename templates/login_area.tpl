<table cellpadding="0" cellspacing="0" id="lz_form_<!--name-->" class="lz_input">
	<tr>
		<td id="lz_form_caption_<!--name-->" class="lz_form_field" valign="top"><!--caption--></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><textarea name="form_<!--name-->" class="lz_form_area" onchange="imposeMaxLength(this, <!--maxlength-->);" onkeyup="imposeMaxLength(this, <!--maxlength-->);"><!--login_value_<!--name-->--></textarea></td>
					<td width="15" align="right"><span class="lz_index_red" id="lz_form_mandatory_<!--name-->" style="display:none;">*</span></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
function imposeMaxLength(_object, _max)
{
	_object.value = _object.value.substring(0,_max);
}
</script>