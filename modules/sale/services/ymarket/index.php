<?php

use Bitrix\Main\Loader;

const NO_AGENT_CHECK = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
const DisableEventsCheck = true;

/** @global CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!Loader::includeModule('sale'))
{
	CHTTP::SetStatus("500 Internal Server Error");
	die('{"error":"Module \"sale\" not installed"}');
}

$pattern = "#^\/bitrix\/services\/ymarket\/(([\w\d\-]{2})\/)?([\w\d\-]+)?(\/)?(([\w\d\-]+)(\/)?)?#";

$matches = [];
preg_match ($pattern, $_SERVER["REQUEST_URI"], $matches);

$siteId = $matches[2] ?? '';
$requestObject = $matches[3] ?? '';
$method = $matches[6] ?? '';

$postData = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) <= 0)
{
	$postData = file_get_contents("php://input");
}

$YMHandler = new CSaleYMHandler([
	"SITE_ID" => $siteId,
]);

$result = $YMHandler->processRequest($requestObject, $method, $postData);
$APPLICATION->RestartBuffer();
echo $result;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
