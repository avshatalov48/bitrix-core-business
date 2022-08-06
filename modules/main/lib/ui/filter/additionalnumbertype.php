<?

namespace Bitrix\Main\UI\Filter;


/**
 * Class AdditionalNumberType. Available additional subtypes of number field
 * @package Bitrix\Main\UI\Filter
 */
class AdditionalNumberType
{
	const BEFORE_N = "before_n";


	/**
	 * Gets subtypes list of number field
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}