<?php

define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

$HTTP_ACCEPT_ENCODING = "";
$_SERVER["HTTP_ACCEPT_ENCODING"] = "";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$cpt = new CCaptcha();

/* Be careful to manupulate captcha parameters here because of admin panel settings */

/* white background, dark chars, lines, ellipses */
/*
$cpt->SetBGColor(array(255, 255, 255));
$cpt->SetTextColor(array(array(0, 100), array(0, 100), array(0, 100)));
$cpt->SetEllipseColor(array(array(127, 255), array(127, 255), array(127, 255)));
$cpt->SetLineColor(array(array(110, 250), array(110, 250), array(110, 250)));
*/

/* black background, light chars, lines, ellipses */
/*
$cpt->SetBGColor(array(0, 0, 0));
$cpt->SetTextColor(array(array(127, 255), array(127, 255), array(127, 255)));
$cpt->SetEllipseColor(array(array(10, 120), array(10, 120), array(10, 120)));
$cpt->SetLineColor(array(array(10, 120), array(10, 120), array(10, 120)));
*/

/* near while background, near gray chars, near gray lines */
/*
$cpt->SetBGColor(array(array(200, 255), array(200, 255), array(200, 255)));
$cpt->SetTextColor(array(array(100, 140), array(100, 140), array(100, 140)));
$cpt->SetEllipsesNumber(0);
$cpt->SetLinesNumber(20);
$cpt->SetLineColor(array(array(100, 140), array(100, 140), array(100, 140)));
*/

if (isset($_GET["captcha_sid"]) && is_string($_GET["captcha_sid"]))
{
	if ($cpt->InitCode($_GET["captcha_sid"]))
		$cpt->Output();
	else
		$cpt->OutputError();
}
elseif (isset($_GET["captcha_code"]) && is_string($_GET["captcha_code"]))
{
	if ($cpt->InitCodeCrypt($_GET["captcha_code"]))
		$cpt->Output();
	else
		$cpt->OutputError();
}
else
{
	$cpt->OutputError();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
