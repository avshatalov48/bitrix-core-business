<?php
namespace Bitrix\Main\Type;

use Bitrix\Main\ArgumentException;

class Collection
{
	/**
	 * Sorting array by column.
	 * You can use short mode: Collection::sortByColumn($arr, 'value'); This is equal Collection::sortByColumn($arr, array('value' => SORT_ASC))
	 *
	 * Pay attention: if two members compare as equal, their relative order in the sorted array is undefined. The sorting is not stable.
	 *
	 * More example:
	 * Collection::sortByColumn($arr, array('value' => array(SORT_NUMERIC, SORT_ASC), 'attr' => SORT_DESC), array('attr' => 'strlen'), 'www');
	 *
	 * @param array        $array
	 * @param string|array $columns
	 * @param string|array $callbacks
	 * @param null         $defaultValueIfNotSetValue If value not set - use $defaultValueIfNotSetValue (any cols)
	 * @param bool         $preserveKeys If false numeric keys will be re-indexed. If true - preserve.
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function sortByColumn(array &$array, $columns, $callbacks = '', $defaultValueIfNotSetValue = null, $preserveKeys = false)
	{
		//by default: sort by ASC
		if (!is_array($columns))
		{
			$columns = array($columns => SORT_ASC);
		}
		$params = $preserveDataKeys = array();
		$alreadyFillPreserveDataKeys = false;
		foreach ($columns as $column => &$order)
		{
			$callback = null;
			//this is an array of callbacks (callable string)
			if(is_array($callbacks) && !is_callable($callbacks))
			{
				//if callback set for column
				if(!empty($callbacks[$column]))
				{
					$callback = is_callable($callbacks[$column])? $callbacks[$column] : false;
				}
			}
			//common callback
			elseif(!empty($callbacks))
			{
				$callback = is_callable($callbacks)? $callbacks : false;
			}

			if($callback === false)
			{
				throw new \Bitrix\Main\ArgumentOutOfRangeException('callbacks');
			}

			//this is similar to the index|slice
			$valueColumn[$column] = array();
			foreach ($array as $index => $row)
			{
				$value = $row[$column] ?? $defaultValueIfNotSetValue;
				if ($callback)
				{
					$value = call_user_func_array($callback, array($value));
				}
				$valueColumn[$column][$index] = $value;
				if($preserveKeys && !$alreadyFillPreserveDataKeys)
				{
					$preserveDataKeys[$index] = $index;
				}
			}
			unset($row, $index);
			$alreadyFillPreserveDataKeys = $preserveKeys && !empty($preserveDataKeys);
			//bug in 5.3 call_user_func_array
			$params[] = &$valueColumn[$column];
			$order    = (array)$order;
			foreach ($order as $i => $ord)
			{
				$params[] = &$columns[$column][$i];
			}
		}
		unset($order, $column);
		$params[] = &$array;
		if($preserveKeys)
		{
			$params[] = &$preserveDataKeys;
		}

		call_user_func_array('array_multisort', $params);

		if($preserveKeys)
		{
			$array = array_combine(array_values($preserveDataKeys), array_values($array));
		}
	}

	/**
	 * Takes all arguments by pairs..
	 * Odd arguments are arrays.
	 * Even arguments are keys to lookup in these arrays.
	 * Keys may be arrays. In this case function will try to dig deeper.
	 * Returns first not empty element of a[k] pair.
	 *
	 * @param array $a array to analyze
	 * @param string|int $k key to lookup
	 * @param mixed $a,... unlimited array/key pairs to go through
	 * @return mixed|string
	 */
	public static function firstNotEmpty()
	{
		$argCount = func_num_args();
		for ($i = 0; $i < $argCount; $i += 2)
		{
			$anArray = func_get_arg($i);
			$key = func_get_arg($i+1);
			if (is_array($key))
			{
				$current = &$anArray;
				$found = true;
				foreach ($key as $k)
				{
					if (!is_array($current) || !array_key_exists($k, $current))
					{
						$found = false;
						break;
					}
					$current = &$current[$k];
				}
				if ($found)
				{
					if (is_array($current) || is_object($current) || $current != "")
						return $current;
				}
			}
			elseif (is_array($anArray) && array_key_exists($key, $anArray))
			{
				if (is_array($anArray[$key]) || is_object($anArray[$key]) || $anArray[$key] != "")
					return $anArray[$key];
			}
		}
		return "";
	}

	/**
	 * Convert array values to int, return unique values > 0. Optionally sorted array.
	 *
	 * @param array &$map	Array for normalize.
	 * @param bool $sorted	If sorted true, result array will be sorted.
	 * @return void
	 */
	public static function normalizeArrayValuesByInt(&$map, $sorted = true)
	{
		if (empty($map) || !is_array($map))
			return;

		$result = array();
		foreach ($map as $value)
		{
			$value = (int)$value;
			if (0 < $value)
				$result[$value] = true;
		}
		$map = array();
		if (!empty($result))
		{
			$map = array_keys($result);
			if ($sorted)
				sort($map);
		}
	}

	/**
	 * Check array is associative.
	 *
	 * @param $array - Array for check.
	 * @return bool
	 */
	public static function isAssociative(array $array)
	{
		$array = array_keys($array);

		return ($array !== array_keys($array));
	}

	/**
	 * Clone array recursively. Keys are preserved
	 *
	 * @param array $originalArray - array to clone
	 *
	 * @return array
	 */
	public static function clone(array $originalArray): array
	{
		$clonedArray = [];
		foreach ($originalArray as $index => $value)
		{
			if (is_array($value))
			{
				$value = static::clone($value);
			}
			elseif (is_object($value))
			{
				$value = clone $value;
			}

			$clonedArray[$index] = $value;
		}

		return $clonedArray;
	}

	/**
	 * Returns $array[p1][p2][p3] with $key = [p1, p2, p3]
	 *
	 * @param array $array
	 * @param array $key
	 * @return mixed
	 */
	public static function getByNestedKey(array $array, array $key)
	{
		if (empty($key))
		{
			return null;
		}

		$value = $array;

		while (!empty($key))
		{
			$subKey = array_shift($key);

			if (array_key_exists($subKey, $value))
			{
				$value = $value[$subKey];
			}
			else
			{
				return null;
			}
		}

		return $value;
	}

	/**
	 * Sets value $array[p1][p2][p3] = $value with $key = [p1, p2, p3]
	 *
	 * @param array $array
	 * @param array $key
	 * @param $value
	 * @return void
	 * @throws ArgumentException
	 */
	public static function setByNestedKey(array &$array, array $key, $value): void
	{
		if (empty($key))
		{
			throw new ArgumentException('Empty key to set');
		}

		$reference =& $array;
		while (!empty($key))
		{
			$subKey = array_shift($key);

			if (!array_key_exists($subKey, $reference))
			{
				$reference[$subKey] = [];
			}
			$reference = &$reference[$subKey];
		}

		$reference = $value;
		unset($reference);
	}

	/**
	 * Unsets last key in array $array[p1][p2][p3] with $key = [p1, p2, p3]
	 *
	 * @param array $array
	 * @param array $key
	 * @return void
	 * @throws ArgumentException
	 */
	public static function unsetByNestedKey(array &$array, array $key): void
	{
		if (empty($key))
		{
			throw new ArgumentException('Empty key to unset');
		}

		$reference =& $array;
		while (!empty($key))
		{
			$subKey = array_shift($key);

			if (!array_key_exists($subKey, $reference))
			{
				break;
			}

			if (empty($key))
			{
				// last element
				unset($reference[$subKey]);
			}
			else
			{
				$reference = &$reference[$subKey];
			}
		}
	}
}