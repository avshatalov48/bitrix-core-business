<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Main\Application;

class CAllCatalogDiscountCoupon
{
	public const TYPE_ONE_TIME = 'Y';
	public const TYPE_ONE_ORDER = 'O';
	public const TYPE_NO_LIMIT = 'N';

	protected static $arOneOrderCoupons = array();
	protected static $existCouponsManager = null;

	/**
	* @deprecated deprecated since catalog 15.0.7
	* @see \Bitrix\Catalog\DiscountCouponTable::getCouponTypes
	*
	* @param bool $boolFull			Get full description.
	* @return array
	*/
	public static function GetCoupontTypes($boolFull = false)
	{
		return Catalog\DiscountCouponTable::getCouponTypes($boolFull);
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $APPLICATION, $USER;

		$ACTION = mb_strtoupper($ACTION);
		if ('UPDATE' != $ACTION && 'ADD' != $ACTION)
			return false;

		if (self::$existCouponsManager === null)
			self::initCouponManager();

		$clearFields = array(
			'ID',
			'~ID',
			'~COUPON',
			'TIMESTAMP_X',
			'DATE_CREATE',
			'~DATE_CREATE',
			'~MODIFIED_BY',
			'~CREATED_BY'
		);
		if ($ACTION =='UPDATE')
			$clearFields[] = 'CREATED_BY';

		foreach ($clearFields as &$fieldName)
		{
			if (array_key_exists($fieldName, $arFields))
				unset($arFields[$fieldName]);
		}
		unset($fieldName, $clearFields);

		if ((is_set($arFields, "DISCOUNT_ID") || $ACTION=="ADD") && intval($arFields["DISCOUNT_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("KGDC_EMPTY_DISCOUNT"), "EMPTY_DISCOUNT_ID");
			return false;
		}

		if ((is_set($arFields, "COUPON") || $ACTION=="ADD") && $arFields["COUPON"] == '')
		{
			$APPLICATION->ThrowException(Loc::getMessage("KGDC_EMPTY_COUPON"), "EMPTY_COUPON");
			return false;
		}
		elseif(is_set($arFields, "COUPON"))
		{
			$currentId = ($ACTION == 'UPDATE' ? $ID : 0);
			$arFields['COUPON'] = mb_substr($arFields['COUPON'], 0, 32);
			if (self::$existCouponsManager)
			{
				$existCoupon = DiscountCouponsManager::isExist($arFields['COUPON']);
				if (!empty($existCoupon))
				{
					if ($existCoupon['MODULE'] != 'catalog' || $currentId != $existCoupon['ID'])
					{
						$APPLICATION->ThrowException(Loc::getMessage("KGDC_DUPLICATE_COUPON"), "DUPLICATE_COUPON");
						return false;
					}
				}
			}
			else
			{
				$couponIterator = Catalog\DiscountCouponTable::getList(array(
					'select' => array('ID', 'COUPON'),
					'filter' => array('=COUPON' => $arFields['COUPON'])
				));
				if ($existCoupon = $couponIterator->fetch())
				{
					if ($currentId != (int)$existCoupon['ID'])
					{
						$APPLICATION->ThrowException(Loc::getMessage("KGDC_DUPLICATE_COUPON"), "DUPLICATE_COUPON");
						return false;
					}
				}
			}
		}

		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"] != "N")
			$arFields["ACTIVE"] = "Y";
		if ((is_set($arFields, "ONE_TIME") || $ACTION=="ADD") && !in_array($arFields["ONE_TIME"], Catalog\DiscountCouponTable::getCouponTypes()))
			$arFields["ONE_TIME"] = self::TYPE_ONE_TIME;

		if ((is_set($arFields, "DATE_APPLY") || $ACTION=="ADD") && (!$DB->IsDate($arFields["DATE_APPLY"], false, SITE_ID, "FULL")))
			$arFields["DATE_APPLY"] = false;

		$intUserID = 0;
		$boolUserExist = CCatalog::IsUserExists();
		if ($boolUserExist)
			$intUserID = (int)$USER->GetID();
		$strDateFunction = $DB->GetNowFunction();
		$arFields['~TIMESTAMP_X'] = $strDateFunction;
		if ($boolUserExist)
		{
			if (!array_key_exists('MODIFIED_BY', $arFields) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = $intUserID;
		}
		if ('ADD' == $ACTION)
		{
			$arFields['~DATE_CREATE'] = $strDateFunction;
			if ($boolUserExist)
			{
				if (!array_key_exists('CREATED_BY', $arFields) || intval($arFields["CREATED_BY"]) <= 0)
					$arFields["CREATED_BY"] = $intUserID;
			}
		}

		return true;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.7
	 * @see \Bitrix\Catalog\DiscountCouponTable::deleteByDiscount()
	 *
	 * @param int $ID
	 * @param bool $bAffectDataFile
	 * @return bool
	 */
	public static function DeleteByDiscountID($ID, $bAffectDataFile = true)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		Catalog\DiscountCouponTable::deleteByDiscount($ID);
		return true;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::add
	*
	* @param string $coupon			Coupon code.
	* @return bool
	*/
	public static function SetCoupon($coupon)
	{
		if (self::$existCouponsManager === null)
			self::initCouponManager();

		if (self::$existCouponsManager)
		{
			if (DiscountCouponsManager::usedByClient())
			{
				return DiscountCouponsManager::add($coupon);
			}
			return false;
		}
		else
		{
			$coupon = trim((string)$coupon);
			if ($coupon === '')
				return false;

			$session = Application::getInstance()->getSession();
			if (!$session->isAccessible())
			{
				return false;
			}

			if (!isset($session['CATALOG_USER_COUPONS']) || !is_array($session['CATALOG_USER_COUPONS']))
				$session['CATALOG_USER_COUPONS'] = array();

			$couponIterator = Catalog\DiscountCouponTable::getList(array(
				'select' => array('ID', 'COUPON'),
				'filter' => array('=COUPON' => $coupon, '=ACTIVE' => 'Y')
			));
			if ($existCoupon = $couponIterator->fetch())
			{
				if (!in_array($existCoupon['COUPON'], $session['CATALOG_USER_COUPONS']))
					$session['CATALOG_USER_COUPONS'][] = $existCoupon['COUPON'];
				return true;
			}
		}
		return false;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::get
	*/
	public static function GetCoupons()
	{
		if (self::$existCouponsManager === null)
			self::initCouponManager();

		if (self::$existCouponsManager)
		{
			if (DiscountCouponsManager::usedByClient())
			{
				return DiscountCouponsManager::get(false, array('MODULE' => 'catalog'), true);
			}
			return array();
		}
		else
		{
			$session = Application::getInstance()->getSession();
			if (!$session->isAccessible())
			{
				return [];
			}

			if (!isset($session['CATALOG_USER_COUPONS']) || !is_array($session['CATALOG_USER_COUPONS']))
				$session['CATALOG_USER_COUPONS'] = array();
			return $session["CATALOG_USER_COUPONS"];
		}
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::delete
	*
	* @param string $strCoupon			Coupon code.
	* @return bool
	*/
	public static function EraseCoupon($strCoupon)
	{
		if (self::$existCouponsManager === null)
			self::initCouponManager();
		if (self::$existCouponsManager)
		{
			if (DiscountCouponsManager::usedByClient())
			{
				return DiscountCouponsManager::delete($strCoupon);
			}
			return false;
		}
		else
		{
			$strCoupon = trim((string)$strCoupon);
			if (empty($strCoupon))
				return false;

			$session = Application::getInstance()->getSession();
			if (!$session->isAccessible())
			{
				return false;
			}

			if (!isset($session['CATALOG_USER_COUPONS']) || !is_array($session['CATALOG_USER_COUPONS']))
			{
				$session['CATALOG_USER_COUPONS'] = array();
				return true;
			}
			$key = array_search($strCoupon, $session['CATALOG_USER_COUPONS']);
			if ($key !== false)
			{
				unset($session['CATALOG_USER_COUPONS'][$key]);
			}
			return true;
		}
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::clear
	*/
	public static function ClearCoupon()
	{
		if (self::$existCouponsManager === null)
			self::initCouponManager();

		if (self::$existCouponsManager)
		{
			if (DiscountCouponsManager::usedByClient())
				DiscountCouponsManager::clear(true);
		}
		else
		{
			$session = Application::getInstance()->getSession();
			if ($session->isAccessible())
			{
				$session['CATALOG_USER_COUPONS'] = array();
			}
		}
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::add
	*
	* @param int $intUserID				User id.
	* @param string $strCoupon			Coupon code.
	* @return bool
	*/
	public static function SetCouponByManage($intUserID, $strCoupon)
	{
		$intUserID = (int)$intUserID;
		if ($intUserID >= 0)
		{
			if (self::$existCouponsManager === null)
				self::initCouponManager();
			if (self::$existCouponsManager)
			{
				if (DiscountCouponsManager::usedByManager() && DiscountCouponsManager::getUserId() == $intUserID)
				{
					return DiscountCouponsManager::add($strCoupon);
				}
				return false;
			}
			else
			{
				$strCoupon = trim((string)$strCoupon);
				if (empty($strCoupon))
					return false;

				$session = Application::getInstance()->getSession();
				if (!$session->isAccessible())
				{
					return false;
				}

				if (!isset($session['CATALOG_MANAGE_COUPONS']) || !is_array($session['CATALOG_MANAGE_COUPONS']))
					$session['CATALOG_MANAGE_COUPONS'] = array();
				if (!isset($session['CATALOG_MANAGE_COUPONS'][$intUserID]) || !is_array($session['CATALOG_MANAGE_COUPONS'][$intUserID]))
					$session['CATALOG_MANAGE_COUPONS'][$intUserID] = array();

				$couponIterator = Catalog\DiscountCouponTable::getList(array(
					'select' => array('ID', 'COUPON'),
					'filter' => array('=COUPON' => $strCoupon, '=ACTIVE' => 'Y')
				));
				if ($existCoupon = $couponIterator->fetch())
				{
					if (!in_array($existCoupon['COUPON'], $session['CATALOG_MANAGE_COUPONS'][$intUserID]))
						$session['CATALOG_MANAGE_COUPONS'][$intUserID][] = $existCoupon['COUPON'];

					return true;
				}
			}
		}
		return false;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::get
	*
	* @param int $intUserID			User id.
	* @return bool|array
	*/
	public static function GetCouponsByManage($intUserID)
	{
		$intUserID = (int)$intUserID;
		if ($intUserID >= 0)
		{
			if (self::$existCouponsManager === null)
				self::initCouponManager();
			if (self::$existCouponsManager)
			{
				if (DiscountCouponsManager::usedByManager() && DiscountCouponsManager::getUserId() == $intUserID)
				{
					return DiscountCouponsManager::get(false, array('MODULE' => 'catalog'), true);
				}
				return false;
			}
			else
			{
				$session = Application::getInstance()->getSession();
				if (!$session->isAccessible())
				{
					return false;
				}

				if (!isset($session['CATALOG_MANAGE_COUPONS']) || !is_array($session['CATALOG_MANAGE_COUPONS']))
					$session['CATALOG_MANAGE_COUPONS'] = array();
				if (!isset($session['CATALOG_MANAGE_COUPONS'][$intUserID]) || !is_array($session['CATALOG_MANAGE_COUPONS'][$intUserID]))
					$session['CATALOG_MANAGE_COUPONS'][$intUserID] = array();

				return $session['CATALOG_MANAGE_COUPONS'][$intUserID];
			}
		}
		return false;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::delete
	*
	* @param int $intUserID				User id.
	* @param string $strCoupon			Coupon code.
	* @return bool
	*/
	public static function EraseCouponByManage($intUserID, $strCoupon)
	{
		$intUserID = (int)$intUserID;
		if ($intUserID >= 0)
		{
			if (self::$existCouponsManager === null)
				self::initCouponManager();
			if (self::$existCouponsManager)
			{
				if (DiscountCouponsManager::usedByManager() && DiscountCouponsManager::getUserId() == $intUserID)
				{
					return DiscountCouponsManager::delete($strCoupon);
				}
				return false;
			}
			else
			{
				$session = Application::getInstance()->getSession();
				if (!$session->isAccessible())
				{
					return false;
				}

				$strCoupon = trim((string)$strCoupon);
				if (empty($strCoupon))
					return false;
				if (!isset($session['CATALOG_MANAGE_COUPONS']) || !is_array($session['CATALOG_MANAGE_COUPONS']))
					return false;
				if (!isset($session['CATALOG_MANAGE_COUPONS'][$intUserID]) || !is_array($session['CATALOG_MANAGE_COUPONS'][$intUserID]))
					return false;
				$key = array_search($strCoupon, $session['CATALOG_MANAGE_COUPONS'][$intUserID]);
				if ($key !== false)
				{
					unset($session['CATALOG_MANAGE_COUPONS'][$intUserID][$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::clear
	*
	* @param int $intUserID				User id.
	* @return bool
	*/
	public static function ClearCouponsByManage($intUserID)
	{
		$intUserID = (int)$intUserID;
		if ($intUserID >= 0)
		{
			if (self::$existCouponsManager === null)
				self::initCouponManager();
			if (self::$existCouponsManager)
			{
				if (DiscountCouponsManager::usedByManager() && DiscountCouponsManager::getUserId() == $intUserID)
				{
					return DiscountCouponsManager::clear(true);
				}
				return false;
			}
			else
			{
				$session = Application::getInstance()->getSession();
				if (!$session->isAccessible())
				{
					return false;
				}

				if (!isset($session['CATALOG_MANAGE_COUPONS']) || !is_array($session['CATALOG_MANAGE_COUPONS']))
					$session['CATALOG_MANAGE_COUPONS'] = array();
				$session['CATALOG_MANAGE_COUPONS'][$intUserID] = array();
				return true;
			}
		}
		return false;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager
	*
	* @param int $intUserID				User id.
	* @param array $arCoupons			Coupon code list.
	* @param array $arModules			Modules list.
	* @return bool
	*/
	public static function OnSetCouponList($intUserID, $arCoupons, $arModules)
	{
		global $USER;
		$boolResult = false;
		if (
			empty($arModules)
			|| (is_array($arModules) && in_array('catalog', $arModules))
		)
		{
			if (!empty($arCoupons))
			{
				if (!is_array($arCoupons))
					$arCoupons = array($arCoupons);
				$intUserID = (int)$intUserID;

				if (self::$existCouponsManager === null)
					self::initCouponManager();
				if (self::$existCouponsManager)
				{
					if ($intUserID == DiscountCouponsManager::getUserId())
					{
						foreach ($arCoupons as &$coupon)
						{
							if (DiscountCouponsManager::add($coupon))
								$boolResult = true;
						}
						unset($coupon);
						return $boolResult;
					}
					return false;
				}
				else
				{
					if ($intUserID > 0)
					{
						$boolCurrentUser = ($USER->IsAuthorized() && $intUserID == $USER->GetID());
						foreach ($arCoupons as &$strOneCoupon)
						{
							if (self::SetCouponByManage($intUserID, $strOneCoupon))
								$boolResult = true;
							if ($boolCurrentUser)
								self::SetCoupon($strOneCoupon);
						}
						unset($strOneCoupon);
					}
					elseif (0 == $intUserID && !$USER->IsAuthorized())
					{
						foreach ($arCoupons as &$strOneCoupon)
						{
							$couponResult = self::SetCoupon($strOneCoupon);
							if ($couponResult)
								$boolResult = true;
						}
						unset($strOneCoupon);
					}
				}
			}
		}

		return $boolResult;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager
	*
	* @param int $intUserID				User id.
	* @param array $arCoupons			Coupon code list.
	* @param array $arModules			Modules list.
	* @return bool
	*/
	public static function OnClearCouponList($intUserID, $arCoupons, $arModules)
	{
		global $USER;

		$boolResult = false;
		if (
			empty($arModules)
			|| (is_array($arModules) && in_array('catalog', $arModules))
		)
		{
			if (!empty($arCoupons))
			{
				if (!is_array($arCoupons))
					$arCoupons = array($arCoupons);
				$intUserID = (int)$intUserID;

				if (self::$existCouponsManager === null)
					self::initCouponManager();
				if (self::$existCouponsManager)
				{
					if ($intUserID == DiscountCouponsManager::getUserId())
					{
						foreach ($arCoupons as &$coupon)
						{
							if (DiscountCouponsManager::delete($coupon))
								$boolResult = true;
						}
						unset($coupon);
						return $boolResult;
					}
					return false;
				}
				else
				{
					if ($intUserID > 0)
					{
						$boolCurrentUser = ($USER->IsAuthorized() && $intUserID == $USER->GetID());
						foreach ($arCoupons as &$strOneCoupon)
						{
							if (self::EraseCouponByManage($intUserID, $strOneCoupon))
								$boolResult = true;
							if ($boolCurrentUser)
								self::EraseCoupon($strOneCoupon);
						}
						unset($strOneCoupon);
					}
					elseif (0 == $intUserID && !$USER->IsAuthorized())
					{
						foreach ($arCoupons as &$strOneCoupon)
						{
							if (self::EraseCoupon($strOneCoupon))
								$boolResult = true;
						}
						unset($strOneCoupon);
					}
				}
			}
		}
		return $boolResult;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager
	* @param int $intUserID				User id.
	* @param array $arModules			Modules list.
	* @return bool
	*/
	public static function OnDeleteCouponList($intUserID, $arModules)
	{
		global $USER;

		$boolResult = false;
		if (
			empty($arModules)
			|| (is_array($arModules) && in_array('catalog', $arModules))
		)
		{
			$intUserID = (int)$intUserID;
			if (self::$existCouponsManager === null)
				self::initCouponManager();
			if (self::$existCouponsManager)
			{
				if ($intUserID == DiscountCouponsManager::getUserId())
				{
					return DiscountCouponsManager::clear(true);
				}
				return false;
			}
			else
			{
				if (0 < $intUserID)
				{
					$boolCurrentUser = ($USER->IsAuthorized() && $intUserID == $USER->GetID());
					$boolResult = self::ClearCouponsByManage($intUserID);
					if ($boolCurrentUser)
						self::ClearCoupon();
				}
				elseif (0 == $intUserID && !$USER->IsAuthorized())
				{
					self::ClearCoupon();
				}
			}
		}
		return $boolResult;
	}

	/**
	* @deprecated deprecated since catalog 15.0.4
	* @see \Bitrix\Sale\DiscountCouponsManager::isExist
	*
	* @param string $strCoupon			Coupon code.
	* @return bool
	*/
	public static function IsExistCoupon($strCoupon)
	{
		return false;
	}

	protected static function initCouponManager()
	{
		if (self::$existCouponsManager === null)
			self::$existCouponsManager = Main\ModuleManager::isModuleInstalled('sale') && Main\Loader::includeModule('sale');
	}
}
