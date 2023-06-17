<?php
const ADMIN_SECTION = true;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
header("Content-Type: text/xml");
if (!Loader::includeModule('iblock'))
{
	return;
}

$request = Context::getCurrent()->getRequest();

$rawId = $request->get('ID');
if ((int)$rawId > 0)
{
	$ID = (int)$rawId;
}
else
{
	$ID = trim($rawId);
}

$LANG = trim((string)$request->get('LANG'));
$TYPE = trim((string)$request->get('TYPE'));
$LIMIT = (int)($request->get('LIMIT'));

CIBlockRSS::GetRSS($ID, $LANG, $TYPE, $LIMIT, false, false);

CMain::FinalActions();
