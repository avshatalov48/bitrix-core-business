<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '<div class="photo-breadcrumb photo-breadcrumb-top">';
$GLOBALS["PHOTO_HIDE_LAST_BREADCRUMB"] = ($GLOBALS["PHOTO_HIDE_LAST_BREADCRUMB"] === true ? true : false);

$itemSize = count($arResult);
if ($GLOBALS["PHOTO_HIDE_LAST_BREADCRUMB"] == true)
	$itemSize--;
for($index = 0; $index < $itemSize; $index++)
{
	if ($index <= 0)
		$strReturn .= '<span class="photo-crumb-item photo-crumb-first">';
	elseif ($index == $itemSize)
		$strReturn .= '</span> <span class="photo-crumb-item photo-crumb-last"><span>&nbsp;&raquo;&nbsp;</span>';
	else
		$strReturn .= '</span> <span class="photo-crumb-item"> <span>&raquo;&nbsp;</span>';

	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	if($arResult[$index]["LINK"] <> "")
		$strReturn .= '<a href="'.$arResult[$index]["LINK"].'" title="'.$title.'">'.$title.'</a>';
	else
		$strReturn .= ''.$title.'';
}

$strReturn .= '</span></div>';
	
return $strReturn;
?>
