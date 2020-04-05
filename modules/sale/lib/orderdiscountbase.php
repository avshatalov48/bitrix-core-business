<?php
namespace Bitrix\Sale;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class OrderDiscountBase
{
	const EVENT_ON_BUILD_DISCOUNT_PROVIDERS = 'onBuildDiscountProviders';

	const ERROR_ID = 'BX_SALE_ORDER_DISCOUNT';

	const PROVIDER_ACTION_PREPARE_DISCOUNT = 'prepareData';
	const PROVIDER_ACTION_GET_URL = 'getEditUrl';
	const PROVIDER_ACTION_APPLY_COUPON = 'calculateApplyCoupons';
	const PROVIDER_ACTION_ROUND_ITEM_PRICE = 'roundPrice';
	const PROVIDER_ACTION_ROUND_BASKET_PRICES = 'roundBasket';

	const STORAGE_TYPE_DISCOUNT_ACTION_DATA = 'ACTION_DATA';
	const STORAGE_TYPE_ORDER_CONFIG = 'ORDER_CONFIG';
	const STORAGE_TYPE_ROUND_CONFIG = 'ROUND_CONFIG';
	const STORAGE_TYPE_BASKET_ITEM = 'BASKET_ITEM';

	protected static $init = false;
	protected static $errors = array();
	private static $discountProviders = array();
	private static $managerConfig = array();

	private static $discountCache = array();

	/**
	 * Initial discount manager.
	 *
	 * @return void
	 */
	public static function init()
	{
		if (self::$init)
			return;

		static::initDiscountProviders();
		self::$init = true;
	}

	/**
	 * Set manager params.
	 *
	 * @param array $config			Manager params (site, currency, etc).
	 * @return bool
	 */
	public static function setManagerConfig($config)
	{
		if (empty($config) || empty($config['SITE_ID']))
			return false;
		if (empty($config['CURRENCY']))
			$config['CURRENCY'] = Internals\SiteCurrencyTable::getSiteCurrency($config['SITE_ID']);
		if (!isset($config['USE_BASE_PRICE']) || ($config['USE_BASE_PRICE'] != 'Y' && $config['USE_BASE_PRICE'] != 'N'))
			$config['USE_BASE_PRICE'] = ((string)Main\Config\Option::get('sale', 'get_discount_percent_from_base_price') == 'Y' ? 'Y' : 'N');
		if (empty($config['BASKET_ITEM']))
			$config['BASKET_ITEM'] = '$basketItem';
		self::$managerConfig = $config;
		return true;
	}

	/**
	 * Return current manager params.
	 *
	 * @return array
	 */
	public static function getManagerConfig()
	{
		return self::$managerConfig;
	}

	/**
	 * Convert and save discount.
	 *
	 * @param array $discount			Discount data.
	 * @param bool $extResult			Result extended result data.
	 * @return Result
	 */
	public static function saveDiscount(array $discount, $extResult = false)
	{
		static::init();
		$result = new Result();

		$extResult = ($extResult === true);

		$process = true;

		$internal = null;
		$discountData = false;
		$fields = false;
		$emptyData = array(
			'ID' => 0,
			'DISCOUNT_ID' => 0,
			'NAME' => '',
			'ORDER_DISCOUNT_ID' => 0,
			'ORDER_COUPON_ID' => 0,
			'USE_COUPONS' => '',
			'LAST_DISCOUNT' => '',
			'MODULE_ID' => '',
			'EDIT_PAGE_URL' => '',
			'ACTIONS_DESCR' => array()
		);
		if ($extResult)
		{
			$emptyData['RAW_DATA'] = array();
			$emptyData['PREPARED_DATA'] = array();
		}
		$resultData = $emptyData;

		$config = static::getManagerConfig();

		if (empty($config))
		{
			$process = false;
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_EMPTY_MANAGER_PARAMS'),
				self::ERROR_ID
			));
		}

		if (empty($discount) || empty($discount['MODULE_ID']))
		{
			$process = false;
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_EMPTY_DISCOUNT'),
				self::ERROR_ID
			));
		}

		if ($process)
		{
			if (!static::isNativeModule($discount['MODULE_ID']))
			{
				if (!static::checkDiscountProvider($discount['MODULE_ID']))
				{
					$process = false;
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_DISCOUNT_MODULE'),
						self::ERROR_ID
					));
				}
				else
				{
					$discountData = static::executeDiscountProvider(
						array('MODULE_ID' => $discount['MODULE_ID'], 'METHOD' => self::PROVIDER_ACTION_PREPARE_DISCOUNT),
						array($discount, $config)
					);
				}
			}
			else
			{
				$discountData = static::prepareData($discount);
			}
			if (empty($discountData) || !is_array($discountData))
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_PREPARE_DISCOUNT'),
					self::ERROR_ID
				));
			}
		}

		if ($process)
		{
			$fields = static::normalizeDiscountFields($discountData);
			if (empty($fields) || !is_array($fields))
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_PREPARE_DISCOUNT'),
					self::ERROR_ID
				));
			}
			elseif ($fields['DISCOUNT_HASH'] === null)
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_DISCOUNT_HASH'),
					self::ERROR_ID
				));
			}
		}

		if ($process)
		{
			$existDiscount = static::searchDiscount($fields['DISCOUNT_HASH']);
			if ($existDiscount === null)
			{
				/** @var Result $internalResult */
				$internalResult = static::addDiscount($fields, $discountData);
				if ($internalResult->isSuccess())
				{
					$existDiscount = static::searchDiscount($fields['DISCOUNT_HASH']);
				}
				else
				{
					$process = false;
					$result->addErrors($internalResult->getErrors());
				}
				unset($internalResult);
			}
			if ($existDiscount !== null)
			{
				$resultData = $existDiscount;
				$result->setId($resultData['ID']);
			}
		}

		if ($process)
		{
			$resultData['EDIT_PAGE_URL'] = $discountData['EDIT_PAGE_URL'];
			if ($extResult)
			{
				$resultData['RAW_DATA'] = $discount;
				$resultData['PREPARED_DATA'] = $discountData;
			}
			$result->setData($resultData);
		}
		unset($resultData, $process);

		return $result;
	}

	/**
	 * Save coupon.
	 *
	 * @param array $coupon		Coupon data.
	 * @return Result
	 */
	public static function saveCoupon($coupon)
	{
		static::init();
		$result = new Result();

		$process = true;

		$resultData = array(
			'ID' => 0,
			'ORDER_ID' => 0,
			'ORDER_DISCOUNT_ID' => 0,
			'COUPON' => '',
			'TYPE' => 0,
			'COUPON_ID' => 0,
			'DATA' => array()
		);

		if (empty($coupon) || !is_array($coupon))
		{
			$process = false;
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_EMPTY_COUPON'),
				self::ERROR_ID
			));
		}
		if ($process)
		{
			if (empty($coupon['ORDER_DISCOUNT_ID']) || (int)$coupon['ORDER_DISCOUNT_ID'] <= 0)
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_EMPTY_COUPON'),
					self::ERROR_ID
				));
			}
			if (empty($coupon['COUPON']))
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_COUPON_CODE_ABSENT'),
					self::ERROR_ID
				));
			}
			if (!isset($coupon['TYPE']))
			{
				$process = false;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage(
						'SALE_ORDER_DISCOUNT_ERR_COUPON_TYPE_ABSENT',
						array('#COUPON#' => $coupon['COUPON'])
					),
					self::ERROR_ID
				));
			}
		}

		if ($process)
		{
			$validateResult = static::validateCoupon($coupon);
			if (!$validateResult->isSuccess())
			{
				$process = false;
				$result->addErrors($validateResult->getErrors());
			}
			unset($validateResult);
		}

		if ($process)
		{
			$iterator = static::getOrderCouponIterator(array(
				'select' => array('*'),
				'filter' => array('=COUPON' => $coupon['COUPON'], '=ORDER_ID' => $coupon['ORDER_ID'])
			));
			if ($row = $iterator->fetch())
			{
				$resultData = $row;
			}
			else
			{
				$internalResult = static::addCoupon($coupon);
				if ($internalResult->isSuccess())
				{
					$resultData = $internalResult->getData();
					$resultData['ID'] = $internalResult->getId();
				}
				else
				{
					$process = false;
					$result->addErrors($internalResult->getErrors());
				}
				unset($internalResult);
			}
			unset($row, $iterator);
		}

		if ($process)
		{
			$result->setId($resultData['ID']);
			$result->setData($resultData);
		}
		unset($process, $resultData);

		return $result;
	}

	/**
	 * Check apply discount.
	 *
	 * @param string $module			Discount module.
	 * @param array $discount			Discount data.
	 * @param array $basket				Basket data.
	 * @param array $params				Calculate params.
	 * @return bool|array
	 */
	public static function calculateApplyCoupons($module, $discount, $basket, $params)
	{
		static::init();

		$module = (string)$module;
		if (static::isNativeModule($module))
			return false;

		return static::executeDiscountProvider(
			array('MODULE_ID' => $module, 'METHOD' => self::PROVIDER_ACTION_APPLY_COUPON),
			array($discount, $basket, $params)
		);
	}

	/**
	 * Round basket item price.
	 *
	 * @param array $basketItem			Basket item data.
	 * @param array $roundData			Round data.
	 * @return array
	 */
	public static function roundPrice(array $basketItem, array $roundData = array())
	{
		static::init();

		if (empty($basketItem))
			return array();

		$result = static::executeDiscountProvider(
			array('MODULE_ID' => $basketItem['MODULE'], 'METHOD' => self::PROVIDER_ACTION_ROUND_ITEM_PRICE),
			array($basketItem, $roundData)
		);
		if (empty($result))
			return array();

		if (!isset($result['PRICE']) || !isset($result['DISCOUNT_PRICE']))
			return array();

		if (!isset($result['ROUND_RULE']))
			return array();

		return $result;
	}

	/**
	 * Round basket prices.
	 *
	 * @param array $basket			Basket.
	 * @param array $roundData		Round data.
	 * @param array $orderData		Order (without basket).
	 * @return array
	 */
	public static function roundBasket(array $basket, array $roundData = array(), array $orderData = array())
	{
		static::init();

		if (empty($basket))
			return array();

		$result = array();
		$basketByModules = array();
		$roundByModules = array();
		foreach ($basket as $basketCode => $basketItem)
		{
			if (!isset($basketItem['MODULE']))
				continue;
			$module = $basketItem['MODULE'];
			if (!isset($basketByModules[$module]))
			{
				$basketByModules[$module] = array();
				$roundByModules[$module] = array();
			}
			$basketByModules[$module][$basketCode] = $basketItem;
			$roundByModules[$module][$basketCode] = (isset($roundData[$basketCode]) ? $roundData[$basketCode] : array());
		}
		unset($basketCode, $basketItem);

		foreach ($basketByModules as $module => $moduleItems)
		{
			$moduleResult = static::executeDiscountProvider(
				array('MODULE_ID' => $module, 'METHOD' => self::PROVIDER_ACTION_ROUND_BASKET_PRICES),
				array($moduleItems, $roundByModules[$module], $orderData)
			);
			if ($moduleResult === false)
			{
				$moduleResult = array();
				foreach ($moduleItems as $basketCode => $basketItem)
				{
					$itemResult = static::roundPrice($basketItem, $roundByModules[$module][$basketCode]);
					if (!empty($itemResult))
						$moduleResult[$basketCode] = $itemResult;
				}
			}
			if (empty($moduleResult))
				continue;

			foreach (array_keys($moduleResult) as $basketCode)
				$result[$basketCode] = $moduleResult[$basketCode];
			unset($moduleResult);
		}
		unset($moduleResult, $module, $moduleItems);

		return $result;
	}

	/**
	 * Check existing discount provider for module.
	 *
	 * @param string $module			Module id.
	 * @return bool
	 */
	public static function checkDiscountProvider($module)
	{
		static::init();
		$module = (string)$module;
		if (static::isNativeModule($module))
			return true;
		return ($module != '' && isset(self::$discountProviders[$module]));
	}

	/**
	 * Return url for edit sale discount.
	 *
	 * @param array $discount			Discount data.
	 * @return string
	 */
	public static function getEditUrl(array $discount)
	{
		$result = '';
		if (!empty($discount['ID']))
			$result = '/bitrix/admin/sale_discount_edit.php?lang='.LANGUAGE_ID.'&ID='.$discount['ID'];
		return $result;
	}

	/**
	 * Clear discount cache.
	 *
	 * @return void
	 */
	public static function clearCache()
	{
		$entity = get_called_class();
		if (!isset(self::$discountCache[$entity]))
			return;
		unset(self::$discountCache[$entity]);
	}

	/**
	 * Load discount result for order.
	 *
	 * @param int $order				Order id.
	 * @param array|bool $basketList	Correspondence between basket ids and basket codes.
	 * @param array $basketData			Basket data.
	 * @return Result
	 */
	public static function loadResultFromDb($order, array $basketList = [], array $basketData = [])
	{
		static::init();
		$result = new Result;

		/** @var Discount $discountClassName */
		$discountClassName = static::getDiscountClassName();
		$emptyApplyBlock = $discountClassName::getEmptyApplyBlock();

		$order = (int)$order;
		if ($order <= 0)
		{
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_BAD_ORDER_ID'),
				self::ERROR_ID
			));
			return $result;
		}
		$resultData = [
			'APPLY_BLOCKS' => [],
			'DISCOUNT_LIST' => [],
			'COUPON_LIST' => [],
			'STORED_ACTION_DATA' => []
		];

		$applyBlocks = [];

		$orderDiscountIndex = [];
		$orderDiscountLink = [];

		$discountList = [];
		$discountSort = [];
		$couponList = [];

		$resultData['COUPON_LIST'] = static::loadCouponsFromDb($order);
		if (!empty($resultData['COUPON_LIST']))
		{
			foreach ($resultData['COUPON_LIST'] as $coupon)
				$couponList[$coupon['ID']] = $coupon['COUPON'];
			unset($coupon);
		}

		$ruleIterator = static::getResultIterator([
			'filter' => ['=ORDER_ID' => $order],
			'order' => ['ID' => 'ASC']
		]);
		while ($rule = $ruleIterator->fetch())
		{
			$rule['ID'] = (int)$rule['ID'];
			$rule['ORDER_DISCOUNT_ID'] = (int)$rule['ORDER_DISCOUNT_ID'];
			$rule['ORDER_COUPON_ID'] = (int)$rule['COUPON_ID'];
			$rule['ENTITY_ID'] = (int)$rule['ENTITY_ID'];

			if ($rule['ORDER_COUPON_ID'] > 0)
			{
				if (!isset($couponList[$rule['COUPON_ID']]))
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage(
							'SALE_ORDER_DISCOUNT_ERR_RULE_COUPON_NOT_FOUND',
							array('#ID#' => $rule['ID'], '#COUPON_ID#' => $rule['COUPON_ID'])
						)
					));
				}
				else
				{
					$rule['COUPON_ID'] = $couponList[$rule['ORDER_COUPON_ID']];
				}
			}
			if (!isset($rule['RULE_DESCR_ID']))
				$rule['RULE_DESCR_ID'] = 0;
			$rule['RULE_DESCR_ID'] = (int)$rule['RULE_DESCR_ID'];

			$rule['APPLY_BLOCK_COUNTER'] = (int)$rule['APPLY_BLOCK_COUNTER'];
			$blockCounter = $rule['APPLY_BLOCK_COUNTER'];
			if (!isset($applyBlocks[$blockCounter]))
				$applyBlocks[$blockCounter] = $emptyApplyBlock;
			if (!isset($orderDiscountIndex[$blockCounter]))
				$orderDiscountIndex[$blockCounter] = 0;

			if (static::isNativeModule($rule['MODULE_ID']))
			{
				$discountId = (int)$rule['ORDER_DISCOUNT_ID'];
				if (!isset($orderDiscountLink[$discountId]))
				{
					$applyBlocks[$blockCounter]['ORDER'][$orderDiscountIndex[$blockCounter]] = static::formatSaleRuleResult($rule);
					$orderDiscountLink[$discountId] = &$applyBlocks[$blockCounter]['ORDER'][$orderDiscountIndex[$blockCounter]];
					$orderDiscountIndex[$blockCounter]++;
				}

				$ruleItem = static::formatSaleItemRuleResult($rule);

				switch (static::getResultEntityFromInternal($rule['ENTITY_TYPE']))
				{
					case $discountClassName::ENTITY_BASKET_ITEM:
						$index = static::transferEntityCodeFromInternal($rule, $basketList);
						if ($index == '')
							continue 2;

						$ruleItem['BASKET_ID'] = static::transferEntityCodeFromInternal($rule, []);
						static::fillRuleProductFields($ruleItem, $basketData, $index);
						if (!isset($orderDiscountLink[$discountId]['RESULT']['BASKET']))
							$orderDiscountLink[$discountId]['RESULT']['BASKET'] = [];
						$orderDiscountLink[$discountId]['RESULT']['BASKET'][$index] = $ruleItem;
						if ($ruleItem['ACTION_BLOCK_LIST'] === null)
							$orderDiscountLink[$discountId]['ACTION_BLOCK_LIST'] = false;
						break;
					case $discountClassName::ENTITY_DELIVERY:
						if (!isset($orderDiscountLink[$discountId]['RESULT']['DELIVERY']))
							$orderDiscountLink[$discountId]['RESULT']['DELIVERY'] = [];
						$ruleItem['DELIVERY_ID'] = static::transferEntityCodeFromInternal($rule, []);
						$orderDiscountLink[$discountId]['RESULT']['DELIVERY'] = $ruleItem;
						break;
				}
				unset($ruleItem, $discountId);
			}
			else
			{
				if (
					$rule['ENTITY_ID'] <= 0
					|| static::getResultEntityFromInternal($rule['ENTITY_TYPE']) != $discountClassName::ENTITY_BASKET_ITEM
				)
					continue;

				$index = static::transferEntityCodeFromInternal($rule, $basketList);
				if ($index == '')
					continue;

				$ruleResult = static::formatBasketRuleResult($rule);
				static::fillRuleProductFields($ruleResult, $basketData, $index);

				if (!isset($applyBlocks[$blockCounter]['BASKET'][$index]))
					$applyBlocks[$blockCounter]['BASKET'][$index] = [];
				$applyBlocks[$blockCounter]['BASKET'][$index][] = $ruleResult;

				unset($ruleResult);
			}

			if (!isset($discountList[$rule['ORDER_DISCOUNT_ID']]))
			{
				$discountList[$rule['ORDER_DISCOUNT_ID']] = $rule['ORDER_DISCOUNT_ID'];
				$discountSort[] = $rule['ORDER_DISCOUNT_ID'];
			}
		}
		unset($rule, $ruleIterator);
		unset($couponList);
		unset($orderDiscountLink, $orderDiscountIndex);

		if (!empty($discountList))
		{
			$resultData['DISCOUNT_LIST'] = static::loadOrderDiscountFromDb($discountList, $discountSort);
			if ($resultData['DISCOUNT_LIST'] === null)
				$resultData['DISCOUNT_LIST'] = [];
		}
		unset($discountSort, $discountList);

		$actionsData = static::loadOrderStoredDataFromDb(
			$order,
			self::STORAGE_TYPE_DISCOUNT_ACTION_DATA
		);
		if ($actionsData !== null)
			$resultData['STORED_ACTION_DATA'] = $actionsData;
		unset($actionsData);

		$dataIterator = static::getRoundResultIterator([
			'select' => ['*'],
			'filter' => [
				'=ORDER_ID' => $order,
				'=ENTITY_TYPE' => static::getRoundEntityInternal($discountClassName::ENTITY_BASKET_ITEM)
			]
		]);
		while ($data = $dataIterator->fetch())
		{
			$data['APPLY_BLOCK_COUNTER'] = (int)$data['APPLY_BLOCK_COUNTER'];
			$blockCounter = $data['APPLY_BLOCK_COUNTER'];
			if (!isset($applyBlocks[$blockCounter]))
				$applyBlocks[$blockCounter] = $emptyApplyBlock;
			$basketCode = static::transferEntityCodeFromInternal($data, $basketList);
			if ($basketCode == '')
				continue;

			$applyBlocks[$blockCounter]['BASKET_ROUND'][$basketCode] = array(
				'RULE_ID' => (int)$data['ID'],
				'APPLY' => $data['APPLY'],
				'ROUND_RULE' => $data['ROUND_RULE']
			);
			unset($basketCode, $blockCounter);
		}
		unset($data, $dataIterator);

		if (!empty($applyBlocks))
			ksort($applyBlocks);

		$resultData['APPLY_BLOCKS'] = $applyBlocks;
		unset($applyBlocks);

		$result->setData($resultData);
		unset($resultData);

		unset($emptyApplyBlock, $discountClassName);

		return $result;
	}

	/**
	 * Load applied discount list.
	 *
	 * @param array $discountIds
	 * @param array $discountOrder
	 * @return array|null
	 */
	protected static function loadOrderDiscountFromDb(array $discountIds, array $discountOrder)
	{
		if (empty($discountIds) || empty($discountOrder))
			return null;

		$result = [];
		$list = [];
		$iterator = static::getOrderDiscountIterator([
			'select' => ['*'],
			'filter' => ['@ID' => $discountIds]
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['ORDER_DISCOUNT_ID'] = $row['ID'];
			$row['MODULES'] = [];
			$row['SIMPLE_ACTION'] = true;
			if (static::isNativeModule($row['MODULE_ID']))
				$row['SIMPLE_ACTION'] = self::isSimpleAction($row['APPLICATION']);
			$list[$row['ID']] = $row;
		}
		unset($row, $iterator);

		if (!empty($list))
		{
			foreach ($discountOrder as $id)
			{
				if (!isset($list[$id]))
					continue;
				$result[$id] = $list[$id];
			}
			unset($id);
		}
		unset($list);

		if (!empty($result))
		{
			$resultIds = array_keys($result);
			$discountModules = static::loadModulesFromDb($resultIds);
			if ($discountModules !== null)
			{
				foreach ($discountModules as $id => $modules)
					$result[$id]['MODULES'] = $modules;
				unset($id, $modules);
			}
			unset($discountModules);

			foreach ($resultIds as $id)
			{
				$discount = $result[$id];
				if (static::isNativeModule($discount['MODULE_ID']))
				{
					$result[$id]['EDIT_PAGE_URL'] = static::getEditUrl(['ID' => $discount['DISCOUNT_ID']]);
				}
				else
				{
					$result[$id]['EDIT_PAGE_URL'] = (string)static::executeDiscountProvider(
						['MODULE_ID' => $discount['MODULE_ID'], 'METHOD' => self::PROVIDER_ACTION_GET_URL],
						[
							['ID' => $discount['DISCOUNT_ID'], 'MODULE_ID' => $discount['MODULE_ID']]
						]
					);
				}
			}
			unset($discount, $id);
		}

		return (!empty($result) ? $result : null);
	}

	/**
	 * Load stored data collection for order.
	 * @internal
	 *
	 * @param int $order				Order id.
	 * @param string $storageType		Storage type (only simple value, no mixed).
	 * @param array $additionalFilter	Additional filter for internal getList.
	 * @return array|null
	 */
	public static function loadStoredDataFromDb($order, $storageType, array $additionalFilter = array())
	{
		$result = null;

		$order = (int)$order;
		if ($order <= 0)
			return $result;

		$storageType = static::getStorageTypeInternal($storageType);
		if ($storageType === null)
			return $result;
		$filter = [
			'=ORDER_ID' => $order,
			'=ENTITY_TYPE' => $storageType,
		];
		if (!empty($additionalFilter))
			$filter = $filter + $additionalFilter;

		$list = [];
		$iterator = static::getStoredDataIterator(array(
			'select' => ['*'],
			'filter' => $filter
		));
		while ($row = $iterator->fetch())
		{
			if (empty($row['ENTITY_DATA']) || !is_array($row['ENTITY_DATA']))
				continue;
			$index = static::getEntityIndex($row);
			$list[$index] = $row['ENTITY_DATA'];
		}
		unset($index, $row, $iterator);
		if (!empty($list))
			$result = $list;
		unset($list);

		return $result;
	}

	/**
	 * Load order stored data row.
	 * @internal
	 *
	 * @param int $order				Order id.
	 * @param string $storageType		Storage type (only simple value, no mixed).
	 * @return array|null
	 */
	public static function loadOrderStoredDataFromDb($order, $storageType)
	{
		$result = null;

		$data = static::loadStoredDataFromDb(
			$order,
			$storageType,
			['=ENTITY_ID' => $order]
		);
		if ($data === null)
			return $result;
		if (count($data) > 1)
			return $result;
		if (isset($data[$order]))
			$result = $data[$order];
		unset($data);

		return $result;
	}

	/**
	 * Save order stored data.
	 *
	 * @param int $order
	 * @param string $storageType
	 * @param array $data
	 * @param array $options
	 * @return Result
	 */
	public static function saveOrderStoredData($order, $storageType, array $data, array $options = array())
	{
		return static::saveStoredDataBlock(
			$order,
			$storageType,
			[$order => [
				'ENTITY_ID' => $order,
				'ENTITY_VALUE' => $order,
				'ENTITY_DATA' => $data
			]],
			$options
		);
	}

	/**
	 * Save stored data for entities.
	 *
	 * @param int $order
	 * @param string $storageType
	 * @param array $block
	 * @param array $options
	 * @return Result
	 * @throws Main\Db\SqlQueryException
	 */
	public static function saveStoredDataBlock($order, $storageType, array $block, array $options = array())
	{
		$result = new Result;

		$order = (int)$order;
		if ($order <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_ORDER_ID'),
				self::ERROR_ID
			));
			return $result;
		}

		$storageType = static::getStorageTypeInternal($storageType);
		if ($storageType === null)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_STORAGE_TYPE'),
				self::ERROR_ID
			));
			return $result;
		}

		$allowUpdate = (isset($options['ALLOW_UPDATE']) && $options['ALLOW_UPDATE'] === 'Y');
		$deleteMissing = (isset($options['DELETE_MISSING']) && $options['DELETE_MISSING'] === 'Y');

		$collection = [];
		$deleteList = [];
		$iterator = static::getStoredDataIterator([
			'select' => ['*'],
			'filter' => [
				'=ORDER_ID' => $order,
				'=ENTITY_TYPE' => $storageType,
			]
		]);
		while ($row = $iterator->fetch())
		{
			$index = static::getEntityIndex($row);
			$collection[$index] = $row;
			$deleteList[$index] = $row['ID'];
		}
		unset($row, $iterator);

		$existError = false;
		foreach ($block as $index => $row)
		{
			if (isset($deleteList[$index]))
				unset($deleteList[$index]);
			if (!empty($collection[$index]) && !$allowUpdate)
			{
				$existError = true;
				continue;
			}
			if (empty($collection[$index]))
			{
				$row['ORDER_ID'] = $order;
				$row['ENTITY_TYPE'] = $storageType;
				$resultInternal = static::addStoredDataInternal($row);
			}
			else
			{
				$resultInternal = static::updateStoredDataInternal(
					$collection[$index]['ID'],
					['ENTITY_DATA' => $row['ENTITY_DATA']]
				);
			}
			if (!$resultInternal->isSuccess())
				$result->addErrors($resultInternal->getErrors());
		}
		unset($resultInternal, $index, $row);

		if ($existError)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_ORDER_STORED_DATA_ALREADY_EXISTS'),
				self::ERROR_ID
			));
		}
		unset($existError);

		if ($result->isSuccess())
		{
			if ($deleteMissing && !empty($deleteList))
				static::deleteRowsByIndex(static::getStoredDataTableInternal(), 'ID', $deleteList);
		}
		unset($deleteList, $deleteMissing);

		return $result;
	}

	public static function addResultBlock($order, array $block)
	{
		$result = new Result();

		$order = (int)$order;
		if ($order <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_ORDER_ID'),
				self::ERROR_ID
			));
			return $result;
		}

		if (empty($block))
			return $result;

		foreach ($block as $row)
		{
			$row['ORDER_ID'] = $order;
			$row['ENTITY_TYPE'] = static::getResultEntityInternal($row['ENTITY_TYPE']);
			if (!isset($row['APPLY_BLOCK_COUNTER']) || $row['APPLY_BLOCK_COUNTER'] < 0)
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_APPLY_BLOCK_COUNTER')
				));
				return $result;
			}
			$resultInternal = static::addResultRow($row);
			if (!$resultInternal->isSuccess())
				$result->addErrors($resultInternal->getErrors());
		}
		unset($resultInternal, $row);

		return $result;
	}

	public static function updateResultBlock($order, array $block)
	{
		$result = new Result();

		$order = (int)$order;
		if ($order <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_ORDER_ID'),
				self::ERROR_ID
			));
			return $result;
		}

		$deleteList = array();
		$iterator = static::getResultIterator(array(
			'select' => array('ID'),
			'filter' => array('=ORDER_ID' => $order),
			'order' => array('ID' => 'ASC')
		));
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$deleteList[$row['ID']] = $row['ID'];
		}
		unset($row, $iterator);

		if (!empty($block))
		{
			foreach ($block as $row)
			{
				$id = null;
				if (isset($row['RULE_ID']) && $row['RULE_ID'] > 0)
					$id = $row['RULE_ID'];
				unset($row['RULE_ID']);
				if ($id === null)
				{
					$result->addError(new Main\Error(
						Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_RESULT_ROW_ID_IS_ABSENT'),
						self::ERROR_ID
					));
					continue;
				}
				if (!isset($deleteList[$id]))
				{
					$result->addError(new Main\Error(
						Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_RESULT_ROW_ID'),
						self::ERROR_ID
					));
					continue;
				}
				unset($deleteList[$id]);

				$resultInternal = static::updateResultRow($id, $row);
				if (!$resultInternal->isSuccess())
					$result->addErrors($resultInternal->getErrors());
			}
			unset($resultInternal, $row);
		}

		if (!$result->isSuccess())
			return $result;

		if (!empty($deleteList))
		{
			self::deleteRowsByIndex(static::getResultTableNameInternal(), 'ID', $deleteList);
			self::deleteRowsByIndex(static::getResultDescriptionTableNameInternal(), 'RULE_ID', $deleteList);
		}

		return $result;
	}

	public static function addRoundBlock($order, array $block)
	{
		$result = new Result();

		$order = (int)$order;
		if ($order <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_ORDER_ID'),
				self::ERROR_ID
			));
			return $result;
		}

		if (empty($block))
			return $result;

		foreach ($block as $row)
		{
			$row['ORDER_ID'] = $order;
			$row['ENTITY_TYPE'] = static::getRoundEntityInternal($row['ENTITY_TYPE']);
			$row['ORDER_ROUND'] = 'N';
			if (!isset($row['APPLY_BLOCK_COUNTER']) || $row['APPLY_BLOCK_COUNTER'] < 0)
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_APPLY_BLOCK_COUNTER')
				));
				return $result;
			}
			$resultInternal = static::addRoundResultInternal($row);
			if (!$resultInternal->isSuccess())
				$result->addErrors($resultInternal->getErrors());
		}
		unset($resultInternal, $row);

		return $result;
	}

	public static function updateRoundBlock($order, array $block)
	{
		$result = new Result();

		$order = (int)$order;
		if ($order <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_ORDER_ID'),
				self::ERROR_ID
			));
			return $result;
		}

		$deleteList = array();
		$iterator = static::getRoundResultIterator([
			'select' => ['ID'],
			'filter' => ['=ORDER_ID' => $order]
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$deleteList[$row['ID']] = $row['ID'];
		}
		unset($row, $iterator);

		if (!empty($block))
		{
			foreach ($block as $row)
			{
				$id = null;
				if (isset($row['RULE_ID']) && $row['RULE_ID'] > 0)
					$id = $row['RULE_ID'];
				if ($id === null)
				{
					$result->addError(new Main\Error(
						Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_ROUND_ROW_ID_IS_ABSENT'),
						self::ERROR_ID
					));
					continue;
				}
				if (!isset($deleteList[$id]))
				{
					$result->addError(new Main\Error(
						Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_BAD_ROUND_ROW_ID'),
						self::ERROR_ID
					));
					continue;
				}
				unset($deleteList[$id]);

				unset($row['RULE_ID']);
				$resultInternal = static::updateRoundResultInternal($id, $row);
				if (!$resultInternal->isSuccess())
					$result->addErrors($resultInternal->getErrors());
			}
			unset($resultInternal, $row);
		}

		if (!$result->isSuccess())
			return $result;

		if (!empty($deleteList))
			self::deleteRowsByIndex(static::getRoundTableNameInternal(), 'ID', $deleteList);

		return $result;
	}

	/**
	 * Delete all data by order.
	 *
	 * @param int $order			Order id.
	 * @return void
	 */
	public static function deleteByOrder($order) {}

	/**
	 * Return parent entity type. The method must be overridden in the derived class.
	 * @internal
	 *
	 * @return string
	 * @throws Main\NotImplementedException
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	protected static function getDiscountClassName()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getDiscountClassName();
	}

	/**
	 * Returns true, if discount from module sale.
	 *
	 * @param string $module	Module id.
	 * @return bool
	 */
	protected static function isNativeModule($module)
	{
		return ($module === 'sale');
	}

	/**
	 * Return valid provider action list.
	 *
	 * @return array
	 */
	protected static function getDiscountProviderActions()
	{
		return array(
			self::PROVIDER_ACTION_PREPARE_DISCOUNT,
			self::PROVIDER_ACTION_GET_URL,
			self::PROVIDER_ACTION_APPLY_COUPON,
			self::PROVIDER_ACTION_ROUND_ITEM_PRICE,
			self::PROVIDER_ACTION_ROUND_BASKET_PRICES
		);
	}

	/**
	 * Initialization discount providers.
	 *
	 * @return void
	 */
	protected static function initDiscountProviders()
	{
		self::$discountProviders = array();
		$event = new Main\Event('sale', self::EVENT_ON_BUILD_DISCOUNT_PROVIDERS, array());
		$event->send();
		$resultList = $event->getResults();
		if (empty($resultList) || !is_array($resultList))
			return;
		$actionList = static::getDiscountProviderActions();
		/** @var Main\EventResult $eventResult */
		foreach ($resultList as $eventResult)
		{
			if ($eventResult->getType() != Main\EventResult::SUCCESS)
				continue;
			$module = (string)$eventResult->getModuleId();
			$provider = $eventResult->getParameters();
			if (empty($provider) || !is_array($provider))
				continue;
			if (!isset($provider[self::PROVIDER_ACTION_PREPARE_DISCOUNT]))
				continue;
			self::$discountProviders[$module] = array(
				'module' => $module
			);
			foreach ($actionList as $action)
			{
				if (isset($provider[$action]))
					self::$discountProviders[$module][$action] = $provider[$action];
			}
		}
		unset($provider, $module, $actionList, $eventResult, $resultList, $event);
	}

	/**
	 * Execute discount provider.
	 *
	 * @param array $provider			Provider info
	 * 	keys are case sensitive:
	 *		<ul>
	 *		<li>string MODULE				Provider module id
	 * 		<li>string METHOD				Prodider method id
	 *		</ul>.
	 * @param array $data				Data for execute.
	 * @return mixed
	 */
	protected static function executeDiscountProvider(array $provider, array $data)
	{
		$module = $provider['MODULE_ID'];
		$method = $provider['METHOD'];

		if (!isset(self::$discountProviders[$module]) || !isset(self::$discountProviders[$module][$method]))
			return false;

		return call_user_func_array(
			self::$discountProviders[$module][$method],
			$data
		);
	}

	/**
	 * Prepare sale discount before saving.
	 *
	 * @param array $discount				Discount data.
	 * @return array|bool
	 */
	protected static function prepareData($discount)
	{
		$fields = static::fillAbsentDiscountFields($discount);
		if ($fields === null)
			return false;

		$discountId = (int)$fields['ID'];
		if (!isset($fields['NAME']) || (string)$fields['NAME'] == '')
			$fields['NAME'] = Loc::getMessage('SALE_ORDER_DISCOUNT_NAME_TEMPLATE', array('#ID#' => $fields['ID']));
		$fields['DISCOUNT_ID'] = $discountId;
		$fields['EDIT_PAGE_URL'] = static::getEditUrl(array('ID' => $discountId));
		unset($fields['ID']);

		return $fields;
	}

	/**
	 * Get absent discount fields from database.
	 *
	 * @param array $fields		Current discount state.
	 * @return array|null
	 */
	protected static function fillAbsentDiscountFields(array $fields)
	{
		if (empty($fields) || empty($fields['ID']))
			return null;

		$discountId = (int)$fields['ID'];
		if ($discountId <= 0)
			return null;

		$requiredFields = static::checkRequiredOrderDiscountFields($fields);
		if (!empty($requiredFields))
		{
			if (in_array('ACTIONS_DESCR', $requiredFields))
				return null;
			$requiredFields[] = 'ID';
			$iterator = static::getDiscountIterator(array(
				'select' => $requiredFields,
				'filter' => array('=ID' => $discountId)
			));
			$row = $iterator->fetch();
			unset($iterator);
			if (empty($row))
				return null;
			foreach ($row as $field => $value)
			{
				if (isset($fields[$field]))
					continue;
				$fields[$field] = $value;
			}
			unset($field, $value);
		}
		unset($requiredFields);

		return $fields;
	}

	/**
	 * Clear raw data and calculate discount hash.
	 *
	 * @param array $rawFields	Discount information.
	 * @return array|null
	 */
	protected static function normalizeDiscountFields(array $rawFields)
	{
		$result = static::normalizeOrderDiscountFieldsInternal($rawFields);
		if (!is_array($result))
			return null;
		$result['DISCOUNT_HASH'] = static::calculateOrderDiscountHashInternal($result);
		return $result;
	}

	/**
	 * Returns exists discount for discount hash (cached).
	 *
	 * @param string $hash		Discount hash.
	 * @return array|null
	 */
	protected static function searchDiscount($hash)
	{
		$hash = (string)$hash;
		if ($hash === '')
			return null;
		$entity = get_called_class();
		if (!isset(self::$discountCache[$entity]))
			self::$discountCache[$entity] = array();
		if (!isset(self::$discountCache[$entity][$hash]))
		{
			$iterator = static::getOrderDiscountIterator(array(
				'select' => array('*'),
				'filter' => array('=DISCOUNT_HASH' => $hash)
			));
			$row = $iterator->fetch();
			if (!empty($row))
				self::setCacheItem($entity, $row);
			unset($row, $iterator);
		}

		return (isset(self::$discountCache[$entity][$hash]) ? self::$discountCache[$entity][$hash] : null);
	}

	/**
	 * Save exist discount to cache.
	 *
	 * @param string $entity	Entity id (class name).
	 * @param array $fields		Discount.
	 * @return void
	 */
	private static function setCacheItem($entity, array $fields)
	{
		$fields['ID'] = (int)$fields['ID'];
		$fields['NAME'] = (string)$fields['NAME'];
		$fields['ORDER_DISCOUNT_ID'] = $fields['ID'];
		self::$discountCache[$entity][$fields['DISCOUNT_HASH']] = $fields;
	}

	/**
	 * Validate coupon.
	 *
	 * @param array $fields		Coupon data.
	 * @return Result
	 */
	protected static function validateCoupon(array $fields)
	{
		$result = new Result();

		if (
			!static::isValidCouponTypeInternal($fields['TYPE'])
		)
		{
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage(
					'SALE_ORDER_DISCOUNT_ERR_COUPON_TYPE_BAD',
					array('#COUPON#' => $fields['COUPON'])
				),
				self::ERROR_ID
			));
		}

		if (empty($fields['COUPON_ID']) || (int)$fields['COUPON_ID'] <= 0)
		{
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage(
					'SALE_ORDER_DISCOUNT_ERR_COUPON_ID_BAD',
					array('#COUPON#' => $fields['COUPON'])
				),
				self::ERROR_ID
			));
		}

		return $result;
	}

	/**
	 * Add new coupon for order.
	 *
	 * @param array $fields			Tablet fields.
	 * @return Result|null
	 */
	protected static function addCoupon(array $fields)
	{
		$result = new Result();

		if (array_key_exists('ID', $fields))
			unset($fields['ID']);
		$tabletResult = static::addOrderCouponInternal($fields);
		if ($tabletResult->isSuccess())
		{
			$fields['ID'] = $tabletResult->getId();
			$result->setId($fields['ID']);
			$result->setData($fields);
		}
		else
		{
			$result->addErrors($tabletResult->getErrors());
		}
		unset($tabletResult);

		return $result;
	}

	/**
	 * Add new unique order discount.
	 *
	 * @param array $fields			Tablet fields.
	 * @param array $rawFields		Additional fields.
	 * @return Result|null
	 */
	protected static function addDiscount(array $fields, array $rawFields)
	{
		$result = new Result;

		$process = true;
		$orderDiscountId = null;

		$tabletResult = static::addOrderDiscountInternal($fields);
		if ($tabletResult->isSuccess())
		{
			$orderDiscountId = (int)$tabletResult->getId();
		}
		else
		{
			$process = false;
			$result->addErrors($tabletResult->getErrors());
		}
		unset($tabletResult);

		if ($process)
		{
			$moduleList = static::prepareDiscountModules($rawFields);
			if (!empty($moduleList))
			{
				$resultModule = static::saveOrderDiscountModulesInternal(
					$orderDiscountId,
					$moduleList
				);
				if (!$resultModule)
				{
					$process = false;
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_SAVE_DISCOUNT_MODULES'),
						self::ERROR_ID
					));
				}
				unset($resultModule);
			}
			unset($moduleList);
		}

		if ($process)
			$result->setId($orderDiscountId);

		return $result;
	}

	/**
	 * Load discount modules.
	 *
	 * @param array $discountIds	Order discount id list.
	 * @return array|null
	 */
	protected static function loadModulesFromDb(array $discountIds)
	{
		if (empty($discountIds))
			return null;

		Main\Type\Collection::normalizeArrayValuesByInt($discountIds, true);
		if (empty($discountIds))
			return null;

		$result = array();
		$iterator = static::getOrderDiscountModuleIterator(array(
			'select' => array('MODULE_ID', 'ORDER_DISCOUNT_ID'),
			'filter' => array('@ORDER_DISCOUNT_ID' => $discountIds)
		));
		while ($row = $iterator->fetch())
		{
			$orderDiscountId = (int)$row['ORDER_DISCOUNT_ID'];
			if (!isset($result[$orderDiscountId]))
				$result[$orderDiscountId] = array();
			$result[$orderDiscountId][] = $row['MODULE_ID'];
		}
		unset($row, $iterator);

		return (!empty($result) ? $result : null);
	}

	protected static function prepareDiscountModules(array $discount)
	{
		$result = array();
		$needDiscountModules = array();
		if (!empty($discount['MODULES']))
		{
			$needDiscountModules = (
				!is_array($discount['MODULES'])
				? array($discount['MODULES'])
				: $discount['MODULES']
			);
		}
		elseif (!empty($discount['HANDLERS']))
		{
			if (!empty($discount['HANDLERS']['MODULES']))
			{
				$needDiscountModules = (
					!is_array($discount['HANDLERS']['MODULES'])
					? array($discount['HANDLERS']['MODULES'])
					: $discount['HANDLERS']['MODULES']
				);
			}
		}
		if (!empty($needDiscountModules))
		{
			foreach ($needDiscountModules as &$module)
			{
				$module = trim((string)$module);
				if (!empty($module))
					$result[$module] = $module;
			}
			unset($module);
			$result = array_values($result);
		}
		return $result;
	}

	/**
	 * Returns entity code for discount and round results.
	 *
	 * @param array $row			Result row.
	 * @param array $transferList	Transfer table (for example, basket id to basket code).
	 * @return int|string
	 */
	protected static function transferEntityCodeFromInternal(array $row, array $transferList)
	{
		$code = '';
		if (empty($row))
			return $code;
		$row['ENTITY_VALUE'] = (string)$row['ENTITY_VALUE'];
		if (!empty($transferList))
		{
			if ($row['ENTITY_ID'] > 0 && isset($transferList[$row['ENTITY_ID']]))
				$code = $transferList[$row['ENTITY_ID']];
			elseif ($row['ENTITY_VALUE'] !== '' && isset($transferList[$row['ENTITY_VALUE']]))
				$code = $transferList[$row['ENTITY_VALUE']];
		}
		else
		{
			$code = ($row['ENTITY_ID'] > 0 ? $row['ENTITY_ID'] : $row['ENTITY_VALUE']);
		}
		return $code;
	}

	/**
	 * Format rule result for basket discount.
	 *
	 * @param array $rule			Rule result from database.
	 * @return array
	 */
	protected static function formatBasketRuleResult(array $rule)
	{
		$ruleResult = [
			'BASKET_ID' => $rule['ENTITY_ID'],
			'RULE_ID' => $rule['ID'],
			'ORDER_ID' => $rule['ORDER_ID'],
			'DISCOUNT_ID' => $rule['ORDER_DISCOUNT_ID'],
			'ORDER_COUPON_ID' => $rule['ORDER_COUPON_ID'],
			'COUPON_ID' => ($rule['ORDER_COUPON_ID'] > 0 ? $rule['COUPON_ID'] : ''),
			'RESULT' => ['APPLY' => $rule['APPLY']],
			'RULE_DESCR_ID' => $rule['RULE_DESCR_ID'],
			'ACTION_BLOCK_LIST' => (isset($rule['ACTION_BLOCK_LIST']) ? $rule['ACTION_BLOCK_LIST'] : null)
		];

		if (!empty($rule['RULE_DESCR']) && is_array($rule['RULE_DESCR']))
		{
			$ruleResult['RESULT']['DESCR_DATA'] = $rule['RULE_DESCR'];
			$ruleResult['RESULT']['DESCR'] = Discount\Formatter::formatList($rule['RULE_DESCR']);
			$ruleResult['DESCR_ID'] = $rule['RULE_DESCR_ID'];
		}

		return $ruleResult;
	}

	/**
	 * Format rule result for sale discount.
	 *
	 * @param array $rule			Rule result from database.
	 * @return array
	 */
	protected static function formatSaleRuleResult(array $rule)
	{
		return [
			'ORDER_ID' => $rule['ORDER_ID'],
			'DISCOUNT_ID' => $rule['ORDER_DISCOUNT_ID'],
			'ORDER_COUPON_ID' => $rule['ORDER_COUPON_ID'],
			'COUPON_ID' => ($rule['ORDER_COUPON_ID'] > 0 ? $rule['COUPON_ID'] : ''),
			'RESULT' => [],
			'ACTION_BLOCK_LIST' => true
		];
	}

	/**
	 * Format rule item result for sale discount.
	 *
	 * @param array $rule			Rule result from database.
	 * @return array
	 */
	protected static function formatSaleItemRuleResult(array $rule)
	{
		$ruleItem = array(
			'RULE_ID' => $rule['ID'],
			'APPLY' => $rule['APPLY'],
			'RULE_DESCR_ID' => $rule['RULE_DESCR_ID'],
			'ACTION_BLOCK_LIST' => (
				!empty($rule['ACTION_BLOCK_LIST']) && is_array($rule['ACTION_BLOCK_LIST'])
				? $rule['ACTION_BLOCK_LIST']
				: null
			)
		);
		if (!empty($rule['RULE_DESCR']) && is_array($rule['RULE_DESCR']))
		{
			$ruleItem['DESCR_DATA'] = $rule['RULE_DESCR'];
			$ruleItem['DESCR'] = Discount\Formatter::formatList($rule['RULE_DESCR']);
			$ruleItem['DESCR_ID'] = $rule['RULE_DESCR_ID'];
		}

		return $ruleItem;
	}

	/**
	 * Fill product fields in rule result.
	 *
	 * @param array &$result			Rule result.
	 * @param array $basketData			Basket data.
	 * @param int|string $index			Basket index.
	 * @return void
	 */
	protected static function fillRuleProductFields(array &$result, array $basketData, $index)
	{
		if (!empty($basketData[$index]))
		{
			$result['MODULE'] = $basketData[$index]['MODULE'];
			$result['PRODUCT_ID'] = $basketData[$index]['PRODUCT_ID'];
		}
	}

	/* discounts */

	/**
	 * Discount getList (prototype).
	 *
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getDiscountIterator(array $parameters)
	{
		return null;
	}

	/* discounts end */

	/* coupons */

	/**
	 * Check coupon type.
	 *
	 * @param int $type		Coupon type.
	 * @return bool
	 */
	protected static function isValidCouponTypeInternal($type)
	{
		return false;
	}

	/* coupons end */

	/* order discounts */

	/**
	 * Order discount getList (prototype).
	 *
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getOrderDiscountIterator(array $parameters)
	{
		return null;
	}

	/**
	 * Low-level method add new discount for order (prototype).
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addOrderDiscountInternal(array $fields)
	{
		return null;
	}

	/**
	 * Returns the list of missing discount fields (prototype).
	 *
	 * @param array $fields		Discount fields.
	 * @return array
	 */
	protected static function checkRequiredOrderDiscountFields(array $fields)
	{
		return [];
	}

	/**
	 * Clear raw order discount data (prototype).
	 *
	 * @param array $rawFields	Discount information.
	 * @return array|null
	 */
	protected static function normalizeOrderDiscountFieldsInternal(array $rawFields)
	{
		return null;
	}

	/**
	 * Calculate order discount hash (prototype).
	 *
	 * @param array $fields		Discount information.
	 * @return string|null
	 */
	protected static function calculateOrderDiscountHashInternal(array $fields)
	{
		return null;
	}

	/* order discounts end */

	/* order coupons */
	/**
	 * Order coupons getList (prototype).
	 *
	 * @param array $parameters \Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	public static function getOrderCouponIterator(array $parameters)
	{
		return null;
	}

	/**
	 * Low-level method add new coupon for order (prototype).
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addOrderCouponInternal(array $fields)
	{
		return null;
	}

	/**
	 * Load coupons for order.
	 *
	 * @param int $order		Order id.
	 * @return array
	 */
	protected static function loadCouponsFromDb($order)
	{
		$result = array();

		$couponIterator = static::getOrderCouponIterator(array(
			'select' => array('*'),
			'filter' => array('=ORDER_ID' => $order),
			'order' => array('ID' => 'ASC')
		));
		while ($coupon = $couponIterator->fetch())
		{
			$coupon['ID'] = (int)$coupon['ID'];
			$coupon['ORDER_ID'] = (int)$coupon['ORDER_ID'];
			$coupon['ORDER_DISCOUNT_ID'] = (int)$coupon['ORDER_DISCOUNT_ID'];
			$result[$coupon['COUPON']] = $coupon;
		}
		unset($coupon, $couponIterator);

		return $result;
	}

	/* order coupons end */

	/* order discount modules */

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getOrderDiscountModuleIterator(array $parameters)
	{
		return null;
	}

	/**
	 * Low-level method save order discount modules.
	 *
	 * @param int $orderDiscountId
	 * @param array $modules
	 * @return bool
	 */
	protected static function saveOrderDiscountModulesInternal($orderDiscountId, array $modules)
	{
		return false;
	}

	/* order discount modules end */

	/* discount results */

	/**
	 * Converts the discount result entity identifier to the database table format (prototype).
	 *
	 * @param string $entity
	 * @return null|int
	 */
	protected static function getResultEntityInternal($entity)
	{
		return null;
	}

	/**
	 * Converts the discount result entity identifier from the database table format (prototype).
	 *
	 * @param int $entity
	 * @return null|string
	 */
	protected static function getResultEntityFromInternal($entity)
	{
		return null;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getResultIterator(array $parameters)
	{
		return null;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getResultDescriptionIterator(array $parameters)
	{
		return null;
	}

	/**
	 * Low-level method add new result discount for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addResultRow(array $fields)
	{
		if (array_key_exists('ID', $fields))
			unset($fields['ID']);
		$resultFields = static::checkResultTableWhiteList($fields);
		if (empty($resultFields))
		{
			$result = new Main\Entity\AddResult();
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_RESULT_ROW_IS_EMPTY')
			));
			return $result;
		}
		$result = static::addResultInternal($resultFields);
		unset($resultFields);
		if (!$result->isSuccess())
			return $result;

		$fields['RULE_ID'] = (int)$result->getId();
		$descriptionFields = static::checkResultDescriptionTableWhiteList($fields);
		if (empty($descriptionFields))
		{
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage('SALE_ORDER_DISCOUNT_ERR_RESULT_ROW_DESCRIPTION_IS_EMPTY')
			));
			return $result;
		}
		$descriptionResult = static::addResultDescriptionInternal($descriptionFields);
		unset($descriptionFields);
		if (!$descriptionResult->isSuccess())
			$result->addErrors($descriptionResult->getErrors());
		unset($descriptionResult);

		return $result;
	}

	/**
	 * Low-level method update result discount for order.
	 *
	 * @param int $id			Tablet row id.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateResultRow($id, array $fields)
	{
		$rowUpdate = ['APPLY' => $fields['APPLY']];
		if (isset($fields['ACTION_BLOCK_LIST']))
			$rowUpdate['ACTION_BLOCK_LIST'] = $fields['ACTION_BLOCK_LIST'];
		$result = static::updateResultInternal($id, $rowUpdate);
		unset($rowUpdate);
		if (!$result->isSuccess())
			return $result;

		$descrId = null;
		if (isset($fields['DESCR_ID']) && $fields['DESCR_ID'] > 0)
			$descrId = $fields['DESCR_ID'];
		if ($descrId === null)
		{
			$iterator = static::getResultDescriptionIterator([
				'select' => ['ID'],
				'filter' => ['=RULE_ID' => $id]
			]);
			$row = $iterator->fetch();
			if (!empty($row['ID']))
				$descrId = (int)$row['ID'];
			unset($row, $iterator);
		}
		if ($descrId === null)
		{
			$iterator = static::getResultIterator([
				'select' => ['MODULE_ID', 'ORDER_DISCOUNT_ID', 'ORDER_ID'],
				'filter' => ['=ID' => $id],
				'order' => []
			]);
			$row = $iterator->fetch();
			unset($iterator);
			$row['RULE_ID'] = $id;
			$row['DESCR'] = $fields['DESCR'];
			$resultDescr = static::addResultDescriptionInternal($row);
			unset($row);
		}
		else
		{
			$resultDescr = static::updateResultDescriptionInternal(
				$fields['DESCR_ID'],
				array('DESCR' => $fields['DESCR'])
			);
		}

		if (!$resultDescr->isSuccess())
			$result->addErrors($resultDescr->getErrors());
		unset($resultDescr);

		return $result;
	}

	/**
	 * Low-level method returns result table name (prototype).
	 *
	 * @return string|null
	 */
	protected static function getResultTableNameInternal()
	{
		return null;
	}

	/**
	 * Low-level method returns result description table name (prototype).
	 *
	 * @return string|null
	 */
	protected static function getResultDescriptionTableNameInternal()
	{
		return null;
	}

	/**
	 * Low-level method returns only those fields that are in the result table (prototype).
	 *
	 * @param array $fields
	 * @return array|null
	 */
	protected static function checkResultTableWhiteList(array $fields)
	{
		return null;
	}

	/**
	 * Low-level method returns only those fields that are in the result description table (prototype).
	 *
	 * @param array $fields
	 * @return array|null
	 */
	protected static function checkResultDescriptionTableWhiteList(array $fields)
	{
		return null;
	}

	/**
	 * Low-level method add new result discount for order (prototype).
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addResultInternal(array $fields)
	{
		return null;
	}

	/**
	 * Low-level method add new result description for order (prototype).
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addResultDescriptionInternal(array $fields)
	{
		return null;
	}

	/**
	 * Low-level method update result discount for order (prototype).
	 *
	 * @param int $id			Primary key.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateResultInternal($id, array $fields)
	{
		return null;
	}

	/**
	 * Low-level method update result description for order.
	 *
	 * @param int $id			Primary key.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateResultDescriptionInternal($id, array $fields)
	{
		return null;
	}

	/* discount results end */

	/* round result */

	/**
	 * Converts the rounded entity identifier to the database table format (prototype).
	 *
	 * @param string $entity
	 * @return null|int
	 */
	protected static function getRoundEntityInternal($entity)
	{
		return null;
	}

	/**
	 * Converts the rounded entity identifier from the database table format (prototype).
	 *
	 * @param int $entity
	 * @return null|string
	 */
	protected static function getRoundEntityFromInternal($entity)
	{
		return null;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getRoundResultIterator(array $parameters)
	{
		return null;
	}

	/**
	 * Low-level method add new round result for order (prototype).
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addRoundResultInternal(array $fields)
	{
		return null;
	}

	/**
	 * Low-level method update round result for order (prototype).
	 *
	 * @param int $id			Tablet row id.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateRoundResultInternal($id, array $fields)
	{
		return null;
	}

	/**
	 * low-level method returns the round-results table name (prototype).
	 *
	 * @return string|null
	 */
	protected static function getRoundTableNameInternal()
	{
		return null;
	}

	/* round result end */

	/* data storage */

	/**
	 * Low-level method for convert storage types to internal format.
	 *
	 * @param string $storageType	Abstract storage type.
	 * @return int|null
	 */
	protected static function getStorageTypeInternal($storageType)
	{
		return null;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getStoredDataIterator(array $parameters)
	{
		return null;
	}

	/**
	 * Low-level method add stored data for order (prototype).
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addStoredDataInternal(array $fields)
	{
		return null;
	}

	/**
	 * Low-level method update stored data for order (prototype).
	 *
	 * @param int $id			Tablet row id.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateStoredDataInternal($id, array $fields)
	{
		return null;
	}

	/**
	 * low-level method returns the order stored data table name (prototype).
	 *
	 * @return string|null
	 */
	protected static function getStoredDataTableInternal()
	{
		return null;
	}

	/* data storage end */

	/**
	 * Return flag simple action in discount.
	 *
	 * @internal
	 * @param string $action		Discount action.
	 * @return bool
	 */
	private static function isSimpleAction($action)
	{
		$result = true;

		$action = (string)$action;
		if ($action == '')
			return $result;

		$action = trim(substr($action, 8));
		$action = substr($action, 2);
		$key = strpos($action, ')');
		if ($key === false)
			return $result;
		$orderName = '\\'.substr($action, 0, $key);

		preg_match_all("/".$orderName."(?:,|\))/".BX_UTF_PCRE_MODIFIER, $action, $list);
		if (isset($list[0]) && is_array($list[0]))
			$result = count($list[0]) <= 2;

		return $result;
	}

	/**
	 * Remove old result rows without events.
	 *
	 * @param string $tableName
	 * @param string $indexField
	 * @param array $ids
	 * @throws Main\Db\SqlQueryException
	 */
	private static function deleteRowsByIndex($tableName, $indexField, array $ids)
	{
		$tableName = (string)$tableName;
		if ($tableName === '')
			return;
		$indexField = (string)$indexField;
		if ($indexField === '')
			return;

		if (empty($ids))
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($ids, true);
		if (empty($ids))
			return;

		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();

		$query = 'delete from '.$helper->quote($tableName).' where '.$helper->quote($indexField);
		foreach (array_chunk($ids, 500) as $page)
		{
			$conn->queryExecute($query.' in ('.implode(', ', $page).')');
		}
		unset($page, $query);

		unset($helper, $conn);
	}

	private static function getEntityIndex(array $row)
	{
		return (isset($row['ENTITY_ID']) ? $row['ENTITY_ID'] : $row['ENTITY_VALUE']);
	}
}