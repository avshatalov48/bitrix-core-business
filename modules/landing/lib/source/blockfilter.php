<?php
namespace Bitrix\Landing\Source;


class BlockFilter
{
	/**
	 * @param array $row
	 * @return bool
	 */
	public static function checkRow($row)
	{
		if (empty($row) || !is_array($row))
		{
			return false;
		}
		if (empty($row['name']) || !is_string($row['name']))
		{
			return false;
		}
		if (empty($row['key']) || !is_string($row['key']))
		{
			return false;
		}
		if (empty($row['value']) || !is_array($row['value']))
		{
			return false;
		}
		return true;
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	public static function checkPreparedRow($row)
	{
		if (empty($row) || !is_array($row))
		{
			return false;
		}
		if (empty($row['key']) || empty($row['value']) || !is_array($row['value']))
		{
			return false;
		}
		return true;
	}

	/**
	 * @param string $name
	 * @param string $key
	 * @param array $value
	 * @return array|null
	 */
	public static function createRow(string $name, string $key, array $value)
	{
		$name = trim($name);
		$key = trim($key);
		if ($name === '' || $key === '' || empty($value))
		{
			return null;
		}
		return [
			'name' => $name,
			'key' => $key,
			'value' => $value
		];
	}
}