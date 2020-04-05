<?
$allow_initial = false;
if (!$DB->Query("SELECT count('x') FROM b_stat_day", true))	$allow_initial = "Y";
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="id" value="statistic">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
<input type="hidden" name="allow_initial" value="<?=$allow_initial?>">
<table border="0" cellspacing="1" cellpadding="3" class="list-table">
	<tr class="head">
		<td align="center"><?=GetMessage("MAIN_PARAMETER_NAME")?></td>
		<td align="center"><?=GetMessage("MAIN_PARAMETER_VALUE")?></td>
	</tr>
	<tr>
		<td><label for="CREATE_I2C_INDEX"><?=GetMessage("STAT_CREATE_I2C_DB")?></label></td>
		<td><input type="checkbox" name="CREATE_I2C_INDEX" id="CREATE_I2C_INDEX" value="Y"></td>
	</tr>
	<? if ($allow_initial == "Y") : ?>
	<tr>
		<td><?=GetMessage("STAT_START_HITS")?></td>
		<td><input type="text" name="START_HITS" value="0" size="5"></td>
	</tr>
	<tr>
		<td><?=GetMessage("STAT_START_HOSTS")?></td>
		<td><input type="text" name="START_HOSTS" value="0" size="5"></td>
	</tr>
	<tr>
		<td><?=GetMessage("STAT_START_GUESTS")?></td>
		<td><input type="text" name="START_GUESTS" value="0" size="5"></td>
	</tr>
	<? endif; ?>
</table><br>
<?if(CModule::IncludeModule('cluster')):?>
<p><?echo GetMessage("STAT_INSTALL_DATABASE")?><select name="DATABASE">
	<option value=""><?echo GetMessage("STAT_MAIN_DATABASE")?></option><?
	$rsDBNodes = CClusterDBNode::GetListForModuleInstall();
	while($arDBNode = $rsDBNodes->Fetch()):
	?><option value="<?echo $arDBNode["ID"]?>"><?echo htmlspecialcharsbx($arDBNode["NAME"])?></option><?
	endwhile;
	?></select></p>
<br>
<?endif;?>
<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL")?>">
</form>