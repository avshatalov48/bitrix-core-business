<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Currency;
use Bitrix\Iblock;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

Loader::includeModule('sale');
$iblockIncluded = Loader::includeModule('iblock');

if (!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$request = Context::getCurrent()->getRequest();

$errorMessage = '';
$bVarsFromForm = false;

$ID = (int)$request->get('ID');

$affiliatePlanType = COption::GetOptionString("sale", "affiliate_plan_type", "N");
$simpleForm = COption::GetOptionString("sale", "lock_catalog", "Y");

$formFields = [];
$formSectionList = [];

if ($request->isPost() && $request->getPost('Update') !== null && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$requiredStringList = [
		'SITE_ID' => GetMessage('SAPE1_NO_SITE'),
		'NAME' => GetMessage('SAPE1_NO_NAME'),
	];
	foreach ($requiredStringList as $fieldId => $fieldError)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value) && $value !== '')
		{
			$formFields[$fieldId] = $value;
		}
		else
		{
			$errorMessage .= $fieldError . '<br>';
		}
	}
	unset($requiredStringList);
	$stringList = [
		'DESCRIPTION',
	];
	foreach ($stringList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = $value;
		}
	}
	unset($stringList);

	$booleanList = [
		'ACTIVE',
	];
	foreach ($booleanList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if ($value === 'Y' || $value === 'N')
		{
			$formFields[$fieldId] = $value;
		}
	}
	unset($booleanList);

	$intList = [
		'NUM_SECTIONS',
	];
	$floatList = [
		'BASE_RATE',
		'MIN_PAY',
	];
	if ($affiliatePlanType === 'N')
	{
		$intList[] = 'MIN_PLAN_VALUE';
	}
	else
	{
		$floatList[] = 'MIN_PLAN_VALUE';
	}
	foreach ($intList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = (int)$value;
		}
	}
	foreach ($floatList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = (float)str_replace(',', '.', $value);
		}
	}
	unset($floatList, $intList);

	$baseRateType = $request->getPost('BASE_RATE_TYPE_CMN');
	if (is_string($baseRateType) && $baseRateType !== '')
	{
		if ($baseRateType === 'P')
		{
			$formFields['BASE_RATE_TYPE'] = $baseRateType;
			$formFields['BASE_RATE_CURRENCY'] = false;
		}
		else
		{
			$formFields['BASE_RATE_TYPE'] = 'F';
			$formFields['BASE_RATE_CURRENCY'] = $baseRateType;
		}
	}
	else
	{
		$errorMessage .= GetMessage('SAPE1_NO_RATE_CURRENCY') . '<br>';
	}
	unset($baseRateType);

	$newCount = 0;
	$defaultSectionValues = [
		'MODULE_ID' => '',
		'SECTION_ID' => '',
		'RATE' => 0,
		'RATE_TYPE' => 'P',
		'RATE_CURRENCY' => '',
	];

	$numSection = $formFields['NUM_SECTIONS'] ?? 0;
	for ($i = 0; $i <= $numSection; $i++)
	{
		$index = $request->getPost('ID_' . $i);
		if (!is_string($index))
		{
			continue;
		}
		$index = (int)$index;
		if ($index <= 0)
		{
			$index = 'n' . $newCount;
			$newCount++;
		}
		$rowError = false;
		$row = [];

		if ($simpleForm === 'Y')
		{
			$row['MODULE_ID'] = 'catalog';
		}
		else
		{
			$moduleId = $request->getPost('MODULE_ID_' . $i);
			$row['MODULE_ID'] = (is_string($moduleId) ? trim($moduleId) : '');
			if ($row['MODULE_ID'] === '')
			{
				$rowError = true;
				$errorMessage .= GetMessage('SAPE1_NO_MODULE') . '<br>';
			}
			unset($moduleId);
		}

		if ($row['MODULE_ID'] === 'catalog')
		{
			$sectionId = '';
			$selectorList = $request->getPost('SECTION_SELECTOR_LEVEL_' . $i);
			if (is_array($selectorList))
			{
				foreach ($selectorList as $value)
				{
					$value = (is_string($value) ? (int)$value : 0);
					if ($value > 0)
					{
						$sectionId = $value;
					}
				}
			}
			if ($sectionId === '')
			{
				$value = $request->getPost('SECTION_ID_' . $i);
				if (is_string($value))
				{
					$value = (int)$value;
					if ($value > 0)
					{
						$sectionId = $value;
					}
				}
			}
			$row['SECTION_ID'] = (string)$sectionId;
		}
		else
		{
			$sectionId = $request->getPost('SECTION_ID_' . $i);
			$row['SECTION_ID'] = (is_string($sectionId) ? trim($sectionId) : '');
		}
		unset($sectionId);
		if ($row['SECTION_ID'] === '')
		{
			$rowError = true;
			$errorMessage .= GetMessage('SAPE1_NO_SECTION') .'<br>';
		}

		$rate = $request->getPost('RATE_'. $i);
		if (!is_string($rate))
		{
			$rate = '';
		}
		$row['RATE'] = (float)str_replace(',', '.', $rate);
		unset($rate);

		$rateType = $request->getPost('RATE_TYPE_CMN_' . $i);
		if (is_string($rateType) && $rateType !== '')
		{
			if ($rateType === 'P')
			{
				$row['RATE_TYPE'] = $rateType;
				$row['RATE_CURRENCY'] = false;
			}
			else
			{
				$row['RATE_TYPE'] = 'F';
				$row['RATE_CURRENCY'] = $rateType;
			}
		}
		else
		{
			$rowError = true;
			$errorMessage .= GetMessage('SAPE1_NO_RATE_CURRENCY') . '<br>';
		}
		unset($rateType);

		if ($rowError)
		{
			$row = array_merge($defaultSectionValues, $row);
		}
		$formSectionList[$index] = $row;
	}

	$conn = Application::getConnection();
	$transaction = false;

	if ($errorMessage === '')
	{
		$transaction = true;
		$conn->startTransaction();

		if ($ID > 0)
		{
			if (!CSaleAffiliatePlan::Update($ID, $formFields))
			{
				$ex = $APPLICATION->GetException();
				if ($ex)
				{
					$errorMessage .= $ex->GetString() . '<br>';
				}
				else
				{
					$errorMessage .= GetMessage('SAPE1_ERROR_SAVE') . '.<br>';
				}
				unset($ex);
			}
		}
		else
		{
			$ID = (int)CSaleAffiliatePlan::Add($formFields);
			if ($ID <= 0)
			{
				$ex = $APPLICATION->GetException();
				if ($ex)
				{
					$errorMessage .= $ex->GetString() . '<br>';
				}
				else
				{
					$errorMessage .= GetMessage("SAPE1_ERROR_SAVE") . '<br>';
				}
				unset($ex);
			}
		}
	}

	if ($errorMessage == '')
	{
		$sectionIds = [];
		foreach ($formSectionList as $index => $row)
		{
			$row['PLAN_ID'] = $ID;
			if (is_int($index))
			{
				if (!CSaleAffiliatePlanSection::Update($index, $row))
				{
					$ex = $APPLICATION->GetException();
					if ($ex)
					{
						$errorMessage .= $ex->GetString() . '<br>';
					}
					else
					{
						$errorMessage .= GetMessage('SAPE1_ERROR_SAVE_SECTION') . '<br>';
					}
					unset($ex);
				}
				else
				{
					$sectionIds[] = $index;
				}
			}
			else
			{
				$index = (int)CSaleAffiliatePlanSection::Add($row);
				if ($index <= 0)
				{
					$ex = $APPLICATION->GetException();
					if ($ex)
					{
						$errorMessage .= $ex->GetString() . '<br>';
					}
					else
					{
						$errorMessage .= GetMessage("SAPE1_ERROR_SAVE_SECTION") . '<br>';
					}
					unset($ex);
				}
				else
				{
					$sectionIds[] = $index;
				}
			}
		}

		CSaleAffiliatePlanSection::DeleteByPlan($ID, $sectionIds);
	}

	if ($transaction)
	{
		if ($errorMessage === '')
		{
			$conn->commitTransaction();
		}
		else
		{
			$conn->rollbackTransaction();
		}
	}
	unset($conn);

	if ($errorMessage === '')
	{
		if ($request->getPost('apply') === null)
		{
			LocalRedirect(
				'/bitrix/admin/sale_affiliate_plan.php?lang=' . LANGUAGE_ID
					. GetFilterParams('filter_', false)
			);
		}
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php';

if ($ID > 0)
{
	$APPLICATION->SetTitle(GetMessage(
		"SAPE1_TITLE_UPDATE",
		[
			'#ID#' => $ID,
		]
	));
}
else
{
	$APPLICATION->SetTitle(GetMessage('SAPE1_TITLE_ADD'));
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$defaultValues = [
	'ID' => 0,
	'SITE_ID' => '',
	'NAME' => '',
	'DESCRIPTION' => '',
	'TIMESTAMP_X' => '',
	'ACTIVE' => 'Y',
	'BASE_RATE' => '',
	'BASE_RATE_TYPE' => '',
	'BASE_RATE_CURRENCY' => 'P',
	'MIN_PAY' => '',
	'MIN_PLAN_VALUE' => 0,
];
$fields = null;
$sectionList = null;

if ($ID > 0)
{
	$iterator = CSaleAffiliatePlan::GetList([], ['ID' => $ID]);
	$fields = $iterator->Fetch();
	unset($iterator);
}
if (!$fields)
{
	$ID = 0;
	$fields = $defaultValues;
	$sectionList = [];
}
else
{
	$sectionList = [];

	$iterator = CSaleAffiliatePlanSection::GetList([], ["PLAN_ID" => $ID]);
	while ($row = $iterator->Fetch())
	{
		$sectionId = (int)$row['ID'];
		$sectionList[$sectionId] = [
			'MODULE_ID' => $row['MODULE_ID'],
			'SECTION_ID' => (int)$row['SECTION_ID'],
			'RATE' => (float)$row['RATE'],
			'RATE_TYPE' => $row['RATE_TYPE'],
			'RATE_CURRENCY' => $row['RATE_CURRENCY'],
		];
	}
	unset($row, $iterator);
}

if ($bVarsFromForm)
{
	$fields = array_merge($fields, $formFields);

	foreach ($formSectionList as $index => $row)
	{
		if (isset($sectionList[$index]))
		{
			$sectionList[$index] = array_merge($sectionList[$index], $row);
		}
		else
		{
			$sectionList[$index] = $row;
		}
	}
}

$aMenu = [
	[
		'TEXT' => GetMessage('SAPE1_LIST'),
		'LINK' => '/bitrix/admin/sale_affiliate_plan.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
		'ICON' => 'btn_list',
	],
];

if ($ID > 0)
{
	$aMenu[] = [
		"SEPARATOR" => "Y",
	];

	$aMenu[] = [
		'TEXT' => GetMessage('SAPE1_ADD'),
		'LINK' => '/bitrix/admin/sale_affiliate_plan_edit.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
		'ICON' => 'btn_new',
	];

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = [
			'TEXT' => GetMessage('SAPE1_DELETE'),
			'LINK' => "javascript:if(confirm('".GetMessage("SAPE1_DELETE_CONF")."')) window.location='/bitrix/admin/sale_affiliate_plan.php?ID=" . $ID. "&action=delete&lang=" . LANGUAGE_ID . "&" . bitrix_sessid_get() . "#tb';",
			'WARNING' => 'Y',
			'ICON' => 'btn_delete',
		];
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errorMessage !== '')
{
	CAdminMessage::ShowMessage([
		'DETAILS' => $errorMessage,
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('SAPE1_ERROR_SAVE'),
		'HTML' => true,
	]);
}
?>
<form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?" name="form1">
<?= GetFilterHiddens("filter_"); ?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<?= $ID ?>">
<?= bitrix_sessid_post(); ?>
<?php
$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('SAPE1_PLAN'),
		'ICON' => 'sale',
		'TITLE' => GetMessage('SAPE1_PLAN_PARAM'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('SAPE1_SECTIONS'),
		'ICON' => 'sale',
		'TITLE' => GetMessage('SAPE1_SECTIONS_ALT'),
	],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID > 0):
		?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?= $ID; ?></td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("SAPE1_TIMESTAMP_X"); ?></td>
			<td width="60%"><?= $fields['TIMESTAMP_X']; ?></td>
		</tr>
		<?php
	endif;
	?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("SAPE1_SITE"); ?></td>
		<td width="60%">
			<?= CSite::SelectBox('SITE_ID', $fields['SITE_ID'], '', ''); ?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("SAPE1_NAME"); ?></td>
		<td>
			<input type="text" name="NAME" size="60" maxlength="250" value="<?= htmlspecialcharsbx($fields['NAME']); ?>">
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("SAPE1_DESCR");?></td>
		<td>
			<textarea name="DESCRIPTION" rows="5" cols="60"><?= htmlspecialcharsbx($fields['DESCRIPTION']); ?></textarea>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAPE1_ACTIVE")?></td>
		<td>
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" name="ACTIVE" value="Y"<?= ($fields['ACTIVE'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("SAPE1_RATE"); ?></td>
		<td>
			<input type="text" name="BASE_RATE" size="10" maxlength="10" value="<?= roundEx($fields['BASE_RATE'], SALE_VALUE_PRECISION) ?>">
			<?php
				if ($fields['BASE_RATE_TYPE'] === "P")
				{
					$baseRateType = "P";
				}
				else
				{
					$baseRateType = $fields['BASE_RATE_CURRENCY'];
				}
			?>
			<select name="BASE_RATE_TYPE_CMN">
				<option value="P"<?= ($baseRateType === "P") ? " selected" : "" ?>>%</option>
				<?php
				$arCurrencies = [];
				if (Loader::includeModule('currency'))
				{
					$arCurrencies = Currency\CurrencyManager::getCurrencyList();
				}
				foreach ($arCurrencies as $key => $value)
				{
					?><option value="<?= $key ?>"<?= ($key === $baseRateType) ? " selected" : "" ?>><?= htmlspecialcharsbx($value); ?></option><?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr><?php
		if ($affiliatePlanType === 'N')
		{
			$value = (int)$fields['MIN_PLAN_VALUE'];
			$fieldTitle = GetMessage('SAPE1_LIMIT');
		}
		else
		{
			$value = (float)$fields['MIN_PLAN_VALUE'];
			$fieldTitle =  GetMessage('SAPE1_LIMIT_HINT');
		}
		?>
		<td><?= $fieldTitle; ?>:</td>
		<td><?php
			if ($affiliatePlanType === 'N')
			?>
			<input type="text" name="MIN_PLAN_VALUE" size="10" maxlength="10" value="<?= $value; ?>">
		</td>
	</tr>
<?php
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<script>

			function ShowHideSectionBox(cnt, val)
			{
				var catalogGroupBox = document.getElementById("ID_CATALOG_GROUP_" + cnt);
				var otherGroupBox = document.getElementById("ID_OTHER_GROUP_" + cnt);

				if (val)
				{
					catalogGroupBox.style["display"] = "block";
					otherGroupBox.style["display"] = "none";
				}
				else
				{
					catalogGroupBox.style["display"] = "none";
					otherGroupBox.style["display"] = "block";
				}
			}

			function ModuleChange(cnt)
			{
				var m = eval("document.form1.MODULE_ID_" + cnt);
				if (!m)
					return;

				if (m[m.selectedIndex].value == "catalog")
					ShowHideSectionBox(cnt, true);
				else
					ShowHideSectionBox(cnt, false);
			}


			var itm_id = new Object();
			var itm_name = new Object();

			function ChlistIBlock(cnt, n_id)
			{
				var max_lev = itm_lev;
				var nex = document.form1["SECTION_SELECTOR_LEVEL_" + cnt + "[0]"];
				var iBlock = eval("document.form1.SECTION_IBLOCK_ID_" + cnt);
				var iBlockID = iBlock[iBlock.selectedIndex].value;
				if (itm_id[iBlockID])
				{
					var curlist = itm_id[iBlockID][0];
					if (curlist && curlist.length > 1)
					{
						var curlistname = itm_name[iBlockID][0];
						var nex_length = nex.length;
						while (nex_length > 1)
						{
							nex_length--;
							nex.options[nex_length] = null;
						}
						nex_length = 1;

						for (i = 1; i < curlist.length; i++)
						{
							var newoption = new Option(curlistname[i], curlist[i], false, false);
							nex.options[nex_length] = newoption;
							if (n_id == curlist[i]) nex.selectedIndex = nex_length;
							nex_length++;
						}
						startClear = 1;
					}
					else
					{
						startClear = 0;
					}
				}
				else
				{
					startClear = 0;
				}

				for (i = startClear; i < max_lev; i++)
				{
					nex = document.form1["SECTION_SELECTOR_LEVEL_" + cnt + "["+i+"]"];
					var nex_length = nex.length;
					while (nex_length > 1)
					{
						nex_length--;
						nex.options[nex_length] = null;
					}
				}
			}

			function Chlist(cnt, num, n_id)
			{
				var max_lev = itm_lev;
				var cur = document.form1["SECTION_SELECTOR_LEVEL_" + cnt + "["+num+"]"];
				var nex = document.form1["SECTION_SELECTOR_LEVEL_" + cnt + "["+(parseInt(num)+1)+"]"];
				var iBlock = document.form1["SECTION_IBLOCK_ID_" + cnt];
				var id = cur[cur.selectedIndex].value;
				var iBlockID = iBlock[iBlock.selectedIndex].value;
				var curlist = itm_id[iBlockID][id];
				if (curlist && curlist.length>1)
				{
					var curlistname = itm_name[iBlockID][id];
					var nex_length = nex.length;
					while (nex_length>1)
					{
						nex_length--;
						nex.options[nex_length] = null;
					}
					nex_length = 1;

					for (i = 1; i < curlist.length; i++)
					{
						var newoption = new Option(curlistname[i], curlist[i], false, false);
						nex.options[nex_length] = newoption;
						if (n_id == curlist[i]) nex.selectedIndex = nex_length;
						nex_length++;
					}
				}
				else
					num--;

				for (i = num + 2; i < max_lev; i++)
				{
					nex = document.form1["SECTION_SELECTOR_LEVEL_" + cnt + "["+i+"]"];
					var nex_length = nex.length;
					while (nex_length>1)
					{
						nex_length--;
						nex.options[nex_length] = null;
					}
				}
			}

			function Fnd(cnt, ar)
			{
				var iBlock = document.form1["SECTION_IBLOCK_ID_" + cnt];
				var fst = document.form1["SECTION_SELECTOR_LEVEL_" + cnt + "[0]"];
				var i = 0;

				for (i = 0; i < iBlock.length; i++)
					if (iBlock[i].value == ar[1])
						iBlock.selectedIndex = i;

				if (ar.length > 2)
					ChlistIBlock(cnt, ar[2]);

				for (i = 0; i < ar.length - 3; i++)
					Chlist(cnt, i, ar[i + 3]);

				Chlist(cnt, i);
			}

			</script>
			<?php
			$arIBlockCache = array();
			$arIBlockTypeCache = array();
			$maxLevel = 0;
			$dbIBlockList = CIBlock::GetList(
				array("IBLOCK_TYPE" => "ASC", "NAME" => "ASC"),
				array("ACTIVE" => "Y")
			);
			while ($arIBlock = $dbIBlockList->Fetch())
			{
				$arIBlockCache[] = $arIBlock;
				if (!array_key_exists($arIBlock["IBLOCK_TYPE_ID"], $arIBlockTypeCache))
					if ($arIBlockType = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG, true))
						$arIBlockTypeCache[$arIBlock["IBLOCK_TYPE_ID"]] = $arIBlockType["NAME"];

				$arSections = [];

				$dbSectionTree = CIBlockSection::GetTreeList(
					array("IBLOCK_ID" => $arIBlock["ID"])
				);
				while ($arSectionTree = $dbSectionTree->Fetch())
				{
					if ($maxLevel < $arSectionTree["DEPTH_LEVEL"])
						$maxLevel = $arSectionTree["DEPTH_LEVEL"];

					$arSectionTree["IBLOCK_SECTION_ID"] = (int)$arSectionTree["IBLOCK_SECTION_ID"];

					$arSections[$arSectionTree["IBLOCK_SECTION_ID"]] ??= [];

					$arSections[$arSectionTree["IBLOCK_SECTION_ID"]][] = [
						"ID" => $arSectionTree["ID"],
						"NAME" => $arSectionTree["NAME"]
					];
				}

				$str1 = "";
				$str2 = "";
				foreach ($arSections as $sectionID => $arSubSection)
				{
					$str1 .= "itm_id['".$arIBlock["ID"]."']['".$sectionID."'] = new Array(0";
					$str2 .= "itm_name['".$arIBlock["ID"]."']['".$sectionID."'] = new Array(''";
					for ($i = 0, $maxCount = count($arSubSection); $i < $maxCount; $i++)
					{
						$str1 .= ", ".$arSubSection[$i]["ID"];
						$str2 .= ", '".CUtil::JSEscape($arSubSection[$i]["NAME"])."'";
					}
					$str1 .= ");\r\n";
					$str2 .= ");\r\n";
				}
				?>
				<script type="text/javascript">

				itm_name['<?= $arIBlock["ID"] ?>'] = {};
				itm_id['<?= $arIBlock["ID"] ?>'] = {};
				<?=$str1;?>
				<?=$str2;?>

				</script>
				<?php
			}
			?>
			<script type="text/javascript">
			itm_lev = <?= $maxLevel ?>;
			var aff_cnt = 0;

			function AffAddSectionRow(cnt, id, moduleID, sectionID, rate, rateCmn, ar)
			{
				var oTbl = document.getElementById("SECTIONS_TABLE");
				if (!oTbl)
					return;
				if (!id)
					id = 0;
				else
					aff_cnt++;

				if (!moduleID)
					moduleID = "catalog";
				if (!sectionID)
					sectionID = "";
				if (!rate)
					rate = 0;
				if (!rateCmn)
					rateCmn = "P";

				if (cnt < 0)
				{
					var oCntr = document.getElementById("NUM_SECTIONS");
					var cnt = parseInt(oCntr.value) + 1;
					oCntr.value = cnt;
				}

				var oRow = oTbl.insertRow(-1);
				oRow.id = "SECTION_TABLE_ROW_" + cnt;

				var str = "";

				<?php
				if ($simpleForm != "Y")
				{
					?>
					var oCell = oRow.insertCell(-1);
					oCell.vAlign = 'top';
					str = '';
					str += '<select name="MODULE_ID_' + cnt + '" id="ID_MODULE_ID_' + cnt + '" OnChange="ModuleChange(' + cnt + ')" style="width:150px;">';
					<?php
					$dbModuleList = CModule::GetList();
					while ($arModuleList = $dbModuleList->Fetch())
					{
						?>str += '<option value="<?= $arModuleList["ID"] ?>"><?= htmlspecialcharsbx(CUtil::JSEscape($arModuleList["ID"])) ?></option>';<?php
					}
					?>
					str += '</select>';

					oCell.innerHTML = str;

					var oModule = document.getElementById("ID_MODULE_ID_" + cnt);
					for (var i = 0; i < oModule.options.length; i++)
					{
						if (oModule.options[i].value == moduleID)
						{
							oModule.selectedIndex = i;
							break;
						}
					}
					<?php
				}
				?>
				var oCell = oRow.insertCell(-1);
				oCell.vAlign = 'top';
				str = '';
				str += '<input type="hidden" name="ID_' + cnt + '" value="' + id + '">';
				str += '<div id="ID_CATALOG_GROUP_' + cnt + '" style="display: none;">';
				str += '<select name="SECTION_IBLOCK_ID_' + cnt + '" onChange="ChlistIBlock(' + cnt + ')" style="width:300px;">';
				str += '<option value="0"> - </option>';
				<?php
				foreach ($arIBlockCache as $key => $arIBlock)
				{
					?>str += '<option value="<?= $arIBlock["ID"] ?>"><?= htmlspecialcharsbx(CUtil::JSescape("[".$arIBlockTypeCache[$arIBlock["IBLOCK_TYPE_ID"]]."] ".$arIBlock["NAME"])) ?></option>';<?php
				}
				?>
				str += '</select><br>';
				<?php
				$initValue = 0;
				for ($i = 0; $i < $maxLevel; $i++)
				{
					?>
					str += '<select name="SECTION_SELECTOR_LEVEL_' + cnt + '[<?= $i ?>]" onChange="Chlist(' + cnt + ', <?= $i ?>)" style="width:300px;">';
					str += '<option value=""><?= GetMessage("SAPE1_NO")?></option>';
					str += '</select><br>';
					<?php
				}
				?>
				str += '</div>';

				str += '<div id="ID_OTHER_GROUP_' + cnt + '" style="display: block;">';
				str += '<input type="text" name="SECTION_ID_' + cnt + '" size="30" value="' + sectionID + '">';
				str += '</div>';

				oCell.innerHTML = str;

				var oCell = oRow.insertCell(-1);
				oCell.vAlign = 'top';
				str = '';
				str += '<input type="text" name="RATE_' + cnt + '" size="10" maxlength="10" value="' + rate + '">';
				str += '<select name="RATE_TYPE_CMN_' + cnt + '" id="ID_RATE_TYPE_CMN_' + cnt + '" style="width:100px;">';
				str += '<option value="P">%</option>';
				<?php
				foreach ($arCurrencies as $key => $value)
				{
					?>str += '<option value="<?= $key ?>"><?= htmlspecialcharsbx(CUtil::JSEscape($value)) ?></option>';<?php
				}
				?>
				str += '</select>';

				oCell.innerHTML = str;

				var oType = document.getElementById("ID_RATE_TYPE_CMN_" + cnt);
				for (var i = 0; i < oType.options.length; i++)
				{
					if (oType.options[i].value == rateCmn)
					{
						oType.selectedIndex = i;
						break;
					}
				}

				var oCell = oRow.insertCell(-1);
				oCell.vAlign = 'top';
				str = '';
				str += '<a href="javascript:if(confirm(\'<?= GetMessage("SAPE1_DELETE1_CONF")?>\')) AffDeleteSectionRow(' + cnt + ')"><?= GetMessage("SAPE1_DELETE1")?></a>';
				oCell.innerHTML = str;

				ChlistIBlock(cnt);

				<?php
				if ($simpleForm != "Y")
				{
					?>ModuleChange(cnt);<?php
				}
				else
				{
					?>ShowHideSectionBox(cnt, true);<?php
				}
				?>

				if (ar && ar.length > 0)
					Fnd(cnt, ar);

				if (document.forms.form1.BXAUTOSAVE)
				{
					setTimeout(function() {
						var r = BX.findChildren(oRow, {tag: /^(input|select)$/i}, true);
						if (r && r.length > 0)
						{
							for (var i=0,l=r.length;i<l;i++)
							{
								r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
							}
						}
					}, 10);
				}
			}

			function AffDeleteSectionRow(index)
			{
				var oTbl = document.getElementById("SECTIONS_TABLE");
				ind = -1;
				for (var i = 0; i < oTbl.rows.length; i++)
				{
					if (oTbl.rows[i].id == "SECTION_TABLE_ROW_" + index)
					{
						ind = i;
						break;
					}
				}
				if (ind >= 0)
					oTbl.deleteRow(ind);
			}

			BX.ready(function() {
				BX.addCustomEvent(document.forms.form1, 'onAutoSaveRestore', function(ob,data) {
					if (data['MODULE_ID_' + aff_cnt])
					{
						var i = aff_cnt;
						while (data['MODULE_ID_' + i])
						{
							AffAddSectionRow(-1);
							i++;
						}
					}
				});
			})

			</script>

			<input type="hidden" name="NUM_SECTIONS" id="NUM_SECTIONS" value="-1">
			<table cellpadding="3" cellspacing="1" border="0" width="100%" class="internal" id="SECTIONS_TABLE">
				<tr class="heading">
					<?php
					if ($simpleForm != "Y")
					{
						?><td><?= GetMessage("SAPE1_MODULE"); ?></td><?php
					}
					?>
					<td><?= GetMessage("SAPE1_SECTION"); ?></td>
					<td><?= GetMessage("SAPE1_RATE1"); ?></td>
					<td>&nbsp;</td>
				</tr>
				<script>
				<?php
				$cnt = -1;
				foreach ($sectionList as $index => $row)
				{
					if (!is_int($index))
					{
						$index = 0;
					}
					$rateType = ($row['RATE_TYPE'] === 'P' ? $row['RATE_TYPE'] : $row['RATE_CURRENCY']);

					$sectionPath = [];
					if ($iblockIncluded && $row['SECTION_ID'] > 0)
					{
						$section = Iblock\SectionTable::getRow([
							'select' => [
								'ID',
								'IBLOCK_ID',
							],
							'filter' => [
								'=ID' => $row['SECTION_ID']
							]
						]);
						if ($section)
						{
							$section['IBLOCK_ID'] = (int)$section['IBLOCK_ID'];
							$chain = CIBlockSection::GetNavChain($section['IBLOCK_ID'], $section['ID'], ['ID'], true);
							if (!empty($chain))
							{
								$sectionPath[] = 0;
								$sectionPath[] = $section['IBLOCK_ID'];
								foreach ($chain as $chainItem)
								{
									$sectionPath[] = (int)$chainItem['ID'];
								}
							}
							unset($chain);
						}
					}
					?>
					AffAddSectionRow(
						-1,
						<?= CUtil::JSEscape($index); ?>,
						'<?= CUtil::JSEscape($row['MODULE_ID']); ?>',
						'<?= CUtil::JSEscape($row['SECTION_ID']); ?>',
						'<?= CUtil::JSEscape($row['RATE']); ?>',
						'<?= CUtil::JSEscape($rateType) ?>',
						[<?= implode(',', $sectionPath); ?>]
					);
					<?php
				}
				?>
				</script>
				<?php
				?>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2"><input type="button" value="<?= GetMessage("SAPE1_ADD1"); ?>" onclick="AffAddSectionRow(-1);"></td>
	</tr>
<?php
$tabControl->EndTab();

$tabControl->Buttons([
	'disabled' => ($saleModulePermissions < 'W'),
	'back_url' => '/bitrix/admin/sale_affiliate_plan.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
]);

$tabControl->End();
?>
</form>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
