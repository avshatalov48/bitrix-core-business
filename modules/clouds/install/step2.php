<?php
/* @var CMain $APPLICATION */

if (!check_bitrix_sessid())
{
	return;
}
IncludeModuleLangFile(__FILE__);

if ($ex = $APPLICATION->GetException())
{
	CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('MOD_INST_ERR'),
		'DETAILS' => $ex->GetString(),
		'HTML' => true,
	]);
}
else
{
	CAdminMessage::ShowNote(GetMessage('MOD_INST_OK'));
}
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?php echo LANG?>">
	<input type="submit" name="" value="<?php echo GetMessage('MOD_BACK')?>">
<form>
