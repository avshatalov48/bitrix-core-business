<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
	$arParams["SHOW_ONLY_PUBLIC"] = ($arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "Y" : "N");
	$arParams["MODERATE"] = ($arParams["MODERATE"] == "Y" ? "Y" : "N");
	$arParams["PERMISSION"] = trim($arParams["PERMISSION"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage().($URL == "index" ? "" : "?");
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default params
********************************************************************/
	if (!empty($_REQUEST["photo_filter_reset"]))
	{
		if (!empty($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]))
			$url = $_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"];
		else
			$url = $APPLICATION->GetCurPageParam("", array("photo_from", "photo_to", "group_photo",
				"photo_filter_reset", "order", "mode"));
		$url = str_replace(array("&group_photo=Y", "&amp;group_photo=Y"), "", $url);
		LocalRedirect($url);
	}

	$arResult["ORDER"] = array("date_create", "shows");
	if ($arParams["USE_RATING"] == "Y")
		$arResult["ORDER"][] = "rating";
	if ($arParams["USE_COMMENTS"] == "Y")
		$arResult["ORDER"][] = "comments";

	$arResult["ORDER_BY"] = (in_array($_REQUEST["order"], $arResult["ORDER"]) ? $_REQUEST["order"] : "date_create");
	$arResult["PERIOD_FROM"] = trim($_REQUEST["photo_from"]);
	$arResult["PERIOD_TO"] = trim($_REQUEST["photo_to"]);
	$arResult["GROUP_BY_DATE_CREATE"] = ($_REQUEST["group_photo"] == "Y" ? "Y" : "N");

	$arResult["MODE"] = ($_REQUEST["mode"] == "public" || $_REQUEST["mode"] == "active" ? $_REQUEST["mode"] : "simple");
	$arResult["SHOW_FILTER"] = ((!empty($arResult["PERIOD_FROM"]) || !empty($arResult["PERIOD_TO"]) || $arResult["GROUP_BY_DATE_CREATE"] == "Y" ||
		$arResult["MODE"] != "simple") ? "Y" : "N");
/********************************************************************
				/Default params
********************************************************************/

if (!$GLOBALS['USER']->IsAuthorized()):
?>
<div class="photo-controls">
	<a href="<?=CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array())?>" title="<?=GetMessage("P_UP_TITLE")?>"  class="photo-action back-to-album" <?
	?>><?=GetMessage("P_UP")?></a>
</div>
<?
endif;
?>
<div class="photo-controls photo-view only-on-main"><noindex>
	<a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=date_create", array("order"))?>"
		title="<?=GetMessage("P_PHOTO_SORT_ID_TITLE")?>" class="photo-view order-date-create<?=
		($arResult["ORDER_BY"] == "date_create" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_ID")?></a>
	<a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=shows", array("order"))?>"
		title="<?=GetMessage("P_PHOTO_SORT_SHOWS_TITLE")?>" class="photo-view order-shows<?=
		($arResult["ORDER_BY"] == "shows" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_SHOWS")?></a>
<?
if (in_array("rating", $arResult["ORDER"])):
?>	<a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=rating", array("order"))?>"
		title="<?=GetMessage("P_PHOTO_SORT_RATING_TITLE")?>" class="photo-view order-rating<?=
		($arResult["ORDER_BY"] == "rating" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_RATING")?></a><?
endif;
if (in_array("comments", $arResult["ORDER"])):
?>	<a rel="nofollow" href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=comments", array("order"))?>"
		title="<?=GetMessage("P_PHOTO_SORT_COMMENTS_TITLE")?>" class="photo-view order-comments<?=
		($arResult["ORDER_BY"] == "comments" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_COMMENTS")?></a><?
endif;
?>
	<div class="empty-clear"></div>
</noindex></div>

<div id="photo-filter">
	<div id="photo-filter-switcher" class="<?=($arResult["SHOW_FILTER"] == "Y" ? "filter-opened" : "filter-closed")?>" <?
		?>onclick="if(this.className=='filter-opened'){this.className='filter-closed';document.getElementById('photo-filter-container').style.display='none';document.getElementById('photo-filter-switcher-href').innerHTML='<?=CUtil::JSEscape(GetMessage("P_OPEN_FILTER"))?>';}else{this.className='filter-opened';document.getElementById('photo-filter-container').style.display='block';document.getElementById('photo-filter-switcher-href').innerHTML='<?=CUtil::JSEscape(GetMessage("P_CLOSE_FILTER"))?>';}"><a href="javascript:void(0);" id="photo-filter-switcher-href"><?=
		($arResult["SHOW_FILTER"] == "Y" ? GetMessage("P_CLOSE_FILTER") : GetMessage("P_OPEN_FILTER"))?></a></div>

	<div id="photo-filter-container" style="display:<?=($arResult["SHOW_FILTER"] == "Y" ? "block" : "none")?>;">
		<div class="photo-filter-fields">
			<form action="" id="photo_filter_form" class="photo_form" method="get">
				<input type="hidden" name="PAGE_NAME" value="detail_list" />
				<input type="hidden" name="order" value="<?=$arResult["ORDER_BY"]?>" />
				<div class="photo-filter-field photo-filter-field-period">
					<label for="photo_from"><?=GetMessage("P_SELECT_PHOTO_FROM_PERIOD")?></label>
					<?$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default",
						Array(
							"SHOW_INPUT"	=>	"Y",
							"INPUT_NAME"	=>	"photo_from",
							"INPUT_NAME_FINISH"	=>	"photo_to",
							"INPUT_VALUE"	=>	$arResult["PERIOD_FROM"],
							"INPUT_VALUE_FINISH"	=>	$arResult["PERIOD_TO"],
							"SHOW_TIME"	=>	"N"
						), $component,
						array("HIDE_ICONS" => "Y"));?>
				</div>
				<div class="photo-filter-field photo-filter-field-group">
					<input type="checkbox" name="group_photo" id="group_photo" value="Y" <?=
					($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? " checked='checked'" : "")?> />
					<label for="group_photo"><?=GetMessage("P_GROUP_BY_DATE_CREATE")?></label>
				</div>
				<?if ($arParams["PERMISSION"] >= "U"):?>
				<div class="photo-filter-field photo-filter-field-mode">
					<fieldset>
						<legend><?=GetMessage("P_SHOW_FILTER")?></legend>
						<?if ($arParams["MODERATE"] == "Y"):?>
						<label for="mode_public" title="<?=GetMessage("P_SHOW_ONLY_NOT_PUBLIC_TITLE".($arParams["SHOW_ONLY_PUBLIC"] == "Y" ? "1" : ""))?>">
							<input type="radio" name="mode" value="public" id="mode_public" <?=($arResult["MODE"] == "public" ? " checked='checked'" : "")?> />
							<?=GetMessage("P_SHOW_ONLY_NOT_PUBLIC")?></label>
						<?endif;?>
						<label for="mode_active" title="<?=GetMessage("P_SHOW_ONLY_NOT_ACTIVE_TILTE")?>">
							<input type="radio" name="mode" value="active" id="mode_active" <?=($arResult["MODE"] == "active" ? " checked='checked'" : "")?> />
							<?=GetMessage("P_SHOW_ONLY_NOT_ACTIVE")?></label>
						<label for="mode_simple" title="<?=GetMessage("P_SHOW_SIMPLE_TITLE")?>">
							<input type="radio" name="mode" value="simple" id="mode_simple" <?=($arResult["MODE"] == "simple" ? " checked='checked'" : "")?> />
							<?=GetMessage("P_SHOW_SIMPLE")?></label>
					</fieldset>
				</div>
				<?endif;?>
				<div class="photo-filter-field-buttons">
					<input type="submit" name="photo_filter_submit" value="<?=GetMessage("P_FILTER_SHOW")?>" />
					<input type="submit" name="photo_filter_reset" value="<?=GetMessage("P_FILTER_RESET")?>" />
				</div>
			</form>
		</div>
		<div id="photo-filter-footer"></div>
	</div>
</div>

<div id="detail_list_order">
<?
$arFilter = array();
if ($arParams["PERMISSION"] >= "U" && $arResult["MODE"] == "public"):

	if ($arParams["SHOW_ONLY_PUBLIC"] == "Y"):
		$arFilter["PROPERTY_PUBLIC_ELEMENT"] = "Y";
	endif;
	$arFilter["PROPERTY_APPROVE_ELEMENT"] = "X";

elseif ($arParams["PERMISSION"] >= "U" && $arResult["MODE"] == "active"):

	$arFilter["ACTIVE"] = "N";

elseif ($arParams["PERMISSION"] >= "U" && $arResult["MODE"] == "simple"):

	$arFilter["ACTIVE"] = "Y";

elseif ($_REQUEST["AJAX_CALL"] == "Y"):
	if ($arParams["SHOW_ONLY_PUBLIC"] == "Y"):
		$arFilter["PROPERTY_PUBLIC_ELEMENT"] = "Y";
	endif;
	if ($arParams["MODERATE"] == "Y"):
		$arFilter["PROPERTY_APPROVE_ELEMENT"] = "Y";
	endif;
endif;

if ($arResult["ORDER_BY"] == "shows")
	$arFilter[">SHOW_COUNTER"] = "0";
elseif ($arResult["ORDER_BY"] == "rating")
	$arFilter[">PROPERTY_RATING"] = "0";
elseif ($arResult["ORDER_BY"] == "comments")
{
	if ($arParams["COMMENTS_TYPE"] == "blog")
		$arFilter[">PROPERTY_BLOG_COMMENTS_CNT"] = "0";
	elseif ($arParams["COMMENTS_TYPE"] == "forum")
		$arFilter[">PROPERTY_FORUM_MESSAGE_CNT"] = "0";
}

if ($arParams["PERMISSION"] >= "U" && ($arResult["MODE"] == "public" || $arResult["MODE"] == "active")):
?>
<div id="photogallery_hidden_actions" class="photo-controls" style="display:none;">
<?
	if ($arResult["MODE"] == "public"):
?>
	<a href="#moderate" onclick="return act('approve');" class="photo-action photo-moderate"><?=GetMessage("P_PUBLIC")?></a>
	<a href="#moderate" onclick="return act('not_approve');" class="photo-action photo-moderate"><?=GetMessage("P_NOT_PUBLIC")?></a>
<?
	else:
?>
	<a href="#moderate" onclick="return act('active');" class="photo-action photo-moderate"><?=GetMessage("P_SHOW")?></a>
<?
	endif;
?>
	<a href="#moderate" onclick="return act('drop');" class="photo-action delete"><?=GetMessage("P_DELETE")?></a>
</div>

<script type="text/javascript">
__photo_counter = 0;
function to_show_pannel()
{
	window.__photo_counter++;
	var error = false;
	if (document.getElementById('select_all1') && document.getElementById('photogallery_hidden_actions')) {
		try{
			var _div = document.getElementById('select_all1').parentNode.parentNode;
			var element = document.getElementById('select_all1').parentNode;
			while (element = element.nextSibling){
				if (element && element.className == 'empty-clear'){
					element.parentNode.removeChild(element);
					break;}
			}
			_div.innerHTML += document.getElementById('photogallery_hidden_actions').innerHTML;
			_div.innerHTML += '<div class="empty-clear"></div>';
			return true;
		}catch(e){error = true;}
	}
	if (error || window.__photo_counter > 10) {
//		document.getElementById('photogallery_hidden_actions').style.display = 'block';
		return false;}
	setTimeout(to_show_pannel, 100);
}
setTimeout(to_show_pannel, 100);

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

function act(action)
{
	var form = document.getElementById('photoForm');
	if (!action){}
	else if (!__check_form(form, 'items[]')){}
	else if (action == 'drop' && !confirm('<?=CUtil::JSEscape(GetMessage("P_DELETE_CONFIRM"))?>')){}
	else
	{
		var input = document.createElement('INPUT');
		input.type = "hidden";
		input.name = 'from_detail_list';
		input.value = '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("", array("ACTION", "sessid", "edit", "photo_filter_submit")))?>';
		form.appendChild(input);
		form.elements['ACTION'].value = action;
		form.submit();
	}
	return false;
}
</script>
<?
endif;

if ($_REQUEST["AJAX_CALL"] == "Y"):
	$APPLICATION->RestartBuffer();
endif;

ob_start();
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list",
	($_REQUEST["AJAX_CALL"] == "Y" ? "ascetic" : $arParams["TEMPLATE_LIST"]),
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"USER_ALIAS" => "",
		"BEHAVIOUR" => "USER",

		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TYPE" => (!empty($arResult["PERIOD_FROM"]) || !empty($arResult["PERIOD_TO"]) ? "period" : ""),
		"ELEMENTS_LAST_TIME_FROM"	=>	$arResult["PERIOD_FROM"],
		"ELEMENTS_LAST_TIME_TO"	=>	$arResult["PERIOD_TO"],
		"ELEMENT_SORT_FIELD"	=>	($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? "created_date" : $arResult["ORDER_BY"]),
		"ELEMENT_SORT_ORDER"	=>	"desc",
		"ELEMENT_SORT_FIELD1"	=>	($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? $arResult["ORDER_BY"] : ""),
		"ELEMENT_SORT_ORDER1"	=>	"desc",
		"ELEMENT_FILTER" => $arFilter,

		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],

		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],

		"USE_DESC_PAGE"	=>	$arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS"	=>	($_REQUEST["AJAX_CALL"] == "Y" ? "10" : $arParams["ELEMENTS_PAGE_ELEMENTS"]),
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],

		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT"	=>	"standart",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "Y",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],

		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	($_REQUEST["AJAX_CALL"] == "Y" ? "none" : "bottom"),

		"SHOW_CONTROLS"	=>	"N",
		"SHOW_INPUTS" => ($arParams["PERMISSION"] >= "U" && ($arResult["MODE"] == "public" || $arResult["MODE"] == "active")),
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],

		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"SHOW_RATING" => $arParams["USE_RATING"],
		"SHOW_COMMENTS" => $arParams["USE_COMMENTS"],
		"SHOW_SHOWS" => "Y",
		"SHOW_DATE" => $arResult["GROUP_BY_DATE_CREATE"],
		"NEW_DATE_TIME_FORMAT" => (empty($arParams["DATE_FORMAT"]) ? $arParams["DATE_TIME_FORMAT_DETAIL"] : $arParams["DATE_FORMAT"]),
		"SET_STATUS_404" => "N",

		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
$new = ob_get_clean();
$new = trim($new);

if (!empty($new)):
	?><?=$new?><?
endif;

if ($_REQUEST["AJAX_CALL"] == "Y"):
	if (empty($new)):
		?><div class="no-photo-text"><?=GetMessage("P_NO_PHOTO");?></div><?
	endif;
	?><div class="all-elements"><noindex><a rel="nofollow" href="<?=($APPLICATION->GetCurPageParam("", array("AJAX_CALL")))?>"><?
		if ($arResult["ORDER_BY"] == "date_create"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_DATE_CREATE")?><?
		elseif ($arResult["ORDER_BY"] == "shows"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_SHOWS")?><?
		elseif ($arResult["ORDER_BY"] == "rating"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_RATING")?><?
		elseif ($arResult["ORDER_BY"] == "comments"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_COMMENTS")?><?
		endif;
	?></a></noindex></div><?
	die();
endif;
//GetMessage("P_SHOW_ONLY_NOT_PUBLIC_TITLE1");
?></div>