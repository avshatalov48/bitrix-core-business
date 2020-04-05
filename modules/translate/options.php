<?
/** @global CMain $APPLICATION */
/** @global string $mid */
$module_id = "translate";

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");
IncludeModuleLangFile(__FILE__);

$TRANS_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($TRANS_RIGHT < "R")
	return;

if ($_SERVER["REQUEST_METHOD"] == "GET" && $TRANS_RIGHT=="W" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
{
	COption::RemoveOption("translate");
	$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}

$arAllOptions = array(
	array("INIT_FOLDERS", GetMessage("TRANS_INIT_FOLDERS"), "/bitrix/", array("text", 50)),
	array("AUTO_CALCULATE", GetMessage("TRANS_AUTO_CALCULATE"), "N", array("checkbox")),
	array("ONLY_ERRORS", GetMessage("TRANS_SHOW_ONLY_ERRORS"), "Y", array("checkbox")),
	array("BUTTON_LANG_FILES", GetMessage("TRANS_BUTTON_LANG_FILES"), "N", array("checkbox")),
	array("BACKUP_FILES", GetMessage("TRANS_BACKUP_FILES"), "N", array("checkbox")),
	);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "translate_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "translate_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && $TRANS_RIGHT=="W" && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption("translate");
		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}
	else
	{
		foreach($arAllOptions as $option)
		{
			if(!is_array($option))
				continue;

			$name = $option[0];
			if (!isset($_POST[$name]) && $option[3][0] != "checkbox")
				continue;

			if ($option[3][0] == "multiselectbox")
			{
				if (!is_array($_POST[$name]))
					continue;
				$val = implode(",", $_POST[$name]);
			}
			else
			{
				$val = (isset($_POST[$name]) ? (string)$_POST[$name] : '');
				if($option[3][0] == "checkbox" && $val != "Y")
					$val = "N";
			}

			COption::SetOptionString($module_id, $name, $val);
		}
		unset($option);
	}

	$Update = $Update.$Apply;
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	ob_end_clean();

	if(strlen($_REQUEST["back_url_settings"]) > 0)
	{
		if((strlen($Apply) > 0) || (strlen($RestoreDefaults) > 0))
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".LANGUAGE_ID."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
	}
}

$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&amp;lang=<?=LANGUAGE_ID?>"><?
$tabControl->BeginNextTab();
__AdmSettingsDrawList("translate", $arAllOptions);
$tabControl->BeginNextTab();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
$tabControl->Buttons();?>
	<input <?if ($TRANS_RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input <?if ($TRANS_RIGHT<"W") echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input <?if ($TRANS_RIGHT<"W") echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input <?if ($TRANS_RIGHT<"W") echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>