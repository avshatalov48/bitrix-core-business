<?php
namespace Bitrix\Catalog\Product\Price;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog,
	Bitrix\Currency;

Loc::loadMessages(__FILE__);

/**
 * Class Calculation
 * Provides methods for product price calculation (discounts, round, precision).
 *
 * @package Bitrix\Catalog\Product\Price
 */
class Calculation
{
	const RESULT_MODE_COMPONENT = 1;
	const RESULT_MODE_RAW = 2;

	protected static $config = array(
		'CURRENCY' => null,
		'PRECISION' => null,
		'USE_DISCOUNTS' => true,
		'RESULT_WITH_VAT' => true,
		'RESULT_MODE' => self::RESULT_MODE_COMPONENT
	);

	private static $stack = array();

	/**
	 * Set calculation settings.
	 *
	 * @param array $config
	 *  keys are case sensitive:
	 *		<ul>
	 * 		<li>string CURRENCY				Result currency (can be null - use base currency (compatibility only)).
	 *		<li>int PRECISION				Calculation precision (can be null - use default catalog precision - CATALOG_VALUE_PRECISION).
	 *		<li>bool USE_DISCOUNTS			Use discounts for calculation (by default use discounts is allowed).
	 *		<li>bool RESULT_WITH_VAT		Returns result price without/with VAT (by default return price with VAT).
	 *		<li>int RESULT_MODE				Returns raw result for provider or prepared result for components (by default - for components).
	 *		</ul>
	 * @return void
	 */
	public static function setConfig(array $config)
	{
		$config = static::checkConfig($config);
		if (empty($config))
			return;

		self::$config = array_merge(self::$config, $config);
	}

	/**
	 * Returns current calculation settings.
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		return self::$config;
	}

	/**
	 * Save current calculation settings to the stack before the changes.
	 *
	 * @return void
	 */
	public static function pushConfig()
	{
		array_push(self::$stack, static::getConfig());
	}

	/**
	 * Set the calculation settings from the stack, if it is not empty.
	 *
	 * @return void
	 */
	public static function popConfig()
	{
		if (empty(self::$stack))
			return;
		static::setConfig(array_pop(self::$stack));
	}

	/**
	 * Returns result calculation currency.
	 *
	 * @return string
	 */
	public static function getCurrency()
	{
		return (self::$config['CURRENCY'] !== null ? self::$config['CURRENCY'] : Currency\CurrencyManager::getBaseCurrency());
	}

	/**
	 * Returns calculation precision.
	 *
	 * @return int
	 */
	public static function getPrecision()
	{
		return (self::$config['PRECISION'] !== null ? self::$config['PRECISION'] : CATALOG_VALUE_PRECISION);
	}

	/**
	 * Returns true if allowed use discounts.
	 *
	 * @return bool
	 */
	public static function isAllowedUseDiscounts()
	{
		return self::$config['USE_DISCOUNTS'];
	}

	/**
	 * Returns true if result price with VAT.
	 *
	 * @return bool
	 */
	public static function isIncludingVat()
	{
		return self::$config['RESULT_WITH_VAT'];
	}

	public static function getResultMode()
	{
		return self::$config['RESULT_MODE'];
	}

	public static function isComponentResultMode()
	{
		return self::$config['RESULT_MODE'] == self::RESULT_MODE_COMPONENT;
	}
	
	public static function isRawResultMode()
	{
		return self::$config['RESULT_MODE'] == self::RESULT_MODE_RAW;
	}	

	/**
	 * Rounding the price or discount to a specified number of decimal places.
	 *
	 * @param float|int $value		Value for rounding.
	 * @return float
	 */
	public static function roundPrecision($value)
	{
		return roundEx($value, static::getPrecision());
	}

	/**
	 * Returns the result of comparing two values with the precision of rounding.
	 *
	 * @param float|int $firstValue         First value.
	 * @param float|int $secondValue        Second value.
	 * @param string $operator              Compare operator ( >, >=, <, <=, ==, !=).
	 * @return bool
	 */
	public static function compare($firstValue, $secondValue, $operator)
	{
		$firstValue = static::roundPrecision($firstValue);
		$secondValue = static::roundPrecision($secondValue);

		$result = false;
		switch ($operator)
		{
			case '>':
				$result = ($firstValue > $secondValue);
				break;
			case '>=':
				$result = ($firstValue >= $secondValue);
				break;
			case '<':
				$result = ($firstValue < $secondValue);
				break;
			case '<=':
				$result = ($firstValue <= $secondValue);
				break;
			case '==':
				$result = ($firstValue == $secondValue);
				break;
			case '!=':
				$result = ($firstValue != $secondValue);
				break;
		}
		return $result;
	}

	/**
	 * Validate new settings (allowed keys and values).
	 * @internal
	 *
	 * @param array $config		New config.
	 * @return array
	 */
	protected static function checkConfig(array $config)
	{
		$result = array();

		$config = array_intersect_key($config, self::$config);
		if (!empty($config))
		{
			foreach ($config as $field => $value)
			{
				$checked = true;
				switch ($field)
				{
					case 'CURRENCY':
						if ($value !== null)
						{
							$value = (string)$value;
							$checked = Currency\CurrencyManager::isCurrencyExist($value);
						}
						break;
					case 'PRECISION':
						if ($value !== null)
						{
							$value = (int)$value;
							$checked = ($value > 0);
						}
						break;
					case 'USE_DISCOUNTS':
					case 'RESULT_WITH_VAT':
						$checked = is_bool($value);
						break;
					case 'RESULT_MODE':
						$value = (int)$value;
						$checked = ($value == self::RESULT_MODE_COMPONENT || $value == self::RESULT_MODE_RAW);
						break;
					default:
						break;
				}
				if ($checked)
					$result[$field] = $value;
			}
			unset($field, $value);
		}

		return $result;
	}
}