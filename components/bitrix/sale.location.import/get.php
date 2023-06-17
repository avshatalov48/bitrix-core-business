<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NO_AGENT_CHECK = true;

use Bitrix\Main\Loader;

$initialTime = time();

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

Loader::includeModule('sale');

CBitrixComponent::includeComponentClass('bitrix:sale.location.import');

CUtil::JSPostUnescape();

$result = true;
$errors = array();

// if we have an exception here, we got ajax parse error on client side.
// we must take care of it until we have better solution
$result = CBitrixSaleLocationImportComponent::doAjaxStuff([
	'INITIAL_TIME' => $initialTime
]);

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo CUtil::PhpToJSObject(
	[
		'result' => empty($result['ERRORS']),
		'errors' => $result['ERRORS'],
		'data' => $result['DATA']
	],
	false,
	false,
	true
);
