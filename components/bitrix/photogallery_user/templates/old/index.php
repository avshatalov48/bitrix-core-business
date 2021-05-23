<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SHOW_BEST_ELEMENT"] = ($arParams["SHOW_BEST_ELEMENT"] == "N" ? "N" : "Y");
	$arParams["MODERATE"] = ($arParams["MODERATE"] == "Y" ? "Y" : "N");
	$arParams["PERMISSION"] = trim($arParams["PERMISSION"]);
	$arParams["SHOW_ONLY_PUBLIC"] = ($arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "Y" : "N");
	$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" ? "Y" : "N");
	$arParams["USE_COMMENTS"] = ($arParams["USE_COMMENTS"] == "Y" ? "Y" : "N");
	$arParams["COMMENTS_TYPE"] = ($arParams["COMMENTS_TYPE"] == "FORUM" ? "FORUM" : "BLOG");
	$arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"] = ($arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"] <= 0 ? 10 : $arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"]);
	$arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"] = ($arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"] <= 0 ? 70 : $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"search" => "PAGE_NAME=search",
		"detail_list" => "PAGE_NAME=detail_list",
		"galleries" => "PAGE_NAME=galleries&USER_ID=#USER_ID#",
		"tags" => "PAGE_NAME=tags");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
/********************************************************************
				/Input params
********************************************************************/

$sDetailListUrl = CComponentEngine::MakePathFromTemplate($arParams["DETAIL_LIST_URL"], array());
if (strpos($sDetailListUrl, "?") === false)
	$sDetailListUrl .= "?";
$arShows = array("SHOW_RATING" => "N", "SHOW_COMMENTS" => "N", "SHOW_SHOWS" => "N");
$sSortField = "ID";
$arFilter = array("ACTIVE" => "Y");
if ($arParams["MODERATE"] == "Y")
	$arFilter["PROPERTY_APPROVE_ELEMENT"] = "Y";
if ($arParams["SHOW_ONLY_PUBLIC"] == "Y")
	$arFilter["PROPERTY_PUBLIC_ELEMENT"] = "Y";

$arFilterBest = $arFilter;
if ($arParams["USE_RATING"] == "Y"):
	$arFilterBest[">PROPERTY_RATING"] = "0";
	$arShows["SHOW_RATING"] = "Y";
	$sSortField = "PROPERTY_RATING";
elseif ($arParams["USE_COMMENTS"] == "Y"):
	if ($arParams["COMMENTS_TYPE"] == "FORUM"):
		$arFilterBest[">PROPERTY_FORUM_MESSAGE_CNT"] = "0";
		$sSortField = "PROPERTY_FORUM_MESSAGE_CNT";
	else:
		$arFilterBest[">PROPERTY_BLOG_COMMENTS_CNT"] = "0";
		$sSortField = "PROPERTY_BLOG_COMMENTS_CNT";
	endif;
	$arShows["SHOW_COMMENTS"] = "Y";
else:
	$arShows["SHOW_SHOWS"] = "Y";
	$sSortField = "shows";
endif;

if ($arParams["SET_TITLE"] != "N"):
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));
endif;

$bSearch = ($arParams["SHOW_TAGS"] == "Y" && IsModuleInstalled("search"));

/********************************************************************
				HTML
********************************************************************/
if ($arParams["PERMISSION"] >= "U"):
	$bNeedModerate =  false; $bNeedPublic = false;
	$arNavParams = array("nTopCount" => 1, "bDescPageNumbering" => "N");
	CModule::IncludeModule("iblock");
	$db_res = CIBlockElement::GetList(array(), array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"!ACTIVE" => "Y"), false, $arNavParams, array("ID", "IBLOCK_ID", "ACTIVE"));
	if ($db_res && $res = $db_res->Fetch()):
		$bNeedModerate =  true;
	endif;

	if ($arParams["MODERATE"] == "Y"):
		$db_res = CIBlockElement::GetList(array(), array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"PROPERTY_PUBLIC_ELEMENT" => "Y",
			"PROPERTY_APPROVE_ELEMENT" => "X"), false, $arNavParams, array("ID"));
		if ($db_res && $res = $db_res->Fetch()):
			$bNeedPublic = true;
		endif;
	endif;

	if ($bNeedModerate || $bNeedPublic):
?>
<div class="photo-controls photo-action">
<?
		if ($bNeedModerate):
?>
	<noindex><a rel="nofollow" href="<?=$sDetailListUrl."&mode=active"?>" class="photo-action photo-moderate" title="<?=GetMessage("P_NOT_MODERATED_TITLE")?>">
		<?=GetMessage("P_NOT_MODERATED")?>
	</a></noindex>
<?
		endif;
		if ($bNeedPublic):
?>
	<noindex><a rel="nofollow" href="<?=$sDetailListUrl."&mode=public"?>" class="photo-action photo-public" title="<?=GetMessage("P_NOT_APPROVED_TITLE")?>">
		<?=GetMessage("P_NOT_APPROVED")?>
	</a></noindex>
<?
		endif;
?>
</div>
<?
	endif;
endif;
?>
<div class="empty-clear"></div>
<div id="photo-main-div">
	<table border="0" cellpadding="0" cellspacing="0" id="photo-main-table">
		<tr>
<?
if ($arParams["SHOW_BEST_ELEMENT"] == "Y"):
?>
			<td id="photo-main-td-left">
				<div id="photo-main-div-best">
					<?$element_id = $APPLICATION->IncludeComponent(
						"bitrix:photogallery.detail.list",
						"simple",
						Array(
							"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
							"IBLOCK_ID" => $arParams["IBLOCK_ID"],
							"BEHAVIOUR" => "USER",
							"USER_ALIAS" => "",
							"PERMISSION" => "",
							"SECTION_ID" => 0,
							"SECTION_CODE" => "",

							"ELEMENTS_LAST_COUNT" => "",
							"ELEMENT_LAST_TIME" => "",
							"ELEMENTS_LAST_TIME_FROM" => "",
							"ELEMENTS_LAST_TIME_TO" => "",
							"ELEMENT_SORT_FIELD"	=>	"created_date",
							"ELEMENT_SORT_ORDER"	=>	"desc",
							"ELEMENT_SORT_FIELD1"	=>	$sSortField,
							"ELEMENT_SORT_ORDER1"	=>	"desc",
							"ELEMENT_FILTER" => $arFilterBest,
							"ELEMENT_SELECT_FIELDS" => array(),
							"PROPERTY_CODE" => array(),

							"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
							"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
							"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
							"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],

							"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
							"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],

							"USE_DESC_PAGE"	=>	"Y",
							"PAGE_ELEMENTS"	=>	"1",
							"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
							"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
							"PICTURES_SIGHT" =>	"detail",
							"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],

							"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
							"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
							"SET_STATUS_404" => "N",

							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"SET_TITLE" => "N",

							"THUMBS_SIZE"	=>	$arParams["PREVIEW_SIZE"],
							"SHOW_PAGE_NAVIGATION"	=>	"none",

							"SHOW_TAGS"	=>	"N",
							"SHOW_RATING"	=>	"N",
							"SHOW_COMMENTS"	=>	"N",
							"SHOW_SHOWS"	=>	"N"
						),
						$component);
					?>
				</div>
			</td>
			<td id="photo-main-td-right">
<?
	$arFilterBest["!ID"] = $element_id;
else:
?>
			<td id="photo-main-td-left" colspan="2">
<?
endif;

ob_start();
	?><?$APPLICATION->IncludeComponent(
		"bitrix:photogallery.detail.list",
		"ascetic",
		Array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"BEHAVIOUR" => "USER",
			"USER_ALIAS" => "",
			"PERMISSION" => "",
			"SECTION_ID" => 0,
			"SECTION_CODE" => "",

			"ELEMENTS_LAST_COUNT" => "",
			"ELEMENT_LAST_TIME" => "",
			"ELEMENTS_LAST_TIME_FROM" => "",
			"ELEMENTS_LAST_TIME_TO" => "",
			"ELEMENT_SORT_FIELD"	=>	"created_date",
			"ELEMENT_SORT_ORDER"	=>	"desc",
			"ELEMENT_SORT_FIELD1"	=>	$sSortField,
			"ELEMENT_SORT_ORDER1"	=>	"desc",
			"ELEMENT_FILTER" => $arFilterBest,
			"ELEMENT_SELECT_FIELDS" => array(),
			"PROPERTY_CODE" => array(),

			"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
			"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
			"DETAIL_SLIDE_SHOW_URL" => $arResult["URL_TEMPLATES"]["detail_slide_show"],
			"SEARCH_URL" => $arResult["URL_TEMPLATES"]["search"],

			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],

			"USE_DESC_PAGE" => "N",
			"PAGE_ELEMENTS" => $arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"],
			"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],

			"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
			"PICTURES_SIGHT" => "standart",
			"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
			"SET_STATUS_404" => "N",

			"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
			"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],

			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SET_TITLE" => "N",

			"THUMBS_SIZE" => $arParams["THUMBS_SIZE"],
			"SHOW_PAGE_NAVIGATION" => "none",

			"SQUARE" => "Y",
			"PERCENT" => $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]
		),
		$component
	);?><?

$best = ob_get_contents();
ob_end_clean();

?>
				<?$APPLICATION->IncludeComponent("bitrix:photogallery.interface", "bookmark",
					Array(
						"DATA" => array(
							array(
								"HEADER" => array(
									"TITLE" => GetMessage("P_BEST_PHOTO"),
									"LINK" => $sDetailListUrl."&order=".$sSortField),
								"BODY" => $best,
								"ACTIVE" => "Y"),
							array(
								"HEADER" => array(
									"TITLE" => GetMessage("P_BEST_PHOTOS"),
									"LINK" => $sDetailListUrl."&order=".$sSortField,
									"HREF" => "Y"),
							))),
					$component,
					array("HIDE_ICONS" => "Y"));?>
			</td>
		</tr>
		<tr>
<?

if($bSearch):
ob_start();
?>
				<?$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default",
						Array(
						"SEARCH" => $arResult["REQUEST"]["~QUERY"],
						"TAGS" => $arResult["REQUEST"]["~TAGS"],

						"PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"],
						"PERIOD" => $arParams["TAGS_PERIOD"],
						"TAGS_INHERIT" => $arParams["TAGS_INHERIT"],

						"URL_SEARCH" =>  CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()),

						"FONT_MAX" => $arParams["TAGS_FONT_MAX"],
						"FONT_MIN" => $arParams["TAGS_FONT_MIN"],
						"COLOR_NEW" => $arParams["TAGS_COLOR_NEW"],
						"COLOR_OLD" => $arParams["TAGS_COLOR_OLD"],
						"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"],
						"WIDTH" => "100%",
						"CACHE_TIME" => $arParams["CACHE_TIME"],
						"CACHE_TYPE" => $arParams["CACHE_TYPE"],
						"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]),
						"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"])
						), $component);?>
<?
$tags_cloud = ob_get_clean();
?>
			<td id="photo-main-td-middle-left">
				<?$APPLICATION->IncludeComponent("bitrix:photogallery.interface",
					"bookmark",
					Array("DATA" => array(
						array(
							"HEADER" => array(
								"TITLE" => GetMessage("P_TAGS_POPULAR"),
								"LINK" => ""),
							"BODY" => $tags_cloud,
							"ACTIVE" => "Y"),
						array(
							"HEADER" => array(
								"TITLE" => GetMessage("P_TAGS_ALL"),
								"HREF" => "Y",
								"LINK" => CComponentEngine::MakePathFromTemplate($arParams["TAGS_URL"], array()))))),
					$component,
					array("HIDE_ICONS" => "Y"));?>
			</td>
			<td id="photo-main-td-middle-right">
<?
else:
?>
			<td id="photo-main-td-middle-left" colspan="2">
<?
endif;
?>
				<div class="photo-head"><a href="<?=CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => "users"))?>"><?
					?><?=GetMessage("P_GALLERIES")?></a></div>
				<div id="photo-main-galleries">
					<?$APPLICATION->IncludeComponent("bitrix:photogallery.gallery.list",
						"ascetic",
						Array(
							"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
							"IBLOCK_ID" => $arParams["IBLOCK_ID"],
							"USER_ID"	=>	"0",
							"SORT_BY"	=>	"ID",
							"SORT_ORD"	=>	"DESC",
							"INDEX_URL"	=>	$arResult["URL_TEMPLATES"]["index"],
							"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
							"GALLERY_EDIT_URL"	=>	$arResult["URL_TEMPLATES"]["gallery_edit"],
							"UPLOAD_URL"	=>	$arResult["URL_TEMPLATES"]["upload"],
							"ONLY_ONE_GALLERY"	=>	$arParams["ONLY_ONE_GALLERY"],
							"GALLERY_SIZE"	=>	$arParams["GALLERY_SIZE"],
							"PAGE_ELEMENTS"	=>	($bSearch ? 3 : 6),
							"PAGE_NAVIGATION_TEMPLATE"	=>	$arParams["PAGE_NAVIGATION_TEMPLATE"],
							"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT_SECTION"],
							"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
							"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
							"SET_STATUS_404" => "N",

							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"],
							"SET_TITLE" => "N",

							"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"],
							"SHOW_PAGE_NAVIGATION"	=>	"none",

							), $component,
							array("HIDE_ICONS" => "Y"));?>
					<div class="photo-gallery-ascetic">
						<div class="all-elements">
							<a href="<?=CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => "users"));
							?>"><?=GetMessage("P_VIEW_ALL_GALLERIES")?></a>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>

<?
ob_start();
?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:photogallery.detail.list",
				"ascetic",
				Array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"SECTION_ID" => 0,
					"SECTION_CODE" => "",
					"USER_ALIAS" => "",
					"BEHAVIOUR" => "USER",
					"ELEMENTS_LAST_COUNT" => "",
					"ELEMENT_LAST_TIME" => "",
					"ELEMENT_SORT_FIELD"	=>	"date_create",
					"ELEMENT_SORT_ORDER"	=>	"desc",
					"ELEMENT_SORT_FIELD1" => "",
					"ELEMENT_SORT_ORDER1" => "",
					"ELEMENT_FILTER" => $arFilter,
					"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
					"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
					"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
					"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
					"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
					"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
					"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
					"PAGE_ELEMENTS" => "10",
					"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
					"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
					"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
					"PICTURES_SIGHT"	=>	"standart",
					"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
					"GET_GALLERY_INFO" => "Y",
					"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
					"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
					"SET_STATUS_404" => "N",
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"SET_TITLE" => "N",
					"THUMBS_SIZE" => $arParams["THUMBS_SIZE"],
					"SHOW_PAGE_NAVIGATION"	=>	"none",
					"SQUARE" => "Y",
					"PERCENT" => $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]
				),
				$component
			);?>
			<div class="all-elements"><noindex><a rel="nofollow" href="<?=($sDetailListUrl."&order=date_create")?>"><?=GetMessage("P_PHOTO_NEW_ALL")?></a></noindex></div>
<?
$new = ob_get_clean();
$arFields = array(
	array(
		"HEADER" => array(
			"TITLE" => GetMessage("P_PHOTO_NEW"),
			"LINK" => ""),
		"BODY" => $new,
		"ACTIVE" => "Y"),
	array(
		"HEADER" => array(
			"TITLE" => GetMessage("P_PHOTO_POPULAR"),
			"LINK" => $sDetailListUrl."&order=shows&group_photo=Y"),
		"BODY" => "",
		"AJAX_USE" => "Y"));

if ($arParams["USE_COMMENTS"] == "Y"):
	$arFields[] = array(
			"HEADER" => array(
				"TITLE" => GetMessage("P_PHOTO_COMMENT"),
				"LINK" => $sDetailListUrl."&order=comments&group_photo=Y"),
			"BODY" => "",
			"AJAX_USE" => "Y");
endif;

?>
	<div id="photo-main-new">
		<?$APPLICATION->IncludeComponent("bitrix:photogallery.interface", "bookmark",
			Array("DATA" => $arFields),
			$component,
			array("HIDE_ICONS" => "Y"));?>
	</div>
</div>

<style>
div#photo-main-new div.photo-photos{
	height:<?=intVal($arParams["THUMBS_SIZE"] * $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]/100)?>px;}
div.photo-body-text-ajax{
	height:<?=intVal($arParams["THUMBS_SIZE"] * $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]/100 + 39)?>px;
	padding-top:<?=intVal($arParams["THUMBS_SIZE"] * $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]/200)?>px;
	text-align:center;}
div#photo-main-galleries div.photo-gallery-ascetic{
	height:<?=($arParams["GALLERY_AVATAR_SIZE"])?>px;
	}
</style>