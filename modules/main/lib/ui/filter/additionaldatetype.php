<?

namespace Bitrix\Main\UI\Filter;


/**
 * Class AdditionalDateType. Available additional subtypes of date field
 * @package Bitrix\Main\UI\Filter
 */
class AdditionalDateType
{
	const CUSTOM_DATE = "CUSTOM_DATE";
	const PREV_DAY = "PREV_DAY";
	const NEXT_DAY = "NEXT_DAY";
	const MORE_THAN_DAYS_AGO = "MORE_THAN_DAYS_AGO";
	const AFTER_DAYS = "AFTER_DAYS";


	/**
	 * Gets subtypes list of date field
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}