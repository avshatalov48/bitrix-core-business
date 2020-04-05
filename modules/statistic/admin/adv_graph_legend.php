<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

// init image
$ImageHandle = CreateImageHandle(45, 2);

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
	imagedashedline($ImageHandle, 3, 0, 40, 0, $color);
	imagedashedline($ImageHandle, 3, 1, 40, 1, $color);
}
else
{
	imageline($ImageHandle, 3, 0, 40, 0, $color);
	imageline($ImageHandle, 3, 1, 40, 1, $color);
}

ShowImageHeader($ImageHandle);
