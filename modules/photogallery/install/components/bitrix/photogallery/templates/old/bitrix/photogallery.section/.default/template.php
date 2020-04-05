<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

IncludeAJAX();

$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/photogallery/templates/.default/script.js"></script>', true);
if ($arParams["PERMISSION"] >= "W")
{
	$GLOBALS['APPLICATION']->IncludeComponent("bitrix:main.calendar", "", array("SILENT" => "Y"), $component, array("HIDE_ICONS" => "Y"));
}
?>
<div class="photo-controls photo-action">
	<noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["BACK_LINK"]?>" <?
		?>title="<?=GetMessage("P_UP_TITLE")?>"  class="photo-action back-to-album"><?=GetMessage("P_UP")?></a></noindex>
<?
if (!empty($arResult["SECTION"]["NEW_LINK"])):
	?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["NEW_LINK"]?>" title="<?=GetMessage("P_ADD_ALBUM_TITLE")?>"  class="photo-action new-album" <?
	?>onclick="EditAlbum('<?=CUtil::JSEscape($arResult["SECTION"]["~NEW_LINK"])?>'); return false;"<?
	?>><?=GetMessage("P_ADD_ALBUM")?></a></noindex><?
endif;
if (!empty($arResult["SECTION"]["UPLOAD_LINK"])):
	?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["UPLOAD_LINK"]?>" class="photo-action photo-upload"><?=GetMessage("P_UPLOAD")?></a></noindex><?
endif;
?>
	<div class="empty-clear"></div>
</div>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="photo-album">
	<tr><td width="1%">
		<div class="photo-album-img">
			<table cellpadding="0" cellspacing="0" class="shadow">
				<tr class="t"><td colspan="2" rowspan="2">
					<div class="outer" style="width:<?=($arParams["ALBUM_PHOTO_SIZE"] + 38)?>px;">
						<div class="tool" style="height:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;"></div>
						<div class="inner">
						<div class="photo-album-cover" id="photo_album_cover_<?=$arResult["SECTION"]["ID"]?>" <?
							?>style="width:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px; <?
							?>height:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;<?
						if (!empty($arResult["SECTION"]["DETAIL_PICTURE"]["SRC"])):
							?>background-image:url('<?=$arResult["SECTION"]["DETAIL_PICTURE"]["SRC"]?>');<?
						endif;
							?>" title="<?=htmlspecialcharsbx($arResult["SECTION"]["~NAME"])?>"></div>
						</div>
					</div>
				</td><td class="t-r"><div class="empty"></div></td></tr>
				<tr class="m"><td class="m-r"><div class="empty"></div></td></tr>
				<tr class="b">
				<td class="b-l"><div class="empty"></div></td>
				<td class="b-c"><div class="empty"></div></td>
				<td class="b-r"><div class="empty"></div></td></tr>
			</table>
		</div>
	</td>
	<td>
		<div class="photo-album-info">
			<div class="password" id="photo_album_password_<?=$arResult["SECTION"]["ID"]?>" title="<?=GetMessage("P_PASSWORD")?>" <?
				if (empty($arResult["SECTION"]["PASSWORD"])):
					?>style="display:none;"<?
				endif;
			?>></div>
			<div class="name<?=($arResult["SECTION"]["ACTIVE"] != "Y" ? " nonactive" : "")?>" id="photo_album_name_<?=$arResult["SECTION"]["ID"]?>"><?
				?><?=$arResult["SECTION"]["NAME"]?></div>
<?

	if (!empty($arResult["SECTION"]["PASSWORD"]) && !$arParams["PASSWORD_CHECKED"]):
?>
			<div class="password-title" style="position:relative;"  title="<?=GetMessage("P_PASSWORD_TITLE")?>">
				<form name="photogallery" method="post" action="<?=POST_FORM_ACTION_URI?>" style="display:none; position:absolute;" <?
					?>id="password_form_<?=$arResult["SECTION"]["ID"]?>" class="photo-form">
					<?=bitrix_sessid_post()?>
					<input type="password" name="password_<?=$arResult["SECTION"]["ID"]?>" id="password_<?=$arResult["SECTION"]["ID"]?>" value="" />
					<input type="submit" name="supply_password" value="<?=GetMessage("P_SUPPLY_PASSWORD")?>">
				</form><?
				?><a href="#" onclick="var tt=this.previousSibling; tt.style.display=''; tt.elements[1].focus(); return false;"><?=GetMessage("P_PASSWORD")?></a>
			</div>
<?
	endif;

?>
			<div class="description" id="photo_album_description_<?=$arResult["SECTION"]["ID"]?>"><?=$arResult["SECTION"]["DESCRIPTION"]?></div>
			<div class="date" id="photo_album_date_<?=$arResult["SECTION"]["ID"]?>">
				<?$APPLICATION->IncludeComponent(
					"bitrix:system.field.view",
					$arResult["SECTION"]["~DATE"]["USER_TYPE"]["USER_TYPE_ID"],
					array("arUserField" => $arResult["SECTION"]["DATE"]), null, array("HIDE_ICONS"=>"Y"));?>
			</div>
			<div class="photos"><?=GetMessage("P_PHOTOS_CNT")?>: <?=$arResult["SECTION"]["ELEMENTS_CNT"]?></div>
<?

		if ($arParams["PERMISSION"] >= "U" && intVal($arResult["SECTIONS_CNT"]) > 0):
?>
			<div class="photo-album-cnt-album"><?=GetMessage("P_ALBUMS_CNT")?>: <?=$arResult["SECTIONS_CNT"]?></div>
<?
		endif;

?>
			<div class="photo-controls photo-album-controls">
<?

		if (!empty($arResult["SECTION"]["EDIT_LINK"])):
			?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["EDIT_LINK"]?>" class="photo-action album-edit" <?
				?> onclick="EditAlbum('<?=CUtil::JSEscape($arResult["SECTION"]["EDIT_LINK"])?>'); return false;"><?
				?><?=GetMessage("P_SECTION_EDIT")?></a></noindex><?
		endif;

		if (!empty($arResult["SECTION"]["EDIT_ICON_LINK"])):
			?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["EDIT_ICON_LINK"]?>" class="photo-action album-edit-icon" <?
				?>onclick="EditAlbum('<?=CUtil::JSEscape($arResult["SECTION"]["EDIT_ICON_LINK"])?>'); return false;"><?
				?><?=GetMessage("P_EDIT_ICON")?></a></noindex><?
		endif;

		if (!empty($arResult["SECTION"]["DROP_LINK"])):
			?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["DROP_LINK"]?>" class="photo-action album-delete" <?
				?>onclick="return confirm('<?=GetMessage('P_SECTION_DELETE_ASK')?>');"><?
				?><?=GetMessage("P_SECTION_DELETE")?></a></noindex><?
		endif;

		if ($arResult["SECTION"]["ELEMENTS_CNT"] > 0):
			?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["SLIDE_SHOW_LINK"]?>" <?
				?>class="photo-view slide-show"><?=GetMessage("P_SLIDE_SHOW")?></a></noindex><?
		endif;
		?>
			</div>
		</div>
	</td></tr>
</table>
