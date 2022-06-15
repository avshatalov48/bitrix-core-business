<?php
namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Catalog;
use Bitrix\Sale\Reservation\Configuration\ReservationSettings;
use Bitrix\Sale\Reservation\Configuration\ReservationSettingsService;
use Bitrix\Sale\Reservation\Configuration\ReserveCondition;

Loc::loadMessages(__FILE__);

/**
 * Class Configuration
 * @package Bitrix\Sale
 */
class Configuration
{
	private static bool $enableAutomaticReservation;

	const ALLOW_DELIVERY_ON_PAY = 'R';
	const ALLOW_DELIVERY_ON_FULL_PAY = 'P';
	const STATUS_ON_PAY = 'R';
	const STATUS_ON_FULL_PAY = 'P';

	/**
	 * Returns reservation condition list.
	 *
	 * @param bool $extendedMode			Format mode.
	 * @return array
	 */
	public static function getReservationConditionList($extendedMode = false)
	{
		$extendedMode = ($extendedMode === true);
		if ($extendedMode)
		{
			return array(
				ReserveCondition::ON_CREATE => Loc::getMessage('SALE_CONFIGURATION_RESERVE_ON_CREATE'),
				ReserveCondition::ON_FULL_PAY => Loc::getMessage('SALE_CONFIGURATION_RESERVE_ON_FULL_PAY'),
				ReserveCondition::ON_PAY => Loc::getMessage('SALE_CONFIGURATION_RESERVE_ON_PAY'),
				ReserveCondition::ON_ALLOW_DELIVERY => Loc::getMessage('SALE_CONFIGURATION_RESERVE_ON_ALLOW_DELIVERY'),
				ReserveCondition::ON_SHIP => Loc::getMessage('SALE_CONFIGURATION_RESERVE_ON_SHIP')
			);
		}
		return array(
			ReserveCondition::ON_CREATE,
			ReserveCondition::ON_FULL_PAY,
			ReserveCondition::ON_PAY,
			ReserveCondition::ON_ALLOW_DELIVERY,
			ReserveCondition::ON_SHIP
		);
	}

	/**
	 * Reservation settings.
	 *
	 * @return ReservationSettings
	 */
	private static function getReservationSettings(): ReservationSettings
	{
		static $settings;

		if (!isset($settings))
		{
			$settings = ReservationSettingsService::getInstance()->get();
		}

		return $settings;
	}

	/**
	 * Returns current reservation condition.
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getProductReservationCondition()
	{
		return self::getReservationSettings()->getReserveCondition();
	}

	/**
	 * @return bool
	 */
	public static function isEnableAutomaticReservation() : bool
	{
		if (!isset(self::$enableAutomaticReservation))
		{
			self::$enableAutomaticReservation = self::getReservationSettings()->isEnableAutomaticReservation();
		}
		return self::$enableAutomaticReservation;
	}

	public static function enableAutomaticReservation()
	{
		self::$enableAutomaticReservation = true;
	}

	public static function disableAutomaticReservation()
	{
		self::$enableAutomaticReservation = false;
	}

	/**
	 * Returns current clear reserve period.
	 *
	 * @return int
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getProductReserveClearPeriod()
	{
		return self::getReservationSettings()->getClearPeriod();
	}

	/**
	 * Check is current reservation with shipment.
	 *
	 * @return bool
	 */
	public static function isReservationDependsOnShipment()
	{
		$condition = static::getProductReservationCondition();
		return in_array($condition, array(ReserveCondition::ON_SHIP, ReserveCondition::ON_ALLOW_DELIVERY));
	}

	/**
	 * Returns true if can use 1C services.
	 *
	 * @return bool
	 */
	public static function isCanUse1c(): bool
	{
		$lang = LANGUAGE_ID;
		if (Loader::includeModule('bitrix24'))
		{
			$lang = \CBitrix24::getLicensePrefix();
		}
		elseif (Loader::includeModule('intranet'))
		{
			$lang = \CIntranetUtils::getPortalZone();
		}

		return in_array($lang, ['ru', 'ua', 'by', 'kz'], true);
	}

	/**
	 * Returns true if import of orders from Bitrix24 is available.
	 *
	 * @return bool
	 */
	public static function isAvailableOrdersImportFromB24(): bool
	{
		$lang = LANGUAGE_ID;
		if (Loader::includeModule('bitrix24'))
		{
			$lang = \CBitrix24::getLicensePrefix();
		}
		elseif (Loader::includeModule('intranet'))
		{
			$lang = \CIntranetUtils::getPortalZone();
		}

		return in_array($lang, ['ru', 'ua', 'by', 'kz'], true);
	}

	/**
	 * Returns true if can use big data personalization.
	 *
	 * @return bool
	 */
	public static function isCanUsePersonalization(): bool
	{
		$lang = LANGUAGE_ID;
		if (Loader::includeModule('bitrix24'))
		{
			$lang = \CBitrix24::getLicensePrefix();
		}
		elseif (Loader::includeModule('intranet'))
		{
			$lang = \CIntranetUtils::getPortalZone();
		}

		return in_array($lang, ['ru', 'ua', 'by', 'kz'], true);
	}

	/**
	 * Returns true, if current condition - delivery.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function needShipOnAllowDelivery()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		return ((string)$optionClassName::get('sale', 'allow_deduction_on_delivery') === 'Y');
	}

	/**
	 * Returns flag allow delivery on pay.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function needAllowDeliveryOnPay()
	{
		$condition = static::getAllowDeliveryOnPayCondition();
		return in_array($condition, array(static::ALLOW_DELIVERY_ON_PAY, ReserveCondition::ON_ALLOW_DELIVERY));
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getAllowDeliveryOnPayCondition()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		return $optionClassName::get('sale', 'status_on_change_allow_delivery_after_paid');
	}

	/**
	 * @param bool $extendedMode
	 *
	 * @return array
	 */
	public static function getAllowDeliveryAfterPaidConditionList($extendedMode = false)
	{
		if ($extendedMode)
		{
			return array(
				self::ALLOW_DELIVERY_ON_PAY => Loc::getMessage('SALE_CONFIGURATION_ON_PAY'),
				self::ALLOW_DELIVERY_ON_FULL_PAY => Loc::getMessage('SALE_CONFIGURATION_ON_FULL_PAY'),
			);
		}
		return array(
			self::ALLOW_DELIVERY_ON_PAY,
			self::ALLOW_DELIVERY_ON_FULL_PAY,
		);
	}

	/**
	 * @return mixed
	 */
	public static function getStatusPaidCondition()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		return $optionClassName::get('sale', 'status_on_paid_condition');
	}

	/**
	 * @return mixed
	 */
	public static function getStatusAllowDeliveryCondition()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		return $optionClassName::get('sale', 'status_on_paid_condition');
	}

	/**
	 * Returns flag enable use stores.
	 *
	 * @return bool
	 */
	public static function useStoreControl()
	{
		if (!Loader::includeModule('catalog'))
			return false;

		return Catalog\Config\State::isUsedInventoryManagement();
	}

	/**
	 * @return int|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getDefaultStoreId()
	{
		if (
			!Loader::includeModule('catalog')
			|| !self::useStoreControl()
		)
		{
			return 0;
		}

		return (int)Catalog\StoreTable::getDefaultStoreId();
	}

	/**
	 * Returns flag use reservations.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function isEnabledReservation()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		return ((string)$optionClassName::get('catalog', 'enable_reservation') === 'Y');
	}

	/**
	 * Tells if allowed to calculate discount on basket separately.
	 * @return bool
	 */
	public static function isAllowedSeparatelyDiscountCalculation()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		return $optionClassName::get('sale', 'discount_separately_calculation') === 'Y';
	}
}
