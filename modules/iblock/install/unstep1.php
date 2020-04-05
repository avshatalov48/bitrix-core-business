<?php

IncludeModuleLangFile(__FILE__);

foreach(GetModuleEvents('iblock', 'OnModuleUnInstall', true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

?>

<? if ($ex = $APPLICATION->GetException()) { ?>
	<form action="<?=$APPLICATION->GetCurPage(); ?>">
		<? $message = new CAdminMessage(GetMessage('MOD_UNINST_IMPOSSIBLE'), $ex); ?>
		<?=$message->show(); ?>
		<input type="hidden" name="lang" value="<?=LANG; ?>">
		<input type="submit" name="inst" value="<?=GetMessage('MOD_UNINST_BACK'); ?>">
	<form>
<? } else { ?>
	<form action="<?=$APPLICATION->GetCurPage(); ?>">
		<?=bitrix_sessid_post(); ?>
		<input type="hidden" name="lang" value="<?=LANG; ?>">
		<input type="hidden" name="id" value="iblock">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<?=CAdminMessage::ShowMessage(GetMessage('MOD_UNINST_WARN')); ?>
		<p><?=GetMessage('MOD_UNINST_SAVE'); ?></p>
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?=GetMessage('MOD_UNINST_SAVE_TABLES'); ?></label></p>
		<input type="submit" name="inst" value="<?=GetMessage('MOD_UNINST_DEL'); ?>">
	</form>
<? } ?>