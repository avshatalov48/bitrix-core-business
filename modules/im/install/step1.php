<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\check_bitrix_sessid())
{
	return;
}

use Bitrix\Main\Localization\Loc;

if ($ex = $APPLICATION->GetException())
{
	\CAdminMessage::ShowMessage([
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	]);
}
else
{
	\CAdminMessage::ShowNote(Loc::getMessage("MOD_INST_OK"));
}
?>
<?=BeginNote()?>
<div style="font-size: 12px; color: #000"><?=Loc::getMessage('IM_MODULE_MAN_INSTALL')?></div>
<div style="font-size: 12px; color: #000; font-weight:bold">&lt;?$APPLICATION-&gt;IncludeComponent(&quot;bitrix:im.messenger&quot;, &quot;&quot;, Array(), null, array(&quot;HIDE_ICONS&quot; => &quot;Y&quot;));?&gt;</div>
<?=EndNote()?>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?= LANG?>">
	<input type="submit" name="" value="<?= Loc::getMessage("MOD_BACK")?>">
</form>
