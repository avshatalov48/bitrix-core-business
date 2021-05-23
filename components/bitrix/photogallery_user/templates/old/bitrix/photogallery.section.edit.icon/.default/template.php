<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

/********************************************************************
				Input params
********************************************************************/
$arParams["SQUARE"] = ($arParams["SQUARE"] == "N" ? "N" : "Y");
$arParams["THUMBS_SIZE"] = intVal(intVal($arParams["THUMBS_SIZE"]) > 0 ? $arParams["THUMBS_SIZE"] : 80);
/********************************************************************
				Input params
********************************************************************/
if ($arParams["AJAX_CALL"] != "Y"):
?>
<div class="photo-controls">
<?
	if (!empty($arResult["SECTION"]["SECTION_LINK"])):
?>
	<noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["SECTION_LINK"]?>" title="<?=GetMessage("P_BACK_UP_TITLE")?>" <?
		?>class="photo-action back-to-album"><?=GetMessage("P_BACK_UP")?></a></noindex>
<?
	elseif (!empty($arResult["SECTION"]["BACK_LINK"])):
?>
	<noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["BACK_LINK"]?>" title="<?=GetMessage("P_UP_TITLE")?>" <?
		?>class="photo-action back-to-album"><?=GetMessage("P_UP")?></a></noindex>
<?
	endif;
?>
</div>
<?
else:
	$APPLICATION->RestartBuffer();
endif;

?>
<div class="photo-window-edit" id="photo_section_edit_form">
<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="form_photo" id="form_photo" onsubmit="return CheckFormEditIcon(this);" class="photo-form">
	<input type="hidden" name="edit" value="Y" />
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="IBLOCK_SECTION_ID" value="<?=$arResult["FORM"]["IBLOCK_SECTION_ID"]?>" />
<table cellpadding="0" cellspacing="0" border="0" class="photo-popup">
	<thead>
		<tr>
			<td><?=$arResult["PAGE_TITLE"]?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="table-body">
				<div class="photo-info-box photo-info-box-section-edit-icon inner">
					<div class="photo-info-box-inner">
		<?	
		if (!empty($arResult["ERROR_MESSAGE"])):
			?><div class="error"><?ShowError($arResult["ERROR_MESSAGE"]);?></div><?
		endif;

if (count($arResult["ITEMS"]) <= 0):
?>
		<?=GetMessage("P_EMPTY_PHOTO")?>
<?
else:
?>
		<?=GetMessage("P_SELECT_PHOTO")?>
<?
$_REQUEST["photos"] = (is_array($_REQUEST["photos"]) ? $_REQUEST["photos"] : array($_REQUEST["photos"]));
?>
	<div class="photo-edit-fields photo-edit-fields-section-icon">
<?
	foreach ($arResult["ITEMS"]	as $key => $arItem):
		if (!is_array($arItem)):
			continue;
		endif;
		$res = array(
			"width" => intVal($arItem["PICTURE"]["WIDTH"]), 
			"height" => intVal($arItem["PICTURE"]["HEIGHT"]), 
			"left" => 0, 
			"top" => 0);
		if ($res["width"] > 0 && $res["height"] > 0):
			$koeff = ($arParams["THUMBS_SIZE"] / min($res["width"], $res["height"]));
			if ($koeff < 1):
				$res["width"] = intVal($res["width"] * $koeff);
				$res["height"] = intVal($res["height"] * $koeff);
			endif;
			$res["left"] = 0 - intVal(($res["width"] - $arParams["THUMBS_SIZE"])/2);
			$res["top"] = 0 - intVal(($res["height"] - $arParams["THUMBS_SIZE"])/2);
		elseif ($res["width"] == 0):
			$res["height"] = $arParams["THUMBS_SIZE"];
		else:
			$res["width"] = $arParams["THUMBS_SIZE"];
		endif;
		$sTitle = htmlspecialcharsEx($arItem["~NAME"]);
		
?>
		<div class="photo-edit-field photo-edit-field-image photo-photo" style="width:<?=$arParams["THUMBS_SIZE"]?>px; height:<?=$arParams["THUMBS_SIZE"]?>px; overflow:hidden;">
			<input type="checkbox" name="photos[]" id="photo_<?=$arItem["ID"]?>" value="<?=$arItem["ID"]?>" <?
				?><?=(in_array($arItem["ID"], $_REQUEST["photos"]) ? 'checked="checked"' : '')?> /><?
			?><img border="0" src="<?=$arItem["PICTURE"]["SRC"]?>" id="photo_img_<?=$arItem["ID"]?>" <?
				?>alt="<?=$sTitle?>" title="<?=$sTitle?>" <?
				?>style="margin-left:<?=$res["left"]?>px; margin-top: <?=$res["top"]?>px; position:static; width:<?=$res["width"]?>px; height:<?=$res["height"]?>px;" <?	
				?>onclick="this.previousSibling.checked=!(this.previousSibling.checked);" />
		</div>
<?
	endforeach;
?>
</div>
<?
endif;

			?>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr><td class="table-controls">
			<input type="submit" name="name_submit" value="<?=GetMessage("P_SUBMIT");?>" />
			<input type="button" name="name_cancel" value="<?=GetMessage("P_CANCEL");?>" onclick="CheckFormEditIconCancel(this)" />
		</td></tr>
	</tfoot>
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