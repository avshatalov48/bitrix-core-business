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

$defaultValues = Main\Config\Option::getDefaults('iblock');

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
	array("excel_export_rights", GetMessage("IBLOCK_EXCEL_EXPORT_RIGHTS"), "N", array("checkbox", "Y")),
	array("list_full_date_edit", GetMessage("IBLOCK_LIST_FULL_DATE_EDIT"), "N", array("checkbox", "Y")),
	array("combined_list_mode", GetMessage("IBLOCK_COMBINED_LIST_MODE"), "N", array("checkbox", "Y")),
	array("iblock_menu_max_sections", GetMessage("IBLOCK_MENU_MAX_SECTIONS"), "50", array("text", 5)),
	array("change_user_by_group_active_modify", GetMessage("IBLOCK_OPTION_CHANGE_USER_BY_GROUP_ACTIVE_MODIFY"), "N", array("checkbox", "N")),
	GetMessage('IBLOCK_OPTION_SECTION_CUSTOM_FORM'),
	array("custom_edit_form_use_property_id", GetMessage("IBLOCK_CUSTOM_FORM_USE_PROPERTY_ID"), "Y", array("checkbox", "Y")),
	GetMessage('IBLOCK_OPTION_SECTION_IMPORT_EXPORT'),
	array("num_catalog_levels", GetMessage("IBLOCK_NUM_CATALOG_LEVELS"), "3", array("text", 5)),
);
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "iblock_cache", "TAB" => GetMessage("IBLOCK_OPTION_TAB_CACHE"), "TITLE" => GetMessage("IBLOCK_OPTION_TAB_CACHE_TITLE"))
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$request = Main\Context::getCurrent()->getRequest();

if($request->isPost() && $Update.$Apply.$RestoreDefaults <> '' && check_bitrix_sessid())
{
	if($RestoreDefaults <> '')
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

		$period = (int)$request->getPost('iblock_activity_dates_period');
		if ($period === -1)
		{
			$period = (int)$request->getPost('iblock_activity_dates_period_custom');
		}
		if ($period !== 0)
		{
			$oldPeriod = (int)Main\Config\Option::get('iblock', 'iblock_activity_dates_period');

			$activity = $request->getPost('IBLOCK_ACTIVITY_DATES');
			if (!is_array($activity))
			{
				$activity = [];
			}
			if (!empty($activity))
			{
				Main\Type\Collection::normalizeArrayValuesByInt($activity, true);
			}
			$oldActivity = (string)Main\Config\Option::get('iblock', 'iblock_activity_dates');
			if ($oldActivity !== '')
			{
				$oldActivity = explode(',', $oldActivity);
				Main\Type\Collection::normalizeArrayValuesByInt($oldActivity, true);
			}
			else
			{
				$oldActivity = array();
			}
			$removeAgents = array();
			$addAgents = array();

			if ($oldPeriod != $period)
			{
				$removeAgents = $oldActivity;
				$addAgents = $activity;
			}
			else
			{
				$removeAgents = array_diff($oldActivity, $activity);
				$addAgents = array_diff($activity, $oldActivity);
			}

			if (!empty($removeAgents))
			{
				foreach ($removeAgents as $iblockId)
				{
					$iterator = CAgent::GetList(array(), array('MODULE_ID' => 'iblock', 'NAME' => '\CIBlock::checkActivityDatesAgent('.$iblockId.',%'));
					while ($row = $iterator->Fetch())
					{
						CAgent::Delete($row['ID']);
					}
					unset($row);
					unset($iterator);
				}
				unset($iblockId);
			}
			if (!empty($addAgents))
			{
				$currentTime = time();
				foreach ($addAgents as $iblockId)
				{
					CAgent::AddAgent(
						'\CIBlock::checkActivityDatesAgent('.$iblockId.', '.$currentTime.');',
						'iblock',
						'N',
						$period,
						'',
						'Y',
						'',
						100,
						false,
						false
					);
				}
				unset($iblockId);
			}

			Main\Config\Option::set('iblock', 'iblock_activity_dates', implode(',', $activity), '');
			Main\Config\Option::set('iblock', 'iblock_activity_dates_period', $period, '');
		}
	}
	if($Update <> '' && $_REQUEST["back_url_settings"] <> '')
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

$activity = (string)Main\Config\Option::get('iblock', 'iblock_activity_dates');
if ($activity !== '')
{
	$activity = explode(',', $activity);
	Main\Type\Collection::normalizeArrayValuesByInt($activity, true);
}
else
{
	$activity = array();
}
$currentValues['iblock_activity_dates'] = $activity;
unset($activity);
$currentValues['iblock_activity_dates_period'] = (int)Main\Config\Option::get('iblock', 'iblock_activity_dates_period');
if ($currentValues['iblock_activity_dates_period'] <= 0)
{
	$currentValues['iblock_activity_dates_period'] = (int)$defaultValues['iblock_activity_dates_period'];
}
$currentValues['iblock_activity_dates_period_custom'] = $currentValues['iblock_activity_dates_period'];

$periodList = array(
	-1 => GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_CUSTOM'),
	3600 => GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_ONE_HOUR'),
	10800 => GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_THREE_HOUR'),
	21600 => GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_SIX_HOUR'),
	43200 => GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_TWELVE_HOUR'),
	86400 => GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_DAY')
);
if (!isset($periodList[$currentValues['iblock_activity_dates_period']]))
{
	$currentValues['iblock_activity_dates_period'] = -1;
}

$optionHints = array(
	'property_features_enabled' => GetMessage(
		'IBLOCK_PROPERTY_FEATURES_HINT',
		['#LINK#' => 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=42&LESSON_ID=1986']
	),
	'change_user_by_group_active_modify' => GetMessage('IBLOCK_OPTION_CHANGE_USER_BY_GROUP_ACTIVE_MODIFY_HINT')
);

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
			<td style="width: 40%; white-space: nowrap;" <? if ($type[0] == "textarea") echo 'class="adm-detail-valign-top"' ?>>
				<?
				if (isset($optionHints[$id]))
				{
					?><span id="hint_<?= $controlId; ?>"></span>
					<script type="text/javascript">BX.hint_replace(BX('hint_<?=$controlId;?>'), '<?=\CUtil::JSEscape($optionHints[$id]); ?>');</script>&nbsp;<?
				}
				?><label for="<?=$controlId; ?>"><?=htmlspecialcharsbx($arOption[1]); ?></label>
			<td>
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
$tabControl->BeginNextTab();
?><tr class="heading"><td colspan="2"><?=htmlspecialcharsbx(GetMessage("IBLOCK_OPTION_SECTION_TAG_CACHE")); ?></td></tr>
<tr>
	<td style="width: 40%; white-space: nowrap;" class="adm-detail-valign-top"><?
	echo GetMessage("IBLOCK_OPTION_CHECK_ACTIVITY_CACHE");
	?></td>
	<td style="width: 60%">
		<table id="iblockList" class="internal">
		<?
		if (!empty($currentValues['iblock_activity_dates']))
		{
			foreach($currentValues['iblock_activity_dates'] as $iblockId)
			{
				$iblockName = (string)CIBlock::GetArrayByID($iblockId, 'NAME');
				if ($iblockName !== '')
				{
					?><tr>
						<td><?=('['.$iblockId.'] '.htmlspecialcharsbx($iblockName)); ?></td>
						<td>
							<input type="button" value="<?=htmlspecialcharsbx(GetMessage("IBLOCK_MESS_DELETE_ENTITY")) ?>" onclick="deleteRow(this)">
							<input type="hidden" name="IBLOCK_ACTIVITY_DATES[]" value="<?=$iblockId; ?>">
						</td>
						</tr><?
				}
			}
			unset($iblockId);
		}
		?>
		</table>
		<script type="text/javascript">
		function deleteRow(button)
		{
			var my_row = button.parentNode.parentNode,
				table = document.getElementById('iblockList'),
				i;
			if (BX.type.isElementNode(table))
			{
				for(i = 0; i < table.rows.length; i++)
				{
					if (table.rows[i] === my_row)
					{
						table.deleteRow(i);
					}
				}
			}
		}
		function InS<?echo md5("input_IBLOCK_LIST")?>(iblockId, iblockName)
		{
			var table = document.getElementById('iblockList'),
				oRow,
				i,
				oCell;
			if (BX.type.isElementNode(table))
			{
				if(iblockId !== '' && iblockName !== '')
				{
					oRow = table.insertRow(-1);

					i=0;
					oCell = oRow.insertCell(i++);
					oCell.innerHTML = '['+iblockId +'] ' + BX.util.htmlspecialchars(iblockName);

					oCell = oRow.insertCell(i++);
					oCell.innerHTML =
						'<input type="button" value="<?=htmlspecialcharsbx(GetMessage("IBLOCK_MESS_DELETE_ENTITY")); ?>" OnClick="deleteRow(this)">'+
						'<input type="hidden" name="IBLOCK_ACTIVITY_DATES[]" value="'+iblockId+'">';
				}
			}
		}
		</script>
		<input name="input_IBLOCK_LIST" id="input_IBLOCK_LIST" type="hidden">
		<input type="button" value="<?=htmlspecialcharsbx(GetMessage("IBLOCK_MESS_ADD_ENTITY")); ?>" onClick="jsUtils.OpenWindow('<?=$selfFolderUrl?>iblock_search.php?lang=<?=LANGUAGE_ID?>&amp;n=input_IBLOCK_LIST&amp;m=y&amp;iblockfix=y&amp;tableId=iblocksection-<?=$IBLOCK_ID; ?>', 900, 700);">
	</td>
</tr>
<tr>
	<td style="width: 40%;"><?=GetMessage("IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD"); ?></td>
	<td style="width: 60%;">
		<select id="iblock_activity_dates_period" name="iblock_activity_dates_period"><?
		foreach ($periodList as $index => $value)
		{
			?><option value="<?=$index; ?>"<?=($index == $currentValues['iblock_activity_dates_period'] ? ' selected' : '');?>><?=htmlspecialcharsbx($value); ?></option><?
		}
		?></select>
	</td>
</tr>
<tr id="iblock_activity_dates_period_custom" style="display: <?=($currentValues['iblock_activity_dates_period'] == -1 ? 'table-row' : 'none');?>;">
	<td style="width: 40%;">&nbsp;</td>
	<td style="width: 60%;">
		<input type="text" name="iblock_activity_dates_period_custom" value="<?=$currentValues['iblock_activity_dates_period_custom']; ?>"><?=GetMessage('IBLOCK_OPTION_CHECK_ACTIVITY_PERIOD_CUSTOM_UNIT'); ?>
	</td>
</tr>
<?
$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<script type="text/javascript">
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
function checkCachePeriod()
{
	var control = BX('iblock_activity_dates_period'),
		block = BX('iblock_activity_dates_period_custom');

	if (BX.type.isElementNode(control) && BX.type.isElementNode(block))
	{
		block.style.display = (control.value === '-1' ? 'table-row' : 'none');
	}
}
BX.ready(function(){
	var featureControl = BX('property_features_enabled'),
		periodControl = BX('iblock_activity_dates_period');
<?if ($needFeatureConfirm)
{
	?>
	if (BX.type.isElementNode(featureControl))
	{
		BX.bind(featureControl, 'click', checkFeatures);
	}
<?
}
?>
	if (BX.type.isElementNode(periodControl))
	{
		BX.bind(periodControl, 'change', checkCachePeriod);
	}
});
</script>