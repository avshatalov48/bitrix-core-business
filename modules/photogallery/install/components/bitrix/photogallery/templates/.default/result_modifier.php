<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["MENU_VARIABLES"] = array();
if ($this->__page !== "menu")
{
	// Include breadcrumbs navigation to the top of all component pages
	if ($arParams["SHOW_NAVIGATION"] == "Y")
	{
		$sTempatePage = $this->__page;
		$sTempateFile = $this->__file;
		$this->__component->IncludeComponentTemplate("menu");
		$this->__page = $sTempatePage;
		$this->__file = $sTempateFile;
	}

	/************** Themes *********************************************/
	$arThemes = array();
	$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", dirname(realpath(__FILE__))."/");
	$dir = $sTemplateDirFull."themes/";
	if (is_dir($dir) && $directory = opendir($dir))
	{
		while (($file = readdir($directory)) !== false)
		{
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arThemes[] = $file;
		}
		closedir($directory);
	}
	$sTemplateDir = preg_replace("'[\\\\/]+'", "/", $this->__component->__template->__folder."/");
	$arParams["THEME"] = trim($arParams["THEME"]);
	if (!empty($arParams["THEME"]) && !in_array($arParams["THEME"], $arThemes))
	{
		$val = str_replace(array("\\", "//"), "/", "/".$arParams["THEME"]."/");
		$arParams["THEME"] = "";
		if (is_file($_SERVER['DOCUMENT_ROOT'].$val."style.css"))
			$arParams["THEME"] = $val;
	}
	$arParams["THEME"] = (empty($arParams["THEME"]) && in_array("gray", $arThemes) ? "gray" : $arParams["THEME"]);
	if (!empty($arParams["THEME"]))
	{
		if (in_array($arParams["THEME"], $arThemes))
		{
			$date = @filemtime($dir.$arParams["THEME"]."/style.css");
			$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'themes/'.$arParams["THEME"].'/style.css?'.$date);
		}
		else
		{
			$date = @filemtime($_SERVER['DOCUMENT_ROOT'].$arParams["THEME"]."/style.css");
			$GLOBALS['APPLICATION']->SetAdditionalCSS($arParams["THEME"].'/style.css?'.$date);
		}
	}

	//$date = @filemtime($sTemplateDirFull."styles/additional.css");
	//$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'styles/additional.css?'.$date);
	//$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/themes/.default/pubstyles.css');
/************** Themes/*********************************************/
?>

<style>
div.photo-album-avatar{
	width:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;
	height:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;}
div.photo-item-cover-block-container,
div.photo-item-cover-block-outer,
div.photo-item-cover-block-inner{
	background-color: white;
	height:<?=($arParams["ALBUM_PHOTO_SIZE"] + 16)?>px;
	width:<?=($arParams["ALBUM_PHOTO_SIZE"] + 40)?>px;}
div.photo-album-thumbs-avatar{
	width:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px;
	height:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px;}
ul.photo-album-list div.photo-item-info-block-outside {
	width: <?=($arParams["ALBUM_PHOTO_SIZE"] + 48)?>px;}
ul.photo-album-thumbs-list div.photo-item-info-block-inner {
	width:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"] + 48)?>px;}
</style>
<?
}
// else
// {
	// return true;
// };
?>