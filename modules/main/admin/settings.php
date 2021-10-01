<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CAdminPage $adminPage
 */

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
define("HELP_FILE", "settings/settings/settings.php");

if(!$USER->CanDoOperation('view_other_settings') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(mb_strpos($_REQUEST["back_url_settings"], '/') !== 0 || mb_strpos($_REQUEST["back_url_settings"], '//') === 0)
	$_REQUEST["back_url_settings"] = '';

IncludeModuleLangFile(__FILE__);

$arModules = array(
	"main"=>array(
		"PAGE"=>$_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php",
		"NAME"=>GetMessage("MAIN_KERNEL"),
		"SORT"=>-1,
	)
);
$adminPage->Init();
foreach($adminPage->aModules as $module)
{
	if($APPLICATION->GetGroupRight($module) < "R")
		continue;
	if($module == "main")
		continue;
	$ifile = getLocalPath("modules/".$module."/install/index.php");
	$ofile = getLocalPath("modules/".$module."/options.php");
	if($ifile !== false && $ofile !== false)
	{
		$info = CModule::CreateModuleObject($module);
		$arModules[$module]["PAGE"] = $_SERVER["DOCUMENT_ROOT"].$ofile;
		$arModules[$module]["NAME"] = $info->MODULE_NAME;
		$arModules[$module]["SORT"] = $info->MODULE_SORT;
	}
}
\Bitrix\Main\Type\Collection::sortByColumn(
	$arModules,
	['SORT' => SORT_ASC, 'NAME' => SORT_STRING],
	'',
	null,
	true
);

$mid = $_REQUEST["mid"];
if($mid == "" || !isset($arModules[$mid]) || !file_exists($arModules[$mid]["PAGE"]))
	$mid = "main";

ob_start();
include($arModules[$mid]["PAGE"]);
$strModuleSettingsTabs = ob_get_contents();
ob_end_clean();

$APPLICATION->SetTitle(GetMessage("MAIN_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>

<form action="">
<select name="mid" onchange="window.location='settings.php?lang=<?=LANGUAGE_ID.($_REQUEST["mid_menu"]<>""? "&amp;mid_menu=1":"")?>&amp;mid='+this[this.selectedIndex].value;">
<?foreach($arModules as $k=>$m):?>
	<option value="<?echo htmlspecialcharsbx($k)?>"<?if($mid == $k) echo " selected"?>><?echo htmlspecialcharsbx($m["NAME"])?></option>
<?endforeach;?>
</select>
</form>
<br />

<?
function __AdmSettingsSaveOptions($module_id, $arOptions)
{
	foreach($arOptions as $arOption)
	{
		__AdmSettingsSaveOption($module_id, $arOption);
	}
}

function __AdmSettingsSaveOption($module_id, $arOption)
{
	if(!is_array($arOption) || isset($arOption["note"]))
		return false;

	if($arOption[3][0] == "statictext" || $arOption[3][0] == "statichtml")
		return false;

	$arControllerOption = CControllerClient::GetInstalledOptions($module_id);

	if(isset($arControllerOption[$arOption[0]]))
		return false;

	$name = $arOption[0];
	$isChoiceSites = array_key_exists(6, $arOption) && $arOption[6] == "Y" ? true : false;

	if ($isChoiceSites)
	{
		if (isset($_REQUEST[$name."_all"]) && $_REQUEST[$name."_all"] <> '')
			COption::SetOptionString($module_id, $name, $_REQUEST[$name."_all"], $arOption[1]);
		else
			COption::RemoveOption($module_id, $name);
		$queryObject = \Bitrix\Main\SiteTable::getList(array(
			'select' => array('LID', 'NAME'),
			'filter' => array(),
			'order' => array('SORT' => 'ASC'),
		));
		while ($site = $queryObject->fetch())
		{
			if (isset($_REQUEST[$name."_".$site["LID"]]) && $_REQUEST[$name."_".$site["LID"]] <> '' &&
				!isset($_REQUEST[$name."_all"]))
			{
				$val = $_REQUEST[$name."_".$site["LID"]];
				if($arOption[3][0] == "checkbox" && $val != "Y")
				{
					$val = "N";
				}
				if($arOption[3][0] == "multiselectbox" && is_array($val))
				{
					$val = implode(",", $val);
				}
				COption::SetOptionString($module_id, $name, $val, $arOption[1], $site["LID"]);
			}
			else
			{
				COption::RemoveOption($module_id, $name, $site["LID"]);
			}
		}
	}
	else
	{
		if(!isset($_REQUEST[$name]))
		{
			if($arOption[3][0] <> 'checkbox' && $arOption[3][0] <> "multiselectbox")
			{
				return false;
			}
		}

		$val = $_REQUEST[$name];

		if($arOption[3][0] == "checkbox" && $val != "Y")
		{
			$val = "N";
		}
		if($arOption[3][0] == "multiselectbox" && is_array($val))
		{
			$val = implode(",", $val);
		}

		COption::SetOptionString($module_id, $name, $val, $arOption[1]);
	}

	return null;
}

function __AdmSettingsDrawRow($module_id, $Option)
{
	$arControllerOption = CControllerClient::GetInstalledOptions($module_id);
	if($Option === null)
	{
		return;
	}

	if(!is_array($Option)):
	?>
		<tr class="heading">
			<td colspan="2"><?=$Option?></td>
		</tr>
	<?
	elseif(isset($Option["note"])):
	?>
		<tr>
			<td colspan="2" align="center">
				<?echo BeginNote('align="center"');?>
				<?=$Option["note"]?>
				<?echo EndNote();?>
			</td>
		</tr>
	<?
	else:
		$isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
		$listSite = array();
		$listSiteValue = array();
		if ($Option[0] != "")
		{
			if ($isChoiceSites)
			{
				$queryObject = \Bitrix\Main\SiteTable::getList(array(
					"select" => array("LID", "NAME"),
					"filter" => array(),
					"order" => array("SORT" => "ASC"),
				));
				$listSite[""] = GetMessage("MAIN_ADMIN_SITE_DEFAULT_VALUE_SELECT");
				$listSite["all"] = GetMessage("MAIN_ADMIN_SITE_ALL_SELECT");
				while ($site = $queryObject->fetch())
				{
					$listSite[$site["LID"]] = $site["NAME"];
					$val = COption::GetOptionString($module_id, $Option[0], $Option[2], $site["LID"], true);
					if ($val)
						$listSiteValue[$Option[0]."_".$site["LID"]] = $val;
				}
				$val = "";
				if (empty($listSiteValue))
				{
					$value = COption::GetOptionString($module_id, $Option[0], $Option[2]);
					if ($value)
						$listSiteValue = array($Option[0]."_all" => $value);
					else
						$listSiteValue[$Option[0]] = "";
				}
			}
			else
			{
				$val = COption::GetOptionString($module_id, $Option[0], $Option[2]);
			}
		}
		else
		{
			$val = $Option[2];
		}
		if ($isChoiceSites):?>
		<tr>
			<td colspan="2" style="text-align: center!important;">
				<label><?=$Option[1]?></label>
			</td>
		</tr>
		<?endif;?>
		<?if ($isChoiceSites):
			foreach ($listSiteValue as $fieldName => $fieldValue):?>
			<tr>
			<?
				$siteValue = str_replace($Option[0]."_", "", $fieldName);
				renderLable($Option, $listSite, $siteValue);
				renderInput($Option, $arControllerOption, $fieldName, $fieldValue);
			?>
			</tr>
			<?endforeach;?>
		<?else:?>
			<tr>
			<?
				renderLable($Option, $listSite);
				renderInput($Option, $arControllerOption, $Option[0], $val);
			?>
			</tr>
		<?endif;?>
		<? if ($isChoiceSites): ?>
			<tr>
				<td width="50%">
					<a href="javascript:void(0)" onclick="addSiteSelector(this)" class="bx-action-href">
						<?=GetMessage("MAIN_ADMIN_ADD_SITE_SELECTOR")?>
					</a>
				</td>
				<td width="50%"></td>
			</tr>
		<? endif; ?>
	<?
	endif;
}

function __AdmSettingsDrawList($module_id, $arParams)
{
	foreach($arParams as $Option)
	{
		__AdmSettingsDrawRow($module_id, $Option);
	}
}

function renderLable($Option, array $listSite, $siteValue = "")
{
	$type = $Option[3];
	$sup_text = array_key_exists(5, $Option) ? $Option[5] : '';
	$isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
	?>
	<?if ($isChoiceSites): ?>
	<script type="text/javascript">
		function changeSite(el, fieldName)
		{
			var tr = jsUtils.FindParentObject(el, "tr");
			var sel = null, tagNames = ["select", "input", "textarea"];
			for (var i = 0; i < tagNames.length; i++)
			{
				sel = jsUtils.FindChildObject(tr.cells[1], tagNames[i]);
				if (sel)
				{
					sel.name = fieldName+"_"+el.value;
					break;
				}

			}
		}
		function addSiteSelector(a)
		{
			var row = jsUtils.FindParentObject(a, "tr");
			var tbl = row.parentNode;
			var tableRow = tbl.rows[row.rowIndex-1].cloneNode(true);
			tbl.insertBefore(tableRow, row);
			var sel = jsUtils.FindChildObject(tableRow.cells[0], "select");
			sel.name = "";
			sel.selectedIndex = 0;
			sel = jsUtils.FindChildObject(tableRow.cells[1], "select");
			sel.name = "";
			sel.selectedIndex = 0;
		}
	</script>
	<td width="50%">
		<select onchange="changeSite(this, '<?=htmlspecialcharsbx($Option[0])?>')">
			<?foreach ($listSite as $lid => $siteName):?>
				<option <?if ($siteValue ==$lid) echo "selected";?> value="<?=htmlspecialcharsbx($lid)?>">
					<?=htmlspecialcharsbx($siteName)?>
				</option>
			<?endforeach;?>
		</select>
	</td>
	<?else:?>
		<td<?if ($type[0]=="multiselectbox" || $type[0]=="textarea" || $type[0]=="statictext" ||
		$type[0]=="statichtml") echo ' class="adm-detail-valign-top"'?> width="50%"><?
		if ($type[0]=="checkbox")
			echo "<label for='".htmlspecialcharsbx($Option[0])."'>".$Option[1]."</label>";
		else
			echo $Option[1];
		if ($sup_text <> '')
		{
			?><span class="required"><sup><?=$sup_text?></sup></span><?
		}
		?><a name="opt_<?=htmlspecialcharsbx($Option[0])?>"></a></td>
	<?endif;
}

function renderInput($Option, $arControllerOption, $fieldName, $val)
{
	$type = $Option[3];
	$disabled = array_key_exists(4, $Option) && $Option[4] == 'Y' ? ' disabled' : '';
	?><td width="50%"><?
	if($type[0]=="checkbox"):
		?><input type="checkbox" <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> id="<?echo htmlspecialcharsbx($Option[0])?>" name="<?=htmlspecialcharsbx($fieldName)?>" value="Y"<?if($val=="Y")echo" checked";?><?=$disabled?><?if($type[2]<>'') echo " ".$type[2]?>><?
	elseif($type[0]=="text" || $type[0]=="password"):
		?><input type="<?echo $type[0]?>"<?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?=htmlspecialcharsbx($fieldName)?>"<?=$disabled?><?=($type[0]=="password" || $type["noautocomplete"]? ' autocomplete="new-password"':'')?>><?
	elseif($type[0]=="selectbox"):
		$arr = $type[1];
		if(!is_array($arr))
			$arr = array();
		?><select name="<?=htmlspecialcharsbx($fieldName)?>" <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> <?=$disabled?>><?
		foreach($arr as $key => $v):
			?><option value="<?echo $key?>"<?if($val==$key)echo" selected"?>><?echo htmlspecialcharsbx($v)?></option><?
		endforeach;
		?></select><?
	elseif($type[0]=="multiselectbox"):
		$arr = $type[1];
		if(!is_array($arr))
			$arr = array();
		$arr_val = explode(",",$val);
		?><select size="5" <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> multiple name="<?=htmlspecialcharsbx($fieldName)?>[]"<?=$disabled?>><?
		foreach($arr as $key => $v):
			?><option value="<?echo $key?>"<?if(in_array($key, $arr_val)) echo " selected"?>><?echo htmlspecialcharsbx($v)?></option><?
		endforeach;
		?></select><?
	elseif($type[0]=="textarea"):
		?><textarea <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?=htmlspecialcharsbx($fieldName)?>"<?=$disabled?>><?echo htmlspecialcharsbx($val)?></textarea><?
	elseif($type[0]=="statictext"):
		echo htmlspecialcharsbx($val);
	elseif($type[0]=="statichtml"):
		echo $val;
	endif;?>
	</td><?
}

echo $strModuleSettingsTabs;
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
