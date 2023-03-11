<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSNMA_PD_FROM") ?>:</td>
	<td width="60%">
		<?
		if ($user->isAdmin())
		{
			echo CBPDocument::ShowParameterField("user", 'message_user_from', $arCurrentValues['message_user_from'], Array('rows'=> 1));
		}
		else
		{
			echo $user->getFullName();
		}
		?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_TO") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'message_user_to', $arCurrentValues['message_user_to'], Array('rows'=> 2));?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_MESSAGE") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'message_text', $arCurrentValues['message_text'], Array('rows'=> 7))?>
	</td>
	<input type="hidden" name="message_format" value="<?=htmlspecialcharsbx($arCurrentValues['message_format'] ?? '')?>">
</tr>