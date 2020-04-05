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
		<?=CBPDocument::ShowParameterField("user", 'to_user_id', $arCurrentValues['to_user_id'], ['rows'=> 1]);?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_MESSAGE") ?>:</span></td>
	<td width="60%" valign="top">
		<?=CBPDocument::ShowParameterField("text", 'message_site', $arCurrentValues['message_site'], ['rows'=> 4, 'cols' => 40])?>
		<?= GetMessage("BPIMNA_PD_MESSAGE_BBCODE") ?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPIMNA_PD_MESSAGE_OUT") ?>:</span></td>
	<td width="60%" valign="top">
		<?=CBPDocument::ShowParameterField("text", 'message_out', $arCurrentValues['message_out'], ['rows'=> 4, 'cols' => 40])?>
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