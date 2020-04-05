<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
	$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" && IsModuleInstalled("search") ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/
$URL_NAME_DEFAULT = array(
	"search" => "PAGE_NAME=search",
	"detail_list" => "PAGE_NAME=detail_list&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
	"section_edit" => "PAGE_NAME=section_edit&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
	"upload" => "PAGE_NAME=upload&SECTION_ID=#SECTION_ID#&ACTION=upload"
);

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
}

$arRes = array();
if (is_array($arParams["SHOW_LINK_ON_MAIN_PAGE"]))
{
	$detail_list = array(
		"~url" => CComponentEngine::MakePathFromTemplate($arParams["DETAIL_LIST_URL"], array("SECTION_ID" => "all", "ELEMENT_ID" => "all")));
	$detail_list["url"] = $detail_list["~url"];
	if (strpos($detail_list["url"], "?") === false)
		$detail_list["url"] .= "?";
	foreach ($arParams["SHOW_LINK_ON_MAIN_PAGE"] as $key)
	{
		if ($key == "id")
		{
			$arRes["id"] = array(
				"title" => GetMessage("P_PHOTO_SORT_ID"),
				"description" => GetMessage("P_PHOTO_SORT_ID_TITLE"),
				"url" => $detail_list["~url"]
			);
		}
		elseif ($key == "shows")
		{
			$arRes["shows"] = array(
				"title" => GetMessage("P_PHOTO_SORT_SHOWS"),
				"description" => GetMessage("P_PHOTO_SORT_SHOWS_TITLE"),
				"url" => $detail_list["url"]."&amp;order=shows"
			);
		}
		elseif ($key == "rating" && ($arParams["USE_RATING"] == "Y"))
		{
			$arRes["rating"] = array(
				"title" => GetMessage("P_PHOTO_SORT_RATING"),
				"description" => GetMessage("P_PHOTO_SORT_RATING_TITLE"),
				"url" => $detail_list["url"]."&amp;order=rating"
			);
		}
		elseif ($key == "comments" && ($arParams["USE_COMMENTS"] == "Y"))
		{
			$arRes["comments"] = array(
				"title" => GetMessage("P_PHOTO_SORT_COMMENTS"),
				"description" => GetMessage("P_PHOTO_SORT_COMMENTS_TITLE"),
				"url" => $detail_list["url"]."&amp;order=comments"
			);
		}
	}
}
?><div class="photo-page-main"><?
if ($arParams["PERMISSION"] >= "U" || $arParams["SHOW_TAGS"] == "Y" || !empty($arRes))
{
	ob_start();
	if ($arParams["PERMISSION"] >= "U")
	{
	?>
	<div class="photo-controls photo-controls-buttons">
		<ul class="photo-controls">
			<li class="photo-control photo-control-first photo-control-album-add">
				<a rel="nofollow" href="<?=CComponentEngine::MakePathFromTemplate($arParams["SECTION_EDIT_URL"],
					array("SECTION_ID" => "0", "ACTION" => "new"))?>" <?
					?>onclick="EditAlbum('<?=CUtil::JSEscape(CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
						array("SECTION_ID" => "0", "ACTION" => "new")))?>'); return false;">
					<span><?=GetMessage("P_ADD_ALBUM")?></span></a>
			</li>
			<li class="photo-control photo-control-last photo-control-album-upload">
				<a rel="nofollow" href="<?=CComponentEngine::MakePathFromTemplate($arParams["UPLOAD_URL"], array("SECTION_ID" => "0"))?>">
					<span><?=GetMessage("P_UPLOAD")?></span></a>
			</li>
		</ul>
		<div class="empty-clear"></div>
	</div>
	<?
	}
	if (!empty($arRes))
	{
?>
<noindex>
	<div id="photo-links-on-main-page">
		<div class="photo-header-big">
			<div class="photo-header-inner">
				<?=GetMessage("P_PHOTOGRAPHIES")?>
			</div>
		</div>
		<div class="photo-controls photo-controls-mainpage">
			<ul class="photo-controls">
		<?
		$counter = 1;
		foreach ($arRes as $key => $val):
			?><li class="photo-control <?=$key?> <?=($counter == 1 ? "photo-control-first" : "")?> <?=($counter == count($arRes) ? "photo-control-last" : "")?>">
				<a rel="nofollow" href="<?=$val["url"]?>" title="<?=$val["description"]?>"><span><?=$val["title"]?></span></a>
			</li><?
			$counter++;
		endforeach;
		?>
			</ul>
			<div class="empty-clear"></div>
		</div>
	</div>
</noindex>
<?
	}
	if ($arParams["SHOW_TAGS"] == "Y")
	{
	?><?$result = $APPLICATION->IncludeComponent(
		"bitrix:search.tags.cloud",
		"photogallery",
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
		$component,
		array("HIDE_ICONS" => "Y")
	);?><?
	}
	$res = ob_get_clean();
	if (!empty($res)):
?>
	<div id="photo-main-page-right">
		<?=$res?>
	</div>
<?
	endif;
}

?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	"",
	Array(
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"PHOTO_LIST_MODE" => $arParams["PHOTO_LIST_MODE"],
		"SHOWN_ITEMS_COUNT" => $arParams["SHOWN_ITEMS_COUNT"],

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
		"DETAIL_EDIT_URL" => $arResult["URL_TEMPLATES"]["detail_edit"],

		"ALBUM_PHOTO_THUMBS_SIZE" => $arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE" => $arParams["ALBUM_PHOTO_SIZE"],
		"SECTION_LIST_THUMBNAIL_SIZE" => $arParams["SECTION_LIST_THUMBNAIL_SIZE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],

		"SET_TITLE"	=>	"N",
		"SHOW_RATING" => $arParams["USE_RATING"],
		"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
		"SHOW_COMMENTS" => $arParams["USE_COMMENTS"],
		"SHOW_DATE" => $arParams["SHOW_DATE"],
		"SHOW_DESRIPTION" => $arParams["SHOW_DESRIPTION"],

		"USE_RATING" => $arParams["USE_RATING"],
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
		"RATING_MAIN_TYPE" => $arParams["RATING_MAIN_TYPE"],

		"USE_COMMENTS" => $arParams["USE_COMMENTS"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],

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
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],

		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
)
?>
<script>
function __photo_check_right_height()
{
	var res = document.getElementsByTagName('li');
	var result = false;
	for (var ii = 0; ii < res.length; ii++)
	{
		if (res[ii].id.match(/photo\_album\_info\_(\d+)/gi))
		{
			var kk = res[ii].offsetHeight;
			var jj = document.getElementById('photo-main-page-right');
			if (jj && kk > 0) {
				jj.style.height = ((parseInt(jj.offsetHeight / kk) + 1) * kk + 1 + 'px');
				result = true;
				break;
			}
		}
	}
	if (!result)
	{
		setTimeout(__photo_check_right_height, 150);
	}
}
//setTimeout(__photo_check_right_height, 150);
</script>
<?

if($arParams["SET_TITLE"] != "N"):
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("P_PHOTO"));
endif;
?>
	<div class="empty-clear"></div>
</div>