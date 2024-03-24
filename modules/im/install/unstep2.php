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

\CAdminMessage::ShowNote(Loc::getMessage("MOD_UNINST_OK"));
?>
<?=BeginNote()?>
<div style="font-size: 12px; color: #000;"><?= Loc::getMessage("MOD_UNINST_DELETE_FROM_TEMPLATE")?></div>
<div style="font-size: 12px; color: #000; font-weight:bold">&lt;?$APPLICATION-&gt;IncludeComponent(&quot;bitrix:im.messenger&quot;, &quot;&quot;, Array(), null, array(&quot;HIDE_ICONS&quot; => &quot;Y&quot;));?&gt;</div>
<?=EndNote()?>
<br>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?= LANG?>">
	<input type="submit" name="" value="<?= Loc::getMessage("MOD_BACK")?>">
</form>