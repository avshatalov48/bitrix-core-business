<?php

namespace Bitrix\Catalog\Product;

class QuantityControl
{
	private static $values = array();
	const QUANTITY_CONTROL_QUANTITY = 'quantity';
	const QUANTITY_CONTROL_AVAILABLE_QUANTITY = 'available_quantity';
	const QUANTITY_CONTROL_RESERVED_QUANTITY = 'reserved_quantity';

	/**
	 * @param null $productId
	 */
	public static function resetAllQuantity($productId = null)
	{
		static::resetValue(static::QUANTITY_CONTROL_QUANTITY, $productId);
		static::resetValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId);
		static::resetValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId);
	}

	/**
	 * @param $productId
	 * @param $value
	 */
	public static function setQuantity($productId, $value)
	{
		static::setValue(static::QUANTITY_CONTROL_QUANTITY, $productId, $value);
	}

	/**
	 * @param $productId
	 * @param $value
	 */
	public static function addQuantity($productId, $value)
	{
		$oldValue = (float) static::getValue(static::QUANTITY_CONTROL_QUANTITY, $productId);
		static::setValue(static::QUANTITY_CONTROL_QUANTITY, $productId, $oldValue + $value);
	}

	/**
	 * @param $productId
	 *
	 * @return float|int|null
	 */
	public static function getQuantity($productId)
	{
		return static::getValue(static::QUANTITY_CONTROL_QUANTITY, $productId);
	}

	/**
	 * @param $productId
	 */
	public static function resetQuantity($productId)
	{
		static::resetValue(static::QUANTITY_CONTROL_QUANTITY, $productId);
	}

	/**
	 * @param $productId
	 * @param $value
	 */
	public static function setReservedQuantity($productId, $value)
	{
		static::setValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId, $value);
	}

	/**
	 * @param $productId
	 * @param $value
	 */
	public static function addReservedQuantity($productId, $value)
	{
		$oldValue = (float) static::getValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId);
		static::setValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId, $oldValue + $value);
	}

	/**
	 * @param $productId
	 *
	 * @return float|int|null
	 */
	public static function getReservedQuantity($productId)
	{
		return static::getValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId);
	}

	/**
	 * @param $productId
	 */
	public static function resetReservedQuantity($productId)
	{
		static::resetValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId);
	}

	/**
	 * @param $productId
	 * @param $value
	 */
	public static function setAvailableQuantity($productId, $value)
	{
		static::setValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId, $value);
	}

	/**
	 * @param $productId
	 * @param $value
	 */
	public static function addAvailableQuantity($productId, $value)
	{
		$oldValue = (float) static::getValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId);
		static::setValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId, $oldValue + $value);
	}


	/**
	 * @param $productId
	 *
	 * @return float|int|null
	 */
	public static function getAvailableQuantity($productId)
	{
		return static::getValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId);
	}


	/**
	 * @param $productId
	 */
	public static function resetAvailableQuantity($productId)
	{
		static::resetValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId);
	}
	/**
	 * @param $type
	 * @param $productId
	 * @param $value
	 */
	private static function setValue($type, $productId, $value)
	{
		static::$values[$type][$productId] = (float) $value;
	}

	/**
	 * @param $type
	 * @param $productId
	 *
	 * @return null|int|float
	 */
	private static function getValue($type, $productId)
	{
		$value = null;
		if (isset(static::$values[$type][$productId]))
		{
			$value = static::$values[$type][$productId];
		}

		return $value;
	}

	/**
	 * @param $type
	 * @param null $productId
	 */
	private static function resetValue($type, $productId = null)
	{
		if ($productId == null)
		{
			unset(static::$values[$type]);
		}
		else
		{
			unset(static::$values[$type][$productId]);
		}

	}

	/**
	 * @param $productId
	 * @param array $values
	 */
	public static function setValues($productId, array $values)
	{
		if (isset($values[static::QUANTITY_CONTROL_QUANTITY]))
		{
			static::setValue(static::QUANTITY_CONTROL_QUANTITY, $productId, $values[static::QUANTITY_CONTROL_QUANTITY]);
		}

		if (isset($values[static::QUANTITY_CONTROL_AVAILABLE_QUANTITY]))
		{
			static::setValue(static::QUANTITY_CONTROL_AVAILABLE_QUANTITY, $productId, $values[static::QUANTITY_CONTROL_AVAILABLE_QUANTITY]);
		}

		if (isset($values[static::QUANTITY_CONTROL_RESERVED_QUANTITY]))
		{
			static::setValue(static::QUANTITY_CONTROL_RESERVED_QUANTITY, $productId, $values[static::QUANTITY_CONTROL_RESERVED_QUANTITY]);
		}
	}
}