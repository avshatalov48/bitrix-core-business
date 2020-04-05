<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arThemes = array();
$dir = preg_replace("'[\\\\/]+'", "/", dirname(realpath(__FILE__))."/themes/");
if (is_dir($dir) && $directory = opendir($dir))
{
	while (($file = readdir($directory)) !== false)
	{
		if ($file == "." || $file == ".." || is_dir($dir.$file))
			continue;
		if (substr($file, -4, 4) == ".css")
			$arThemes[] = substr($file, 0, strlen($file) - 4);
	}
	closedir($directory);
}

$this->InitComponentTemplate();
$sTemplateDir = preg_replace("'[\\\\/]+'", "/", $this->__template->__folder."/");
$arParams["THEME"] = trim($arParams["THEME"]);
$arParams["THEME"] = (in_array($arParams["THEME"], $arThemes) ? $arParams["THEME"] : "");

if (in_array($arParams["THEME"], $arThemes))
{
	$date = @filemtime($dir.$arParams["THEME"].".css");
	$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'themes/'.$arParams["THEME"].'.css?'.$date);

} elseif (!empty($arParams["THEME"])) {

	$date = @filemtime($_SERVER['DOCUMENT_ROOT'].$arParams["THEME"]."/style.css");
	if ($date)
		$GLOBALS['APPLICATION']->SetAdditionalCSS($arParams["THEME"].'/style.css?'.$date);
}
?>

