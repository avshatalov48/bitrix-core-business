<?php
namespace Bitrix\Main\Filter;
use \Bitrix\Main\UI\Filter\NumberType;
class Range
{
	public static function prepareFrom(array &$filter, $name, $value)
	{
		if($value !== '')
		{
			$typeKey = "{$name}_numsel";
			$operation = '>=';
			if(isset($filter[$typeKey]))
			{
				if($filter[$typeKey] === NumberType::MORE)
				{
					$operation = '>';
				}
				unset($filter[$typeKey]);
			}
			$filter["$operation{$name}"] = $value;
		}
		unset($filter["{$name}_from"]);

	}
	public static function prepareTo(array &$filter, $name, $value)
	{
		if($value !== '')
		{
			$typeKey = "{$name}_numsel";
			$operation = '<=';
			if(isset($filter[$typeKey]))
			{
				if($filter[$typeKey] === NumberType::LESS)
				{
					$operation = '<';
				}
				unset($filter[$typeKey]);
			}
			$filter["$operation{$name}"] = $value;
		}
		unset($filter["{$name}_to"]);

	}
}