<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$URL_NAME_DEFAULT = array(
	"search" => "PAGE_NAME=search",
	"detail_list" => "PAGE_NAME=detail_list&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
}

if ($arParams["SHOW_TAGS"] == "Y" && IsModuleInstalled("search"))
{
?>
	<div class="tags-cloud">
<?
	$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default",
		Array(
			"SEARCH" => $arResult["REQUEST"]["~QUERY"],
			"TAGS" => $arResult["REQUEST"]["~TAGS"],

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
		$component
	);
?>
	</div>
<?
}
if (is_array($arParams["SHOW_LINK_ON_MAIN_PAGE"]))
{
	$detail_list = array(
		"~url" => CComponentEngine::MakePathFromTemplate($arParams["DETAIL_LIST_URL"], array("SECTION_ID" => "all", "ELEMENT_ID" => "all")));
	$detail_list["url"] = $detail_list["~url"];
	if (strpos($detail_list["url"], "?") === false)
		$detail_list["url"] .= "?";

	$arRes = array();

	foreach ($arParams["SHOW_LINK_ON_MAIN_PAGE"] as $key):

		if ($key == "id"):
			$arRes["id"] = array(
				"title" => GetMessage("P_PHOTO_SORT_ID"),
				"description" => GetMessage("P_PHOTO_SORT_ID_TITLE"),
				"url" => $detail_list["~url"]);
		elseif ($key == "shows"):
			$arRes["shows"] = array(
				"title" => GetMessage("P_PHOTO_SORT_SHOWS"),
				"description" => GetMessage("P_PHOTO_SORT_SHOWS_TITLE"),
				"url" => $detail_list["url"]."&amp;order=shows");
		elseif ($key == "rating" && ($arParams["USE_RATING"] == "Y")):
			$arRes["rating"] = array(
				"title" => GetMessage("P_PHOTO_SORT_RATING"),
				"description" => GetMessage("P_PHOTO_SORT_RATING_TITLE"),
				"url" => $detail_list["url"]."&amp;order=rating");
		elseif ($key == "comments" && ($arParams["USE_COMMENTS"] == "Y")):
			$arRes["comments"] = array(
				"title" => GetMessage("P_PHOTO_SORT_COMMENTS"),
				"description" => GetMessage("P_PHOTO_SORT_COMMENTS_TITLE"),
				"url" => $detail_list["url"]."&amp;order=comments");
		endif;
	endforeach;

?>
	<div class="photo-controls photo-view only-on-main">
<?
$counter = 1;
foreach ($arRes as $key => $val):
	if ($counter > 1):
	?><div class="empty"></div><?
	endif;
	?><noindex><a rel="nofollow" href="<?=$val["url"]?>" <?
		?>class="photo-view <?=$key?> <?=($counter == 1 ? "photo-item-first" : "")?> <?=($counter == count($arRes) ? "photo-item-last" : "")?>" <?
		?>title="<?=$val["description"]?>"><span><?=$val["title"]?></span></a></noindex><?
	$counter++;
endforeach;

	if ($arParams["PERMISSION"] >= "U"):?>
		<div class="empty"></div>
		<noindex><a rel="nofollow" href="<?= CComponentEngine::MakePathFromTemplate($arResult["URL_TEMPLATES"]["section_edit"], array("SECTION_ID" => "0", "ACTION" => "new"))?>"><span><?=GetMessage("P_ADD_ALBUM")?></span></a></noindex>
	<?endif;?>
	<div class="empty-clear"></div>
</div>
<?
}

?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	"",
	Array(
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],

		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],

		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],

		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],

		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL" => $arResult["URL_TEMPLATES"]["search"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"SET_STATUS_404" => "N",

		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"SET_TITLE"	=>	"N",
	),
	$component
);
?><?

if($arParams["SET_TITLE"] != "N"):
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("P_PHOTO"));
endif;

?>