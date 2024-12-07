<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!check_bitrix_sessid())
{
	return;
}

use Bitrix\Main\Localization\Loc;

global $errors;

if (!is_array($errors) && $errors == '' || is_array($errors) && count($errors) <= 0)
{
	CAdminMessage::ShowNote(Loc::getMessage('MOD_INST_OK'));
}
else
{
	$alErrors = "";
	for($i=0; $i<count($errors); $i++)
	{
		$alErrors .= $errors[$i]."<br>";
	}
	\CAdminMessage::ShowMessage([
		'TYPE' => "ERROR",
		'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
		'DETAILS' => $alErrors,
		'HTML' => true
	]);
}
if ($ex = $APPLICATION->GetException())
{
	\CAdminMessage::ShowMessage([
		'TYPE' => "ERROR",
		'MESSAGE' => Loc::getMessage("MOD_INST_ERR"),
		'HTML' => true,
		'DETAILS' => $ex->GetString()
	]);
}
?>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?= LANG?>">
	<input type="submit" name="" value="<?= Loc::getMessage("MOD_BACK")?>">
</form>