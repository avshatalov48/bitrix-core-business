<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || mb_strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;

/********************************************************************
				Input params
********************************************************************/
$arParams["SQUARE"] = ($arParams["SQUARE"] == "N" ? "N" : "Y");
$arParams["THUMBNAIL_SIZE"] = intval(intVal($arParams["THUMBNAIL_SIZE"]) > 0 ? $arParams["THUMBNAIL_SIZE"] : 80);
/********************************************************************
				Input params
********************************************************************/

if ($arParams["AJAX_CALL"] == "Y"):
	$APPLICATION->RestartBuffer();
?>

<script src="/bitrix/components/bitrix/photogallery.section.edit.icon/templates/.default/script.js"></script>
<?
else:
	CAjax::Init();
endif;
?>

<?if ($arResult["ERROR_MESSAGE"] != ""):?>
<script>
window.oPhotoEditIconDialogError = "<?= CUtil::JSEscape($arResult["ERROR_MESSAGE"]); ?>";
</script>
<?
if ($arParams["AJAX_CALL"] == "Y") {die();}
endif;
?>

<script>
BX.ready(function(){
	if (window.oPhotoEditAlbumDialog)
	{
		window.oPhotoEditAlbumDialog.SetTitle('<?= GetMessage('P_EDIT_ALBUM_ICON_TITLE')?>');
		if (!window.BXPH_MESS)
			BXPH_MESS = {};

		BXPH_MESS.UnknownError = '<?= GetMessage('P_UNKNOWN_ERROR')?>';
	}
});
</script>

<div class="photo-window-edit" id="photo_section_edit_form">
<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="form_photo_edit_icon" id="form_photo_edit_icon" onsubmit="return CheckFormEditIcon(this);" class="photo-form">
	<input type="hidden" name="edit" value="Y" />
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="IBLOCK_SECTION_ID" value="<?=$arResult["FORM"]["IBLOCK_SECTION_ID"]?>" />
<table class="photo-popup">
	<tr>
		<td class="table-body">
			<div class="photo-info-box photo-info-box-section-edit-icon">
				<div class="photo-info-box-inner">

<p>
<span id="bxph_error_cont" style="display: none; color: red!important;">
<?if (!empty($arResult["ERROR_MESSAGE"]))
	ShowError($arResult["ERROR_MESSAGE"]);
?>
</span>
<?=(count($arResult["ITEMS"]) <= 0 ? GetMessage("P_EMPTY_PHOTO") : GetMessage("P_SELECT_PHOTO"))?>
</p>

<?
if (count($arResult["ITEMS"]) > 0):
$_REQUEST["photos"] = (is_array($_REQUEST["photos"]) ? $_REQUEST["photos"] : array($_REQUEST["photos"]));
?>
<div class="photo-edit-fields photo-edit-fields-section-icon">
	<div id="photo-cover-images">
	<!--#THUMBS_BEGIN#--> <?/* DON'T REMOVE THIS COMMENT! Used to cut it off in AJAX mode*/?>

<?foreach ($arResult["ITEMS"]	as $key => $arItem):
	if (!is_array($arItem))
		continue;

	$res = array(
		"width" => intval($arItem["PICTURE"]["WIDTH"]),
		"height" => intval($arItem["PICTURE"]["HEIGHT"]),
		"left" => 0,
		"top" => 0
	);
	if ($res["width"] > 0 && $res["height"] > 0)
	{
		$koeff = ($arParams["THUMBNAIL_SIZE"] / min($res["width"], $res["height"]));
		if ($koeff < 1)
		{
			$res["width"] = intval($res["width"] * $koeff);
			$res["height"] = intval($res["height"] * $koeff);
		}
		$res["left"] = 0 - intval(($res["width"] - $arParams["THUMBNAIL_SIZE"])/2);
		$res["top"] = 0 - intval(($res["height"] - $arParams["THUMBNAIL_SIZE"])/2);
	}
	elseif ($res["width"] == 0)
	{
		$res["height"] = $arParams["THUMBNAIL_SIZE"];
	}
	else
	{
		$res["width"] = $arParams["THUMBNAIL_SIZE"];
	}
	$sTitle = htmlspecialcharsEx($arItem["~NAME"]);

?>

	<div class="photo-edit-field photo-edit-field-image photo-photo" style="width:<?=$arParams["THUMBNAIL_SIZE"]?>px; height:<?=$arParams["THUMBNAIL_SIZE"]?>px; overflow:hidden; display:inline;">
		<input type="checkbox" name="photos[]" id="photo_ch_<?=$arItem["ID"]?>" value="<?=$arItem["ID"]?>" <?= (in_array($arItem["ID"], $_REQUEST["photos"]) ? 'checked="checked"' : '')?> /><label for="photo_ch_<?= $arItem["ID"]?>"><img border="0" src="<?=$arItem["PICTURE"]["SRC"]?>" id="photo_img_<?=$arItem["ID"]?>" alt="<?=$sTitle?>" title="<?=$sTitle?>" style="margin-left:<?=$res["left"]?>px; margin-top: <?=$res["top"]?>px; position:static; width:<?=$res["width"]?>px; height:<?=$res["height"]?>px;" /></label>
	</div>
<?endforeach;?>
	<!--#THUMBS_END#--> <?/* DON'T REMOVE THIS COMMENT! Used to cut it off in AJAX mode*/?>
	</div><div class="empty-clear"></div>
</div>

<div id="photo-cover-navigation">
	<!--#NAVI_BEGIN#--> <?/* DON'T REMOVE THIS COMMENT! Used to cut it off in AJAX mode*/?>
<?if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 1 && $arResult["NAV_RESULT"]->NavPageNomer > 1):?>
	<a href="<?=$APPLICATION->GetCurPageParam(
		"PAGEN_".$arResult["NAV_RESULT"]->NavNum."=".($arResult["NAV_RESULT"]->NavPageNomer - 1),
		array("PAGEN_".$arResult["NAV_RESULT"]->NavNum, "AJAX_CALL"))?>" onclick="return get_more_covers(this);"><?=GetMessage("P_PHOTO_MORE")?></a>
<?endif;?>
	<!--#NAVI_END#--> <?/* DON'T REMOVE THIS COMMENT! Used to cut it off in AJAX mode*/?>
</div>
<?
endif;

?>
				</div>

<? if ($arParams["AJAX_CALL"] != "Y"):?>
				<div style="margin:20px 0 0 !important;">
					<input type="submit" name="name_submit" value="<?=GetMessage("P_SUBMIT");?>" />
					<input type="submit" name="cancel" value="<?=GetMessage("P_CANCEL");?>" />
				</div>
<?endif;?>

			</div>
		</td>
	</tr>
</table>
</form>
</div>
<?

if ($arParams["AJAX_CALL"] == "Y"):
	die();
else:
?><script>
function CheckFormEditIconCancel(pointer)
{
	if (pointer.form)
	{
		pointer.form.edit.value = 'cancel';
		pointer.form.submit();
	}
	return false;
}
function CheckFormEditIcon()
{
	return true;
}
</script><?
endif;
?>