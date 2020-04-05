<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';

//we can't use $APPLICATION->SetAdditionalCSS() here because we are inside the buffered function GetNavChain()
//$css = $APPLICATION->GetCSSArray();
//if(!is_array($css) || !in_array("/bitrix/css/main/font-awesome.css", $css))
//{
//	$strReturn .= '<link href="'.CUtil::GetAdditionalFileURL("/bitrix/css/main/font-awesome.css").'" type="text/css" rel="stylesheet" />'."\n";
//	$strReturn .= '<style type="text/css">
//	. .-item {
//		float: left;
//		margin-bottom: 10px;
//		white-space: nowrap;
//		line-height: 13px;
//		vertical-align: middle;
//		margin-right: 10px;
//	}
//</style>';
//}

$strReturn .= '<ul class="landing-breadcrumb u-list-inline">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
//	$nextRef = ($index < $itemSize-2 && $arResult[$index+1]["LINK"] <> ""? ' itemref="bx_breadcrumb_'.($index+1).'"' : '');
//	$child = ($index > 0? ' itemprop="child"' : '');
	$arrow = ($index > 0? '<i class="landing-breadcrumb-arrow fa g-mx-5"></i>' : '');

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$strReturn .= '
			<li class="landing-breadcrumb-item list-inline-item mr-0"
				itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				'.$arrow.'
				<a class="landing-breadcrumb-link u-link-v5"
					href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="url">
					<span class="landing-breadcrumb-name" itemprop="name">'.$title.'</span>
				</a>
				<meta itemprop="position" content="'.($index + 1).'" />
			</li>';
	}
	else
	{
		$strReturn .= '
			<li class="landing-breadcrumb-item landing-breadcrumb-item--last list-inline-item g-color-primary"
				itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				'.$arrow.'
				<span class="landing-breadcrumb-name landing-breadcrumb-name--last" itemprop="name">'.$title.'</span>
				<meta itemprop="position" content="'.($index + 1).'" />
			</li>';
	}
	
	
//	landing-breadcrumb-arrow {fa fa-angle-right}
	
//                    <li class="list-inline-item g-mr-7">
//                      <a class="u-link-v5 g-color-white g-color-primary--hover" href="#!">Home</a>
//                      <i class="fa fa-angle-right g-ml-7"></i>
//                    </li>
//                    <li class="list-inline-item g-mr-7">
//                      <a class="u-link-v5 g-color-white g-color-primary--hover" href="#!">Pages</a>
//                      <i class="fa fa-angle-right g-ml-7"></i>
//                    </li>
//                    <li class="list-inline-item g-color-primary">
//                      <span>About Us</span>
//                    </li>
}

$strReturn .= '</ul>';

return $strReturn;
