<?

namespace Bitrix\Main\Grid;


/**
 * Class Types. Column data types
 * @package Bitrix\Main\Grid
 */
class Types
{
	const GRID_CHECKBOX = "checkbox";
	const GRID_TEXT = "text";
	const GRID_INT = "int";
	const GRID_CUSTOM = "custom";
	const GRID_LIST = "list";
	const GRID_GRID = "grid";


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