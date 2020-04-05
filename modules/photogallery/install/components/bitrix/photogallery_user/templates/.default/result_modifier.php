<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// TODO: Get rid of this code and put it to the detail_list component
if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_REQUEST["from_detail_list"]))
{
	if ($arParams["PERMISSION"] < "U")
	{
	}
	elseif (!check_bitrix_sessid())
	{
	}
	elseif (!is_array($_REQUEST["items"]) || empty($_REQUEST["items"]))
	{
	}
	else
	{
		CModule::IncludeModule("photogallery");
		CModule::IncludeModule("iblock");
		$arSections = array();
		@set_time_limit(0);
		foreach ($_REQUEST["items"] as $item)
		{
			$db_res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => $item), false, false, 
				array("ID", "ACTIVE", "IBLOCK_SECTION_ID", "PROPERTY_REAL_PICTURE"));
			if (!($db_res && $res = $db_res->Fetch()))
				continue;

			if ($_REQUEST["ACTION"] == "approve" || $_REQUEST["ACTION"] == "not_approve")
			{
				if ($_REQUEST["ACTION"] == "approve")
				{
					CIBlockElement::SetPropertyValues($item, $arParams["IBLOCK_ID"], "Y", "APPROVE_ELEMENT");
					CIBlockElement::SetPropertyValues($item, $arParams["IBLOCK_ID"], "Y", "PUBLIC_ELEMENT");
				}
				else
				{
					CIBlockElement::SetPropertyValues($item, $arParams["IBLOCK_ID"], "N", "APPROVE_ELEMENT");
				}
				if ($res["ACTIVE"] != "Y")
					$_REQUEST["ACTION"] = "active";
				PClearComponentCacheEx($arParams["IBLOCK_ID"]);
			}

			if ($_REQUEST["ACTION"] == "active" || $_REQUEST["ACTION"] == "drop")
			{
				if ($_REQUEST["ACTION"] == "active")
				{
					$arFields = array("ACTIVE" => "Y");
					$be = new CIBlockElement;
					$be->Update($item, $arFields);
				}
				else
				{
					CIBlockElement::Delete($item);
				}
				// section 
				if (is_set($arSections, $res["IBLOCK_SECTION_ID"]))
					$res = $arSections[$res["IBLOCK_SECTION_ID"]];

				PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0,$res["ID"], $res["IBLOCK_SECTION_ID"]));
			}
		}
		LocalRedirect($_REQUEST["from_detail_list"]);
	}
}

$arParams["SHOW_BEST_ELEMENT"] = ($arParams["SHOW_BEST_ELEMENT"] == "N" ? "N" : "Y");
$arResult["MENU_VARIABLES"] = array();
if ($this->__page !== "menu")
{
	// Include breadcrumbs navigation to the top of all component pages
	$sTempatePage = $this->__page;
	$sTempateFile = $this->__file;
	$this->__component->IncludeComponentTemplate("menu");
	$this->__page = $sTempatePage;
	$this->__file = $sTempateFile;

	$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/components/bitrix/photogallery/templates/.default/style.css");

	/************** Themes *********************************************/
	$arThemes = array();
	$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", dirname(realpath(__FILE__))."/");
	$dir = $sTemplateDirFull."themes/";
	if (is_dir($dir) && $directory = opendir($dir))
	{
		while (($file = readdir($directory)) !== false)
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arThemes[] = $file;
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
/************** Themes/*********************************************/
?>

<?if(!isset($_REQUEST["photo_list_action"]) || !$_REQUEST["photo_list_action"]):?>
<style>
div.photo-item-cover-block-container, 
div.photo-item-cover-block-outer, 
div.photo-item-cover-block-inner{
	background-color: white;
	height:<?=($arParams["ALBUM_PHOTO_SIZE"] + 16)?>px;
	width:<?=($arParams["ALBUM_PHOTO_SIZE"] + 40)?>px;}
.photo-album-thumbs-list div.photo-item-cover-block-container, 
.photo-album-thumbs-list div.photo-item-cover-block-outer, 
.photo-album-thumbs-list div.photo-item-cover-block-inner{
	background-color: white;
	height:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"] + 16)?>px;
	width:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"] + 40)?>px;}
div.photo-gallery-avatar{
	width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;
	height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;}
div.photo-album-avatar{
	width:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;
	height:<?=$arParams["ALBUM_PHOTO_SIZE"]?>px;}
div.photo-album-thumbs-avatar{
	width:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px;
	height:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px;}
ul.photo-album-list div.photo-item-info-block-outside {
	width: <?=($arParams["ALBUM_PHOTO_SIZE"] + 48)?>px;}
ul.photo-album-thumbs-list div.photo-item-info-block-inner {
	width:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"] + 48)?>px;}
div.photo-body-text-ajax{
	height:<?=intVal($arParams["THUMBNAIL_SIZE"] * $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]/100 + 39)?>px;
	padding-top:<?=intVal($arParams["THUMBNAIL_SIZE"] * $arParams["INDEX_PAGE_TOP_ELEMENTS_PERCENT"]/200)?>px;
	text-align:center;}
</style>
<?endif; /*(!isset($_REQUEST["photo_list_action"]) || !$_REQUEST["photo_list_action"])*/?>

<?
	$arResult["MENU_VARIABLES"] = $this->__component->__photogallery_values;
	if (!is_array($arResult["MENU_VARIABLES"]))
	{
		return false;
	}
	elseif (empty($arResult["MENU_VARIABLES"]["USER_ALIAS"]))
	{
		//CHTTP::SetStatus("404 Not Found");
		return false;
	}
	elseif (($arResult["VARIABLES"]["USER_ALIAS"] && $arResult["VARIABLES"]["USER_ALIAS"] != $arResult["MENU_VARIABLES"]["USER_ALIAS"]) || $arResult["VARIABLES"]["SECTION_ID"] != $arResult["MENU_VARIABLES"]["SECTION_ID"])
	{
		if ($arParams["SEF_MODE"] != "Y")
		{
			$url = $GLOBALS["APPLICATION"]->GetCurPageParam(
				$arResult["ALIASES"]["USER_ALIAS"]."=".$arResult["MENU_VARIABLES"]["USER_ALIAS"]."&".
					$arResult["ALIASES"]["SECTION_ID"]."=".$arResult["MENU_VARIABLES"]["SECTION_ID"], 
				array($arResult["ALIASES"]["USER_ALIAS"], $arResult["ALIASES"]["SECTION_ID"]));
		}
		else
		{
			$res = $arResult["VARIABLES"];
			$res["USER_ALIAS"] = $arResult["MENU_VARIABLES"]["USER_ALIAS"];
			$res["SECTION_ID"] = $arResult["MENU_VARIABLES"]["SECTION_ID"];
			$url = CComponentEngine::MakePathFromTemplate($arResult["URL_TEMPLATES"][$sTempatePage], $res);
		}
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	}
}
else
{
	return true;
}
?>