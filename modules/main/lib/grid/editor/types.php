<?

namespace Bitrix\Main\Grid\Editor;


/**
 * Class Types. Inline editor field types
 * @package Bitrix\Main\Grid\Editor
 */
class Types
{
	const DROPDOWN = "DROPDOWN";
	const CHECKBOX = "CHECKBOX";
	const TEXT = "TEXT";
	const DATE = "DATE";
	const NUMBER = "NUMBER";
	const RANGE = "RANGE";
	const TEXTAREA = "TEXTAREA";
	const CUSTOM = "CUSTOM";
	const IMAGE = "IMAGE";
	const MONEY = "MONEY";
	const MULTISELECT = "MULTISELECT";


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