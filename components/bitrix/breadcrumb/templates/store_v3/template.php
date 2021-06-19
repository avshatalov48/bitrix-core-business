<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';

$strReturn .= '<div class="store-breadcrumb">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	$jsonSeparator = ($index > 0? ',' : '');

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$strReturn .=  '
			<div class="store-breadcrumb-item" id="bx_breadcrumb_'.$index.'">
				<a class="store-breadcrumb-item-link" href="'.$arResult[$index]["LINK"].'" title="'.$title.'" >
					<svg width="11" height="18" viewBox="0 0 11 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M10.209 15.7666L4.44046 9.99811L3.40415 8.99999L4.44046 8.00127L10.209 2.23275L8.79716 0.820923L0.618452 8.99963L8.79716 17.1783L10.209 15.7666Z" fill="#525C69"/>
					</svg>
				</a>
			</div>';
		$jsonLDBreadcrumbList .= $jsonSeparator.'{
				"@type": "ListItem",
				"position": '.$index.',
				"name": "'.$title.'",
				"item": "'.$arResult[$index]["LINK"].'"
		  	}';
	}
}
$jsonLDBreadcrumb = '
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": ['.$jsonLDBreadcrumbList.']
    }
</script>
';
$strReturn .= '</div>'.$jsonLDBreadcrumb;



return $strReturn;
