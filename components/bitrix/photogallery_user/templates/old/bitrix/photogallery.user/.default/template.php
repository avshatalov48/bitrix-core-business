<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

IncludeAJAX();
$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ?  $arParams["GALLERY_AVATAR_SIZE"] : 50);
/* MY TOP PANEL */
if ($GLOBALS["USER"]->IsAuthorized() && (!empty($arResult["MY_GALLERY"]) || $arResult["I"]["ACTIONS"]["CREATE_GALLERY"] == "Y")):
?>
<div class="photo-user photo-user-my">
	<div class="photo-controls photo-action">
	<noindex>
	<?
	if (empty($arResult["MY_GALLERY"])):
		?><a rel="nofollow" href="<?=$arResult["LINK"]["NEW"]?>" title="<?=GetMessage("P_GALLERY_CREATE_TITLE")?>" <?
			?>class="photo-action gallery-create-first"><?=GetMessage("P_GALLERY_CREATE")?></a><?
	else:
		if ($arParams["PAGE_NAME"] != "INDEX"):
		?><a rel="nofollow" href="<?=$arResult["LINK"]["INDEX"]?>" title="<?=GetMessage("P_GALLERY_TITLE")?>" <?
			?>class="photo-action back-to-album"><?=GetMessage("P_GALLERY")?></a><?
		endif;
		?><a rel="nofollow" href="<?=$arResult["MY_GALLERY"]["LINK"]["VIEW"]?>" title="<?=GetMessage("P_PHOTO_VIEW_TITLE")?>" <?
			?>class="photo-action gallery-view"><?=GetMessage("P_PHOTO_VIEW")?></a> <?

		if (count($arResult["MY_GALLERIES"]) > 1 || $arResult["I"]["ACTIONS"]["CREATE_GALLERY"] == "Y"):
		?><a rel="nofollow" href="<?=$arResult["LINK"]["GALLERIES"]?>" title="<?=GetMessage("P_GALLERIES_VIEW_TITLE")?>" <?
			?>class="photo-action gallery-view-list"><?=GetMessage("P_GALLERIES_VIEW")?></a> <?
		else:
		?><a rel="nofollow" href="<?=$arResult["MY_GALLERY"]["LINK"]["EDIT"]?>" title="<?=GetMessage("P_GALLERY_VIEW_TITLE")?>" <?
			?>class="photo-action gallery-edit"><?=GetMessage("P_GALLERY_VIEW")?></a> <?
		endif;
		?><a rel="nofollow" href="<?=$arResult["MY_GALLERY"]["LINK"]["UPLOAD"]?>" class="photo-action photo-upload"><?=GetMessage("P_UPLOAD")?></a><?
	endif;
	?></div>
	<div class="empty-clear"></div>
</div>
<?
elseif (!$GLOBALS["USER"]->IsAuthorized()):
?>
<div class="photo-controls photo-action">
	<noindex><a rel="nofollow" href="<?=htmlspecialcharsbx($APPLICATION->GetCurPageParam("auth=yes&backurl=".$arResult["backurl_encode"],
		array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
		?>" class="photo-action authorize" title="<?=GetMessage("P_LOGIN_TITLE")?>"><?=GetMessage("P_LOGIN")?></a></noindex>
	<div class="empty-clear"></div>
</div>
<?
endif;

if (!empty($arResult["GALLERY"])):
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="gallery-table-header"><tr>
	<td width="0%" class="picture"><div class="photo-gallery-avatar" <?
		?>style="width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px; height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;<?
	if (!empty($arResult["GALLERY"]["PICTURE"])):
			?>background-image:url('<?=$arResult["GALLERY"]["PICTURE"]["SRC"]?>');<?
	endif;
		?>"></div>
	</td>
	<td width="<?=($arParams["GALLERY_SIZE"] > 0 ? "70" : "100")?>%" align="left" class="data">
		<div class="photo-gallery-name"><?=$arResult["GALLERY"]["NAME"]?></div><?
	if (!empty($arResult["GALLERY"]["DESCRIPTION"])):?>
		<div class="photo-gallery-description"><?=$arResult["GALLERY"]["DESCRIPTION"]?></div><?
	endif;

	if ($arParams["GALLERY_SIZE"] > 0):?>
	</td><td align="right" class="size">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="gallery-size"><tr><td align="right">
			<div class="out"><div class="in" id="photo_gallery_size_inner" style="width:<?=$arResult["GALLERY"]["UF_GALLERY_SIZE_PERCENT"]?>%">&nbsp;</div></div>
			<div class="out1"><div class="in1" id="photo_gallery_size_inner1"><?=GetMessage("P_GALLERY_SIZE")." ".$arResult["GALLERY"]["UF_GALLERY_SIZE_PERCENT"]?>%</div></div>
		</td></tr></table><?

		if ($arParams["PERMISSION"] >= "W" && $arResult["GALLERY"]["ELEMENTS_CNT"] > 0):
			if ($arResult["GALLERY"]["RECALC_INFO"]["STATUS"] == "CONTINUE"):
				$res = intVal(intVal($arResult["GALLERY"]["RECALC_INFO"]["FILE_COUNT"])/$arResult["GALLERY"]["ELEMENTS_CNT"]*100);
		?><table width="100%" cellpadding="0" cellspacing="0" border="0" class="gallery-progress"><tr><td align="right">
			<div class="out"><div class="in" style="width:<?=$res?>%">&nbsp;</div></div>
			<div class="out1"><div class="in1"><?=GetMessage("P_GALLERY_SIZE_RECOUNT")." ".$res?>%</div></div>
		</td></tr></table>
		<noindex>
			<a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("action=recalc", array("action", "status", "AJAX_CALL"))?>" <?
				?>class="gallery-recalc-begin" ><?=GetMessage("P_GALLERY_SIZE_RECALC_NEW")?></a>
			<a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("action=recalc&status=continue", array("action", "status", "AJAX_CALL"))
			?>" class="gallery-recalc-continue" ><?=GetMessage("P_GALLERY_SIZE_RECALC_CONTINUE")?></a>
		</noindex><?

			else:?>
			<div id="photo_progress_outer" style="display:none;">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="gallery-progress"><tr><td align="right">
				<div class="out"><div class="in" id="photo_progress_inner">&nbsp;</div></div>
				<div class="out1"><div class="in1" id="photo_progress_inner1"><?=GetMessage("P_GALLERY_SIZE_RECOUNT")?> 00%</div></div>
			</td></tr></table></div>
			<noindex><a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("action=recalc", array("action", "status", "AJAX_CALL"))
			?>" onclick="oGallery.Start(this); return false;" class="gallery-recalc-begin" ><?=GetMessage("P_GALLERY_SIZE_RECALC")?></a></noindex><?
			endif;
		endif;
	endif;?>
	</td>
</tr></table>
<?
endif;
?>