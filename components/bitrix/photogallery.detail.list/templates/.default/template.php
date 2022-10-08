<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
if (empty($arResult["ELEMENTS_LIST"])):
	return true;
elseif (!$this->__component->__parent || mb_strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slider_big/script_cursor.js");
if ($GLOBALS['USER']->IsAuthorized()):
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/admin_tools.js');
endif;
CAjax::Init();
$arTemplates = array(
	"default" => GetMessage("P_DEFAULT_TEMPLATE"),
	"square" => GetMessage("P_SQUARE_TEMPLATE"),
	"rectangle" => GetMessage("P_RECTANGLE_TEMPLATE"),
	"table" => "table",
	"ascetic" => "ascetic");
/********************************************************************
				Input params
********************************************************************/
// PICTURE
$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBNAIL_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBNAIL_SIZE"] = (intval($temp["WIDTH"]) > 0 ? intval($temp["WIDTH"]) : 120);
if ($arParams["PICTURES_SIGHT"] != "standart" && intval($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]) > 0)
	$arParams["THUMBNAIL_SIZE"] = $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"];
$arParams["PERCENT"] = (intval($arParams["PERCENT"]) > 0 ? intval($arParams["PERCENT"]) : 60);
$arParams["percent_width"] = $arParams["percent_height"] = 100;
$arParams["ID"] = md5(serialize(array("default", $arParams["FILTER"], $arParams["SORTING"])));

$arParams["~TEMPLATE"] = trim(mb_strtolower($arParams["TEMPLATE"]));
$arParams["~TEMPLATE"] = ($arParams["~TEMPLATE"] == ".default" ? "default" : $arParams["~TEMPLATE"]);
$arParams["~TEMPLATE"] = (array_key_exists($arParams["~TEMPLATE"], $arTemplates) ? $arParams["~TEMPLATE"] : "");
if ($arParams["~SQUARE"] == "Y")
{
	$arParams["~TEMPLATE"] = "ascetic";
}
if (!empty($arParams["~TEMPLATE"]))
{
	$arParams["TEMPLATE"] = $arParams["~TEMPLATE"];
}
else
{
	$arParams["TEMPLATE_DEFAULT"] = (empty($arParams["TEMPLATE_DEFAULT"]) ? "square" : $arParams["TEMPLATE_DEFAULT"]);
	$arParams["TEMPLATE_DEFAULT"] = ($arParams["TEMPLATE_DEFAULT"] == ".default" ? "default" : $arParams["TEMPLATE_DEFAULT"]);
	$arParams["TEMPLATE_DEFAULT"] = (array_key_exists($arParams["TEMPLATE_DEFAULT"], $arTemplates) ? $arParams["TEMPLATE_DEFAULT"] : "square");

	if ($GLOBALS['USER']->IsAuthorized())
	{
		$arTemplateParams = CUserOptions::GetOption('photogallery', 'template');
		$arTemplateParams = (!is_array($arTemplateParams) ? array() : $arTemplateParams);
		$arParams["TEMPLATE"] = $arTemplateParams['template'];
		if ($_REQUEST["template"] && check_bitrix_sessid() && $arTemplateParams["template"] != $_REQUEST["template"])
		{
			$arTemplateParams['template'] = $arParams["TEMPLATE"] = $_REQUEST["template"];
			CUserOptions::SetOption('photogallery', 'template', $arTemplateParams);
		}
	}
	else
	{
		if (!empty($_SESSION['photogallery']['template']))
			$arParams["TEMPLATE"] = $_SESSION['photogallery']['template'];
		if (!empty($_REQUEST["template"]))
			$_SESSION['photogallery']['template'] = $arParams["TEMPLATE"] = $_REQUEST["template"];
	}
	$arParams["TEMPLATE"] = (array_key_exists($arParams["TEMPLATE"], $arTemplates) ? $arParams["TEMPLATE"] : $arParams["TEMPLATE_DEFAULT"]);
}

$sTemplateName = "default";
if ($arParams["TEMPLATE"] == "square" || $arParams["TEMPLATE"] == "ascetic"):
	$arParams["percent_width"] = $arParams["percent_height"] = $arParams["PERCENT"];
	$sTemplateName = "ascetic";
elseif ($arParams["TEMPLATE"] == "rectangle"):
	$arParams["percent_width"] = 0;
	$arParams["percent_height"] = $arParams["PERCENT"];
	$sTemplateName = "ascetic";
elseif ($arParams["TEMPLATE"] == "table"):
	$sTemplateName = "table";
endif;
if ($sTemplateName != "default"):
	$arParams["MAX_WIDTH"] = $arParams["MAX_HEIGHT"] = $arParams["THUMBNAIL_SIZE"];
else:
	$arParams["MAX_WIDTH"] = ($arResult["ELEMENTS"]["MAX_WIDTH"] < $arParams["THUMBNAIL_SIZE"] ? $arResult["ELEMENTS"]["MAX_WIDTH"] : $arParams["THUMBNAIL_SIZE"]);
	$arParams["MAX_HEIGHT"] = ($arResult["ELEMENTS"]["MAX_HEIGHT"] < $arParams["THUMBNAIL_SIZE"] ? $arResult["ELEMENTS"]["MAX_HEIGHT"] : $arParams["THUMBNAIL_SIZE"]);
endif;

$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["SHOW_SHOWS"] = ($arParams["SHOW_SHOWS"] == "Y" ? "Y" : "N");
$arParams["SHOW_COMMENTS"] = ($arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");
$arParams["COMMENTS_TYPE"] = (mb_strtolower($arParams["COMMENTS_TYPE"]) == "forum" ? "forum" : "blog");
$arParams["SHOW_DATETIME"] = ($arParams["SHOW_DATETIME"] == "Y" ? "Y" : "N");
$arParams["SHOW_ANCHOR"] = $arResult["USER_HAVE_ACCESS"];
$arParams["SHOW_DESCRIPTION"] = ($arParams["SHOW_DESCRIPTION"] == "Y" ? "Y" : "N");

// PAGE
$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ?
		$arParams["SHOW_PAGE_NAVIGATION"] : "bottom");
$arParams["NEW_DATE_TIME_FORMAT"] = trim(!empty($arParams["NEW_DATE_TIME_FORMAT"]) ? $arParams["NEW_DATE_TIME_FORMAT"] :
	$DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
// FORM & CONTROLS
$arParams["SHOW_FORM"] = (($arParams["SHOW_INPUTS"] == "Y" || $arParams["SHOW_CONTROLS"] == "Y" || $arParams["SHOW_FORM"] == "Y") &&
	$arParams["PERMISSION"] >= "U" ? "Y" : "N");
$arParams["GROUP_DATE"] = ($arParams["GROUP_DATE"] == "Y" ? "Y" : "N");
/********************************************************************
				Input params
********************************************************************/
$arParams["mode"] = ($arParams["SHOW_FORM"] == "Y" ? "edit" : "view");
$_REQUEST["items"] = (is_array($_REQUEST["items"]) ? $_REQUEST["items"] : array());
/********************************************************************
				Actions
********************************************************************/
include_once(str_replace(array("\\", "//"), "/", __DIR__."/template_".$sTemplateName.".php"));
/********************************************************************
				/Actions
********************************************************************/
if (!empty($arResult["ERROR_MESSAGE"])):
?>
<div class="photo-error">
	<?=ShowError($arResult["ERROR_MESSAGE"])?>
</div>
<?
endif;

if (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("top", "both")) && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;

// Pictures Sights
if (empty($arParams["~TEMPLATE"]) || !empty($arParams["PICTURES"]))
{
?>
<script type="text/javascript">
var phpVars;
if (typeof(phpVars) != "object")
	var phpVars = {};
phpVars.bitrix_sessid = '<?=bitrix_sessid()?>';
</script>
<noindex>
<div class="photo-controls photo-controls-photo-top">
	<ul class="photo-controls">
<?
	include_once(str_replace(array("\\", "//"), "/", __DIR__."/template_resizer.php"));
	if (empty($arParams["~TEMPLATE"]))
	{
?>
		<li class="photo-control <?=(empty($arParams["PICTURES"]) ? " photo-control-first " : "")?> photo-control-photo-templates">
			<span>
				<ul class="photo-controls photo-control-photo-templates">
					<li class="photo-control-photo-template-square<?=($arParams["TEMPLATE"] == "square" ? " photo-control-photo-template-square-active" : "")?>">
						<a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam(
							"template=square".($GLOBALS["USER"]->IsAuthorized() ? "&".bitrix_sessid_get() : ""),
							array("template", "sessid"))?>" <?
							?>title="<?=GetMessage("P_SQUARE_TEMPLATE_TITLE")?>" <?
							?>onclick="try {__photo_change_template(this, '<?=$arParams["ID"]?>');return false;} catch (e) {return true;}"><i><span><?=GetMessage("P_SQUARE_TEMPLATE")?></span></i></a>
					</li>
					<li class="photo-control-photo-template-rectangle<?=($arParams["TEMPLATE"] == "rectangle" ? " photo-control-photo-template-rectangle-active" : "")?>">
						<a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam(
							"template=rectangle".($GLOBALS["USER"]->IsAuthorized() ? "&".bitrix_sessid_get() : ""),
							array("template", "sessid"))?>" <?
							?>title="<?=GetMessage("P_RECTANGLE_TEMPLATE_TITLE")?>" <?
							?>onclick="try {__photo_change_template(this, '<?=$arParams["ID"]?>');return false;} catch (e) {return true;}"><i><span><?=GetMessage("P_RECTANGLE_TEMPLATE")?></span></i></a>
					</li>
					<li class="photo-control-photo-template-default<?=($arParams["TEMPLATE"] == "default" ? " photo-control-photo-template-default-active" : "")?>">
						<a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam(
							"template=default".($GLOBALS["USER"]->IsAuthorized() ? "&".bitrix_sessid_get() : ""),
							array("template", "sessid"))?>" <?
							?>title="<?=GetMessage("P_DEFAULT_TEMPLATE_TITLE")?>" <?
							?>onclick="try {__photo_change_template(this, '<?=$arParams["ID"]?>');return false;} catch (e) {return true;}"><i><span><?=GetMessage("P_DEFAULT_TEMPLATE")?></span></i></a>
					</li>
				</ul>
			</span>
		</li>
<?
	}
?>
	</ul>
	<div class="empty-clear"></div>
</div>
</noindex>
<?
}
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
endif;

$current_date = "";
?>

<div class="photo-items-list photo-photo-list" id="photo_list_<?=$arParams["ID"]?>">
<?
if ($_REQUEST["package_id"] == $arParams["ID"]):
	$APPLICATION->RestartBuffer();
endif;
?>
	<!-- Photo List <?=$arParams["ID"]?> -->
	<div class="empty-clear"></div>
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

	$title = (isset($arItem["PREVIEW_TEXT"]) && $arItem["PREVIEW_TEXT"] != '') ? $arItem["PREVIEW_TEXT"] : $arItem["NAME"];
	$arItem["TITLE"] = $title.($arItem["ACTIVE"] != "Y" ? GetMessage("P_PHOTO_NOT_APPROVED") : "");

	if ($arParams["SHOW_COMMENTS"] != "N")
		$arItem["COMMENTS"] = intval($arParams["COMMENTS_TYPE"] != "blog" ?
			$arItem["PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"] : $arItem["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"]);
	call_user_func("__photo_template_".$sTemplateName, $arItem, $arParams, $this);
endforeach;
?>
	<div class="empty-clear"></div>
	<!-- Photo List End <?=$arParams["ID"]?> -->
<?
if ($_REQUEST["package_id"] == $arParams["ID"]):
	die();
endif;

?>
</div>

<?
if ($arParams["SHOW_FORM"] == "Y"):
?>
	<noindex>
	<div class="photo-controls photo-controls-photo-bottom">
		<ul class="photo-controls">
			<li class="photo-control photo-control-first photo-control-photo-selectall">
				<span>
					<input type="checkbox" id="select_all1" onclick="SelectAll(this);" name="select_all" value="N" />
					<label for="select_all1"><?=GetMessage("P_SELECT_ALL")?></label>
				</span>
			</li>
			<li class="photo-control photo-control-photo-drop">
				<span><a href="#" onclick="Delete(this.firstChild.form); return false;"><input type="hidden" /><?=GetMessage("P_DELETE_SELECTED")?></a></span>
			</li>
			<li class="photo-control photo-control-last photo-control-photo-move" <?
				?>onclick="this.style.display='none'; this.nextSibling.style.display='block';">
					<span><a href="#" onclick="return false;"><?=GetMessage("P_MOVE_SELECTED")?></a></span>
			</li><?
			?><li class="photo-control photo-control-last photo-control-photo-move" style="display:none;">
				<span>
					<label for="TO_SECTION_ID"><?=GetMessage("P_MOVE_SELECTED_IN")?> </label>
					<select name="TO_SECTION_ID"><?
					foreach ($arResult["SECTIONS_LIST"] as $key => $val):
						?><option value="<?=$key?>" <?
							?> <?=((intval($arParams["SECTION_ID"]) == intval($key)) ? " selected='selected'" : "")?>><?=$val?></option><?
					endforeach;
					?></select><?
					?><input type="button" name="name_submit" value="OK" onclick="Move(this.form)"  style="margin-left:0.2em;" />
				</span>
			</li>
		</ul>
		<div class="empty-clear"></div>
	</div>
	</noindex>
</form>
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
function SelectAll(oObj)
{
	oObj.value = (oObj.value == 'N' ? 'Y' : 'N');
	for (var ii = 0; ii < oObj.form.elements.length; ii++) {
		if (oObj.form.elements[ii].name == 'items[]'){
				oObj.form.elements[ii].checked = (oObj.value == 'Y');
				if (oObj.form.elements[ii].onclick) {oObj.form.elements[ii].onclick();}}}
	return false;
}
</script>
<?
endif;

if (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("bottom", "both")) && !empty($arResult["NAV_STRING"])):
?>
	<div class="photo-navigation photo-navigation-bottom">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
endif;
if ($arParams["INCLUDE_SLIDER"] == "Y"):
	$this->__component->setTemplateName("slider_big");
	$this->__component->IncludeComponentTemplate();
endif;
?>