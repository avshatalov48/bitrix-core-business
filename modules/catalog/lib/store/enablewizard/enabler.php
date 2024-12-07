<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Catalog;
use Bitrix\Main\EventManager;
use Bitrix\Crm\Order\TradingPlatform;
use Bitrix\Crm\Component\EntityDetails\ProductList;
use Bitrix\Crm\Order\Internals\ShipmentRealizationTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Crm\Timeline\TimelineEntry;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

abstract class Enabler
{
	public static function enable(array $options = []): Result
	{
		$result = new Result();

		if (!self::hasAccess())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('STORE_ENABLE_WIZARD_NO_PERMISSION_ENABLE'),
					0,
					[
						'analyticsCode' => 'no_access',
					],
				)
			);
		}

		if (!self::canBeEnabled())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('STORE_ENABLE_WIZARD_ENABLE_ERROR'),
					0,
					[
						'analyticsCode' => 'conditions_not_met',
					],
				)
			);
		}

		self::enableOptions();
		self::resetQuantities();
		self::installRealizationDocumentTradingPlatform();
		self::registerEventsHandlers();
		self::showEntityProductGridColumns();

		return $result;
	}

	public static function disable(): Result
	{
		$result = new Result();

		if (!self::hasAccess())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('STORE_ENABLE_WIZARD_NO_PERMISSION_DISABLE'),
					0,
					[
						'analyticsCode' => 'no_access',
					],
				)
			);
		}

		if (!self::canBeDisabled())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('STORE_ENABLE_WIZARD_DISABLE_ERROR'),
					0,
					[
						'analyticsCode' => 'conditions_not_met',
					],
				)
			);
		}

		self::disableOptions();
		self::resetQuantities();
		self::deleteDocuments();
		self::uninstallRealizationDocumentTradingPlatform();
		self::unRegisterEventsHandlers();

		return $result;
	}

	private static function canBeEnabled(): bool
	{
		return (
			self::isCrmIncluded()
			&& !\CCrmSaleHelper::isWithOrdersMode()
		);
	}

	private static function canBeDisabled(): bool
	{
		return self::isCrmIncluded();
	}

	private static function enableOptions(): void
	{
		Option::set('catalog', 'default_quantity_trace', 'Y');
		Option::set('catalog', 'default_can_buy_zero', 'Y');
		Option::set('catalog', 'allow_negative_amount', 'Y');
		Option::set('catalog', 'default_use_store_control', 'Y');
		Option::set('catalog', 'enable_reservation', 'Y');
	}

	private static function disableOptions(): void
	{
		Option::set('catalog', 'default_use_store_control', 'N');
		Option::set('catalog', 'default_quantity_trace', 'N');
	}

	private static function resetQuantities(): void
	{
		if (!ConditionsChecker::doesProductWithQuantityExist())
		{
			return;
		}

		self::resetQuantity();
		self::resetQuantityTrace();
		self::resetSaleReserve();
		self::resetCrmReserve();
	}

	private static function deleteDocuments(): void
	{
		if (!ConditionsChecker::doesConductedDocumentExist())
		{
			return;
		}

		self::resetStoreBatch();
		self::deleteStoreDocuments();
		self::deleteRealizations();
	}

	private static function resetQuantity(): void
	{
		$conn = Application::getConnection();
		$conn->queryExecute('truncate table b_catalog_store_product');
		$conn->queryExecute('delete from b_catalog_store_barcode where ORDER_ID is null and STORE_ID > 0');
		unset($conn);
	}

	private static function resetQuantityTrace(): void
	{
		self::resetQuantityTraceMainTypes();
		self::resetQuantityTraceSku();
		self::resetQuantityTraceEmptySku();
		self::resetQuantityTraceSets();
	}

	private static function resetQuantityTraceMainTypes(): void
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

	private static function resetQuantityTraceSku(): void
	{
		if (
			!self::isCloud()
			&& Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y'
		)
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

	private static function resetQuantityTraceEmptySku(): void
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

	private static function resetQuantityTraceSets(): void
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

	private static function resetStoreBatch(): void
	{
		Application::getConnection()->queryExecute('truncate table b_catalog_store_batch');
		Application::getConnection()->queryExecute('truncate table b_catalog_store_batch_docs_element');
	}

	private static function resetSaleReserve(): void
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
		if (!self::isCrmIncluded())
		{
			return;
		}

		Application::getConnection()->queryExecute("truncate table b_crm_product_row_reservation");
		Application::getConnection()->queryExecute("truncate table b_crm_product_reservation_map");
	}

	private static function deleteStoreDocuments(): void
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

		if (self::isCrmIncluded())
		{
			TimelineEntry::deleteByAssociatedEntityType(\CCrmOwnerType::StoreDocument);

			$conn->queryExecute("truncate table b_crm_store_document_contractor");
		}
	}

	private static function deleteRealizations(): void
	{
		if (!self::isCrmIncluded())
		{
			return;
		}

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

	private static function installRealizationDocumentTradingPlatform(): void
	{
		if (!self::isCrmIncluded())
		{
			return;
		}

		$platformCode = TradingPlatform\RealizationDocument::TRADING_PLATFORM_CODE;
		$platform = TradingPlatform\RealizationDocument::getInstanceByCode($platformCode);
		if (!$platform->isInstalled())
		{
			$platform->install();
		}
	}

	private static function uninstallRealizationDocumentTradingPlatform(): void
	{
		if (!self::isCrmIncluded())
		{
			return;
		}

		$platformCode = TradingPlatform\RealizationDocument::TRADING_PLATFORM_CODE;
		$platform = TradingPlatform\RealizationDocument::getInstanceByCode($platformCode);
		if ($platform->isInstalled())
		{
			$platform->unsetActive();
		}
	}

	private static function registerEventsHandlers()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->registerEventHandler('sale', 'onBeforeSaleShipmentSetField', 'crm', '\Bitrix\Crm\Order\EventsHandler\Shipment', 'onBeforeSetField');
	}

	private static function unRegisterEventsHandlers()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->unRegisterEventHandler('sale', 'onBeforeSaleShipmentSetField', 'crm', '\Bitrix\Crm\Order\EventsHandler\Shipment', 'onBeforeSetField');
	}

	private static function showEntityProductGridColumns(): void
	{
		if (!self::isCrmIncluded())
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
	}

	private static function isCloud(): bool
	{
		return Loader::includeModule('bitrix24');
	}

	private static function isCrmIncluded(): bool
	{
		return Loader::includeModule('crm');
	}

	private static function hasAccess(): bool
	{
		$accessController = AccessController::getCurrent();

		return (
			$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& $accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
		);
	}
}
