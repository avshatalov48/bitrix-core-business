<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global string $mid */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

if(!$USER->IsAdmin())
	return;

if (!Loader::includeModule('iblock'))
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = array(
	GetMessage('IBLOCK_OPTION_SECTION_SYSTEM'),
	array("property_features_enabled", GetMessage("IBLOCK_PROPERTY_FEATURES"), "Y", array("checkbox", "Y")),
	array("event_log_iblock", GetMessage("IBLOCK_EVENT_LOG"), "Y", array("checkbox", "Y")),
	array("path2rss", GetMessage("IBLOCK_PATH2RSS"), "/upload/", array("text", 30)),
	GetMessage('IBLOCK_OPTION_SECTION_LIST_AND_FORM'),
	array("use_htmledit", GetMessage("IBLOCK_USE_HTMLEDIT"), "N", array("checkbox", "Y")),
	array("list_image_size", GetMessage("IBLOCK_LIST_IMAGE_SIZE"), "50", array("text", 5)),
	array("detail_image_size", GetMessage("IBLOCK_DETAIL_IMAGE_SIZE"), "200", array("text", 5)),
	array("show_xml_id", GetMessage("IBLOCK_SHOW_LOADING_CODE"), "N", array("checkbox", "Y")),
	array("list_full_date_edit", GetMessage("IBLOCK_LIST_FULL_DATE_EDIT"), "N", array("checkbox", "Y")),
	array("combined_list_mode", GetMessage("IBLOCK_COMBINED_LIST_MODE"), "N", array("checkbox", "Y")),
	array("iblock_menu_max_sections", GetMessage("IBLOCK_MENU_MAX_SECTIONS"), "50", array("text", 5)),
	GetMessage('IBLOCK_OPTION_SECTION_CUSTOM_FORM'),
	array("custom_edit_form_use_property_id", GetMessage("IBLOCK_CUSTOM_FORM_USE_PROPERTY_ID"), "Y", array("checkbox", "Y")),
	GetMessage('IBLOCK_OPTION_SECTION_IMPORT_EXPORT'),
	array("num_catalog_levels", GetMessage("IBLOCK_NUM_CATALOG_LEVELS"), "3", array("text", 5)),
);
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption("iblock");
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			if (!is_array($arOption))
				continue;
			$name=$arOption[0];
			if (!isset($_REQUEST[$name]))
				continue;
			$val=$_REQUEST[$name];
			if($arOption[3][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString("iblock", $name, $val);
		}
		unset($arOption);
	}
	if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".LANGUAGE_ID."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}

$currentValues = [];
foreach($arAllOptions as $option)
{
	if (!is_array($option))
		continue;
	$id = $option[0];
	$currentValues[$id] = (string)Main\Config\Option::get('iblock', $id);
}
unset($id, $option);

$needFeatureConfirm = false;
if ($currentValues['property_features_enabled'] == 'N')
	$needFeatureConfirm = !Iblock\Model\PropertyFeature::isPropertyFeaturesExist();

$tabControl->Begin();
?><form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?echo LANGUAGE_ID?>"><?
$tabControl->BeginNextTab();
foreach($arAllOptions as $arOption)
{
	if (!is_array($arOption))
	{
		?><tr class="heading"><td colspan="2"><?=htmlspecialcharsbx($arOption); ?></td></tr><?
	}
	else
	{
		$id = $arOption[0];
		$val = $currentValues[$id];
		$type = $arOption[3];
		$controlId = htmlspecialcharsbx($id);
		?>
		<tr>
			<td width="40%" nowrap <? if ($type[0] == "textarea") echo 'class="adm-detail-valign-top"' ?>>
				<?
				if ($id == 'property_features_enabled')
				{
					$message = GetMessage(
						'IBLOCK_PROPERTY_FEATURES_HINT',
						['#LINK#' => 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=42&LESSON_ID=1986']
					);
					?><span id="hint_<?= $controlId; ?>"></span>
					<script type="text/javascript">BX.hint_replace(BX('hint_<?=$controlId;?>'), '<?=\CUtil::JSEscape($message); ?>');</script>&nbsp;<?
					unset($message);
				}
				?><label for="<?=$controlId; ?>"><?=htmlspecialcharsbx($arOption[1]); ?></label>
			<td width="60%">
			<?
			switch ($type[0])
			{
				case "checkbox":
					?><input type="hidden" name="<?=$controlId; ?>" value="N">
					<input type="checkbox" id="<?=$controlId; ?>" name="<?=$controlId; ?>" value="Y"<?=($val == "Y" ? " checked" : ""); ?>><?
					break;
				case "text":
					?><input type="text" id="<?=$controlId; ?>" name="<?=$controlId; ?>" value="<?=htmlspecialcharsbx($val); ?>" size="<?=$type[1]; ?>" maxlength="255"><?
					break;
				case "textarea":
					?><textarea id="<?=$controlId; ?>" name="<?=$controlId; ?>" rows="<?=$type[1]; ?>" cols="<?=$type[2]; ?>"><?=htmlspecialcharsbx($val); ?></textarea><?
					break;
			}
			?>
			</td>
		</tr>
		<?
	}
}
unset($arOption, $arAllOptions);
$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form><?
if ($needFeatureConfirm)
{
	?><script type="text/javascript">
function checkFeatures()
{
	var featureControl = BX('property_features_enabled');
	if (BX.type.isElementNode(featureControl))
	{
		if (featureControl.checked)
		{
			if (!confirm('<?=\CUtil::JSEscape(GetMessage('IBLOCK_OPTION_MESS_CHECK_FEATURES')); ?>'))
				featureControl.checked = false;
		}
	}
}
BX.ready(function(){
	var featureControl = BX('property_features_enabled');
	if (BX.type.isElementNode(featureControl))
	{
		BX.bind(featureControl, 'click', checkFeatures);
	}
});
</script><?
}