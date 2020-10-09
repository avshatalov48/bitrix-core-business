<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
	$arParams["SHOW_ONLY_PUBLIC"] = ($arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "Y" : "N");
	$arParams["MODERATE"] = ($arParams["MODERATE"] == "Y" && $arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "Y" : "N");
	$arParams["PERMISSION"] = trim($arParams["PERMISSION"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[mb_strtoupper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][mb_strtolower($URL)]);
		if (empty($arParams[mb_strtoupper($URL)."_URL"]))
			$arParams[mb_strtoupper($URL)."_URL"] = $APPLICATION->GetCurPage().($URL == "index" ? "" : "?");
		$arParams["~".mb_strtoupper($URL)."_URL"] = $arParams[mb_strtoupper($URL)."_URL"];
		$arParams[mb_strtoupper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".mb_strtoupper($URL)."_URL"]);
	}
//***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] != "N" ? "Y" : "N"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default params
********************************************************************/
	if (!empty($_REQUEST["photo_filter_reset"]))
	{
		if (!empty($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]) && file_exists($_SERVER['DOCUMENT_ROOT']."/urlrewrite.php") && check_bitrix_sessid())
			$url = $_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"];
		else
			$url = $APPLICATION->GetCurPageParam("", array("photo_from", "photo_to", "group_photo",
				"photo_filter_reset", "order", "mode"));
		$url = str_replace(array("&group_photo=Y", "&amp;group_photo=Y"), "", $url);

		LocalRedirect($url);
	}

	$arResult["ORDER"] = array("date_create", "shows");
	if ($arParams["USE_RATING"] == "Y")
		$arResult["ORDER"][] = "rating";
	if ($arParams["USE_COMMENTS"] == "Y")
		$arResult["ORDER"][] = "comments";

	$arResult["ORDER_BY"] = (in_array($_REQUEST["order"], $arResult["ORDER"]) ? $_REQUEST["order"] : "date_create");
	$arResult["PERIOD_FROM"] = trim($_REQUEST["photo_from"]);
	$arResult["PERIOD_TO"] = trim($_REQUEST["photo_to"]);
	$arResult["GROUP_BY_DATE_CREATE"] = ($_REQUEST["group_photo"] == "Y" ? "Y" : "N");

	$arResult["MODE"] = ($_REQUEST["mode"] == "public" || $_REQUEST["mode"] == "active" ? $_REQUEST["mode"] : "simple");
	$arResult["SHOW_FILTER"] = ((!empty($arResult["PERIOD_FROM"]) || !empty($arResult["PERIOD_TO"]) || $arResult["GROUP_BY_DATE_CREATE"] == "Y" ||
		$arResult["MODE"] != "simple") ? "Y" : "N");
/********************************************************************
				/Default params
********************************************************************/

$arSort = array(
	"date_create" => array(
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
else
{
	$arSort["shows"] = array(
		"title" => GetMessage("P_PHOTO_SORT_RATING"),
		"description" => GetMessage("P_PHOTO_SORT_RATING_TITLE"));
}

if ($arParams["USE_COMMENTS"] == "Y")
{
	$arSort["comments"] = array(
		"title" => GetMessage("P_PHOTO_SORT_COMMENTS"),
		"description" => GetMessage("P_PHOTO_SORT_COMMENTS_TITLE"));
}

$arFilter = array();
if ($arParams["PERMISSION"] < "U")
{
	$arFilter["ACTIVE"] = "Y";
	$arResult["MODE"] = false;
	if ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y")
		$arFilter["PROPERTY_PUBLIC_ELEMENT"] = "Y";
}

$sTitle = GetMessage("P_PHOTO_ORDER_BY_DATE_CREATE");
if ($arResult["MODE"] == "public")
{
	$arResult["ORDER_BY"] = false;
	$arFilter["PROPERTY_PUBLIC_ELEMENT"] = "Y";
	$arFilter["PROPERTY_APPROVE_ELEMENT"] = "X";
	$sTitle = GetMessage("P_NOT_PULIC_PHOTO");
}
elseif ($arResult["MODE"] == "active")
{
	$arResult["ORDER_BY"] = false;
	$arFilter["ACTIVE"] = "N";
	$sTitle = GetMessage("P_NOT_ACTIVE_PHOTO");
}
elseif ($arResult["ORDER_BY"] == "shows")
{
	$arFilter[">SHOW_COUNTER"] = "0";
	$sTitle = GetMessage("P_PHOTO_ORDER_BY_SHOWS");
}
elseif ($arResult["ORDER_BY"] == "rating")
{
	$arFilter[">PROPERTY_RATING"] = "0";
	$sTitle = GetMessage("P_PHOTO_ORDER_BY_RATING");
}
elseif ($arResult["ORDER_BY"] == "comments")
{
	if ($arParams["COMMENTS_TYPE"] == "blog")
		$arFilter[">PROPERTY_BLOG_COMMENTS_CNT"] = "0";
	elseif ($arParams["COMMENTS_TYPE"] == "forum")
		$arFilter[">PROPERTY_FORUM_MESSAGE_CNT"] = "0";
	$sTitle = GetMessage("P_PHOTO_ORDER_BY_COMMENTS");
}

?>
<div class="photo-page-detail-list">
<noindex>
<div class="photo-controls photo-controls-mainpage">
	<ul class="photo-controls">
<?
$counter = 1;
foreach ($arSort as $key => $val):
?>
		<li class="photo-control <?=$key?> <?=($counter == 1 ? "photo-control-first" : "")?> <?= ($counter == count($arSort) ? "photo-control-last" : "")?> <?
			?><?=($arResult["ORDER_BY"] == $key ? " photo-control-active " : "")?>">
			<a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=".$key, array("order", "mode"))?>" <?
			?>rel="nofollow" title="<?=$val["description"]?>"><span><?=$val["title"]?></span></a>
		</li>
<?
	$counter++;
endforeach;
?>
	</ul>
	<div class="empty-clear"></div>
</div>
<?
if ($arParams["PERMISSION"] >= "U")
{
	CModule::IncludeModule("iblock");
	$bNeedModerate =  false; $bNeedPublic = false;

	$arMargin = array();
	$arFilterModerate = array("IBLOCK_ID" => $arParams["IBLOCK_ID"]);
	$res = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION");
	if (is_array($res) && !empty($res["UF_PASSWORD"]))
	{
		CModule::IncludeModule("iblock");
		$arFilterPassoword = $arFilterModerate;
		$arFilterPassoword["!=UF_PASSWORD"] = "";
		$db_res = CIBlockSection::GetList(Array(), $arFilterPassoword);
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				$arMargin[] = array($res["LEFT_MARGIN"], $res["RIGHT_MARGIN"]);
			}while ($res = $db_res->Fetch());
		}
		if (count($arMargin) > 0)
			$arFilterModerate["!SUBSECTION"] = $arMargin;
	}

	$arFilterApprove = $arFilterModerate + array("!ACTIVE" => "Y");
	$bNeedModerate = CIBlockElement::GetList(
		array(),
		$arFilterApprove,
		array(),
		false,
		array("ID", "IBLOCK_ID", "ACTIVE"));

	if ($arParams["MODERATE"] == "Y"):
		$arFilterPublic = $arFilterModerate + array("PROPERTY_PUBLIC_ELEMENT" => "Y", "PROPERTY_APPROVE_ELEMENT" => "X");
		$bNeedPublic = CIBlockElement::GetList(
			array(),
			$arFilterPublic,
			array(),
			false,
			array("ID"));
	endif;
	if ($bNeedModerate || $bNeedPublic)
	{
?>
	<div class="photo-note-box photo-note-moderate">
		<div class="photo-note-box-text">
			<ul class="photo-list">
<?
		if ($bNeedPublic)
		{
?>
				<li><?=GetMessage("P_NOT_PULIC_PHOTO_2")?>: <a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam(
					"&mode=public", array("order", "mode"))?>"><?=$bNeedPublic?></a>.</li>
<?
		}
		if ($bNeedModerate)
		{
?>
				<li><?=GetMessage("P_NOT_ACTIVE_PHOTO_2")?>: <a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam(
					"&mode=active", array("order", "mode"))?>"><?=$bNeedModerate?></a>.</li>
<?
		}
?>
			</ul>
		</div>
	</div>
<?
	}
}
?>
</noindex>

<div class="photo-filter photo-calendar-on-detaillist">
	<form action="" id="photo_filter_form" class="photo_form" method="get">
		<input type="hidden" name="PAGE_NAME" value="detail_list" />
		<input type="hidden" name="order" value="<?=$arResult["ORDER_BY"]?>" />
		<div class="photo-filter-fields">
			<div class="photo-filter-field photo-calendar-field">
				<label for="photo_from"><?=GetMessage("P_SELECT_PHOTO_FROM_PERIOD")?></label>
					<?$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default",
						Array(
							"SHOW_INPUT"	=>	"Y",
							"INPUT_NAME"	=>	"photo_from",
							"INPUT_NAME_FINISH"	=>	"photo_to",
							"INPUT_VALUE"	=>	$arResult["PERIOD_FROM"],
							"INPUT_VALUE_FINISH"	=>	$arResult["PERIOD_TO"],
							"SHOW_TIME"	=>	"N"
						), $component,
						array("HIDE_ICONS" => "Y"));?>
			</div>
			<div class="photo-filter-field photo-filter-field-group">
				<input type="checkbox" name="group_photo" id="group_photo" value="Y" <?=
				($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? " checked='checked'" : "")?> />
				<label for="group_photo"><?=GetMessage("P_GROUP_BY_DATE_CREATE")?></label>
			</div>
		</div>
		<div class="photo-filter-field-buttons">
			<input type="submit" name="photo_filter_submit" value="<?=GetMessage("P_FILTER_SHOW")?>" />
			<input type="submit" name="photo_filter_reset" value="<?=GetMessage("P_FILTER_RESET")?>" />
		</div>
	</form>
</div>
<?
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list.ex",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"USER_ALIAS" => isset($_REQUEST['USER_ALIAS']) ? $_REQUEST['USER_ALIAS'] : "",
		"BEHAVIOUR" => "USER",
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TYPE" => (!empty($arResult["PERIOD_FROM"]) || !empty($arResult["PERIOD_TO"]) ? "period" : ""),
		"ELEMENTS_LAST_TIME_FROM"	=>	$arResult["PERIOD_FROM"],
		"ELEMENTS_LAST_TIME_TO"	=>	$arResult["PERIOD_TO"],
		"ELEMENT_SORT_FIELD"	=>	($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? "created_date" : ($arResult["ORDER_BY"] == "date_create" ? "id" : $arResult["ORDER_BY"])),
		"ELEMENT_SORT_ORDER"	=>	"desc",
		"ELEMENT_SORT_FIELD1"	=>	($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? ($arResult["ORDER_BY"] == "date_create" ? "id" : $arResult["ORDER_BY"]) : ""),
		"ELEMENT_SORT_ORDER1"	=>	"desc",
		"ELEMENT_FILTER" => $arFilter,

		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		"DETAIL_EDIT_URL" => $arResult["URL_TEMPLATES"]["detail_edit"],

		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],

		"USE_DESC_PAGE"	=>	$arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS"	=>	($_REQUEST["AJAX_CALL"] == "Y" ? "10" : $arParams["ELEMENTS_PAGE_ELEMENTS"]),
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],

		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "Y",
		"SHOW_PHOTO_USER" => "N",
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],

		"THUMBNAIL_SIZE"	=>	$arParams["THUMBNAIL_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"bottom",
		"TEMPLATE"	=>	"",

		"SHOW_FORM" => "N",

		"SHOW_TAGS" => "N",
		"SHOW_RATING" => ($arResult["ORDER_BY"] == "rating" ? "Y" : "N"),
		"SHOW_COMMENTS" => ($arResult["ORDER_BY"] == "comments" ? "Y" : "N"),
		"SHOW_SHOWS" => ($arResult["ORDER_BY"] == "shows" ? "Y" : "N"),
		"SHOW_DATE" => $arResult["GROUP_BY_DATE_CREATE"],
		"NEW_DATE_TIME_FORMAT" => (empty($arParams["DATE_FORMAT"]) ? $arParams["DATE_TIME_FORMAT_DETAIL"] : $arParams["DATE_FORMAT"]),

		"INCLUDE_SLIDER" => "Y",

		"USE_RATING" => $arParams["USE_RATING"],
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
		"RATING_MAIN_TYPE" => $arParams["RATING_MAIN_TYPE"],

		"MAX_SHOWED_PHOTOS" => 500,
		"DRAG_SORT" => "N",
		"MORE_PHOTO_NAV" => "Y",
		"MODERATION" => $arParams["MODERATION"],

		"USE_COMMENTS" => $arParams["USE_COMMENTS"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		"FORUM_ID" => $arParams["FORUM_ID"],
		"USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
		"PREORDER" => $arParams["PREORDER"],
		"BLOG_URL" => $arParams["BLOG_URL"],
		"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"],
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>
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