<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$URL_NAME_DEFAULT = array(
		"sections_top" => "");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}

?><div class="photo-controls"><?
?><noindex><a rel="nofollow" href="<?=CComponentEngine::MakePathFromTemplate($arParams["SECTIONS_TOP_URL"], array())?>" title="<?=GetMessage("P_UP_TITLE")?>" <?
	?> class="photo-action back-to-album"><?=GetMessage("P_UP")?></a></noindex><?
?></div><?

$order = (in_array($_REQUEST["order"], array("id", "shows", "rating", "comments")) ? $_REQUEST["order"] : "id");

$arSort = array(
	"id" => array(
		"title" => GetMessage("P_PHOTO_SORT_ID"),
		"description" => GetMessage("P_PHOTO_SORT_ID_TITLE")),
	"shows" => array(
		"title" => GetMessage("P_PHOTO_SORT_SHOWS"),
		"description" => GetMessage("P_PHOTO_SORT_SHOWS_TITLE")));

if ($arParams["USE_RATING"] == "Y")
{
	$arSort["rating"] = array(
		"title" => GetMessage("P_PHOTO_SORT_RATING"),
		"description" => GetMessage("P_PHOTO_SORT_RATING_TITLE"));
}

if ($arParams["USE_COMMENTS"] == "Y")
{
	$arSort["comments"] = array(
		"title" => GetMessage("P_PHOTO_SORT_COMMENTS"),
		"description" => GetMessage("P_PHOTO_SORT_COMMENTS_TITLE"));
}
?><div class="photo-controls photo-view only-on-main"><?
	$counter = 0;

	foreach ($arSort as $key => $val):

		$addClassName = (count($arSort) <= 1 ? " single" : "");
		$addClassName .= ($order == $key ? " active" : "");

		?><noindex><a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=".$key, array("order"))?>" <?
			?>title="<?=$val["description"]?>" class="photo-view <?=$key?><?=$addClassName?>"><?=$val["title"]?></a></noindex><?

		if ($counter < (count($arSort) - 1)):
			?><span class="empty"></span><?
		endif;

		$counter++;
	endforeach;
?></div><?

if ($arParams["SHOW_PHOTO_ON_DETAIL_LIST"] == "show_period"):

$photo_from = trim($_REQUEST["photo_from"]);
$photo_to = trim($_REQUEST["photo_to"]);
?><div id="empty-clear"></div><?
?><div id="photo_filter" style="position:relative;"><?
?><div id="photo_filter_form_div" style="display:<?=((!empty($photo_from) || !empty($photo_to)) ? 'block' : 'none')?>;position:absolute; background:white; z-index:1000;"><?
?><form action="<?=POST_FORM_ACTION_URI?>"<?
	?> id="photo_filter_form" method="post" class="photo-form"><?
	?><span id="photo_filter_calendar"><?
	$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default",
	Array(
		"SHOW_INPUT"	=>	"Y",
		"INPUT_NAME"	=>	"photo_from",
		"INPUT_NAME_FINISH"	=>	"photo_to",
		"INPUT_VALUE"	=>	$photo_from,
		"INPUT_VALUE_FINISH"	=>	$photo_to,
		"SHOW_TIME"	=>	"N"
	), $component,
	array("HIDE_ICONS" => "Y"));
	?></span><?
	?><span id="photo_filter_submit"><?
		?><input type="submit" name="photo_filter_submit" value="<?=GetMessage("P_FILTER_SHOW")?>" /><?
	?></span><?
?></form><?
?></div><?
?><noindex><a rel="nofollow" href="#" onclick="show_filter(); return false;" class="photo-action set-filter"><?=GetMessage("P_SET_FILTER")?></a></noindex><?
?></div><br />
<script>
function show_filter()
{
	document.getElementById('photo_filter_form_div').style.display='block';
	document.getElementById('photo_filter_form').photo_from.focus();
	return false;
}
</script><?
endif;

?><div id="detail_list_order"><?
	$arParams["ELEMENT_FILTER"] = array();
	if ($order == "shows")
	{
		$arParams["ELEMENT_FILTER"] = array(">SHOW_COUNTER" => "0");
	}
	elseif ($order == "rating" && $arParams["USE_RATING"] == "Y")
	{
		$arParams["ELEMENT_FILTER"] = array(">PROPERTY_RATING" => "0");
	}
	elseif ($order == "comments" && ($arParams["USE_COMMENTS"] == "Y"))
	{
		if ($arParams["COMMENTS_TYPE"] == "blog")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_BLOG_COMMENTS_CNT" => "0");
		elseif ($arParams["COMMENTS_TYPE"] == "forum")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_FORUM_MESSAGE_CNT" => "0");
	}

?><?$APPLICATION->IncludeComponent("bitrix:photogallery.detail.list", $arParams["TEMPLATE_LIST"],
		Array(
			"IBLOCK_TYPE"	=>	$arParams["IBLOCK_TYPE"],
			"IBLOCK_ID"	=>	$arParams["IBLOCK_ID"],
			"SECTION_ID"	=>	$arParams["SECTION_ID"],
			"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
			"ELEMENT_LAST_TYPE"	=>	str_replace("show_", "", $arParams["SHOW_PHOTO_ON_DETAIL_LIST"]),
			"ELEMENTS_LAST_TIME_FROM"	=>	$photo_from,
			"ELEMENTS_LAST_TIME_TO"	=>	$photo_to,
			"ELEMENTS_LAST_COUNT" => $arParams["SHOW_PHOTO_ON_DETAIL_LIST_COUNT"],
			"ELEMENT_LAST_TIME" => $arParams["SHOW_PHOTO_ON_DETAIL_LIST_COUNT"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
			"PAGE_ELEMENTS"	=>	$arParams["ELEMENTS_PAGE_ELEMENTS"],
			"USE_DESC_PAGE"	=>	$arParams["ELEMENTS_USE_DESC_PAGE"],
			"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
			"ELEMENT_SORT_FIELD" => $order,
			"ELEMENT_SORT_ORDER" => "desc",
			"ELEMENT_FILTER" => $arParams["ELEMENT_FILTER"],
			"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
			"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
			"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"ADDITIONAL_SIGHTS"	=>	$arParams["ADDITIONAL_SIGHTS"],
			"PICTURES_SIGHT"	=>	"",
			"SET_STATUS_404" => "N",
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SET_TITLE"	=>	$arParams["SET_TITLE"],
			"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
			"SHOW_PAGE_NAVIGATION"	=>	"bottom",
			"SHOW_CONTROLS"	=>	"N",
			"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
			"SHOW_TAGS" => $arParams["SHOW_TAGS"],
			"SHOW_RATING" => (($arParams["USE_RATING"] == "Y" && $order == "rating") ? "Y" : $arParams["SHOW_RATING"]),
			"SHOW_COMMENTS" => (($arParams["USE_COMMENTS"] == "Y" && $order == "comments") ? "Y" : $arParams["SHOW_COMMENTS"]),
			"SHOW_SHOWS" => ($order == "shows" ? "Y" : $arParams["SHOW_SHOWS"]),
			"MAX_VOTE" => $arParams["MAX_VOTE"],
			"VOTE_NAMES" => $arParams["VOTE_NAMES"],
			"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"]
			),
			$component,
			array("HIDE_ICONS" => "Y"));
?></div>