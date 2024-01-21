<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Session\Session;
use Bitrix\Sale;

class DiscountCouponsManagerBase
{
	public const MODE_CLIENT = 0x0001;
	public const MODE_MANAGER = 0x0002;
	public const MODE_ORDER = 0x0004;
	public const MODE_SYSTEM = 0x0008;
	public const MODE_EXTERNAL = 0x0010;

	public const STATUS_NOT_FOUND = 0x0001;
	public const STATUS_ENTERED = 0x0002;
	public const STATUS_APPLYED = 0x0004;
	public const STATUS_NOT_APPLYED = 0x0008;
	public const STATUS_FREEZE = 0x0010;

	public const COUPON_CHECK_OK = 0x0000;
	public const COUPON_CHECK_NOT_FOUND = 0x0001;
	public const COUPON_CHECK_NO_ACTIVE = 0x0002;
	public const COUPON_CHECK_RANGE_ACTIVE_FROM = 0x0004;
	public const COUPON_CHECK_RANGE_ACTIVE_TO = 0x0008;
	public const COUPON_CHECK_NO_ACTIVE_DISCOUNT = 0x0010;
	public const COUPON_CHECK_RANGE_ACTIVE_FROM_DISCOUNT = 0x0020;
	public const COUPON_CHECK_RANGE_ACTIVE_TO_DISCOUNT = 0x0040;
	public const COUPON_CHECK_BAD_USER_ID = 0x0080;
	public const COUPON_CHECK_ALREADY_MAX_USED = 0x0100;
	public const COUPON_CHECK_UNKNOWN_TYPE = 0x0200;
	public const COUPON_CHECK_CORRUPT_DATA = 0x0400;
	public const COUPON_CHECK_NOT_APPLIED = 0x0800;

	public const COUPON_MODE_SIMPLE = 0x0001;
	public const COUPON_MODE_FULL = 0x0002;

	public const EVENT_ON_BUILD_COUPON_PROVIDES = 'onBuildCouponProviders';
	public const EVENT_ON_SAVE_APPLIED_COUPONS = 'onManagerSaveApplied';
	public const EVENT_ON_COUPON_ADD = 'onManagerCouponAdd';
	public const EVENT_ON_COUPON_DELETE = 'onManagerCouponDelete';
	public const EVENT_ON_COUPON_APPLY_PRODUCT = 'onManagerCouponApplyByProduct';
	public const EVENT_ON_COUPON_APPLY = 'onManagerCouponApply';

	public const STORAGE_MANAGER_COUPONS = 'CATALOG_MANAGE_COUPONS';
	public const STORAGE_CLIENT_COUPONS = 'CATALOG_USER_COUPONS';

	protected static array $coupons = [];
	protected static bool $init = false;
	protected static int $useMode = self::MODE_CLIENT;
	protected static array $errors = [];
	protected static ?bool $onlySaleDiscount = null;
	protected static ?int $userId = null;
	protected static array $couponProviders = [];
	protected static array $couponTypes = [];
	protected static int $couponIndex = 0;
	protected static ?int $orderId = null;
	protected static bool $allowedSave = false;
	protected static bool $checkActivity = true;
	protected static bool $useOrderCoupons = true;

	protected static array $clearFields = [
		'STATUS',
		'CHECK_CODE',
		'DISCOUNT_NAME',
		'DISCOUNT_ACTIVE',
		'SAVED',
		'BASKET',
		'DELIVERY',
	];
	protected static array $timeFields = [
		'DISCOUNT_ACTIVE_FROM',
		'DISCOUNT_ACTIVE_TO',
		'ACTIVE_FROM',
		'ACTIVE_TO',
	];

	protected static int $allowCouponStorage = 0;

	protected static array $lockedCoupons = [];

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * Init use mode and user id.
	 *
	 * @param int $mode			Discount manager mode.
	 * @param array $params		Initial params (userId, orderId, oldUserId)
	 * 		keys are case-sensitive:
	 * 			<ul>
	 * 			<li>int userId		Order owner (for MODE_MANAGER or MODE_ORDER only)
	 * 			<li>int orderId		Edit order id (for MODE_ORDER only(!))
	 * 			<li>int oldUserId	Old order owner (for MODE_MANAGER or MODE_ORDER only)
	 * 			</ul>.
	 * @return void
	 */
	public static function initUseMode($mode = self::MODE_CLIENT, $params = [])
	{
		$mode = (int)$mode;
		if (!is_array($params))
		{
			$params = [];
		}
		self::$checkActivity = true;
		self::$userId = null;
		self::$orderId = null;
		self::$allowedSave = false;
		self::$useOrderCoupons = true;

		self::$useMode = self::MODE_SYSTEM;
		switch ($mode)
		{
			case self::MODE_MANAGER:
				if (!isset($params['userId']) || (int)$params['userId'] < 0)
				{
					self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_BAD_USER_ID');
				}
				if (isset($params['orderId']))
				{
					self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_ORDER_ID_EXIST');
				}
				if (empty(self::$errors))
				{
					self::$userId = (int)$params['userId'];
					self::$orderId = null;
					self::$allowedSave = true;
					self::$useMode = self::MODE_MANAGER;
					if (isset($params['oldUserId']))
					{
						self::migrateStorage($params['oldUserId']);
					}
				}
				break;
			case self::MODE_ORDER:
				if (!isset($params['userId']) || (int)$params['userId'] < 0)
				{
					self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_BAD_USER_ID');
				}
				if (!isset($params['orderId']) || (int)$params['orderId'] <= 0)
				{
					self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_ORDER_ID_ABSENT');
				}
				if (empty(self::$errors))
				{
					self::$userId = (int)$params['userId'];
					self::$orderId = (int)$params['orderId'];
					self::$allowedSave = true;
					self::$useMode = self::MODE_ORDER;
					if (isset($params['oldUserId']))
					{
						self::migrateStorage($params['oldUserId']);
					}

				}
				break;
			case self::MODE_CLIENT:
				self::$useMode = self::MODE_CLIENT;
				if (isset($params['userId']) && (int)$params['userId'] >= 0)
				{
					self::$userId = (int)$params['userId'];
				}
				else
				{
					self::initUserId();
				}
				if (self::isSuccess())
				{
					self::$allowedSave = true;
				}
				break;
			case self::MODE_EXTERNAL:
				self::$useMode = self::MODE_EXTERNAL;
				self::$userId = (
					isset($params['userId']) && (int)$params['userId'] >= 0
						? (int)$params['userId']
						: \CSaleUser::GetAnonymousUserID()
				);
				break;
			case self::MODE_SYSTEM:
				break;
			default:
				self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_BAD_MODE');
				break;
		}
	}

	/**
	 * Returns use mode.
	 *
	 * @return int
	 */
	public static function getUseMode()
	{
		return self::$useMode;
	}

	/**
	 * Verifies that the client mode being used.
	 *
	 * @return bool
	 */
	public static function usedByClient()
	{
		return (self::$useMode === self::MODE_CLIENT);
	}

	/**
	 * Verifies that the manager mode being used.
	 *
	 * @return bool
	 */
	public static function usedByManager()
	{
		return (self::$useMode === self::MODE_MANAGER || self::$useMode === self::MODE_ORDER);
	}

	/**
	 * Verifies that the external order mode being used.
	 *
	 * @return bool
	 */
	public static function usedByExternal()
	{
		return (self::$useMode === self::MODE_EXTERNAL);
	}

	/**
	 * Returns user id.
	 *
	 * @return null|int
	 */
	public static function getUserId()
	{
		if ((self::$userId === null || self::$userId === 0) && self::usedByClient())
		{
			self::$userId = null;
			self::initUserId();
		}

		return self::$userId;
	}

	/**
	 * Returns order id, if current use mode self::MODE_ORDER.
	 *
	 * @return null|int
	 */
	public static function getOrderId()
	{
		return self::$orderId;
	}

	/**
	 * Session object.
	 *
	 * If session is not accessible, returns null and add error.
	 *
	 * @return Session|null
	 */
	protected static function getSession(): ?Session
	{
		/** @var Session $session */
		$session = Application::getInstance()->getSession();
		if (!$session->isAccessible())
		{
			self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_SESSION_NOT_ACCESSIBLE');

			return null;
		}

		return $session;
	}

	/**
	 * Returns a sign of success.
	 *
	 * @return bool
	 */
	public static function isSuccess()
	{
		return empty(self::$errors);
	}

	/**
	 * Returns error list.
	 *
	 * @return array
	 */
	public static function getErrors()
	{
		return self::$errors;
	}

	/**
	 * Clear errors list.
	 *
	 * @return void
	 */
	public static function clearErrors()
	{
		self::$errors = [];
	}

	/**
	 * Returns coupon status list.
	 *
	 * @param bool $extendedMode	Get status Ids or Ids with description.
	 * @return array
	 */
	public static function getStatusList($extendedMode = false)
	{
		$extendedMode = ($extendedMode === true);
		if ($extendedMode)
		{
			return [
				self::STATUS_NOT_FOUND => Loc::getMessage('BX_SALE_DCM_STATUS_NOT_FOUND'),
				self::STATUS_ENTERED => Loc::getMessage('BX_SALE_DCM_STATUS_ENTERED'),
				self::STATUS_NOT_APPLYED => Loc::getMessage('BX_SALE_DCM_STATUS_NOT_APPLYED'),
				self::STATUS_APPLYED => Loc::getMessage('BX_SALE_DCM_STATUS_APPLYED'),
				self::STATUS_FREEZE => Loc::getMessage('BX_SALE_DCM_STATUS_FREEZE'),
			];
		}

		return [
			self::STATUS_NOT_FOUND,
			self::STATUS_ENTERED,
			self::STATUS_NOT_APPLYED,
			self::STATUS_APPLYED,
			self::STATUS_FREEZE,
		];
	}

	/**
	 * Returns check code list.
	 *
	 * @param bool $extendedMode	Get codes or codes with description.
	 * @return array
	 */
	public static function getCheckCodeList($extendedMode = false)
	{
		$extendedMode = ($extendedMode === true);
		if ($extendedMode)
		{
			return [
				self::COUPON_CHECK_OK => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_OK'),
				self::COUPON_CHECK_NOT_FOUND => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_NOT_FOUND'),
				self::COUPON_CHECK_NO_ACTIVE => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_NO_ACTIVE'),
				self::COUPON_CHECK_RANGE_ACTIVE_FROM => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_RANGE_ACTIVE_FROM'),
				self::COUPON_CHECK_RANGE_ACTIVE_TO => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_RANGE_ACTIVE_TO'),
				self::COUPON_CHECK_NO_ACTIVE_DISCOUNT => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_NO_ACTIVE_DISCOUNT'),
				self::COUPON_CHECK_RANGE_ACTIVE_FROM_DISCOUNT => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_RANGE_ACTIVE_FROM_DISCOUNT'),
				self::COUPON_CHECK_RANGE_ACTIVE_TO_DISCOUNT => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_RANGE_ACTIVE_TO_DISCOUNT'),
				self::COUPON_CHECK_BAD_USER_ID => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_BAD_USER_ID'),
				self::COUPON_CHECK_ALREADY_MAX_USED => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_ALREADY_MAX_USED'),
				self::COUPON_CHECK_UNKNOWN_TYPE => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_UNKNOWN_TYPE'),
				self::COUPON_CHECK_CORRUPT_DATA => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_CORRUPT_DATA'),
				self::COUPON_CHECK_NOT_APPLIED => Loc::getMessage('BX_SALE_DCM_COUPON_CHECK_NOT_APPLIED'),
			];
		}

		return [
			self::COUPON_CHECK_OK,
			self::COUPON_CHECK_NOT_FOUND,
			self::COUPON_CHECK_NO_ACTIVE,
			self::COUPON_CHECK_RANGE_ACTIVE_FROM,
			self::COUPON_CHECK_RANGE_ACTIVE_TO,
			self::COUPON_CHECK_NO_ACTIVE_DISCOUNT,
			self::COUPON_CHECK_RANGE_ACTIVE_FROM_DISCOUNT,
			self::COUPON_CHECK_RANGE_ACTIVE_TO_DISCOUNT,
			self::COUPON_CHECK_BAD_USER_ID,
			self::COUPON_CHECK_ALREADY_MAX_USED,
			self::COUPON_CHECK_UNKNOWN_TYPE,
			self::COUPON_CHECK_CORRUPT_DATA,
			self::COUPON_CHECK_NOT_APPLIED,
		];
	}

	/**
	 * Returns description of check code.
	 * @param int $code Code value.
	 *
	 * @return string|null
	 */
	public static function getCheckCodeMessage($code)
	{
		$codes = self::getCheckCodeList(true);
		if (isset($codes[$code]))
		{
			return $codes[$code];
		}

		return null;
	}

	/**
	 * Set use ordered coupons for apply.
	 *
	 * @param bool $state		Use state.
	 * @return void
	 */
	public static function useSavedCouponsForApply($state)
	{
		if ($state !== true && $state !== false)
		{
			return;
		}
		self::$useOrderCoupons = $state;
	}

	/**
	 * Returns use ordered coupons for apply.
	 *
	 * @return bool
	 */
	public static function isUsedOrderCouponsForApply()
	{
		return self::$useOrderCoupons;
	}

	/**
	 * Enable get coupons for calculate discounts.
	 *
	 * @return void
	 */
	public static function unFreezeCouponStorage()
	{
		self::$allowCouponStorage++;
	}

	/**
	 * Disable get coupons for calculate discounts.
	 *
	 * @return void
	 */
	public static function freezeCouponStorage()
	{
		self::$allowCouponStorage--;
	}

	/**
	 * Returns true, if disallow get coupons for calculate discounts.
	 *
	 * @return bool
	 */
	public static function isFrozenCouponStorage()
	{
		return (self::$allowCouponStorage < 0);
	}

	/**
	 * Initialization coupon manager.
	 *
	 * @param int $mode				Discount manager mode.
	 * @param array $params			Initial params (userId, orderId, oldUserId)
	 * 		keys are case-sensitive:
	 * 			<ul>
	 * 			<li>int userId		Order owner (for MODE_MANAGER or MODE_ORDER only)
	 * 			<li>int orderId		Edit order id (for MODE_ORDER only(!))
	 * 			<li>int oldUserId	Old order owner (for MODE_MANAGER or MODE_ORDER only)
	 * 			</ul>.
	 * @param bool $clearStorage	Clear coupon session storage.
	 * @return void
	 */
	public static function init($mode = self::MODE_CLIENT, $params = [], $clearStorage = false)
	{
		if (self::$init)
		{
			return;
		}
		self::$onlySaleDiscount = null;
		self::$couponTypes = Internals\DiscountCouponTable::getCouponTypes(true);
		self::$couponIndex = 0;
		self::$lockedCoupons = [];
		self::clearErrors();
		self::initUseMode($mode, $params);
		self::initUseDiscount();
		if (!self::isSuccess())
		{
			return;
		}
		if (self::$useMode !== self::MODE_SYSTEM)
		{
			$session = self::getSession();
			if (!$session)
			{
				return;
			}

			self::clear($clearStorage);
			$couponsList = [];
			switch (self::$useMode)
			{
				case self::MODE_CLIENT:
				case self::MODE_EXTERNAL:
					if (
						!empty($session[self::STORAGE_CLIENT_COUPONS])
						&& is_array($session[self::STORAGE_CLIENT_COUPONS])
					)
					{
						$couponsList = $session[self::STORAGE_CLIENT_COUPONS];
					}
					break;
				case self::MODE_MANAGER:
					if (
						!empty($session[self::STORAGE_MANAGER_COUPONS])
						&& !empty($session[self::STORAGE_MANAGER_COUPONS][self::$userId])
						&& is_array($session[self::STORAGE_MANAGER_COUPONS][self::$userId])
					)
					{
						$couponsList = $session[self::STORAGE_MANAGER_COUPONS][self::$userId];
					}
					break;
				case self::MODE_ORDER:
					self::load();
					if (
						!empty($session[self::STORAGE_MANAGER_COUPONS])
						&& !empty($session[self::STORAGE_MANAGER_COUPONS][self::$userId])
						&& is_array($session[self::STORAGE_MANAGER_COUPONS][self::$userId])
					)
					{
						$couponsList = $session[self::STORAGE_MANAGER_COUPONS][self::$userId];
					}
					break;
			}
			if (!empty($couponsList))
			{
				self::setCoupons($couponsList);
			}
			unset($couponsList);
			if (self::$useMode === self::MODE_ORDER)
			{
				self::saveToStorage();
			}
		}
		self::$init = true;
	}

	/**
	 * Unconditional reinitialization coupon manager.
	 *
	 * @param int $mode				Discount manager mode.
	 * @param array $params			Initial params (userId, orderId, oldUserId)
	 * 		keys are case-sensitive:
	 * 			<ul>
	 * 			<li>int userId		Order owner (for MODE_MANAGER or MODE_ORDER only)
	 * 			<li>int orderId		Edit order id (for MODE_ORDER only(!))
	 * 			<li>int oldUserId	Old order owner (for MODE_MANAGER or MODE_ORDER only)
	 * 			</ul>.
	 * @param bool $clearStorage	Clear coupon session storage.
	 * @return void
	 */
	public static function reInit($mode = self::MODE_CLIENT, $params = [], $clearStorage = false)
	{
		if (self::isFrozenCouponStorage())
		{
			return;
		}
		self::$init = false;
		self::init($mode, $params, $clearStorage);
	}

	/**
	 * Returns true, if coupons were are entered.
	 *
	 * @return bool
	 */
	public static function isEntered()
	{
		return !empty(self::$coupons);
	}

	/**
	 * Add coupon in manager.
	 *
	 * @param string $coupon	Added coupon.
	 * @return bool
	 */
	public static function add($coupon)
	{
		if (!self::$init)
		{
			self::init();
		}
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess())
		{
			return false;
		}

		$coupon = trim((string)$coupon);
		if ($coupon === '')
		{
			return false;
		}
		if (!isset(self::$coupons[$coupon]))
		{
			$couponData = self::getData($coupon);
			if (!isset(self::$coupons[$couponData['COUPON']]))
			{
				$couponData['SORT'] = self::$couponIndex;
				self::createApplyFields($couponData);
				self::$coupons[$couponData['COUPON']] = $couponData;
				self::$couponIndex++;
				self::saveToStorage();
				$event = new Main\Event('sale', self::EVENT_ON_COUPON_ADD, $couponData);
				$event->send();
			}
			if (self::$coupons[$couponData['COUPON']]['MODE'] === self::COUPON_MODE_FULL)
			{
				return (self::$coupons[$couponData['COUPON']]['STATUS'] != self::STATUS_NOT_FOUND);
			}
			else
			{
				return (
					self::$coupons[$couponData['COUPON']]['STATUS'] != self::STATUS_NOT_FOUND
					&& self::$coupons[$couponData['COUPON']]['STATUS'] != self::STATUS_FREEZE
				);
			}
		}
		else
		{
			if (self::$coupons[$coupon]['MODE'] === self::COUPON_MODE_FULL)
			{
				return (self::$coupons[$coupon]['STATUS'] != self::STATUS_NOT_FOUND);
			}
			else
			{
				return (
					self::$coupons[$coupon]['STATUS'] != self::STATUS_NOT_FOUND
					&& self::$coupons[$coupon]['STATUS'] != self::STATUS_FREEZE
				);
			}
		}
	}

	/**
	 * Delete coupon from manager.
	 *
	 * @param string $coupon	Deleted coupon.
	 * @return bool
	 */
	public static function delete($coupon)
	{
		if (!self::$init)
		{
			self::init();
		}
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess())
		{
			return false;
		}

		$coupon = trim((string)$coupon);
		if ($coupon === '')
		{
			return false;
		}
		$founded = false;
		if (isset(self::$coupons[$coupon]))
		{
			$couponData = self::$coupons[$coupon];
			unset(self::$coupons[$coupon]);
			$founded = true;
		}
		else
		{
			$couponData = self::getData($coupon, false);
			if (isset(self::$coupons[$couponData['COUPON']]))
			{
				unset(self::$coupons[$couponData['COUPON']]);
				$founded = true;
			}
		}
		if ($founded)
		{
			self::saveToStorage();
			$event = new Main\Event('sale', self::EVENT_ON_COUPON_DELETE, $couponData);
			$event->send();

			return true;
		}

		return false;
	}

	/**
	 * Clear coupon storage.
	 *
	 * @param bool $clearStorage		Clear coupon session storage.
	 * @return bool
	 */
	public static function clear($clearStorage = false)
	{
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess())
		{
			return false;
		}

		$clearStorage = ($clearStorage === true);
		self::$coupons = [];
		if ($clearStorage)
		{
			self::saveToStorage();
		}

		return true;
	}

	/**
	 * Clear coupon storage for order.
	 *
	 * @param int $order			Order id.
	 * @return bool
	 */
	public static function clearByOrder($order)
	{
		if (!self::isSuccess())
		{
			return false;
		}
		$order = (int)$order;
		if ($order <= 0)
		{
			return false;
		}
		$userId = 0;

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderBase $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$orderIterator = $orderClassName::getList([
			'select' => ['ID', 'USER_ID'],
			'filter' => ['=ID' => $order],
		]);
		if ($orderData = $orderIterator->fetch())
		{
			$userId = (int)$orderData['USER_ID'];
		}
		unset($orderData, $orderIterator);
		if ($userId <= 0)
		{
			return false;
		}
		self::initUseMode(
			self::MODE_ORDER,
			[
				'userId' => $userId,
				'orderId' => $order,
			]
		);
		if (!self::isSuccess())
		{
			return false;
		}
		self::$coupons = [];
		self::saveToStorage();

		return true;
	}

	/**
	 * Change coupons owner in manager or order mode.
	 *
	 * @param int $oldUser				Old user id.
	 * @return void
	 */
	public static function migrateStorage($oldUser)
	{
		if (self::$useMode !== self::MODE_MANAGER && self::$useMode !== self::MODE_ORDER || self::$userId === null)
		{
			return;
		}

		$oldUser = (int)$oldUser;
		if ($oldUser < 0)
		{
			return;
		}

		$session = self::getSession();
		if (!$session)
		{
			return;
		}

		if (empty($session[self::STORAGE_MANAGER_COUPONS]))
		{
			$session[self::STORAGE_MANAGER_COUPONS] = [];
		}

		if (
			empty($session[self::STORAGE_MANAGER_COUPONS][self::$userId])
			|| !is_array($session[self::STORAGE_MANAGER_COUPONS][self::$userId])
		)
		{
			$session[self::STORAGE_MANAGER_COUPONS][self::$userId] = [];
		}

		if (!empty($session[self::STORAGE_MANAGER_COUPONS][$oldUser]))
		{
			if (is_array($session[self::STORAGE_MANAGER_COUPONS][$oldUser]))
			{
				$session[self::STORAGE_MANAGER_COUPONS][self::$userId] = $session[self::STORAGE_MANAGER_COUPONS][$oldUser];
			}
			unset($session[self::STORAGE_MANAGER_COUPONS][$oldUser]);
		}
	}

	/**
	 * Load coupons for existing order.
	 *
	 * @return void
	 */
	public static function load()
	{
		if (self::$useMode !== self::MODE_ORDER)
		{
			return;
		}

		self::$checkActivity = false;
		$couponsList = [];

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderDiscountBase $storageClassName */
		$storageClassName = $registry->getOrderDiscountClassName();

		$couponIterator = $storageClassName::getOrderCouponIterator([
			'select' => [
				'*',
				'MODULE_ID' => 'ORDER_DISCOUNT.MODULE_ID',
				'DISCOUNT_ID' => 'ORDER_DISCOUNT.DISCOUNT_ID',
				'DISCOUNT_NAME' => 'ORDER_DISCOUNT.NAME',
			],
			'filter' => ['=ORDER_ID' => self::$orderId],
			'order' => ['ID' => 'ASC'],
		]);
		while ($coupon = $couponIterator->fetch())
		{
			$couponData = $coupon['DATA'];
			$couponData['COUPON'] = $coupon['COUPON'];
			$couponData['STATUS'] = self::STATUS_ENTERED;
			$couponData['CHECK_CODE'] = self::COUPON_CHECK_OK;
			$couponData['MODULE'] = $coupon['MODULE_ID'];
			$couponData['MODULE_ID'] = $coupon['MODULE_ID'];
			$couponData['ID'] = $coupon['COUPON_ID'];
			$couponData['DISCOUNT_ID'] = $coupon['DISCOUNT_ID'];
			$couponData['DISCOUNT_NAME'] = (string)$coupon['DISCOUNT_NAME'];
			$couponData['DISCOUNT_ACTIVE'] = 'Y';
			$couponData['TYPE'] = $coupon['TYPE'];
			$couponData['ACTIVE'] = 'Y';
			$couponData['SAVED'] = 'Y';
			foreach (self::$timeFields as $fieldName)
			{
				if (isset($couponData[$fieldName]))
				{
					$couponData[$fieldName] = Main\Type\DateTime::createFromTimestamp($couponData[$fieldName]);
				}
			}
			unset($fieldName);
			if (empty($couponData['USER_INFO']) && $couponData['MODE'] === self::COUPON_MODE_FULL)
			{
				$couponData['USER_INFO'] = [
					'USER_ID' => 0,
					'MAX_USE' => 0,
					'USE_COUNT' => 0,
					'ACTIVE_FROM' => null,
					'ACTIVE_TO' => null,
				];
			}
			if (!empty($couponData['USER_INFO']))
			{
				foreach (self::$timeFields as $fieldName)
				{
					if (isset($couponData['USER_INFO'][$fieldName]))
					{
						$couponData['USER_INFO'][$fieldName] = Main\Type\DateTime::createFromTimestamp($couponData['USER_INFO'][$fieldName]);
					}
				}
				unset($fieldName);
				foreach ($couponData['USER_INFO'] as $fieldName => $fieldValue)
				{
					$couponData[$fieldName] = $fieldValue;
				}
			}
			$couponsList[$couponData['COUPON']] = $couponData;
		}
		unset($coupon, $couponIterator);

		if (!empty($couponsList))
		{
			self::setCoupons($couponsList, false);
		}

		self::$checkActivity = true;
	}

	/**
	 * Get coupons list.
	 *
	 * @param bool $extMode			Get full information or coupons only.
	 * @param array $filter			Coupons filter.
	 * @param bool $show			Get for show or apply.
	 * @param bool $final			Change status ENTERED to NOT_APPLIED.
	 * @return array|bool
	 */
	public static function get($extMode = true, $filter = [], $show = false, $final = false)
	{
		if (self::$useMode === self::MODE_SYSTEM)
		{
			return false;
		}
		$extMode = ($extMode === true);
		if (!is_array($filter))
		{
			$filter = [];
		}
		static::convertOldFilterFields($filter);
		$show = ($show === true);
		if (!self::$init)
		{
			self::init();
		}
		if (!self::isSuccess())
		{
			return false;
		}

		if (self::isFrozenCouponStorage() || !self::isEntered())
		{
			return [];
		}

		$final = ($final === true);
		if ($final)
		{
			self::finalApply();
		}
		$validCoupons = (
			$show
				? self::$coupons
				: array_filter(self::$coupons, '\Bitrix\Sale\DiscountCouponsManager::filterFreezeCoupons')
		);
		if (empty($validCoupons))
		{
			return [];
		}
		if (!empty($filter))
		{
			self::filterArrayCoupons($validCoupons, $filter);
		}
		if (!empty($validCoupons))
		{
			self::clearSystemData($validCoupons);
		}
		if ($show && !empty($validCoupons))
		{
			self::fillCouponHints($validCoupons);
		}

		return ($extMode ? $validCoupons : array_keys($validCoupons));
	}

	/**
	 * Get coupons list for apply.
	 *
	 * @param array $filter					Coupons filter.
	 * @param array $product				Product description.
	 * @param bool $uniqueDiscount			Get one coupon for discount.
	 * @return array|bool
	 */
	public static function getForApply($filter, $product = [], $uniqueDiscount = false)
	{
		if (self::$useMode === self::MODE_SYSTEM)
		{
			return [];
		}
		if (self::$useMode === self::MODE_ORDER && static::isUsedOrderCouponsForApply())
		{
			$filter['SAVED'] = ['Y', 'N'];
		}
		else
		{
			$filter['SAVED'] = 'N';
		}

		$couponsList = self::get(true, $filter, false);
		if ($couponsList === false)
		{
			return [];
		}
		if (!empty($couponsList))
		{
			$uniqueDiscount = ($uniqueDiscount === true);
			if ($uniqueDiscount)
			{
				self::filterUniqueDiscount($couponsList);
			}
			if (!empty($product))
			{
				$hash = self::getProductHash($product);
				if ($hash !== '')
				{
					$productCoupons = [];
					foreach ($couponsList as $id => $data)
					{
						if (self::filterOneRowCoupons($data, $hash))
						{
							$productCoupons[$id] = $data;
						}
					}
					$couponsList = $productCoupons;
					unset($productCoupons);
				}
				else
				{
					$couponsList = [];
				}
			}
		}

		static::filterLockedCoupons($couponsList);

		return $couponsList;
	}

	/**
	 * Returns coupons for current order.
	 *
	 * @param bool $extMode					Get full information or coupons only.
	 * @param array $filter					Coupons filter.
	 * @return array
	 */
	public static function getOrderedCoupons($extMode = true, $filter = [])
	{
		$extMode = ($extMode === true);
		$result = [];
		if (self::$useMode !== self::MODE_ORDER)
		{
			return $result;
		}
		if (!self::isSuccess())
		{
			return $result;
		}

		if (!self::isEntered())
		{
			return $result;
		}

		$result = array_filter(self::$coupons, '\Bitrix\Sale\DiscountCouponsManager::filterFreezeCoupons');
		if (empty($result))
		{
			return $result;
		}
		$result = array_filter($result, '\Bitrix\Sale\DiscountCouponsManager::filterFreezeOrderedCoupons');
		if (empty($result))
		{
			return $result;
		}

		$filter['SAVED'] = 'Y';
		static::filterArrayCoupons($result, $filter);
		if (!empty($result))
		{
			static::clearSystemData($result);
		}

		return ($extMode ? $result : array_keys($result));
	}

	/**
	 * Verifies the current status of new applied coupons. Used before order save.
	 *
	 * @return Result
	 */
	public static function verifyApplied()
	{
		$result = new Sale\Result();

		if (
			self::$useMode === self::MODE_SYSTEM
			|| !self::isEntered()
		)
		{
			return $result;
		}

		$appliedCoupons = self::filterCoupons(['STATUS' => self::STATUS_APPLYED, 'SAVED' => 'N'], true);
		if (!empty($appliedCoupons))
		{
			$badCoupons = [];
			$appliedCoupons = array_keys($appliedCoupons);
			foreach ($appliedCoupons as $coupon)
			{
				$row = self::getData($coupon, true);
				if ($row['STATUS'] == self::STATUS_NOT_FOUND || $row['STATUS'] == self::STATUS_FREEZE)
				{
					$badCoupons[$coupon] = $row;
				}
			}
			unset($row, $coupon);
			if (!empty($badCoupons))
			{
				self::fillCouponHints($badCoupons);
				$errorData = [];
				foreach ($badCoupons as $row)
				{
					$errorData[$row['COUPON']] = implode(', ', $row['CHECK_CODE_TEXT']);
				}
				unset($row);
				$result->addError(new Main\Error(
					Loc::getMessage('BX_SALE_DCM_COUPONS_VERIFY_ERR'),
					'COUPON',
					$errorData
				));
				unset($errorData);
			}
			unset($badCoupons);
		}
		unset($appliedCoupons);

		return $result;
	}

	/**
	 * Save applied coupons.
	 *
	 * @return Main\Result
	 */
	public static function saveApplied(): Main\Result
	{
		$commonResult = new Main\Result();
		if (
			self::$useMode === self::MODE_SYSTEM
			|| !self::isEntered()
			|| !self::$allowedSave
		)
		{
			return $commonResult;
		}

		$result = [];
		$currentTime = new Main\Type\DateTime();
		$userId = self::getUserId();

		$appliedCoupons = self::filterCoupons(
			[
				'STATUS' => self::STATUS_APPLYED,
				'MODULE_ID' => 'sale',
				'SAVED' => 'N',
			],
			true
		);

		if (!empty($appliedCoupons))
		{
			$result['sale'] = [
				'COUPONS' => $appliedCoupons,
			];
			$saveResult = Internals\DiscountCouponTable::saveApplied($appliedCoupons, $userId, $currentTime);

			if ($saveResult === false)
			{
				$result['sale']['ERROR'] = true;
			}
			else
			{
				if ($saveResult['STATUS'])
				{
					$result['sale']['DEACTIVATE'] = $saveResult['DEACTIVATE'];
					$result['sale']['LIMITED'] = $saveResult['LIMITED'];
					$result['sale']['INCREMENT'] = $saveResult['INCREMENT'];
					self::eraseAppliedCoupons($result['sale']);
				}
				else
				{
					$commonResult->addError(new Main\Error(
						Loc::getMessage('BX_SALE_DCM_ERR_SAVE_APPLIED'),
						'sale',
						$saveResult['ERROR']
					));

					return $commonResult;
				}
			}
		}
		if (!self::$onlySaleDiscount && !empty(self::$couponProviders))
		{
			foreach (self::$couponProviders as $provider)
			{
				$appliedCoupons = self::filterCoupons(
					[
						'STATUS' => self::STATUS_APPLYED,
						'MODULE_ID' => $provider['module'],
						'SAVED' => 'N',
					],
					true
				);
				if (empty($appliedCoupons))
				{
					continue;
				}
				$result[$provider['module']] = [
					'COUPONS' => $appliedCoupons,
				];
				$saveResult = call_user_func_array(
					$provider['saveApplied'],
					[
						$appliedCoupons,
						$userId,
						$currentTime,
					]
				);
				if (empty($saveResult) || !is_array($saveResult))
				{
					$result[$provider['module']]['ERROR'] = true;
				}
				else
				{
					if (!isset($saveResult['STATUS']) || $saveResult['STATUS'])
					{
						$result[$provider['module']]['DEACTIVATE'] = ($saveResult['DEACTIVATE'] ?? []);
						$result[$provider['module']]['LIMITED'] = ($saveResult['LIMITED'] ?? []);
						$result[$provider['module']]['INCREMENT'] = ($saveResult['INCREMENT'] ?? []);
						self::eraseAppliedCoupons($result[$provider['module']]);
					}
					else
					{
						$commonResult->addError(new Main\Error(
							Loc::getMessage('BX_SALE_DCM_ERR_SAVE_APPLIED'),
							$provider['module'],
							$saveResult['ERROR']
						));

						return $commonResult;
					}
				}
			}
		}
		self::saveToStorage();
		self::$allowedSave = false;
		$event = new Main\Event('sale', self::EVENT_ON_SAVE_APPLIED_COUPONS, $result);
		$event->send();

		return $commonResult;
	}

	/**
	 * Set applied information for product.
	 *
	 * @param array $product		Product description.
	 * @param array $couponsList	Coupons for product.
	 * @param bool $oldMode			Compatibility mode with old custom providers.
	 * @return bool
	 */
	public static function setApplyByProduct($product, $couponsList, $oldMode = false)
	{
		static $count = null;
		if ($count === null)
		{
			$count = 0;
		}
		if (self::$useMode === self::MODE_SYSTEM)
		{
			return false;
		}
		if (empty($couponsList) || empty($product))
		{
			return false;
		}
		$oldMode = ($oldMode === true);
		if ($oldMode)
		{
			if (!isset($product['BASKET_ID']))
			{
				$product['BASKET_ID'] = 'c'.$count;
			}
			$count++;
		}
		$hash = ($oldMode ? self::getCatalogProductHash($product) : self::getProductHash($product));

		if ($hash === '')
		{
			return false;
		}
		$applyed = false;
		$applyList = [];
		foreach ($couponsList as $coupon)
		{
			$coupon = trim((string)$coupon);
			if ($coupon === '' || !isset(self::$coupons[$coupon]))
			{
				continue;
			}
			if (
				self::$coupons[$coupon]['STATUS'] == self::STATUS_NOT_FOUND
				|| self::$coupons[$coupon]['STATUS'] == self::STATUS_FREEZE
			)
			{
				continue;
			}
			if (
				self::$coupons[$coupon]['TYPE'] == Internals\DiscountCouponTable::TYPE_BASKET_ROW
				&& !empty(self::$coupons[$coupon]['BASKET'])
			)
			{
				continue;
			}
			self::$coupons[$coupon]['BASKET'][$hash] = true;
			self::$coupons[$coupon]['STATUS'] = self::STATUS_APPLYED;
			$applyed = true;
			$applyList[$coupon] = self::$coupons[$coupon];
		}
		unset($coupon);
		if ($applyed)
		{
			$event = new Main\Event(
				'sale',
				self::EVENT_ON_COUPON_APPLY_PRODUCT,
				[
					'PRODUCT' => $product,
					'COUPONS' => $applyList,
				]
			);
			$event->send();
		}
		unset($applyList);

		return $applyed;
	}

	/**
	 * Set applied information for basket.
	 *
	 * @param string $coupon		Coupon.
	 * @param array $data				Apply data (basket, delivery).
	 * @return bool
	 */
	public static function setApply($coupon, $data)
	{
		if (self::$useMode === self::MODE_SYSTEM)
		{
			return false;
		}
		$coupon = trim((string)$coupon);
		if ($coupon === '' || empty($data) || !is_array($data))
		{
			return false;
		}
		if (!isset(self::$coupons[$coupon]))
		{
			return false;
		}
		if (
			self::$coupons[$coupon]['STATUS'] == self::STATUS_NOT_FOUND
			|| self::$coupons[$coupon]['STATUS'] == self::STATUS_FREEZE
		)
		{
			return false;
		}
		$result = [];
		if ((!empty($data['BASKET']) && is_array($data['BASKET'])) || !empty($data['DELIVERY']))
		{
			if (!empty($data['BASKET']) && is_array($data['BASKET']))
			{
				if (self::$coupons[$coupon]['TYPE'] == Internals\DiscountCouponTable::TYPE_BASKET_ROW && count($data['BASKET']) > 1)
				{
					return false;
				}
				foreach ($data['BASKET'] as $product)
				{
					if (empty($product))
					{
						continue;
					}
					$hash = self::getProductHash($product);
					if ($hash === '')
					{
						continue;
					}
					if (
						self::$coupons[$coupon]['TYPE'] == Internals\DiscountCouponTable::TYPE_BASKET_ROW
						&& !empty(self::$coupons[$coupon]['BASKET'])
					)
					{
						continue;
					}
					self::$coupons[$coupon]['BASKET'][$hash] = true;
					self::$coupons[$coupon]['STATUS'] = self::STATUS_APPLYED;
					$result['COUPON'] = self::$coupons[$coupon];
					if (!isset($result['BASKET']))
					{
						$result['BASKET'] = [];
					}
					$result['BASKET'][] = $product;
				}
				unset($product);
			}
			if (!empty($data['DELIVERY']))
			{
				self::$coupons[$coupon]['DELIVERY'] = $data['DELIVERY'];
				self::$coupons[$coupon]['STATUS'] = self::STATUS_APPLYED;
				$result['COUPON'] = self::$coupons[$coupon];
				$result['DELIVERY'] = self::$coupons[$coupon]['DELIVERY'];
			}
			$event = new Main\Event('sale', self::EVENT_ON_COUPON_APPLY, $result);
			unset($result);
			$event->send();

			return true;
		}

		return false;
	}

	/**
	 * Clear applied information for product.
	 *
	 * @param array $product		Product description.
	 * @return bool
	 */
	public static function deleteApplyByProduct($product)
	{
		if (self::$useMode === self::MODE_SYSTEM || empty($product))
		{
			return false;
		}
		$hash = self::getProductHash($product);
		if ($hash === '')
		{
			return false;
		}
		$success = false;
		foreach (self::$coupons as &$oneCoupon)
		{
			if ($oneCoupon['STATUS'] == self::STATUS_NOT_FOUND || $oneCoupon['STATUS'] == self::STATUS_FREEZE)
			{
				continue;
			}
			if ($oneCoupon['SAVED'] === 'Y')
			{
				continue;
			}
			if (isset($oneCoupon['BASKET'][$hash]))
			{
				unset($oneCoupon['BASKET'][$hash]);
				if (empty($oneCoupon['BASKET']) && empty($oneCoupon['DELIVERY']))
				{
					$oneCoupon['STATUS'] = self::STATUS_NOT_APPLYED;
				}
				$success = true;
			}
		}
		unset($oneCoupon);

		return $success;
	}

	/**
	 * Change status coupons for save.
	 *
	 * @return void
	 */
	public static function finalApply()
	{
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess() || empty(self::$coupons))
		{
			return;
		}

		foreach (self::$coupons as &$oneCoupon)
		{
			if ($oneCoupon['STATUS'] == self::STATUS_ENTERED)
			{
				$oneCoupon['STATUS'] = self::STATUS_NOT_APPLYED;
				if ($oneCoupon['CHECK_CODE'] == self::COUPON_CHECK_OK)
				{
					$oneCoupon['CHECK_CODE'] = self::COUPON_CHECK_NOT_APPLIED;
				}
			}
		}
		unset($oneCoupon);
	}

	/**
	 * Clear applied data for coupon.
	 *
	 * @param string $coupon			Coupon.
	 * @return bool
	 */
	public static function clearApplyCoupon($coupon)
	{
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess())
		{
			return false;
		}
		if (empty(self::$coupons))
		{
			return true;
		}
		$coupon = trim((string)$coupon);
		if ($coupon === '')
		{
			return false;
		}
		if (!isset(self::$coupons[$coupon]))
		{
			return false;
		}
		if (
			self::$coupons[$coupon]['STATUS'] == self::STATUS_NOT_FOUND
			|| self::$coupons[$coupon]['STATUS'] == self::STATUS_FREEZE
		)
		{
			return false;
		}
		self::$coupons[$coupon]['STATUS'] = self::STATUS_ENTERED;
		self::createApplyFields(self::$coupons[$coupon]);

		return true;
	}

	/**
	 * Clear applied data for coupons.
	 *
	 * @param bool $all					Clear for coupons or not saved.
	 * @return bool
	 */
	public static function clearApply($all = true)
	{
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess())
		{
			return false;
		}
		if (self::isFrozenCouponStorage())
		{
			return false;
		}
		if (empty(self::$coupons))
		{
			return true;
		}
		$all = ($all !== false);
		foreach (self::$coupons as &$coupon)
		{
			if (
				$coupon['STATUS'] == self::STATUS_NOT_FOUND
				|| $coupon['STATUS'] == self::STATUS_FREEZE
			)
			{
				continue;
			}
			if (!$all && $coupon['SAVED'] === 'Y')
			{
				continue;
			}
			$coupon['STATUS'] = self::STATUS_ENTERED;
			self::createApplyFields($coupon);
		}
		unset($coupon);

		return true;
	}

	/**
	 * Returns information about coupon.
	 *
	 * @param string $coupon			Coupon for search.
	 * @param bool $checkCoupon			Check coupon data.
	 * @return array|false
	 */
	public static function getData($coupon, $checkCoupon = true)
	{
		$currentTime = new Main\Type\DateTime();
		$currentTimestamp = $currentTime->getTimestamp();
		self::initUseDiscount();
		$coupon = trim((string)$coupon);
		if ($coupon === '')
		{
			return false;
		}
		$checkCoupon = ($checkCoupon === true);

		$result = static::getEmptyCouponFields($coupon);

		$resultKeyList = [
			'ID',
			'COUPON',
			'DISCOUNT_ID',
			'TYPE',
			'ACTIVE',
			'DISCOUNT_NAME',
			'DISCOUNT_ACTIVE',
			'DISCOUNT_ACTIVE_FROM',
			'DISCOUNT_ACTIVE_TO',
		];

		$couponIterator = Internals\DiscountCouponTable::getList([
			'select' => [
				'ID',
				'COUPON',
				'DISCOUNT_ID',
				'TYPE',
				'ACTIVE',
				'USER_ID',
				'MAX_USE',
				'USE_COUNT',
				'ACTIVE_FROM',
				'ACTIVE_TO',
				'DISCOUNT_NAME' => 'DISCOUNT.NAME',
				'DISCOUNT_ACTIVE' => 'DISCOUNT.ACTIVE',
				'DISCOUNT_ACTIVE_FROM' => 'DISCOUNT.ACTIVE_FROM',
				'DISCOUNT_ACTIVE_TO' => 'DISCOUNT.ACTIVE_TO',
			],
			'filter' => [
				'=COUPON' => $coupon,
			],
		]);
		if ($existCoupon = $couponIterator->fetch())
		{
			$result['MODE'] = self::COUPON_MODE_FULL;
			$result['MODULE'] = 'sale';
			$result['MODULE_ID'] = 'sale';
			$checkCode = self::checkBaseData($existCoupon, self::COUPON_CHECK_OK);
			foreach ($resultKeyList as $resultKey)
			{
				$result[$resultKey] = $existCoupon[$resultKey];
			}
			unset($resultKey);

			if ($checkCoupon)
			{
				$checkCode = self::checkFullData($existCoupon, $result['MODE'], $checkCode, $currentTimestamp);
				self::fillUserInfo($result, $existCoupon, $checkCode);
			}
			$result['STATUS'] = ($checkCode == self::COUPON_CHECK_OK ? self::STATUS_ENTERED : self::STATUS_FREEZE);
			$result['CHECK_CODE'] = $checkCode;
			unset($checkCode);
		}
		elseif (!self::$onlySaleDiscount && !empty(self::$couponProviders))
		{
			foreach (self::$couponProviders as $provider)
			{
				$existCoupon = call_user_func_array(
					$provider['getData'],
					[
						$coupon,
					]
				);
				if (!empty($existCoupon) && is_array($existCoupon))
				{
					$result['MODE'] = (int)$provider['mode'];
					$result['MODULE'] = $provider['module'];
					$result['MODULE_ID'] = $provider['module'];
					$checkCode = self::checkBaseData($existCoupon, self::COUPON_CHECK_OK);
					foreach ($resultKeyList as $resultKey)
					{
						$result[$resultKey] = $existCoupon[$resultKey];
					}
					unset($resultKey);

					if ($checkCoupon)
					{
						$checkCode = self::checkFullData($existCoupon, $result['MODE'], $checkCode, $currentTimestamp);
						self::fillUserInfo($result, $existCoupon, $checkCode);
					}
					$result['STATUS'] = ($checkCode == self::COUPON_CHECK_OK ? self::STATUS_ENTERED : self::STATUS_FREEZE);
					$result['CHECK_CODE'] = $checkCode;
					unset($checkCode);
					break;
				}
			}
			unset($provider);
		}

		return $result;
	}

	/**
	 * Checks if a coupon exists.
	 *
	 * @param string $coupon		Coupon for check.
	 * @return array|bool
	 */
	public static function isExist($coupon)
	{
		$coupon = trim((string)$coupon);
		if ($coupon === '')
		{
			return false;
		}

		self::initUseDiscount();
		$couponIterator = Internals\DiscountCouponTable::getList([
			'select' => [
				'ID',
				'COUPON',
			],
			'filter' => [
				'=COUPON' => $coupon,
			],
		]);
		if ($existCoupon = $couponIterator->fetch())
		{
			return [
				'ID' => $existCoupon['ID'],
				'COUPON' => $existCoupon['COUPON'],
				'MODULE' => 'sale',
				'MODULE_ID' => 'sale',
			];
		}
		else
		{
			if (!self::$onlySaleDiscount && !empty(self::$couponProviders))
			{
				foreach (self::$couponProviders as $provider)
				{
					$existCoupon = call_user_func_array(
						$provider['isExist'],
						[
							$coupon,
						]
					);
					if (!empty($existCoupon) && is_array($existCoupon))
					{
						if (!isset($existCoupon['ID']) || !isset($existCoupon['COUPON']))
						{
							continue;
						}
						return [
							'ID' => $existCoupon['ID'],
							'COUPON' => $existCoupon['COUPON'],
							'MODULE' => $provider['module'],
							'MODULE_ID' => $provider['module'],
						];
					}
				}
				unset($provider);
			}
		}

		return false;
	}

	/**
	 * Returns entered coupon data.
	 *
	 * @param string $coupon			Coupon code.
	 * @param bool $clearData			Clear data for save order coupon.
	 * @return bool|array
	 */
	public static function getEnteredCoupon($coupon, $clearData = false)
	{
		if (!self::$init)
		{
			self::init();
		}
		$result = false;
		if (self::$useMode === self::MODE_SYSTEM || !self::isSuccess())
		{
			return false;
		}

		$clearData = ($clearData === true);
		$coupon = trim((string)$coupon);
		if ($coupon === '')
		{
			return false;
		}
		if (!isset(self::$coupons[$coupon]))
		{
			$couponData = self::getData($coupon);
			if (isset(self::$coupons[$couponData['COUPON']]))
			{
				$result = self::$coupons[$couponData['COUPON']];
			}
		}
		else
		{
			$result = self::$coupons[$coupon];
		}
		if (!empty($result))
		{
			if ($result['MODE'] === self::COUPON_MODE_FULL)
			{
				$result['USER_INFO'] = $result['SYSTEM_DATA'];
				unset($result['SYSTEM_DATA']);
			}
			if ($clearData)
			{
				foreach (self::$clearFields as $fieldName)
				{
					unset($result[$fieldName]);
				}
				unset($fieldName);
				foreach (self::$timeFields as $fieldName)
				{
					if (isset($result[$fieldName]) && $result[$fieldName] instanceof Main\Type\DateTime)
					{
						$result[$fieldName] = $result[$fieldName]->getTimestamp();
					}
				}
				unset($fieldName);

				if (!empty($result['USER_INFO']))
				{
					foreach (self::$timeFields as $fieldName)
					{
						if (isset($result['USER_INFO'][$fieldName]) && $result['USER_INFO'][$fieldName] instanceof Main\Type\DateTime)
						{
							$result['USER_INFO'][$fieldName] = $result['USER_INFO'][$fieldName]->getTimestamp();
						}
					}
					unset($fieldName);
				}
			}
		}

		return $result;
	}

	/**
	 * Clear coupons storage with logout from public.
	 *
	 * @return void
	 */
	public static function logout()
	{
		if (!self::$init)
		{
			self::init();
		}
		if (self::$useMode !== self::MODE_CLIENT)
		{
			return;
		}
		if (self::isSuccess())
		{
			self::clear(true);
		}
	}

	/**
	 * Returns true if the coupon was used in the order and saved.
	 *
	 * @param array $coupon			Coupon data.
	 * @return bool
	 */
	public static function filterOrderCoupons($coupon)
	{
		return (isset($coupon['SAVED']) && $coupon['SAVED'] === 'Y');
	}

	/**
	 * Reload discount coupons providers.
	 * @internal
	 *
	 * @param bool $mode true, if you need use only sale discounts.
	 * @return void
	 */
	public static function setUseOnlySaleDiscounts($mode)
	{
		if (!is_bool($mode))
		{
			return;
		}
		if (self::getUseMode() != self::MODE_ORDER)
		{
			return;
		}
		self::$onlySaleDiscount = $mode;
		self::loadCouponProviders();
	}

	/**
	 * Checks the basic coupon fields.
	 *
	 * @param array &$data			Coupon data.
	 * @param int $checkCode		Start status.
	 * @return int
	 */
	protected static function checkBaseData(&$data, $checkCode = self::COUPON_CHECK_OK)
	{
		if (empty(self::$couponTypes))
		{
			self::$couponTypes = Internals\DiscountCouponTable::getCouponTypes(true);
		}

		if (!isset($data['ID']))
		{
			$data['ID'] = 0;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		else
		{
			$data['ID'] = (int)$data['ID'];
			if ($data['ID'] <= 0 && self::$checkActivity)
			{
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
		}
		if (!isset($data['DISCOUNT_ID']))
		{
			$data['DISCOUNT_ID'] = 0;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		else
		{
			$data['DISCOUNT_ID'] = (int)$data['DISCOUNT_ID'];
			if ($data['DISCOUNT_ID'] <= 0 && self::$checkActivity)
			{
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
		}
		if (!isset($data['TYPE']))
		{
			$data['TYPE'] = Internals\DiscountCouponTable::TYPE_UNKNOWN;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		else
		{
			$data['TYPE'] = (int)$data['TYPE'];
			if (!isset(self::$couponTypes[$data['TYPE']]) && $data['TYPE'] != Internals\DiscountCouponTable::TYPE_ARCHIVED)
			{
				$data['TYPE'] = Internals\DiscountCouponTable::TYPE_UNKNOWN;
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
		}
		if (!isset($data['ACTIVE']))
		{
			$data['ACTIVE'] = '';
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		else
		{
			$data['ACTIVE'] = (string)$data['ACTIVE'];
			if ($data['ACTIVE'] !== 'Y' && $data['ACTIVE'] !== 'N')
			{
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
		}
		if (isset($data['ACTIVE_FROM']) && !($data['ACTIVE_FROM'] instanceof Main\Type\DateTime))
		{
			$data['ACTIVE_FROM'] = null;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		if (isset($data['ACTIVE_TO']) && !($data['ACTIVE_TO'] instanceof Main\Type\DateTime))
		{
			$data['ACTIVE_TO'] = null;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		$data['DISCOUNT_NAME'] = (isset($data['DISCOUNT_NAME']) ? (string)$data['DISCOUNT_NAME'] : '');
		if (!isset($data['DISCOUNT_ACTIVE']))
		{
			$data['DISCOUNT_ACTIVE'] = '';
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		else
		{
			$data['DISCOUNT_ACTIVE'] = (string)$data['DISCOUNT_ACTIVE'];
			if ($data['DISCOUNT_ACTIVE'] !== 'Y' && $data['DISCOUNT_ACTIVE'] !== 'N')
			{
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
		}
		if (isset($data['DISCOUNT_ACTIVE_FROM']) && !($data['DISCOUNT_ACTIVE_FROM'] instanceof Main\Type\DateTime))
		{
			$data['DISCOUNT_ACTIVE_FROM'] = null;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}
		if (isset($data['DISCOUNT_ACTIVE_TO']) && !($data['DISCOUNT_ACTIVE_TO'] instanceof Main\Type\DateTime))
		{
			$data['DISCOUNT_ACTIVE_TO'] = null;
			$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
		}

		return $checkCode;
	}

	/**
	 * Checks the extended coupon fields.
	 *
	 * @param array &$data			Coupon data.
	 * @param int $mode				Coupon mode (full or simple).
	 * @param int $checkCode		Start check status.
	 * @param int $currentTimestamp		Current time.
	 * @return int
	 */
	protected static function checkFullData(&$data, $mode, $checkCode, $currentTimestamp)
	{
		$mode = ((int)$mode !== self::COUPON_MODE_SIMPLE ? self::COUPON_MODE_FULL : self::COUPON_MODE_SIMPLE);

		if (self::$checkActivity)
		{
			if ($data['ACTIVE'] !== 'Y')
			{
				$checkCode |= self::COUPON_CHECK_NO_ACTIVE;
			}
			if ($data['DISCOUNT_ACTIVE'] !== 'Y')
			{
				$checkCode |= self::COUPON_CHECK_NO_ACTIVE_DISCOUNT;
			}
			if ($data['DISCOUNT_ACTIVE_FROM'] instanceof Main\Type\DateTime && $data['DISCOUNT_ACTIVE_FROM']->getTimestamp() > $currentTimestamp)
			{
				$checkCode |= self::COUPON_CHECK_RANGE_ACTIVE_FROM_DISCOUNT;
			}
			if ($data['DISCOUNT_ACTIVE_TO'] instanceof Main\Type\DateTime && $data['DISCOUNT_ACTIVE_TO']->getTimestamp() < $currentTimestamp)
			{
				$checkCode |= self::COUPON_CHECK_RANGE_ACTIVE_TO_DISCOUNT;
			}
		}

		if ($mode === self::COUPON_MODE_FULL)
		{
			if (self::$checkActivity)
			{
				if ($data['ACTIVE_FROM'] instanceof Main\Type\DateTime && $data['ACTIVE_FROM']->getTimestamp() > $currentTimestamp)
				{
					$checkCode |= self::COUPON_CHECK_RANGE_ACTIVE_FROM;
				}
				if ($data['ACTIVE_TO'] instanceof Main\Type\DateTime && $data['ACTIVE_TO']->getTimestamp() < $currentTimestamp)
				{
					$checkCode |= self::COUPON_CHECK_RANGE_ACTIVE_TO;
				}
			}
			if (!isset($data['USER_ID']))
			{
				$data['USER_ID'] = 0;
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
			else
			{
				$data['USER_ID'] = (int)$data['USER_ID'];
				if ($data['USER_ID'] > 0 && $data['USER_ID'] != self::$userId)
				{
					$checkCode |= self::COUPON_CHECK_BAD_USER_ID;
				}
			}
			if (!isset($data['MAX_USE']))
			{
				$data['MAX_USE'] = 0;
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
			else
			{
				$data['MAX_USE'] = (int)$data['MAX_USE'];
			}
			if (!isset($data['USE_COUNT']))
			{
				$data['USE_COUNT'] = 0;
				$checkCode |= self::COUPON_CHECK_CORRUPT_DATA;
			}
			else
			{
				if (self::$checkActivity)
				{
					$data['USE_COUNT'] = (int)$data['USE_COUNT'];
					if ($data['MAX_USE'] > 0 && $data['USE_COUNT'] >= $data['MAX_USE'])
					{
						$checkCode |= self::COUPON_CHECK_ALREADY_MAX_USED;
					}
				}
			}
		}

		return $checkCode;
	}

	/**
	 * Fill client information.
	 *
	 * @param array &$result			Coupon data.
	 * @param array $existCoupon	User data.
	 * @param int $checkCode		Checked result.
	 * @return void
	 */
	protected static function fillUserInfo(&$result, $existCoupon, $checkCode)
	{
		if ($checkCode == self::COUPON_CHECK_OK && $result['MODE'] === self::COUPON_MODE_FULL)
		{
			$result['SYSTEM_DATA'] = [
				'USER_ID' => $existCoupon['USER_ID'],
				'MAX_USE' => $existCoupon['MAX_USE'],
				'USE_COUNT' => $existCoupon['USE_COUNT'],
				'ACTIVE_FROM' => $existCoupon['ACTIVE_FROM'],
				'ACTIVE_TO' => $existCoupon['ACTIVE_TO'],
			];
			if (self::usedByManager() || ($existCoupon['USER_ID'] > 0 && $existCoupon['USER_ID'] == self::$userId))
			{
				$result['USER_INFO'] = $result['SYSTEM_DATA'];
			}
		}
	}

	/**
	 * Get user by fuser id.
	 *
	 * @return void
	 */
	protected static function initUserId(): void
	{
		if (
			self::isSuccess()
			&& self::$useMode === self::MODE_CLIENT
			&& self::$userId === null
		)
		{
			$currentUserId = self::getCurrentUserId();
			if ($currentUserId === null)
			{
				$fuserId = Fuser::getId(true);
				if ($fuserId !== null)
				{
					// TODO: replace this code after refactoring Fuser::getUserIdById
					$row = Internals\FuserTable::getRow([
						'select' => [
							'ID',
							'USER_ID',
						],
						'filter' => [
							'=ID' => $fuserId,
						],
						'order' => [
							'ID' => 'DESC',
						],
					]);
					if ($row !== null)
					{
						$currentUserId = (int)$row['USER_ID'];
					}
					// end TODO
				}
				unset($fuserId);
			}
			if ($currentUserId === null)
			{
				self::$errors[] = Loc::getMessage('BX_SALE_DCM_ERR_BAD_FUSER_ID');
			}
			else
			{
				self::$userId = $currentUserId;
			}
			unset($currentUserId);
		}
	}

	/**
	 * Save current coupons to session storage.
	 *
	 * @return void
	 */
	protected static function saveToStorage()
	{
		if (self::isSuccess())
		{
			$session = self::getSession();
			if (!$session)
			{
				return;
			}

			$couponsList = [];
			if (!empty(self::$coupons))
			{
				$couponsList = array_filter(self::$coupons, '\Bitrix\Sale\DiscountCouponsManager::clearSavedCoupons');
				if (!empty($couponsList))
				{
					$couponsList = array_keys($couponsList);
				}
			}

			if (self::usedByManager())
			{
				if (!isset($session[self::STORAGE_MANAGER_COUPONS]) || !is_array($session[self::STORAGE_MANAGER_COUPONS]))
				{
					$session[self::STORAGE_MANAGER_COUPONS] = [];
				}
				$session[self::STORAGE_MANAGER_COUPONS][self::$userId] = $couponsList;
			}
			else
			{
				$session[self::STORAGE_CLIENT_COUPONS] = $couponsList;
			}
			unset($couponsList);
		}
	}

	/**
	 * Clear applied coupons.
	 *
	 * @param array $result		Applied coupons.
	 * @return void
	 */
	protected static function eraseAppliedCoupons($result)
	{
		if (!empty($result['DEACTIVATE']) || !empty($result['LIMITED']))
		{
			$clear = array_keys(array_merge($result['DEACTIVATE'], $result['LIMITED']));
			foreach ($clear as $coupon)
			{
				if (isset(self::$coupons[$coupon]))
				{
					unset(self::$coupons[$coupon]);
				}
				if (isset(self::$lockedCoupons[$coupon]))
				{
					unset(self::$lockedCoupons[$coupon]);
				}
			}
			unset($coupon, $clear);
		}
	}

	/**
	 * Create applied fields.
	 *
	 * @param array &$couponData	Coupon data.
	 * @return void
	 */
	protected static function createApplyFields(&$couponData)
	{
		$couponData['BASKET'] = [];
		$couponData['DELIVERY'] = [];
	}

	/**
	 * Load coupon providers from modules.
	 *
	 * @return void
	 */
	protected static function loadCouponProviders()
	{
		self::$couponProviders = [];
		if (!self::$onlySaleDiscount)
		{
			$eventData = [
				'COUPON_UNKNOWN' => Internals\DiscountCouponTable::TYPE_UNKNOWN,
				'COUPON_TYPES' => Internals\DiscountCouponTable::getCouponTypes(false),
			];
			$event = new Main\Event('sale', self::EVENT_ON_BUILD_COUPON_PROVIDES, $eventData);
			$event->send();
			$resultList = $event->getResults();
			if (empty($resultList) || !is_array($resultList))
				return;
			/** @var Main\EventResult $eventResult */
			foreach ($resultList as $eventResult)
			{
				if ($eventResult->getType() != Main\EventResult::SUCCESS)
				{
					continue;
				}
				$module = (string)$eventResult->getModuleId();
				$provider = $eventResult->getParameters();
				if (empty($provider) || !is_array($provider))
				{
					continue;
				}
				if (empty($provider['getData']) || empty($provider['isExist']) || empty($provider['saveApplied']))
				{
					continue;
				}
				self::$couponProviders[] = [
					'module' => $module,
					'getData' => $provider['getData'],
					'isExist' => $provider['isExist'],
					'saveApplied' => $provider['saveApplied'],
					'mode' => (
						(int)($provider['mode'] ?? self::COUPON_MODE_SIMPLE) === self::COUPON_MODE_FULL
							? self::COUPON_MODE_FULL
							: self::COUPON_MODE_SIMPLE
					),
				];
			}
			unset($provider, $module, $eventResult, $resultList, $event, $eventData);
		}
	}

	/**
	 * Initialization coupons providers.
	 *
	 * @return void
	 */
	protected static function initUseDiscount()
	{
		if (self::$onlySaleDiscount !== null)
		{
			return;
		}

		self::$onlySaleDiscount = Option::get('sale', 'use_sale_discount_only') === 'Y';
		self::loadCouponProviders();
	}

	/**
	 * Filter for remove unknown coupons.
	 *
	 * @param array $coupon		Coupon data.
	 * @return bool
	 */
	protected static function filterUnknownCoupons($coupon)
	{
		if (empty(self::$couponTypes))
		{
			self::$couponTypes = Internals\DiscountCouponTable::getCouponTypes(true);
		}

		return (isset($coupon['TYPE']) && isset(self::$couponTypes[$coupon['TYPE']]));
	}

	/**
	 * Filter for remove freeze coupons.
	 *
	 * @param array $coupon		Coupon data.
	 * @return bool
	 */
	protected static function filterFreezeCoupons($coupon)
	{
		if (empty(self::$couponTypes))
		{
			self::$couponTypes = Internals\DiscountCouponTable::getCouponTypes(true);
		}

		return (
			isset($coupon['TYPE'])
			&& isset(self::$couponTypes[$coupon['TYPE']])
			&& $coupon['STATUS'] != self::STATUS_FREEZE
		);
	}

	/**
	 * Filter for remove freeze ordered coupons.
	 *
	 * @param array $coupon		Coupon data.
	 * @return bool
	 */
	protected static function filterFreezeOrderedCoupons($coupon)
	{
		static $currentTimeStamp = null;
		if ($currentTimeStamp === null)
		{
			$currentTimeStamp = time();
		}
		if (!isset($coupon['SAVED']) || $coupon['SAVED'] !== 'Y')
		{
			return true;
		}
		if (isset($coupon['MODE']) && $coupon['MODE'] === self::COUPON_MODE_FULL)
		{
			if (
				isset($coupon['ACTIVE_FROM']) && $coupon['ACTIVE_FROM'] instanceof Main\Type\DateTime
				&& $coupon['ACTIVE_FROM']->getTimestamp() > $currentTimeStamp
			)
			{
				return false;
			}
			if (
				isset($coupon['ACTIVE_TO']) && $coupon['ACTIVE_TO'] instanceof Main\Type\DateTime
				&& $coupon['ACTIVE_TO']->getTimestamp() < $currentTimeStamp
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Clear one row coupons.
	 *
	 * @param array $coupon		Coupon data.
	 * @param string $hash		Product hash.
	 * @return bool
	 */
	protected static function filterOneRowCoupons($coupon, $hash)
	{
		return (
			$coupon['TYPE'] != Internals\DiscountCouponTable::TYPE_BASKET_ROW
			|| empty($coupon['BASKET'])
			|| (count($coupon['BASKET']) === 1 && isset($coupon['BASKET'][$hash]))
		);
	}

	/**
	 * Returns one coupon for one discount.
	 *
	 * @param array &$coupons		Coupons list.
	 * @return void
	 */
	protected static function filterUniqueDiscount(&$coupons)
	{
		$existDiscount = [];
		$hash = '';
		foreach ($coupons as $key => $oneCoupon)
		{
			$hash = $oneCoupon['MODULE_ID'].':'.$oneCoupon['DISCOUNT_ID'];
			if (
				isset($existDiscount[$hash])
				&& (
					$oneCoupon['TYPE'] == Internals\DiscountCouponTable::TYPE_ONE_ORDER
					|| $oneCoupon['TYPE'] == Internals\DiscountCouponTable::TYPE_MULTI_ORDER
				)
			)
			{
				unset($coupons[$key]);
			}
			else
			{
				$existDiscount[$hash] = true;
			}
		}
		unset($hash, $existDiscount);
	}

	protected static function filterLockedCoupons(array &$couponList): void
	{
		if (empty($couponList) || !static::usedByClient())
		{
			return;
		}

		$locker = Sale\Discount\CouponLocker::getInstance();
		foreach (array_keys($couponList) as $coupon)
		{
			if (!static::needLockCoupon($coupon))
			{
				continue;
			}
			if (isset(self::$lockedCoupons[$coupon]))
			{
				continue;
			}
			$locker->lock($coupon);
			if ($locker->isLocked($coupon))
			{
				self::$lockedCoupons[$coupon] = true;
			}
			else
			{
				unset($couponList[$coupon]);
			}
		}
		unset($locker);
	}

	/**
	 * Filter manager coupons list.
	 *
	 * @param array $filter			Filter for coupons.
	 * @param bool $getId			Resturn Id or full data.
	 * @return array
	 */
	protected static function filterCoupons($filter, $getId = false)
	{
		$getId = ($getId === true);
		$result = [];
		if (empty(self::$coupons) || empty($filter) || !is_array($filter))
		{
			return $result;
		}

		foreach (self::$coupons as $id => $data)
		{
			$copy = true;
			foreach ($filter as $filterKey => $filterValue)
			{
				if (is_array($filterValue) && isset($filterValue['LOGIC']))
				{
					$logic = mb_strtolower($filterValue['LOGIC']);
					if ($logic !== 'and' && $logic !== 'or')
					{
						break 2;
					}
					unset($filterValue['LOGIC']);
					if (empty($filterValue))
					{
						break 2;
					}
					$subresult = [];
					foreach ($filterValue as $subfilterKey => $subfilterValue)
					{
						$invert = strncmp($subfilterKey, '!', 1) === 0;
						$fieldName = ($invert? mb_substr($subfilterKey, 1) : $subfilterKey);
						if (!isset($data[$fieldName]))
						{
							break 3;
						}
						else
						{
							$compare = (
								is_array($subfilterValue)
									? in_array($data[$fieldName], $subfilterValue)
									: $data[$fieldName] == $subfilterValue
							);
							if ($invert)
							{
								$compare = !$compare;
							}
							$subresult[] = $compare;
						}
					}
					$compare = (
						$logic === 'and'
						? !in_array(false, $subresult, true)
						: in_array(true, $subresult, true)
					);
					if (!$compare)
					{
						$copy = false;
						break;
					}
				}
				else
				{
					$invert = strncmp($filterKey, '!', 1) === 0;
					$fieldName = ($invert? mb_substr($filterKey, 1) : $filterKey);
					if (!isset($data[$fieldName]))
					{
						break 2;
					}
					else
					{
						$compare = (
							is_array($filterValue)
								? in_array($data[$fieldName], $filterValue)
								: $data[$fieldName] == $filterValue
						);
						if ($invert)
						{
							$compare = !$compare;
						}
						if (!$compare)
						{
							$copy = false;
							break;
						}
					}
				}
			}
			if ($copy)
			{
				$result[$id] = ($getId ? $data['ID'] : $data);
			}
		}

		return $result;
	}

	/**
	 * Filter coupons list.
	 *
	 * @param array &$coupons		Coupons list.
	 * @param array $filter			Coupon filter.
	 * @return void
	 */
	protected static function filterArrayCoupons(&$coupons, $filter)
	{
		if (empty($coupons) || !is_array($coupons) || empty($filter) || !is_array($filter))
		{
			return;
		}
		$result = [];
		foreach ($coupons as $id => $data)
		{
			$copy = true;
			foreach ($filter as $filterKey => $filterValue)
			{
				if (is_array($filterValue) && isset($filterValue['LOGIC']))
				{
					$logic = mb_strtolower($filterValue['LOGIC']);
					if ($logic !== 'and' && $logic !== 'or')
					{
						break 2;
					}
					unset($filterValue['LOGIC']);
					if (empty($filterValue))
					{
						break 2;
					}
					$subresult = [];
					foreach ($filterValue as $subfilterKey => $subfilterValue)
					{
						$invert = strncmp($subfilterKey, '!', 1) === 0;
						$fieldName = ($invert? mb_substr($subfilterKey, 1) : $subfilterKey);
						if (!isset($data[$fieldName]))
						{
							break 3;
						}
						else
						{
							$compare = (
								is_array($subfilterValue)
									? in_array($data[$fieldName], $subfilterValue)
									: $data[$fieldName] == $subfilterValue
							);
							if ($invert)
							{
								$compare = !$compare;
							}
							$subresult[] = $compare;
						}
					}
					$compare = (
						$logic === 'and'
							? !in_array(false, $subresult, true)
							: in_array(true, $subresult, true)
					);
					if (!$compare)
					{
						$copy = false;
						break;
					}
				}
				else
				{
					$invert = strncmp($filterKey, '!', 1) === 0;
					$fieldName = ($invert? mb_substr($filterKey, 1) : $filterKey);
					if (!isset($data[$fieldName]))
					{
						break 2;
					}
					else
					{
						$compare = (
							is_array($filterValue)
								? in_array($data[$fieldName], $filterValue)
								: $data[$fieldName] == $filterValue
						);
						if ($invert)
						{
							$compare = !$compare;
						}
						if (!$compare)
						{
							$copy = false;
							break;
						}
					}
				}
			}
			if ($copy)
			{
				$result[$id] = $data;
			}
		}
		$coupons = $result;
		unset($result);
	}

	/**
	 * Create product hash.
	 *
	 * @param array $product		Product description.
	 * @return string
	 */
	protected static function getProductHash($product)
	{
		$hash = '';
		if (!empty($product) && is_array($product))
		{
			$module = '';
			if (isset($product['MODULE_ID']))
			{
				$module = trim((string)$product['MODULE_ID']);
			}
			elseif (isset($product['MODULE']))
			{
				$module = trim((string)$product['MODULE']);
			}
			$productId = (isset($product['PRODUCT_ID']) ? (int)$product['PRODUCT_ID'] : 0);
			$basketId = (isset($product['BASKET_ID']) ? trim((string)$product['BASKET_ID']) : '0');
			if ($productId > 0 && $basketId !== '')
			{
				$hash = $module.':'.$productId.':'.$basketId;
			}
		}

		return $hash;
	}

	/**
	 * Create catalog product hash for old custom providers.
	 *
	 * @param array $product		Product description.
	 * @return string
	 */
	protected static function getCatalogProductHash($product)
	{
		$hash = '';
		$module = 'catalog';
		$productId = 0;
		$basketId = '';
		if (!empty($product) && is_array($product))
		{
			if (isset($product['MODULE_ID']))
			{
				$module = trim((string)$product['MODULE_ID']);
			}
			elseif (isset($product['MODULE']))
			{
				$module = trim((string)$product['MODULE']);
			}
			if (isset($product['PRODUCT_ID']))
			{
				$productId = (int)$product['PRODUCT_ID'];
			}
			$basketId = (isset($product['BASKET_ID']) ? trim((string)$product['BASKET_ID']) : '0');
		}
		if ($productId >= 0 && $basketId !== '')
		{
			$hash = $module.':'.$productId.':'.$basketId;
		}

		return $hash;
	}
	/**
	 * Fill coupon hints.
	 *
	 * @param array &$coupons			Coupons list.
	 * @return void
	 */
	protected static function fillCouponHints(&$coupons)
	{
		$statusList = self::getStatusList(true);
		$checkCode = self::getCheckCodeList(true);
		foreach ($coupons as &$oneCoupon)
		{
			$oneCoupon['STATUS_TEXT'] = $statusList[$oneCoupon['STATUS']];
			if ($oneCoupon['CHECK_CODE'] == self::COUPON_CHECK_OK || $oneCoupon['CHECK_CODE'] == self::COUPON_CHECK_NOT_APPLIED)
			{
				if ($oneCoupon['CHECK_CODE'] == self::COUPON_CHECK_OK)
				{
					$oneCoupon['CHECK_CODE_TEXT'] = (
						$oneCoupon['STATUS'] == self::STATUS_APPLYED
							? [$statusList[$oneCoupon['STATUS']]]
							: [$checkCode[self::COUPON_CHECK_OK]]
					);
				}
				else
				{
					$oneCoupon['CHECK_CODE_TEXT'] = [$checkCode[self::COUPON_CHECK_NOT_APPLIED]];
				}
			}
			else
			{
				$oneCoupon['CHECK_CODE_TEXT'] = [];
				foreach ($checkCode as $code => $text)
				{
					if ($code == self::COUPON_CHECK_OK)
					{
						continue;
					}
					if (($oneCoupon['CHECK_CODE'] & $code) == $code)
					{
						$oneCoupon['CHECK_CODE_TEXT'][] = $checkCode[$code];
					}
				}
			}
		}
		unset($oneCoupon);
	}

	/**
	 * Set coupons list.
	 *
	 * @param array $couponsList			Coupons list.
	 * @param bool $checkCoupons			Find coupons.
	 * @return void
	 */
	protected static function setCoupons($couponsList, $checkCoupons = true)
	{
		if (empty($couponsList) || !is_array($couponsList))
		{
			return;
		}

		$checkCoupons = ($checkCoupons !== false);
		if ($checkCoupons)
		{
			foreach ($couponsList as $coupon)
			{
				$coupon = trim((string)$coupon);
				if ($coupon === '')
				{
					continue;
				}
				$couponData = self::getData($coupon);
				if (!isset(self::$coupons[$couponData['COUPON']]))
				{
					$couponData['SORT'] = self::$couponIndex;
					self::createApplyFields($couponData);
					self::$coupons[$couponData['COUPON']] = $couponData;
					self::$couponIndex++;
				}
			}
			unset($couponData, $coupon);
		}
		else
		{
			$currentTime = new Main\Type\DateTime();
			$currentTimestamp = $currentTime->getTimestamp();
			unset($currentTime);
			foreach ($couponsList as $coupon)
			{
				if (empty($coupon) || !is_array($coupon))
				{
					continue;
				}
				$checkCode = self::checkBaseData($coupon, self::COUPON_CHECK_OK);
				$checkCode = self::checkFullData($coupon, $coupon['MODE'], $checkCode, $currentTimestamp);
				$coupon['STATUS'] = ($checkCode == self::COUPON_CHECK_OK ? self::STATUS_ENTERED : self::STATUS_FREEZE);
				$coupon['CHECK_CODE'] = $checkCode;
				unset($checkCode);
				if (!isset(self::$coupons[$coupon['COUPON']]))
				{
					$coupon['SORT'] = self::$couponIndex;
					self::createApplyFields($coupon);
					self::$coupons[$coupon['COUPON']] = $coupon;
					self::$couponIndex++;
				}
			}
			unset($coupon, $currentTimestamp);
		}
	}

	/**
	 * Clear order saved coupons.
	 *
	 * @internal
	 * @param array $coupon		Coupon data.
	 * @return bool
	 */
	protected static function clearSavedCoupons($coupon)
	{
		return (!isset($coupon['SAVED']) || $coupon['SAVED'] !== 'Y');
	}

	/**
	 * Clear system data.
	 *
	 * @internal
	 * @param array &$coupons			Coupons.
	 * @return void
	 */
	protected static function clearSystemData(&$coupons)
	{
		$result = [];
		foreach ($coupons as $couponIndex => $couponData)
		{
			if (array_key_exists('SYSTEM_DATA', $couponData))
			{
				unset($couponData['SYSTEM_DATA']);
			}
			$result[$couponIndex] = $couponData;
		}
		unset($couponIndex, $couponData);
		$coupons = $result;
	}

	/**
	 * Convert old filter fields.
	 *
	 * @internal
	 * @param array &$filter		Coupons filter.
	 * @return void
	 */
	protected static function convertOldFilterFields(array &$filter)
	{
		if (array_key_exists('MODULE', $filter))
		{
			if (!isset($filter['MODULE_ID']))
			{
				$filter['MODULE_ID'] = $filter['MODULE'];
			}
			unset($filter['MODULE']);
		}
		if (array_key_exists('!MODULE', $filter))
		{
			if (!isset($filter['!MODULE_ID']))
			{
				$filter['!MODULE_ID'] = $filter['!MODULE'];
			}
			unset($filter['!MODULE']);
		}
	}

	/**
	 * Returns empty coupon (default field values).
	 *
	 * @internal
	 * @param string $coupon		Coupon code.
	 * @return array
	 */
	protected static function getEmptyCouponFields($coupon)
	{
		/* field MODULE - unused, for compatibility only */
		return [
			'COUPON' => $coupon,
			'MODE' => self::COUPON_MODE_SIMPLE,
			'STATUS' => self::STATUS_NOT_FOUND,
			'CHECK_CODE' => self::COUPON_CHECK_NOT_FOUND,
			'MODULE' => '',
			'MODULE_ID' => '',
			'ID' => 0,
			'DISCOUNT_ID' => 0,
			'DISCOUNT_NAME' => '',
			'TYPE' => Internals\DiscountCouponTable::TYPE_UNKNOWN,
			'ACTIVE' => '',
			'USER_INFO' => [],
			'SAVED' => 'N',
		];
	}

	private static function getCurrentUserId(): ?int
	{
		global $USER;

		if (!(
			isset($USER)
			&& $USER instanceof \CUser
		))
		{
			return null;
		}

		$userId = (int)$USER->GetID();

		return $userId > 0 ? $userId : null;
	}

	protected static function needLockCoupon(string $coupon): bool
	{
		$type = self::$coupons[$coupon]['TYPE'] ?? Internals\DiscountCouponTable::TYPE_UNKNOWN;

		return (
			$type === Internals\DiscountCouponTable::TYPE_BASKET_ROW
			|| $type === Internals\DiscountCouponTable::TYPE_ONE_ORDER
		);
	}
}
