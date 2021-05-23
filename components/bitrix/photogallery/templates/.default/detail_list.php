<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-detail-list">
<?
/********************************************************************
				Input params
********************************************************************/
//***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] != "N" ? "Y" : "N"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
$order = (in_array($_REQUEST["order"], array("id", "shows", "rating", "comments")) ? $_REQUEST["order"] : "id");
$arSort = array(
	"id" => array(
		"title" => GetMessage("P_PHOTO_SORT_ID"),
		"description" => GetMessage("P_PHOTO_SORT_ID_TITLE")),
	"shows" => array(
		"title" => GetMessage("P_PHOTO_SORT_SHOWS"),
		"description" => GetMessage("P_PHOTO_SORT_SHOWS_TITLE"))
	);

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
?>
<noindex>
<div class="photo-controls photo-controls-mainpage">
	<ul class="photo-controls">
<?
$counter = 1;
foreach ($arSort as $key => $val):
	?><li class="photo-control <?=$key?> <?=($counter == 1 ? "photo-control-first" : "")?><?
		?><?=($order == $key ? " photo-control-active " : "")?>">
		<a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=".$key, array("order"))?>" <?
			?>rel="nofollow" title="<?=$val["description"]?>"><span><?=$val["title"]?></span></a>
	</li><?
	$counter++;
endforeach;
?>
	</ul>
	<div class="empty-clear"></div>
</div>
</noindex>
<?
$photo_from = trim($_REQUEST["photo_from"]);
$photo_to = trim($_REQUEST["photo_to"]);
?>
<div class="photo-filter photo-calendar-on-detaillist">
	<form name="photo_form" class="photo-form" action="<?=$APPLICATION->GetCurPageParam("", array("photo_from", "photo_to", "sessid"))?>" method="post">
		<input type="hidden" name="order" value="<?=$order?>" />
		<div class="photo-filter-fields">
			<div class="photo-filter-field photo-calendar-field">
				<label for="photo_from"><?=GetMessage("P_CHOSE_PHOTO_FROM_PERIOD")?>:</label>
				<?$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default",
					Array(
						"SHOW_INPUT" => "Y",
						"INPUT_NAME" => "photo_from",
						"INPUT_NAME_FINISH" => "photo_to",
						"INPUT_VALUE" => $photo_from,
						"INPUT_VALUE_FINISH" => $photo_to,
						"SHOW_TIME" => "N"
					), $component,
					array("HIDE_ICONS" => "Y"));
				?>
			</div>
		</div>
		<div class="photo-filter-buttons">
			<input type="submit" value="<?=GetMessage("P_FILTER_SHOW")?>" />
			<input type="button" onclick="this.form.photo_from.value=''; this.form.photo_to.value=''; this.form.submit();" value="<?=GetMessage("P_FILTER_RESET")?>" />
		</div>
	</form>
</div>
<?
?><div id="detail_list_order"><?
	$arParams["ELEMENT_FILTER"] = array();
	$sTitle = GetMessage("P_TITLE_ID");
	if ($order == "shows")
	{
		$arParams["ELEMENT_FILTER"] = array(">SHOW_COUNTER" => "0");
		$sTitle = GetMessage("P_TITLE_SHOWS");
	}
	elseif ($order == "rating" && $arParams["USE_RATING"] == "Y")
	{
		$arParams["ELEMENT_FILTER"] = array(">PROPERTY_RATING" => "0");
		$sTitle = GetMessage("P_TITLE_RATING");
	}
	elseif ($order == "comments" && ($arParams["USE_COMMENTS"] == "Y"))
	{
		if ($arParams["COMMENTS_TYPE"] == "blog")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_BLOG_COMMENTS_CNT" => "0");
		elseif ($arParams["COMMENTS_TYPE"] == "forum")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_FORUM_MESSAGE_CNT" => "0");
		$sTitle = GetMessage("P_TITLE_COMMENTS");
	}

?>
	<?$APPLICATION->IncludeComponent("bitrix:photogallery.detail.list.ex",
		"",
		Array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"SECTION_ID" => $arParams["SECTION_ID"],
			"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
			"ELEMENT_LAST_TYPE" => "period",
			"ELEMENTS_LAST_TIME_FROM" => $photo_from,
			"ELEMENTS_LAST_TIME_TO" => $photo_to,
			"ELEMENTS_LAST_COUNT" => 0,
			"ELEMENT_LAST_TIME" => 0,
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
			"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
			"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
			"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
			"ELEMENT_SORT_FIELD" => $order,
			"ELEMENT_SORT_ORDER" => "DESC",
			"ELEMENT_FILTER" => $arParams["ELEMENT_FILTER"],
			"DRAG_SORT" => "N",
			"MORE_PHOTO_NAV" => "Y",
			"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
			"SEARCH_URL" => $arResult["URL_TEMPLATES"]["search"],
			"DETAIL_SLIDE_SHOW_URL" => $arResult["URL_TEMPLATES"]["detail_slide_show"],
			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"ADDITIONAL_SIGHTS" => $arParams["ADDITIONAL_SIGHTS"],
			"PICTURES_SIGHT" => "",
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SET_TITLE" => "N",
			"THUMBNAIL_SIZE" => $arParams["THUMBNAIL_SIZE"],
			"SHOW_PAGE_NAVIGATION" => "bottom",
			"SHOW_CONTROLS" => "N",
			"CELL_COUNT" => $arParams["CELL_COUNT"],
			"SHOW_RATING" => (($arParams["USE_RATING"] == "Y" && $order == "rating") ? "Y" : "N"),
			"SHOW_COMMENTS" => (($arParams["USE_COMMENTS"] == "Y" && $order == "comments") ? "Y" : "N"),
			"SHOW_SHOWS" => ($order == "shows" ? "Y" : "N"),
			"SHOW_DATE" => ($order == "id" ? "Y" : "N"),
			"USE_COMMENTS" => $arParams["USE_COMMENTS"],
			"USE_RATING" => $arParams["USE_RATING"],
			"MAX_VOTE" => $arParams["MAX_VOTE"],
			"VOTE_NAMES" => $arParams["VOTE_NAMES"],
			"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
			"RATING_MAIN_TYPE" => $arParams["RATING_MAIN_TYPE"],
			"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
			"FORUM_ID" => $arParams["FORUM_ID"],
			"USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
			"URL_TEMPLATES_READ" => $arParams["URL_TEMPLATES_READ"],
			"URL_TEMPLATES_PROFILE_VIEW" => $arParams["URL_TEMPLATES_PROFILE_VIEW"],
			"POST_FIRST_MESSAGE" => $arParams["POST_FIRST_MESSAGE"],
			"PREORDER" => $arParams["PREORDER"],
			"SHOW_LINK_TO_FORUM" => $arParams["SHOW_LINK_TO_FORUM"] == "Y" ? "Y" : "N",
			"BLOG_URL" => $arParams["BLOG_URL"],
			"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"],
			"PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
?></div>
<?
/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
	if ($arParams["SET_TILTE"] != "N")
		$GLOBALS["APPLICATION"]->SetTitle($sTitle);
/************** BreadCrumb *****************************************/
	if ($arParams["SET_NAV_CHAIN"] != "N")
		$GLOBALS["APPLICATION"]->AddChainItem($sTitle);
/********************************************************************
				/Standart
********************************************************************/
?>
</div>