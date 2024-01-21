<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Document\StoreDocumentTableManager;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogStoreFieldConfigList extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		if (!isset($arParams['ENTITY_ID']))
		{
			$arParams['ENTITY_ID'] = StoreTable::getUfId();
		}

		if (!isset($arParams['HELPDESK_ARTICLE_ID']))
		{
			$arParams['HELPDESK_ARTICLE_ID'] = '17415624';
		}
		elseif (!is_numeric($arParams['HELPDESK_ARTICLE_ID']))
		{
			$arParams['HELPDESK_ARTICLE_ID'] = (int)$arParams['HELPDESK_ARTICLE_ID'];
		}

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		$template = '';

		if (!$this->checkRights())
		{
			$template = 'error';

			$this->arResult['ERROR'] = Loc::getMessage('CATALOG_COMPONENT_STORE_FIELD_CONFIG_LIST_ERROR_ACCESS_DENIED');
		}

		$this->includeComponentTemplate($template);
	}

	private function checkRights(): bool
	{
		$entityId = $this->arParams['ENTITY_ID'];
		if ($entityId === StoreTable::getUfId())
		{
			return AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_MODIFY);
		}

		if (in_array($entityId, StoreDocumentTableManager::getUfEntityIds(), true))
		{
			$documentType = StoreDocumentTableManager::getTypeByUfId($entityId);
			if ($documentType)
			{
				return AccessController
					::getCurrent()
					->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $documentType)
				;
			}
		}

		return false;
	}
}
