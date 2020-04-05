<?

namespace Bitrix\Main\Grid;


/**
 * Class Actions. Types of grid actions
 * @package Bitrix\Main\Grid
 */
class Actions
{
	const GRID_SET_COLUMNS = "setColumns";
	const GRID_SET_THEME = "setTheme";
	const GRID_SAVE_SETTINGS = "saveSettings";
	const GRID_DELETE_VIEW = "deleteView";
	const GRID_SET_VIEW = "setView";
	const GRID_SET_FILTER_ROWS = "setFilterRows";
	const GRID_SAVE_FILTER_SETTINGS = "saveFilterSettings";
	const GRID_DELETE_FILTER = "deleteFilter";
	const GRID_SET_FILTER_SWITCH = "setFilterSwitch";
	const GRID_SET_SORT = "setSort";
	const GRID_SET_COLUMN_SIZES = "setColumnSizes";
	const GRID_SET_PAGE_SIZE = "setPageSize";
	const GRID_RESET = "gridReset";
	const SET_CUSTOM_NAMES = "setCustomNames";
	const GRID_GET_CHILD_ROWS = "getChildRows";
	const GRID_UPDATE_ROW = "updateRow";
	const GRID_DELETE_ROW = "deleteRow";
	const GRID_ADD_ROW = "addRow";
	const GRID_SET_EXPANDED_ROWS = "setExpandedRows";
	const GRID_RESET_EXPANDED_ROWS = "resetExpandedRows";
	const GRID_SAVE_ROWS_SORT = "saveRowsSort";
	const GRID_SAVE_BATH = "saveBath";
	const GRID_SET_COLLAPSED_GROUPS = "setCollapsedGroups";


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