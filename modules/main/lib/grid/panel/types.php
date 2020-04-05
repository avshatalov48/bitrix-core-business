<?

namespace Bitrix\Main\Grid\Panel;


/**
 * Group actions panel control types
 * @package Bitrix\Main\Grid\Panel
 */
class Types
{
	const DROPDOWN = "DROPDOWN";
	const CHECKBOX = "CHECKBOX";
	const TEXT = "TEXT";
	const BUTTON = "BUTTON";
	const LINK = "LINK";
	const CUSTOM = "CUSTOM";
	const HIDDEN = "HIDDEN";
	const DATE = "DATE";


	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}