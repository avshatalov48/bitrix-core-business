<?php
namespace Bitrix\Sale\Internals;

class PoolBase
{
	protected static $pool = array();
	protected static $index = 0;

	public static function getPoolByCode($code)
	{
		if (isset(static::$pool[$code]))
		{
			return static::$pool[$code];
		}

		return null;
	}

	public static function get($code, $type)
	{
		if (isset(static::$pool[$code][$type]))
		{
			return static::$pool[$code][$type];
		}

		return null;
	}

	/**
	 * @param $code
	 * @param $type
	 * @param int $index
	 *
	 * @return null|mixed
	 */
	public static function getByIndex($code, $type, $index)
	{
		if (isset(static::$pool[$code][$type][$index]))
		{
			return static::$pool[$code][$type][$index];
		}

		return null;
	}

	/**
	 * @param $code
	 * @param $type
	 * @param $value
	 */
	public static function add($code, $type, $value)
	{
		static::$index++;
		static::$pool[$code][$type][static::$index] = $value;
	}

	/**
	 * @param $code
	 * @param $type
	 * @param $index
	 */
	public static function delete($code, $type, $index)
	{
		if (isset(static::$pool[$code][$type][$index]))
		{
			unset(static::$pool[$code][$type][$index]);
		}
	}

	/**
	 * @param $code
	 * @param $type
	 *
	 * @return bool
	 */
	public static function isTypeExists($code, $type)
	{
		return (!empty(static::$pool[$code][$type]));
	}

	/**
	 * @param null $code
	 * @param null $type
	 */
	public static function resetPool($code = null, $type = null)
	{
		if ($code !== null)
		{
			if ($type !== null)
			{
				unset(static::$pool[$code][$type]);
			}
			else
			{
				unset(static::$pool[$code]);
			}
		}
		else
		{
			static::$pool = array();
		}

		if (empty(static::$pool[$code]))
		{
			unset(static::$pool[$code]);
		}
	}
}
