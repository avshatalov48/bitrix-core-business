<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

// create an image canvas
$ImageHandle = CreateImageHandle(45, 2);
imagefill($ImageHandle, 0, 0, imagecolorallocate($ImageHandle, 255, 255, 255));

if (isset($_REQUEST["color"]))
	$dec = ReColor($_REQUEST["color"]);
else
	$dec = 0;

if (is_array($dec))
	$color = imagecolorallocate($ImageHandle, $dec[0], $dec[1], $dec[2]);
else
	$color = imagecolorallocate($ImageHandle, 0, 0, 0);

if (isset($_REQUEST["dash"]) && $_REQUEST["dash"] == "Y")
{
	$style = array(
		$color,
		$color,
		IMG_COLOR_TRANSPARENT,
		IMG_COLOR_TRANSPARENT,
		IMG_COLOR_TRANSPARENT,
	);
	imagesetstyle($ImageHandle, $style);
	imageline($ImageHandle, 1, 0, 45, 0, IMG_COLOR_STYLED);
	imageline($ImageHandle, 1, 1, 45, 1, IMG_COLOR_STYLED);
}
else
{
	imageline($ImageHandle, 0, 0, 45, 0, $color);
	imageline($ImageHandle, 0, 1, 45, 1, $color);
}

ShowImageHeader($ImageHandle);
