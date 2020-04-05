<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arParams["PERMISSION"] <= "D"):
	ShowError(GetMessage("P_ACCESS_DENIED"));
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
//***************** STANDART ***************************************/
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
?>
<div class="photo-page-search">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="photo-table">
	<tr valign="top">
		<td class="photo-td-left">
<?
$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"",
	Array(
		"SEF_PAGE_NAME" => $arParams['SEF_MODE'] == "N" && isset($arParams['VARIABLE_ALIASES']['PAGE_NAME']) ? $arParams['VARIABLE_ALIASES']['PAGE_NAME'] : false, // Used for correct search page redirections in non SEF mode
		"TAGS_PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"], 
		"TAGS_PERIOD" => $arParams["TAGS_PERIOD"], 
		"TAGS_URL_SEARCH" => $arResult["URL_TEMPLATES"]["search"], 
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"], 
		
		"FONT_MAX" => $arParams["FONT_MAX"],
		"FONT_MIN" => $arParams["FONT_MIN"],
		"COLOR_NEW" => $arParams["COLOR_NEW"],
		"COLOR_OLD" => $arParams["COLOR_OLD"],
		"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"], 
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"WIDTH" => "100%",  
		
		"PAGE_RESULT_COUNT" => (empty($arParams["PAGE_RESULT_COUNT"]) ? 50 : $arParams["PAGE_RESULT_COUNT"]),
		"PAGER_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],

		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		
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
					
					"PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"],
					"PERIOD" => $arParams["TAGS_PERIOD"],
					"TAGS_INHERIT" => $arParams["TAGS_INHERIT"],
					
					"URL_SEARCH" =>  CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()),
					
					"FONT_MAX" => $arParams["FONT_MAX"],
					"FONT_MIN" => $arParams["FONT_MIN"],
					"COLOR_NEW" => $arParams["COLOR_NEW"],
					"COLOR_OLD" => $arParams["COLOR_OLD"],
					"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"],
					"WIDTH" => "100%",
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]), 
					"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"])
				), 
				$component, 
				array("HIDE_ICONS" => "Y")
			);?>
		</td>
	</tr>
</table>
</div>
<?
/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y")
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("P_TITLE"));
/************** BreadCrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] == "Y")
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("P_TITLE"));
/********************************************************************
				/Standart
********************************************************************/
?>