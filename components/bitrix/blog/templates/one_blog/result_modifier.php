<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult["VARIABLES"]["blog"] = $arParams["BLOG_URL"];
$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/blog/templates/.default/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;

$arParams["THEME"] = trim($arParams["THEME"]);
$arParams["THEME"] = (in_array($arParams["THEME"], $arThemes) ? $arParams["THEME"] : (in_array("blue", $arThemes) ? "blue" : $arThemes[0]));

$arParams["NAV_TEMPLATE"] = trim($arParams["NAV_TEMPLATE"]);
$arParams["NAV_TEMPLATE"] = (empty($arParams["NAV_TEMPLATE"]) ? "blog" : $arParams["NAV_TEMPLATE"]);

if (!empty($arParams["THEME"]))
{
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/'.$arParams["THEME"].'/style.css');
}
?>