<?php

namespace Bitrix\Main\UI\Filter;


use Bitrix\Main\Filter\Range;

/**
 * Class DateType. Available subtypes of date field
 * @package Bitrix\Main\UI\Filter
 */
class DateType
{
	const NONE = "NONE";
	const YESTERDAY = "YESTERDAY";
	const CURRENT_DAY = "CURRENT_DAY";
	const TOMORROW = "TOMORROW";
	const CURRENT_WEEK = "CURRENT_WEEK";
	const CURRENT_MONTH = "CURRENT_MONTH";
	const CURRENT_QUARTER = "CURRENT_QUARTER";
	const LAST_7_DAYS = "LAST_7_DAYS";
	const LAST_30_DAYS = "LAST_30_DAYS";
	const LAST_60_DAYS = "LAST_60_DAYS";
	const LAST_90_DAYS = "LAST_90_DAYS";
	const PREV_DAYS = "PREV_DAYS";
	const NEXT_DAYS = "NEXT_DAYS";
	const MONTH = "MONTH";
	const QUARTER = "QUARTER";
	const YEAR = "YEAR";
	const EXACT = "EXACT";
	const LAST_WEEK = "LAST_WEEK";
	const LAST_MONTH = "LAST_MONTH";
	const RANGE = "RANGE";
	const NEXT_WEEK = "NEXT_WEEK";
	const NEXT_MONTH = "NEXT_MONTH";


	/**
	 * Gets subtypes list of date field
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}

	/**
	 * Returns postfix for request.
	 * @return string
	 */
	public static function getPostfix()
	{
		return "_datesel";
	}

	/**
	 * Search in plain array data that can belongs to this type.
	 * @param array $data
	 * @param array $filterFields
	 * @return array
	 */
	public static function getLogicFilter(array $data, array $filterFields)
	{
		$filter = [];
		$keys = array_filter($data, function ($key) {
			return (mb_substr($key, 0 - mb_strlen(self::getPostfix())) == self::getPostfix());
		}, ARRAY_FILTER_USE_KEY);
		foreach ($keys as $key => $val)
		{
			$id = mb_substr($key, 0, 0 - mb_strlen(self::getPostfix()));
			if (array_key_exists($id . "_from", $data))
			{
				if ($val === self::RANGE)
				{
					Range::prepareFrom($filter, $id, $data[$id . "_from"]);
				} else
				{
					$filter[">=" . $id] = $data[$id . "_from"];
				}
			}
			if (array_key_exists($id . "_to", $data))
			{
				if ($val === self::RANGE)
				{
					Range::prepareTo($filter, $id, $data[$id . "_to"]);
				} else
				{
					$filter["<=" . $id] = $data[$id . "_to"];
				}
			}
		}

		return $filter;
	}
}