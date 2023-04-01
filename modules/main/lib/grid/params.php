<?php

namespace Bitrix\Main\Grid;


class Params
{
	/**
	 * Gets first a Boolean value from $values or gets a default value
	 * @param array $values
	 * @param $defaultValue
	 *
	 * @return bool|*
	 */
	public static function prepareBoolean(array $values, $defaultValue)
	{
		$value = $defaultValue;

		foreach ($values as $item)
		{
			if (is_bool($item))
			{
				$value = $item;
				break;
			}
		}

		return $value;
	}


	/**
	 * Gets first a String value from $values or gets a default value
	 * @param array $values
	 * @param $defaultValue
	 *
	 * @return string|*
	 */
	public static function prepareString(array $values, $defaultValue)
	{
		$value = $defaultValue;

		foreach ($values as $item)
		{
			if (is_string($item))
			{
				$value = $item;
				break;
			}
		}

		return $value;
	}


	/**
	 * Gets first a Array value from $values or gets a default value
	 * @param array $values
	 * @param $defaultValue
	 *
	 * @return array|*
	 */
	public static function prepareArray(array $values, $defaultValue)
	{
		$value = $defaultValue;

		foreach ($values as $item)
		{
			if (is_array($item))
			{
				$value = $item;
				break;
			}
		}

		return $value;
	}


	/**
	 * Gets first a Integer value from $values or gets a default value
	 * @param array $values
	 * @param $defaultValue
	 *
	 * @return int|string
	 */
	public static function prepareInt(array $values, $defaultValue)
	{
		$value = $defaultValue;

		foreach ($values as $item)
		{
			if (is_numeric($item))
			{
				$value = $item;
				break;
			}
		}

		return $value;
	}

	public static function ensureString(array $params, $name)
	{
		return isset($params[$name]) && is_string($params[$name]);
	}

	public static function ensureNotEmptyString(array $params, $name)
	{
		return isset($params[$name]) && is_string($params[$name]) && $params[$name] !== "";
	}

	public static function ensureArray(array $params, $name)
	{
		return isset($params[$name]) && is_array($params[$name]);
	}

	public static function ensureNotEmptyArray(array $params, $name)
	{
		return isset($params[$name]) && is_array($params[$name]) && !empty($params[$name]);
	}
}
