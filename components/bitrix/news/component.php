<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
$arParams['NEWS_COUNT'] = (int)($arParams['NEWS_COUNT'] ?? 0);

$arParams['USE_SEARCH'] = (string)($arParams['USE_SEARCH'] ?? 'N');
$arParams['USE_SEARCH'] = $arParams['USE_SEARCH'] === 'Y' ? 'Y' : 'N';
$arParams['USE_RSS'] = (string)($arParams['USE_RSS'] ?? 'N');
$arParams['USE_RSS'] = $arParams['USE_RSS'] === 'Y' ? 'Y' : 'N';
$arParams['USE_RATING'] = (string)($arParams['USE_RATING'] ?? 'N');
$arParams['USE_RATING'] = $arParams['USE_RATING'] === 'Y' ? 'Y' : 'N';
$arParams['USE_CATEGORIES'] = (string)($arParams['USE_CATEGORIES'] ?? 'N');
$arParams['USE_CATEGORIES'] = $arParams['USE_CATEGORIES'] === 'Y'; // boolean
$arParams['USE_FILTER'] = (string)($arParams['USE_FILTER'] ?? 'N');
$arParams['USE_FILTER'] = $arParams['USE_FILTER'] === 'Y' ? 'Y' : 'N';

$arParams['SORT_BY1'] = trim((string)($arParams['SORT_BY1'] ?? ''));
$arParams['SORT_ORDER1'] = trim((string)($arParams['SORT_ORDER1'] ?? ''));
$arParams['SORT_BY2'] = trim((string)($arParams['SORT_BY2'] ?? ''));
$arParams['SORT_ORDER2'] = trim((string)($arParams['SORT_ORDER2'] ?? ''));

$arParams['CHECK_DATES'] = (string)($arParams['CHECK_DATES'] ?? 'Y');
$arParams['CHECK_DATES'] = $arParams['CHECK_DATES'] === 'N' ? 'N' : 'Y';

$arParams['PREVIEW_TRUNCATE_LEN'] = (int)($arParams['PREVIEW_TRUNCATE_LEN'] ?? 0);

$arParams['LIST_ACTIVE_DATE_FORMAT'] = trim((string)($arParams['LIST_ACTIVE_DATE_FORMAT'] ?? ''));
$arParams['LIST_FIELD_CODE'] ??= [];
$arParams['LIST_FIELD_CODE'] = is_array($arParams['LIST_FIELD_CODE']) ? $arParams['LIST_FIELD_CODE'] : [];
$arParams['LIST_PROPERTY_CODE'] ??= [];
$arParams['LIST_PROPERTY_CODE'] = is_array($arParams['LIST_PROPERTY_CODE']) ? $arParams['LIST_PROPERTY_CODE'] : [];

$arParams['HIDE_LINK_WHEN_NO_DETAIL'] = (string)($arParams['HIDE_LINK_WHEN_NO_DETAIL'] ?? 'N');
$arParams['HIDE_LINK_WHEN_NO_DETAIL'] = $arParams['HIDE_LINK_WHEN_NO_DETAIL'] === 'Y' ? 'Y' : 'N';
$arParams['DISPLAY_NAME'] = (string)($arParams['DISPLAY_NAME'] ?? 'Y');
$arParams['DISPLAY_NAME'] = $arParams['DISPLAY_NAME'] === 'N' ? 'N' : 'Y';
$arParams['META_KEYWORDS'] = (string)($arParams['META_KEYWORDS'] ?? '-');
$arParams['META_DESCRIPTION'] = (string)($arParams['META_DESCRIPTION'] ?? '-');
$arParams['BROWSER_TITLE'] = (string)($arParams['BROWSER_TITLE'] ?? '-');

$arParams['DETAIL_SET_CANONICAL_URL'] = (string)($arParams['DETAIL_SET_CANONICAL_URL'] ?? 'N');
$arParams['DETAIL_SET_CANONICAL_URL'] = $arParams['DETAIL_SET_CANONICAL_URL'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_ACTIVE_DATE_FORMAT'] = trim((string)($arParams['DETAIL_ACTIVE_DATE_FORMAT'] ?? ''));
$arParams['DETAIL_FIELD_CODE'] ??= [];
$arParams['DETAIL_FIELD_CODE'] = is_array($arParams['DETAIL_FIELD_CODE']) ? $arParams['DETAIL_FIELD_CODE'] : [];
$arParams['DETAIL_PROPERTY_CODE'] ??= [];
$arParams['DETAIL_PROPERTY_CODE'] = is_array($arParams['DETAIL_PROPERTY_CODE']) ? $arParams['DETAIL_PROPERTY_CODE'] : [];
$arParams['DETAIL_DISPLAY_TOP_PAGER'] = (string)($arParams['DETAIL_DISPLAY_TOP_PAGER'] ?? 'N');
$arParams['DETAIL_DISPLAY_TOP_PAGER'] = $arParams['DETAIL_DISPLAY_TOP_PAGER'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_DISPLAY_BOTTOM_PAGER'] = (string)($arParams['DETAIL_DISPLAY_BOTTOM_PAGER'] ?? 'N');
$arParams['DETAIL_DISPLAY_BOTTOM_PAGER'] = $arParams['DETAIL_DISPLAY_BOTTOM_PAGER'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_PAGER_TITLE'] = trim((string)($arParams['DETAIL_PAGER_TITLE'] ?? ''));
$arParams['DETAIL_PAGER_TEMPLATE'] = trim((string)($arParams['DETAIL_PAGER_TEMPLATE'] ?? ''));
$arParams['DETAIL_PAGER_SHOW_ALL'] = (string)($arParams['DETAIL_PAGER_SHOW_ALL'] ?? 'Y');
$arParams['DETAIL_PAGER_SHOW_ALL'] = $arParams['DETAIL_PAGER_SHOW_ALL'] === 'N' ? 'N' : 'Y';

$arParams['SET_LAST_MODIFIED'] = (string)($arParams['SET_LAST_MODIFIED'] ?? 'N');
$arParams['SET_LAST_MODIFIED'] = $arParams['SET_LAST_MODIFIED'] === 'Y' ? 'Y' : 'N';

$arParams['SET_TITLE'] = (string)($arParams['SET_TITLE'] ?? 'Y');
$arParams['SET_TITLE'] = $arParams['SET_TITLE'] === 'N' ? 'N' : 'Y';

$arParams['ADD_SECTIONS_CHAIN'] = (string)($arParams['ADD_SECTIONS_CHAIN'] ?? 'Y');
$arParams['ADD_SECTIONS_CHAIN'] = $arParams['ADD_SECTIONS_CHAIN'] === 'N' ? 'N' : 'Y';

$arParams['ADD_ELEMENT_CHAIN'] = (string)($arParams['ADD_ELEMENT_CHAIN'] ?? 'N');
$arParams['ADD_ELEMENT_CHAIN'] = $arParams['ADD_ELEMENT_CHAIN'] === 'Y' ? 'Y' : 'N';

$arParams['USE_PERMISSIONS'] = (string)($arParams['USE_PERMISSIONS'] ?? 'N');
$arParams['USE_PERMISSIONS'] = $arParams['USE_PERMISSIONS'] === 'Y' ? 'Y' : 'N';
$arParams['GROUP_PERMISSIONS'] ??= [1];
$arParams['GROUP_PERMISSIONS'] = is_array($arParams['GROUP_PERMISSIONS']) ? $arParams['GROUP_PERMISSIONS'] : [1];

$arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 0);
$arParams['CACHE_FILTER'] = (string)($arParams['CACHE_FILTER'] ?? 'N');
$arParams['CACHE_FILTER'] = $arParams['CACHE_FILTER'] === 'Y' ? 'Y' : 'N';
$arParams['CACHE_GROUPS'] = (string)($arParams['CACHE_GROUPS'] ?? 'Y');
$arParams['CACHE_GROUPS'] = $arParams['CACHE_GROUPS'] === 'N' ? 'N' : 'Y';

$arParams['FILTER_NAME'] = trim((string)($arParams['FILTER_NAME'] ?? ''));
if ($arParams['USE_FILTER'] === 'Y')
{
	if ($arParams['FILTER_NAME'] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams['FILTER_NAME']))
	{
		$arParams['FILTER_NAME'] = 'arrFilter';
	}
}
else
{
	$arParams['FILTER_NAME'] = '';
}
$arParams['FILTER_FIELD_CODE'] ??= [];
$arParams['FILTER_FIELD_CODE'] = is_array($arParams['FILTER_FIELD_CODE']) ? $arParams['FILTER_FIELD_CODE'] : [];
$arParams['FILTER_PROPERTY_CODE'] ??= [];
$arParams['FILTER_PROPERTY_CODE'] = is_array($arParams['FILTER_PROPERTY_CODE']) ? $arParams['FILTER_PROPERTY_CODE'] : [];

$arParams['NUM_NEWS'] = (int)($arParams['NUM_NEWS'] ?? 0);
$arParams['NUM_DAYS'] = (int)($arParams['NUM_DAYS'] ?? 0);
$arParams['YANDEX'] = (string)($arParams['YANDEX'] ?? 'N');
$arParams['YANDEX'] = $arParams['YANDEX'] === 'Y' ? 'Y' : 'N';

$arParams['MAX_VOTE'] = (int)($arParams['MAX_VOTE'] ?? 0);
$arParams['VOTE_NAMES'] ??= [];
$arParams['VOTE_NAMES'] = is_array($arParams['VOTE_NAMES']) ? $arParams['VOTE_NAMES'] : [];
$arParams['DISPLAY_AS_RATING'] = trim((string)($arParams['DISPLAY_AS_RATING'] ?? ''));

$arParams['CATEGORY_IBLOCK'] ??= [];
$arParams['CATEGORY_IBLOCK'] = is_array($arParams['CATEGORY_IBLOCK']) ? $arParams['CATEGORY_IBLOCK'] : [];
if ($arParams["USE_CATEGORIES"])
{
	\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($arParams['CATEGORY_IBLOCK'], false);
}
$arParams['CATEGORY_CODE'] = trim((string)($arParams['CATEGORY_CODE'] ?? ''));
if ($arParams['CATEGORY_CODE'] === '')
{
	$arParams['CATEGORY_CODE'] = 'CATEGORY';
}
$arParams['CATEGORY_ITEMS_COUNT'] = (int)($arParams['CATEGORY_ITEMS_COUNT'] ?? 0);

foreach ($arParams['CATEGORY_IBLOCK'] as $iblock_id)
{
	$arParams['CATEGORY_THEME_' . $iblock_id] ??= '';
	if ($arParams['CATEGORY_THEME_' . $iblock_id] !== 'photo')
	{
		$arParams['CATEGORY_THEME_' . $iblock_id] = 'list';
	}
}

$arParams['PAGER_BASE_LINK_ENABLE'] = (string)($arParams['PAGER_BASE_LINK_ENABLE'] ?? 'N');
$arParams['PAGER_BASE_LINK_ENABLE'] = $arParams['PAGER_BASE_LINK_ENABLE'] === 'Y' ? 'Y' : 'N';
$arParams['PAGER_BASE_LINK'] = trim((string)($arParams['PAGER_BASE_LINK'] ?? ''));
$arParams['PAGER_PARAMS_NAME'] = trim((string)($arParams['PAGER_PARAMS_NAME'] ?? ''));

$arDefaultUrlTemplates404 = array(
	"news" => "",
	"search" => "search/",
	"rss" => "rss/",
	"rss_section" => "#SECTION_ID#/rss/",
	"detail" => "#ELEMENT_ID#/",
	"section" => "",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
);

if($arParams["USE_SEARCH"] != "Y")
{
	unset($arDefaultUrlTemplates404["search"]);
	unset($arParams["SEF_URL_TEMPLATES"]["search"]);
}
else
{
	$arComponentVariables[] = "q";
	$arComponentVariables[] = "tags";
}

if($arParams["USE_RSS"] != "Y")
{
	unset($arDefaultUrlTemplates404["rss"]);
	unset($arDefaultUrlTemplates404["rss_section"]);
	unset($arParams["SEF_URL_TEMPLATES"]["rss"]);
	unset($arParams["SEF_URL_TEMPLATES"]["rss_section"]);
}
else
{
	$arComponentVariables[] = "rss";
}

/* Compatibility with deleted DETAIL_STRICT_SECTION_CHECK */
if (isset($arParams['STRICT_SECTION_CHECK']))
{
	$arParams['DETAIL_STRICT_SECTION_CHECK'] = $arParams['STRICT_SECTION_CHECK'];
}
else
{
	$arParams['STRICT_SECTION_CHECK'] = (string)($arParams['DETAIL_STRICT_SECTION_CHECK'] ?? '');
}

$arParams['SET_STATUS_404'] = (string)($arParams['SET_STATUS_404'] ?? 'N');
$arParams['SET_STATUS_404'] = $arParams['SET_STATUS_404'] === 'Y' ? 'Y' : 'N';
$arParams['SHOW_404'] = (string)($arParams['SHOW_404'] ?? 'N');
$arParams['SHOW_404'] = $arParams['SHOW_404'] === 'Y' ? 'Y' : 'N';
$arParams['FILE_404'] = trim((string)($arParams['FILE_404'] ?? ''));
$arParams['MESSAGE_404'] = trim((string)($arParams['MESSAGE_404'] ?? ''));

$arParams['VARIABLE_ALIASES'] ??= [];

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$engine = new CComponentEngine($this);
	if (CModule::IncludeModule('iblock'))
	{
		$engine->addGreedyPart("#SECTION_CODE_PATH#");
		$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
	}
	$componentPage = $engine->guessComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	$b404 = false;
	if(!$componentPage)
	{
		$componentPage = "news";
		$b404 = true;
	}

	if($componentPage == "section")
	{
		if (isset($arVariables["SECTION_ID"]))
			$b404 |= (intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]);
		else
			$b404 |= !isset($arVariables["SECTION_CODE"]);
	}

	if($b404 && CModule::IncludeModule('iblock'))
	{
		$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
		if ($folder404 != "/")
			$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
		if (mb_substr($folder404, -1) == "/")
			$folder404 .= "index.php";

		if ($folder404 != $APPLICATION->GetCurPage(true))
		{
			\Bitrix\Iblock\Component\Tools::process404(
				""
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SHOW_404"] === "Y")
				,$arParams["FILE_404"]
			);
		}
	}

	CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases,
	);
}
else
{
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
		$componentPage = "detail";
	elseif(isset($arVariables["ELEMENT_CODE"]) && $arVariables["ELEMENT_CODE"] <> '')
		$componentPage = "detail";
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
	{
		if(isset($arVariables["rss"]) && $arVariables["rss"]=="y")
			$componentPage = "rss_section";
		else
			$componentPage = "section";
	}
	elseif(isset($arVariables["SECTION_CODE"]) && $arVariables["SECTION_CODE"] <> '')
	{
		if(isset($arVariables["rss"]) && $arVariables["rss"]=="y")
			$componentPage = "rss_section";
		else
			$componentPage = "section";
	}
	elseif(isset($arVariables["q"]) && trim($arVariables["q"]) <> '')
		$componentPage = "search";
	elseif(isset($arVariables["tags"]) && trim($arVariables["tags"]) <> '')
		$componentPage = "search";
	elseif(isset($arVariables["rss"]) && $arVariables["rss"]=="y")
		$componentPage = "rss";
	else
		$componentPage = "news";

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => array(
			"news" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
			"section" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"),
			"detail" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#"),
			"search" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
			"rss" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?rss=y"),
			"rss_section" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#&rss=y"),
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}

if ($componentPage=="search")
{
	include_once("newstools.php");
	global $BX_NEWS_DETAIL_URL, $BX_NEWS_SECTION_URL;
	$BX_NEWS_DETAIL_URL = $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"];
	$BX_NEWS_SECTION_URL = $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"];
	AddEventHandler("search", "OnSearchGetURL", array("CNewsTools","OnSearchGetURL"), 20);
}

$arResult["URL_TEMPLATES"]['search'] ??= '';
$arResult["VARIABLES"]["ELEMENT_ID"] ??= '';
$arResult["VARIABLES"]["ELEMENT_CODE"] ??= '';
$arResult["VARIABLES"]["SECTION_ID"] ??= '';
$arResult["VARIABLES"]["SECTION_CODE"] ??= '';

$this->includeComponentTemplate($componentPage);
