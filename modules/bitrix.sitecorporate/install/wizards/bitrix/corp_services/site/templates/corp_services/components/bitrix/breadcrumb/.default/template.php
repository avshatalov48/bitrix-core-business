<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//delayed function must return a string
if(empty($arResult))
	return "";

if($arResult[count($arResult)-1]["LINK"]!="" && $arResult[count($arResult)-1]["LINK"]!=$GLOBALS["APPLICATION"]->GetCurPage(false))
	$arResult[] = Array("TITLE"=>$GLOBALS["APPLICATION"]->GetTitle());

$strReturn = '<b class="r0 top"></b><p><a href="'.SITE_DIR.'" title="'.GetMessage("HDR_GOTO_MAIN").'"><img src="'.SITE_TEMPLATE_PATH.'/images/home.gif" width="9" height="8" /></a>';
for($index = 0, $itemSize = count($arResult); $index < $itemSize; $index++)
{
	$strReturn .= '&nbsp;&mdash;&nbsp;';

	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	if($arResult[$index]["LINK"] <> "" && $arResult[$index]["LINK"]!=$GLOBALS["APPLICATION"]->GetCurPage(false))
		$strReturn .= '<span id="bx_breadcrumb_'.$index.'" itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"'.($index > 0? ' itemprop="child"' : '').' itemref="bx_breadcrumb_'.($index+1).'"><a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="url"><span itemprop="title" class="bx-breadcrumbs-name">'.$title.'</span></a></span>';
	else
		$strReturn .= '<span id="bx_breadcrumb_'.$index.'" class="bx-breadcrumbs-name">'.$title.'</span>';
}

$strReturn .= '</p><b class="r0 bottom"></b>';
return $strReturn;
?>
