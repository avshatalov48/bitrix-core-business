<?

namespace Bitrix\Main\Grid;


use Bitrix\Main\Application;

/**
 * Class Options of main.ui.grid
 *
 * @package Bitrix\Main\Grid
 */
class Options extends \CGridOptions
{
	/** @var string */
	protected $id;


	/**
	 * Options constructor.
	 *
	 * @param $gridId $arParams["GRID_ID"]
	 * @param array $filterPresets
	 */
	public function __construct($gridId, array $filterPresets = array())
	{
		$this->id = $gridId;
		parent::__construct($gridId, $filterPresets);

		if (!static::isAuthorized() &&
			isset(Application::getInstance()->getSession()["main.ui.grid.options"][$this->id]) &&
			is_array(Application::getInstance()->getSession()["main.ui.grid.options"][$this->id]) &&
			!empty(Application::getInstance()->getSession()["main.ui.grid.options"][$this->id]))
		{
			$this->all_options = Application::getInstance()->getSession()["main.ui.grid.options"][$this->id];
		}
	}


	/**
	 * Gets grid id
	 * @return string $arParams["GRID_ID"]
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Gets $USER object
	 * @return \CUser
	 */
	protected static function getUser()
	{
		global $USER;
		return $USER;
	}


	/**
	 * Sets width of grid columns
	 * @param number $expand
	 * @param array $sizes
	 */
	public function setColumnsSizes($expand, $sizes)
	{
		$columns = array();
		foreach ((array) $sizes as $name => $width)
		{
			$name  = trim($name);
			$width = is_scalar($width) ? (int) $width : 0;
			if ($name != '' && $width > 0)
				$columns[$name] = $width;
		}

		$this->all_options['views'][$this->currentView]['columns_sizes'] = array(
			'expand'  => is_scalar($expand) ? round((float) $expand, 8) : 1,
			'columns' => $columns
		);
	}


	/**
	 * Sets page size
	 * @param int $size
	 */
	public function setPageSize($size)
	{
		$size = is_scalar($size) ? (int) $size : 20;
		$size = $size >= 0 ? $size : 20;

		$this->all_options['views'][$this->currentView]['page_size'] = $size;
	}


	/**
	 * Sets custom names of grid columns
	 * @param array $names
	 */
	public function setCustomNames($names)
	{
		$this->all_options["views"]["default"]["custom_names"] = $names;
	}


	/**
	 * Resets saved expanded ids of rows
	 */
	public function resetExpandedRows()
	{
		$this->setExpandedRows();
	}


	/**
	 * Sets ids of expanded rows
	 * @param array [$ids = array()]
	 */
	public function setExpandedRows($ids = array())
	{
		Application::getInstance()->getSession()["main.ui.grid"][$this->getId()]["expanded_rows"] = $ids;
	}


	/**
	 * Gets ids of expanded rows
	 * @return array|null
	 */
	public function getExpandedRows()
	{
		return Application::getInstance()->getSession()["main.ui.grid"][$this->getId()]["expanded_rows"] ?? null;
	}


	/**
	 * Sets collapsed groups
	 * @param array $ids
	 */
	public function setCollapsedGroups($ids = array())
	{
		Application::getInstance()->getSession()["main.ui.grid"][$this->getId()]["collapsed_groups"] = is_array($ids) ? $ids : array();
	}


	/**
	 * Gets ids of collapsed groups
	 * @return ?array
	 */
	public function getCollapsedGroups()
	{
		return Application::getInstance()->getSession()["main.ui.grid"][$this->getId()]["collapsed_groups"] ?? null;
	}


	/**
	 * Resets view settings by view id
	 * @param string $viewId
	 */
	public function resetView($viewId)
	{
		$gridId = $this->getId();
		$this->all_options["views"][$viewId] = array();
		unset(Application::getInstance()->getSession()["main.interface.grid"][$gridId]);
		unset(Application::getInstance()->getSession()["main.ui.grid"][$gridId]);
		$this->Save();
		parent::__construct($gridId);
	}


	/**
	 * Deletes view settings by view id
	 * @param string $viewId
	 */
	public function deleteView($viewId)
	{
		$gridId = $this->getId();
		unset($this->all_options["views"][$viewId]);
		unset(Application::getInstance()->getSession()["main.interface.grid"][$gridId]);
		unset(Application::getInstance()->getSession()["main.ui.grid"][$gridId]);
		$this->Save();
		parent::__construct($gridId);
	}


	/**
	 * @return array
	 */
	public function getCurrentOptions()
	{
		$options = $this->getOptions();
		$currentViewId = $options["current_view"];
		return $options["views"][$currentViewId];
	}


	/**
	 * @return array
	 */
	private static function getDefaultGetSortingResult()
	{
		return array(
			"sort" => array(),
			"vars" => array(
				"by" => "by",
				"order" => "order"
			)
		);
	}


	/**
	 * Gets current grid sorting
	 * @param array [$default = array()] - Default value
	 * @return array
	 */
	public function getSorting($default = array())
	{
		$result = static::getDefaultGetSortingResult();
		$result["sort"] = isset($default["sort"]) && is_array($default["sort"]) ? $default["sort"] : $result["sort"];
		$result["vars"] = isset($default["vars"]) && is_array($default["vars"]) ? $default["vars"] : $result["vars"];

		$options = $this->getCurrentOptions();

		if (!empty($options["last_sort_by"]) && !empty($options["last_sort_order"]))
		{
			$result["sort"] = array($options["last_sort_by"] => $options["last_sort_order"]);
		}

		return $result;
	}


	/**
	 * Gets current user id
	 * @return int
	 */
	protected static function getUserId()
	{
		$userId = static::getUser()->getID();
		return is_scalar($userId) ? (int) $userId : 0;
	}


	/**
	 * Checks that current user is authorized
	 * @return bool
	 */
	protected static function isAuthorized()
	{
		return static::getUser()->isAuthorized();
	}


	/**
	 * Saves all options
	 */
	public function save()
	{
		$gridId = $this->getId();

		if (static::getUser()->isAuthorized())
		{
			\CUserOptions::setOption("main.interface.grid", $gridId, $this->all_options);
		}
		else
		{
			Application::getInstance()->getSession()["main.ui.grid.options"][$gridId] = $this->all_options;
		}
	}


	/**
	 * Gets used columns
	 * @param array $defaultColumns
	 * @return array
	 */
	public function getUsedColumns($defaultColumns = array())
	{
		$currentOptions = $this->getCurrentOptions();

		if (is_string($currentOptions["columns"]) && $currentOptions["columns"] !== "")
		{
			return explode(",", $currentOptions["columns"]);
		}

		return $defaultColumns;
	}


	/**
	 * Sets sticked columns
	 * @param string[] $columns
	 */
	public function setStickedColumns($columns = [])
	{
		$this->all_options["views"]["default"]["sticked_columns"] = is_array($columns) ? $columns : [];
	}

	/**
	 * Gets sticked columns
	 * @return string[]|null
	 */
	public function getStickedColumns()
	{
		$currentOptions = $this->getCurrentOptions();

		if (isset($currentOptions["sticked_columns"]) && is_array($currentOptions["sticked_columns"]))
		{
			return $currentOptions["sticked_columns"];
		}

		return null;
	}
}