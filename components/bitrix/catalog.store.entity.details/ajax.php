<?php

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Config\Ini;
use Bitrix\Main\Loader;

if (!Loader::includeModule('catalog'))
{
	return;
}

CUtil::JSPostUnescape();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid())
{
	return;
}

global $APPLICATION;

$action = (string)($_POST['ACTION'] ?? '');

if ($action === 'GET_FORMATTED_SUM')
{
	if (!Loader::includeModule('currency'))
	{
		return;
	}

	$sum = $_POST['SUM'] ?? 0;
	$currencyID = $_POST['CURRENCY_ID'] ?? '';
	if($currencyID === '')
	{
		$currencyID = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
	}

	$APPLICATION->RestartBuffer();
	echo \Bitrix\Main\Web\Json::encode([
		'FORMATTED_SUM' => CCurrencyLang::CurrencyFormat($sum, $currencyID, false),
		'FORMATTED_SUM_WITH_CURRENCY' => CCurrencyLang::CurrencyFormat($sum, $currencyID),
	]);
}
elseif($action === 'RENDER_IMAGE_INPUT')
{
	$fieldName = (string)($_POST['FIELD_NAME'] ?? '');
	if (empty($fieldName))
	{
		return;
	}

	$imageIds = [];
	$allowUpload = $_POST['ALLOW_UPLOAD'] ?? 'N';
	if ($allowUpload !== 'N')
	{
		// always images
		$allowUpload = 'I';
	}

	$entityId = (int)($_POST['ACTION_ENTITY_ID'] ?? 0);
	if ($entityId > 0 && $fieldName === 'IMAGE_ID')
	{
		$row = StoreTable::getRow([
			'select' => [
				'IMAGE_ID',
			],
			'filter' => [
				'=ID' => $entityId,
			],
		]);
		if (isset($row['IMAGE_ID']))
		{
			$imageIds[] = (int)$row['IMAGE_ID'];
		}
	}

	header('Content-Type: text/html; charset='.LANG_CHARSET);
	$APPLICATION->ShowAjaxHead();
	$APPLICATION->IncludeComponent(
		'bitrix:main.file.input',
		'',
		array(
			'MODULE_ID' => 'catalog',
			'MAX_FILE_SIZE' => Ini::unformatInt(ini_get('upload_max_filesize')),
			'MULTIPLE'=> 'N',
			'ALLOW_UPLOAD' => $allowUpload,
			'CONTROL_ID' => mb_strtolower($fieldName).'_uploader',
			'INPUT_NAME' => $fieldName,
			'INPUT_NAME_UNSAVED' => $fieldName . '_tmp',
			'INPUT_VALUE' => $imageIds,
		),
	);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();
