<?php
namespace Bitrix\Sale\Discount\Migration;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

final class OrderDiscountMigrator
{
	const ERROR_ID = 'BX_SALE_ORDER_DISCOUNT_MIGRATOR';

	private static $catalogIncluded = null;
	private static $migrateDiscountsCache = array();
	private static $migrateCouponsCache = array();
	private static $catalogDiscountsCache = array();

	/**
	 * Migrate discount data from b_sale_basket into new entity.
	 *
	 * @param array $order				Order data.
	 * @return Sale\Result
	 */
	public static function processing(array $order)
	{
		static $useBasePrice = null;
		if ($useBasePrice === null)
			$useBasePrice = (string)Main\Config\Option::get('sale', 'get_discount_percent_from_base_price');

		$process = true;
		$result = new Sale\Result();

		if (empty($order['ID']) || (int)$order['ID'] <= 0)
		{
			$process = false;
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_ERR_EMPTY_ORDER_ID'),
				self::ERROR_ID
			));
		}

		$catalogOrder = false;
		$basketData = array();
		if ($process)
		{
			$order['ID'] = (int)$order['ID'];
			$basePrices = array();

			$basketIterator = Sale\Internals\BasketTable::getList(array(
				'select' => array(
					'ID', 'DISCOUNT_COUPON', 'DISCOUNT_NAME', 'DISCOUNT_VALUE',
					'MODULE', 'PRICE', 'DISCOUNT_PRICE', 'CURRENCY', 'SET_PARENT_ID', 'TYPE'
				),
				'filter' => array('=ORDER_ID' => $order['ID'])
			));
			while ($basket = $basketIterator->fetch())
			{
				$basket['ID'] = (int)$basket['ID'];
				$basket['MODULE'] = (string)$basket['MODULE'];
				$basket['DISCOUNT_COUPON'] = trim((string)$basket['DISCOUNT_COUPON']);
				$basket['DISCOUNT_NAME'] = trim((string)$basket['DISCOUNT_NAME']);
				$basket['SET_PARENT_ID'] = (int)$basket['SET_PARENT_ID'];
				$basket['TYPE'] = (int)$basket['TYPE'];
				if ($basket['MODULE'] == 'catalog')
				{
					$basePrices[$basket['ID']] = array(
						'BASE_PRICE' => $basket['PRICE'] + $basket['DISCOUNT_PRICE'],
						'BASE_PRICE_CURRENCY' => $basket['CURRENCY']
					);
				}

				if ($basket['MODULE'] != 'catalog' || ($basket['DISCOUNT_NAME'] == '' && $basket['DISCOUNT_COUPON'] == ''))
					continue;
				if ($basket['SET_PARENT_ID'] > 0 && $basket['TYPE'] <= 0)
					continue;

				$catalogOrder = true;
				$hash = md5($basket['DISCOUNT_NAME'].'|'.$basket['DISCOUNT_COUPON']);
				if (!isset($basketData[$hash]))
					$basketData[$hash] = array(
						'DISCOUNT_NAME' => $basket['DISCOUNT_NAME'],
						'DISCOUNT_COUPON' => $basket['DISCOUNT_COUPON'],
						'ITEMS' => array()
					);
				$basketData[$hash]['ITEMS'][$basket['ID']] = $basket;
			}
			unset($basket, $basketIterator);
		}

		if ($process && $catalogOrder)
		{
			Sale\OrderDiscount::setManagerConfig(array(
				'CURRENCY' => $order['CURRENCY'],
				'SITE_ID' => $order['LID'],
				'USE_BASE_PRICE' => $useBasePrice
			));
			foreach ($basketData as $row)
			{
				if (!self::migrateDiscount($order['ID'], $row))
				{
					$process = false;
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_ERR_SAVE_MIGRATE_DISCOUNT'),
						self::ERROR_ID
					));
					break;
				}
			}
			unset($row);
		}
		unset($basketData);

		Sale\Internals\OrderDiscountDataTable::clearByOrder($order['ID']);
		if ($process)
		{
			if (!empty($basePrices))
			{
				foreach ($basePrices as $basketId => $price)
				{
					$fields = array(
						'ORDER_ID' => $order['ID'],
						'ENTITY_TYPE' => Sale\Internals\OrderDiscountDataTable::ENTITY_TYPE_BASKET_ITEM,
						'ENTITY_ID' => $basketId,
						'ENTITY_VALUE' => $basketId,
						'ENTITY_DATA' => $price,
					);
					$operationResult = Sale\Internals\OrderDiscountDataTable::add($fields);
					if (!$operationResult->isSuccess())
					{
						$process = false;
						$result->addErrors($operationResult->getErrors());
					}
					unset($operationResult);
				}
				unset($basketId, $price);
			}
		}

		if ($process)
		{
			$fields = array(
				'ORDER_ID' => $order['ID'],
				'ENTITY_TYPE' => Sale\Internals\OrderDiscountDataTable::ENTITY_TYPE_ORDER,
				'ENTITY_ID' => $order['ID'],
				'ENTITY_VALUE' => $order['ID'],
				'ENTITY_DATA' => array(
					'OLD_ORDER' => 'Y'
				)
			);
			$operationResult = Sale\Internals\OrderDiscountDataTable::add($fields);
			if (!$operationResult->isSuccess())
			{
				$process = false;
				$result->addErrors($operationResult->getErrors());
			}
			unset($operationResult);
		}
		unset($process);

		return $result;
	}

	/**
	 * Convert discount for old order.
	 *
	 * @internal
	 * @param int $orderId				Order id.
	 * @param array &$data				Discount data.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	private static function migrateDiscount($orderId, array &$data)
	{
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Main\Loader::includeModule('catalog');
		if (!self::$catalogIncluded)
			return false;

		$discountData = array(
			'COUPON' => '',
			'NAME' => '',
			'DISCOUNT_ID' => 0
		);
		if ($data['DISCOUNT_NAME'] != '')
		{
			$discountName = array();
			if (preg_match('/^\[(\d+)\][ ](.+)$/', $data['DISCOUNT_NAME'], $discountName) == 1)
			{
				$discountData['NAME'] = $discountName[2];
				$discountData['DISCOUNT_ID'] = $discountName[1];
			}
			unset($discountName);
		}
		if ($data['DISCOUNT_COUPON'] != '')
		{
			$discountData['COUPON'] = $data['DISCOUNT_COUPON'];
			if (!self::checkMigrateCoupon($discountData['COUPON']))
				return false;

			if ($discountData['DISCOUNT_ID'] == 0)
			{
				$discountData['NAME'] = self::$migrateCouponsCache[$discountData['COUPON']]['DISCOUNT_NAME'];
				$discountData['DISCOUNT_ID'] = self::$migrateCouponsCache[$discountData['COUPON']]['DISCOUNT_ID'];
			}
			else
			{
				if (
					self::$migrateCouponsCache[$discountData['COUPON']]['TYPE'] != Sale\Internals\DiscountCouponTable::TYPE_ARCHIVED
					&& self::$migrateCouponsCache[$discountData['COUPON']]['DISCOUNT_ID'] >= 0
					&& $discountData['DISCOUNT_ID'] != self::$migrateCouponsCache[$discountData['COUPON']]['DISCOUNT_ID']
				)
					$discountData['DISCOUNT_ID'] = 0;
			}
		}
		if ($discountData['DISCOUNT_ID'] == 0)
		{
			if ($discountData['COUPON'] == '')
				return false;
			self::createEmptyDiscount($discountData);
		}
		else
		{
			self::checkMigrateDiscount($discountData);
		}
		$saveResult = self::saveMigrateDiscount($discountData);
		if (!$saveResult->isSuccess())
			return false;

		$migrateDiscountData = $saveResult->getData();
		unset($saveResult);
		$orderDiscountId = $migrateDiscountData['ORDER_DISCOUNT_ID'];
		$orderCouponId = 0;
		$discountDescr = current($migrateDiscountData['ACTIONS_DESCR']['BASKET']);
		if ($discountData['COUPON'] != '')
		{
			$couponData = self::$migrateCouponsCache[$discountData['COUPON']];
			$couponData['ORDER_ID'] = $orderId;
			$couponData['ORDER_DISCOUNT_ID'] = $migrateDiscountData['ORDER_DISCOUNT_ID'];
			$couponData['DATA']['DISCOUNT_ID'] = $migrateDiscountData['DISCOUNT_ID'];
			if (array_key_exists('DISCOUNT_ID', $couponData))
				unset($couponData['DISCOUNT_ID']);
			if (array_key_exists('DISCOUNT_NAME', $couponData))
				unset($couponData['DISCOUNT_NAME']);

			$saveResult = Sale\OrderDiscount::saveCoupon($couponData);
			if (!$saveResult->isSuccess())
				return false;
			$migrateCoupon = $saveResult->getData();
			$orderCouponId = $migrateCoupon['ID'];
		}

		foreach ($data['ITEMS'] as $basketItem)
		{
			$applyDescr = $discountDescr;
			if ($basketItem['DISCOUNT_VALUE'] != '')
			{
				if ($applyDescr['TYPE'] == Sale\Discount\Formatter::TYPE_SIMPLE)
				{
					$applyDescr['DESCR'] .= ' ('.$basketItem['DISCOUNT_VALUE'].')';
				}
				else
				{
					$valueData = array();
					if (preg_match('/^(|\+|-)(\d+|[.,]\d+|\d+[.,]\d+)\s?%$/', $basketItem['DISCOUNT_VALUE'], $valueData) == 1)
					{
						$applyDescr['RESULT_VALUE'] = (float)$basketItem['DISCOUNT_VALUE'];
						$applyDescr['RESULT_UNIT'] = Sale\Discount\Formatter::VALUE_TYPE_PERCENT;
					}
					unset($valueData);
				}
			}
			$ruleRow = array(
				'MODULE_ID' => 'catalog',
				'ORDER_DISCOUNT_ID' => $orderDiscountId,
				'ORDER_ID' => $orderId,
				'ENTITY_TYPE' => Sale\Internals\OrderRulesTable::ENTITY_TYPE_BASKET_ITEM,
				'ENTITY_ID' => $basketItem['ID'],
				'ENTITY_VALUE' => $basketItem['ID'],
				'COUPON_ID' => $orderCouponId,
				'APPLY' => 'Y'
			);
			$ruleDescr = array(
				'MODULE_ID' => 'catalog',
				'ORDER_DISCOUNT_ID' => $orderDiscountId,
				'ORDER_ID' => $orderId,
				'DESCR' => array($applyDescr)
			);
			$ruleResult = Sale\Internals\OrderRulesTable::add($ruleRow);
			if ($ruleResult->isSuccess())
			{
				$ruleDescr['RULE_ID'] = $ruleResult->getId();
				$descrResult = Sale\Internals\OrderRulesDescrTable::add($ruleDescr);
				if (!$descrResult->isSuccess())
					return false;
			}
			else
			{
				return false;
			}
			unset($ruleResult);
		}
		unset($basketItem);

		return true;
	}

	/**
	 * Check coupon for convert.
	 *
	 * @internal
	 * @param string $coupon				Coupon.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	private static function checkMigrateCoupon($coupon)
	{
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Main\Loader::includeModule('catalog');
		if (!self::$catalogIncluded)
			return false;

		static $catalogCouponTypes = null;
		if ($catalogCouponTypes === null)
			$catalogCouponTypes = array(
				Catalog\DiscountCouponTable::TYPE_ONE_ROW => Sale\Internals\DiscountCouponTable::TYPE_BASKET_ROW,
				Catalog\DiscountCouponTable::TYPE_ONE_ORDER => Sale\Internals\DiscountCouponTable::TYPE_ONE_ORDER,
				Catalog\DiscountCouponTable::TYPE_NO_LIMIT => Sale\Internals\DiscountCouponTable::TYPE_MULTI_ORDER
			);

		if (!isset(self::$migrateCouponsCache[$coupon]))
		{
			self::$migrateCouponsCache[$coupon] = false;
			$couponIterator = Catalog\DiscountCouponTable::getList(array(
				'select' => array('COUPON_ID' => 'ID', 'COUPON', 'TYPE', 'DISCOUNT_ID', 'DISCOUNT_NAME' => 'DISCOUNT.NAME'),
				'filter' => array('=COUPON' => $coupon)
			));
			$existCoupon = $couponIterator->fetch();
			unset($couponIterator);
			if (!empty($existCoupon))
			{
				$existCoupon['TYPE'] = (
					isset($catalogCouponTypes[$existCoupon['TYPE']])
					? $catalogCouponTypes[$existCoupon['TYPE']]
					: Sale\Internals\DiscountCouponTable::TYPE_ARCHIVED
				);
				$existCoupon['DATA'] = array(
					'MODE' => Sale\DiscountCouponsManager::COUPON_MODE_SIMPLE,
					'MODULE' => 'catalog',
					'DISCOUNT_ID' => 0,
					'TYPE' => Sale\Internals\DiscountCouponTable::TYPE_ARCHIVED,
					'USER_INFO' => array(),
				);
				self::$migrateCouponsCache[$coupon] = $existCoupon;
			}
			else
			{
				self::$migrateCouponsCache[$coupon] = self::createEmptyCoupon($coupon);
			}
			unset($existCoupon);
		}
		return true;
	}

	/**
	 * Create fake coupon.
	 *
	 * @internal
	 * @param string $coupon			Coupon.
	 * @return array
	 */
	private static function createEmptyCoupon($coupon)
	{
		return array(
			'COUPON' => $coupon,
			'TYPE' => Sale\Internals\DiscountCouponTable::TYPE_ARCHIVED,
			'COUPON_ID' => 0,
			'DATA' => array(
				'COUPON' => $coupon,
				'MODE' => Sale\DiscountCouponsManager::COUPON_MODE_SIMPLE,
				'MODULE' => 'catalog',
				'DISCOUNT_ID' => 0,
				'TYPE' => Sale\Internals\DiscountCouponTable::TYPE_ARCHIVED,
				'USER_INFO' => array(),
			)
		);
	}

	/**
	 * Create fake discount.
	 *
	 * @internal
	 * @param array &$discountData					Discount data.
	 * @param bool $accumulate				Accumulate discount.
	 * @return void
	 */
	private static function createEmptyDiscount(array &$discountData, $accumulate = false)
	{
		$accumulate = ($accumulate === true);
		static $emptyFields = null;
		if ($emptyFields === null)
		{
			$emptyFields = array(
				'DISCOUNT_ID' => 0,
				'NAME' => Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_MESS_CATALOG_DISCOUNT_NAME'),
				'SORT' => 100,
				'PRIORITY' => 1,
				'LAST_DISCOUNT' => 'Y',
				'USE_COUPONS' => 'N'
			);
		}

		static $replaceFields = null;
		static $replaceKeys = null;
		if ($replaceFields === null)
		{
			$replaceFields = array(
				'MODULE_ID' => 'catalog',
				'CONDITIONS' => array(
					'CLASS_ID' => 'CondGroup',
					'DATA' => array('All' => 'AND', 'True' => 'True'),
					'CHILDREN' => array()
				),
				'UNPACK' => '((1 == 1))',
				'ACTIONS' => array(),
				'APPLICATION' => '0'
			);
			$replaceKeys = array(
				'MODULE_ID',
				'CONDITIONS',
				'UNPACK',
				'ACTIONS',
				'APPLICATION'
			);
		}
		static $discountDescr = null;
		if ($discountDescr === null)
		{
			$discountDescr = Sale\Discount\Formatter::prepareRow(
				Sale\Discount\Formatter::TYPE_SIMPLE,
				Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_MESS_CATALOG_DISCOUNT_SIMPLE_MESS')
			);
		}
		static $accumulateDescr = null;
		if ($accumulateDescr === null)
		{
			$accumulateDescr = Sale\Discount\Formatter::prepareRow(
				Sale\Discount\Formatter::TYPE_SIMPLE,
				Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_MESS_TYPE_ACCUMULATE_EMPTY')
			);
		}
		foreach ($replaceKeys as $key)
		{
			if (array_key_exists($key, $discountData))
				unset($discountData[$key]);
		}
		unset($key);
		$discountData = array_merge($emptyFields, $discountData);
		foreach ($replaceFields as $key => $value)
		{
			$discountData[$key] = $value;
		}
		unset($key, $value);
		if (empty($discountData['ACTIONS_DESCR']))
			$discountData['ACTIONS_DESCR'] = array(
				'BASKET' => array(
					0 => ($accumulate ? $accumulateDescr : $discountDescr)
				)
			);
		if (!$accumulate)
			$discountData['USE_COUPONS'] = ($discountData['COUPON'] != '' ? 'Y' : 'N');
	}

	/**
	 * Check discount for convert.
	 *
	 * @internal
	 * @param array &$discountData			Discount data.
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 */
	private static function checkMigrateDiscount(&$discountData)
	{
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Main\Loader::includeModule('catalog');
		if (!self::$catalogIncluded)
			return;

		$coupon = $discountData['COUPON'];
		$hash = md5($discountData['DISCOUNT_ID'].'|'.$discountData['NAME']);
		if (!isset(self::$catalogDiscountsCache[$hash]))
		{
			$discountIterator = Catalog\DiscountTable::getList(array(
				'select' => array('*'),
				'filter' => array('=ID' => $discountData['DISCOUNT_ID'], '=NAME' => $discountData['NAME'])
			));
			$existDiscount = $discountIterator->fetch();
			unset($discountIterator);
			if (!empty($existDiscount))
			{
				if ($existDiscount['NAME'] != $discountData['NAME'])
				{
					self::createEmptyDiscount($discountData);
				}
				else
				{
					if ($existDiscount['TYPE'] == Catalog\DiscountTable::TYPE_DISCOUNT_SAVE)
					{
						self::createEmptyDiscount($discountData, true);
					}
					else
					{
						$existDiscount['COUPON'] = $discountData['COUPON'];
						$discountData = Catalog\Discount\DiscountManager::prepareData(
							$existDiscount, Sale\OrderDiscount::getManagerConfig()
						);
					}
				}
			}
			else
			{
				self::createEmptyDiscount($discountData);
			}
			unset($existDiscount);
			self::$catalogDiscountsCache[$hash] = $discountData;
		}
		else
		{
			$discountData = self::$catalogDiscountsCache[$hash];
		}
		$discountData['COUPON'] = $coupon;
	}

	/**
	 * Save converted discount.
	 *
	 * @internal
	 * @param array $discountData				Discount data.
	 * @return Sale\Result
	 * @throws \Exception
	 */
	private static function saveMigrateDiscount(array $discountData)
	{
		$result = new Sale\Result();
		$process = true;
		$hash = false;
		$resultData = array();
		$fields = Sale\Internals\OrderDiscountTable::prepareDiscountData($discountData);
		if (empty($fields) || !is_array($fields))
		{
			$process = false;
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_ERR_BAD_PREPARE_DISCOUNT'),
				self::ERROR_ID
			));
		}

		if ($process)
		{
			$hash = Sale\Internals\OrderDiscountTable::calculateHash($fields);
			if ($hash === false)
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_ERR_BAD_DISCOUNT_HASH'),
					self::ERROR_ID
				));
			}
		}

		if ($process)
		{
			if (!isset(self::$migrateDiscountsCache[$hash]))
			{
				$orderDiscountIterator = Sale\Internals\OrderDiscountTable::getList(array(
					'select' => array('*'),
					'filter' => array('=DISCOUNT_HASH' => $hash)
				));
				if ($orderDiscount = $orderDiscountIterator->fetch())
					self::$migrateDiscountsCache[$hash] = $orderDiscount;
				unset($orderDiscount, $orderDiscountIterator);
			}
			if (!empty(self::$migrateDiscountsCache[$hash]))
			{
				$resultData = self::$migrateDiscountsCache[$hash];
				$resultData['ID'] = (int)$resultData['ID'];
				$resultData['NAME'] = (string)$resultData['NAME'];
				$resultData['ORDER_DISCOUNT_ID'] = $resultData['ID'];
				$result->setId($resultData['ID']);
			}
			else
			{
				$fields['DISCOUNT_HASH'] = $hash;
				$fields['ACTIONS_DESCR'] = array();
				if (isset($discountData['ACTIONS_DESCR']))
					$fields['ACTIONS_DESCR'] = $discountData['ACTIONS_DESCR'];
				$tableResult = Sale\Internals\OrderDiscountTable::add($fields);
				if ($tableResult->isSuccess())
				{
					$resultData = $fields;
					$resultData['ID'] = (int)$tableResult->getId();
					$resultData['NAME'] = (string)$resultData['NAME'];
					$resultData['ORDER_DISCOUNT_ID'] = $resultData['ID'];
					$result->setId($resultData['ID']);
				}
				else
				{
					$process = false;
					$result->addErrors($tableResult->getErrors());
				}
				unset($tableResult, $fields);

				if ($process)
				{
					$moduleList = Sale\Internals\OrderDiscountTable::getDiscountModules($discountData);
					if (!empty($moduleList))
					{
						$resultModule = Sale\Internals\OrderModulesTable::saveOrderDiscountModules(
							$resultData['ORDER_DISCOUNT_ID'],
							$moduleList
						);
						if (!$resultModule)
						{
							Sale\Internals\OrderDiscountTable::clearList($resultData['ORDER_DISCOUNT_ID']);
							$resultData = array();
							$process = false;
							$result->addError(new Main\Entity\EntityError(
								Loc::getMessage('SALE_ORDER_DISCOUNT_MIGRATOR_ERR_SAVE_DISCOUNT_MODULES'),
								self::ERROR_ID
							));
						}
						unset($resultModule);
					}
					unset($needDiscountModules, $moduleList);
				}
			}
		}

		if ($process)
			$result->setData($resultData);
		unset($resultData, $process);

		return $result;
	}
}