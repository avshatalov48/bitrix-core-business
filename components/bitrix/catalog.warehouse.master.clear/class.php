<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Url\InventoryManagementSourceBuilder;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

\Bitrix\Main\Loader::includeModule('catalog');

/**
 * Class WarehouseMasterClear
 */
class WarehouseMasterClear extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
	/**
	 * @return void
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function executeComponent()
	{
		if (Main\Loader::includeModule('crm'))
		{
			$this->arResult['IS_WITH_ORDERS_MODE'] = \CCrmSaleHelper::isWithOrdersMode();
		}

		$this->arResult['IS_USED_ONEC'] = \Bitrix\Catalog\Component\UseStore::isUsedOneC();
		$this->arResult['IS_PLAN_RESTRICTED'] = \Bitrix\Catalog\Component\UseStore::isPlanRestricted();
		$this->arResult['IS_USED'] = \Bitrix\Catalog\Config\State::isEnabledInventoryManagement();
		$this->arResult['IS_EMPTY'] = \Bitrix\Catalog\Component\UseStore::isEmpty();
		$this->arResult['IS_RESTRICTED_ACCESS'] = !$this->checkRights();
		$this->arResult['PREVIEW_LANG'] = $this->getPreviewLang();
		$this->arResult['INVENTORY_MANAGEMENT_SOURCE'] =
			InventoryManagementSourceBuilder::getInstance()->getInventoryManagementSource()
		;

		$this->includeComponentTemplate();
	}

	protected function getPreviewLang(): string
	{
		$zone = $this->getZone();

		return in_array($zone, ['ru','by','kz']) ? 'ru' : 'en';
	}

	private function getZone()
	{
		if (Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		else
		{
			$iterator = Bitrix\Main\Localization\LanguageTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=DEF' => 'Y',
					'=ACTIVE' => 'Y'
				]
			]);
			$row = $iterator->fetch();
			$zone = $row['ID'];
		}

		return $zone;
	}

	protected function checkRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS);
	}

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		return [];
	}
}
