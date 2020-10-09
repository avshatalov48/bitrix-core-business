<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//Navigation chain template

$arChainBody = array();
foreach($arCHAIN as $item)
{
	if(mb_strlen($item["LINK"]) < mb_strlen(SITE_DIR))
		continue;
	if($item["LINK"] <> "")
		$arChainBody[] = '<a href="'.$item["LINK"].'">'.htmlspecialcharsex($item["TITLE"]).'</a>';
	else
		$arChainBody[] = htmlspecialcharsex($item["TITLE"]);
}
return implode('&nbsp;/&nbsp;', $arChainBody);
?>