<?php
const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const NO_AGENT_CHECK = true;
const DisableEventsCheck = true;

if (!(
	($_SERVER['HTTP_BX_AJAX'] ?? null) !== null
	|| ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) === 'XMLHttpRequest'
))
{
	die();
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\StoreDocumentFileTable;
use Bitrix\Currency\CurrencyManager;

/** @global CMain $APPLICATION */
global $APPLICATION;

if (!Loader::includeModule('catalog'))
{
	return;
}

$request = Context::getCurrent()->getRequest();

if (!$request->isPost() || !check_bitrix_sessid())
{
	return;
}

$accessController = AccessController::getCurrent();
if (!(
	$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
	&& $accessController->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS)
))
{
	return;
}

$action = $request->getPost('ACTION');
if ($action === null)
{
	return;
}

if (!CBitrixComponent::includeComponentClass('bitrix:catalog.store.document.detail'))
{
	return;
}

switch ($action)
{
	case 'SAVE':
		$allowModify = false;
		$documentId = $request->getPost('ACTION_ENTITY_ID');
		if (!is_string($documentId))
		{
			$documentId = 0;
		}
		$documentId = (int)$documentId;
		if ($documentId > 0)
		{
			$documentType = \CatalogStoreDocumentDetailComponent::getTypeByDocumentId($documentId);
			if ($documentType)
			{
				$allowModify =
					$accessController->checkByValue(
						ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
						$documentType
					)
				;
			}
			unset($documentType);
		}
		if ($allowModify)
		{
			$fields = [];
			$title = $request->getPost('TITLE');
			if (!is_string($title))
			{
				$title = '';
			}
			$title = trim($title);
			if ($title !== '')
			{
				$fields['TITLE'] = $title;
			}
			$responsibleId = $request->getPost('RESPONSIBLE_ID');
			if (!is_string($responsibleId))
			{
				$responsibleId = 0;
			}
			$responsibleId = (int)$responsibleId;
			if ($responsibleId > 0)
			{
				$fields['RESPONSIBLE_ID'] = $responsibleId;
			}
			if (empty($fields))
			{
				return;
			}
			CCatalogDocs::update($documentId, $fields);
			unset($fields);
		}
		unset($documentId, $allowModify);
		break;
	case 'GET_FORMATTED_SUM':
		if (!Loader::includeModule('currency'))
		{
			return;
		}

		$sum = $request->getPost('SUM');
		if (!is_string($sum))
		{
			$sum = 0.0;
		}
		$sum = (float)$sum;

		$currencyId = $request->getPost('CURRENCY_ID');
		if (!is_string($currencyId))
		{
			$currencyId = '';
		}
		$currencyId = trim($currencyId);
		if ($currencyId === '')
		{
			$currencyID = CurrencyManager::getBaseCurrency();
		}

		$APPLICATION->RestartBuffer();
		echo \Bitrix\Main\Web\Json::encode([
			'FORMATTED_SUM' => CCurrencyLang::CurrencyFormat($sum, $currencyId, false),
			'FORMATTED_SUM_WITH_CURRENCY' => CCurrencyLang::CurrencyFormat($sum, $currencyId),
		]);
		break;
	case 'RENDER_IMAGE_INPUT':
		$documentId = $request->getPost('ACTION_ENTITY_ID');
		if (!is_string($documentId))
		{
			$documentId = 0;
		}
		$documentId = (int)$documentId;
		$fieldName = $request->getPost('FIELD_NAME');
		if (!is_string($fieldName))
		{
			$fieldName = '';
		}
		$fieldName = trim($fieldName);
		if ($fieldName !== '')
		{
			$value = [];
			if ($documentId > 0)
			{
				$allowView = false;
				$documentType = \CatalogStoreDocumentDetailComponent::getTypeByDocumentId($documentId);
				if ($documentType)
				{
					$allowView =
						$accessController->checkByValue(
							ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
							$documentType
						)
					;
				}
				unset($documentType);
				if ($allowView)
				{
					$files = StoreDocumentFileTable::getList([
						'select' => [
							'FILE_ID',
						],
						'filter' => [
							'=DOCUMENT_ID' => $documentId,
						],
					])->fetchAll();
					$value = array_column($files, 'FILE_ID');
					unset($files);
				}
				unset($allowView);
			}
			Header('Content-Type: text/html; charset=' . LANG_CHARSET);
			$APPLICATION->ShowAjaxHead();
			$APPLICATION->IncludeComponent(
				'bitrix:main.file.input',
				'',
				[
					'MODULE_ID' => 'catalog',
					'MAX_FILE_SIZE' => 3145728,
					'MULTIPLE'=> 'Y',
					'ALLOW_UPLOAD' => ($request->getPost('ALLOW_UPLOAD') ?? 'N') === 'Y' ? 'Y' : 'N',
					'CONTROL_ID' => mb_strtolower($fieldName) . '_uploader',
					'INPUT_NAME' => $fieldName,
					'INPUT_NAME_UNSAVED' => $fieldName . '_tmp',
					'INPUT_VALUE' => $value,
				],
			);
			unset($value);
		}
		unset($fieldName, $documentId);
		break;
	case 'GET_SECONDARY_ENTITY_INFOS':
		$contractorsProvider = Bitrix\Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
			Bitrix\Catalog\v2\Contractor\Provider\Manager::PROVIDER_STORE_DOCUMENT
		);
		if ($contractorsProvider)
		{
			$contractorsProvider::processDocumentCardAjaxActions($action);
		}
		break;
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();
