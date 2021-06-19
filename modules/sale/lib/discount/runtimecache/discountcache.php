<?php

namespace Bitrix\Sale\Discount\RuntimeCache;


use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\DiscountCouponTable;
use Bitrix\Sale\Internals\DiscountEntitiesTable;
use Bitrix\Sale\Internals\DiscountGroupTable;
use Bitrix\Sale\Internals\DiscountModuleTable;
use Bitrix\Sale\Internals\DiscountTable;

final class DiscountCache
{
	/** @var DiscountCache */
	private static $instance;
	private $discounts = array();
	private $discountIds = array();
	private $discountModules = array();
	private $discountEntities = array();

	private function __construct()
	{}

	private function __clone()
	{}

	/**
	 * Returns Singleton of DiscountCache.
	 * @return DiscountCache
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	public function getDiscountIds(array $userGroups)
	{
		Collection::normalizeArrayValuesByInt($userGroups);
		$cacheKey = md5('G' . implode('|', $userGroups));

		if(!isset($this->discountIds[$cacheKey]))
		{
			$this->discountIds[$cacheKey] = array();

			$groupDiscountIterator = DiscountGroupTable::getList(array(
				'select' => array('DISCOUNT_ID'),
				'filter' => array(
					'@GROUP_ID' => $userGroups,
					'=ACTIVE' => 'Y',
				),
				'order' => array('DISCOUNT_ID' => 'ASC')
			));
			while ($groupDiscount = $groupDiscountIterator->fetch())
			{
				$groupDiscount['DISCOUNT_ID'] = (int)$groupDiscount['DISCOUNT_ID'];
				if ($groupDiscount['DISCOUNT_ID'] > 0)
				{
					$this->discountIds[$cacheKey][$groupDiscount['DISCOUNT_ID']] = $groupDiscount['DISCOUNT_ID'];
				}
			}

		}

		return $this->discountIds[$cacheKey];
	}

	public function getDiscountModules(array $discountIds)
	{
		if(empty($discountIds))
		{
			return array();
		}

		Collection::normalizeArrayValuesByInt($discountIds);

		$discountIds = array_combine($discountIds, $discountIds);
		$needToLoad = array_diff($discountIds, array_keys($this->discountModules));

		foreach(DiscountModuleTable::getByDiscount($needToLoad) as $discountId => $modules)
		{
			$this->discountModules[$discountId] = $modules;
		}

		return array_intersect_key($this->discountModules, $discountIds);
	}

	public function getDiscountEntities(array $discountIds)
	{
		if(empty($discountIds))
		{
			return array();
		}
		Collection::normalizeArrayValuesByInt($discountIds);
		if(empty($discountIds))
		{
			return array();
		}

		$cacheKey = 'D' . implode('_', $discountIds);
		if (!isset($this->discountEntities[$cacheKey]))
		{
			$needToLoad = $discountIds;
			$resultEntities = array();

			foreach ($this->discountEntities as $data)
			{
				list($loadedDiscountIds, $entities) = $data;

				$needToLoadPortion = array_diff($needToLoad, $loadedDiscountIds);

				if (count($needToLoad) > count($needToLoadPortion))
				{
					$resultEntities = array_merge($resultEntities, $entities);
				}

				$needToLoad = $needToLoadPortion;
				if (empty($needToLoad))
				{
					break;
				}
			}

			if (!empty($needToLoad))
				self::recursiveMerge($resultEntities, DiscountEntitiesTable::getByDiscount($needToLoad));

			$this->discountEntities[$cacheKey] = array($discountIds, $resultEntities);
		}
		return $this->discountEntities[$cacheKey][1];
	}

	public function getDiscounts(array $discountIds, array $executeModuleFilter, $siteId, array $couponList = array())
	{
		$cacheKey = 'D'.implode('_', $discountIds).'-S'.$siteId;
		if(!empty($couponList))
		{
			$cacheKey .= '-C'. implode('_', array_keys($couponList));
		}

		$cacheKey .= '-MF'.implode('_', $executeModuleFilter);
		$cacheKey = md5($cacheKey);

		if(!isset($this->discounts[$cacheKey]))
		{
			$currentList = array();

			\CTimeZone::Disable();
			$currentDatetime = new DateTime();
			$discountSelect = array(
				'ID', 'PRIORITY', 'SORT', 'LAST_DISCOUNT', 'LAST_LEVEL_DISCOUNT', 'UNPACK', 'APPLICATION', 'USE_COUPONS', 'EXECUTE_MODULE',
				'NAME', 'CONDITIONS_LIST', 'ACTIONS_LIST', 'ACTIVE_FROM', 'ACTIVE_TO', 'PREDICTIONS_LIST', 'PREDICTIONS_APP',
				'PREDICTION_TEXT', 'PRESET_ID', 'CURRENCY', 'LID', 'SHORT_DESCRIPTION', 'SHORT_DESCRIPTION_STRUCTURE',
			);
			$discountFilter = array(
				'@ID' => $discountIds,
				'=LID' => $siteId,
				'@EXECUTE_MODULE' => $executeModuleFilter,
				array(
					'LOGIC' => 'OR',
					'=ACTIVE_FROM' => null,
					'<=ACTIVE_FROM' => $currentDatetime
				),
				array(
					'LOGIC' => 'OR',
					'=ACTIVE_TO' => null,
					'>=ACTIVE_TO' => $currentDatetime
				)
			);

			$couponsDiscount = array();
			if (!empty($couponList))
			{
				$iterator = DiscountCouponTable::getList(array(
					'select' => array('DISCOUNT_ID', 'COUPON'),
					'filter' => array('@DISCOUNT_ID' => $discountIds,'@COUPON' => array_keys($couponList)),
					'order' => array('DISCOUNT_ID' => 'ASC')
				));
				while ($row = $iterator->fetch())
				{
					$id = (int)$row['DISCOUNT_ID'];
					if (isset($couponsDiscount[$id]))
						continue;
					$couponsDiscount[$id] = $row['COUPON'];
				}
				unset($id, $row, $iterator);
			}

			if (empty($couponsDiscount))
			{
				$discountFilter['=USE_COUPONS'] = 'N';
			}
			else
			{
				$discountFilter[] = array(
					'LOGIC' => 'OR',
					'=USE_COUPONS' => 'N',
					array(
						'=USE_COUPONS' => 'Y',
						'@ID' => array_keys($couponsDiscount)
					)
				);
			}

			//todo remove order. It's unnecessary because we rearrange discounts after by benefit for client.
			$discountIterator = DiscountTable::getList(array(
				'select' => $discountSelect,
				'filter' => $discountFilter,
				'order' => array('PRIORITY' => 'DESC', 'SORT' => 'ASC', 'ID' => 'ASC')
			));

			while($discount = $discountIterator->fetch())
			{
				$discount['ID'] = (int)$discount['ID'];
				if ($discount['USE_COUPONS'] == 'Y')
					$discount['COUPON'] = $couponList[$couponsDiscount[$discount['ID']]];
				$discount['CONDITIONS'] = $discount['CONDITIONS_LIST'];
				$discount['ACTIONS'] = $discount['ACTIONS_LIST'];
				$discount['PREDICTIONS'] = $discount['PREDICTIONS_LIST'];
				$discount['MODULE_ID'] = 'sale';
				$discount['MODULES'] = array();
				unset($discount['ACTIONS_LIST'], $discount['CONDITIONS_LIST'], $discount['PREDICTIONS_LIST']);
				$currentList[$discount['ID']] = $discount;
			}
			unset($discount, $discountIterator);

			\CTimeZone::Enable();

			if (!empty($currentList))
			{
				$discountModules = static::getDiscountModules(array_keys($currentList));
				foreach ($discountModules as $id => $modules)
					$currentList[$id]['MODULES'] = $modules;
				unset($id, $modules, $discountModules);
			}

			$this->discounts[$cacheKey] = $currentList;
		}

		return $this->discounts[$cacheKey];
	}

	/**
	 * Added keys from source array to destination array.
	 *
	 * @param array &$dest			Destination array.
	 * @param array $src			Source array.
	 * @return void
	 */
	private static function recursiveMerge(&$dest, $src)
	{
		if (!is_array($dest) || !is_array($src))
			return;
		if (empty($dest))
		{
			$dest = $src;
			return;
		}
		foreach ($src as $key => $value)
		{
			if (!isset($dest[$key]))
			{
				$dest[$key] = $value;
				continue;
			}
			if (is_array($dest[$key]))
				self::recursiveMerge($dest[$key], $value);
		}
		unset($value, $key);
	}
}