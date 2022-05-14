<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Catalog\StoreDocumentFileTable;
use Bitrix\Main\Context;

if (!\Bitrix\Main\Loader::includeModule('catalog'))
{
	return;
}

CUtil::JSPostUnescape();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid())
{
	return;
}

global $APPLICATION;

$action = $_POST['ACTION'];
if ($action === 'SAVE')
{
	$documentId = $_POST['ACTION_ENTITY_ID'];
	$title = $_POST['TITLE'];
	if (!$title)
	{
		return;
	}
	$fields = [
		'TITLE' => $title,
	];
	CCatalogDocs::update($documentId, $fields);
}
elseif ($action === 'GET_FORMATTED_SUM')
{
	if (!\Bitrix\Main\Loader::includeModule('currency'))
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
	if (!\Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_read'))
	{
		return;
	}

	$documentId = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;

	$fieldName = isset($_POST['FIELD_NAME']) ? $_POST['FIELD_NAME'] : '';

	if ($fieldName !== '')
	{
		if ($documentId > 0)
		{
			$files = StoreDocumentFileTable::getList(['select' => ['FILE_ID'], 'filter' => ['DOCUMENT_ID' => $documentId]])->fetchAll();
			$value = array_column($files, 'FILE_ID');
		}
		else
		{
			$value = [];
		}

		Header('Content-Type: text/html; charset='.LANG_CHARSET);
		$APPLICATION->ShowAjaxHead();
		$APPLICATION->IncludeComponent(
			'bitrix:main.file.input',
			'',
			array(
				'MODULE_ID' => 'catalog',
				'MAX_FILE_SIZE' => 3145728,
				'MULTIPLE'=> 'Y',
				'ALLOW_UPLOAD' => $_POST['ALLOW_UPLOAD'] ?? 'N',
				'CONTROL_ID' => mb_strtolower($fieldName).'_uploader',
				'INPUT_NAME' => $fieldName,
				'INPUT_NAME_UNSAVED' => $fieldName . '_tmp',
				'INPUT_VALUE' => $value
			),
		);
	}
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();