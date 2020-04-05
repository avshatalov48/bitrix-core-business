<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
$arParams['ELEMENT_CODE'] = ($arParams["ELEMENT_ID"] > 0 ? '' : trim($arParams['ELEMENT_CODE']));
$arParams['SHOW_DEACTIVATED'] = (isset($arParams['SHOW_DEACTIVATED']) && $arParams['SHOW_DEACTIVATED'] == 'Y' ? 'Y' : 'N');

$arParams['CACHE_GROUPS'] = (isset($arParams['CACHE_GROUPS']) && $arParams['CACHE_GROUPS'] == 'N' ? 'N' : 'Y');
if (!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600000;

$arParams['ELEMENT_COUNT'] = (isset($arParams['ELEMENT_COUNT']) ? (int)$arParams['ELEMENT_COUNT'] : 0);
$arParams['SINGLE_COMPONENT'] = (isset($arParams['SINGLE_COMPONENT']) && $arParams['SINGLE_COMPONENT'] == 'Y' ? 'Y' : 'N');

if(!isset($arParams["WIDTH"]) || intval($arParams["WIDTH"]) <= 0)
	$arParams["WIDTH"] = 120;

if(!isset($arParams["HEIGHT"]) || intval($arParams["HEIGHT"]) <= 0)
	$arParams["HEIGHT"] = 50;

if(!isset($arParams["WIDTH_SMALL"]) || intval($arParams["WIDTH_SMALL"]) <= 0)
	$arParams["WIDTH_SMALL"] = 21;

if(!isset($arParams["HEIGHT_SMALL"]) || intval($arParams["HEIGHT_SMALL"]) <= 0)
	$arParams["HEIGHT_SMALL"] = 17;

if (!isset($arParams['PROP_CODE']))
	$arParams['PROP_CODE'] = array();
if (!is_array($arParams['PROP_CODE']))
	$arParams['PROP_CODE'] = array($arParams['PROP_CODE']);

//Let's cache it
$additionalCache = $arParams["CACHE_GROUPS"] === "N"? false: array($USER->GetGroups());
if ($this->startResultCache(false, $additionalCache))
{
	if (!Loader::includeModule("iblock"))
	{
		$this->abortResultCache();
		ShowError(GetMessage("IBLOCK_CBB_IBLOCK_NOT_INSTALLED"));
		return;
	}

	if (!Loader::includeModule('highloadblock'))
	{
		$this->abortResultCache();
		ShowError(GetMessage("IBLOCK_CBB_HLIBLOCK_NOT_INSTALLED"));
		return;
	}

	$arParams['PROP_CODE'] = array_filter($arParams['PROP_CODE'], 'CIBlockParameters::checkParamValues');
	if (empty($arParams['PROP_CODE']))
	{
		$this->abortResultCache();
		return;
	}

	//Handle case when ELEMENT_CODE used
	if($arParams["ELEMENT_ID"] <= 0)
	{
		$findFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => 'R'
		);
		if ($arParams["SHOW_DEACTIVATED"] !== "Y")
			$findFilter["ACTIVE"] = "Y";

		$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["~ELEMENT_CODE"],
			false,
			false,
			$findFilter
		);
		$arParams["ELEMENT_ID"] = (int)$arParams["ELEMENT_ID"];
	}
	$arResult['ID'] = $arParams["ELEMENT_ID"];

	$arBrandBlocks = array();

	$propList = array();

	// Show only linked to element brands
	if ($arResult['ID'] > 0)
	{
		foreach ($arParams['PROP_CODE'] as &$oneCode)
		{
			$rsProps = CIBlockElement::GetProperty(
				$arParams['IBLOCK_ID'],
				$arResult['ID'],
				'sort',
				'asc',
				array(
					'CODE' => $oneCode,
					'PROPERTY_TYPE' => 'S',
					'ACTIVE' => 'Y',
					'EMPTY' => 'N'
				)
			);
			while ($prop = $rsProps->Fetch())
			{
				$prop['ID'] = (int)$prop['ID'];
				$prop['USER_TYPE'] = (string)$prop['USER_TYPE'];
				if ($prop['USER_TYPE'] !== 'directory')
					continue;
				if (!isset($prop['USER_TYPE_SETTINGS']['TABLE_NAME']) || empty($prop['USER_TYPE_SETTINGS']['TABLE_NAME']))
					continue;
				if ($prop['MULTIPLE'] == 'N')
				{
					$propList[$prop['ID']] = $prop;
				}
				else
				{
					if (!isset($propList[$prop['ID']]))
					{
						$prop['VALUE'] = array($prop['VALUE']);
						$propList[$prop['ID']] = $prop;
					}
					else
					{
						$propList[$prop['ID']]['VALUE'][] = $prop['VALUE'];
					}
				}
			}
		}
		unset($oneCode);
	}
	else // Show all rows from table
	{
		foreach ($arParams['PROP_CODE'] as &$oneCode)
		{
			$rsProps = CIBlockProperty::GetList(
				array('SORT' => 'ASC', 'ID' => 'ASC'),
				array(
					'IBLOCK_ID' => $arParams['IBLOCK_ID'],
					'CODE' => $oneCode,
					'PROPERTY_TYPE' => 'S:directory',
					'ACTIVE' => 'Y'
				)
			);
			while ($prop = $rsProps->Fetch())
			{
				$prop['ID'] = (int)$prop['ID'];
				$prop['USER_TYPE'] = (string)$prop['USER_TYPE'];
				if (!isset($prop['USER_TYPE_SETTINGS']['TABLE_NAME']) || empty($prop['USER_TYPE_SETTINGS']['TABLE_NAME']))
					continue;
				$prop['VALUE'] = false;
				$propList[$prop['ID']] = $prop;
			}
		}
		unset($oneCode);
	}

	if (empty($propList))
	{
		$this->abortResultCache();
		return;
	}

	$hlblocks = array();
	$reqParams = array();

	foreach ($propList as &$prop)
	{
		if (!isset($hlblocks[$prop['USER_TYPE_SETTINGS']['TABLE_NAME']]))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
				array('filter' => array('=TABLE_NAME' => $prop['USER_TYPE_SETTINGS']['TABLE_NAME']))
			)->fetch();

			$hlblocks[$prop['USER_TYPE_SETTINGS']['TABLE_NAME']] = $hlblock;
		}
		else
		{
			$hlblock = $hlblocks[$prop['USER_TYPE_SETTINGS']['TABLE_NAME']];
		}

		if (isset($hlblock['ID']))
		{
			if (!isset($reqParams[$hlblock['ID']]))
			{
				$reqParams[$hlblock['ID']] = array(
					'HLB' => $hlblock
				);
			}
			if ($prop['VALUE'] === false)
			{
				$reqParams[$hlblock['ID']]['VALUES'] = false;
			}
			else
			{
				$reqParams[$hlblock['ID']]['VALUES'] = (
					isset($reqParams[$hlblock['ID']]['VALUES'])
					? array_merge($reqParams[$hlblock['ID']]['VALUES'], $prop['VALUE'])
					: $prop['VALUE']
				);
			}
		}
	}
	unset($prop);

	if (empty($reqParams))
	{
		$this->abortResultCache();
		return;
	}

	$checkCount = ($arParams['SINGLE_COMPONENT'] == 'Y' && $arParams['ELEMENT_COUNT'] > 0);
	$fullCount = 0;
	foreach ($reqParams as &$params)
	{
		$boolName = true;
		$boolPict = true;

		$hlblockName = $params['HLB']['NAME'];
		$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($params['HLB']);
		$entityDataClass = $entity->getDataClass();
		$fieldsList = $entityDataClass::getMap();
		if (count($fieldsList) === 1 && isset($fieldsList['ID']))
			$fieldsList = $entityDataClass::getEntity()->getFields();

		$directoryOrder = array();
		if (isset($fieldsList['UF_SORT']))
			$directoryOrder['UF_SORT'] = 'ASC';
		$directoryOrder['ID'] = 'ASC';

		$arFilter = array(
			'order' => $directoryOrder
		);
		if ($arParams['ELEMENT_COUNT'] > 0)
			$arFilter['limit'] = $arParams['ELEMENT_COUNT'];

		if($arResult['ID'] > 0 && $params['VALUES'] !== false)
		{
			$arFilter['filter'] = array(
				'=UF_XML_ID' => $params['VALUES']
			);
		}

		$rsPropEnums = $entityDataClass::getList($arFilter);
		while ($arEnum = $rsPropEnums->fetch())
		{
			$boolPict = true;
			if (!isset($arEnum['UF_NAME']))
			{
				$boolName = false;
				break;
			}

			$arEnum['PREVIEW_PICTURE'] = false;
			$arEnum['ID'] = (int)$arEnum['ID'];

			if (!isset($arEnum['UF_FILE']) || (int)$arEnum['UF_FILE'] <= 0)
				$boolPict = false;

			if ($boolPict)
			{
				$arEnum['PREVIEW_PICTURE'] = CFile::GetFileArray($arEnum['UF_FILE']);
				if (empty($arEnum['PREVIEW_PICTURE']))
					$boolPict = false;
			}

			$descrExists = (isset($arEnum['UF_DESCRIPTION']) && (string)$arEnum['UF_DESCRIPTION'] !== '');
			if ($boolPict)
			{
				if ($descrExists)
				{
					$width = $arParams["WIDTH_SMALL"];
					$height = $arParams["HEIGHT_SMALL"];
					$type = "PIC_TEXT";
				}
				else
				{
					$width = $arParams["WIDTH"];
					$height = $arParams["HEIGHT"];
					$type = "ONLY_PIC";
				}

				$arEnum['PREVIEW_PICTURE']['WIDTH'] = (int)$arEnum['PREVIEW_PICTURE']['WIDTH'];
				$arEnum['PREVIEW_PICTURE']['HEIGHT'] = (int)$arEnum['PREVIEW_PICTURE']['HEIGHT'];
				if (
					$arEnum['PREVIEW_PICTURE']['WIDTH'] > $width
					|| $arEnum['PREVIEW_PICTURE']['HEIGHT'] > $height
					)
				{
					$arEnum['PREVIEW_PICTURE'] = CFile::ResizeImageGet(
						$arEnum['PREVIEW_PICTURE'],
						array("width" => $width, "height" => $height),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						true
					);

					$arEnum['PREVIEW_PICTURE']['SRC'] = $arEnum['PREVIEW_PICTURE']['src'];
					$arEnum['PREVIEW_PICTURE']['WIDTH'] = $arEnum['PREVIEW_PICTURE']['width'];
					$arEnum['PREVIEW_PICTURE']['HEIGHT'] = $arEnum['PREVIEW_PICTURE']['height'];
				}
			}
			elseif ($descrExists)
			{
				$type = "ONLY_TEXT";
			}
			else //Nothing to show
			{
				continue;
			}
			$arBrandBlocks[$hlblockName.'_'.$arEnum['ID']] = array(
				'TYPE' => $type,
				'NAME' => (isset($arEnum['UF_NAME']) ? $arEnum['UF_NAME'] : false),
				'LINK' => (isset($arEnum['UF_LINK']) && '' != $arEnum['UF_LINK'] ? $arEnum['UF_LINK'] : false),
				'DESCRIPTION' => ($descrExists ? $arEnum['UF_DESCRIPTION'] : false),
				'FULL_DESCRIPTION' => (isset($arEnum['UF_FULL_DESCRIPTION']) && (string)$arEnum['UF_FULL_DESCRIPTION'] !== '' ? $arEnum['UF_FULL_DESCRIPTION'] : false),
				'PICT' => ($boolPict ? $arEnum['PREVIEW_PICTURE'] : false)
			);
			$fullCount++;
			if ($checkCount && $fullCount >= $arParams['ELEMENT_COUNT'])
				break 2;
		}
	}
	unset($params, $reqParams);
	unset($fullCount, $checkCount);

	$arResult["BRAND_BLOCKS"] = $arBrandBlocks;

	$this->includeComponentTemplate();
}