<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult["GALLERY"]) || !$this->__component->__parent)
	return false;

/********************************************************************
				Input params
********************************************************************/
$arParams["GALLERY_AVATAR_SIZE"] = intval(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ?  $arParams["GALLERY_AVATAR_SIZE"] : 50);
/********************************************************************
				/Input params
********************************************************************/
if ($arParams["PERMISSION"] >= "U")
{
?>
	<noindex>
	<div class="photo-controls photo-controls-buttons photo-controls-gallery">
		<ul class="photo-controls">
			<li class="photo-control photo-control-album-add">
				<a onclick="EditAlbum('<?= CUtil::JSEscape(htmlspecialcharsbx($arResult["GALLERY"]["LINK"]["~NEW_ALBUM"]))?>'); return false;" <?
					?>rel="nofollow" href="<?=$arResult["GALLERY"]["LINK"]["NEW_ALBUM"]?>"><span><?=GetMessage("P_ADD_ALBUM")?></span></a>
			</li>
<??>
			<li class="photo-control photo-control-last photo-control-album-upload">
				<a target="_self" rel="nofollow" href="<?=$arResult["GALLERY"]["LINK"]["UPLOAD"]?>"><span><?=GetMessage("P_UPLOAD")?></span></a>
			</li>
		</ul>
	</div>
	</noindex>
<?
}
?>
<div class="photo-item photo-gallery-item">
	<div class="photo-gallery-avatar <?=(empty($arResult["GALLERY"]["PICTURE"]["SRC"])? "photo-gallery-avatar-empty" : "")?>" <?
		if (!empty($arResult["GALLERY"]["PICTURE"])):
			?> style="background-image:url('<?=$arResult["GALLERY"]["PICTURE"]["SRC"]?>');"<?
		endif;
	?>></div>
	<table cellpadding="0" cellspacing="0" border="0" class="photo-table">
		<tr>
			<td>
				<div class="photo-gallery-name"><?=$arResult["GALLERY"]["NAME"]?></div>
			</td>
<?
if ($arParams["PERMISSION"] >= "U")
{
?>
			<td>
				<div class="photo-control">( <a target="_self" href="<?=$arResult["GALLERY"]["LINK"]["EDIT"]?>"><?=GetMessage("P_EDIT")?></a> )</div>
			</td>
<?
}
?>
		</tr>
	</table>
<?
if (!empty($arResult["GALLERY"]["DESCRIPTION"]))
{
?>
		<div class="photo-gallery-description"><?=$arResult["GALLERY"]["DESCRIPTION"]?></div>
<?
}
?>
	<div class="empty-clear"></div>
</div>
