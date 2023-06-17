<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
if (!empty($arResult["ERROR_MESSAGE"])):
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"] ?? 0) > 0 ? $arParams["IMAGE_SIZE"] : 500);
$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" && IsModuleInstalled("vote") ? "Y" : "N");
$arParams["ATTACH_MODE"] = array("NAME", "THUMB");
$arParams["ATTACH_SIZE"] = $arParams["IMAGE_SIZE"];
/********************************************************************
				/Input params
********************************************************************/
if ($arResult["VIEW"] == "Y"):
	?><?$GLOBALS["APPLICATION"]->IncludeComponent(
	"bitrix:forum.message.template",
	".preview",
	Array(
		"MESSAGE" => $arResult["MESSAGE_VIEW"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"arResult" => $arResult,
		"arParams" => $arParams
	),
	$component->__parent,
	array("HIDE_ICONS" => "Y")
);?><?
elseif (($arResult["SHOW_MESSAGE_FOR_AJAX"] ?? '') === "Y"):

	ob_end_clean();
	ob_start();
	$GLOBALS["bShowImageScriptPopup"] = true;
?>
<div class="forum-post-text" id="message_text_<?=$arResult["MESSAGE"]["ID"]?>"><?=$arResult["MESSAGE"]["POST_MESSAGE_TEXT"]?></div>
<?
if (!empty($arResult["MESSAGE"]["FILES"])):
?>
	<div class="forum-post-attachments">
		<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
	foreach ($arResult["MESSAGE"]["FILES"] as $arFile):
?>
		<div class="forum-post-attachment"><?
		?><?$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface", "show_file",
			Array(
				"FILE" => $arFile,
				"WIDTH" => $arParams["IMAGE_SIZE"],
				"HEIGHT" => $arParams["IMAGE_SIZE"],
				"CONVERT" => "N",
				"FAMILY" => "FORUM",
				"SINGLE" => "Y",
				"RETURN" => "N",
				"SHOW_LINK" => "Y"),
			null,
			array("HIDE_ICONS" => "Y"));
		?></div>
<?
	endforeach;
?>
	</div>
<?
endif;

if (!empty($arResult["MESSAGE"]["EDITOR_NAME"])):
?><div class="forum-post-lastedit">
	<span class="forum-post-lastedit"><?=GetMessage("F_EDIT_HEAD")?>
		<span class="forum-post-lastedit-user"><?
	if (!empty($arResult["MESSAGE"]["EDITOR_LINK"])):
		?><a href="<?=$arResult["MESSAGE"]["EDITOR_LINK"]?>"><?=$arResult["MESSAGE"]["EDITOR_NAME"]?></a><?
	else:
		?><?=$arResult["MESSAGE"]["EDITOR_NAME"]?><?
	endif;
	?></span> - <span class="forum-post-lastedit-date"><?=$arResult["MESSAGE"]["EDIT_DATE"]?></span>
	<span class="forum-post-lastedit-reason">(<span><?=$arResult["MESSAGE"]["EDIT_REASON"]?></span>)</span></span>
</div><?
endif;

	if(!function_exists("__ConverData"))
	{
		function __ConverData(&$item, $key)
		{
			static $search = array("&#92;");
			static $replace = array("&amp;#92;");
			if(is_array($item))
				array_walk($item, "__ConverData");
			else
			{
				$item = htmlspecialcharsEx($item);
				$item = str_replace($search, $replace, $item);
			}
		}
	}

	$post = ob_get_contents();
	ob_end_clean();
	$res = array(
		"id" => $arParams["MID"],
		"post" => $post);
	if (isset($_REQUEST["CONVERT_DATA"]) && $_REQUEST["CONVERT_DATA"] == "Y")
		array_walk($res, "__ConverData");

	$GLOBALS["APPLICATION"]->RestartBuffer();
	?><?=CUtil::PhpToJSObject($res)?><?
	die();

endif;
?>