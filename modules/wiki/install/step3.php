<?if(!check_bitrix_sessid()) return;?>
<?
if (!empty($GLOBALS['errors']))
	echo CAdminMessage::ShowMessage($GLOBALS['errors']);
else
	echo CAdminMessage::ShowNote(GetMessage('MOD_INST_OK'));
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage('MOD_BACK')?>">	
<form>