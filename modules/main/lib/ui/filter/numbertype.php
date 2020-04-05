<?

namespace Bitrix\Main\UI\Filter;


/**
 * Class NumberType. Subtypes of number field
 * @package Bitrix\Main\UI\Filter
 */
class NumberType
{
	const SINGLE = "exact";
	const RANGE = "range";
	const MORE = "more";
	const LESS = "less";


	/**
	 * Gets number field types list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}