<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

?>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<input type="hidden" name="id" value="translate">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?
	\CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"));
	?>
	<p><?= Loc::getMessage("MOD_UNINST_SAVE")?></p>

	<p>
		<label>
			<input type="checkbox" name="savedata" value="Y" checked="checked">
			<?= Loc::getMessage("MOD_UNINST_SAVE_TABLES") ?>
		</label>
	</p>
	<input type="submit" value="<?= Loc::getMessage("MOD_UNINST_DEL") ?>">
</form>
<?
