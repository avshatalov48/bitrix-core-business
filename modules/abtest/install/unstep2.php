<?php

if(!check_bitrix_sessid()) return;

IncludeModuleLangFile(__FILE__);

global $errors;

echo CAdminMessage::ShowNote(GetMessage('MOD_UNINST_OK'));

?>

<br>
<form action="<?=$APPLICATION->GetCurPage(); ?>">
	<input type="hidden" name="lang" value="<?=LANG; ?>">
	<input type="submit" name="" value="<?=GetMessage('MOD_BACK'); ?>">
</form>
