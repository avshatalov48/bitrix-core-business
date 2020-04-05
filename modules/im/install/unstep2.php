<?
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);
global $errors;

echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
?>
<?=BeginNote()?>
<div style="font-size: 12px; color: #000;"><?echo GetMessage("MOD_UNINST_DELETE_FROM_TEMPLATE")?></div>
<div style="font-size: 12px; color: #000; font-weight:bold">&lt;?$APPLICATION-&gt;IncludeComponent(&quot;bitrix:im.messenger&quot;, &quot;&quot;, Array(), null, array(&quot;HIDE_ICONS&quot; => &quot;Y&quot;));?&gt;</div>
<?=EndNote()?>
<br>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>