<?php

/** @global CMain $APPLICATION */

use Bitrix\Main;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$request = Main\Context::getCurrent()->getRequest();

$bPublicMode = defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1;

set_time_limit(0);
$IBLOCK_ID = (int)$request->get('IBLOCK_ID');
$STEP = (int)$request->get('STEP');
if ($STEP <= 0)
{
	$STEP = 1;
}
if ($request->isPost())
{
	if ($request->getPost('backButton') !== null)
	{
		$STEP -= 2;
	}
	if ($request->getPost('backButton2') !== null)
	{
		$STEP = 1;
	}
}

$NUM_CATALOG_LEVELS = (int)Main\Config\Option::get('iblock', 'num_catalog_levels');
if ($NUM_CATALOG_LEVELS <= 0)
{
	$NUM_CATALOG_LEVELS = 3;
}
$strError = "";
$DATA_FILE_NAME = "";

$num_rows_writed = 0;

/////////////////////////////////////////////////////////////////////

/*
This function takes an array (arTuple) which is mix of scalar values and arrays
and return "rectangular" array of arrays.
For example:
array(1, array(1, 2), 3, arrays(4, 5))
will be transformed as
array(
	array(1, 1, 3, 4),
	array(1, 1, 3, 5),
	array(1, 2, 3, 4),
	array(1, 2, 3, 5),
)
*/
function ArrayMultiply(&$arResult, $arTuple, $arTemp = array())
{
	global $csvFile, $DATA_FILE_NAME, $num_rows_writed;
	if(empty($arTuple))
	{
		/** @var CCSVData $csvFile */
		$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $arTemp);
		$num_rows_writed++;
	}
	else
	{
		$head = array_shift($arTuple);
		$arTemp[] = false;
		if(is_array($head))
		{
			if(empty($head))
			{
				$arTemp[count($arTemp)-1] = "";
				ArrayMultiply($arResult, $arTuple, $arTemp);
			}
			else
			{
				foreach ($head as $value)
				{
					$arTemp[count($arTemp)-1] = $value;
					ArrayMultiply($arResult, $arTuple, $arTemp);
				}
			}
		}
		else
		{
			$arTemp[count($arTemp)-1] = $head;
			ArrayMultiply($arResult, $arTuple, $arTemp);
		}
	}
}
/////////////////////////////////////////////////////////////////////

$delimiterList = [
	'TZP' => GetMessage('IBLOCK_ADM_EXP_DELIM_TZP'),
	'ZPT' => GetMessage('IBLOCK_ADM_EXP_DELIM_ZPT'),
	'TAB' => GetMessage('IBLOCK_ADM_EXP_DELIM_TAB'),
	'SPS' => GetMessage('IBLOCK_ADM_EXP_DELIM_SPS'),
	'OTR' => GetMessage('IBLOCK_ADM_EXP_DELIM_OTR'),
];

$fields_type = (string)$request->getPost('fields_type');
$delimiter_r = (string)$request->getPost('delimiter_r');
if (!isset($delimiterList[$delimiter_r]))
{
	$delimiter_r = 'TZP';
}
$delimiter_other_r = (string)$request->getPost('delimiter_other_r');
$first_line_names = (string)$request->getPost('first_line_names');
$field_needed = $request->getPost('field_needed') ?? [];
$field_code = $request->getPost('field_code') ?? [];
$field_num = $request->getPost('field_num') ?? [];

if ($request->isPost() && $STEP > 1 && check_bitrix_sessid())
{
	//*****************************************************************//
	$arIBlock = false;
	if ($IBLOCK_ID > 0)
	{
		$arIBlockRes = CIBlock::GetList(
			["SORT" => "ASC"],
			[
				"ID" => $IBLOCK_ID,
				"MIN_PERMISSION" => "X",
				"OPERATION" => "iblock_export",
			]
		);
		$arIBlockRes = new CIBlockResult($arIBlockRes);
		$arIBlock = $arIBlockRes->GetNext();
	}

	if ($IBLOCK_ID <= 0 || !$arIBlock)
	{
		$strError .= GetMessage("IBLOCK_ADM_EXP_NO_IBLOCK") . "<br>";
	}

	if ($strError !== '')
	{
		$STEP = 1;
	}
	//*****************************************************************//

	if ($STEP > 2)
	{
		//*****************************************************************//
		$csvFile = new CCSVData();

		if ($fields_type !== "F" && $fields_type !== "R")
		{
			$strError .= GetMessage("IBLOCK_ADM_EXP_NO_FORMAT") . "<br>";
		}

		$csvFile->SetFieldsType($fields_type);

		$delimiter_r_char = "";
		switch ($delimiter_r)
		{
			case "TAB":
				$delimiter_r_char = "\t";
				break;
			case "ZPT":
				$delimiter_r_char = ",";
				break;
			case "SPS":
				$delimiter_r_char = " ";
				break;
			case "OTR":
				$delimiter_r_char = mb_substr($delimiter_other_r, 0, 1);
				break;
			case "TZP":
				$delimiter_r_char = ";";
				break;
		}

		if (mb_strlen($delimiter_r_char) !== 1)
		{
			$strError .= GetMessage("IBLOCK_ADM_EXP_NO_DELIMITER") . "<br>";
		}

		if ($strError === '')
		{
			$csvFile->SetDelimiter($delimiter_r_char);
		}

		$fileName = (string)$request->getPost('DATA_FILE_NAME');
		if ($fileName === '')
		{
			$strError .= GetMessage("IBLOCK_ADM_EXP_NO_FILE_NAME")."<br>";
		}
		elseif (
			preg_match('/[^a-zA-Z0-9\s!#\$%&\(\)\[\]\{\}+\.;=@\^_\~\/\\\\\-]/i', $fileName)
			|| preg_match('/^[a-z]+:\\/\\//i', $fileName)
			|| HasScriptExtension($fileName)
		)
		{
			$strError .= GetMessage("IBLOCK_ADM_EXP_FILE_NAME_ERROR")."<br>";
		}
		else
		{
			$DATA_FILE_NAME = Rel2Abs("/", $fileName);
			if (mb_strtolower(mb_substr($DATA_FILE_NAME, mb_strlen($DATA_FILE_NAME) - 4)) != ".csv")
			{
				$DATA_FILE_NAME .= ".csv";
			}
		}

		if ($strError === '')
		{
			$fp = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "w");
			if(!is_resource($fp))
			{
				$strError .= GetMessage("IBLOCK_ADM_EXP_CANNOT_CREATE_FILE")."<br>";
				$DATA_FILE_NAME = "";
			}
			else
			{
				fclose($fp);
			}
		}

		if (!is_array($field_needed) || !in_array("Y", $field_needed))
			$strError .= GetMessage("IBLOCK_ADM_EXP_NO_FIELDS")."<br>";

		if ($strError == '')
		{
			$selectArray = array(
				"ID",
				"IBLOCK_ID",
				"IBLOCK_SECTION_ID",
			);
			$bNeedGroups = false;
			$bNeedProps  = false;
			$arNeedFields = array();
			foreach ($field_code as $i => $value)
			{
				if (isset($field_needed[$i]) && $field_needed[$i] === "Y")
				{
					if(strncmp($value, "IE_", 3) == 0)
					{
						$selectArray[] = mb_substr($value, 3);
					}
					elseif(strncmp($value, "IC_GROUP", 8) == 0)
					{
						$bNeedGroups = true;
					}
					elseif(!$bNeedProps && (strncmp($value, "IP_PROP", 7) == 0))
					{
						$selectArray[] = "PROPERTY_*";
						$bNeedProps = true;
					}

					$j = (int)($field_num[$i] ?? 0);
					while (array_key_exists($j, $arNeedFields))
					{
						$j++;
					}
					$arNeedFields[$j] = $value;
				}
			}
			ksort($arNeedFields);

			if ($first_line_names === "Y")
			{
				$arResFields = array();
				foreach($arNeedFields as $field_name)
				{
					$arResFields[] = $field_name;
				}
				$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $arResFields);
			}

			$res = CIBlockElement::GetList(
				array(),
				array("IBLOCK_ID" => $IBLOCK_ID, "MIN_PERMISSION" => "W"),
				false,
				false,
				$selectArray
			);

			$arUserTypeFormat = false;

			while ($obElement = $res->GetNextElement())
			{
				$arElement = $obElement->GetFields();
				if(array_key_exists("PREVIEW_PICTURE", $arElement))
				{
					$arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
					if($arElement["PREVIEW_PICTURE"])
						$arElement["~PREVIEW_PICTURE"] = $arElement["PREVIEW_PICTURE"]["SRC"];
				}
				if(array_key_exists("DETAIL_PICTURE", $arElement))
				{
					$arElement["DETAIL_PICTURE"] = CFile::GetFileArray($arElement["DETAIL_PICTURE"]);
					if($arElement["DETAIL_PICTURE"])
						$arElement["~DETAIL_PICTURE"] = $arElement["DETAIL_PICTURE"]["SRC"];
				}

				if($bNeedProps)
					$arProperties = $obElement->GetProperties();
				else
					$arProperties = array();

				if($arUserTypeFormat === false)
				{
					$arUserTypeFormat = array();
					foreach($arProperties as $prop_id => $arProperty)
					{
						$arUserTypeFormat[$arProperty["ID"]] = false;
						if($arProperty["USER_TYPE"] <> '')
						{
							$arUserType = CIBlockProperty::GetUserType($arProperty["USER_TYPE"]);
							if(isset($arUserType["GetPublicViewHTML"]))
							{
								$arUserTypeFormat[$arProperty["ID"]] = $arUserType["GetPublicViewHTML"];
							}
						}
					}
				}

				$arPropsValues = array();
				foreach($arProperties as $prop_id => $arProperty)
				{
					if($arUserTypeFormat[$arProperty["ID"]])
					{
						if ($arProperty['MULTIPLE'] == 'Y' && is_array($arProperty["~VALUE"]))
						{
							$arValues = array();
							foreach($arProperty["~VALUE"] as $value)
								$arValues[] = call_user_func_array($arUserTypeFormat[$arProperty["ID"]],
									array(
										$arProperty,
										array("VALUE" => $value),
										array("MODE" => "CSV_EXPORT"),
									));
						}
						else
						{
							$arValues = call_user_func_array($arUserTypeFormat[$arProperty["ID"]],
								array(
									$arProperty,
									array("VALUE" => $arProperty["~VALUE"]),
									array("MODE" => "CSV_EXPORT"),
								));
						}
					}
					elseif($arProperty["PROPERTY_TYPE"] == "F")
					{
						if(is_array($arProperty["~VALUE"]))
						{
							$arValues = array();
							foreach($arProperty["~VALUE"] as $file_id)
							{
								$file = CFile::GetFileArray($file_id);
								if($file)
									$arValues[] = $file["SRC"];
							}
						}
						elseif($arProperty["~VALUE"] > 0)
						{
							$file = CFile::GetFileArray($arProperty["~VALUE"]);
							if($file)
								$arValues = $file["SRC"];
							else
								$arValues = "";
						}
						else
						{
							$arValues = "";
						}
					}
					else
					{
						$arValues = $arProperty["~VALUE"];
					}
					$arPropsValues[$arProperty["ID"]] = $arValues;
				}

				$arResSections = array();
				if($bNeedGroups)
				{
					if($arElement["IBLOCK_SECTION_ID"] > 0)
					{

						$arPath = array();
						$rsPath = CIBlockSection::GetNavChain(
							$IBLOCK_ID,
							$arElement["IBLOCK_SECTION_ID"],
							[
								'ID',
								'NAME',
							],
							true
						);
						foreach ($rsPath as $arPathSection)
						{
							$arPath[] = $arPathSection["NAME"];
						}
						unset($arPathSection, $rsPath);
						$arResSections[$arElement["IBLOCK_SECTION_ID"]] = $arPath;
					}

					$arSections = array();
					$rsSections = CIBlockElement::GetElementGroups($arElement["ID"], true, array("ID"));
					while($arSection = $rsSections->Fetch())
					{
						$arSections[] = (int)$arSection["ID"];
					}
					sort($arSections);

					foreach ($arSections as $sectionId)
					{
						if (!isset($arResSections[$sectionId]))
						{
							$arPath = array();
							$rsPath = CIBlockSection::GetNavChain(
								$IBLOCK_ID,
								$sectionId,
								[
									'ID',
									'NAME',
								],
								true
							);
							foreach ($rsPath as $arPathSection)
							{
								$arPath[] = $arPathSection["NAME"];
							}
							unset($arPathSection, $rsPath);
							$arResSections[$sectionId] = $arPath;
						}
					}
				}
				if (empty($arResSections))
					$arResSections[] = [];

				$arResFields = array();
				foreach($arResSections as $arPath)
				{
					$arTuple = array();
					foreach($arNeedFields as $field_name)
					{
						if(strncmp($field_name, "IE_", 3) == 0)
						{
							$arTuple[] = $arElement["~" . mb_substr($field_name, 3)];
						}
						elseif(strncmp($field_name, "IP_PROP", 7) === 0)
						{
							$index = (int)mb_substr($field_name, 7);
							$arTuple[] = $arPropsValues[$index] ?? null;
						}
						elseif(strncmp($field_name, "IC_GROUP", 8) === 0)
						{
							$index = (int)mb_substr($field_name, 8);
							$arTuple[] = $arPath[$index] ?? null;
						}
					}

					ArrayMultiply($arResFields, $arTuple);
				}
			}
		}

		if ($strError !== '')
		{
			$STEP = 2;
		}
		elseif ($bPublicMode)
		{
?>
<div id="result">
	<div style="text-align: center; margin: 20px;">
<?= GetMessage(
	'IBLOCK_ADM_EXP_LINES_EXPORTED',
	[
		'#LINES#' => '<b>' . $num_rows_writed . '</b>',
	]
); ?><br />
<?= GetMessage(
	'IBLOCK_ADM_EXP_DOWNLOAD_RESULT',
	[
		'#HREF#' => '<a href="' . htmlspecialcharsbx($DATA_FILE_NAME) . '">'
			. htmlspecialcharsbx($DATA_FILE_NAME)
			. '</a>'
		,
	]
); ?>
	</div>
</div>

<script>
top.BX.closeWait();
var w = top.BX.WindowManager.Get();
w.SetTitle('<?=CUtil::JSEscape(GetMessage("IBLOCK_ADM_EXP_PAGE_TITLE")." ".$STEP)?>');
w.SetHead('');
w.ClearButtons();
w.SetContent(document.getElementById('result').innerHTML);
w.SetButtons(w.btnClose);
</script>
<?php
			die();
		}
		//*****************************************************************//
	}

	//*****************************************************************//
}
/////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_EXP_PAGE_TITLE")." ".$STEP);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
CAdminMessage::ShowMessage($strError);
?>
<form method="POST" action="<?=$APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID; ?>" ENCTYPE="multipart/form-data" name="dataload">
<input type="hidden" name="STEP" value="<?= $STEP + 1;?>">
<?=bitrix_sessid_post()?>
<?php
if ($STEP > 1)
{
	?><input type="hidden" name="IBLOCK_ID" value="<?= $IBLOCK_ID ?>"><?php
}
if (!$bPublicMode)
{
	$aTabs = [
		[
			"DIV" => "edit1",
			"TAB" => GetMessage("IBLOCK_ADM_EXP_TAB1"),
			"ICON" => "iblock",
			"TITLE" => GetMessage("IBLOCK_ADM_EXP_TAB1_ALT")
		],
		[
			"DIV" => "edit2",
			"TAB" => GetMessage("IBLOCK_ADM_EXP_TAB2"),
			"ICON" => "iblock",
			"TITLE" => GetMessage("IBLOCK_ADM_EXP_TAB2_ALT")
		],
		[
			"DIV" => "edit3",
			"TAB" => GetMessage("IBLOCK_ADM_EXP_TAB3"),
			"ICON" => "iblock",
			"TITLE" => GetMessage("IBLOCK_ADM_EXP_TAB3_ALT")
		],
	];
}
else
{
	$aTabs = [
		[
			"DIV" => "edit2",
			"TAB" => GetMessage("IBLOCK_ADM_EXP_TAB2"),
			"ICON" => "iblock",
			"TITLE" => GetMessage("IBLOCK_ADM_EXP_TAB2_ALT")
		]
	];
}

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();

if (!$bPublicMode)
{
	$tabControl->BeginNextTab();

	if ($STEP < 2)
	{
?>
	<tr>
		<td width="40%"><?= GetMessage("IBLOCK_ADM_EXP_CHOOSE_IBLOCK") ?></td>
		<td width="60%">
			<?= GetIBlockDropDownListEx(
				$IBLOCK_ID,
				'IBLOCK_TYPE_ID',
				'IBLOCK_ID',
				array(
					"MIN_PERMISSION" => "X",
					"OPERATION" => "iblock_export",
				),
				'',
				'',
				'class="adm-detail-iblock-types"',
				'class="adm-detail-iblock-list"'
			);?>
		</td>
	</tr>
<?php
	}

	$tabControl->EndTab();
}

$tabControl->BeginNextTab();

if ($STEP == 2)
{
	?>
	<tr class="heading">
		<td colspan="2">
			<?= GetMessage("IBLOCK_ADM_EXP_CHOOSE_FORMAT") ?>
			<input type="hidden" name="fields_type" value="R">
		</td>
	</tr>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_EXP_DELIMITER") ?>:</td>
		<td width="60%"><?php
			foreach ($delimiterList as $value => $message)
			{
				$safeValueId = htmlspecialcharsbx('delimiter_' . $value);
				?>
				<input
					type="radio" name="delimiter_r" id="<?=$safeValueId; ?>"
					value="<?=htmlspecialcharsbx($value);?>"<?=($delimiter_r === $value ? ' checked' : '');?>
				><label for="<?=$safeValueId; ?>"><?= htmlspecialcharsbx($message); ?></label><br>
				<?php
			}
			?>
			<input type="text" name="delimiter_other_r" size="3" value="<?= htmlspecialcharsbx($delimiter_other_r) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_ADM_EXP_FIRST_LINE_NAMES") ?>:</td>
		<td>
			<input type="checkbox" name="first_line_names" value="Y"<?= ($first_line_names=="Y" || $strError === '' ? ' checked' : ''); ?>>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_EXP_FIELDS_MAPPING") ?></td>
	</tr>

	<tr>
		<td colspan="2">
				<?php
				$arAvailFields = array(
					array("value"=>"IE_XML_ID", "name"=>GetMessage("IBLOCK_FIELD_XML_ID")." (B_IBLOCK_ELEMENT.XML_ID)"),
					array("value"=>"IE_NAME", "name"=>GetMessage("IBLOCK_FIELD_NAME")." (B_IBLOCK_ELEMENT.NAME)"),
					array("value"=>"IE_ID", "name"=>GetMessage("IBLOCK_FIELD_ID")." (B_IBLOCK_ELEMENT.ID)"),
					array("value"=>"IE_ACTIVE", "name"=>GetMessage("IBLOCK_FIELD_ACTIVE")." (B_IBLOCK_ELEMENT.ACTIVE)"),
					array("value"=>"IE_ACTIVE_FROM", "name"=>GetMessage("IBLOCK_FIELD_ACTIVE_FROM")." (B_IBLOCK_ELEMENT.ACTIVE_FROM)"),
					array("value"=>"IE_ACTIVE_TO", "name"=>GetMessage("IBLOCK_FIELD_ACTIVE_TO")." (B_IBLOCK_ELEMENT.ACTIVE_TO)"),
					array("value"=>"IE_PREVIEW_PICTURE", "name"=>GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE")." (B_IBLOCK_ELEMENT.PREVIEW_PICTURE)"),
					array("value"=>"IE_PREVIEW_TEXT", "name"=>GetMessage("IBLOCK_FIELD_PREVIEW_TEXT")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT)"),
					array("value"=>"IE_PREVIEW_TEXT_TYPE", "name"=>GetMessage("IBLOCK_FIELD_PREVIEW_TEXT_TYPE")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT_TYPE)"),
					array("value"=>"IE_DETAIL_PICTURE", "name"=>GetMessage("IBLOCK_FIELD_DETAIL_PICTURE")." (B_IBLOCK_ELEMENT.DETAIL_PICTURE)"),
					array("value"=>"IE_DETAIL_TEXT", "name"=>GetMessage("IBLOCK_FIELD_DETAIL_TEXT")." (B_IBLOCK_ELEMENT.DETAIL_TEXT)"),
					array("value"=>"IE_DETAIL_TEXT_TYPE", "name"=>GetMessage("IBLOCK_FIELD_DETAIL_TEXT_TYPE")." (B_IBLOCK_ELEMENT.DETAIL_TEXT_TYPE)"),
					array("value"=>"IE_CODE", "name"=>GetMessage("IBLOCK_FIELD_CODE")." (B_IBLOCK_ELEMENT.CODE)"),
					array("value"=>"IE_SORT", "name"=>GetMessage("IBLOCK_FIELD_SORT")." (B_IBLOCK_ELEMENT.SORT)"),
					array("value"=>"IE_TAGS", "name"=>GetMessage("IBLOCK_FIELD_TAGS")." (B_IBLOCK_ELEMENT.TAGS)"),
				);
				$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
				while ($prop_fields = $properties->Fetch())
				{
					$arAvailFields[] = array(
						"value" => "IP_PROP".$prop_fields["ID"],
						"name" => GetMessage("IBLOCK_ADM_EXP_PROPERTY", array("#PROPERTY_NAME#" => htmlspecialcharsex($prop_fields["NAME"]))),
					);
				}
				for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
				{
					$arAvailFields[] = array(
						"value" => "IC_GROUP".$i,
						"name" => GetMessage("IBLOCK_ADM_EXP_GROUP_LEVEL", array("#LEVEL_NUM#" => ($i+1))),
					);
				}
				$intCountFields = count($arAvailFields);
				$intCountChecked = 0;
				$arCheckID = array();
				for ($i = 0; $i < $intCountFields; $i++)
				{
					if (
						(isset($field_needed[$i]) && $field_needed[$i] === "Y")
						|| (empty($field_needed) && $strError === '')
					)
					{
						$arCheckID[] = $i;
						$intCountChecked++;
					}
				}
			?><table width="100%" border="0" cellspacing="0" cellpadding="0" class="internal">
				<tr class="heading">
					<td style="text-align: left !important;"><input type="checkbox" name="field_needed_all" id="field_needed_all" value="Y" onclick="checkAll(this,<?= $intCountFields; ?>);"<?= ($intCountChecked === $intCountFields ? ' checked' : ''); ?>>&nbsp;<?= GetMessage("IBLOCK_ADM_EXP_IS_FIELD_NEEDED") ?></td>
					<td><?= GetMessage("IBLOCK_ADM_EXP_FIELD_NAME") ?></td>
					<td><?= GetMessage("IBLOCK_ADM_EXP_FIELD_SORT") ?></td>
				</tr><?php
				for ($i = 0; $i < $intCountFields; $i++)
				{
					$fieldNumValue = (int)($field_num[$i] ?? (10 * ($i + 1)));
					?>
					<tr>
						<td style="text-align: left !important;">
							<input type="checkbox" name="field_needed[<?= $i ?>]" id="field_needed_<?= $i; ?>"<?= (in_array($i, $arCheckID) ? ' checked' : ''); ?> value="Y" onclick="checkOne(this,<?= $intCountFields; ?>);">
						</td>
						<td>
							<?= ($i < 2 ? '<b>' : '') . $arAvailFields[$i]["name"] . ($i < 2 ? '</b>' : ''); ?>
						</td>
						<td align="center">
							<?= ($i < 2 ? '<b>' : ''); ?>
							<input type="text" name="field_num[<?= $i ?>]" value="<?= $fieldNumValue; ?>" size="2">
							<input type="hidden" name="field_code[<?= $i ?>]" value="<?= htmlspecialcharsbx($arAvailFields[$i]["value"]); ?>">
							<?= ($i < 2 ? '</b>' : ''); ?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
			<input type="hidden" name="count_checked" id="count_checked" value="<?= $intCountChecked; ?>">
			<script>
			function checkAll(obj,cnt)
			{
				var boolCheck = obj.checked;
				for (i = 0; i < cnt; i++)
				{
					BX('field_needed_'+i).checked = boolCheck;
				}
				BX('count_checked').value = (boolCheck ? cnt : 0);
			}
			function checkOne(obj,cnt)
			{
				const boolCheck = obj.checked;
				let intCurrent = parseInt(BX('count_checked').value);
				intCurrent += (boolCheck ? 1 : -1);
				BX('field_needed_all').checked = (intCurrent >= cnt);
				BX('count_checked').value = intCurrent;
			}
			</script>
			<br><br>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_EXP_FILE_NAME") ?></td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_ADM_EXP_ENTER_FILE_NAME") ?>:</td>
		<td><?php
			if ($DATA_FILE_NAME <> '')
			{
				$exportFileName = $DATA_FILE_NAME;
			}
			else
			{
				$exportFileName = "/".Main\Config\Option::get("main", "upload_dir", "upload")."/export_file_";
				$exportFileName .= Main\Security\Random::getString(16);
				$exportFileName .= '.csv';
			}
			?>
			<input type="text" name="DATA_FILE_NAME" size="40" value="<?=htmlspecialcharsbx($exportFileName); ?>"><br>
			<small><?= GetMessage("IBLOCK_ADM_EXP_FILE_WARNING") ?></small>
		</td>
	</tr>
	<?php
}

$tabControl->EndTab();

if (!$bPublicMode)
{
	$tabControl->BeginNextTab();

	if ($STEP > 2)
	{
?>
	<tr>
		<td>
		<?php
		$message = GetMessage(
			"IBLOCK_ADM_EXP_LINES_EXPORTED",
			array(
				"#LINES#" => "<b>". $num_rows_writed . "</b>"
			)
		);
		$message .= '<br>'
			. GetMessage(
				"IBLOCK_ADM_EXP_DOWNLOAD_RESULT",
				array(
					"#HREF#" => '<a href="' . htmlspecialcharsbx($DATA_FILE_NAME) . '" target="_blank">'
						. htmlspecialcharsex($DATA_FILE_NAME)
						. '</a>'
				)
			)
		;
		CAdminMessage::ShowMessage([
			"TYPE" => "PROGRESS",
			"MESSAGE" => GetMessage("IBLOCK_ADM_EXP_SUCCESS"),
			"DETAILS" => $message,
			"HTML" => true,
		]);
		?>
		</td>
	</tr>
	<?php
	}
	$tabControl->EndTab();
}

if ($bPublicMode):
	$tabControl->Buttons(array());
else:
	$tabControl->Buttons();
	if ($STEP < 3):
		if ($STEP > 1):
?>
		<input type="submit" name="backButton" value="&lt;&lt; <?= GetMessage("IBLOCK_ADM_EXP_BACK_BUTTON") ?>">
<?php
		endif;
?>
	<input type="submit" value="<?= ($STEP==2)?GetMessage("IBLOCK_ADM_EXP_FINISH_BUTTON"):GetMessage("IBLOCK_ADM_EXP_NEXT_BUTTON") ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">
<?php
	else:
?>
	<input type="submit" name="backButton2" value="&lt;&lt; <?= GetMessage("IBLOCK_ADM_EXP_RESTART_BUTTON") ?>" class="adm-btn-save">
<?php
	endif;
endif;

$tabControl->End();
if (!$bPublicMode):
?>
<script>
BX.ready(function() {
<?php
if ($STEP < 2):
?>
	tabControl.SelectTab("edit1");
	tabControl.DisableTab("edit2");
	tabControl.DisableTab("edit3");
<?php
elseif ($STEP == 2):
?>
	tabControl.SelectTab("edit2");
	tabControl.DisableTab("edit1");
	tabControl.DisableTab("edit3");
<?php
elseif ($STEP > 2):
?>
	tabControl.SelectTab("edit3");
	tabControl.DisableTab("edit1");
	tabControl.DisableTab("edit2");
<?php
endif;
?>
});
</script>
<?php
endif;
?>
</form>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
