<?php

if (!check_bitrix_sessid())
{
	return;
}

/**
 * @global CMain $APPLICATION
 */

global $errors;

if ($errors === false):
	CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
else:
	CAdminMessage::ShowMessage([
		"TYPE"=>"ERROR",
		"MESSAGE" =>GetMessage("MOD_INST_ERR"),
		"DETAILS" => $errors,
		"HTML" => true,
	]);
endif;
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= htmlspecialcharsbx(GetMessage("MOD_BACK")) ?>">
<form>