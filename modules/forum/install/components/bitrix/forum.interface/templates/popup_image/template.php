<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["URL"] = trim($arParams["~URL"]);
if (empty($arParams["URL"]))
	return false;
if ($arParams["CONVERT"] == "Y")
	$arParams["URL"] = htmlspecialcharsbx($arParams["URL"]);
// *************************/BASE **********************************************************************
// ************************* ADDITIONAL ****************************************************************
// True image size For example 1024x768
$img = array("width" => 0, "height" => 0);
if (array_key_exists("IMG_WIDTH", $arParams))
	$img = array("width" => intval($arParams["IMG_WIDTH"]), "height" => intval($arParams["IMG_HEIGHT"]));
else if (array_key_exists("WIDTH", $arParams))
	$img = array("width" => intval($arParams["WIDTH"]), "height" => intval($arParams["HEIGHT"]));
$img = array_merge($img, array("~width" => $img["width"], "~height" => $img["height"]));
// user size from image parameters [IMG URL='bla-bla' WIDTH=100 HEIGHT=100] to resize image on client
$arParams["SIZE"] = (is_array($arParams["SIZE"]) ? array_change_key_case($arParams["SIZE"], CASE_LOWER) : null);
if ($arParams["SIZE"] !== null && ($arParams["SIZE"]["width"] > 0 || $arParams["SIZE"]["height"] > 0))
{
	if ($arParams["SIZE"]["width"] > 0)
		$img["width"] = $arParams["SIZE"]["width"];
	if ($arParams["SIZE"]["height"] > 0)
		$img["height"] = $arParams["SIZE"]["height"];
}

// size to resize image on server
$arParams["MAX_SIZE"] = (is_array($arParams["MAX_SIZE"]) ? array_change_key_case($arParams["MAX_SIZE"], CASE_LOWER) : null);

// size to resize image on client in browser only. It's helpful for space economy
$arParams["HTML_SIZE"] = ($arParams["HTML_SIZE"] > 0 ?
	array("width" => $arParams["HTML_SIZE"], "height" => $arParams["HTML_SIZE"]) :
	(is_array($arParams["HTML_SIZE"]) ? array_change_key_case($arParams["HTML_SIZE"], CASE_LOWER) : $arParams["MAX_SIZE"]));
if ($arParams["HTML_SIZE"] !== null && $arParams["MAX_SIZE"] !== null)
{
	$arParams["HTML_SIZE"]["width"] = min($arParams["HTML_SIZE"]["width"], $arParams["MAX_SIZE"]["width"]);
	$arParams["HTML_SIZE"]["height"] = min($arParams["HTML_SIZE"]["height"], $arParams["MAX_SIZE"]["height"]);
}
$arParams["FAMILY"] = trim($arParams["FAMILY"]);
$arParams["FAMILY"] = mb_strtolower(empty($arParams["FAMILY"])? "forum" : $arParams["FAMILY"]);
$arParams["FAMILY"] = preg_replace("/[^a-z]/is", "", $arParams["FAMILY"]);
$arParams["RETURN"] = ($arParams["RETURN"] == "Y" ? "Y" : "N");
$arParams["MODE"] = trim($arParams["MODE"]);
// *************************/ADDITIONAL ****************************************************************
// *************************/Input params***************************************************************

$img["~src"] = $arParams["URL"];
$img["src_download"] = $arParams["URL"].(mb_strpos($arParams["URL"], '?') !== false ? '&' : '?')."action=download";
$img["src"] = $arParams["URL"].(mb_strpos($arParams["URL"], '?') !== false ? '&' : '?').($arParams["MAX_SIZE"] !== null ? http_build_query($arParams["MAX_SIZE"]) : "");

// HTML size
$bNeedCreatePicture = false;
$props =
	($img["width"] > 0 ? 'width="'.$img["width"].'" ' : '').
	($img["height"] > 0 ? 'height="'.$img["height"].'" ' : '');
if ($arParams["HTML_SIZE"] !== null)
{
	if ($arParams["HTML_SIZE"]["width"] > 0 && $arParams["HTML_SIZE"]["height"] > 0 &&
		$img["width"] > 0 && $img["height"] > 0)
	{
		CFile::ScaleImage(
			$img["width"], $img["height"],
			$arParams["HTML_SIZE"], BX_RESIZE_IMAGE_PROPORTIONAL,
			$bNeedCreatePicture, $arSourceSize, $arDestinationSize);
		if ($bNeedCreatePicture)
			$props = 'width="'.$arDestinationSize["width"].'" height="'.$arDestinationSize["height"].'" ';
	}
	else
	{
		$style = array();
		if ($arParams["HTML_SIZE"]["width"] > 0)
			$style[] = 'max-width:'.$arParams["HTML_SIZE"]["width"].'px;';
		if ($arParams["HTML_SIZE"]["height"] > 0)
			$style[] = 'max-height:'.$arParams["HTML_SIZE"]["height"].'px;';
		if (!empty($style))
			$props = 'style="'.implode($style, "").'"';
	}

}
if ($arParams['MODE'] == 'RSS')
{
	$arParams["RETURN_DATA"] = <<<HTML
<img src="{$img["src"]}" {$props} />
HTML;
	if ($bNeedCreatePicture)
	{
$arParams["RETURN_DATA"] = <<<HTML
<a href="{$img["~src"]}" target="_blank">{$arParams["RETURN_DATA"]}</a>
HTML;
	}
}
elseif ($arParams['MODE'] == 'SHOW2IMAGES')
{
$arParams["RETURN_DATA"] = <<<HTML
<img src="{$img["src"]}" {$props}
	data-bx-viewer="image"
	data-bx-src="{$img["~src"]}"
	data-bx-download="{$img["src_download"]}"
	data-bx-width="{$img["~width"]}"
	data-bx-height="{$img["~height"]}"
	data-bx-title="{$arParams["IMG_NAME"]}"
	data-bx-size="{$arParams["IMG_SIZE"]}" />
HTML;
}
else
{
	CUtil::InitJSCore();
	do {
		$id = "popup_".rand();
	} while(ForumGetEntity($id) !== false);

$arParams["RETURN_DATA"] = <<<HTML
<img src="{$img["~src"]}" id="{$id}" border="0" {$props} data-bx-viewer="image" data-bx-src="{$img["~src"]}" />
HTML;
}
$arParams["RETURN_DATA"] = str_replace(array("\n", "\t", "  "), " ", $arParams["RETURN_DATA"]);

if ($arParams["RETURN"] == "Y")
	$this->__component->arParams["RETURN_DATA"] = $arParams["RETURN_DATA"];
else
	echo $arParams["RETURN_DATA"];
?>