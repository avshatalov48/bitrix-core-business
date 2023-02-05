<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('advertising'))
	return;

IncludeModuleLangFile(__FILE__);

//Matrix
$arWeekday = Array(
	"SUNDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"MONDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"TUESDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"WEDNESDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"THURSDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"FRIDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"SATURDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23)
);

$contractId  = false;

$rsADV = CAdvContract::GetList("s_sort", "desc", array("NAME" => 'Eshop', 'DESCRIPTION' => GetMessage("CONTRACT_DESC")." [".WIZARD_SITE_ID."]"));
if ($arADV = $rsADV->Fetch())
{
	$contractId  = $arADV["ID"];
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		CAdvContract::Delete($arADV["ID"]);
		$contractId  = false;
	}
}
if ($contractId == false)
{
	$arFields = array(
		'ACTIVE' => 'Y',
		'NAME' => 'Eshop',
		'SORT' => 1000,
		'DESCRIPTION' => GetMessage("CONTRACT_DESC")." [".WIZARD_SITE_ID."]",
		'EMAIL_COUNT' => 1,
		'arrTYPE' => array('ALL'),
		'arrWEEKDAY' => $arWeekday,
		'arrSITE' => Array(WIZARD_SITE_ID),
	);
	$contractId = CAdvContract::Set($arFields, 0, 'N');

	//Types
	$arTypes = Array(
		Array(
			"SID" => "MAIN",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"NAME" => GetMessage("DEMO_ADV_MAIN_TYPE"),
			"DESCRIPTION" => ""
		),
		Array(
			"SID" => "PARALLAX",
			"ACTIVE" => "Y",
			"SORT" => 2,
			"NAME" => GetMessage("DEMO_ADV_PARALLAX_TYPE"),
			"DESCRIPTION" => ""
		)
	);

	foreach ($arTypes as $arFields)
	{
		$dbResult = CAdvType::GetByID($arTypes["SID"], $CHECK_RIGHTS="N");
		if ($dbResult && $dbResult->Fetch())
			continue;

		CAdvType::Set($arFields, "", $CHECK_RIGHTS="N");
	}

	$pathToBanner = str_replace("\\", "/", __DIR__);
	$lang = (in_array(LANGUAGE_ID, array("ru", "en", "de"))) ? LANGUAGE_ID : \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
	$pathToBanner = $pathToBanner."/lang/".$lang;

	if (CModule::IncludeModule("iblock"))
	{
		$IBLOCK_CATALOG_ID = $_SESSION["WIZARD_CATALOG_IBLOCK_ID"];

		$arSectionLinks = array();

		$urlTemplate = CIBlock::GetArrayById($IBLOCK_CATALOG_ID, "SECTION_PAGE_URL");
		$urlTemplate = str_replace("#SITE_DIR#", WIZARD_SITE_DIR, $urlTemplate);

		$dbSect = CIBlockSection::GetList(Array(), Array("IBLOCK_TYPE" => "catalog", "IBLOCK_ID"=>$IBLOCK_CATALOG_ID, "CODE" => array("underwear", "shoes"), "IBLOCK_SITE_ID" => WIZARD_SITE_ID), false, array("SECTION_PAGE_URL"));
		$dbSect->SetUrlTemplates("", $urlTemplate);
		while ($arSect = $dbSect->GetNext())
		{
			$arSectionLinks[$arSect["CODE"]] = $arSect["SECTION_PAGE_URL"];
		}
	}

	$arBanners = Array(
		Array(
			"CONTRACT_ID" => $contractId,
			"TYPE_SID" => "MAIN",
			"STATUS_SID" => "PUBLISHED",
			"NAME" => GetMessage("DEMO_ADV_NAME"),
			"ACTIVE" => "Y",
			"arrSITE" => Array(WIZARD_SITE_ID),
			"WEIGHT"=> 200,
			"FIX_SHOW" => "N",
			"AD_TYPE" => "template",
			"arrWEEKDAY" => $arWeekday,
			"COMMENTS" => "banner for " . WIZARD_SITE_ID,
			"TEMPLATE" => serialize(array(
				"NAME" => "bootstrap_v4",
				"MODE" => "N",
				"PROPS" => array(
					0 => array(
						"BANNER_NAME" => GetMessage("DEMO_ADV_1_NAME"),
						"BACKGROUND" => "stream",
						"IMG_FIXED" => "N",
						"LINK_URL" => $arSectionLinks["underwear"],
						"LINK_TITLE" => "",
						"LINK_TARGET" => "_self",
						"PRESET" => "3",
						"HEADING_SHOW" => "Y",
						"HEADING" => GetMessage("DEMO_ADV_1_NAME"),
						"HEADING_FONT_SIZE" => 35,
						"HEADING_FONT_COLOR" => "000000",
						"HEADING_BG_COLOR" => "FFFFFF",
						"HEADING_BG_OPACITY" => 20,
						"ANNOUNCEMENT_SHOW" => "Y",
						"ANNOUNCEMENT" => GetMessage("DEMO_ADV_1_ANNOUNCEMENT"),
						"ANNOUNCEMENT_FONT_SIZE" => "14",
						"ANNOUNCEMENT_FONT_COLOR" => "000000",
						"ANNOUNCEMENT_BG_COLOR" => "FFFFFF",
						"ANNOUNCEMENT_BG_OPACITY" => "100",
						"ANNOUNCEMENT_MOBILE_HIDE" => "N",
						"BUTTON" => "Y",
						"BUTTON_TEXT" => GetMessage("DEMO_ADV_1_BUTTON"),
						"BUTTON_FONT_COLOR" => "FFFFFF",
						"BUTTON_BG_COLOR" => "E6A323",
						"BUTTON_LINK_URL" => $arSectionLinks["underwear"],
						"BUTTON_LINK_TARGET" => "_self",
						"ANIMATION" => "N",
						"OVERLAY" => "N",
						"STREAM" => "https://youtu.be/h-Sw7RZc4mQ",
						"STREAM_MUTE" => "Y"
					),
					1 => array(
						"BANNER_NAME" => GetMessage("DEMO_ADV_2_NAME"),
						"BACKGROUND" => "image",
						"IMG_FIXED" => "N",
						"LINK_URL" => $arSectionLinks["shoes"],
						"LINK_TITLE" => "",
						"LINK_TARGET" => "_self",
						"PRESET" => "3",
						"HEADING_SHOW" => "Y",
						"HEADING" => GetMessage("DEMO_ADV_2_HEADING"),
						"HEADING_FONT_SIZE" => 30,
						"HEADING_FONT_COLOR" => "000000",
						"HEADING_BG_COLOR" => "FFFFFF",
						"HEADING_BG_OPACITY" => 20,
						"ANNOUNCEMENT_SHOW" => "Y",
						"ANNOUNCEMENT" => GetMessage("DEMO_ADV_2_ANNOUNCEMENT"),
						"ANNOUNCEMENT_FONT_SIZE" => "14",
						"ANNOUNCEMENT_FONT_COLOR" => "000000",
						"ANNOUNCEMENT_BG_COLOR" => "FFFFFF",
						"ANNOUNCEMENT_BG_OPACITY" => "100",
						"ANNOUNCEMENT_MOBILE_HIDE" => "N",
						"BUTTON" => "Y",
						"BUTTON_TEXT" => GetMessage("DEMO_ADV_2_BUTTON"),
						"BUTTON_FONT_COLOR" => "FFFFFF",
						"BUTTON_BG_COLOR" => "E6A323",
						"BUTTON_LINK_URL" => $arSectionLinks["shoes"],
						"BUTTON_LINK_TARGET" => "_self",
						"ANIMATION" => "N",
						"OVERLAY" => "N",
					),
					2 => array(
						"BANNER_NAME" => GetMessage("DEMO_ADV_3_NAME"),
						"BACKGROUND" => "image",
						"IMG_FIXED" => "N",
						//	"LINK_URL" => $arSectionLinks["shoes"],
						"LINK_TITLE" => "",
						"LINK_TARGET" => "_self",
						"PRESET" => "3",
						"HEADING_SHOW" => "Y",
						"HEADING" => GetMessage("DEMO_ADV_3_HEADING"),
						"HEADING_FONT_SIZE" => 30,
						"HEADING_FONT_COLOR" => "000000",
						"HEADING_BG_COLOR" => "FFFFFF",
						"HEADING_BG_OPACITY" => 20,
						"ANNOUNCEMENT_SHOW" => "Y",
						"ANNOUNCEMENT" => GetMessage("DEMO_ADV_3_ANNOUNCEMENT"),
						"ANNOUNCEMENT_FONT_SIZE" => "14",
						"ANNOUNCEMENT_FONT_COLOR" => "000000",
						"ANNOUNCEMENT_BG_COLOR" => "FFFFFF",
						"ANNOUNCEMENT_BG_OPACITY" => "100",
						"ANNOUNCEMENT_MOBILE_HIDE" => "N",
						"BUTTON" => "N",
						"ANIMATION" => "N",
						"OVERLAY" => "N",
					)
				)
			)),
			"TEMPLATE_FILES" => array(
				0 => array(),
				1 => array(
					"IMG" => Array(
						"name" => "banner_shoes.jpeg",
						"type" => "image/jpg",
						"tmp_name" => $pathToBanner."/banner_shoes.jpg",
						"error" => "0",
						"size" => @filesize($pathToBanner."/banner_shoes.jpg"),
						"MODULE_ID" => "advertising"
					)
				),
				2 => array(
					"IMG" => Array(
						"name" => "banner.jpeg",
						"type" => "image/jpg",
						"tmp_name" => $pathToBanner."/banner.jpg",
						"error" => "0",
						"size" => @filesize($pathToBanner."/banner.jpg"),
						"MODULE_ID" => "advertising"
					)
				))
		),
		Array(
			"CONTRACT_ID" => $contractId,
			"TYPE_SID" => "PARALLAX",
			"STATUS_SID" => "PUBLISHED",
			"NAME" => GetMessage("DEMO_ADV_SLIDER_NAME"),
			"ACTIVE" => "Y",
			"arrSITE" => Array(WIZARD_SITE_ID),
			"WEIGHT"=> 200,
			"FIX_SHOW" => "N",
			"AD_TYPE" => "template",
			"arrWEEKDAY" => $arWeekday,
			"COMMENTS" => "banner2 for " . WIZARD_SITE_ID,
			"TEMPLATE" => serialize(array(
				"NAME" => "parallax",
				"MODE" => "N",
				"PROPS" => array(
					0 => array(
						"BANNER_NAME" => GetMessage("DEMO_ADV_4_NAME"),
						"BACKGROUND" => "image",
						"IMG_FIXED" => "N",
						"LINK_URL" => WIZARD_SITE_DIR."about/delivery/",
						"LINK_TITLE" => "",
						"LINK_TARGET" => "_self",
						"PRESET" => "3",
						"HEADING_SHOW" => "Y",
						"HEADING" => GetMessage("DEMO_ADV_4_HEADING"),
						"HEADING_FONT_SIZE" => 30,
						"HEADING_FONT_COLOR" => "000000",
						"HEADING_BG_COLOR" => "FFFFFF",
						"HEADING_BG_OPACITY" => 20,
						"ANNOUNCEMENT_SHOW" => "Y",
						"ANNOUNCEMENT" => GetMessage("DEMO_ADV_4_ANNOUNCEMENT"),
						"ANNOUNCEMENT_FONT_SIZE" => "14",
						"ANNOUNCEMENT_FONT_COLOR" => "000000",
						"ANNOUNCEMENT_BG_COLOR" => "FFFFFF",
						"ANNOUNCEMENT_BG_OPACITY" => "100",
						"ANNOUNCEMENT_MOBILE_HIDE" => "N",
						"BUTTON" => "Y",
						"BUTTON_TEXT" => GetMessage("DEMO_ADV_4_BUTTON"),
						"BUTTON_FONT_COLOR" => "FFFFFF",
						"BUTTON_BG_COLOR" => "E6A323",
						"BUTTON_LINK_URL" => WIZARD_SITE_DIR."about/delivery/",
						"BUTTON_LINK_TARGET" => "_self",
					//	"ANIMATION" => "N",
						"OVERLAY" => "N",
					)
				)
			)),
			"TEMPLATE_FILES" => array(
				array(
					"IMG" => Array(
						"name" => "banner_parallax.jpeg",
						"type" => "image/jpg",
						"tmp_name" => $pathToBanner."/banner_parallax.jpg",
						"error" => "0",
						"size" => @filesize($pathToBanner."/banner_parallax.jpg"),
						"MODULE_ID" => "advertising"
					)
				)
			)
		)
	);

	foreach ($arBanners as $arFields)
	{
		$dbResult = CAdvBanner::GetList('', '', Array("COMMENTS" => $arFields["COMMENTS"], "COMMENTS_EXACT_MATCH" => "Y"), null, "N");
		if ($dbResult && $dbResult->Fetch())
			continue;

		CAdvBanner::Set($arFields, "", $CHECK_RIGHTS="N");
	}
}
?>