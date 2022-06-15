<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog;
use Bitrix\Catalog\Component\Preset\Factory;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Order\TradingPlatform;
use Bitrix\Crm\Component\EntityDetails\ProductList;

Loc::loadMessages(__FILE__);

final class UseStore
{
	protected const CATEGORY_NAME = "use_store";
	protected const OPTION_NAME = "catalog.warehouse.master.clear";

	protected const STORE_WILDBERRIES = "WILDBERRIES";
	protected const STORE_SBERMEGAMARKET = "SBERMEGAMARKET";
	protected const STORE_OZON = "OZON";
	protected const STORE_ALIEXPRESS = "ALIEXPRESS";

	public const URL_PARAM_STORE_MASTER_HIDE = "STORE_MASTER_HIDE";

	public static function isUsed(): bool
	{
		return State::isUsedInventoryManagement();
	}

	public static function isUsedOneC(): bool
	{
		return Option::get('catalog', 'once_inventory_management') === 'Y';
	}

	public static function enableOnec(): bool
	{
		Option::set('catalog', 'once_inventory_management', 'Y');

		return true;
	}

	public static function disableOnec(): bool
	{
		Option::set('catalog', 'once_inventory_management', 'N');

		return true;
	}

	public static function isPlanRestricted(): bool
	{
		return !Catalog\Config\Feature::isInventoryManagementEnabled();
	}

	/**
	 * @return bool
	 */
	protected static function isCrmExists(): bool
	{
		return Loader::includeModule('crm');
	}

	/**
	 * @return bool
	 */
	protected static function shouldManageQuantityTrace(): bool
	{
		if (!self::isCrmExists())
		{
			return false;
		}

		return !\CCrmSaleHelper::isWithOrdersMode();
	}

	/**
	 * @return bool
	 */
	protected static function isSeparateSku(): bool
	{
		return Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
	}

	public static function enable(): bool
	{
		if (!self::isCrmExists())
		{
			return false;
		}
		if (\CCrmSaleHelper::isWithOrdersMode())
		{
			return false;
		}

		Option::set('catalog', 'default_quantity_trace', 'Y');
		Option::set('catalog', 'default_can_buy_zero', 'Y');
		Option::set('catalog', 'allow_negative_amount', 'Y');

		self::resetQuantity();

		Option::set('catalog', 'default_use_store_control', 'Y');
		Option::set('catalog', 'enable_reservation', 'Y');

		self::resetSaleReserve();

		self::installRealizationDocumentTradingPlatform();

		self::showEntityProductGridColumns();
		self::setNeedShowSlider(false);

		return true;
	}

	public static function installCatalogStores()
	{
		if (self::hasDefaultCatalogStore() === false)
		{
			$storeId = self::getFirstCatalogStore();
			if ($storeId > 0)
			{
				self::setDefaultCatalogStore($storeId);
			}
			else
			{
				self::createDefaultCatalogStore();
			}
		}

		if (self::isBitrixSiteManagement() === false)
		{
			self::createCatalogStores();
		}
	}

	protected static function hasDefaultCatalogStore(): bool
	{
		return Catalog\StoreTable::getDefaultStoreId() !== null;
	}

	protected static function getFirstCatalogStore(): int
	{
		$iterator = Catalog\StoreTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=SITE_ID' => '',
			],
			'limit' => 1,
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);

		return (!empty($row) ? (int)$row['ID'] : 0);
	}

	protected static function setDefaultCatalogStore($storeId): bool
	{
		$r = Catalog\StoreTable::update(
			$storeId,
			[
				'IS_DEFAULT' => 'Y',
			]
		);

		return $r->isSuccess();
	}

	protected static function createDefaultCatalogStore(): bool
	{
		$title = Loc::getMessage('CATALOG_USE_STORE_DEFAULT');
		$r = Catalog\StoreTable::add([
			'TITLE' => $title,
			'ADDRESS' => $title,
		]);

		return $r->isSuccess();
	}

	public static function disable(): bool
	{
		if (!self::isCrmExists())
		{
			return false;
		}
		if (self::conductedDocumentsExist())
		{
			return false;
		}

		Option::set('catalog', 'default_use_store_control', 'N');
		Option::set('catalog', 'default_quantity_trace', 'N');

		if (self::shouldManageQuantityTrace())
		{
			self::disableQuantityTraceMainTypes();
			self::disableQuantityTraceSku();
			self::disableQuantityTraceEmptySku();
			self::disableQuantityTraceSets();
		}

		self::clearNeedShowSlider();
		self::deactivateRealizationDocumentTradingPlatform();

		if (Loader::includeModule('pull'))
		{
			\CPullWatch::AddToStack(
				'CATALOG_INVENTORY_MANAGEMENT_CHANGED',
				[
					'module_id' => 'crm',
					'command' => 'onCatalogInventoryManagementDisabled',
				],
			);
		}

		return true;
	}

	protected static function resetQuantity(): void
	{
		$conn = Application::getConnection();
		$conn->queryExecute('truncate table b_catalog_store_product');
		$conn->queryExecute('delete from b_catalog_store_barcode where ORDER_ID is null');
		unset($conn);

		self::resetQuantityTraceMainTypes();
		self::resetQuantityTraceSku();
		self::resetQuantityTraceEmptySku();
		self::resetQuantityTraceSets();
	}

	protected static function resetQuantityTraceMainTypes(): void
	{
		$mainTypes = implode(
			', ',
			[
				ProductTable::TYPE_PRODUCT,
				ProductTable::TYPE_OFFER,
				ProductTable::TYPE_FREE_OFFER,
			]
		);

		Application::getConnection()->queryExecute("
			update b_catalog_product
			set 
				QUANTITY = 0,
				QUANTITY_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_DEFAULT . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
				AVAILABLE = '" . ProductTable::STATUS_YES . "'
			where 
				TYPE in (" . $mainTypes . ")
		");
	}

	protected static function resetQuantityTraceSku(): void
	{
		if (!self::isCloud() && self::isSeparateSku())
		{
			Application::getConnection()->queryExecute("
				update b_catalog_product
				set
					QUANTITY = 0,
					QUANTITY_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
					CAN_BUY_ZERO = '" . ProductTable::STATUS_DEFAULT . "',
					NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
					AVAILABLE = '" . ProductTable::STATUS_YES . "'
				where
					TYPE = " . ProductTable::TYPE_SKU
			);
		}
		else
		{
			Application::getConnection()->queryExecute("
				update b_catalog_product
				set
					QUANTITY = 0,
					QUANTITY_TRACE = '" . ProductTable::STATUS_NO . "',
					CAN_BUY_ZERO = '" . ProductTable::STATUS_YES . "',
					NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_YES . "',
					AVAILABLE = '" . ProductTable::STATUS_YES . "'
				where
					TYPE = " . ProductTable::TYPE_SKU
			);
		}
	}

	protected static function resetQuantityTraceEmptySku(): void
	{
		if (!self::isCloud())
		{
			return;
		}
		Application::getConnection()->queryExecute("
			update b_catalog_product
			set 
				QUANTITY = 0,
				QUANTITY_TRACE = '" . ProductTable::STATUS_YES . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_NO . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_NO . "',
				AVAILABLE = '" . ProductTable::STATUS_NO . "',
				TYPE = " . ProductTable::TYPE_PRODUCT . "
			where 
				TYPE = " . ProductTable::TYPE_EMPTY_SKU
		);
	}

	protected static function resetQuantityTraceSets(): void
	{
		Application::getConnection()->queryExecute("
			update b_catalog_product 
			set 
				QUANTITY = 0,
				QUANTITY_TRACE = '" . ProductTable::STATUS_NO . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_YES . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_YES . "',
				AVAILABLE = '" . ProductTable::STATUS_YES . "'
			where 
				TYPE = " . ProductTable::TYPE_SET
		);
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

	protected static function disableQuantityTraceMainTypes(): void
	{
		$mainTypes = implode(
			', ',
			[
				ProductTable::TYPE_PRODUCT,
				ProductTable::TYPE_OFFER,
				ProductTable::TYPE_FREE_OFFER,
			]
		);

		Application::getConnection()->queryExecute("
			update b_catalog_product
			set 
				QUANTITY_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_DEFAULT . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
				AVAILABLE = '" . ProductTable::STATUS_YES . "'
			where 
				TYPE in (" . $mainTypes . ")
		");
	}

	protected static function disableQuantityTraceSku(): void
	{
		Application::getConnection()->queryExecute("
			update b_catalog_product
			set
				QUANTITY_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_DEFAULT . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_DEFAULT . "',
				AVAILABLE = '" . ProductTable::STATUS_YES . "'
			where
				TYPE = " . ProductTable::TYPE_SKU
		);
	}

	protected static function disableQuantityTraceEmptySku(): void
	{
		if (!self::isCloud())
		{
			return;
		}
		Application::getConnection()->queryExecute("
			update b_catalog_product
			set 
				QUANTITY = 0,
				QUANTITY_TRACE = '" . ProductTable::STATUS_YES . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_NO . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_NO . "',
				AVAILABLE = '" . ProductTable::STATUS_NO . "'
			where 
				TYPE = " . ProductTable::TYPE_EMPTY_SKU
		);
	}

	protected static function disableQuantityTraceSets(): void
	{
		Application::getConnection()->queryExecute("
			update b_catalog_product 
			set 
				QUANTITY_TRACE = '" . ProductTable::STATUS_NO . "',
				CAN_BUY_ZERO = '" . ProductTable::STATUS_YES . "',
				NEGATIVE_AMOUNT_TRACE = '" . ProductTable::STATUS_YES . "',
				AVAILABLE = '" . ProductTable::STATUS_YES . "'
			where 
				TYPE = " . ProductTable::TYPE_SET
		);
	}

	public static function conductedDocumentsExist(): bool
	{
		$iterator = Catalog\StoreDocumentTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=STATUS' => 'Y',
			],
			'limit' => 1,
		]);
		$row = $iterator->fetch();
		unset($iterator);

		return !empty($row);
	}

	public static function isEmpty(): bool
	{
		return self::catalogIsEmpty() && self::storeIsEmpty();
	}

	private static function catalogIsEmpty(): bool
	{
		global $DB;
		$str = "SELECT ID FROM b_catalog_product WHERE QUANTITY > 0 or QUANTITY_RESERVED > 0 limit 1";
		$r = $DB->query($str);

		return !($r->fetch());
	}

	private static function storeIsEmpty(): bool
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

	public static function getCodesStoreByZone() :array
	{
		$result = [];

		$portalZone = self::getPortalZone();

		if ($portalZone === 'ru')
		{
			$result = [
				self::STORE_ALIEXPRESS,
				self::STORE_OZON,
				self::STORE_SBERMEGAMARKET,
				self::STORE_WILDBERRIES,
			];
		}
		else if ($portalZone === 'by')
		{
			$result = [
				self::STORE_ALIEXPRESS,
				self::STORE_OZON,
				self::STORE_WILDBERRIES,
			];
		}
		else if ($portalZone === 'ua')
		{
			$result = [
				self::STORE_ALIEXPRESS,
			];
	}

		return $result;
	}

	private static function createCatalogStores(): void
	{
		$codes = self::getCodesStoreByZone();

		if (!empty($codes))
		{
			foreach ($codes as $code)
			{
				$title = Loc::getMessage('CATALOG_USE_STORE_' . $code);

				$iterator = Catalog\StoreTable::getList([
					'select' => [
						'CODE',
					],
					'filter' => [
						'=CODE' => $code,
					],
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if (empty($row))
				{
					Catalog\StoreTable::add([
						'TITLE' => $title,
						'ADDRESS' => $title,
						'CODE' => $code,
					]);
				}
			}
		}
	}

	private static function installRealizationDocumentTradingPlatform(): void
	{
		if (Loader::includeModule('crm'))
		{
			$platformCode = TradingPlatform\RealizationDocument::TRADING_PLATFORM_CODE;
			$platform = TradingPlatform\RealizationDocument::getInstanceByCode($platformCode);
			if (!$platform->isInstalled())
			{
				$platform->install();
			}
		}
	}

	private static function showEntityProductGridColumns(): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		$headers = [
			'STORE_INFO',
			'STORE_AVAILABLE',
			'RESERVE_INFO',
			'ROW_RESERVED',
			'DEDUCTED_INFO',
		];

		$allHeaderMap = ProductList::getHeaderDefaultMap();
		$allHeaders = array_keys($allHeaderMap);

		$gridId = ProductList::DEFAULT_GRID_ID;
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$queryResult = $connection->query(/** @lang MySQL */
			"SELECT ID, VALUE FROM b_user_option WHERE CATEGORY = 'main.interface.grid' AND NAME = '{$sqlHelper->forSql($gridId)}'"
		);

		$resetCache = false;
		while ($gridSettings = $queryResult->fetch())
		{
			$optionID = (int)$gridSettings['ID'];
			$value = $gridSettings['VALUE'];
			if (!$value)
			{
				continue;
			}

			$options = unserialize($value, ['allowed_classes' => false]);
			if (
				!is_array($options)
				|| empty($options)
				|| !isset($options['views'])
				|| !is_array($options['views'])
			)
			{
				continue;
			}

			$changed = false;
			foreach ($options['views'] as &$view)
			{
				if (!isset($view['columns']) || $view['columns'] === '')
				{
					continue;
				}

				$allUsedColumns = explode(',', $view['columns']);
				$currentHeadersInDefaultPosition = array_values(
					array_intersect($allHeaders, array_merge($allUsedColumns, $headers))
				);
				$headers = array_values(array_intersect($allHeaders, $headers));

				foreach ($headers as $header)
				{
					if (in_array($header, $allUsedColumns, true))
					{
						continue;
					}

					$insertPosition = array_search($header, $currentHeadersInDefaultPosition, true);
					array_splice($allUsedColumns, $insertPosition, 0, $header);
					$changed = true;
				}

				if ($changed)
				{
					$view['columns'] = implode(',', $allUsedColumns);
				}
			}
			unset($view);

			if ($changed)
			{
				$sqlValue = $sqlHelper->forSql(serialize($options));
				$connection->queryExecute(/** @lang MySQL */
					"UPDATE b_user_option SET VALUE = '{$sqlValue}' WHERE ID ='{$optionID}'"
				);
				$resetCache = true;
			}
		}

		if ($resetCache && isset($GLOBALS['CACHE_MANAGER']) && is_object($GLOBALS['CACHE_MANAGER']))
		{
			/** @global \CCacheManager $CACHE_MANAGER */
			global $CACHE_MANAGER;
			$CACHE_MANAGER->cleanDir('user_option');
		}

		if (Loader::includeModule('pull'))
		{
			\CPullWatch::AddToStack(
				'CATALOG_INVENTORY_MANAGEMENT_CHANGED',
				[
					'module_id' => 'crm',
					'command' => 'onCatalogInventoryManagementEnabled',
				],
			);
		}
	}

	private static function deactivateRealizationDocumentTradingPlatform()
	{
		if (Loader::includeModule('crm'))
		{
			$platformCode = TradingPlatform\RealizationDocument::TRADING_PLATFORM_CODE;
			$platform = TradingPlatform\RealizationDocument::getInstanceByCode($platformCode);
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
		\CUserOptions::SetOption(
			self::CATEGORY_NAME,
			self::OPTION_NAME,
			$disabled,
			false,
			(int)$currentUser->getId()
		);
	}

	public static function needShowSlider(): bool
	{
		$currentUser = CurrentUser::get();
		if (self::isUsed())
		{
			return false;
		}

		return \CUserOptions::GetOption(
			self::CATEGORY_NAME,
			self::OPTION_NAME,
			false,
			(int)$currentUser->getId()
		) === false;
	}

	public static function clearNeedShowSlider()
	{
		$currentUser = CurrentUser::get();
		\CUserOptions::DeleteOption(
			self::CATEGORY_NAME,
			self::OPTION_NAME,
			false,
			(int)$currentUser->getId()
		);
	}

	public static function isPortal(): bool
	{
		return (
			ModuleManager::isModuleInstalled('bitrix24')
			|| ModuleManager::isModuleInstalled('intranet')
		);
	}

	public static function isCloud(): bool
	{
		return Loader::includeModule('bitrix24');
	}

	public static function isBitrixSiteManagement (): bool
	{
		return !self::isCloud() && !self::isPortal();
	}

	static public function installPreset($list)
	{
		foreach (Catalog\Component\Preset\Enum::getAllType() as $type)
		{
			in_array($type, $list) ?
				Factory::create($type)->enable() :
				Factory::create($type)->disable();
		}
	}

	static public function resetPreset()
	{
		foreach (Catalog\Component\Preset\Enum::getAllType() as $type)
		{
			Factory::create($type)->disable();
		}
	}
}
