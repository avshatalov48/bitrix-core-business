<?php
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
if(!check_bitrix_sessid()) return;

global $errors;

if (empty($errors))
{
	CAdminMessage::ShowNote(GetMessage('MOD_INST_OK'));
}
else
{
	CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('MOD_INST_ERR'),
		'DETAILS' => implode('<br>', $errors),
		'HTML' => true,
	]);
}
?><form action="<?= $APPLICATION->GetCurPage() ?>">
<p>
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= htmlspecialcharsbx(GetMessage('MOD_BACK')) ?>">
</p>
<form>