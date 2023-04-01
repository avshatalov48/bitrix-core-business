<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Catalog\Access;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ActionDictionary
{
	public const PREFIX = "ACTION_";

	/**
	 * The right to access the catalog and the module settings
	 */
	public const ACTION_CATALOG_READ = 'catalog_read';
	/**
	 * The right to access the catalog (without access the module settings).
	 */
	public const ACTION_CATALOG_VIEW = 'catalog_view';
	public const ACTION_STORE_VIEW = 'catalog_store';
	public const ACTION_STORE_ANALYTIC_VIEW = 'catalog_store_analytic';
	public const ACTION_VAT_EDIT = 'catalog_vat';
	public const ACTION_MEASURE_EDIT = 'catalog_measure';
	public const ACTION_CATALOG_IMPORT_EDIT = 'catalog_import_edit';
	public const ACTION_CATALOG_EXPORT_EDIT = 'catalog_export_edit';
	public const ACTION_CATALOG_IMPORT_EXECUTION = 'catalog_import_exec';
	public const ACTION_CATALOG_EXPORT_EXECUTION = 'catalog_export_exec';
	public const ACTION_INVENTORY_MANAGEMENT_ACCESS = 'catalog_inventory_management_access';
	public const ACTION_STORE_MODIFY = 'catalog_store_modify';
	public const ACTION_DEAL_PRODUCT_RESERVE = 'catalog_deal_product_reserve';
	public const ACTION_STORE_PRODUCT_RESERVE = 'catalog_store_product_reserve';
	public const ACTION_RESERVED_SETTINGS_ACCESS = 'catalog_setting_access';
	public const ACTION_STORE_DOCUMENT_VIEW = 'catalog_store_document_view';
	public const ACTION_STORE_DOCUMENT_PERFORM = 'catalog_store_document_perform';
	public const ACTION_STORE_DOCUMENT_MODIFY = 'catalog_store_document_modify';
	public const ACTION_STORE_DOCUMENT_CANCEL = 'catalog_store_document_cancel';
	public const ACTION_STORE_DOCUMENT_CONDUCT = 'catalog_store_document_conduct';
	public const ACTION_STORE_DOCUMENT_DELETE = 'catalog_store_document_delete';
	public const ACTION_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY = 'catalog_store_document_allow_negation_product_quantity';
	public const ACTION_CATALOG_SETTINGS_ACCESS = 'catalog_settings';
	public const ACTION_CATALOG_RIGHTS_EDIT = 'catalog_rights_edit';
	public const ACTION_SELL_NEGATIVE_COMMODITIES_SETTINGS_EDIT = 'catalog_sell_negative_commodities_settings_edit';
	public const ACTION_PRODUCT_CARD_EDIT = 'catalog_product_card_edit';
	public const ACTION_PRODUCT_CARD_SETTINGS_FOR_USERS_SET = 'catalog_product_card_settings_for_users_set';
	public const ACTION_STORE_DOCUMENT_CARD_EDIT = 'catalog_document_card_edit';
	public const ACTION_PRODUCT_VIEW = 'catalog_product_view';
	public const ACTION_PRODUCT_ADD = 'catalog_product_add';
	public const ACTION_PRODUCT_EDIT = 'catalog_product_edit';
	public const ACTION_PRODUCT_DELETE = 'catalog_product_delete';
	public const ACTION_PRODUCT_PURCHASE_INFO_VIEW = 'catalog_purchas_info';
	public const ACTION_PRODUCT_DISCOUNT_SET = 'catalog_discount';
	public const ACTION_PRODUCT_PRICE_EXTRA_EDIT = 'catalog_extra';
	public const ACTION_PRICE_GROUP_EDIT = 'catalog_group';
	public const ACTION_PRICE_EDIT = 'catalog_price';
	public const ACTION_PRICE_ENTITY_EDIT = 'catalog_entity_price';
	public const ACTION_PRODUCT_PUBLIC_VISIBILITY_SET = 'catalog_product_public_visibility_set';

	protected static function getClassName()
	{
		return __CLASS__;
	}

	/**
	 * permission on action
	 * @return array
	 */
	public static function getActionPermissionMap(): array
	{
		return [
			self::ACTION_CATALOG_VIEW => PermissionDictionary::CATALOG_PRODUCT_VIEW,
			self::ACTION_CATALOG_READ => PermissionDictionary::CATALOG_PRODUCT_READ,
			self::ACTION_VAT_EDIT => PermissionDictionary::CATALOG_VAT_MODIFY,
			self::ACTION_CATALOG_IMPORT_EDIT => PermissionDictionary::CATALOG_IMPORT_EDIT,
			self::ACTION_CATALOG_EXPORT_EDIT => PermissionDictionary::CATALOG_EXPORT_EDIT,
			self::ACTION_CATALOG_EXPORT_EXECUTION => PermissionDictionary::CATALOG_EXPORT_EXECUTION,
			self::ACTION_CATALOG_IMPORT_EXECUTION => PermissionDictionary::CATALOG_IMPORT_EXECUTION,
			self::ACTION_PRODUCT_PURCHASE_INFO_VIEW => PermissionDictionary::CATALOG_PRODUCT_PURCHASING_PRICE_VIEW,
			self::ACTION_PRODUCT_PRICE_EXTRA_EDIT => PermissionDictionary::CATALOG_PRODUCT_PRICE_EXTRA_EDIT,
			self::ACTION_MEASURE_EDIT => PermissionDictionary::CATALOG_MEASURE_MODIFY,
			self::ACTION_STORE_VIEW => PermissionDictionary::CATALOG_STORE_VIEW,
			self::ACTION_STORE_ANALYTIC_VIEW => PermissionDictionary::CATALOG_STORE_ANALYTIC_VIEW,
			self::ACTION_INVENTORY_MANAGEMENT_ACCESS => PermissionDictionary::CATALOG_INVENTORY_MANAGEMENT_ACCESS,
			self::ACTION_STORE_MODIFY => PermissionDictionary::CATALOG_STORE_MODIFY,
			self::ACTION_DEAL_PRODUCT_RESERVE => PermissionDictionary::CATALOG_RESERVE_DEAL,
			self::ACTION_STORE_PRODUCT_RESERVE => PermissionDictionary::CATALOG_STORE_RESERVE,
			self::ACTION_RESERVED_SETTINGS_ACCESS => PermissionDictionary::CATALOG_RESERVE_SETTINGS,
			self::ACTION_STORE_DOCUMENT_VIEW => PermissionDictionary::CATALOG_STORE_DOCUMENT,
			self::ACTION_STORE_DOCUMENT_MODIFY => PermissionDictionary::CATALOG_STORE_DOCUMENT,
			self::ACTION_STORE_DOCUMENT_CANCEL => PermissionDictionary::CATALOG_STORE_DOCUMENT,
			self::ACTION_STORE_DOCUMENT_CONDUCT => PermissionDictionary::CATALOG_STORE_DOCUMENT,
			self::ACTION_STORE_DOCUMENT_DELETE => PermissionDictionary::CATALOG_STORE_DOCUMENT,
			self::ACTION_CATALOG_SETTINGS_ACCESS => PermissionDictionary::CATALOG_SETTINGS_ACCESS,
			self::ACTION_CATALOG_RIGHTS_EDIT => PermissionDictionary::CATALOG_SETTINGS_EDIT_RIGHTS,
			self::ACTION_SELL_NEGATIVE_COMMODITIES_SETTINGS_EDIT => PermissionDictionary::CATALOG_SETTINGS_SELL_NEGATIVE_COMMODITIES,
			self::ACTION_PRODUCT_CARD_EDIT => PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_EDIT,
			self::ACTION_PRODUCT_CARD_SETTINGS_FOR_USERS_SET => PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_SET_PROFILE_FOR_USERS,
			self::ACTION_STORE_DOCUMENT_CARD_EDIT => PermissionDictionary::CATALOG_SETTINGS_STORE_DOCUMENT_CARD_EDIT,
			self::ACTION_PRODUCT_VIEW => PermissionDictionary::CATALOG_PRODUCT_VIEW,
			self::ACTION_PRODUCT_ADD => PermissionDictionary::CATALOG_PRODUCT_ADD,
			self::ACTION_PRODUCT_EDIT => PermissionDictionary::CATALOG_PRODUCT_EDIT,
			self::ACTION_PRODUCT_DELETE => PermissionDictionary::CATALOG_PRODUCT_DELETE,
			self::ACTION_PRICE_GROUP_EDIT => PermissionDictionary::CATALOG_PRICE_GROUP_MODIFY,
			self::ACTION_PRICE_EDIT => PermissionDictionary::CATALOG_PRODUCT_EDIT_CATALOG_PRICE,
			self::ACTION_PRICE_ENTITY_EDIT => PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE,
			self::ACTION_PRODUCT_DISCOUNT_SET => PermissionDictionary::CATALOG_PRODUCT_SET_DISCOUNT,
			self::ACTION_PRODUCT_PUBLIC_VISIBILITY_SET => PermissionDictionary::CATALOG_PRODUCT_PUBLIC_VISIBILITY,
		];
	}

	public static function getLegacyMap(): array
	{
		return [
			self::ACTION_CATALOG_VIEW => [
				self::ACTION_CATALOG_VIEW,
				self::ACTION_PRODUCT_VIEW,
			],
			self::ACTION_CATALOG_READ => [self::ACTION_CATALOG_READ],
			self::ACTION_MEASURE_EDIT => [self::ACTION_MEASURE_EDIT],
			self::ACTION_VAT_EDIT => [self::ACTION_VAT_EDIT],
			self::ACTION_CATALOG_IMPORT_EDIT => [self::ACTION_CATALOG_IMPORT_EDIT],
			self::ACTION_CATALOG_EXPORT_EDIT => [self::ACTION_CATALOG_EXPORT_EDIT],
			self::ACTION_CATALOG_EXPORT_EXECUTION => [self::ACTION_CATALOG_EXPORT_EXECUTION],
			self::ACTION_CATALOG_IMPORT_EXECUTION => [self::ACTION_CATALOG_IMPORT_EXECUTION],
			self::ACTION_STORE_VIEW => [
				self::ACTION_STORE_VIEW,
				self::ACTION_STORE_ANALYTIC_VIEW,
				self::ACTION_INVENTORY_MANAGEMENT_ACCESS,
				self::ACTION_STORE_MODIFY,
				self::ACTION_DEAL_PRODUCT_RESERVE,
				self::ACTION_STORE_PRODUCT_RESERVE,
				self::ACTION_STORE_DOCUMENT_VIEW,
				self::ACTION_STORE_DOCUMENT_MODIFY,
				self::ACTION_STORE_DOCUMENT_CANCEL,
				self::ACTION_STORE_DOCUMENT_CONDUCT,
				self::ACTION_STORE_DOCUMENT_DELETE,
				self::ACTION_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY,
				self::ACTION_STORE_DOCUMENT_CARD_EDIT,
			],
			self::ACTION_PRICE_GROUP_EDIT => [self::ACTION_PRICE_GROUP_EDIT],
			self::ACTION_PRICE_EDIT => [
				self::ACTION_PRICE_EDIT,
				// for legacy `catalog_price` is equals modify rights
				self::ACTION_PRODUCT_EDIT,
				self::ACTION_PRODUCT_ADD,
				self::ACTION_PRODUCT_DELETE,
				self::ACTION_PRODUCT_PUBLIC_VISIBILITY_SET,
			],
			self::ACTION_PRODUCT_DISCOUNT_SET => [self::ACTION_PRODUCT_DISCOUNT_SET],
			self::ACTION_PRODUCT_PURCHASE_INFO_VIEW => [self::ACTION_PRODUCT_PURCHASE_INFO_VIEW],
			self::ACTION_PRODUCT_PRICE_EXTRA_EDIT => [self::ACTION_PRODUCT_PRICE_EXTRA_EDIT],
			self::ACTION_CATALOG_SETTINGS_ACCESS => [
				self::ACTION_RESERVED_SETTINGS_ACCESS,
				self::ACTION_CATALOG_SETTINGS_ACCESS,
				self::ACTION_SELL_NEGATIVE_COMMODITIES_SETTINGS_EDIT,
				self::ACTION_PRODUCT_CARD_EDIT,
				self::ACTION_PRODUCT_CARD_SETTINGS_FOR_USERS_SET,
			],
		];
	}

	public static function getStoreDocumentActionPermissionMap(): array
	{
		return [
			self::ACTION_STORE_DOCUMENT_VIEW => PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
			self::ACTION_STORE_DOCUMENT_MODIFY => PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
			self::ACTION_STORE_DOCUMENT_CANCEL => PermissionDictionary::CATALOG_STORE_DOCUMENT_CANCEL,
			self::ACTION_STORE_DOCUMENT_CONDUCT => PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT,
			self::ACTION_STORE_DOCUMENT_DELETE => PermissionDictionary::CATALOG_STORE_DOCUMENT_DELETE,
		];
	}

	/**
	 * get action name by string value
	 * @param string $value string value of action
	 *
	 * @return string|null
	 * @throws \ReflectionException
	 */
	public static function getActionRuleName(string $value): ?string
	{
		$constants = self::getActionNames();
		if (!array_key_exists($value, $constants))
		{
			return null;
		}

		$storeDocumentActions = [
			self::ACTION_STORE_DOCUMENT_MODIFY,
			self::ACTION_STORE_DOCUMENT_CANCEL,
			self::ACTION_STORE_DOCUMENT_DELETE,
			self::ACTION_STORE_DOCUMENT_CONDUCT,
			self::ACTION_STORE_DOCUMENT_VIEW,
		];
		if (in_array($value, $storeDocumentActions, true))
		{
			$value = self::ACTION_STORE_DOCUMENT_PERFORM;
		}

		$storeActions = [
			self::ACTION_STORE_VIEW,
			self::ACTION_STORE_PRODUCT_RESERVE,
		];
		if (in_array($value, $storeActions, true))
		{
			$value = self::ACTION_STORE_VIEW;
		}

		if ($value === self::ACTION_PRODUCT_DISCOUNT_SET)
		{
			$value = self::ACTION_PRICE_ENTITY_EDIT;
		}

		return str_replace(self::PREFIX, '', $constants[$value]);
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	private static function getActionNames(): array
	{
		$class = new \ReflectionClass(__CLASS__);
		$constants = $class->getConstants();
		foreach ($constants as $name => $value)
		{
			if (mb_strpos($name, self::PREFIX) !== 0)
			{
				unset($constants[$name]);
			}
		}

		return array_flip($constants);
	}
}
