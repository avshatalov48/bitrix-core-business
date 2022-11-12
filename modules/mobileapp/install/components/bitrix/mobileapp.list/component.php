<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("MAPP_ML_MOBILEAPP_NOT_INSTALLED");
	return;
}

if(empty($arParams["ITEMS"]) || !is_array($arParams["ITEMS"]))
	return;

$arResult["ITEMS"] = $arParams["ITEMS"];

if (isset($_REQUEST['ajax_mode']) && $_REQUEST['ajax_mode'] == 'Y')
{
	$arResult["AJAX_MODE"] = true;
}
else
{
	$arResult["AJAX_MODE"] = false;
	$arResult["AJAX_PATH"] = $componentPath."/ajax.php";
	$arResult["JS_EVENT_ITEM_CHANGE"] = isset($arParams["JS_EVENT_ITEM_CHANGE"]) ? $arParams["JS_EVENT_ITEM_CHANGE"] : false;
	$arResult["JS_EVENT_BOTTOM_REACHED"] = isset($arParams["JS_EVENT_BOTTOM_REACHED"]) ? $arParams["JS_EVENT_BOTTOM_REACHED"] : 'mappJsEventListBottomReached';
	$arResult["MAPP_LIST_PRELOAD_START"] = $arResult["MAPP_LIST_PRELOAD_START"] ? $arParams["MAPP_LIST_PRELOAD_START"] : 1;
}

$Sanitizer = new CBXSanitizer();
$Sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_LOW);
array_walk_recursive($arResult["ITEMS"], 'sanitizeInputData', $Sanitizer);

$this->IncludeComponentTemplate();

function sanitizeInputData(&$item, $key, $Sanitizer)
{
	if($key === 'DETAIL_LINK')
	{
		$urlObject = parse_url($item);
		$host = $urlObject["host"];
		if ($host)
		{
			$origin = parse_url("https://" .$_SERVER["HTTP_HOST"]);
			if ($origin["host"] !== $urlObject["host"])
			{
				$item = "";
			}
		}
		$linkItem = '<a href="'.$item.'">test</a>';
		if($linkItem != $Sanitizer->SanitizeHtml($linkItem))
			$item = '';
	}
	else
	{

		$item = $Sanitizer->SanitizeHtml($item);
	}

}

?>