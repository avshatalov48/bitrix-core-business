<?php
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
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if (!isset($arParams["CACHE_TIME"]))
{
	$arParams["CACHE_TIME"] = 36000000;
}

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? '');
$arParams["IBLOCK_ID"] = (int)($arParams["IBLOCK_ID"] ?? 0);
$arParams["SECTION_ID"] = (int)($arParams["SECTION_ID"] ?? 0);
$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"] ?? '');

$arParams["SECTION_URL"] = trim($arParams["SECTION_URL"] ?? '');

$arParams["TOP_DEPTH"] = (int)($arParams["TOP_DEPTH"] ?? 0);
if($arParams["TOP_DEPTH"] <= 0)
{
	$arParams["TOP_DEPTH"] = 2;
}
$arParams["COUNT_ELEMENTS"] = !(isset($arParams["COUNT_ELEMENTS"]) && $arParams["COUNT_ELEMENTS"] === "N");
if (!isset($arParams["COUNT_ELEMENTS_FILTER"]))
{
	$arParams["COUNT_ELEMENTS_FILTER"] = "CNT_ACTIVE";
}
if (
	$arParams["COUNT_ELEMENTS_FILTER"] !== "CNT_ALL"
	&& $arParams["COUNT_ELEMENTS_FILTER"] !== "CNT_ACTIVE"
	&& $arParams["COUNT_ELEMENTS_FILTER"] !== "CNT_AVAILABLE"
)
{
	$arParams["COUNT_ELEMENTS_FILTER"] = "CNT_ALL";
}

if (
	empty($arParams["FILTER_NAME"])
	|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])
)
{
	$arrFilter = array();
}
else
{
	global ${$arParams["FILTER_NAME"]};
	$arrFilter = ${$arParams["FILTER_NAME"]};
	if (!is_array($arrFilter))
	{
		$arrFilter = [];
	}
}
if (
	empty($arParams["ADDITIONAL_COUNT_ELEMENTS_FILTER"])
	|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ADDITIONAL_COUNT_ELEMENTS_FILTER"])
)
{
	$addCountFilter = array();
}
else
{
	global ${$arParams["ADDITIONAL_COUNT_ELEMENTS_FILTER"]};
	$addCountFilter = ${$arParams["ADDITIONAL_COUNT_ELEMENTS_FILTER"]};
	if (!is_array($addCountFilter))
	{
		$addCountFilter = [];
	}
}
$arParams['HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS'] = (
	isset($arParams['HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS'])
	&& $arParams['HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS'] === 'Y'
		? 'Y'
		: 'N'
);

$arParams["CACHE_FILTER"] = isset($arParams["CACHE_FILTER"]) && $arParams["CACHE_FILTER"] == "Y";
if (!$arParams["CACHE_FILTER"] && (!empty($arrFilter) || !empty($addCountFilter)))
{
	$arParams["CACHE_TIME"] = 0;
}

$arParams["ADD_SECTIONS_CHAIN"] = !(isset($arParams["ADD_SECTIONS_CHAIN"]) && $arParams["ADD_SECTIONS_CHAIN"] === "N"); //Turn on by default

$arParams['SHOW_TITLE'] = ($arParams['SHOW_TITLE'] ?? 'N') === 'Y';

$arResult["SECTIONS"] = array();

/*************************************************************************
			Work with cache
*************************************************************************/
if ($this->startResultCache(
	false,
	array(
		$arrFilter,
		$addCountFilter,
		($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())
	)
))
{
	if (!Loader::includeModule("iblock"))
	{
		$this->abortResultCache();
		ShowError(Loc::getMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$existIblock = Iblock\IblockSiteTable::getList(array(
		'select' => array('IBLOCK_ID'),
		'filter' => array(
			'=IBLOCK_ID' => $arParams['IBLOCK_ID'],
			'=SITE_ID' => SITE_ID,
			'=IBLOCK.ACTIVE' => 'Y',
		),
	))->fetch();
	if (empty($existIblock))
	{
		$this->abortResultCache();
		return;
	}

	$countTitleSuffix = '_ELEMENT';
	if (Loader::includeModule('catalog'))
	{
		$catalog = CCatalogSku::GetInfoByIBlock($arParams['IBLOCK_ID']);
		if (!empty($catalog))
		{
			$countTitleSuffix = '_PRODUCT';
		}
	}

	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	);

	$arSelect = array();

	if (!empty($arParams["SECTION_FIELDS"]) && is_array($arParams["SECTION_FIELDS"]))
	{
		foreach($arParams["SECTION_FIELDS"] as &$field)
		{
			if (!empty($field) && is_string($field))
			{
				$arSelect[] = $field;
			}
		}
		unset($field);
	}
	if (!empty($arSelect))
	{
		$arSelect = array_merge(
			$arSelect,
			array(
				"ID",
				"NAME",
				"LEFT_MARGIN",
				"RIGHT_MARGIN",
				"DEPTH_LEVEL",
				"IBLOCK_ID",
				"IBLOCK_SECTION_ID",
				"LIST_PAGE_URL",
				"SECTION_PAGE_URL"
			)
		);
	}
	$boolPicture = empty($arSelect) || in_array('PICTURE', $arSelect);

	if (!empty($arParams["SECTION_USER_FIELDS"]) && is_array($arParams["SECTION_USER_FIELDS"]))
	{
		foreach($arParams["SECTION_USER_FIELDS"] as &$field)
		{
			if(is_string($field) && preg_match("/^UF_/", $field))
			{
				$arSelect[] = $field;
			}
		}
		unset($field);
	}
	$arSelect = array_unique($arSelect);

	$arResult["SECTION"] = false;
	$intSectionDepth = 0;
	if($arParams["SECTION_ID"]>0)
	{
		$arFilter["ID"] = $arParams["SECTION_ID"];
		$rsSections = CIBlockSection::GetList(array(), $arFilter, false, $arSelect);
		$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
		$arResult["SECTION"] = $rsSections->GetNext();
	}
	elseif ($arParams["SECTION_CODE"] !== '')
	{
		$arFilter["=CODE"] = $arParams["SECTION_CODE"];
		$rsSections = CIBlockSection::GetList(array(), $arFilter, false, $arSelect);
		$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
		$arResult["SECTION"] = $rsSections->GetNext();
	}

	if (is_array($arResult["SECTION"]))
	{
		$arResult["SECTION"]["~ELEMENT_CNT"] = null;
		$arResult["SECTION"]["ELEMENT_CNT"] = null;
		unset($arFilter["ID"]);
		unset($arFilter["=CODE"]);
		$arFilter["LEFT_MARGIN"] = $arResult["SECTION"]["LEFT_MARGIN"] + 1;
		$arFilter["RIGHT_MARGIN"] = $arResult["SECTION"]["RIGHT_MARGIN"];
		$arFilter["<="."DEPTH_LEVEL"] = $arResult["SECTION"]["DEPTH_LEVEL"] + $arParams["TOP_DEPTH"];

		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arResult["SECTION"]["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
		$arResult["SECTION"]["IPROPERTY_VALUES"] = $ipropValues->getValues();

		$arResult["SECTION"]["PATH"] = array();
		$rsPath = CIBlockSection::GetNavChain(
			$arResult["SECTION"]["IBLOCK_ID"],
			$arResult["SECTION"]["ID"],
			array(
				"ID", "CODE", "XML_ID", "EXTERNAL_ID", "IBLOCK_ID",
				"IBLOCK_SECTION_ID", "SORT", "NAME", "ACTIVE",
				"DEPTH_LEVEL", "SECTION_PAGE_URL"
			)
		);
		$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
		while($arPath = $rsPath->GetNext())
		{
			if ($arParams["ADD_SECTIONS_CHAIN"])
			{
				$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arParams["IBLOCK_ID"], $arPath["ID"]);
				$arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
			}
			$arResult["SECTION"]["PATH"][]=$arPath;
		}
		unset($rsPath);

		$buttons = CIBlock::GetPanelButtons(
			$arResult["SECTION"]["IBLOCK_ID"],
			0,
			$arResult["SECTION"]["ID"],
			array("SESSID"=>false, "CATALOG"=>true)
		);
		$arResult["SECTION"]["EDIT_LINK"] = $buttons["edit"]["edit_section"]["ACTION_URL"] ?? '';
		$arResult["SECTION"]["DELETE_LINK"] = $buttons["edit"]["delete_section"]["ACTION_URL"] ?? '';
		unset($buttons);
	}
	else
	{
		$arResult["SECTION"] = array(
			"ID" => 0,
			"DEPTH_LEVEL" => 0,
		);
		$arFilter["<="."DEPTH_LEVEL"] = $arParams["TOP_DEPTH"];
	}
	$intSectionDepth = $arResult["SECTION"]['DEPTH_LEVEL'];

	$sectionFilter = array_merge($arrFilter, $arFilter);

	$elementCountFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CHECK_PERMISSIONS" => "Y",
		"MIN_PERMISSION" => "R",
		"INCLUDE_SUBSECTIONS" => (isset($sectionFilter["ELEMENT_SUBSECTIONS"]) && $sectionFilter["ELEMENT_SUBSECTIONS"] == "N" ? "N" : "Y")
	);
	if (!empty($sectionFilter['PROPERTY']) && is_array($sectionFilter['PROPERTY']))
	{
		foreach (array_keys($sectionFilter['PROPERTY']) as $propertyId)
		{
			$field = CIBlock::MkOperationFilter($propertyId);
			$elementCountFilter[$field['PREFIX'].'PROPERTY_'.$field['FIELD']] = $sectionFilter['PROPERTY'][$propertyId];
		}
		unset($field, $propertyId, $value);
	}
	if (!empty($addCountFilter))
	{
		$elementCountFilter = array_merge(
			$addCountFilter,
			$elementCountFilter
		);
	}

	switch ($arParams["COUNT_ELEMENTS_FILTER"])
	{
		case "CNT_ALL":
			break;
		case "CNT_ACTIVE":
			$elementCountFilter["ACTIVE"] = "Y";
			$elementCountFilter["ACTIVE_DATE"] = "Y";
			break;
		case "CNT_AVAILABLE":
			$elementCountFilter["ACTIVE"] = "Y";
			$elementCountFilter["ACTIVE_DATE"] = "Y";
			$elementCountFilter["AVAILABLE"] = "Y";
			break;
	}

	if ($arParams["COUNT_ELEMENTS"] && $arResult['SECTION']['ID'] > 0)
	{
		$elementFilter = $elementCountFilter;
		$elementFilter['SECTION_ID'] = $arResult['SECTION']['ID'];
		if ($arResult['SECTION']['RIGHT_MARGIN'] == ($arResult['SECTION']['LEFT_MARGIN'] + 1))
		{
			$elementFilter['INCLUDE_SUBSECTIONS'] = 'N';
		}
		$arResult["SECTION"]["~ELEMENT_CNT"] = CIBlockElement::GetList(array(), $elementFilter, array());
		$arResult["SECTION"]["ELEMENT_CNT"] = $arResult["SECTION"]["~ELEMENT_CNT"];

		if (!empty($arResult["SECTION"]["ELEMENT_CNT"]))
		{
			$count = (int)$arResult["SECTION"]["ELEMENT_CNT"];
			$val = ($count < 100 ? $count : $count % 100);
			$dec = $val % 10;

			if ($val == 0)
			{
				$messageId = 'CP_BCSL_MESS_COUNT_ZERO';
			}
			elseif ($val == 1)
			{
				$messageId = 'CP_BCSL_MESS_COUNT_ONE';
			}
			elseif ($val >= 10 && $val <= 20)
			{
				$messageId = 'CP_BCSL_MESS_COUNT_TEN';
			}
			elseif ($dec == 1)
			{
				$messageId = 'CP_BCSL_MESS_COUNT_MOD_ONE';
			}
			elseif (2 <= $dec && $dec <= 4)
			{
				$messageId = 'CP_BCSL_MESS_COUNT_MOD_TWO';
			}
			else
			{
				$messageId = 'CP_BCSL_MESS_COUNT_OTHER';
			}
			$messageId .= $countTitleSuffix;

			$arResult["SECTION"]['ELEMENT_CNT_TITLE'] = Loc::getMessage($messageId, ['#VALUE#' => $count]);
		}
	}

	//ORDER BY

	$arSort = array();
	if (!empty($this->arParams['CUSTOM_SECTION_SORT']) && is_array($this->arParams['CUSTOM_SECTION_SORT']))
	{
		foreach ($this->arParams['CUSTOM_SECTION_SORT'] as $field => $value)
		{
			if (!is_string($value))
			{
				continue;
			}
			$field = strtoupper($field);
			if (isset($arSort[$field]))
			{
				continue;
			}
			if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls)?$/i', $value))
			{
				continue;
			}
			$arSort[$field] = $value;
		}
		unset($field, $value);
	}

	if (empty($arSort))
	{
		$arSort = array(
			"LEFT_MARGIN" => "ASC",
		);
	}

	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $sectionFilter, false, $arSelect);
	$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
	while($arSection = $rsSections->GetNext())
	{
		\Bitrix\Iblock\InheritedProperty\SectionValues::queue($arSection["IBLOCK_ID"], $arSection["ID"]);

		$arSection['RELATIVE_DEPTH_LEVEL'] = $arSection['DEPTH_LEVEL'] - $intSectionDepth;

		$arButtons = CIBlock::GetPanelButtons(
			$arSection["IBLOCK_ID"],
			0,
			$arSection["ID"],
			array("SESSID"=>false, "CATALOG"=>true)
		);
		$arSection["EDIT_LINK"] = $arButtons["edit"]["edit_section"]["ACTION_URL"] ?? '';
		$arSection["DELETE_LINK"] = $arButtons["edit"]["delete_section"]["ACTION_URL"] ?? '';

		$arSection["~ELEMENT_CNT"] = null;
		$arSection["ELEMENT_CNT"] = null;
		$arSection['ELEMENT_CNT_TITLE'] = '';

		$arResult["SECTIONS"][]=$arSection;
	}

	$list = [];
	foreach (array_keys($arResult["SECTIONS"]) as $index)
	{
		$arSection = $arResult["SECTIONS"][$index];

		if ($arParams["COUNT_ELEMENTS"])
		{
			$elementFilter = $elementCountFilter;
			$elementFilter["SECTION_ID"] = $arSection["ID"];
			if ($arSection['RIGHT_MARGIN'] == ($arSection['LEFT_MARGIN'] + 1))
			{
				$elementFilter['INCLUDE_SUBSECTIONS'] = 'N';
			}
			$arSection["~ELEMENT_CNT"] = CIBlockElement::GetList(array(), $elementFilter, array());
			$arSection["ELEMENT_CNT"] = $arSection["~ELEMENT_CNT"];
			if (!empty($arSection["ELEMENT_CNT"]))
			{
				$count = (int)$arSection["ELEMENT_CNT"];
				$val = ($count < 100 ? $count : $count % 100);
				$dec = $val % 10;

				if ($val == 0)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_ZERO';
				}
				elseif ($val == 1)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_ONE';
				}
				elseif ($val >= 10 && $val <= 20)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_TEN';
				}
				elseif ($dec == 1)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_MOD_ONE';
				}
				elseif (2 <= $dec && $dec <= 4)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_MOD_TWO';
				}
				else
				{
					$messageId = 'CP_BCSL_MESS_COUNT_OTHER';
				}
				$messageId .= $countTitleSuffix;

				$arSection['ELEMENT_CNT_TITLE'] = Loc::getMessage($messageId, ['#VALUE#' => $count]);
			}
			elseif ($arParams['HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS'] === 'Y')
			{
				continue;
			}
		}

		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arSection["IBLOCK_ID"], $arSection["ID"]);
		$arSection["IPROPERTY_VALUES"] = $ipropValues->getValues();

		if ($boolPicture)
		{
			\Bitrix\Iblock\Component\Tools::getFieldImageData(
				$arSection,
				array('PICTURE'),
				\Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
				'IPROPERTY_VALUES'
			);
		}

		if ($arParams["COUNT_ELEMENTS"])
		{
			$elementFilter = $elementCountFilter;
			$elementFilter["SECTION_ID"] = $arSection["ID"];
			if ($arSection['RIGHT_MARGIN'] == ($arSection['LEFT_MARGIN'] + 1))
			{
				$elementFilter['INCLUDE_SUBSECTIONS'] = 'N';
			}
			$arSection["~ELEMENT_CNT"] = CIBlockElement::GetList(array(), $elementFilter, array());
			$arSection["ELEMENT_CNT"] = $arSection["~ELEMENT_CNT"];
			if (!empty($arSection["ELEMENT_CNT"]))
			{
				$count = (int)$arSection["ELEMENT_CNT"];
				$val = ($count < 100 ? $count : $count % 100);
				$dec = $val % 10;

				if ($val == 0)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_ZERO';
				}
				elseif ($val == 1)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_ONE';
				}
				elseif ($val >= 10 && $val <= 20)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_TEN';
				}
				elseif ($dec == 1)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_MOD_ONE';
				}
				elseif (2 <= $dec && $dec <= 4)
				{
					$messageId = 'CP_BCSL_MESS_COUNT_MOD_TWO';
				}
				else
				{
					$messageId = 'CP_BCSL_MESS_COUNT_OTHER';
				}
				$messageId .= $countTitleSuffix;

				$arSection['ELEMENT_CNT_TITLE'] = Loc::getMessage($messageId, ['#VALUE#' => $count]);
			}
		}

		$list[] = $arSection;
	}
	unset($arSection);

	$arResult['SECTIONS'] = $list;
	$arResult["SECTIONS_COUNT"] = count($arResult["SECTIONS"]);

	$this->setResultCacheKeys(array(
		"SECTIONS_COUNT",
		"SECTION",
	));

	$this->includeComponentTemplate();
}

if ($arResult["SECTIONS_COUNT"] > 0 || isset($arResult["SECTION"]))
{
	if(
		$USER->IsAuthorized()
		&& $APPLICATION->GetShowIncludeAreas()
		&& Loader::includeModule("iblock")
	)
	{
		$UrlDeleteSectionButton = "";
		if (isset($arResult["SECTION"]) && $arResult["SECTION"]['IBLOCK_SECTION_ID'] > 0)
		{
			$rsSection = CIBlockSection::GetList(
				array(),
				array("=ID" => $arResult["SECTION"]['IBLOCK_SECTION_ID']),
				false,
				array("SECTION_PAGE_URL")
			);
			$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
			$arSection = $rsSection->GetNext();
			$UrlDeleteSectionButton = $arSection["SECTION_PAGE_URL"];
		}

		if (empty($UrlDeleteSectionButton))
		{
			$url_template = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "LIST_PAGE_URL");
			$arIBlock = CIBlock::GetArrayByID($arParams["IBLOCK_ID"]);
			$arIBlock["IBLOCK_CODE"] = $arIBlock["CODE"];
			$UrlDeleteSectionButton = CIBlock::ReplaceDetailUrl($url_template, $arIBlock, true, false);
		}

		$arReturnUrl = array(
			"add_section" => (
				'' != $arParams["SECTION_URL"]?
				$arParams["SECTION_URL"]:
				CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_PAGE_URL")
			),
			"add_element" => (
				'' != $arParams["SECTION_URL"]?
				$arParams["SECTION_URL"]:
				CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_PAGE_URL")
			),
			"delete_section" => $UrlDeleteSectionButton,
		);
		$arButtons = CIBlock::GetPanelButtons(
			$arParams["IBLOCK_ID"],
			0,
			$arResult["SECTION"]["ID"],
			array("RETURN_URL" =>  $arReturnUrl, "CATALOG"=>true)
		);

		$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
	}

	if ($arParams["ADD_SECTIONS_CHAIN"] && isset($arResult["SECTION"]["PATH"]) && is_array($arResult["SECTION"]["PATH"]))
	{
		foreach($arResult["SECTION"]["PATH"] as $arPath)
		{
			if (
				isset($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"])
				&& $arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] !== ""
			)
			{
				$APPLICATION->AddChainItem(
					$arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"],
					$arPath["~SECTION_PAGE_URL"]
				);
			}
			else
			{
				$APPLICATION->AddChainItem(
					$arPath["NAME"],
					$arPath["~SECTION_PAGE_URL"]
				);
			}
		}
	}
}
