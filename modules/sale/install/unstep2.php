<?php

/** @global CMain $APPLICATION */

if (!check_bitrix_sessid())
{
	return;
}

global $errors;

if (empty($errors)):
	CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
else:
	$alErrors = implode('<br>', $errors);

	CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('MOD_UNINST_ERR'),
		'DETAILS' => $alErrors,
		'HTML' => true,
	]);
endif;

$ex = $APPLICATION->GetException();
if ($ex)
{
	CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('MOD_UNINST_ERR'),
		'HTML' => true,
		'DETAILS' => $ex->GetString(),
	]);
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= htmlspecialcharsbx(GetMessage('MOD_BACK')) ?>">
<form>