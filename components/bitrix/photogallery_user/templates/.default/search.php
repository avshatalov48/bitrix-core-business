<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-search">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="photo-table">
	<tr valign="top">
		<td class="photo-td-left">
<?$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"",
	Array(
		"TAGS_PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"], 
		"TAGS_PERIOD" => $arParams["TAGS_PERIOD"], 
		"TAGS_URL_SEARCH" => $arResult["URL_TEMPLATES"]["search"], 
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"], 
		
		"FONT_MAX" => $arParams["TAGS_FONT_MAX"],
		"FONT_MIN" => $arParams["TAGS_FONT_MIN"],
		"COLOR_NEW" => $arParams["TAGS_COLOR_NEW"],
		"COLOR_OLD" => $arParams["TAGS_COLOR_OLD"],
		"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"], 
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"WIDTH" => "100%",  
		
		"PAGE_RESULT_COUNT" => (empty($arParams["PAGE_RESULT_COUNT"]) ? 50 : $arParams["PAGE_RESULT_COUNT"]),
		"PAGER_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],

		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => 0,
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"], 
		"arrWHERE" => Array(), 
		"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]), 
		"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"]),
		
		"THUMBNAIL_SIZE"	=>	$arParams["THUMBNAIL_SIZE"], 
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT" => ""
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);
?>
		</td>
		<td class="photo-td-right">
			<?$APPLICATION->IncludeComponent(
				"bitrix:search.tags.cloud", 
				"photogallery", 
				Array(
					"SEARCH" => $_REQUEST["q"],
					"TAGS" => $_REQUEST["tags"],
					"CHECK_DATES" => $arParams["CHECK_DATES"],
					"SORT" => $arParams["TAGS_SORT"],
					"PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"],
					"PERIOD" => $arParams["TAGS_PERIOD"],
					"URL_SEARCH" => $arResult["SEARCH_LINK"],
					"TAGS_INHERIT" => $arResult["TAGS_INHERIT"],
					"FONT_MAX" => (empty($arParams["FONT_MAX"]) ? "40" : $arParams["FONT_MAX"]),
					"FONT_MIN" => (empty($arParams["FONT_MIN"]) ? "12" : $arParams["FONT_MIN"]),
					"COLOR_NEW" => (empty($arParams["COLOR_NEW"]) ? "707C8F" : $arParams["COLOR_NEW"]),
					"COLOR_OLD" => (empty($arParams["COLOR_OLD"]) ? "C0C0C0" : $arParams["COLOR_OLD"]),
					"PERIOD_NEW_TAGS" => $arParams["PERIOD_NEW_TAGS"],
					"SHOW_CHAIN" => "N",
					"COLOR_TYPE" => $arParams["COLOR_TYPE"],
					"WIDTH" => $arParams["WIDTH"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"RESTART" => $arParams["RESTART"],
					"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]), 
					"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"])
				), 
				$component, 
				array("HIDE_ICONS" => "Y"));?>
		</td>
	</tr>
</table>
</div>
<?
/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));
/************** BreadCrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] != "N")
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("P_TITLE"));
/********************************************************************
				/Standart
********************************************************************/
?>