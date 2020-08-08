<?
$module_id = "clouds";
$RIGHT_R = $USER->CanDoOperation('clouds_config');
$RIGHT_W = $USER->CanDoOperation('clouds_config');
if($RIGHT_R || $RIGHT_W) :

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = array(
	array("log_404_errors", GetMessage("CLO_OPTIONS_LOG_404_ERRORS"), array("checkbox")),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "clouds_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "clouds_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

CModule::IncludeModule($module_id);

if($REQUEST_METHOD=="POST" && $Update.$Apply.$RestoreDefaults <> '' && $RIGHT_W && check_bitrix_sessid())
{

	if($RestoreDefaults <> '')
	{
		COption::RemoveOption($module_id);
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			$val = trim($_REQUEST[$name], " \t\n\r");
			if($arOption[2][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString($module_id, $name, $val, $arOption[1]);
		}
	}

	ob_start();
	$Update = $Update.$Apply;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights2.php");
	ob_end_clean();

	if($_REQUEST["back_url_settings"] <> '')
	{
		if(($Apply <> '') || ($RestoreDefaults <> ''))
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
	}
}

?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();

	foreach($arAllOptions as $arOption):
		$val = COption::GetOptionString($module_id, $arOption[0]);
		$type = $arOption[2];
	?>
	<tr>
		<td width="40%" nowrap <?if($type[0]=="textarea") echo 'class="adm-detail-valign-top"'?>>
			<label for="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo $arOption[1]?></label>
		<td width="60%">
			<?if($type[0]=="checkbox"):?>
				<input type="checkbox" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
			<?elseif($type[0]=="text"):?>
				<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>">
			<?elseif($type[0]=="textarea"):?>
				<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
			<?elseif($type[0]=="selectbox"):
				?><select name="<?echo htmlspecialcharsbx($arOption[0])?>"><?
				foreach ($type[1] as $key => $value)
				{
					?><option value="<?echo $key?>"<?if($val==$key)echo" selected"?>><?echo htmlspecialcharsbx($value)?></option><?
				}
				?></select><?
			endif?>
		</td>
	</tr>
	<?endforeach?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights2.php");?>
<?$tabControl->Buttons();?>
	<input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input <?if(!$RIGHT_W) echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>
