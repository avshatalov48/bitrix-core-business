<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"search" => "PAGE_NAME=search",
		"detail_list" => "PAGE_NAME=detail_list",
		"galleries" => "PAGE_NAME=galleries&USER_ID=#USER_ID#",
		"upload" => "PAGE_NAME=upload&SECTION_ID=#SECTION_ID#&ACTION=upload");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITTIONAL ************************************/
	$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
	$arParams["SHOW_BEST_ELEMENT"] = ($arParams["SHOW_BEST_ELEMENT"] == "N" ? "N" : "Y");
	$arParams["PERMISSION"] = trim($arParams["PERMISSION"]);

	$arParams["SHOW_ONLY_PUBLIC"] = ($arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "Y" : "N");
	$arParams["MODERATE"] = ($arParams["MODERATE"] == "Y" && $arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "Y" : "N");

	$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" ? "Y" : "N");
	$arParams["USE_COMMENTS"] = ($arParams["USE_COMMENTS"] == "Y" ? "Y" : "N");
	$arParams["COMMENTS_TYPE"] = ($arParams["COMMENTS_TYPE"] == "forum" ? "forum" : "blog");
	$arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"] = ($arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"] <= 0 ? 50 : $arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"]);
	$arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"] = 70;
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] != "N")
{
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("P_TITLE"));
}
/********************************************************************
				/Standart
********************************************************************/

if (is_array($arResult["MENU_VARIABLES"]) && isset($arResult["MENU_VARIABLES"]["ALL"]))
	$arResult = $arResult + $arResult["MENU_VARIABLES"]["ALL"];

ob_start();
?><?$arResultGalleries = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.gallery.list",
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
		"PAGE_ELEMENTS"	=>	10,
		"PAGE_NAVIGATION_TEMPLATE"	=>	$arParams["PAGE_NAVIGATION_TEMPLATE"],
		"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT_SECTION"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"SECTION_FILTER" => array(">ELEMENTS_CNT" => 0),
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"SET_STATUS_404" => "N",

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"],
		"SET_TITLE" => "N",

		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none"
	),
	$component,
	array("HIDE_ICONS" => "Y"));?><?
	$sGalleryList = ob_get_clean();

if (empty($sGalleryList))
{
	$sError = GetMessage("P_ERROR1")." ";
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		if (empty($arResult["MY_GALLERY"]) && $arResult["I"]["ACTIONS"]["CREATE_GALLERY"] == "Y")
			$sError .= GetMessage("P_ERROR2");
		elseif (!empty($arResult["MY_GALLERY"]))
			$sError .= GetMessage("P_ERROR3");
	}
	$sError = str_replace(array("#CREATE_GALLERY#", "#UPLOAD#"), array($arResult["LINK"]["NEW"], $arResult["MY_GALLERY"]["LINK"]["UPLOAD"]),$sError);
?>
<div class="photo-note-box">
	<div class="photo-note-box-text">
		<?=$sError?>
	</div>
</div>
<?
return false;
}
$sDetailListUrl = CComponentEngine::MakePathFromTemplate($arParams["DETAIL_LIST_URL"], array());
$sDetailListUrl .= (strpos($sDetailListUrl, "?") === false ? "?" : "&");
$sBestPhoto = "";
$sBestPhotos = "";
$arError = array();
$res = "";

$sSortField = "ID";
$arFilter = array();
if ($arParams["PERMISSION"] < 'X')
	$arFilter["ACTIVE"] = "Y";

if ($arParams["SHOW_ONLY_PUBLIC"] == "Y")
{
	$arFilter["PROPERTY_APPROVE_ELEMENT"] = "Y";
	$arFilter["PROPERTY_PUBLIC_ELEMENT"] = "Y";
}

$arFilterBest = $arFilter;
if ($arParams["USE_RATING"] == "Y")
{
	$sSortField = "PROPERTY_RATING";
	$arFilterBest[">PROPERTY_RATING"] = "0";
}
elseif ($arParams["USE_COMMENTS"] == "Y")
{
	if ($arParams["COMMENTS_TYPE"] == "forum")
	{
		$arFilterBest[">PROPERTY_FORUM_MESSAGE_CNT"] = "0";
		$sSortField = "PROPERTY_FORUM_MESSAGE_CNT";
	}
	else
	{
		$arFilterBest[">PROPERTY_BLOG_COMMENTS_CNT"] = "0";
		$sSortField = "PROPERTY_BLOG_COMMENTS_CNT";
	}
}
else
{
	$sSortField = "shows";
}

$bEmptyBest = false;
if ($arParams["SHOW_BEST_ELEMENT"] == "Y" && $_REQUEST["return_array"] != "Y")
{
	if (!isset($_REQUEST['image_rotator']))
		ob_start();

	$APPLICATION->IncludeComponent("bitrix:photogallery.imagerotator",
		"",
		array(
			"WIDTH" => 300,
			"HEIGHT" => 300,
			"ROTATETIME" => 5,
			"BACKCOLOR" => "#000000",
			"FRONTCOLOR" => "#e8e8e8",
			"LIGHTCOLOR" => "#ffffff",
			// "SCREENCOLOR" => "#ffffff",
			//"LOGO" => '',
			"OVERSTRETCH" => "Y",
			"SHOWICONS" => "N",
			"SHOWNAVIGATION" => "Y",
			"TRANSITION" => "slowfade", //random|fade|bgfade|blocks|bubbles|circles|flash|fluids|lines|slowfade",
			"USEFULLSCREEN" => "N",
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"BEHAVIOUR" => "USER",
			"USER_ALIAS" => "",
			"PERMISSION" => "",
			"SECTION_ID" => 0,
			"SECTION_CODE" => "",
			"PAGE_ELEMENTS" => "20",
			"ELEMENT_SORT_FIELD"	=>	$arParams["USE_RATING"] != "N" ? 'PROPERTY_RATING' : 'shows',
			"ELEMENT_SORT_ORDER"	=>	"desc",
			"ELEMENT_FILTER" => $arFilter,
			"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
			"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			
			"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"]
		)
);
	if (!isset($_REQUEST['image_rotator']))
		$sBestPhoto = ob_get_clean();

	// if ($element_id > 0)
		// $arFilterBest["!ID"] = $element_id;
	// else
		// $bEmptyBest = true;
}
$element_id = false;

if ($bEmptyBest == true && (isset($arFilterBest[">PROPERTY_FORUM_MESSAGE_CNT"]) || isset($arFilterBest[">PROPERTY_BLOG_COMMENTS_CNT"])))
{
	unset($arFilterBest[">PROPERTY_FORUM_MESSAGE_CNT"]);
	unset($arFilterBest[">PROPERTY_BLOG_COMMENTS_CNT"]);
	$sSortField = "shows";
	$bEmptyBest = false;
}

if (!$bEmptyBest)
{
	if ($_REQUEST["return_array"] != "Y")
		ob_start();
	?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:photogallery.detail.list.ex",
		"",
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
			//"ELEMENT_SORT_FIELD1"	=>	$sSortField,
			//"ELEMENT_SORT_ORDER1"	=>	"desc",
			"ELEMENT_FILTER" => $arFilter,
			"ELEMENT_SELECT_FIELDS" => array(),
			"PROPERTY_CODE" => array(),
			"DRAG_SORT" => "N",

			"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
			"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
			"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
			"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
			"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
			"DETAIL_EDIT_URL" => $arResult["URL_TEMPLATES"]["detail_edit"],

			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],

			"USE_DESC_PAGE" => "N",
			"PAGE_ELEMENTS" => $arParams["INDEX_PAGE_TOP_ELEMENTS_COUNT"],
			"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

	 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],

			"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
			"PICTURES_SIGHT"	=>	"",
			"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],

			"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
			"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
			"MODERATION" => $arParams["MODERATION"],

			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SET_TITLE" => "N",
			"RETURN_FORMAT" => "LIST",

			"THUMBNAIL_SIZE"	=>	$arParams["THUMBNAIL_SIZE"],
			"SHOW_PAGE_NAVIGATION" => "none",
			"INCLUDE_SLIDER" => "Y",
			"USE_RATING" => $arParams["USE_RATING"],
			"MAX_VOTE" => $arParams["MAX_VOTE"],
			"VOTE_NAMES" => $arParams["VOTE_NAMES"],
			"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
			"USE_COMMENTS" => $arParams["USE_COMMENTS"],
			"~UNIQUE_COMPONENT_ID" => "bx_photo_users_index"
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);?>

	<?
	if ($_REQUEST["return_array"] != "Y")
		$sBestPhotos = ob_get_clean();
}
?>
<div class="photo-page-main">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="photo-table">
	<tr valign="top">
		<td class="photo-td-left">
<?
	if ($arParams["PERMISSION"] >= "W")
	{
		$arGalleries = unserialize(COption::GetOptionString("photogallery", "UF_GALLERY_SIZE"));
		$arGalleries = (is_array($arGalleries) ? $arGalleries : array());
		$arGallery = $arGalleries[$arParams["IBLOCK_ID"]];
		if (empty($arGallery))
		{
?>
	<div class="photo-note-box">
		<div class="photo-note-box-text">
			<?=str_replace("#URL#", $APPLICATION->GetCurPage()."?galleries_recalc=Y", GetMessage("P_RECALC_7"))?>
		</div>
	</div>
<?
		}
	}
?>
<?
	if (!empty($sBestPhotos))
	{
?>
			<div class="photo-header-component"><?=GetMessage("P_PHOTO_LAST")?></div>
			<?=$sBestPhotos?>
<?
	}
	else
	{
		if ($sSortField == "PROPERTY_RATING")
			$sBestPhotos = GetMessage("P_ERROR4");
		elseif ($sSortField != "shows")
			$sBestPhotos = GetMessage("P_ERROR5");

		if (!empty($sBestPhotos))
		{
?>
			<div class="photo-note-box">
				<div class="photo-note-box-text"><?=$sBestPhotos?></div>
			</div>
<?
		}
	}

?>
		</td>

		<td class="photo-td-right">
<?
$this->SetViewTarget("sidebar", 100);
if (!empty($sBestPhoto)):?>
<div class="photo-info-box photo-info-box-best-photo">
	<div class="photo-info-box-inner">
		<div class="photo-header-big">
			<div class="photo-header-inner">
<?
	if ($sSortField == "PROPERTY_RATING"):
		?><?=GetMessage("P_RATING_PHOTO")?><?
	elseif ($sSortField != "shows"):
		?><?=GetMessage("P_COMMENT_PHOTO")?><?
	else:
		?><?=GetMessage("P_PHOTO_2")?><?
	endif;
?>
			</div>
		</div>
		<?=$sBestPhoto?>
	</div>
</div>
<?endif;?>

<?
if ($arParams["PERMISSION"] >= "U")
{
	CModule::IncludeModule("iblock");
	$bNeedModerate =  false; $bNeedPublic = false;
	$arNavParams = array("nTopCount" => 1, "bDescPageNumbering" => "N");

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

	$db_res = CIBlockElement::GetList(array(), $arFilterApprove, false, $arNavParams, array("ID", "IBLOCK_ID", "ACTIVE"));
	if ($db_res && $res = $db_res->Fetch())
		$bNeedModerate =  true;

	if ($arParams["MODERATE"] == "Y")
	{
		$arFilterPublic = $arFilterModerate + array("PROPERTY_PUBLIC_ELEMENT" => "Y", "PROPERTY_APPROVE_ELEMENT" => "X");
		$db_res = CIBlockElement::GetList(array(), $arFilterPublic, false, $arNavParams, array("ID"));
		if ($db_res && $res = $db_res->Fetch())
			$bNeedPublic = true;
	}

	if (($bNeedModerate || $bNeedPublic) && $arParams['SHOW_CONTROLS_BUTTONS'] != "N")
	{
?>
<noindex>
	<div class="photo-controls photo-controls-buttons photo-controls-moderate">
		<ul class="photo-controls">
		<?if ($bNeedModerate):?>
			<li class="photo-control photo-control-first <?=($bNeedPublic ? "" : "photo-control-last")?> photo-control-moderate">
				<a rel="nofollow" href="<?=$sDetailListUrl."&mode=active"?>" title="<?=GetMessage("P_NOT_MODERATED_TITLE")?>">
					<span><?=GetMessage("P_NOT_MODERATED")?></span>
				</a>
			</li>
		<?endif;?>
		<?if ($bNeedPublic):?>
			<li class="photo-control <?=($bNeedModerate ? "" : "photo-control-first")?> photo-control-last photo-control-public">
				<a rel="nofollow" href="<?=$sDetailListUrl."&mode=public"?>" title="<?=GetMessage("P_NOT_APPROVED_TITLE")?>">
					<span><?=GetMessage("P_NOT_APPROVED")?></span>
				</a>
			</li>
		<?endif;?>
		</ul>
		<div class="empty-clear"></div>
	</div>
</noindex>
<?
	}
}

if ($GLOBALS["USER"]->IsAuthorized() && (!empty($arResult["MY_GALLERY"]) || $arResult["I"]["ACTIONS"]["CREATE_GALLERY"] == "Y") && $arParams['SHOW_CONTROLS_BUTTONS'] != "N")
{
?>
<noindex>
	<div class="photo-controls photo-controls-buttons photo-controls-usermenu">
		<ul class="photo-controls">
<?if (empty($arResult["MY_GALLERY"])):?>
			<li class="photo-control photo-control-first photo-control-last photo-control-create photo-control-create-gallery-first">
				<a rel="nofollow" href="<?=$arResult["LINK"]["NEW"]?>">
					<span><?=GetMessage("P_GALLERY_CREATE")?></span></a>
			</li>
<?else:?>
			<li class="photo-control photo-control-first photo-control-view photo-control-gallery">
				<a rel="nofollow" href="<?=$arResult["MY_GALLERY"]["LINK"]["VIEW"]?>">
					<span><?=GetMessage("P_PHOTO_VIEW")?></span></a>
			</li>
<?if (count($arResult["MY_GALLERIES"]) > 1 || $arResult["I"]["ACTIONS"]["CREATE_GALLERY"] == "Y"):?>
			<li class="photo-control photo-control-view photo-control-galleries">
				<a rel="nofollow" href="<?=$arResult["LINK"]["GALLERIES"]?>">
					<span><?=GetMessage("P_GALLERIES_VIEW")?></span></a>
			</li>
<?else:?>
			<li class="photo-control photo-control-edit photo-control-gallery-edit">
				<a rel="nofollow" href="<?=$arResult["MY_GALLERY"]["LINK"]["EDIT"]?>">
					<span><?=GetMessage("P_GALLERY_VIEW")?></span></a>
			</li>
<?endif;?>
			<li class="photo-control photo-control-last photo-control-album-upload">
				<a rel="nofollow" href="<?=$arResult["MY_GALLERY"]["LINK"]["UPLOAD"]?>"><span><?=GetMessage("P_UPLOAD")?></span></a>
			</li>
<?endif;?>
		</ul>
		<div class="empty-clear"></div>
	</div>
</noindex>
<?
}
elseif (!$GLOBALS["USER"]->IsAuthorized() && $arParams['SHOW_CONTROLS_BUTTONS'] != "N")
{
?>
<noindex>
	<div class="photo-controls photo-controls-buttons photo-controls-authorize">
		<ul class="photo-controls">
			<li class="photo-control photo-control-first photo-control-last photo-control-authorize">
				<a rel="nofollow" href="<?=htmlspecialcharsbx($APPLICATION->GetCurPageParam("auth=yes&backurl=".$arResult["backurl_encode"],
			array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)))?>"><span><?=GetMessage("P_LOGIN")?></span></a>
			</li>
		</ul>
		<div class="empty-clear"></div>
	</div>
</noindex>
<?
}
?>

<noindex>
<div class="photo-info-box photo-info-box-photo-list">
	<div class="photo-info-box-inner">
		<div class="photo-header-big">
			<div class="photo-header-inner">
				<?=GetMessage("P_PHOTOGRAPHIES")?>
			</div>
		</div>
		<div class="photo-controls photo-controls-mainpage">
			<ul class="photo-controls">
				<li class="photo-control"><a rel="nofollow" href="<?=$sDetailListUrl?>"><span><?=GetMessage("P_PHOTO_NEW")?></span></a></li>
				<li class="photo-control"><a rel="nofollow" href="<?=$sDetailListUrl?>order=shows&group_photo=Y"><span><?=GetMessage("P_PHOTO_SHOWS")?></span></a></li>
<?
		if ($arParams["USE_RATING"] == "Y")
		{
?>
				<li class="photo-control"><a rel="nofollow" href="<?=$sDetailListUrl?>order=rating&group_photo=Y"><span><?=GetMessage("P_PHOTO_RATING")?></span></a></li>
<?
		}
		if ($arParams["USE_COMMENTS"] == "Y")
		{
?>
				<li class="photo-control"><a rel="nofollow" href="<?=$sDetailListUrl?>order=comments&group_photo=Y"><span><?=GetMessage("P_PHOTO_COMMENTS")?></span></a></li>
<?
		}
?>
			</ul>
			<div class="empty-clear"></div>
		</div>
	</div>
</div>
</noindex>
<?
if (!empty($sGalleryList))
{
?>
<div class="photo-info-box photo-info-box-galleries">
	<div class="photo-info-box-inner">
		<div class="photo-header-big">
			<div class="photo-header-inner">
				<?=GetMessage("P_USERS_GALLERIES")?>
<?if (count($arResultGalleries["GALLERIES"]) >= 10):?>
				<span class="photo-header-link"> (<a href="<?=CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => "users"))?>"><?=GetMessage("P_ALL_PHOTOS")?></a>)</span>
<?endif;?>
			</div>
		</div>
		<div class="photo-controls photo-controls-mainpage">
			<?=$sGalleryList?>
			<div class="empty-clear"></div>
		</div>
	</div>
</div>
<?
}

if ($arParams["SHOW_TAGS"] == "Y" && IsModuleInstalled("search"))
{
	$APPLICATION->IncludeComponent(
		"bitrix:search.tags.cloud",
		"photogallery",
		Array(
			"SEARCH" => "",
			"TAGS" => "",

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
		),
		$component,
		array("HIDE_ICONS" => "Y"));
}
$this->EndViewTarget();
?>
		</td>

	</tr>
</table>
</div>