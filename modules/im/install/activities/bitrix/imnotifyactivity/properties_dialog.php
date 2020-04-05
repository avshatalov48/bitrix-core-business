<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_TO") ?>:</span></td>
	<td width="60%">
		<?
		if ($user->isAdmin())
		{
			echo CBPDocument::ShowParameterField("user", 'from_user_id', $arCurrentValues['from_user_id'], Array('rows'=> 1));
		}
		else
		{
			echo $user->GetFullName();
		}
		?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_FROM") ?>:</span></td>
	<td width="60%">
			<input type="text" name="to_user_id" id="id_to_user_id" value="<?= htmlspecialcharsbx($arCurrentValues["to_user_id"]) ?>" size="50">
			<input type="button" value="..." onclick="BPAShowSelector('id_to_user_id', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_MESSAGE") ?>:</span></td>
	<td width="60%" valign="top">
		<textarea name="message_site" id="id_message_site" rows="4" cols="40"><?= htmlspecialcharsbx($arCurrentValues["message_site"]) ?></textarea>
		<input style="vertical-align: top" type="button" value="..." onclick="BPAShowSelector('id_message_site', 'string');"><br/>
		<?= GetMessage("BPIMNA_PD_MESSAGE_BBCODE") ?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_MESSAGE_OUT") ?>:</span></td>
	<td width="60%" valign="top">
		<textarea name="message_out" id="id_message_out" rows="4" cols="40"><?= htmlspecialcharsbx($arCurrentValues["message_out"]) ?></textarea>
		<input style="vertical-align: top" type="button" value="..." onclick="BPAShowSelector('id_message_out', 'string');"><br/>
		<?= GetMessage("BPIMNA_PD_MESSAGE_OUT_EMPTY") ?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_NOTIFY_TYPE") ?>:</span></td>
	<td width="60%">
		<?=InputType("radio", "message_type", "2", $arCurrentValues["message_type"], false, "&nbsp;".GetMessage("BPIMNA_PD_NOTIFY_TYPE_FROM"))?><br/>
		<?=InputType("radio", "message_type", "4", $arCurrentValues["message_type"], false, "&nbsp;".GetMessage("BPIMNA_PD_NOTIFY_TYPE_SYSTEM"))?>
	</td>
</tr>