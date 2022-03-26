<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class UseStore
{
	const CATEGORY_NAME = "use_store";
	const OPTION_NAME = "catalog.warehouse.master.clear";

	const STORE_WILDBERRIES = "WILDBERRIES";
	const STORE_SBERMEGAMARKET = "SBERMEGAMARKET";
	const STORE_OZON = "OZON";
	const STORE_ALIEXPRESS = "ALIEXPRESS";

	const URL_PARAM_STORE_MASTER_HIDE = "STORE_MASTER_HIDE";

	static public function isUsed(): bool
	{
		return State::isUsedInventoryManagement();
	}

	static public function isUsedOneC(): bool
	{
		return Option::get('catalog', 'once_inventory_management', 'N') === 'Y';
	}

	static public function enableOnec(): bool
	{
		Option::set('catalog', 'once_inventory_management', 'Y');
		return true;
	}

	static public function disableOnec(): bool
	{
		Option::set('catalog', 'once_inventory_management', 'N');
		return true;
	}

	static public function isPlanRestricted(): bool
	{
		return !Catalog\Config\Feature::isInventoryManagementEnabled();
	}

	static public function enable()
	{
		UseStore::resetQuantity();
		UseStore::resetSaleReserve();

		Option::set('catalog', 'default_quantity_trace', 'Y');
		Option::set('catalog', 'default_can_buy_zero', 'Y');
		Option::set('catalog', 'allow_negative_amount', 'Y');

		Option::set('catalog', 'default_use_store_control', 'Y');
		Option::set('catalog', 'enable_reservation', 'Y');

		self::installCatalogStores();
		self::installRealizationDocumentTradingPlatform();

		self::setNeedShowSlider(false);
	}

	static protected function installCatalogStores()
	{
		if (self::hasDefaultCatalogStore() === false)
		{
			$storeId = self::getFirstCatalogStore();
			if($storeId > 0)
			{
				self::setDefaultCatalogStore($storeId);
			}
			else
			{
				self::createDefaultCatalogStore();
			}
		}

		if(self::isBitrixSiteManagement() === false)
		{
			self::createCatalogStores();
		}
	}

	static protected function hasDefaultCatalogStore(): bool
	{
		$storeId = Catalog\StoreTable::getDefaultStoreId();

		return $storeId !== null;
	}

	static protected function getFirstCatalogStore(): int
	{
		$result = 0;
		$iterator = Catalog\StoreTable::getList([
			'select' => ['ID'],
			'filter' => ['ACTIVE' => 'Y', 'SITE_ID' => ''],
			'limit' => 1,
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row))
			$result = (int)$row['ID'];
		unset($row);
		return $result;
	}

	static protected function setDefaultCatalogStore($storeId): bool
	{
		$r = Catalog\StoreTable::update($storeId, array("IS_DEFAULT" => "Y"));
		return $r->isSuccess();
	}

	static protected function createDefaultCatalogStore(): bool
	{
		$title = Loc::getMessage("CATALOG_USE_STORE_DEFAULT");
		$r = Catalog\StoreTable::add(['TITLE'=>$title, 'ADDRESS'=>$title]);
		return $r->isSuccess();
	}

	static public function disable(): bool
	{
		if (self::conductedDocumentsExist())
		{
			return false;
		}
		Option::set('catalog', 'default_use_store_control', 'N');

		self::clearNeedShowSlider();
		self::deactivateRealizationDocumentTradingPlatform();
		return true;
	}

	static protected function resetQuantity(): void
	{
		$conn = Application::getConnection();
		$mainTypes = [
			ProductTable::TYPE_PRODUCT,
			ProductTable::TYPE_OFFER,
			ProductTable::TYPE_FREE_OFFER,
		];
		$conn->queryExecute("truncate table b_catalog_store_product");
		$conn->queryExecute("delete from b_catalog_store_barcode where ORDER_ID is null");
		$conn->queryExecute(
			"
				update b_catalog_product
				set 
					QUANTITY = 0, 
					QUANTITY_TRACE = '".ProductTable::STATUS_DEFAULT."', 
					CAN_BUY_ZERO = '".ProductTable::STATUS_DEFAULT."',
					NEGATIVE_AMOUNT_TRACE = '".ProductTable::STATUS_DEFAULT."',
					AVAILABLE = '".ProductTable::STATUS_YES."'
				where 
					TYPE in (".implode(', ', $mainTypes).")"
		);
		$conn->queryExecute(
			"
				update b_catalog_product
				set 
					QUANTITY = 0, 
					QUANTITY_TRACE = '".ProductTable::STATUS_NO."', 
					CAN_BUY_ZERO = '".ProductTable::STATUS_YES."', 
					NEGATIVE_AMOUNT_TRACE = '".ProductTable::STATUS_YES."', 
					AVAILABLE = '".ProductTable::STATUS_YES."'
				where 
					TYPE = ".ProductTable::TYPE_SKU
		);
		$conn->queryExecute(
			"
				update b_catalog_product
				set 
					QUANTITY = 0, 
					QUANTITY_TRACE = '".ProductTable::STATUS_YES."',
					CAN_BUY_ZERO = '".ProductTable::STATUS_NO."',
					NEGATIVE_AMOUNT_TRACE = '".ProductTable::STATUS_NO."',
					AVAILABLE = '".ProductTable::STATUS_NO."',
					TYPE = ".ProductTable::TYPE_PRODUCT."
				where 
					TYPE = ".ProductTable::TYPE_EMPTY_SKU
		);
		$conn->queryExecute(
			"
				update b_catalog_product 
				set 
					QUANTITY = 0, 
					QUANTITY_TRACE = '".ProductTable::STATUS_YES."', 
					CAN_BUY_ZERO = '".ProductTable::STATUS_NO."',
					NEGATIVE_AMOUNT_TRACE = '".ProductTable::STATUS_NO."',
					AVAILABLE = '".ProductTable::STATUS_NO."'
				where 
					TYPE = ".ProductTable::TYPE_SET
		);
		unset($conn);
	}

	protected static function resetSaleReserve(): void
	{
		if (Loader::includeModule('sale'))
		{
			$conn = Application::getConnection();

			$conn->queryExecute("update b_sale_order_dlv_basket set RESERVED_QUANTITY = 0 where 1 = 1");
			$conn->queryExecute("update b_sale_order_delivery set RESERVED='N' where 1 = 1");
		}
	}

	static public function conductedDocumentsExist(): bool
	{
		return Catalog\StoreDocumentTable::getList([
			'select' => ['ID'],
			'filter' => ['STATUS' => 'Y'],
			'count_total' => true,
		])->getCount() > 0;
	}

	static public function isEmpty(): bool
	{
		return self::catalogIsEmpty() && self::storeIsEmpty();
	}

	static private function catalogIsEmpty(): bool
	{
		global $DB;
		$str = "SELECT ID FROM b_catalog_product WHERE QUANTITY > 0 or QUANTITY_RESERVED > 0 limit 1";
		$r = $DB->query($str);
		return !($r->fetch());
	}

	static private function storeIsEmpty(): bool
	{
		global $DB;
		$str = "SELECT ID FROM b_catalog_store_product WHERE AMOUNT > 0 limit 1";
		$r = $DB->query($str);
		return !($r->fetch());
	}

	private static function getPortalZone(): string
	{
		$portalZone = '';

		if (Loader::includeModule('bitrix24'))
		{
			if (method_exists('CBitrix24', 'getLicensePrefix'))
			{
				$licensePrefix = \CBitrix24::getLicensePrefix();
				if ($licensePrefix !== false)
				{
					$portalZone = (string)$licensePrefix;
				}
			}
		}
		elseif (Loader::includeModule('intranet'))
		{
			if (method_exists('CIntranetUtils', 'getPortalZone'))
			{
				$portalZone = \CIntranetUtils::getPortalZone();
			}
		}
		return $portalZone;
	}

	private static function createCatalogStores(): void
	{
		$portalZone = self::getPortalZone();

		$codes = [];

		if ($portalZone === 'ru')
		{
			$codes = [self::STORE_ALIEXPRESS, self::STORE_OZON, self::STORE_SBERMEGAMARKET, self::STORE_WILDBERRIES];

		}
		else if ($portalZone === 'by')
		{
			$codes = [self::STORE_ALIEXPRESS, self::STORE_OZON, self::STORE_WILDBERRIES];
		}
		else if ($portalZone === 'ua')
		{
			$codes = [self::STORE_ALIEXPRESS];
		}

		if(count($codes)>0)
		{
			foreach ($codes as $code)
			{
				$title = Loc::getMessage("CATALOG_USE_STORE_".$code);

				$iterator = Catalog\StoreTable::getList([
					'select' => ['CODE'],
					'filter' => ['=CODE' => $code],
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if (empty($row))
				{
					Catalog\StoreTable::add(['TITLE'=>$title, 'ADDRESS'=>$title, 'CODE'=>$code]);
				}
			}
		}
	}

	private static function installRealizationDocumentTradingPlatform(): void
	{
		if (Loader::includeModule('crm'))
		{
			$platformCode = \Bitrix\Crm\Order\TradingPlatform\RealizationDocument::TRADING_PLATFORM_CODE;
			$platform = \Bitrix\Crm\Order\TradingPlatform\RealizationDocument::getInstanceByCode($platformCode);
			if (!$platform->isInstalled())
			{
				$platform->install();
			}
		}
	}

	private static function deactivateRealizationDocumentTradingPlatform()
	{
		if (Loader::includeModule('crm'))
		{
			$platformCode = \Bitrix\Crm\Order\TradingPlatform\RealizationDocument::TRADING_PLATFORM_CODE;
			$platform = \Bitrix\Crm\Order\TradingPlatform\RealizationDocument::getInstanceByCode($platformCode);
			if ($platform->isInstalled())
			{
				$platform->unsetActive();
			}
		}
	}

	public static function setNeedShowSlider($need)
	{
		$disabled = !(bool)$need;

		$currentUser = CurrentUser::get();
		\CUserOptions::SetOption(UseStore::CATEGORY_NAME, UseStore::OPTION_NAME, $disabled, false, (int)$currentUser->getId());
	}

	public static function needShowSlider(): bool
	{
		$currentUser = CurrentUser::get();
		if (self::isUsed())
		{
			return false;
		}

		return \CUserOptions::GetOption(UseStore::CATEGORY_NAME, UseStore::OPTION_NAME, false, (int)$currentUser->getId()) === false;
	}

	public static function clearNeedShowSlider()
	{
		$currentUser = CurrentUser::get();
		\CUserOptions::DeleteOption(UseStore::CATEGORY_NAME, UseStore::OPTION_NAME, false, (int)$currentUser->getId());
	}

	public static function isPortal(): bool
	{
		return (ModuleManager::isModuleInstalled('bitrix24') || ModuleManager::isModuleInstalled('intranet'));
	}

	public static function isCloud(): bool
	{
		return Loader::includeModule('bitrix24');
	}

	public static function isBitrixSiteManagement (): bool
	{
		return !self::isCloud() && !self::isPortal();
	}
}
