<?php
namespace Bitrix\Sale\Discount;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

class Actions
{
	const VALUE_TYPE_FIX = 'F';
	const VALUE_TYPE_PERCENT = 'P';
	const VALUE_TYPE_SUMM = 'S';
	const VALUE_TYPE_CLOSEOUT = 'C';

	const GIFT_SELECT_TYPE_ONE = 'one';
	const GIFT_SELECT_TYPE_ALL = 'all';

	const BASKET_APPLIED_FIELD = 'DISCOUNT_APPLIED';

	const VALUE_EPS = 1E-5;

	const MODE_CALCULATE = 0x0001;
	const MODE_MANUAL = 0x0002;
	const MODE_MIXED = 0x0004;

	const APPLY_COUNTER_START = -1;

	const PERCENT_FROM_CURRENT_PRICE = 0x0001;
	const PERCENT_FROM_BASE_PRICE = 0x0002;

	const RESULT_ENTITY_BASKET = 0x0001;
	const RESULT_ENTITY_DELIVERY = 0x0002;
	const RESULT_ENTITY_ORDER = 0x0004;

	const APPLY_RESULT_MODE_COUNTER = 0x0001;
	const APPLY_RESULT_MODE_DESCR = 0x0002;
	const APPLY_RESULT_MODE_SIMPLE = 0x0004;

	const ACTION_TYPE_DISCOUNT = 'D';
	const ACTION_TYPE_EXTRA = 'E';

	protected static $useMode = self::MODE_CALCULATE;
	protected static $applyCounter = self::APPLY_COUNTER_START;
	protected static $actionResult = array();
	protected static $actionDescription = array();
	protected static $applyResult = array();
	protected static $applyResultMode = self::APPLY_RESULT_MODE_COUNTER;
	protected static $storedData = array();

	protected static $useBasketFilter = true;

	protected static $percentValueMode = self::PERCENT_FROM_CURRENT_PRICE;
	protected static $currencyId = '';
	protected static $siteId = '';

	private static $compatibleBasketFields = array('DISCOUNT_PRICE', 'PRICE', 'VAT_VALUE', 'PRICE_DEFAULT');

	/**
	 * Check for zero value.
	 *
	 * @param float|int $value Price or discount value.
	 * @return float|int
	 */
	public static function roundZeroValue($value)
	{
		return (abs($value) <= self::VALUE_EPS ? 0 : $value);
	}

	/**
	 * Rounded value with sale rules.
	 *
	 * @param float|int $value Value.
	 * @param string $currency Currency.
	 * @return float
	 */
	public static function roundValue($value, /** @noinspection PhpUnusedParameterInspection */ $currency)
	{
		/** @noinspection PhpInternalEntityUsedInspection */
		return Sale\PriceMaths::roundPrecision($value);
	}

	/**
	 * Set use actions mode.
	 *
	 * @param int $mode Use mode.
	 * @param array $config Config.
	 * @return void
	 */
	public static function setUseMode($mode, array $config = array())
	{
		$mode = (int)$mode;
		if ($mode !== self::MODE_CALCULATE && $mode !== self::MODE_MANUAL && $mode !== self::MODE_MIXED)
			return;
		self::$useMode = $mode;
		switch (self::$useMode)
		{
			case self::MODE_CALCULATE:
				$percentOption = (string)Main\Config\Option::get('sale', 'get_discount_percent_from_base_price');
				self::$percentValueMode = ($percentOption == 'Y' ? self::PERCENT_FROM_BASE_PRICE : self::PERCENT_FROM_CURRENT_PRICE);
				unset($percentOption);
				if (isset($config['CURRENCY']))
					self::$currencyId = $config['CURRENCY'];
				if (isset($config['SITE_ID']))
				{
					self::$siteId = $config['SITE_ID'];
					if (self::$currencyId == '')
						self::$currencyId = Sale\Internals\SiteCurrencyTable::getSiteCurrency(self::$siteId);
				}
				break;
			case self::MODE_MANUAL:
			case self::MODE_MIXED:
				$percentOption = '';
				if (isset($config['USE_BASE_PRICE']))
					$percentOption = $config['USE_BASE_PRICE'];
				if ($percentOption == '')
					$percentOption = (string)Main\Config\Option::get('sale', 'get_discount_percent_from_base_price');
				self::$percentValueMode = ($percentOption == 'Y' ? self::PERCENT_FROM_BASE_PRICE : self::PERCENT_FROM_CURRENT_PRICE);
				unset($percentOption);
				if (isset($config['CURRENCY']))
					self::$currencyId = $config['CURRENCY'];
				if (isset($config['SITE_ID']))
				{
					self::$siteId = $config['SITE_ID'];
					if (self::$currencyId == '')
						self::$currencyId = Sale\Internals\SiteCurrencyTable::getSiteCurrency(self::$siteId);
				}
				break;
		}
		static::clearApplyCounter();
		static::enableBasketFilter();
	}

	/**
	 * Returns current use actions mode.
	 *
	 * @return int
	 */
	public static function getUseMode()
	{
		return self::$useMode;
	}

	/**
	 * Check calculate mode.
	 *
	 * @return bool
	 */
	public static function isCalculateMode()
	{
		return self::$useMode === self::MODE_CALCULATE;
	}

	/**
	 * Check manual mode.
	 *
	 * @return bool
	 */
	public static function isManualMode()
	{
		return self::$useMode === self::MODE_MANUAL;
	}

	/**
	 * Check mixed mode.
	 *
	 * @return bool
	 */
	public static function isMixedMode()
	{
		return self::$useMode === self::MODE_MIXED;
	}

	/**
	 * Check current use actions mode.
	 *
	 * @param array $list
	 * @return bool
	 */
	public static function checkUseMode(array $list)
	{
		return (in_array(self::$useMode, $list, true));
	}

	/**
	 * Return calculate mode for percent discount.
	 *
	 * @return int
	 */
	public static function getPercentMode()
	{
		return self::$percentValueMode;
	}

	/**
	 * Return calculate currency.
	 *
	 * @return string
	 */
	public static function getCurrency()
	{
		return self::$currencyId;
	}

	/**
	 * Clear apply counter.
	 *
	 * @return void
	 */
	public static function clearApplyCounter()
	{
		self::$applyCounter = self::APPLY_COUNTER_START;
	}

	/**
	 * Return current apply counter.
	 *
	 * @return int
	 */
	public static function getApplyCounter()
	{
		return self::$applyCounter;
	}

	/**
	 * Increment current apply counter. Use BEFORE discount action apply.
	 *
	 * @return void
	 */
	public static function increaseApplyCounter()
	{
		self::$applyCounter++;
	}

	/**
	 * Disable basket filter for mixed apply mode.
	 *
	 * @return void
	 */
	public static function disableBasketFilter()
	{
		if (!static::isMixedMode())
			return;
		self::$useBasketFilter = false;
	}

	/**
	 * Enable basket filter for mixed apply mode.
	 *
	 * @return void
	 */
	public static function enableBasketFilter()
	{
		if (!static::isMixedMode())
			return;
		self::$useBasketFilter = true;
	}

	/**
	 * Return is enabled basket filter mixed apply mode.
	 *
	 * @return bool
	 */
	public static function usedBasketFilter()
	{
		return self::$useBasketFilter;
	}

	/**
	 * Fill compatible fields for old public api.
	 *
	 * @param array &$order Order data.
	 * @return void
	 */
	public static function fillCompatibleFields(array &$order)
	{
		$adminSection = Main\Context::getCurrent()->getRequest()->isAdminSection();
		if (empty($order) || !is_array($order))
			return;
		if (!empty($order['BASKET_ITEMS']) && is_array($order['BASKET_ITEMS']))
		{
			foreach ($order['BASKET_ITEMS'] as &$item)
			{
				if (isset($item['PRICE_DEFAULT']))
					$item['PRICE_DEFAULT'] = $item['PRICE'];
				if ($adminSection)
					continue;

				foreach (self::$compatibleBasketFields as &$fieldName)
				{
					if (array_key_exists($fieldName, $item) && !is_array($item[$fieldName]))
						$item['~'.$fieldName] = $item[$fieldName];
				}
				unset($fieldName);
			}
			unset($item);
		}
	}

	/**
	 * Basket filter.
	 *
	 * @param array $item Basket item.
	 * @return bool
	 */
	public static function filterBasketForAction(array $item)
	{
		return (
			(!isset($item['CUSTOM_PRICE']) || $item['CUSTOM_PRICE'] != 'Y') &&
			(
				(isset($item['TYPE']) && (int)$item['TYPE'] == Sale\BasketItem::TYPE_SET) ||
				(!isset($item['SET_PARENT_ID']) || (int)$item['SET_PARENT_ID'] <= 0)
			) &&
			(!isset($item['ITEM_FIX']) || $item['ITEM_FIX'] != 'Y') &&
			(!isset($item['LAST_DISCOUNT']) || $item['LAST_DISCOUNT'] != 'Y') &&
			(!isset($item['IN_SET']) || $item['IN_SET'] != 'Y')
		);
	}

	/**
	 * Return all actions description.
	 *
	 * @return array
	 */
	public static function getActionDescription()
	{
		return self::$actionDescription;
	}

	/**
	 * Return all actions results.
	 *
	 * @return array
	 */
	public static function getActionResult()
	{
		return self::$actionResult;
	}

	/**
	 * Set apply result format mode.
	 *
	 * @param int $mode			Apply result mode.
	 * @return void
	 */
	public static function setApplyResultMode($mode)
	{
		$mode = (int)$mode;
		if ($mode != self::APPLY_RESULT_MODE_COUNTER && $mode != self::APPLY_RESULT_MODE_DESCR && $mode != self::APPLY_RESULT_MODE_SIMPLE)
			return;
		self::$applyResultMode = $mode;
		self::$applyResult = array();
	}

	/**
	 * Return apply result format mode.
	 *
	 * @return int
	 */
	public static function getApplyResultMode()
	{
		return self::$applyResultMode;
	}

	/**
	 * Set apply result list.
	 *
	 * @param array $applyResult Apply data.
	 * @return void
	 */
	public static function setApplyResult(array $applyResult)
	{
		self::$applyResult = $applyResult;
	}

	/**
	 * Fill data to store for discount.
	 *
	 * @param array $data   Data.
	 * @return void
	 */
	public static function setStoredData(array $data)
	{
		self::$storedData = $data;
	}

	/**
	 * Return stored data after discount calculation.
	 *
	 * @return array
	 */
	public static function getStoredData()
	{
		return self::$storedData;
	}

	/**
	 * Fill action data to store.
	 *
	 * @param array $data   Action data to store.
	 * @return void
	 */
	public static function setActionStoredData(array $data)
	{
		if (!static::isCalculateMode())
			return;
		if (empty($data))
			return;
		self::$storedData[static::getApplyCounter()] = $data;
	}

	/**
	 * Return stored action.
	 *
	 * @return array|null
	 */
	public static function getActionStoredData()
	{
		$counter = static::getApplyCounter();
		if (isset(self::$storedData[$counter]))
			return self::$storedData[$counter];
		return null;
	}

	/**
	 * Clear actions description and result.
	 *
	 * @return void
	 */
	public static function clearAction()
	{
		self::clearApplyCounter();
		self::$applyResult = array();
		self::$actionResult = array();
		self::$actionDescription = array();
		self::$storedData = array();
	}

	/**
	 * Basket action.
	 *
	 * @param array &$order Order data.
	 * @param array $action Action detail
	 *    keys are case sensitive:
	 *        <ul>
	 *        <li>float|int VALUE                Discount value.
	 *        <li>char UNIT                    Discount type.
	 *        <li>string CURRENCY                Currency discount (optional).
	 *        <li>char MAX_BOUND                Max bound (optional).
	 *        </ul>.
	 * @param callable $filter Filter for basket items.
	 * @return void
	 */
	public static function applyToBasket(array &$order, array $action, $filter)
	{
		static::increaseApplyCounter();

		if (!isset($action['VALUE']) || !isset($action['UNIT']))
			return;

		$orderCurrency = static::getCurrency();
		$value = (float)$action['VALUE'];
		$limitValue = (int)$action['LIMIT_VALUE'];
		$unit = (string)$action['UNIT'];
		$currency = (isset($action['CURRENCY']) ? $action['CURRENCY'] : $orderCurrency);
		$maxBound = false;
		if ($unit == self::VALUE_TYPE_FIX && $value < 0)
			$maxBound = (isset($action['MAX_BOUND']) && $action['MAX_BOUND'] == 'Y');
		$valueAction = (
			$value < 0
			? Formatter::VALUE_ACTION_DISCOUNT
			: Formatter::VALUE_ACTION_EXTRA
		);

		$actionDescription = array(
			'ACTION_TYPE' => Formatter::TYPE_VALUE,
			'VALUE' => abs($value),
			'VALUE_ACTION' => $valueAction
		);
		switch ($unit)
		{
			case self::VALUE_TYPE_SUMM:
				$actionDescription = [
					'ACTION_TYPE' => Formatter::TYPE_VALUE,
					'VALUE' => abs($value),
					'VALUE_ACTION' => ($value < 0 ? Formatter::VALUE_ACTION_DISCOUNT : Formatter::VALUE_ACTION_EXTRA),
					'VALUE_TYPE' => Formatter::VALUE_TYPE_SUMM,
					'VALUE_UNIT' => $currency
				];
				break;
			case self::VALUE_TYPE_PERCENT:
				$actionDescription = [
					'ACTION_TYPE' => Formatter::TYPE_VALUE,
					'VALUE' => abs($value),
					'VALUE_ACTION' => ($value < 0 ? Formatter::VALUE_ACTION_DISCOUNT : Formatter::VALUE_ACTION_EXTRA),
					'VALUE_TYPE' => Formatter::VALUE_TYPE_PERCENT
				];
				break;
			case self::VALUE_TYPE_FIX:
				$actionDescription = [
					'ACTION_TYPE' => ($maxBound ? Formatter::TYPE_MAX_BOUND : Formatter::TYPE_VALUE),
					'VALUE' => abs($value),
					'VALUE_ACTION' => ($value < 0 ? Formatter::VALUE_ACTION_DISCOUNT : Formatter::VALUE_ACTION_EXTRA),
					'VALUE_TYPE' => Formatter::VALUE_TYPE_CURRENCY,
					'VALUE_UNIT' => $currency
				];
				break;
			case self::VALUE_TYPE_CLOSEOUT:
				$actionDescription = [
					'ACTION_TYPE' => Formatter::TYPE_FIXED,
					'VALUE' => abs($value),
					'VALUE_ACTION' => Formatter::VALUE_ACTION_DISCOUNT,
					'VALUE_TYPE' => Formatter::VALUE_TYPE_CURRENCY,
					'VALUE_UNIT' => $currency
				];
				break;
			default:
				return;
				break;
		}
		$valueAction = $actionDescription['VALUE_ACTION'];

		if(!empty($limitValue))
		{
			$actionDescription['ACTION_TYPE'] = Formatter::TYPE_LIMIT_VALUE;
			$actionDescription['LIMIT_TYPE'] = Formatter::LIMIT_MAX;
			$actionDescription['LIMIT_UNIT'] = $orderCurrency;
			$actionDescription['LIMIT_VALUE'] = $limitValue;
		}

		static::setActionDescription(self::RESULT_ENTITY_BASKET, $actionDescription);

		if (empty($order['BASKET_ITEMS']) || !is_array($order['BASKET_ITEMS']))
			return;

		static::enableBasketFilter();
		$filteredBasket = static::getBasketForApply($order['BASKET_ITEMS'], $filter, $action);
		if (empty($filteredBasket))
			return;

		$applyBasket = array_filter($filteredBasket, '\Bitrix\Sale\Discount\Actions::filterBasketForAction');
		unset($filteredBasket);
		if (empty($applyBasket))
			return;

		if ($unit == self::VALUE_TYPE_SUMM || $unit == self::VALUE_TYPE_FIX)
		{
			if ($currency != $orderCurrency)
				$value = \CCurrencyRates::ConvertCurrency($value, $currency, $orderCurrency);
			if ($unit == self::VALUE_TYPE_SUMM)
			{
				$value = static::getPercentByValue($applyBasket, $value);
				if (
					($valueAction == Formatter::VALUE_ACTION_DISCOUNT && ($value >= 0 || $value < -100))
					||
					($valueAction == Formatter::VALUE_ACTION_EXTRA && $value <= 0)
				)
					return;
				$unit = self::VALUE_TYPE_PERCENT;
			}
		}
		$value = static::roundZeroValue($value);
		if ($value == 0)
			return;

		foreach ($applyBasket as $basketCode => $basketRow)
		{
			list($calculateValue, $result) = self::calculateDiscountPrice(
				$value,
				$unit,
				$basketRow,
				$limitValue,
				$maxBound
			);
			if ($result >= 0)
			{
				self::fillDiscountPrice($basketRow, $result, -$calculateValue);

				$order['BASKET_ITEMS'][$basketCode] = $basketRow;

				$rowActionDescription = $actionDescription;
				$rowActionDescription['BASKET_CODE'] = $basketCode;
				$rowActionDescription['RESULT_VALUE'] = abs($calculateValue);
				$rowActionDescription['RESULT_UNIT'] = $orderCurrency;

				if(!empty($limitValue))
				{
					$rowActionDescription['ACTION_TYPE'] = Formatter::TYPE_LIMIT_VALUE;
					$rowActionDescription['LIMIT_TYPE'] = Formatter::LIMIT_MAX;
					$rowActionDescription['LIMIT_UNIT'] = $orderCurrency;
					$rowActionDescription['LIMIT_VALUE'] = $limitValue;
				}

				static::setActionResult(self::RESULT_ENTITY_BASKET, $rowActionDescription);
				unset($rowActionDescription);
			}
			unset($result);
		}
		unset($basketCode, $basketRow);
	}

	/**
	 * Cumulative action.
	 *
	 * @param array &$order				Order data.
	 * @param array $ranges
	 * @param array $configuration
	 * @param callable|null $filter
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function applyCumulativeToBasket(array &$order, array $ranges, array $configuration = array(), $filter = null)
	{
		static::increaseApplyCounter();

		Main\Type\Collection::sortByColumn($ranges, 'sum');

		$sumConfiguration = $configuration['sum']?: array();
		$applyIfMoreProfitable = $configuration['apply_if_more_profitable'] === 'Y';

		if (static::checkUseMode(array(self::MODE_MANUAL, self::MODE_MIXED)))
		{
			$actionStoredData = self::getActionStoredData();
			$cumulativeOrderUserValue = $actionStoredData['cumulative_value'];
		}
		else
		{
			$cumulativeCalculator = new CumulativeCalculator((int)$order['USER_ID'], $order['SITE_ID']);
			$cumulativeCalculator->setSumConfiguration(
				array(
					'type_sum_period' => $sumConfiguration['type_sum_period'],
					'sum_period_data' => array(
						'order_start' => $sumConfiguration['sum_period_data']['discount_sum_order_start'],
						'order_end' => $sumConfiguration['sum_period_data']['discount_sum_order_end'],
						'period_value' => $sumConfiguration['sum_period_data']['discount_sum_period_value'],
						'period_type' => $sumConfiguration['sum_period_data']['discount_sum_period_type'],
					),
				)
			);
			$cumulativeOrderUserValue = $cumulativeCalculator->calculate();
		}

		$rangeToApply = null;
		foreach ($ranges as $range)
		{
			if ($cumulativeOrderUserValue >= $range['sum'])
			{
				$rangeToApply = $range;
			}
		}

		if (!$rangeToApply)
		{
			return;
		}

		$action = array(
			'VALUE' => -$rangeToApply['value'],
			'UNIT' => $rangeToApply['type'],
		);

		if (!isset($action['VALUE']) || !isset($action['UNIT']))
			return;

		$orderCurrency = static::getCurrency();
		$value = (float)$action['VALUE'];
		$limitValue = 0;
		$unit = (string)$action['UNIT'];
		$currency = (string)($action['CURRENCY'] ?? $orderCurrency);
		$maxBound = false;
		if ($unit == self::VALUE_TYPE_FIX && $value < 0)
			$maxBound = (isset($action['MAX_BOUND']) && $action['MAX_BOUND'] == 'Y');
		$valueAction = Formatter::VALUE_ACTION_CUMULATIVE;

		$actionDescription = array(
			'ACTION_TYPE' => Formatter::TYPE_VALUE,
			'VALUE' => abs($value),
			'VALUE_ACTION' => $valueAction
		);
		switch ($unit)
		{
			case self::VALUE_TYPE_PERCENT:
				$actionDescription['VALUE_TYPE'] = Formatter::VALUE_TYPE_PERCENT;
				break;
			case self::VALUE_TYPE_FIX:
				$actionDescription['VALUE_TYPE'] = Formatter::VALUE_TYPE_CURRENCY;
				$actionDescription['VALUE_UNIT'] = $currency;
				if ($maxBound)
					$actionDescription['ACTION_TYPE'] = Formatter::TYPE_MAX_BOUND;
				break;
			default:
				return;
		}

		if ($unit == self::VALUE_TYPE_FIX && $currency != $orderCurrency)
		{
			$value = \CCurrencyRates::ConvertCurrency($value, $currency, $orderCurrency);
		}

		$value = static::roundZeroValue($value);
		if ($value == 0)
		{
			return;
		}

		if(!empty($limitValue))
		{
			$actionDescription['ACTION_TYPE'] = Formatter::TYPE_LIMIT_VALUE;
			$actionDescription['LIMIT_TYPE'] = Formatter::LIMIT_MAX;
			$actionDescription['LIMIT_UNIT'] = $orderCurrency;
			$actionDescription['LIMIT_VALUE'] = $limitValue;
		}

		static::setActionDescription(self::RESULT_ENTITY_BASKET, $actionDescription);

		if (empty($order['BASKET_ITEMS']) || !is_array($order['BASKET_ITEMS']))
			return;

		static::enableBasketFilter();

		if ($applyIfMoreProfitable)
		{
			if ($filter === null)
			{
				$filter = function(){
					return true;
				};
			}
			$filter = self::wrapFilterToFindMoreProfitableForCumulative($filter, $unit, $value, $limitValue, $maxBound);
		}

		$filteredBasket = static::getBasketForApply($order['BASKET_ITEMS'], $filter, $action);
		if (empty($filteredBasket))
			return;


		$applyBasket = array_filter($filteredBasket, '\Bitrix\Sale\Discount\Actions::filterBasketForAction');
		unset($filteredBasket);
		if (empty($applyBasket))
			return;

		foreach ($applyBasket as $basketCode => $basketRow)
		{
			if ($applyIfMoreProfitable)
			{
				$basketRow['PRICE'] = $basketRow['BASE_PRICE'];
				$basketRow['DISCOUNT_PRICE'] = 0;
			}

			list($calculateValue, $result) = self::calculateDiscountPrice(
				$value,
				$unit,
				$basketRow,
				$limitValue,
				$maxBound
			);
			if ($result >= 0)
			{
				self::fillDiscountPrice($basketRow, $result, -$calculateValue);

				$order['BASKET_ITEMS'][$basketCode] = $basketRow;

				$rowActionDescription = $actionDescription;
				$rowActionDescription['BASKET_CODE'] = $basketCode;
				$rowActionDescription['RESULT_VALUE'] = abs($calculateValue);
				$rowActionDescription['RESULT_UNIT'] = $orderCurrency;

				if(!empty($limitValue))
				{
					$rowActionDescription['ACTION_TYPE'] = Formatter::TYPE_LIMIT_VALUE;
					$rowActionDescription['LIMIT_TYPE'] = Formatter::LIMIT_MAX;
					$rowActionDescription['LIMIT_UNIT'] = $orderCurrency;
					$rowActionDescription['LIMIT_VALUE'] = $limitValue;
				}

				if ($applyIfMoreProfitable)
				{
					//TODO: remove this hack
					//revert apply on affected basket items
					$rowActionDescription['REVERT_APPLY'] = true;
				}

				static::setActionResult(self::RESULT_ENTITY_BASKET, $rowActionDescription);
				unset($rowActionDescription);
			}
			unset($result);
		}
		unset($basketCode, $basketRow);

		if (self::getUseMode() == self::MODE_CALCULATE)
		{
			self::setActionStoredData(
				array(
					'cumulative_value' => $cumulativeOrderUserValue,
				)
			);
		}
	}

	private static function wrapFilterToFindMoreProfitableForCumulative($filter, $unit, $value, $limitValue, $maxBound)
	{
		if (!is_callable($filter))
		{
			return null;
		}

		return function($basketItem) use ($filter, $unit, $value, $limitValue, $maxBound) {
			if (empty($basketItem['BASE_PRICE']))
			{
				return false;
			}

			if (empty($basketItem['DISCOUNT_PRICE']))
			{
				return true;
			}

			if (!$filter($basketItem))
			{
				return false;
			}

			$prevPrice = $basketItem['PRICE'];
			$basketItem['PRICE'] = $basketItem['BASE_PRICE'];
			list(, $newPrice) = self::calculateDiscountPrice(
				$value,
				$unit,
				$basketItem,
				$limitValue,
				$maxBound
			);

			return $newPrice < $prevPrice;
		};
	}

	/**
	 * Delivery action.
	 *
	 * @param array &$order Order data.
	 * @param array $action Action detail
	 *    keys are case sensitive:
	 *        <ul>
	 *        <li>float|int VALUE                Discount value.
	 *        <li>char UNIT                    Discount type.
	 *        <li>string CURRENCY                Currency discount (optional).
	 *        <li>char MAX_BOUND                Max bound.
	 *        </ul>.
	 * @return void
	 */
	public static function applyToDelivery(array &$order, array $action)
	{
		static::increaseApplyCounter();

		if (!isset($action['VALUE']) || !isset($action['UNIT']))
			return;
		if ($action['UNIT'] != self::VALUE_TYPE_PERCENT && $action['UNIT'] != self::VALUE_TYPE_FIX)
			return;

		$orderCurrency = static::getCurrency();
		$unit = (string)$action['UNIT'];
		$value = (float)$action['VALUE'];
		$currency = (isset($action['CURRENCY']) ? $action['CURRENCY'] : $orderCurrency);
		$maxBound = false;
		if ($unit == self::VALUE_TYPE_FIX && $value < 0)
			$maxBound = (isset($action['MAX_BOUND']) && $action['MAX_BOUND'] == 'Y');

		$actionDescription = array(
			'ACTION_TYPE' => Formatter::TYPE_VALUE,
			'VALUE' => abs($value),
			'VALUE_ACTION' => (
				$value < 0
				? Formatter::VALUE_ACTION_DISCOUNT
				: Formatter::VALUE_ACTION_EXTRA
			)
		);
		if ($maxBound)
			$actionDescription['ACTION_TYPE'] = Formatter::TYPE_MAX_BOUND;

		switch ($unit)
		{
			case self::VALUE_TYPE_PERCENT:
				$actionDescription['VALUE_TYPE'] = Formatter::VALUE_TYPE_PERCENT;
				$value = ($order['PRICE_DELIVERY'] * $value) / 100;
				break;
			case self::VALUE_TYPE_FIX:
				$actionDescription['VALUE_TYPE'] = Formatter::VALUE_TYPE_CURRENCY;
				$actionDescription['VALUE_UNIT'] = $currency;
				if ($currency != $orderCurrency)
					$value = \CCurrencyRates::ConvertCurrency($value, $currency, $orderCurrency);
				break;
		}
		static::setActionDescription(self::RESULT_ENTITY_DELIVERY, $actionDescription);

		if (isset($order['CUSTOM_PRICE_DELIVERY']) && $order['CUSTOM_PRICE_DELIVERY'] == 'Y')
			return;
		if (
			!isset($order['PRICE_DELIVERY'])
			|| (
				static::roundZeroValue($order['PRICE_DELIVERY']) == 0
				&& $actionDescription['VALUE_ACTION'] == Formatter::VALUE_ACTION_DISCOUNT
			)
		)
			return;

		$value = static::roundValue($value, $order['CURRENCY']);
		$value = static::roundZeroValue($value);
		if ($value == 0)
			return;

		$resultValue = static::roundZeroValue($order['PRICE_DELIVERY'] + $value);
		if ($maxBound && $resultValue < 0)
		{
			$resultValue = 0;
			$value = -$order['PRICE_DELIVERY'];
		}

		if ($resultValue < 0)
			return;

		if (!isset($order['PRICE_DELIVERY_DIFF']))
			$order['PRICE_DELIVERY_DIFF'] = 0;
		$order['PRICE_DELIVERY_DIFF'] -= $value;
		$order['PRICE_DELIVERY'] = $resultValue;

		$actionDescription['RESULT_VALUE'] = abs($value);
		$actionDescription['RESULT_UNIT'] = $orderCurrency;

		static::setActionResult(self::RESULT_ENTITY_DELIVERY, $actionDescription);
		unset($actionDescription);
	}

	/**
	 * Simple gift action.
	 *
	 * @param array &$order			Order data.
	 * @param callable $filter		Filter.
	 * @throws Main\ArgumentOutOfRangeException
	 * @return void
	 */
	public static function applySimpleGift(array &$order, $filter)
	{
		static::increaseApplyCounter();

		$actionDescription = array(
			'ACTION_TYPE' => Formatter::TYPE_SIMPLE_GIFT
		);
		static::setActionDescription(self::RESULT_ENTITY_BASKET, $actionDescription);

		if (!is_callable($filter))
			return;

		if (empty($order['BASKET_ITEMS']) || !is_array($order['BASKET_ITEMS']))
			return;

		static::disableBasketFilter();

		$itemsCopy = $order['BASKET_ITEMS'];
		Main\Type\Collection::sortByColumn($itemsCopy, 'PRICE', null, null, true);
		$filteredBasket = static::getBasketForApply(
			$itemsCopy,
			$filter,
			array(
				'GIFT_TITLE' => Loc::getMessage('BX_SALE_DISCOUNT_ACTIONS_SIMPLE_GIFT_DESCR')
			)
		);
		unset($itemsCopy);

		static::enableBasketFilter();

		if (empty($filteredBasket))
			return;

		$applyBasket = array_filter($filteredBasket, '\Bitrix\Sale\Discount\Actions::filterBasketForAction');
		unset($filteredBasket);
		if (empty($applyBasket))
			return;

		foreach ($applyBasket as $basketCode => $basketRow)
		{
			self::fillDiscountPrice($basketRow, 0, $basketRow['PRICE']);

			$order['BASKET_ITEMS'][$basketCode] = $basketRow;

			$rowActionDescription = $actionDescription;
			$rowActionDescription['BASKET_CODE'] = $basketCode;
			static::setActionResult(self::RESULT_ENTITY_BASKET, $rowActionDescription);
			unset($rowActionDescription);
		}
		unset($basketCode, $basketRow);
	}

	/**
	 * Return basket item for action apply.
	 *
	 * @param array $basket Basket.
	 * @param mixed $filter Filter.
	 * @param array $action Prepare data.
	 * @return mixed
	 */
	public static function getBasketForApply(array $basket, $filter, $action = array())
	{
		$result = array();
		switch (static::getUseMode())
		{
			case self::MODE_CALCULATE:
				$result = (is_callable($filter) ? array_filter($basket, $filter) : $basket);
				break;
			case self::MODE_MANUAL:
			case self::MODE_MIXED:
				switch (static::getApplyResultMode())
				{
					case self::APPLY_RESULT_MODE_COUNTER:
						$currentCounter = static::getApplyCounter();
						$basketCodeList = array_keys($basket);
						foreach ($basketCodeList as &$code)
						{
							if (empty(self::$applyResult['BASKET'][$code]) || !is_array(self::$applyResult['BASKET'][$code]))
								continue;
							if (!in_array($currentCounter, self::$applyResult['BASKET'][$code]))
								continue;
							$result[$code] = $basket[$code];
						}
						unset($code, $basketCodeList, $currentCounter);
						break;
					case self::APPLY_RESULT_MODE_DESCR:
						$basketCodeList = array_keys($basket);
						foreach ($basketCodeList as &$code)
						{
							if (empty(self::$applyResult['BASKET'][$code]) || !is_array(self::$applyResult['BASKET'][$code]))
								continue;
							foreach (self::$applyResult['BASKET'][$code] as $descr)
							{
								if (static::compareBasketResultDescr($action, $descr))
								{
									$result[$code] = $basket[$code];
									break;
								}
							}
							unset($descr);
							// only for old format simple gifts
							if (!isset($result[$code]))
							{
								if (isset($action['GIFT_TITLE']))
								{
									end(self::$applyResult['BASKET'][$code]);
									$descr = current(self::$applyResult['BASKET'][$code]);
									if (
										$descr['TYPE'] == Formatter::TYPE_SIMPLE
										&& $descr['DESCR'] == $action['GIFT_TITLE']
									)
										$result[$code] = $basket[$code];
									unset($descr);
								}
							}
						}
						unset($code, $basketCodeList);
						break;
					case self::APPLY_RESULT_MODE_SIMPLE:
						$basketCodeList = array_keys($basket);
						foreach ($basketCodeList as &$code)
						{
							if (isset(self::$applyResult['BASKET'][$code]))
								$result[$code] = $basket[$code];
						}
						unset($code, $basketCodeList);
						break;
				}
				break;
		}

		return $result;
	}

	/**
	 * Save action description.
	 *
	 * @param int $type Action object type.
	 * @param array $description Description.
	 * @return void
	 */
	public static function setActionDescription($type, $description)
	{
		if (!static::isCalculateMode())
			return;
		if (empty($description) || !is_array($description) || !isset($description['ACTION_TYPE']))
			return;
		$actionType = $description['ACTION_TYPE'];
		if ($actionType == Formatter::TYPE_SIMPLE)
			$description = (isset($description['ACTION_DESCRIPTION']) ? $description['ACTION_DESCRIPTION'] : '');

		$prepareResult = Sale\Discount\Formatter::prepareRow($actionType, $description);
		unset($actionType);

		if ($prepareResult !== null)
		{
			switch ($type)
			{
				case self::RESULT_ENTITY_BASKET:
					if (!isset(self::$actionDescription['BASKET']))
						self::$actionDescription['BASKET'] = array();
					self::$actionDescription['BASKET'][static::getApplyCounter()] = $prepareResult;
					break;
				case self::RESULT_ENTITY_DELIVERY:
					if (!isset(self::$actionDescription['DELIVERY']))
						self::$actionDescription['DELIVERY'] = array();
					self::$actionDescription['DELIVERY'][static::getApplyCounter()] = $prepareResult;
					break;
			}
		}
		unset($prepareResult);
	}

	/**
	 * Save result.
	 *
	 * @param int $entity			Action object type.
	 * @param array $actionResult	Result description.
	 * @return void
	 */
	public static function setActionResult($entity, array $actionResult)
	{
		if (empty($actionResult) || !isset($actionResult['ACTION_TYPE']))
			return;

		$actionType = $actionResult['ACTION_TYPE'];
		if ($actionType == Formatter::TYPE_SIMPLE)
			$actionDescription = (isset($actionResult['ACTION_DESCRIPTION']) ? $actionResult['ACTION_DESCRIPTION'] : '');
		else
			$actionDescription = $actionResult;
		$prepareResult = Sale\Discount\Formatter::prepareRow($actionType, $actionDescription);
		unset($actionDescription, $actionType);

		if ($prepareResult !== null)
		{
			switch ($entity)
			{
				case self::RESULT_ENTITY_BASKET:
					if (!isset(self::$actionResult['BASKET']))
						self::$actionResult['BASKET'] = array();
					$basketCode = $actionResult['BASKET_CODE'];
					if (!isset(self::$actionResult['BASKET'][$basketCode]))
						self::$actionResult['BASKET'][$basketCode] = array();
					//TODO: remove this hack
					if (isset($actionResult['REVERT_APPLY']))
						$prepareResult['REVERT_APPLY'] = $actionResult['REVERT_APPLY'];
					self::$actionResult['BASKET'][$basketCode][static::getApplyCounter()] = $prepareResult;
					unset($basketCode);
					break;
				case self::RESULT_ENTITY_DELIVERY:
					if (!isset(self::$actionResult['DELIVERY']))
						self::$actionResult['DELIVERY'] = array();
					self::$actionResult['DELIVERY'][static::getApplyCounter()] = $prepareResult;
					break;
			}
		}
		unset($prepareResult);
	}

	/**
	 * @param int $entity			Entity id.
	 * @param array $entityParams	Entity params (optional).
	 * @return void
	 */
	public static function clearEntityActionResult($entity, array $entityParams = array())
	{
		switch ($entity)
		{
			case self::RESULT_ENTITY_BASKET:
				if (empty($entityParams))
				{
					if (array_key_exists('BASKET', self::$actionResult))
						unset(self::$actionResult['BASKET']);
				}
				else
				{
					if (isset($entityParams['BASKET_CODE']) && array_key_exists($entityParams['BASKET_CODE'], self::$actionResult['BASKET']))
						unset(self::$actionResult['BASKET'][$entityParams['BASKET_CODE']]);
				}
				break;
			case self::RESULT_ENTITY_DELIVERY:
				if (array_key_exists('DELIVERY', self::$actionResult))
					unset(self::$actionResult['DELIVERY']);
				break;
		}
	}

	/**
	 * Return percent value.
	 *
	 * @param array $basket Basket.
	 * @param int|float $value Value.
	 * @return float
	 */
	public static function getPercentByValue($basket, $value)
	{
		$summ = 0;
		switch (static::getPercentMode())
		{
			case self::PERCENT_FROM_BASE_PRICE:
				foreach ($basket as $basketRow)
					$summ += (float)$basketRow['BASE_PRICE'] * (float)$basketRow['QUANTITY'];
				unset($basketRow);
				break;
			case self::PERCENT_FROM_CURRENT_PRICE:
				foreach ($basket as $basketRow)
					$summ += (float)$basketRow['PRICE'] * (float)$basketRow['QUANTITY'];
				unset($basketRow);
				break;
		}

		return static::roundZeroValue($summ > 0 ? ($value * 100) / $summ : 0);
	}

	/**
	 * Calculate percent price.
	 *
	 * @param array $basketRow Basket item.
	 * @param float $percent Percent value.
	 * @return float
	 */
	public static function percentToValue($basketRow, $percent)
	{
		$value = 0.0;
		switch (static::getPercentMode())
		{
			case self::PERCENT_FROM_BASE_PRICE:
				$value = ((float)$basketRow['BASE_PRICE'] * $percent) / 100;
				break;
			case self::PERCENT_FROM_CURRENT_PRICE:
				$value = ((float)$basketRow['PRICE'] * $percent) / 100;
				break;
		}

		return $value;
	}

	public static function getActionConfiguration(array $discount)
	{
		$actionStructure = self::getActionStructure($discount);

		if(!$actionStructure || !is_array($actionStructure))
		{
			return null;
		}

		if($actionStructure['CLASS_ID'] != 'CondGroup')
		{
			return null;
		}

		if(count($actionStructure['CHILDREN']) > 1)
		{
			return null;
		}

		$action = reset($actionStructure['CHILDREN']);
		if($action['CLASS_ID'] != 'ActSaleBsktGrp')
		{
			return null;
		}

		$actionData = $action['DATA'];

		$configuration = array(
			'TYPE' => $actionData['Type'],
			'VALUE' => $actionData['Value'],
			'LIMIT_VALUE' => $actionData['Max']?: 0,
		);
		switch ($actionData['Unit'])
		{
			case 'CurEach':
				$configuration['VALUE_TYPE'] = Sale\Discount\Actions::VALUE_TYPE_FIX;
				break;
			case 'CurAll':
				$configuration['VALUE_TYPE'] = Sale\Discount\Actions::VALUE_TYPE_SUMM;
				break;
			default:
				$configuration['VALUE_TYPE'] = Sale\Discount\Actions::VALUE_TYPE_PERCENT;
				break;
		}

		return $configuration;
	}

	protected static function getActionStructure(array $discount)
	{
		$actionStructure = null;
		if (isset($discount['ACTIONS']) && !empty($discount['ACTIONS']))
		{
			$actionStructure = false;
			if (!is_array($discount['ACTIONS']))
			{
				if (CheckSerializedData($discount['ACTIONS']))
				{
					$actionStructure = unserialize($discount['ACTIONS'], ['allowed_classes' => false]);
				}
			}
			else
			{
				$actionStructure = $discount['ACTIONS'];
			}
		}
		elseif(isset($discount['ACTIONS_LIST']) && is_array($discount['ACTIONS_LIST']))
		{
			$actionStructure = $discount['ACTIONS_LIST'];
		}

		return $actionStructure;
	}

	/**
	 * Return check result for error mode.
	 *
	 * @param array $action			Action description.
	 * @param array $resultDescr	Result description.
	 * @return bool
	 */
	protected static function compareBasketResultDescr(array $action, $resultDescr)
	{
		$result = false;

		if (empty($action))
			return $result;
		if (!is_array($resultDescr) || !isset($resultDescr['TYPE']))
			return $result;

		$currency = (isset($action['CURRENCY']) ? $action['CURRENCY'] : static::getCurrency());
		$value = abs($action['VALUE']);
		$valueAction = (
			$action['VALUE'] < 0
			? Formatter::VALUE_ACTION_DISCOUNT
			: Formatter::VALUE_ACTION_EXTRA
		);

		switch ($resultDescr['TYPE'])
		{
			case Formatter::TYPE_VALUE:
				if (
					$resultDescr['VALUE'] == $value
					&& $resultDescr['VALUE_ACTION'] = $valueAction
				)
				{
					switch($action['UNIT'])
					{
						case self::VALUE_TYPE_SUMM:
							$result = (
								(
									$resultDescr['VALUE_TYPE'] == Formatter::VALUE_TYPE_SUMM_BASKET
									|| $resultDescr['VALUE_TYPE'] == Formatter::VALUE_TYPE_SUMM
								)
								&& $resultDescr['VALUE_UNIT'] == $currency
							);
							break;
						case self::VALUE_TYPE_PERCENT:
							$result = ($resultDescr['VALUE_TYPE'] == Formatter::VALUE_TYPE_PERCENT);
							break;
						case self::VALUE_TYPE_FIX:
							$result = (
								$resultDescr['VALUE_TYPE'] == Formatter::VALUE_TYPE_CURRENCY
								&& $resultDescr['VALUE_UNIT'] == $currency
							);
							break;
					}
				}
				break;
			case Formatter::TYPE_MAX_BOUND:
				$result = (
					$resultDescr['VALUE'] == $value
					&& $resultDescr['VALUE_ACTION'] == $valueAction
					&& $resultDescr['VALUE_TYPE'] == Formatter::VALUE_TYPE_CURRENCY
					&& $resultDescr['VALUE_UNIT'] == $currency
				);
				break;
		}

		unset($valueAction, $value, $currency);

		return $result;
	}

	/**
	 * Calculate simple discount result.
	 *
	 * @param int|float $value				Discount value.
	 * @param string $unit					Discount value type.
	 * @param array $basketRow				Basket item.
	 * @param int|float|null $limitValue	Max discount value.
	 * @param bool $maxBound				Allow set price to 0, if discount more than price.
	 *
	 * @return array
	 */
	protected static function calculateDiscountPrice($value, $unit, array $basketRow, $limitValue, $maxBound)
	{
		$calculateValue = $value;
		if ($unit == self::VALUE_TYPE_PERCENT)
			$calculateValue = static::percentToValue($basketRow, $calculateValue);
		$calculateValue = static::roundValue($calculateValue, $basketRow['CURRENCY']);

		if ($unit == self::VALUE_TYPE_CLOSEOUT)
		{
			if ($calculateValue < $basketRow['PRICE'])
			{
				$result = $calculateValue;
				$calculateValue = $result - $basketRow['PRICE'];
			}
			else
			{
				$result = -1;
			}
		}
		else
		{
			if (!empty($limitValue) && $limitValue + $calculateValue <= 0)
				$calculateValue = -$limitValue;

			$result = static::roundZeroValue($basketRow['PRICE'] + $calculateValue);
			if ($maxBound && $result < 0)
			{
				$result = 0;
				$calculateValue = -$basketRow['PRICE'];
			}
		}

		return [$calculateValue, $result];
	}

	/**
	 * Fill price fields in basket item.
	 *
	 * @param array &$basketRow		Basket item fields.
	 * @param int|float $price		New price.
	 * @param int|float $discount	Value of the discount change.
	 * @return void
	 */
	protected static function fillDiscountPrice(array &$basketRow, $price, $discount)
	{
		if (!isset($basketRow['DISCOUNT_PRICE']))
			$basketRow['DISCOUNT_PRICE'] = 0;
		$basketRow['PRICE'] = $price;
		$basketRow['DISCOUNT_PRICE'] += $discount;
	}
}