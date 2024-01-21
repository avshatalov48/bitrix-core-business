<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Order\TradingPlatform;
use Bitrix\Crm\Component\EntityDetails\ProductList;
use Bitrix\Crm\Order\Internals\ShipmentRealizationTable;
use Bitrix\Sale\Internals\ShipmentTable;

Loc::loadMessages(__FILE__);

final class UseStore
{
	protected const CATEGORY_NAME = "use_store";

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

	protected static function checkEnablingConditions(): bool
	{
		return (
			self::isCrmExists()
			&& !\CCrmSaleHelper::isWithOrdersMode()
			&& !self::isUsedOneC()
		);
	}

	protected static function enableOptions(): void
	{
		Option::set('catalog', 'default_quantity_trace', 'Y');
		Option::set('catalog', 'default_can_buy_zero', 'Y');
		Option::set('catalog', 'allow_negative_amount', 'Y');
		Option::set('catalog', 'default_use_store_control', 'Y');
		Option::set('catalog', 'enable_reservation', 'Y');
	}

	/**
	 * Enables inventory management and resets all the reserves and quantities
	 * @return bool
	 */
	public static function enable(): bool
	{
		if (!self::checkEnablingConditions())
		{
			return false;
		}

		self::enableOptions();

		self::resetQuantity();
		self::resetQuantityTrace();
		self::resetStoreBatch();
		self::resetSaleReserve();
		self::resetCrmReserve();

		self::installRealizationDocumentTradingPlatform();

		self::registerEventsHandlers();

		self::showEntityProductGridColumns();

		return true;
	}

	protected static function registerEventsHandlers()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->registerEventHandler('sale', 'onBeforeSaleShipmentSetField', 'crm', '\Bitrix\Crm\Order\EventsHandler\Shipment', 'onBeforeSetField');
	}

	protected static function unRegisterEventsHandlers()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->unRegisterEventHandler('sale', 'onBeforeSaleShipmentSetField', 'crm', '\Bitrix\Crm\Order\EventsHandler\Shipment', 'onBeforeSetField');
	}

	/**
	 * Enables inventory management without resetting any reserves or quantities
	 * @return bool
	 */
	public static function enableWithoutResetting(): bool
	{
		if (!self::checkEnablingConditions())
		{
			return false;
		}

		self::enableOptions();

		self::installRealizationDocumentTradingPlatform();

		self::showEntityProductGridColumns();

		return true;
	}

	public static function disable(): bool
	{
		if (!self::isCrmExists())
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

		self::deactivateRealizationDocumentTradingPlatform();

		self::unRegisterEventsHandlers();

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
		$conn->queryExecute('delete from b_catalog_store_barcode where ORDER_ID is null and STORE_ID > 0');
		unset($conn);
	}

	protected static function resetQuantityTrace(): void
	{
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
				QUANTITY_RESERVED = 0,
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
					QUANTITY_RESERVED = 0,
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
					QUANTITY_RESERVED = 0,
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

			$conn->queryExecute("truncate table b_sale_basket_reservation_history");
			$conn->queryExecute("truncate table b_sale_basket_reservation");
		}
	}

	private static function resetCrmReserve(): void
	{
		if (Loader::includeModule('crm'))
		{
			$conn = Application::getConnection();
			$conn->queryExecute("truncate table b_crm_product_row_reservation");
			$conn->queryExecute("truncate table b_crm_product_reservation_map");
		}
	}

	private static function resetStoreBatch(): void
	{
		Application::getConnection()->queryExecute('truncate table b_catalog_store_batch');
		Application::getConnection()->queryExecute('truncate table b_catalog_store_batch_docs_element');
	}

	/**
	 * Delete all shipments a.k.a. realizations and linked entries.
	 *
	 * @return void
	 */
	private static function resetCrmRealizations(): void
	{
		if (Loader::includeModule('crm'))
		{
			$realizations = ShipmentRealizationTable::getList([
				'filter' => [
					'=IS_REALIZATION' => 'Y',
				],
			]);
			foreach ($realizations as $realization)
			{
				ShipmentRealizationTable::delete($realization['ID']);
				ShipmentTable::deleteWithItems($realization['SHIPMENT_ID']);
			}
		}
	}

	/**
	 * Delete all catalog store documents  and linked entries.
	 *
	 * @return void
	 */
	private static function resetStoreDocuments(): void
	{
		global $USER_FIELD_MANAGER;

		$fileIds = Catalog\StoreDocumentFileTable::getList(['select' => ['FILE_ID']])->fetchAll();
		$fileIds = array_column($fileIds, 'FILE_ID');

		foreach ($fileIds as $fileId)
		{
			\CFile::Delete($fileId);
		}

		$documents = Catalog\StoreDocumentTable::getList(['select' => ['ID', 'DOC_TYPE']])->fetchAll();
		foreach ($documents as $document)
		{
			$typeTableClass = Catalog\Document\StoreDocumentTableManager::getTableClassByType($document['DOC_TYPE']);
			if ($typeTableClass)
			{
				$USER_FIELD_MANAGER->Delete($typeTableClass::getUfId(), $document['ID']);
			}
		}

		$conn = Application::getConnection();

		$conn->queryExecute('truncate table b_catalog_store_docs');
		$conn->queryExecute('truncate table b_catalog_docs_element');
		$conn->queryExecute('truncate table b_catalog_docs_barcode');
		$conn->queryExecute('truncate table b_catalog_store_document_file');

		if (Loader::includeModule('crm'))
		{
			\Bitrix\Crm\Timeline\TimelineEntry::deleteByAssociatedEntityType(\CCrmOwnerType::StoreDocument);

			$conn->queryExecute("truncate table b_crm_store_document_contractor");
		}
	}

	/**
	 * Delete all warehouse documents.
	 *
	 * @return void
	 */
	public static function resetDocuments(): void
	{
		self::resetStoreDocuments();
		self::resetCrmRealizations();
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

	/**
	 * Checks for:
	 * - products with inconsistencies between their QUANTITY field and their actual amount between stores
	 * - products that are not in any store but have something in their QUANTITY field
	 * @return bool
	 */
	public static function isQuantityInconsistent(): bool
	{
		$connection = Application::getConnection();

		$productTypes = new SqlExpression('(?i, ?i)', ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER);
		$query = $connection->query("
			select cp.ID, (cp.QUANTITY - (sum(csp.AMOUNT) - sum(csp.QUANTITY_RESERVED))) as QUANTITY_DIFFERENCE from b_catalog_product cp
			inner join b_catalog_store_product csp on cp.ID = csp.PRODUCT_ID
			inner join b_catalog_store cs on cs.ID = csp.STORE_ID
			where cp.TYPE in {$productTypes} and (cs.ACTIVE = 'Y')
			group by cp.ID
			having QUANTITY_DIFFERENCE != 0
			limit 1
		");

		if ($query->fetch())
		{
			return true;
		}

		$query = $connection->query("
			select cp.ID, cp.QUANTITY from b_catalog_product cp
			left outer join b_catalog_store_product csp on cp.ID = csp.PRODUCT_ID
			where cp.TYPE in {$productTypes} and csp.PRODUCT_ID is null and cp.QUANTITY != 0
			limit 1
		");

		if ($query->fetch())
		{
			return true;
		}

		return false;
	}

	public static function doNonEmptyProductsExist(): bool
	{
		$connection = Application::getConnection();

		$productTypes = new SqlExpression('(?i, ?i)', ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER);
		$query = $connection->query("
			select ID from b_catalog_product cp
			where TYPE in {$productTypes} and (QUANTITY != 0 or QUANTITY_RESERVED != 0)
			limit 1
		");

		if ($query->fetch())
		{
			return true;
		}

		$query = $connection->query("
			select ID from b_catalog_store_product csp
			where AMOUNT != 0 or QUANTITY_RESERVED != 0
			limit 1
		");

		if ($query->fetch())
		{
			return true;
		}

		return false;
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

		if ($resetCache)
		{
			Application::getInstance()->getManagedCache()->cleanDir('user_option');
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

	public static function needShowSlider(): bool
	{
		return !self::isUsed();
	}

	public static function isCloud(): bool
	{
		return Loader::includeModule('bitrix24');
	}
}
