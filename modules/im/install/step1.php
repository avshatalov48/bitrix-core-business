<?
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

if($ex = $APPLICATION->GetException())
	echo CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<?=BeginNote()?>
<div style="font-size: 12px; color: #000"><?=GetMessage('IM_MODULE_MAN_INSTALL')?></div>
<div style="font-size: 12px; color: #000; font-weight:bold">&lt;?$APPLICATION-&gt;IncludeComponent(&quot;bitrix:im.messenger&quot;, &quot;&quot;, Array(), null, array(&quot;HIDE_ICONS&quot; => &quot;Y&quot;));?&gt;</div>
<?=EndNote()?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>
