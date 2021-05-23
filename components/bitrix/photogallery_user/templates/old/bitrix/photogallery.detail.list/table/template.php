<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"])):
	return true;
endif;
/********************************************************************
				Input params
********************************************************************/
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

IncludeAJAX();

$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBS_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 120);
if ($arParams["PICTURES_SIGHT"] != "standart" && intVal($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]) > 0)
	$arParams["THUMBS_SIZE"] = $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"];

$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ?
		$arParams["SHOW_PAGE_NAVIGATION"] : "none");
$arParams["SHOW_CONTROLS"] = ($arParams["SHOW_CONTROLS"] == "Y" ? "Y" : "N");
$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["SHOW_SHOWS"] = ($arParams["SHOW_SHOWS"] == "Y" ? "Y" : "N");
$arParams["SHOW_COMMENTS"] = ($arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
/********************************************************************
				Input params
********************************************************************/
$arResult["ELEMENTS"]["MAX_HEIGHT"] = $arParams["THUMBS_SIZE"];

if (!empty($arResult["ERROR_MESSAGE"])):
?>
	<div class="photo-error"><?=ShowError($arResult["ERROR_MESSAGE"])?></div>
<?
endif;
if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
	<div class="photo-navigation photo-navigation-top"><?=$arResult["NAV_STRING"]?></div>
<?
endif;

if ($arParams["SHOW_CONTROLS"] == "Y"):
	if (!empty($arParams["PICTURES"])):
?>
<div class="photo-controls photo-view">
<?
	$arRes = array_merge(
		array("standart" => array("title" => GetMessage("P_STANDARD"))),
		$arParams["PICTURES"]);
?>
	<span class="photo-view sights"><?=GetMessage("P_PICTURES_SIGHT")?>:
		<select name="picture" onchange="ChangeText(this);" title="<?=GetMessage("P_PICTURES_SIGHT_TITLE")?>"><?
		foreach ($arRes as $key => $val):
			?><option value="<?=$key?>"<?=($key."" == $arParams["PICTURES_SIGHT"]."" ? " selected" : "")?>><?=$val["title"]?></option><?
		endforeach;
		?></select>
	</span>
</div>
<?
	endif;


	if ($arParams["PERMISSION"] >= "W"):
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="post" id="photoForm" class="photo-form">
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="detail_list_edit" value="Y" />
	<input type="hidden" name="ACTION" id="ACTION" value="Y" />
	<input type="hidden" name="SECTION_ID" value="<?=$arParams["SECTION_ID"]?>" />
	<input type="hidden" name="IBLOCK_ID" value="<?=$arParams["IBLOCK_ID"]?>" />
	<input type="hidden" name="REDIRECT_URL" value="<?=htmlspecialcharsbx($APPLICATION->GetCurPageParam("", array(), false))?>" />
<?
		if ($arParams["DetailListViewMode"] == "edit"):
?>
	<div class="photo-controls">
		<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("view_mode=view&".bitrix_sessid_get(), array("view_mode", "sessid"), false)?>"<?
			?> class="photo-action go-to-view" title="<?=GetMessage("P_VIEW_TITLE")?>"><span><?=GetMessage("P_VIEW")?></span></a></noindex>
		<a href="#" class="photo-action select-all" onclick="return SelectAll(this.firstChild);"><?
			?><input type="hidden" id="select_all1" name="select_all" value="N" /><?=GetMessage("P_SELECT_ALL")?></a><?
		?><a href="#" onclick="return Delete(this.previousSibling.firstChild.form);" <?
			?>class="photo-action delete"><span><?=GetMessage("P_DELETE_SELECTED")?></span></a>
		<a href="#" onclick="this.style.display='none';this.nextSibling.style.display='block'; return false;" <?
			?>class="photo-action move"><span><?=GetMessage("P_MOVE_SELECTED")?></span></a><?
		?><span style="display:none;"><a href="#" onclick="return false;" class="photo-action move"><span><?=GetMessage("P_MOVE_SELECTED_IN")?></span></a><?
			?><select name="TO_SECTION_ID"><?
				foreach ($arResult["SECTIONS_LIST"] as $key => $val):
					?><option value="<?=$key?>" <?
						?> <?=((intVal($arParams["SECTION_ID"]) == intVal($key)) ? " selected='selected'" : "")?>><?=$val?></option><?
				endforeach;
			?></select><input type="button" name="name_submit" value="OK" onclick="Move(this.form)" />
		</span>
		<div class="empty-clear"></div>
	</div>
<?
		else:
?>
	<div class="photo-controls">
		<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("view_mode=edit&".bitrix_sessid_get(), array("view_mode", "sessid"), false)?>" <?
		?> title="<?=GetMessage("P_EDIT_TITLE")?>" class="photo-action go-to-edit"><?=GetMessage("P_EDIT")?></a></noindex>
	</div>
<?
		endif;
	endif;
endif;
$counter = 0;

?>
<div class="photo-detail-list photo-photos">
<?
foreach ($arResult["ELEMENTS_LIST"]	as $key => $arItem):

	if (!is_array($arItem))
		continue;
	$counter++;
	if ($arParams["CELL_COUNT"] > 0 && $counter > $arParams["CELL_COUNT"]):
		?><div class="empty-clear"></div><?
		$counter = 1;
	endif;
	$bActiveElement = ($arItem["ACTIVE"] != "Y" ? false : true);

?>
	<table cellpadding="0" cellspacing="0" border="0" class="result <?
		?><?=(!$bActiveElement ? " photo-photo-notapproved" : "")?>" <?
		?>style="width:<?=intVal($arParams["THUMBS_SIZE"] + 36/* 3*2 + 10*2 + 5*2*/)?>px; height:<?=intVal($arParams["THUMBS_SIZE"] + 26 + 50)?>px;" <?
		?>onmouseover="ShowDescription('<?=$arItem["ID"]?>')" onmouseout="HideDescription('<?=$arItem["ID"]?>')">
		<tr><th class="result_image" align="center">
<?
	if($arResult["USER_HAVE_ACCESS"] == "Y" && ($arParams["SHOW_CONTROLS"] == "Y" && $arParams["DetailListViewMode"] == "edit")):
		?><div style="position:relative;"><?
	endif;
		?>
			<table class="shadow" cellpadding="0" cellspacing="0" border="0" width="0">
				<tr valign="middle"><td colspan="2" class="photo-image" align="center" <?
					?>style="width:<?=($arParams["THUMBS_SIZE"]+14)?>px; height:<?=($arParams["THUMBS_SIZE"]+14)?>px;"><?

	if(!is_array($arItem["PICTURE"])):
		?><div style="width:<?=$arParams["THUMBS_SIZE"]?>px; height:<?=$arParams["THUMBS_SIZE"]?>px;"></div><?
	else:
		$sTitle = htmlspecialcharsEx($arItem["~NAME"].(!$bActiveElement ? GetMessage("P_PHOTO_NOT_APPROVED") : ""));
		$sImage = CFile::ShowImage($arItem["PICTURE"], $arParams["THUMBS_SIZE"], $arParams["THUMBS_SIZE"],
			"border=\"0\" vspace=\"0\" hspace=\"0\" alt=\"".$sTitle."\" title=\"".$sTitle."\"");
		if ($arResult["USER_HAVE_ACCESS"] != "Y"):
			?><?=$sImage?><?
		elseif ($arParams["SHOW_CONTROLS"] == "Y" && $arParams["DetailListViewMode"] == "edit"):
			?><input type="checkbox" value="<?=$arItem["ID"]?>" name="items[]" <?=(($arResult["bVarsFromForm"] == "Y" && in_array($arItem["ID"], $_REQUEST["items"])) ? "checked" : "")?> id="items_<?=$arItem["ID"]?>" /><?
			?><a href="#" onclick="this.previousSibling.checked=!this.previousSibling.checked; return false;"><?=$sImage?></a><?
		else:
			?><a href="<?=$arItem["URL"]?>"><?=$sImage?></a><?
		endif;
	endif;
				?></td></tr>
				<tr class="b">
					<td class="l"><div class="empty"></div></td>
					<td class="r"><div class="empty"></div></td>
				</tr>
			</table>
<?
	if($arResult["USER_HAVE_ACCESS"] == "Y" && ($arParams["SHOW_CONTROLS"] == "Y" && $arParams["DetailListViewMode"] == "edit")):
		?></div><?
	endif;

		?>
		</th></tr>
		<tr><td class="result_text"><?

	if (!empty($arItem) && $arParams["DetailListViewMode"] != "edit"):
?>
	<div style="position:relative;">
		<div class="photo-photo-item-description-inner" id="item_<?=$arItem["ID"]?>" style="display:none;<?=(PhotoGetBrowser() == "opera" ? 'overflow:auto; height:150px;"' : '')?>">
<?
		if ($arResult["USER_HAVE_ACCESS"] == "Y"):
?>
			<div class="photo-title"><a href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a></div>
			<div class="photo-controls photo-view">
				<noidex><a rel="nofollow" href="<?=$arItem["SLIDE_SHOW_URL"]?>" <?
					?>class="photo-view original" title="<?=GetMessage("P_FULL_SCREEN_TITLE")?>"><?=GetMessage("P_FULL_SCREEN")?></a></noidex></div>
<?
		else:
?>
			<div class="photo-title"><?=$arItem["NAME"]?></div>
<?
		endif;
?>
			<div class="photo-date"><?=$arItem["DATE_CREATE"]?></div>
<?

		if ($arParams["SHOW_TAGS"] == "Y"):
			$tmp = array();
			$arItem["TAGS_LIST"] = (!is_array($arItem["TAGS_LIST"]) ? array($arItem["TAGS_LIST"]) : $arItem["TAGS_LIST"]);
			foreach ($arItem["TAGS_LIST"] as $tags):
				$tmp[] = '<noindex><a rel="nofollow" href="'.$tags["TAGS_URL"].'">'.$tags["TAGS_NAME"].'</a></noindex>';
			endforeach;
			if (!empty($tmp)):
?>
			<div class="photo-tags"><?=implode(", ", $tmp)?></div>
<?
			endif;
		endif;

		if ($arParams["SHOW_RATING"] == "Y"):
?>
			<div class="photo-rating">
			<?
			$APPLICATION->IncludeComponent(
				"bitrix:iblock.vote",
				"ajax",
				Array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ELEMENT_ID" => $arItem["ID"],
					"MAX_VOTE" => $arParams["MAX_VOTE"],
					"VOTE_NAMES" => $arParams["VOTE_NAMES"],
					"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"]
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);
			?>
			</div>
<?
		endif;

		if ($arParams["SHOW_SHOWS"] == "Y"):
?>
			<div class="photo-shows"><?=GetMessage("P_SHOWS")?>: <?=intVal($arItem["SHOW_COUNTER"])?></div>
<?
		endif;

		if ($arParams["SHOW_COMMENTS"] == "Y"):
?>
			<div class="photo-shows"><?=GetMessage("P_COMMENTS")?>: <?=intVal($arParams["COMMENTS_TYPE"] == "FORUM" ? $arItem["PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"] : $arItem["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"])?></div>
<?
		endif;

		if (!empty($arItem["PREVIEW_TEXT"])):
			?><div class="photo-description"><?=$arItem["PREVIEW_TEXT"]?></div><?
		endif;

		?>
		</div>
	</div>
	<?
	endif;
?>
	<div class="photo-title" style="width:<?=intVal($arParams["THUMBS_SIZE"] + 14)?>px;"><?=$arItem["NAME"]?></div>
	</td></tr>
</table>
<?
endforeach;

?>
	<div class="empty-clear"></div>
</div>
<?

if ($arParams["SHOW_CONTROLS"] == "Y" && $arParams["PERMISSION"] >= "W"):
?>
	</form>
<script type="text/javascript">
function Delete(form)
{
	if (!form || !__check_form(form, 'items[]'))
	{
		return false;
	}
	else if (confirm('<?=GetMessage("P_DELETE_CONFIRM")?>'))
	{
		form.elements['ACTION'].value = 'drop';
		form.submit();
	}
	return false;
}

function Move(form) {
	if (!form || !__check_form(form, 'items[]'))
		return false;
	form.elements['ACTION'].value = 'move';
	form.submit();
	return false;
}
function __check_form(form, name) {
	var bNotEmpty = false;
	if (!(form && form.elements[name]))
	{
	}
	else if (!form.elements[name].length && form.elements[name].checked)
	{
		bNotEmpty = true;
	}
	else if (form.elements[name].length > 0){
		for (var ii = 0; ii < form.elements[name].length; ii++){
			if (form.elements[name][ii].checked == true){
				bNotEmpty = true;
				break;}
		}
	}
	return bNotEmpty;
}
</script>
<?
endif;

if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
	<div class="photo-navigation photo-navigation-bottom"><?=$arResult["NAV_STRING"]?></div>
<?
endif;

?>
<script type="text/javascript">
function ChangeText(obj)
{
	if (typeof obj != "object")
		return;
	if (<?=intVal($GLOBALS["USER"]->GetId())?> > 0)
	{
		var TID = CPHttpRequest.InitThread();
		CPHttpRequest.SetAction(TID, function(data){window.location.reload(true);})
		CPHttpRequest.Send(TID, '/bitrix/components/bitrix/photogallery.detail.list/user_settings.php', {"picture_sight":obj.value, "sessid":'<?=bitrix_sessid()?>'});
	}
	else
	{
		jsUtils.Redirect([], '<?=CUtil::addslashes($GLOBALS["APPLICATION"]->GetCurPageParam("PICTURES_SIGHT=#pictures_sight#", array("PICTURES_SIGHT", "sessid"), false))?>'.replace('#pictures_sight#', obj.value));
	}
}
function HideDescription(id)
{
	if (document.getElementById('item_' + id))
		document.getElementById('item_' + id).style.display = 'none';
}
function ShowDescription(id)
{
	if (document.getElementById('item_' + id))
		document.getElementById('item_' + id).style.display = 'block';
}
function SelectAll(oObj)
{
	oObj.value = (oObj.value == 'N' ? 'Y' : 'N');
	for (var ii = 0; ii < oObj.form.elements.length; ii++) {
		if (oObj.form.elements[ii].name == 'items[]'){
				oObj.form.elements[ii].checked = (oObj.value == 'Y');}}
	return false;
}
</script>