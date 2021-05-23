<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"])):
	return true;
endif;
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

IncludeAJAX();
/********************************************************************
				Input params
********************************************************************/
$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBS_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 120);
if ($arParams["PICTURES_SIGHT"] != "standart" && intVal($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]) > 0)
	$arParams["THUMBS_SIZE"] = $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"];

$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ?
		$arParams["SHOW_PAGE_NAVIGATION"] : "bottom");

$arParams["SHOW_CONTROLS"] = ($arParams["SHOW_CONTROLS"] == "Y" ? "Y" : "N");
$arParams["SHOW_FORM"] = ($arParams["SHOW_INPUTS"] == "Y" || $arParams["SHOW_CONTROLS"] == "Y" ? "Y" : "N");
$arParams["SHOW_FORM"] = ($arParams["SHOW_FORM"] == "Y" && $arParams["PERMISSION"] >= "U" ? "Y" : "N");

$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["SHOW_SHOWS"] = ($arParams["SHOW_SHOWS"] == "Y" ? "Y" : "N");
$arParams["SHOW_COMMENTS"] = ($arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arParams["SHOW_DATE"] = ($arParams["SHOW_DATE"] == "Y" ? "Y" : "N");
$arParams["NEW_DATE_TIME_FORMAT"] = trim(!empty($arParams["NEW_DATE_TIME_FORMAT"]) ? $arParams["NEW_DATE_TIME_FORMAT"] :
	$DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
$arParams["COMMENTS_TYPE"] = (strToLower($arParams["COMMENTS_TYPE"]) == "forum" ? "forum" : "blog");
/********************************************************************
				Input params
********************************************************************/
if ($arResult["ELEMENTS"]["MAX_HEIGHT"] > $arParams["THUMBS_SIZE"] || $arResult["ELEMENTS"]["MAX_HEIGHT"] <= 0):
	$arResult["ELEMENTS"]["MAX_HEIGHT"] = $arParams["THUMBS_SIZE"];
endif;
$bShowInput = false;
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

if ($arParams["SHOW_CONTROLS"] == "Y" && !empty($arParams["PICTURES"])):
?>
	<div class="photo-controls photo-view">
<?
	$arRes = array_merge(array("standart" => array("title" => GetMessage("P_STANDARD"))),$arParams["PICTURES"]);
?>
	<span class="photo-view sights"><?=GetMessage("P_PICTURES_SIGHT")?>:
		<select name="picture" onchange="ChangeText(this);" title="<?=GetMessage("P_PICTURES_SIGHT_TITLE")?>">
<?
	foreach ($arRes as $key => $val):
		?><option value="<?=$key?>"<?=($key."" == $arParams["PICTURES_SIGHT"]."" ? " selected" : "")?>><?=$val["title"]?></option><?
	endforeach;
?>
		</select>
	</span>
	</div>
<?
endif;

if ($arParams["SHOW_FORM"] == "Y"):
?>
	<form action="<?=POST_FORM_ACTION_URI?>" method="post" id="photoForm" class="photo-form" onsubmit="return false;">
		<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
		<input type="hidden" name="detail_list_edit" value="Y" />
		<input type="hidden" name="ACTION" id="ACTION" value="Y" />
		<input type="hidden" name="SECTION_ID" value="<?=$arParams["SECTION_ID"]?>" />
		<input type="hidden" name="IBLOCK_ID" value="<?=$arParams["IBLOCK_ID"]?>" />
		<input type="hidden" name="REDIRECT_URL" value="<?=htmlspecialcharsbx($APPLICATION->GetCurPageParam("", array(), false))?>" />
<?
	if ($arParams["SHOW_CONTROLS"] == "Y" && $arParams["DetailListViewMode"] == "edit"):
		$bShowInput = true;
?>
	<div class="photo-controls">
		<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("view_mode=view&".bitrix_sessid_get(), array("view_mode", "sessid"), false)?>"<?
			?> class="photo-action go-to-view" title="<?=GetMessage("P_VIEW_TITLE")?>"><span><?=GetMessage("P_VIEW")?></span></a></noindex>
		<a href="#" class="photo-action select-all" onclick="return SelectAll(this.firstChild);"><?
			?><input type="hidden" id="select_all1" name="select_all" value="N" /><?=GetMessage("P_SELECT_ALL")?></a><?
		?><a href="#" onclick="return Delete(this.previousSibling.firstChild.form);" class="photo-action delete">
			<span><?=GetMessage("P_DELETE_SELECTED")?></span></a>
		<a href="#" onclick="this.style.display='none';this.nextSibling.style.display='block'; return false;" class="photo-action move"><?
			?><span><?=GetMessage("P_MOVE_SELECTED")?></span></a><?
		?><span style="display:none;">
			<a href="#" onclick="return false;" class="photo-action move"><span><?=GetMessage("P_MOVE_SELECTED_IN")?></span></a>
			<select name="TO_SECTION_ID"><?
			foreach ($arResult["SECTIONS_LIST"] as $key => $val):
				?><option value="<?=$key?>" <?
					?> <?=((intVal($arParams["SECTION_ID"]) == intVal($key)) ? " selected='selected'" : "")?>><?=$val?></option><?
			endforeach;
			?></select><input type="button" name="name_submit" value="OK" onclick="Move(this.form)" />
		</span>
		<div class="empty-clear"></div>
	</div>
<?
	elseif ($arParams["SHOW_CONTROLS"] == "Y"):
?>
	<div class="photo-controls photo-action">
		<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("view_mode=edit&amp;".bitrix_sessid_get(), array("view_mode", "sessid"), false)?>"<?
			?> title="<?=GetMessage("P_EDIT_TITLE")?>" class="photo-action go-to-edit"><span><?=GetMessage("P_EDIT")?></span></a></noindex>
		<div class="empty-clear"></div>
	</div>

<?
	else:
		$bShowInput = true;
?>
	<div class="photo-controls photo-action select-all">
		<noindex><a rel="nofollow" href="#" class="photo-action select-all" onclick="return SelectAll(this.firstChild); return false;"><?
			?><input type="hidden" id="select_all1" name="select_all" value="N" /><?=GetMessage("P_SELECT_ALL")?></a></noindex>
		<div class="empty-clear"></div>
	</div>
<?
	endif;
endif;

$current_date = "";
?>
<div class="photo-photo-list photo-photos">
<?
foreach ($arResult["ELEMENTS_LIST"]	as $key => $arItem):
	if (!is_array($arItem)):
		continue;
	elseif ($arParams["SHOW_DATE"] == "Y"):
		$this_date = PhotoFormatDate($arItem["~DATE_CREATE"], "DD.MM.YYYY HH:MI:SS", "d.m.Y");
		if ($this_date != $current_date)
		{
			$current_date = $this_date;
			?><div class="group-by-days photo-date"><?=PhotoDateFormat($arParams["NEW_DATE_TIME_FORMAT"], MakeTimeStamp($this_date, "DD.MM.YYYY"))?></div><?
		}
	endif;
	$bActiveElement = ($arItem["ACTIVE"] != "Y" ? false : true);
?>
<table border="0" cellpadding="0" cellspacing="0" class="photo-photo-item <?=($bShowInput ? " photo-photo-item-edit" : "")?><?
	?><?=(!$bActiveElement ? " photo-photo-item-notapproved" : "")?>"<?
	?> style="height: <?=($arResult["ELEMENTS"]["MAX_HEIGHT"] + 19)
		/* 19: 5*2 - padding; 2*2 - image-border; 2*2 - div-border; 5 - additional space*/ ?>px;"><tr><td>
	<div class="photo-photo photo-photo-item-inner" onmouseover="ShowDescription('<?=$arItem["ID"]?>')" onmouseout="HideDescription('<?=$arItem["ID"]?>')">
<?
	$sTitle = htmlspecialcharsEx($arItem["~NAME"].(!$bActiveElement ? GetMessage("P_PHOTO_NOT_APPROVED") : ""));

	$sImage = CFile::ShowImage($arItem["PICTURE"], $arParams["THUMBS_SIZE"], $arParams["THUMBS_SIZE"],
		"border=\"0\" vspace=\"0\" hspace=\"0\" alt=\"".$sTitle."\" title=\"".$sTitle."\"");

	if (!is_array($arItem["PICTURE"])):
		?><div class="empty"></div><?
	elseif ($arResult["USER_HAVE_ACCESS"] != "Y"):
		?><?=$sImage?><?
	elseif (!$bShowInput):
		?><a href="<?=$arItem["URL"]?>"><?=$sImage?></a><?
	else:
		?><input type="checkbox" value="<?=$arItem["ID"]?>" name="items[]" id="items_<?=$arItem["ID"]?>" <?
			?><?=(($arResult["bVarsFromForm"] == "Y" && in_array($arItem["ID"], $_REQUEST["items"])) ? "checked" : "")?> /><?
		?><a href="#" onclick="this.previousSibling.checked=!this.previousSibling.checked; return false;"><?
			?><?=$sImage?></a><?
	endif;

	if (!$bShowInput):
?>
	<div style="position:relative;" class="photo-photo-item-description">
		<div class="photo-photo-item-description-inner" id="item_<?=$arItem["ID"]?>" <?=(PhotoGetBrowser() == "opera" ? 'style="overflow:auto; height:150px;"' : '')?>>
<?
		if ($arResult["USER_HAVE_ACCESS"] == "Y"):
?>
			<div class="photo-title"><a href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a></div>
			<div class="photo-controls photo-view">
				<noindex><a rel="nofollow" href="<?=$arItem["SLIDE_SHOW_URL"]?>" <?
					?> class="photo-view original" title="<?=GetMessage("P_FULL_SCREEN_TITLE")?>"><?=GetMessage("P_FULL_SCREEN")?></a></noindex></div>
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
			<div class="photo-shows"><?=GetMessage("P_COMMENTS")?>: <?=intVal($arParams["COMMENTS_TYPE"] != "blog" ? $arItem["PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"] : $arItem["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"])?></div>
<?
		endif;

		if (!empty($arItem["PREVIEW_TEXT"])):
			?><div class="photo-description"><?=$arItem["PREVIEW_TEXT"]?></div><?
		endif;
		?></div>
	</div>
<?
endif;
?>
</div>
</td></tr></table>
<?
endforeach;
?>
	<div class="empty-clear"></div>
</div>
<?
if ($arParams["SHOW_FORM"] == "Y"):
?>
</form>
<?
	if ($arParams["DetailListViewMode"] == "edit"):
?>
<script type="text/javascript">
function Delete(form)
{
	if (!form || !__check_form(form, 'items[]')){
		return false;}
	else if (confirm('<?=CUtil::JSEscape(GetMessage("P_DELETE_CONFIRM"))?>')) {
		form.elements['ACTION'].value = 'drop';
		form.submit();}
	return false;}
function Move(form) {
	if (!form || !__check_form(form, 'items[]'))
		return false;
	form.elements['ACTION'].value = 'move';
	form.submit();
	return false;}
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