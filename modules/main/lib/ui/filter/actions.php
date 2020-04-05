<?

namespace Bitrix\Main\UI\Filter;


/**
 * Class Actions. Available actions for works with user options.
 * @package Bitrix\Main\UI\Filter
 */
class Actions
{
	const SET_FILTER = "SET_FILTER";
	const REMOVE_FILTER = "REMOVE_FILTER";
	const SET_FILTER_ARRAY = "SET_FILTER_ARRAY";
	const RESTORE_FILTER = "RESTORE_FILTER";
	const SET_TMP_PRESET = "SET_TMP_PRESET";
	const PIN_PRESET = "PIN_PRESET";


	/**
	 * Gets actions list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}