<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
$arParams["ALBUM_PHOTO_SIZE"] = intval($arParams["ALBUM_PHOTO_SIZE"]);

/********************************************************************
				/Input params
********************************************************************/

// TODO: get rid of this
CAjax::Init();
// TODO: get rid of this too
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');

$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/components/bitrix/photogallery/templates/.default/script.js');
if (!$this->__component->__parent || mb_strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
?>
<style>
.photo-album-list div.photo-item-cover-block-container,
.photo-album-list div.photo-item-cover-block-outer,
.photo-album-list div.photo-item-cover-block-inner{
	background-color: white;
	height:<?=($arParams["ALBUM_PHOTO_SIZE"] + 16)?>px;
	width:<?=($arParams["ALBUM_PHOTO_SIZE"] + 40)?>px;}
div.photo-album-avatar{
	width:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;
	height:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;}
ul.photo-album-list div.photo-item-info-block-outside {
	width: <?=($arParams["ALBUM_PHOTO_SIZE"] + 48)?>px;}
</style>
<?
endif;
?>

<?if (empty($arResult["SECTIONS"])):?>
<div class="photo-info-box photo-info-box-sections-list-empty">
	<div class="photo-info-box-inner"><?=GetMessage("P_EMPTY_DATA")?></div>
</div>
<?
return false;
endif;?>

<ul class="photo-items-list photo-album-list<?if($arParams['PHOTO_LIST_MODE'] == "Y"){echo " photo-album-list-first-photos";}?>">
	<?foreach($arResult["SECTIONS"] as $res):?>
	<li class="photo-album-item photo-album-<?=($res["ACTIVE"] != "Y" ? "nonactive" : "active")?> <?=(
		!empty($res["PASSWORD"]) ? "photo-album-password" : "")?>" id="photo_album_info_<?=$res["ID"]?>" <?
		if ($res["ACTIVE"] != "Y" || !empty($res["PASSWORD"]))
		{
			$sTitle = GetMessage("P_ALBUM_IS_NOT_ACTIVE");
			if ($res["ACTIVE"] != "Y" && !empty($res["PASSWORD"]))
				$sTitle = GetMessage("P_ALBUM_IS_NOT_ACTIVE_AND_PASSWORDED");
			elseif (!empty($res["PASSWORD"]))
				$sTitle = GetMessage("P_ALBUM_IS_PASSWORDED");
			?> title="<?=$sTitle?>" <?
		}
		?>>

	<?if($arParams['PHOTO_LIST_MODE'] == "Y"):?>
		<?if ($res["ACTIVE"] != "Y" || !empty($res["PASSWORD"]))
		{
			$sTitle = GetMessage("P_ALBUM_IS_NOT_ACTIVE");
			if ($res["ACTIVE"] != "Y" && !empty($res["PASSWORD"]))
				$sTitle = GetMessage("P_ALBUM_IS_NOT_ACTIVE_AND_PASSWORDED");
			elseif (!empty($res["PASSWORD"]))
				$sTitle = GetMessage("P_ALBUM_IS_PASSWORDED");
			$sTitle = ' - '.$sTitle;
		}?>
		<div>
			<div class="album-top-section">
				<a class="album-name" href="<?=$res["LINK"]?>" title="<?= $res["NAME"].$sTitle?>"><?= $res["NAME"]?></a>
				<?if (!empty($res["PASSWORD"])):?>
					<span class="album-passworded">(<?= GetMessage("P_ALBUM_IS_PASSWORDED_SHORT")?>)</span>
				<?endif;?>
				<? if($res["DATE"]):?>
				<span class="album-date"><?= $res["DATE"]?></span>
				<?endif;?>
				<? if($res["ELEMENTS_CNT"] > 0):?>
				<span class="album-photos">(<a class="more-photos" href="<?=$res["LINK"]?>" title="<?= GetMessage('P_OTHER_PHOTOS_TITLE')?>"><?= $res["ELEMENTS_CNT"]." ".GetMessage('P_SECT_PHOTOS')?></a>)</span>
				<?endif;?>
			</div>
			<?if ($arParams["PERMISSION"] >= "W"):?>
				<div class="album-list-action-cont">
					<a rel="nofollow" href="<?=$res["EDIT_LINK"]?>" class="photo-control-edit photo-control-album-edit" title="<?=GetMessage("P_SECTION_EDIT_TITLE")?>"><?=GetMessage("P_SECTION_EDIT")?></a>
					<a rel="nofollow" href="<?= $res["DROP_LINK"]."&".bitrix_sessid_get()?>" class="photo-control-drop photo-control-album-drop" onclick="if (confirm('<?=GetMessage('P_SECTION_DELETE_ASK')?>')) {DropAlbum(this.href, parseInt('<?=$res["ID"]?>'));} return BX.PreventDefault(arguments[0]);" title="<?= GetMessage("P_SECTION_DELETE_TITLE")?>"><span><?=GetMessage("P_SECTION_DELETE")?></span></a>
				</div>
			<?endif;?>

			<div class="album-photos-section">
				<? if($res["ELEMENTS_CNT"] > 0):?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list.ex",
	"",
	Array(
		"BEHAVIOUR" => (isset($arParams["BEHAVIOUR"]) && $arParams["BEHAVIOUR"] != "") ? $arParams["BEHAVIOUR"] : "SIMPLE",
		"USER_ALIAS" => $arParams["USER_ALIAS"],

		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $res["ID"],
		"DRAG_SORT" => "N",
		"MORE_PHOTO_NAV" => "N",
		"THUMBNAIL_SIZE" => $arParams["SECTION_LIST_THUMBNAIL_SIZE"],
		"SHOW_CONTROLS" => "Y",
		"SHOW_RATING" => "Y",
		"SHOW_SHOWS" => "N",
		"SHOW_COMMENTS" => "Y",
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => array(),
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
		"SET_TITLE" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_NOTES" => "",
		"ELEMENT_LAST_TYPE" => "none",
		"ELEMENT_FILTER" => array("INCLUDE_SUBSECTIONS" => "Y"),
		"RELOAD_ITEMS_ONLOAD" => "Y",
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => $arParams["ELEMENT_SORT_FIELD1"],
		"ELEMENT_SORT_ORDER1" => $arParams["ELEMENT_SORT_ORDER1"],
		"PROPERTY_CODE" => array(),
		"INDEX_URL" => $arParams["~INDEX_URL"],
		"DETAIL_URL" => $arParams["~DETAIL_URL"],
		"GALLERY_URL" => $arParams["~GALLERY_URL"],
		"SECTION_URL" => $arParams["~SECTION_URL"],
		"DETAIL_EDIT_URL" => $arParams["~DETAIL_EDIT_URL"],
		"PERMISSION" => $arParams["PERMISSION"],
		"GROUP_PERMISSIONS" => array(),
		"PAGE_ELEMENTS" => $arParams["SHOWN_ITEMS_COUNT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"SET_STATUS_404" => "N",
		"ADDITIONAL_SIGHTS" => array(),
		"PICTURES_SIGHT" => "real",
		"USE_COMMENTS" => $arParams["USE_COMMENTS"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		"FORUM_ID" => $arParams["FORUM_ID"],
		"USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
		"POST_FIRST_MESSAGE" => $arParams["POST_FIRST_MESSAGE"] == "N" ? "N" : "Y",
		"SHOW_LINK_TO_FORUM" => "N",
		"BLOG_URL" => $arParams["~BLOG_URL"],
		"PATH_TO_BLOG" => $arParams["~PATH_TO_BLOG"],
		// Display user
		"PATH_TO_USER" => $arParams["~PATH_TO_USER"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"~UNIQUE_COMPONENT_ID" => "bxfg_ucid_from_req_".$arParams["IBLOCK_ID"]."_".$res["ID"],
		"ACTION_URL" => $res["~LINK"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
				<?else:?>
					<span class="album-no-photos"><?= GetMessage('P_NO_PHOTOS')?></span>
				<?endif;?>
			</div>
			<div class="album-separator-line"></div>
		</div>
	<?else:?>
		<div class="photo-item-cover-block-outside">
			<div class="photo-item-cover-block-container">
				<div class="photo-item-cover-block-outer">
					<div class="photo-item-cover-block-inner">
						<div class="photo-item-cover-block-inside">
							<div class="photo-item-cover photo-album-avatar <?=(empty($res["DETAIL_PICTURE"]["SRC"])? "photo-album-avatar-empty" : "")?>" id="photo_album_cover_<?=$res["ID"]?>" title="<?= htmlspecialcharsbx($res["~NAME"])?>"
								<?if (!empty($res["DETAIL_PICTURE"]["SRC"])):?>
									style="background-image:url('<?=$res["DETAIL_PICTURE"]["SRC"]?>');"
								<?endif;?>
								<?if ($arParams["PERMISSION"] >= "W"):?>
									onmouseover="BX.addClass(this, 'photo-album-avatar-edit');"
								<?else:?>
									onclick="window.location='<?=CUtil::JSEscape(htmlspecialcharsbx($res["~LINK"]))?>';"
								<?endif;?>
								>
								<?if ($arParams["PERMISSION"] >= "W"):?>
								<div class="photo-album-menu" onmouseout="BX.removeClass(this.parentNode, 'photo-album-avatar-edit')" onclick="window.location='<?=CUtil::JSEscape(htmlspecialcharsbx($res["~LINK"]))?>';">
									<div class="photo-album-menu-substrate"></div>
										<div class="photo-album-menu-controls">
										<a rel="nofollow" href="<?=$res["EDIT_LINK"]?>" class="photo-control-edit photo-control-album-edit" title="<?=GetMessage("P_SECTION_EDIT_TITLE")?>"><span><?=GetMessage("P_SECTION_EDIT")?></span></a>
										<a rel="nofollow" href="<?= $res["DROP_LINK"]."&".bitrix_sessid_get()?>" class="photo-control-drop photo-control-album-drop" onclick="if (confirm('<?=GetMessage('P_SECTION_DELETE_ASK')?>')) {DropAlbum(this.href, parseInt('<?=$res["ID"]?>'));} return BX.PreventDefault(arguments[0]);" title="<?= GetMessage("P_SECTION_DELETE_TITLE")?>"><span><?=GetMessage("P_SECTION_DELETE")?></span></a>
									</div>
								</div>
								<?endif;?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="photo-item-info-block-outside">
			<div class="photo-item-info-block-container">
				<div class="photo-item-info-block-outer">
					<div class="photo-item-info-block-inner">
						<div class="photo-album-photos-top"><?=$res["ELEMENTS_CNT"]?> <?=GetMessage("P_SECT_PHOTOS")?></div>
						<div class="photo-album-name">
							<a href="<?=$res["LINK"]?>" id="photo_album_name_<?=$res["ID"]?>" title="<?=htmlspecialcharsbx($res["~NAME"])?>" onmouseover="__photo_check_name_length(event, this);"><?=$res["NAME"]?></a>
						</div>
						<div class="photo-album-description" id="photo_album_description_<?=$res["ID"]?>"><?=$res["DESCRIPTION"]?></div>
						<div class="photo-album-date"><span id="photo_album_date_<?=$res["ID"]?>"><?=$res["DATE"]?></span></div>
						<div class="photo-album-photos"><?=$res["ELEMENTS_CNT"]?> <?=GetMessage("P_SECT_PHOTOS")?></div>
					</div>
				</div>
			</div>
		</div>
	<?endif;?>
	</li>
<?endforeach;?>
</ul>
<div class="empty-clear"></div>

<?
if (!empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>