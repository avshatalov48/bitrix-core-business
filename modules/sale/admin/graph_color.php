<?
@require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
@require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

if (function_exists("FormDecode")) FormDecode();
UnQuoteAll();

// create an image canvas
$ImageHandle = CreateImageHandle(45, 2);

$colorFFFFFF = ImageColorAllocate($ImageHandle,255,255,255);
ImageFill($ImageHandle, 0, 0, $colorFFFFFF);

$dec=ReColor($color);
$color = ImageColorAllocate($ImageHandle,$dec[0],$dec[1],$dec[2]);
if (isset($dash) && $dash=="Y") 
{
	$style = array (
		$color,$color,
		IMG_COLOR_TRANSPARENT, 
		IMG_COLOR_TRANSPARENT, 
		IMG_COLOR_TRANSPARENT
		); 
	//$white = ImageColorAllocate($ImageHandle,255,255,255);
	//$style = array ($color,$color,$white,$white,$white); 
	ImageSetStyle($ImageHandle, $style); 
	ImageLine($ImageHandle, 1, 0, 45, 0, IMG_COLOR_STYLED);
	ImageLine($ImageHandle, 1, 1, 45, 1, IMG_COLOR_STYLED);
}
else 
{
	ImageLine($ImageHandle, 0, 0, 45, 0, $color);
	ImageLine($ImageHandle, 0, 1, 45, 1, $color);
}

/******************************************************
                Send to client
*******************************************************/

ShowImageHeader($ImageHandle);
?>