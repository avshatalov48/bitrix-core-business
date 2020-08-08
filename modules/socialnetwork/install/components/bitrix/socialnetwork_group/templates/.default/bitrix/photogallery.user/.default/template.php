<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeAJAX();
return;
$bIsMine = ($arResult["GALLERY"]["CREATED_BY"] == $USER->GetID() && $USER->IsAuthorized());
$arParams["GALLERY_AVATAR_SIZE"] = intval(intval($arParams["GALLERY_AVATAR_SIZE"]) > 0 ?  $arParams["GALLERY_AVATAR_SIZE"] : 50);
/* MY TOP PANEL */
if ($GLOBALS["USER"]->IsAuthorized() && !empty($arResult["GALLERY"])):
?><div class="photo-user photo-user-my"><?
	?><div class="photo-controls photo-action">
	<a href="<?=($bIsMine ? $arResult["MY_GALLERY"]["LINK"]["VIEW"] : $arResult["GALLERY"]["LINK"]["VIEW"])?>" class="photo-action gallery-view" title="<?=
		($bIsMine ? GetMessage("P_PHOTO_MY_TITLE") : GetMessage("P_PHOTO_TITLE"))?>"><?=
		($bIsMine ? GetMessage("P_PHOTO_MY") : GetMessage("P_PHOTO"))?>
	</a> 
	<?
if ($arParams["PERMISSION"] >= "W"):
	if ($bIsMine && (count($arResult["MY_GALLERIES"]) > 1 || $arResult["I"]["ACTIONS"]["CREATE_GALLERY"] == "Y")):
	?>
	<a href="<?=$arResult["LINK"]["GALLERIES"]?>"  class="photo-action gallery-view-list" title="<?=GetMessage("P_GALLERIES_MY_TITLE")?>"><?=
		GetMessage("P_GALLERIES_MY")?></a> 
	<?
	else:
	?>
	<a href="<?=$arResult["GALLERY"]["LINK"]["EDIT"]?>"  class="photo-action gallery-edit" title="<?=
		($bIsMine ? GetMessage("P_GALLERY_MY_TITLE") : GetMessage("P_GALLERY_TITLE"))?>"><?=
		($bIsMine ? GetMessage("P_GALLERY_MY") : GetMessage("P_GALLERY"))?>
	</a> 
	<?
	endif;
	?><a href="<?=($bIsMine ? $arResult["MY_GALLERY"]["LINK"]["UPLOAD"] : $arResult["GALLERY"]["LINK"]["UPLOAD"])?>" class="photo-action photo-upload"><?=GetMessage("P_UPLOAD")?></a><?
endif;	
	?></div>
</div>
<div class="empty-clear"></div>
<?

elseif (!$GLOBALS["USER"]->IsAuthorized()):
?><div class="photo-controls photo-action"><?
	?><a href="<?=htmlspecialcharsbx($APPLICATION->GetCurPageParam("auth=yes&backurl=".$arResult["backurl_encode"], 
		array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
		?>" class="photo-action authorize" title="<?=GetMessage("P_LOGIN_TITLE")?>"><?=GetMessage("P_LOGIN")?></a><?
?></div>
<div class="empty-clear"></div>
<?
endif;

if (!empty($arResult["GALLERY"])):
?><div class="photo-user <?=($arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId() ? " photo-user-my" : "")?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="gallery-table-header"><tr>
	<td width="0%" class="picture"><div class="photo-gallery-avatar" <?
		?>style="width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px; height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;<?
	if (!empty($arResult["GALLERY"]["PICTURE"])):
			?>background-image:url(<?=$arResult["GALLERY"]["PICTURE"]["SRC"]?>);<?
	endif;
		?>"></div>
	</td>
	<td width="<?=($arParams["GALLERY_SIZE"] > 0 ? "70" : "100")?>%" align="left" class="data">
		<div class="photo-gallery-name"><?=$arResult["GALLERY"]["NAME"]?></div><?
	if (!empty($arResult["GALLERY"]["DESCRIPTION"])):?>
		<div class="photo-gallery-description"><?=$arResult["GALLERY"]["DESCRIPTION"]?></div><?
	endif;
	?>
	</td>
</tr></table>
</div><?
endif;
?>