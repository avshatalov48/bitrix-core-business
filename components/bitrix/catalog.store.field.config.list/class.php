<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogStoreFieldConfigList extends CBitrixComponent
{
	public function executeComponent()
	{
		$template = '';

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_MODIFY))
		{
			$template = 'error';

			$this->arResult['ERROR'] = Loc::getMessage('CATALOG_COMPONENT_STORE_FIELD_CONFIG_LIST_ERROR_ACCESS_DENIED');
		}

		$this->includeComponentTemplate($template);
	}
}
