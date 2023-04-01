<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Permission;

use Bitrix\Catalog\StoreTable;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Category\DealCategory;
use Bitrix\Main\Access\Permission;
use Bitrix\Main\Loader;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Integration\Report\Dashboard\DashboardManager;

class PermissionDictionary extends Permission\PermissionDictionary
{
	public const VALUE_VARIATION_ALL = -1;

	public const CATALOG_INVENTORY_MANAGEMENT_ACCESS = 1;
	public const CATALOG_STORE_MODIFY = 2;
	public const CATALOG_STORE_VIEW = 3;
	public const CATALOG_STORE_ANALYTIC_VIEW = 4;

	public const CATALOG_RESERVE_DEAL = 101;
	public const CATALOG_STORE_RESERVE = 102;

	public const CATALOG_STORE_DOCUMENT = 200;
	public const CATALOG_STORE_DOCUMENT_VIEW = 201;
	public const CATALOG_STORE_DOCUMENT_MODIFY = 202;
	public const CATALOG_STORE_DOCUMENT_CANCEL = 203;
	public const CATALOG_STORE_DOCUMENT_CONDUCT = 204;
	public const CATALOG_STORE_DOCUMENT_DELETE = 205;
	public const CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY = 206;

	public const CATALOG_SETTINGS_ACCESS = 301;
	public const CATALOG_SETTINGS_EDIT_RIGHTS = 302;
	public const CATALOG_SETTINGS_SELL_NEGATIVE_COMMODITIES = 303;
	public const CATALOG_SETTINGS_PRODUCT_CARD_EDIT = 304;
	public const CATALOG_SETTINGS_PRODUCT_CARD_SET_PROFILE_FOR_USERS = 305;
	public const CATALOG_SETTINGS_STORE_DOCUMENT_CARD_EDIT = 306;
	public const CATALOG_RESERVE_SETTINGS = 307;
	public const CATALOG_VAT_MODIFY = 308;
	public const CATALOG_MEASURE_MODIFY = 309;
	public const CATALOG_PRICE_GROUP_MODIFY = 310;
	public const CATALOG_IMPORT_EDIT = 311;
	public const CATALOG_EXPORT_EDIT = 312;

	public const CATALOG_PRODUCT_VIEW = 400;
	public const CATALOG_PRODUCT_READ = 401;
	public const CATALOG_PRODUCT_ADD = 402;
	public const CATALOG_PRODUCT_EDIT = 403;
	public const CATALOG_PRODUCT_DELETE = 404;
	public const CATALOG_PRODUCT_PURCHASING_PRICE_VIEW = 405;
	public const CATALOG_PRODUCT_EDIT_CATALOG_PRICE = 406;
	public const CATALOG_PRODUCT_EDIT_ENTITY_PRICE = 407;
	public const CATALOG_PRODUCT_SET_DISCOUNT = 408;
	public const CATALOG_PRODUCT_PUBLIC_VISIBILITY = 409;
	public const CATALOG_EXPORT_EXECUTION = 414;
	public const CATALOG_IMPORT_EXECUTION = 415;
	public const CATALOG_PRODUCT_PRICE_EXTRA_EDIT = 416;

	/** @var array */
	protected static $stores;

	/** @var array */
	protected static $dynamicTypes;

	/** @var array */
	protected static $storeAnalitycs;

	/** @var array */
	protected static $priceEntities;

	/** @var array */
	protected static $dealCategories;

	public static function getPermission($permissionId): array
	{
		$permission = parent::getPermission($permissionId);
		$storePermissions = [
			self::CATALOG_STORE_VIEW,
			self::CATALOG_STORE_RESERVE,
		];

		$permissionId = (int)$permissionId;
		if (in_array($permissionId, $storePermissions, true))
		{
			$permission['type'] = Permission\PermissionDictionary::TYPE_MULTIVARIABLES;
			$permission['enableSearch'] = true;
			$permission['variables'] = self::getStoreVariables();
			$permission['hintTitle'] = Loc::getMessage('CATALOG_STORE_VIEW_DESCRIPTION_HINT');
		}
		elseif ($permissionId === self::CATALOG_RESERVE_DEAL)
		{
			$permission['type'] = Permission\PermissionDictionary::TYPE_MULTIVARIABLES;
			$permission['variables'] = self::getDealCategoryVariables();
			$permission['showAvatars'] = false;
			$permission['compactView'] = true;
			$permission['hintTitle'] = Loc::getMessage('CATALOG_RESERVE_DEAL_DESCRIPTION_HINT');
		}
		elseif (
			$permissionId === self::CATALOG_PRODUCT_EDIT_ENTITY_PRICE
			|| $permissionId === self::CATALOG_PRODUCT_SET_DISCOUNT
		)
		{
			$permission['type'] = Permission\PermissionDictionary::TYPE_MULTIVARIABLES;
			$permission['variables'] = self::getPriceSelectorVariables();
			$permission['showAvatars'] = false;
			$permission['compactView'] = true;
		}
		elseif ($permissionId === self::CATALOG_STORE_ANALYTIC_VIEW)
		{
			$permission['type'] = Permission\PermissionDictionary::TYPE_MULTIVARIABLES;
			$permission['variables'] = self::getStoreAnalyticVariables();
			$permission['showAvatars'] = false;
			$permission['compactView'] = true;
		}

		if ($permission['type'] === Permission\PermissionDictionary::TYPE_MULTIVARIABLES)
		{
			$permission['allSelectedCode'] = static::VALUE_VARIATION_ALL;
		}

		return $permission;
	}

	public static function getDefaultPermissionValue($permissionId): int
	{
		$permission = static::getPermission($permissionId);
		if ($permission['type'] === static::TYPE_MULTIVARIABLES)
		{
			return static::VALUE_VARIATION_ALL;
		}

		return static::VALUE_YES;
	}

	public static function getStoreDocumentPermissionRules(array $permissions, array $documents = null): array
	{
		$result = [];

		if ($documents === null)
		{
			$documents = [
				StoreDocumentTable::TYPE_ARRIVAL,
				StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
				StoreDocumentTable::TYPE_MOVING,
				StoreDocumentTable::TYPE_RETURN,
				StoreDocumentTable::TYPE_DEDUCT,
				StoreDocumentTable::TYPE_SALES_ORDERS,
			];
		}

		foreach ($documents as $document)
		{
			foreach ($permissions as $permissionId)
			{
				$result[] = self::getStoreDocumentPermissionId($permissionId, $document);
			}
		}

		return $result;
	}


	public static function getStoreDocumentPermission($permissionId, $documentCode): array
	{
		$permission = self::getPermission($permissionId);
		$permission['id'] = self::getStoreDocumentPermissionId($permissionId, $documentCode);

		// personal langs
		$fields = [
			'title' => self::getName($permissionId) . '_' . $documentCode,
			'hint' => self::HINT_PREFIX . self::getName($permissionId) . '_' . $documentCode,
		];
		foreach ($fields as $field => $langMessage)
		{
			$fieldValue = Loc::getMessage($langMessage);
			if ($fieldValue)
			{
				$permission[$field] = $fieldValue;
			}
		}

		return $permission;
	}

	public static function getStoreDocumentPermissionId($permissionId, $documentCode): string
	{
		return "{$permissionId}_{$documentCode}";
	}

	public static function getStoreVariables(): array
	{
		if (static::$stores !== null)
		{
			return static::$stores;
		}

		$items = [];
		$stores = StoreTable::getList([
			'select' => ['ID', 'TITLE', 'ADDRESS'],
			'cache' => [
				'ttl' => 3600
			]
		]);

		while ($store = $stores->fetch())
		{
			$title = $store['TITLE'];
			if ($title === '')
			{
				$title = $store['ADDRESS'];
			}
			$items[] = [
				'id' => $store['ID'],
				'title' => htmlspecialcharsbx($title),
				'entityId' => 'store',
			];
		}

		static::$stores = $items;

		return static::$stores;
	}

	public static function getDealCategoryVariables(): array
	{
		if (static::$dealCategories !== null)
		{
			return static::$dealCategories;
		}

		if (!Loader::includeModule('crm'))
		{
			static::$dealCategories = [];

			return static::$dealCategories;
		}

		$items = [];
		$dealCategories = DealCategory::getSelectListItems();
		foreach ($dealCategories as $key => $dealCategory)
		{
			$items[] = [
				'id' => $key,
				'title' => htmlspecialcharsbx($dealCategory),
				'avatar' => null,
			];
		}

		static::$dealCategories = $items;

		return static::$dealCategories;
	}

	public static function getAvailableStoreDocuments(): array
	{
		return [
			StoreDocumentTable::TYPE_ARRIVAL,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
			StoreDocumentTable::TYPE_MOVING,
			StoreDocumentTable::TYPE_RETURN,
			StoreDocumentTable::TYPE_DEDUCT,
			StoreDocumentTable::TYPE_SALES_ORDERS,
		];
	}

	public static function getPriceSelectorVariables()
	{
		if (static::$priceEntities !== null)
		{
			return static::$priceEntities;
		}

		static::$priceEntities = [];
		if (!Loader::includeModule('crm'))
		{
			return static::$priceEntities;
		}

		$items = [
			\CCrmOwnerType::Deal => 'deal',
			\CCrmOwnerType::Lead => 'lead',
			\CCrmOwnerType::SmartInvoice => 'invoice',
			\CCrmOwnerType::Quote => 'quote',
			\CCrmOwnerType::Order => 'order',
		];

		foreach ($items as $crmItem => $entityId)
		{
			static::$priceEntities[] = [
				'id' => $crmItem,
				'title' => \CCrmOwnerType::GetDescription($crmItem),
				'entityId' => $entityId,
			];
		}

		static::$priceEntities = array_merge(static::$priceEntities, self::getDynamicTypeVariables());

		return static::$priceEntities;
	}

	private static function getDynamicTypeVariables(): array
	{
		if (static::$dynamicTypes !== null)
		{
			return static::$dynamicTypes;
		}

		if (!Loader::includeModule('crm'))
		{
			static::$dynamicTypes = [];

			return static::$dynamicTypes;
		}

		$items = [];
		$types = Container::getInstance()
			->getDynamicTypesMap()
			->load()
			->getTypes()
		;

		foreach ($types as $type)
		{
			$items[] = [
				'id' => $type->getEntityTypeId(),
				'title' => $type->getTitle(),
				'supertitle' => Loc::getMessage('CRM_DYNAMIC_TYPE_NAME'),
			];
		}

		static::$dynamicTypes = $items;

		return static::$dynamicTypes;
	}
	public static function getStoreAnalyticVariables(): array
	{
		if (static::$storeAnalitycs !== null)
		{
			return static::$storeAnalitycs;
		}

		$items = [];
		if (Loader::includeModule('report'))
		{
			$dashboards = DashboardManager::getCatalogDashboardList();
			foreach ($dashboards as $dashboard)
			{
				$items[] = [
					'id' => $dashboard->getAccessBoardId(),
					'title' => $dashboard->getBoardTitle(),
				];
			}
		}

		static::$storeAnalitycs = $items;

		return static::$storeAnalitycs;
	}
}
