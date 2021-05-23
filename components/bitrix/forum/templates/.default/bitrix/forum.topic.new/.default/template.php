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
$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 500);
$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" && IsModuleInstalled("vote") ? "Y" : "N");
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
elseif ($arResult["SHOW_MESSAGE_FOR_AJAX"] == "Y"):
	ob_end_clean();
	ob_start();
	$GLOBALS["bShowImageScriptPopup"] = true;
	?><?$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:forum.message.template",
		".preview",
		Array(
			"MESSAGE" => $arResult["MESSAGE"],
			"ATTACH_MODE" => $arParams["ATTACH_MODE"],
			"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
			"arResult" => $arResult,
			"arParams" => $arParams
		),
		$component->__parent,
		array("HIDE_ICONS" => "Y")
	);?><?
	if(!function_exists("__ConvertData"))
	{
		function __ConvertData(&$item, $key)
		{
			static $search = array("&#92;");
			static $replace = array("&amp;#92;");
			if(is_array($item))
				array_walk($item, "__ConvertData");
			else
			{
				$item = htmlspecialcharsbx($item);
				$item = str_replace($search, $replace, $item);
			}
		}
	}
	
	$post =
	$res = array("id" => $arParams["MID"], "post" => ob_get_clean());
	if ($_REQUEST["CONVERT_DATA"] == "Y")
		array_walk($res, "__ConvertData");
$GLOBALS["APPLICATION"]->RestartBuffer();
?><?=CUtil::PhpToJSObject($res)?><?
die();
endif;
?>