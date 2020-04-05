<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '<div class="forum-breadcrumb forum-breadcrumb-top">';
$GLOBALS["FORUM_HIDE_LAST_BREADCRUMB"] = ($GLOBALS["FORUM_HIDE_LAST_BREADCRUMB"] === true ? true : false);
$itemSize = count($arResult);
if ($GLOBALS["FORUM_HIDE_LAST_BREADCRUMB"] == true)
	$itemSize--;
for($index = 0; $index < $itemSize; $index++)
{
	if ($index <= 0)
		$strReturn .= '<span class="forum-crumb-item forum-crumb-first">';
	elseif ($index == $itemSize)
		$strReturn .= '</span> <span class="forum-crumb-item forum-crumb-last"><span>&nbsp;&raquo;&nbsp;</span>';
	else
		$strReturn .= '</span> <span class="forum-crumb-item"> <span>&raquo;&nbsp;</span>';

	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	if($arResult[$index]["LINK"] <> "")
		$strReturn .= '<a href="'.$arResult[$index]["LINK"].'" title="'.$title.'">'.$title.'</a>';
	else
		$strReturn .= ''.$title.'';
}

$strReturn .= '</span></div>';
	
return $strReturn;
?>
