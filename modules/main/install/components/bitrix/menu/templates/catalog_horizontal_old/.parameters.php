<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arThemes = array();

$arThemes["site"] = GetMessage("F_THEME_SITE");

$arThemesMessages = array(
	"blue" => GetMessage("F_THEME_BLUE"),
	"wood" => GetMessage("F_THEME_WOOD"),
	"yellow" => GetMessage("F_THEME_YELLOW"),
	"green" => GetMessage("F_THEME_GREEN"),
	"red" => GetMessage("F_THEME_RED"),
	"black" => GetMessage("F_THEME_BLACK")
);
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir))
{
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : strtoupper(substr($file, 0, 1)).strtolower(substr($file, 1)));
	}
	closedir($directory);
}
$arTemplateParameters = array(
	"MENU_THEME"=>array(
		"NAME" => GetMessage("MENU_THEME"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"PARENT" => "BASE",
	)
);
?>
