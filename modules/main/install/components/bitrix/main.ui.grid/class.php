<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text;
use Bitrix\Main\Grid;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Web;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);


/**
 * Class CMainUIGrid
 */
class CMainUIGrid extends CBitrixComponent
{
	/** @var \CGridOptions $gridOptions */
	protected $gridOptions;

	/** @var Bitrix\Main\Web\Uri $uri */
	protected $uri;

	/** @var array $options */
	protected $options;

	/** @var array $showedColumnsList */
	protected $showedColumnsList;

	/** @var boolean $editDate default value $this->arResult["EDIT_DATE"] */
	protected $editDate = false;

	/** @var boolean $allowEdit default value $this->arResult["ALLOW_EDIT"] */
	protected $allowEdit = false;

	/** @var boolean $allowEditAll default value $this->arResult["ALLOW_EDIT_ALL"] */
	protected $allowEditAll = false;

	/** @var boolean $allowInlineEdit default value $this->arResult["ALLOW_INLINE_EDIT"] */
	protected $allowInlineEdit = false;

	/** @var boolean $allowInlineEditAll default value $this->arResult["ALLOW_INLINE_EDIT_ALL"] */
	protected $allowInlineEditAll = false;

	/** @var array $dataForEdit default value $this->arResult["DATA_FOR_EDIT"] */
	protected $dataForEdit = array();

	/** @var array $columnsEditMeta default value $this->arResult["COLS_EDIT_META"] */
	protected $columnsEditMeta = array();

	/** @var array $columnsEditMetaAll default value $this->arResult["COLS_EDIT_META_ALL"] */
	protected $columnsEditMetaAll = array();

	/** @var string $navString default value $this->arResult["NAV_STRING"] */
	protected $navString = "";

	/** @var integer $totalRowsCount default value $this->arResult["TOTAL_ROWS_COUNT"] */
	protected $totalRowsCount = 0;

	/** @var boolean $showBottomPanel default value $this->arResult["SHOW_BOTTOM_PANEL"] */
	protected $showBottomPanel = true;

	/** @var boolean $showMoreButton default value $this->arResult["SHOW_MORE_BUTTON"] */
	protected $showMoreButton = false;

	protected $defaultPageSize = 20;

	protected $minColumnWidth = 70;
	protected $jsFolder = "/js/";
	protected $blocksFolder = "/blocks/";
	protected $cssFolder = "/css/";


	protected function validateColumn($column = array())
	{
		return (is_array($column) && isset($column["id"]) && is_string($column["id"]) && !empty($column["id"]));
	}

	protected function validateColumns($columns = array())
	{
		$result = true;

		foreach ($columns as $key => $column)
		{
			if (!$this->validateColumn($column))
			{
				$result = false;
				break;
			}
		}

		return $result;
	}


	protected function checkRequiredParams()
	{
		$messages = array();
		$returns = true;


		if (!isset($this->arParams["GRID_ID"]) ||
			!is_string($this->arParams["GRID_ID"]) ||
			(is_string($this->arParams["GRID_ID"]) && empty($this->arParams["GRID_ID"])))
		{
			$messages[]["MESSAGE"] = Loc::getMessage("GRID_ID_INCORRECT");
		}

		//region Columns
		if(
			!(isset($this->arParams["COLUMNS"]) && is_array($this->arParams["COLUMNS"])) &&
			!(isset($this->arParams["HEADERS"]) && is_array($this->arParams["HEADERS"]))
		)
		{
			$messages[]["MESSAGE"] = Loc::getMessage("GRID_COLUMNS_INCORRECT");
		}
		//endregion

		if (!empty($messages))
		{
			foreach ($messages as $key => $message)
			{
				ShowMessage($message);
			}

			$returns = false;
		}

		return $returns;
	}


	/**
	 * Prepares arParams
	 * @method prepareParams
	 * @return array
	 */
	protected function prepareParams()
	{
		$this->arParams["GRID_ID"] = Grid\Params::prepareString(
			array($this->arParams["GRID_ID"]),
			""
		);

		$this->arParams["SORT"] = Grid\Params::prepareArray(
			array($this->arParams["SORT"]),
			array()
		);

		//region Columns
		$this->arParams["COLUMNS"] = isset($this->arParams["COLUMNS"]) && is_array($this->arParams["COLUMNS"])
			? $this->arParams["COLUMNS"] : array();
		//For backward compatibility
		if(empty($this->arParams["COLUMNS"]))
		{
			$this->arParams["COLUMNS"] = isset($this->arParams["HEADERS"]) && is_array($this->arParams["HEADERS"])
				? $this->arParams["HEADERS"] : array();
		}
		//endregion



		$this->arParams["ROWS"] = Grid\Params::prepareArray(
			array($this->arParams["ROWS"]),
			array()
		);

		$this->arParams["TOTAL_ROWS_COUNT"] = Grid\Params::prepareInt(
			array($this->arParams["TOTAL_ROWS_COUNT"], $this->arParams["FOOTER"]["TOTAL_ROWS_COUNT"]),
			null
		);

		$this->arParams["AJAX_ID"] = Grid\Params::prepareString(
			array($this->arParams["AJAX_ID"]),
			""
		);

		$this->arParams["AGGREGATE_ROWS"] = Grid\Params::prepareArray(
			array($this->arParams["AGGREGATE_ROWS"]),
			array()
		);

		$this->arParams["NAV_PARAM_NAME"] = Grid\Params::prepareString(
			array($this->arParams["NAV_PARAM_NAME"]),
			null
		);

		$this->arParams["CURRENT_PAGE"] = Grid\Params::prepareInt(
			array($this->arParams["CURRENT_PAGE"]),
			null
		);

		$this->arParams["NAV_STRING"] = Grid\Params::prepareString(
			array($this->arParams["NAV_STRING"], $this->arParams["FOOTER"]["NAV_STRING"]),
			""
		);

		$this->arParams["ACTIONS_LIST"] = Grid\Params::prepareArray(
			array($this->arParams["ACTIONS_LIST"], $this->arParams["ACTIONS"]["list"]),
			array()
		);

		$this->arParams["PAGE_SIZES"] = Grid\Params::prepareArray(
			array($this->arParams["PAGE_SIZES"]),
			array()
		);

		$this->arParams["DEFAULT_PAGE_SIZE"] = Grid\Params::prepareInt(
			array($this->arParams["DEFAULT_PAGE_SIZE"]),
			$this->defaultPageSize
		);

		$this->arParams["ALLOW_INLINE_EDIT"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_INLINE_EDIT"], $this->arParams["EDITABLE"]),
			true
		);

		$this->arParams["SHOW_ROW_ACTIONS_MENU"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_ROW_ACTIONS_MENU"], $this->arParams["ROW_ACTIONS"]),
			true
		);

		$this->arParams["SHOW_ROW_CHECKBOXES"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_ROW_CHECKBOXES"]),
			true
		);

		$this->arParams["SHOW_NAVIGATION_PANEL"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_NAVIGATION_PANEL"]),
			true
		);

		$this->arParams["ALLOW_GROUP_ACTIONS"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_GROUP_ACTIONS"], $this->arParams["ACTION_ALL_ROWS"]),
			false
		);

		$this->arParams["SHOW_CHECK_ALL_CHECKBOXES"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_CHECK_ALL_CHECKBOXES"]),
			true
		);

		$this->arParams["ALLOW_CONTEXT_MENU"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_CONTEXT_MENU"]),
			false
		);

		$this->arParams["SHOW_GROUP_EDIT_BUTTON"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_GROUP_EDIT_BUTTON"]),
			false
		);

		$this->arParams["SHOW_GROUP_DELETE_BUTTON"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_GROUP_DELETE_BUTTON"]),
			false
		);

		$this->arParams["SHOW_GROUP_ACTIONS_HTML"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_GROUP_ACTIONS_HTML"]),
			false
		);

		$this->arParams["SHOW_SELECT_ALL_RECORDS_CHECKBOX"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_SELECT_ALL_RECORDS_CHECKBOX"]),
			false
		);

		$this->arParams["SHOW_GRID_SETTINGS_MENU"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_GRID_SETTINGS_MENU"]),
			true
		);

		$this->arParams["SHOW_MORE_BUTTON"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_MORE_BUTTON"]),
			false
		);

		$this->arParams["SHOW_PAGINATION"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_PAGINATION"]),
			true
		);

		$this->arParams["SHOW_PAGESIZE"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_PAGESIZE"]),
			false
		);

		$this->arParams["SHOW_SELECTED_COUNTER"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_SELECTED_COUNTER"]),
			true
		);

		$this->arParams["SHOW_TOTAL_COUNTER"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_TOTAL_COUNTER"]),
			true
		);

		$this->arParams["ALLOW_COLUMNS_SORT"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_COLUMNS_SORT"]),
			true
		);

		$this->arParams["ALLOW_ROWS_SORT"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_ROWS_SORT"]),
			false
		);

		$this->arParams["ALLOW_SELECT_ROWS"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_SELECT_ROWS"]),
			false
		);

		$this->arParams["ALLOW_HORIZONTAL_SCROLL"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_HORIZONTAL_SCROLL"]),
			true
		);

		$this->arParams["ALLOW_SORT"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_SORT"]),
			true
		);

		$this->arParams["ALLOW_COLUMNS_RESIZE"] = Grid\Params::prepareBoolean(
			array($this->arParams["ALLOW_COLUMN_RESIZE"]),
			true
		);

		$this->arParams["SHOW_ACTION_PANEL"] = Grid\Params::prepareBoolean(
			array($this->arParams["SHOW_ACTION_PANEL"]),
			true
		);

		$this->arParams["PRESERVE_HISTORY"] = isset($this->arParams["PRESERVE_HISTORY"])
			? (bool)$this->arParams["PRESERVE_HISTORY"] : false;

		$this->arParams["ENABLE_COLLAPSIBLE_ROWS"] = Grid\Params::prepareBoolean(
			array($this->arParams["ENABLE_COLLAPSIBLE_ROWS"]),
			false
		);

		$this->arParams["TILE_GRID_MODE"] = Grid\Params::prepareBoolean(
			array($this->arParams["TILE_GRID_MODE"]),
			false
		);

		$this->arParams["JS_CLASS_TILE_GRID_ITEM"] = Grid\Params::prepareString(
			array($this->arParams["JS_CLASS_TILE_GRID_ITEM"]),
			null
		);

		$this->arParams["TILE_SIZE"] = Grid\Params::prepareString(
			array($this->arParams["TILE_SIZE"]),
			null
		);

		if (is_array($this->arParams["ROW_LAYOUT"]) && !empty($this->arParams["ROW_LAYOUT"]))
		{
			$this->arParams["ALLOW_COLUMNS_SORT"] = false;
			$this->arParams["ALLOW_SORT"] = false;
		}

		$this->arParams["TOP_ACTION_PANEL_PINNED_MODE"] = Grid\Params::prepareBoolean(
			array($this->arParams["TOP_ACTION_PANEL_PINNED_MODE"]),
			false
		);

		$this->arParams["ADVANCED_EDIT_MODE"] = Grid\Params::prepareBoolean(
			array($this->arParams["ADVANCED_EDIT_MODE"]),
			false
		);

		return $this->arParams;
	}


	/**
	 * Prepares arResult
	 * @method prepareResult
	 * @return $this
	 */
	protected function prepareResult()
	{
		$this->arResult["GRID_ID"] = $this->arParams["GRID_ID"];
		$this->arResult["FORM_ID"] = $this->arParams["FORM_ID"];
		$this->arResult["COLUMNS_ALL"] = $this->prepareColumnsAll();
		$this->arResult["HEADERS_ALL"] = $this->prepareColumnsAll();
		$this->arResult["COLUMNS"] = $this->prepareColumns();
		$this->arResult["HEADERS"] = $this->prepareColumns();
		$this->arResult["OPTIONS"] = $this->getOptions();
		$this->arResult["COLS_NAMES"] = $this->prepareColumnNames();
		$this->arResult["COLS_RESIZE_META"] = $this->prepareColumnsResizeMeta();
		$this->arResult["COLS_EDIT_META"] = $this->prepareColumnsEditMeta();
		$this->arResult["COLS_EDIT_META_ALL"] = $this->prepareColumnsEditMetaAll();
		$this->arResult["ROWS"] = $this->prepareRows();
		$this->arResult["TILE_GRID_ITEMS"] = $this->prepareTileGridItems();
		$this->arResult["HAS_ACTIONS"] = $this->prepareHasActions();
		$this->arResult["EDIT_DATE"] = $this->prepareEditDate();
		$this->arResult["ALLOW_EDIT"] = $this->prepareAllowEdit();
		$this->arResult["ALLOW_EDIT_ALL"] = $this->prepareAllowEditAll();
		$this->arResult["ALLOW_INLINE_EDIT"] = $this->prepareAllowInlineEdit();
		$this->arResult["ALLOW_INLINE_EDIT_ALL"] = $this->prepareAllowInlineEditAll();
		$this->arResult["DATA_FOR_EDIT"] = $this->prepareDataForEdit();
		$this->arResult["NAV_STRING"] = $this->prepareNavString();
		$this->arResult["TOTAL_ROWS_COUNT"] = $this->prepareTotalRowsCount();
		$this->arResult["SHOW_BOTTOM_PANEL"] = $this->prepareShowBottomPanel();
		$this->arResult["SHOW_MORE_BUTTON"] = $this->prepareShowMoreButton();
		$this->arResult["NEXT_PAGE"] = $this->prepareNextPage();
		$this->arResult["NEXT_PAGE_URL"] = $this->prepareNextPageUrl();
		$this->arResult["AGGREGATE"] = $this->prepareAggregate();
		$this->arResult["IS_AJAX"] = $this->prepareIsAjax();
		$this->arResult["IS_INTERNAL"] = $this->prepareIsInternalRequest();
		$this->arResult["OPTIONS_HANDLER_URL"] = $this->prepareOptionsHandlerUrl();
		$this->arResult["OPTIONS_ACTIONS"] = $this->prepareOptionsActions();
		$this->arResult["ACTIONS_LIST_JSON"] = $this->prepareActionsListJson();
		$this->arResult["ACTIONS_LIST_CURRENT_JSON"] = $this->prepareActionsListCurrentJson();
		$this->arResult["PAGE_SIZES_JSON"] = $this->preparePageSizesJson();
		$this->arResult["PANEL_ACTIONS"] = $this->preparePanelActions();
		$this->arResult["PANEL_TYPES"] = $this->preparePanelTypes();
		$this->arResult["EDITOR_TYPES"] = $this->prepareEditorTypes();
		$this->arResult["BACKEND_URL"] = $this->prepareBackendUrl();
		$this->arResult["ALLOW_CONTEXT_MENU"] = $this->arParams["ALLOW_CONTEXT_MENU"];
		$this->arResult["DEFAULT_COLUMNS"] = $this->prepareDefaultColumns();
		$this->arResult["DEPTH"] = $this->prepareDepth();
		$this->arResult["MESSAGES"] = $this->prepareMessages($this->arParams["MESSAGES"]);
		$this->arResult["LAZY_LOAD"] = $this->arParams["LAZY_LOAD"];
		$this->arResult["HAS_STICKED_COLUMNS"] = !empty($this->getGridOptions()->getStickedColumns());
		$this->arResult["HANDLE_RESPONSE_ERRORS"] = (
			isset($this->arParams["HANDLE_RESPONSE_ERRORS"])
			&& $this->arParams["HANDLE_RESPONSE_ERRORS"] === true
		);

		return $this;
	}

	protected function prepareMessages($messages = array())
	{
		$result = array();
		$isArray = is_array($messages);
		$isAssociative = ($isArray && Collection::isAssociative($messages));

		if ($isArray && $isAssociative)
		{
			$result[] = self::prepareMessage($messages);
		}

		if ($isArray && !$isAssociative)
		{
			foreach ($messages as $key => $message)
			{
				$result[] = self::prepareMessage($message);
			}
		}

		if (is_string($messages) && $messages !== "")
		{
			$result[] = self::prepareMessage($messages);
		}

		return $result;
	}


	protected static function prepareMessage($message = array())
	{
		$result = array(
			"TYPE" => Grid\MessageType::MESSAGE,
			"TITLE" => "",
			"TEXT" => ""
		);

		if (is_string($message) && $message !== "")
		{
			$result["TEXT"] = $message;
		}

		if (is_array($message))
		{
			if (isset($message["TEXT"]))
			{
				$result["TEXT"] = $message["TEXT"];
			}

			if (isset($message["TITLE"]))
			{
				$result["TITLE"] = $message["TITLE"];
			}

			if (isset($message["TYPE"]))
			{
				$result["TYPE"] = $message["TYPE"];
			}
		}

		return $result;
	}

	protected function prepareDepth()
	{
		$request = $this->request;
		return $request["depth"] !== null ? $request["depth"] : 0;
	}

	protected function prepareDefaultColumns()
	{
		$columns = array();
		$commonColumns = null;

		global $USER;
		if (!$USER->CanDoOperation("edit_other_settings"))
		{
			$commonOptions = CUserOptions::getOption("main.interface.grid", $this->arParams["GRID_ID"], array());
			if (!empty($commonOptions) &&
				isset($commonOptions["views"]["default"]["columns"]) &&
				!empty($commonOptions["views"]["default"]["columns"]))
			{
				$commonColumns = explode(",", $commonOptions["views"]["default"]["columns"]);
			}
		}

		foreach ($this->arParams["COLUMNS"] as $key => $column)
		{
			$columns[$column["id"]] = $column;
			$columns[$column["id"]]["sort_url"] = $this->arResult["COLUMNS_ALL"][$column["id"]]["sort_url"];
			$columns[$column["id"]]["sort_by"] = $this->arResult["COLUMNS_ALL"][$column["id"]]["sort"];
			$columns[$column["id"]]["sort_order"] = $this->arResult["COLUMNS_ALL"][$column["id"]]["next_sort_order"];

			if (is_array($commonColumns))
			{
				$columns[$column["id"]]["default"] = in_array($column["id"], $commonColumns);
			}
		}

		return $columns;
	}

	protected function prepareBackendUrl()
	{
		$url = $this->arParams["BACKEND_URL"];

		if (!isset($this->arParams["BACKEND_URL"]) ||
			!is_string($this->arParams["BACKEND_URL"]) ||
			empty($this->arParams["BACKEND_URL"]))
		{
			$url = $this->getUri()->getUri();
		}

		return $url;
	}

	protected function prepareEditorTypes()
	{
		return Grid\Editor\Types::getList();
	}

	protected function preparePanelTypes()
	{
		return Grid\Panel\Types::getList();
	}

	protected function preparePanelActions()
	{
		return Grid\Panel\Actions::getList();
	}

	protected function preparePageSizesJson()
	{
		if (empty($this->arResult["PAGE_SIZES_JSON"]))
		{
			$this->arResult["PAGE_SIZES_JSON"] = $this->arParams["PAGE_SIZES"];
			$this->arResult["PAGE_SIZES_JSON"] = Web\Json::encode($this->arResult["PAGE_SIZES_JSON"]);
			$this->arResult["PAGE_SIZES_JSON"] = Text\Converter::getHtmlConverter()->encode($this->arResult["PAGE_SIZES_JSON"]);
		}

		return $this->arResult["PAGE_SIZES_JSON"];
	}


	protected function prepareActionsListJson()
	{
		if (empty($this->arResult["ACTIONS_LIST_JSON"]))
		{
			$this->arResult["ACTIONS_LIST_JSON"] = $this->arParams["ACTIONS_LIST"];
			$this->arResult["ACTIONS_LIST_JSON"] = Web\Json::encode($this->arResult["ACTIONS_LIST_JSON"]);
			$this->arResult["ACTIONS_LIST_JSON"] = Text\Converter::getHtmlConverter()->encode($this->arResult["ACTIONS_LIST_JSON"]);
		}

		return $this->arResult["ACTIONS_LIST_JSON"];
	}


	protected function prepareActionsListCurrentJson()
	{
		if (empty($this->arResult["ACTIONS_LIST_CURRENT_JSON"]))
		{
			$this->arResult["ACTIONS_LIST_CURRENT_JSON"] = $this->arParams["ACTIONS_LIST"][0];
			$this->arResult["ACTIONS_LIST_CURRENT_JSON"] = Web\Json::encode($this->arResult["ACTIONS_LIST_CURRENT_JSON"]);
			$this->arResult["ACTIONS_LIST_CURRENT_JSON"] = Text\Converter::getHtmlConverter()->encode($this->arResult["ACTIONS_LIST_CURRENT_JSON"]);
		}

		return $this->arResult["ACTIONS_LIST_JSON"];
	}

	protected function prepareOptionsActions()
	{
		return Grid\Actions::getList();
	}

	/**
	 * @return string
	 */
	protected function prepareOptionsHandlerUrl()
	{
		return join("/", array($this->getPath(), "settings.ajax.php"));
	}


	/**
	 * Checks request is ajax
	 * @return bool
	 */
	protected function prepareIsAjax()
	{
		return $this->request->isAjaxRequest();
	}


	/**
	 * Checks request is internal
	 * @return bool
	 */
	protected function prepareIsInternalRequest()
	{
		return (bool)$this->request->get("internal");
	}


	/**
	 * Prepares aggregate data
	 * @method prepareAggregate
	 * @return array
	 */
	protected function prepareAggregate()
	{
		if (empty($this->arResult["AGGREGATE"]))
		{
			$this->arResult["AGGREGATE"] = $this->arParams["AGGREGATE"];
		}

		return $this->arResult["AGGREGATE"];
	}


	/**
	 * Applies user settings
	 * @method applyUserSettings
	 * @return null
	 */
	protected function applyUserSettings()
	{
		$this->applyColumnsCustomNames();
		$this->applyColumnsDisplay();
		$this->applyRowsSort();
		$this->applyColumnsSticked();
		$this->applyColumnsSort();
		$this->applyColumnsSizes();
		return $this;
	}


	protected function applyColumnsSticked()
	{
		$gridOptions = $this->getGridOptions();
		$stickedColumns = $gridOptions->getStickedColumns();

		if (is_array($stickedColumns))
		{
			$this->arResult["HAS_STICKED_COLUMNS"] = false;

			foreach ($this->arResult["COLUMNS_ALL"] as $key => $column)
			{
				$this->arResult["COLUMNS_ALL"][$key]["sticked"] = in_array($column["id"], $stickedColumns);

				if ($this->isShownColumn($column))
				{
					$this->arResult["COLUMNS"][$key]["sticked"] = in_array($column["id"], $stickedColumns);

					if (!$this->arResult["HAS_STICKED_COLUMNS"] && $this->arResult["COLUMNS"][$key]["sticked"])
					{
						$this->arResult["HAS_STICKED_COLUMNS"] = true;
					}
				}
			}
		}

		Collection::sortByColumn($this->arResult["COLUMNS"], [
			"sticked" => SORT_DESC,
			"sort_index" => SORT_ASC
		]);
	}


	/**
	 * Applies columns sort setting
	 */
	protected function applyColumnsSort()
	{
		if (count($this->getShowedColumnsList()) > 0)
		{
			$this->arResult["COLUMNS"] = array();

			foreach ($this->getShowedColumnsList() as $key => $item)
			{
				$this->arResult["COLUMNS"][$item] = $this->arResult["COLUMNS_ALL"][$item];
				$this->arResult["COLUMNS"][$item]["sort_index"] = $key;
			}

			Collection::sortByColumn($this->arResult["COLUMNS"], [
				"sticked" => SORT_DESC,
				"sort_index" => SORT_ASC
			]);
		}
	}


	/**
	 * Applies rows sort setting
	 */
	protected function applyRowsSort()
	{
		$options = $this->getCurrentOptions();
		$this->getUri()->addParams(array(
			"by" => $options["last_sort_by"],
			"order" => $options["last_sort_order"]
		));

		$this->prepareNextPageUrl();
	}


	/**
	 * Applies custom column names
	 * @method applyColumnsCustomNames
	 * @return null
	 */
	protected function applyColumnsCustomNames()
	{
		if (is_array($this->getCustomNames()) && count($this->getCustomNames()) > 0)
		{
			foreach ($this->getCustomNames() as $key => $value)
			{
				if ($this->columnExists($key))
				{
					$this->arResult["COLUMNS_ALL"][$key]["name"] = Bitrix\Main\Text\Converter::getHtmlConverter()->decode($value);
				}
			}
		}

		return $this;
	}


	/**
	 * Applies custom column display
	 * @method applyColumnsDisplay
	 * @return null
	 */
	protected function applyColumnsDisplay()
	{
		if (count($this->getShowedColumnsList()) > 0)
		{
			$this->arResult["COLUMNS"] = array();

			foreach ($this->getShowedColumnsList() as $key => $id)
			{
				$this->arResult["COLUMNS_ALL"][$id]["is_shown"] = true;
				$this->arResult["COLUMNS"][$id] = $this->arResult["COLUMNS_ALL"][$id];
				$this->arResult["COLUMNS"][$id]["sort_index"] = $key;
			}
		}

		return $this;
	}


	/**
	 * Applies custom column sizes
	 * @return $this
	 */
	protected function applyColumnsSizes()
	{
		$options = $this->getCurrentOptions();
		$colSizes = $options["columns_sizes"]["columns"];

		foreach ($this->arResult["COLUMNS"] as $key => $item)
		{
			$colSize = $colSizes[$item["id"]];

			if (is_numeric($colSize) && $item["resizeable"] !== false)
			{
				$this->arResult["COLUMNS"][$key]["width"] = $colSize;
			}
		}

		return $this;
	}


	/**
	 * Prepares next page url
	 * @method prepareNextPageUrl
	 * @return string URI
	 */
	protected function prepareNextPageUrl()
	{
		$this->getUri()->addParams(array(
			$this->arParams["NAV_PARAM_NAME"] => $this->prepareNextPage()
		));

		$this->arResult["NEXT_PAGE_URL"] = $this->getUri()->getUri();

		return $this->arResult["NEXT_PAGE_URL"];
	}


	/**
	 * Prepares next page number
	 * @method prepareNextPage
	 * @return int Page number
	 */
	protected function prepareNextPage()
	{
		if (!$this->arResult["NEXT_PAGE"])
		{
			$this->arResult["NEXT_PAGE"] = $this->arParams["CURRENT_PAGE"] + 1;
		}

		return $this->arResult["NEXT_PAGE"];
	}


	/**
	 * Prepares show more button
	 * @method prepareShowMoreButton
	 * @return boolean
	 */
	protected function prepareShowMoreButton()
	{
		$this->arResult["SHOW_MORE_BUTTON"] = $this->showMoreButton;

		if ($this->arParams["ENABLE_NEXT_PAGE"] &&
			!empty($this->arParams["NAV_PARAM_NAME"]) &&
			is_string($this->arParams["NAV_PARAM_NAME"]) &&
			!empty($this->arParams["CURRENT_PAGE"]) &&
			is_numeric($this->arParams["CURRENT_PAGE"])
		)
		{
			$this->arResult["SHOW_MORE_BUTTON"] = true;
		}

		return $this->arResult["SHOW_MORE_BUTTON"];
	}


	/**
	 * Prepares show bottom panels
	 * @method prepareShowBottomPanel
	 * @return boolean
	 */
	protected function prepareShowBottomPanel()
	{
		if (!is_bool($this->arResult["SHOW_BOTTOM_PANEL"]))
		{
			$this->arResult["SHOW_MORE_BUTTON"] = $this->showBottomPanel;

			if (!$this->prepareNavString() && !$this->prepareTotalRowsCount())
			{
				$this->arResult["SHOW_BOTTOM_PANEL"] = false;
			}
		}

		return $this->arResult["SHOW_BOTTOM_PANEL"];
	}



	/**
	 * Prepares total rows count
	 * @method prepareTotalRowsCount
	 * @return integer
	 */
	protected function prepareTotalRowsCount()
	{
		if (!is_numeric($this->arResult["TOTAL_ROWS_COUNT"]))
		{
			$this->arResult["TOTAL_ROWS_COUNT"] = $this->arParams["TOTAL_ROWS_COUNT"];

			if (!is_numeric($this->arResult["TOTAL_ROWS_COUNT"]))
			{
				$this->arResult["TOTAL_ROWS_COUNT"] = $this->totalRowsCount;
			}
		}

		return $this->arResult["TOTAL_ROWS_COUNT"];
	}


	/**
	 * Prepares pagination string
	 * @method prepareNavString
	 * @return string
	 */
	protected function prepareNavString()
	{
		global $APPLICATION;

		if (!is_string($this->arResult["NAV_STRING"]))
		{
			$this->arResult["NAV_STRING"] = $this->navString;

			if ($this->arParams["NAV_STRING"] <> '')
			{
				$this->arResult["NAV_STRING"] = $this->arParams["NAV_STRING"];
			}
			elseif(is_object($this->arParams["NAV_OBJECT"]))
			{
				if(!is_array($this->arParams["~NAV_PARAMS"]))
				{
					$this->arParams["~NAV_PARAMS"] = array();
				}

				if(($nav = $this->arParams["NAV_OBJECT"]) instanceof \Bitrix\Main\UI\PageNavigation)
				{
					$params = array_merge(
						array(
							"NAV_OBJECT" => $nav,
							"PAGE_WINDOW" => 5,
							"SHOW_ALWAYS" => true,
						),
						$this->arParams["~NAV_PARAMS"]
					);

					ob_start();

					$APPLICATION->IncludeComponent(
						"bitrix:main.pagenavigation",
						"grid",
						$params,
						false,
						array(
							"HIDE_ICONS" => "Y",
						)
					);

					$this->arResult["NAV_STRING"] = ob_get_clean();
				}
				else
				{
					/** @noinspection PhpUndefinedClassInspection */
					/** @var CDBResult $nav */
					$nav = $this->arParams["NAV_OBJECT"];
					$nav->nPageWindow = 5;
					$this->arResult["NAV_STRING"] = $nav->GetPageNavStringEx($dummy, "", "grid", true, null, $this->arParams["~NAV_PARAMS"]);
				}
			}
		}

		return $this->arResult["NAV_STRING"];
	}


	/**
	 * Prepares data for edit form
	 * @method prepareDataForEdit
	 * @return array
	 */
	protected function prepareDataForEdit()
	{
		if (!is_array($this->arResult["DATA_FOR_EDIT"]))
		{
			$this->arResult["DATA_FOR_EDIT"] = $this->dataForEdit;

			foreach ($this->prepareRows() as $rowKey => $rowItem)
			{
				if ($rowItem["editable"] === false)
				{
					continue;
				}

				$id = !empty($rowItem["id"]) ? $rowItem["id"] : $rowItem["data"]["ID"];

				foreach ($this->prepareColumnsAll() as $headerKey => $headerItem)
				{
					if (isset($headerItem["editable"]) && $headerItem["editable"] !== false)
					{
						if (isset($rowItem["editable"][$headerKey]) && $rowItem["editable"][$headerKey] === false)
						{
							$this->arResult["DATA_FOR_EDIT"][$id][$headerKey] = false;
						}
						else
						{
							$this->arResult["DATA_FOR_EDIT"][$id][$headerKey] = isset($rowItem["data"]["~".$headerKey])
								? $rowItem["data"]["~".$headerKey] : $rowItem["data"][$headerKey];
						}
					}
				}
			}
		}

		return $this->arResult["DATA_FOR_EDIT"];
	}


	/**
	 * Prepares $this->arResult["ALLOW_INLINE_EDIT_ALL"] value
	 * @method prepareAllowInlineEditAll
	 * @return boolean
	 */
	protected function prepareAllowInlineEditAll()
	{
		if (!is_bool($this->arResult["ALLOW_INLINE_EDIT_ALL"]))
		{
			$this->arResult["ALLOW_INLINE_EDIT_ALL"] = $this->allowInlineEditAll;

			foreach ($this->prepareColumnsAll() as $key => $item)
			{
				if (isset($item["editable"]) && $item["editable"])
				{
					$this->arResult["ALLOW_INLINE_EDIT_ALL"] = true;
					break;
				}
			}
		}

		return $this->arResult["ALLOW_INLINE_EDIT_ALL"];
	}


	/**
	 * Prepares $this->arResult["ALLOW_INLINE_EDIT"] value
	 * @method prepareAllowInlineEdit
	 * @return boolean
	 */
	protected function prepareAllowInlineEdit()
	{
		if (!is_bool($this->arResult["ALLOW_INLINE_EDIT"]))
		{
			$this->arResult["ALLOW_INLINE_EDIT"] = $this->allowInlineEdit;

			foreach ($this->prepareRows() as $key => $item)
			{
				if (
					$item['id'] !== 'template_0'
					&& $this->arResult["ALLOW_INLINE_EDIT"] === $this->allowInlineEdit
				)
				{
					$this->arResult["ALLOW_INLINE_EDIT"] = ($item["editable"] !== false);
				}
			}
		}

		return $this->arResult["ALLOW_INLINE_EDIT"];
	}


	/**
	 * Prepares $this->arResult["ALLOW_EDIT"] value
	 * @method prepareAllowEdit
	 * @return boolean
	 */
	protected function prepareAllowEdit()
	{
		if (!is_bool($this->arResult["ALLOW_EDIT"]))
		{
			$this->arResult["ALLOW_EDIT"] = $this->allowEdit;
			$this->arResult["ALLOW_EDIT"] = $this->prepareAllowInlineEdit() || $this->prepareHasActions();
		}

		return $this->arResult["ALLOW_EDIT"];
	}


	/**
	 * Prepares $arResult["ALLOW_EDIT"] value
	 * @method prepareAllowEdit
	 * @return boolean
	 */
	protected function prepareAllowEditAll()
	{
		if (!is_bool($this->arResult["ALLOW_EDIT_ALL"]))
		{
			if ($this->arParams["EDITABLE"] && $this->prepareHasActions())
			{
				$this->arResult["ALLOW_EDIT_ALL"] = true;
			}
		}

		return $this->arResult["ALLOW_EDIT_ALL"];
	}


	/**
	 * Prepares $this->arResult["EDIT_DATE"]) value
	 * @method prepareEditDate
	 * @return boolean
	 */
	protected function prepareEditDate()
	{
		if (!is_bool($this->arResult["EDIT_DATE"]))
		{
			$this->arResult["EDIT_DATE"] = $this->editDate;

			foreach ($this->prepareColumnsAll() as $key => $item)
			{
				if ($this->arParams["EDITABLE"] && $item["editable"] !== false && $item["type"] === "date")
				{
					$this->arResult["EDIT_DATE"] = true;
					break;
				}
			}
		}

		return $this->arResult["EDIT_DATE"];
	}


	/**
	 * Prepares $this->arResult["HAS_ACTION"]) value
	 * @method prepareHasActions
	 * @return boolean
	 */
	protected function prepareHasActions()
	{
		if (!is_bool($this->arResult["HAS_ACTIONS"]))
		{
			$this->arResult["HAS_ACTIONS"] = (
				is_array($this->arParams["ACTIONS"]) &&
				count($this->arParams["ACTIONS"]) > 0
			);
		}

		return $this->arResult["HAS_ACTIONS"];
	}

	protected function compatibleActions($actions, &$row)
	{
		foreach ($actions as $key => $action)
		{
			if (isset($action["SEPARATOR"]))
			{
				$isDelimiter = $action["SEPARATOR"];

				$actions[$key]["delimiter"] = $isDelimiter;
				unset($actions[$key]["SEPARATOR"]);

				if($isDelimiter)
				{
					continue;
				}
			}

			if (isset($action["ICONCLASS"]))
			{
				$actions[$key]["className"] = $action["ICONCLASS"];
				unset($actions[$key]["ICONCLASS"]);
			}

			if (isset($action["TITLE"]))
			{
				$actions[$key]["title"] = $action["TITLE"];
				unset($actions[$key]["TITLE"]);
			}

			if (isset($action["TEXT"]))
			{
				$actions[$key]["text"] = $action["TEXT"];
				unset($actions[$key]["TEXT"]);
			}

			if (isset($action["ONCLICK"]))
			{
				$actions[$key]["onclick"] = $action["ONCLICK"];
				unset($actions[$key]["ONCLICK"]);
			}

			if (isset($action["DEFAULT"]))
			{
				$actions[$key]["default"] = $action["DEFAULT"];
				unset($actions[$key]["DEFAULT"]);
			}

			if (isset($action["MENU"]) && is_array($action["MENU"]) && !empty($action["MENU"]))
			{
				$actions[$key]["items"] = $this->compatibleActions($action["MENU"], $row);
				unset($actions[$key]["MENU"]);
			}

			if (isset($action["HREF"]))
			{
				$actions[$key]["href"] = $action["HREF"];
				unset($actions[$key]["HREF"]);
			}

			if (isset($row["default_action"]) && is_array($row["default_action"]))
			{
				if (isset($row["default_action"]["href"]) && is_string($row["default_action"]["href"]))
				{
					$row["default_action"]["js"] = "(window.location = '".$row["default_action"]["href"]."')";
				}
				elseif (isset($row["default_action"]["onclick"]) && is_string($row["default_action"]["onclick"]))
				{
					$row["default_action"]["js"] = $row["default_action"]["onclick"];
				}
			}
			else
			{
				if ($action["default"] === true)
				{
					$row["default_action"] = array();

					if (isset($action["onclick"]) && is_string($action["onclick"]))
					{
						$row["default_action"]["js"] = $action["onclick"];
					}

					if (isset($action["href"]) && is_string($action["href"]))
					{
						$row["default_action"]["js"] = "(window.location = '".$action["href"]."')";
					}

					$row["default_action"]["title"] = isset($action["text"]) ? $action["text"] : "";
				}
			}
		}

		return $actions;
	}


	protected function compatibleRow($row)
	{
		if (isset($row["actions"]) && is_array($row["actions"]) && !empty($row["actions"]))
		{
			$row["actions"] = $this->compatibleActions($row["actions"], $row);
		}

		$row["attrs_string"] = "";

		if (isset($row["attrs"]) && is_array($row["attrs"]) && !empty($row["attrs"]))
		{
			$attrsString = " ";

			foreach ($row["attrs"] as $key => $value)
			{
				if (is_array($value))
				{
					$escapedValue = Text\HtmlFilter::encode(CUtil::PhpToJSObject($value));
				}
				else
				{
					$escapedValue = Text\HtmlFilter::encode($value);
				}

				$escapedKey = Text\HtmlFilter::encode($key);
				$attrsString .= $escapedKey."=\"".$escapedValue."\"";
			}

			$attrsString .= " ";
			$row["attrs_string"] = $attrsString;
		}

		if(!isset($row["id"]))
		{
			$row["id"] = $row["data"]["ID"];
		}

		return $row;
	}


	/**
	 * Prepares rows
	 * @method prepareRows
	 * @return array
	 */
	protected function prepareRows()
	{
		foreach ($this->arParams["ROWS"] as $key => $row)
		{
			//Prepare default values
			$actualRow = $this->compatibleRow($row);
			if(isset($this->arParams["COLUMNS"]) && (isset($actualRow["columns"]) || isset($actualRow["data"])))
			{
				foreach($this->arParams["COLUMNS"] as $header)
				{
					$id = $header["id"];
					if(!isset($actualRow["columns"][$id]) && isset($actualRow["data"][$id]))
					{
						$actualRow["columns"][$id] = $actualRow["data"][$id];
					}
				}
			}
			$this->arParams["ROWS"][$key] = $actualRow;

			if (!isset($this->arParams["ROWS"][$key]["editableColumns"]))
			{
				$this->arParams["ROWS"][$key]["editableColumns"] = [];
			}
		}

		return $this->arParams["ROWS"];
	}

	protected function prepareTileGridItems()
	{
		if (!$this->arParams['TILE_GRID_MODE'])
		{
			return null;
		}
		$this->setTemplateName('tilegrid');

		if (isset($this->arParams['TILE_GRID_ITEMS']))
		{
			return $this->arParams['TILE_GRID_ITEMS'];
		}

		return $this->reformatRowsToTileGridItems($this->arParams['ROWS']);
	}

	protected function reformatRowsToTileGridItems($rows)
	{
		$items = [];

		foreach ($rows as $row)
		{
			$items[] = [
				'id' => $row['ID'],
				'name' => isset($row['NAME'])? $row['NAME'] : null,
				'image' => isset($row['IMAGE'])? $row['IMAGE'] : null,
			];
		}

		return $items;
	}

	protected function prepareColumnsEditMeta()
	{
		if (!is_array($this->arResult["COLS_EDIT_META"]))
		{
			$this->arResult["COLS_EDIT_META"] = $this->columnsEditMeta;
			$columns = $this->prepareColumnsEditMetaAll();

			foreach ($this->prepareColumns() as $key => $item)
			{
				$this->arResult["COLS_EDIT_META"][$key] = $columns[$key];
			}
		}

		return $this->arResult["COLS_EDIT_META"];
	}


	/**
	 * Prepares $this->arResult["COLS_EDIT_META_ALL"] value
	 * @method prepareColumnsEditMetaAll
	 * @return array
	 */
	protected function prepareColumnsEditMetaAll()
	{
		if (!is_array($this->arResult["COLS_EDIT_META_ALL"]))
		{
			$this->arResult["COLS_EDIT_META_ALL"] = $this->columnsEditMetaAll;

			foreach ($this->prepareColumnsAll() as $key => $item)
			{
				$this->arResult["COLS_EDIT_META_ALL"][$key] = array(
					"editable" => $item["editable"] ? true : false,
					"type" => $item["type"] ? $item["type"] : Grid\Types::GRID_TEXT
				);

				if ($this->arParams["EDITABLE"] && is_array($item["editable"]))
				{
					foreach ($item["editable"] as $attrKey => $attrValue)
					{
						$this->arResult["COLS_EDIT_META_ALL"][$key][$attrKey] = $attrValue;
					}
				}
			}
		}

		return $this->arResult["COLS_EDIT_META_ALL"];
	}


	/**
	 * Prepares $this->arResult["COLS_RESIZE_META"] value
	 * @method prepareColumnsResizeMeta
	 * @return array
	 */
	protected function prepareColumnsResizeMeta()
	{
		if (empty($this->arResult["COLS_RESIZE_META"]))
		{
			$this->arResult["COLS_RESIZE_META"] = array("expand" => 1, "columns" => array());
		}

		return $this->arResult["COLS_RESIZE_META"];
	}


	/**
	 * Prepares $this->arResult["COLS_NAMES"] value
	 * @method prepareColumnNames
	 * @return array
	 */
	protected function prepareColumnNames()
	{
		if (empty($this->arResult["COLS_NAMES"]))
		{
			$this->arResult["COLS_NAMES"] = array();

			foreach ($this->prepareColumnsAll() as $key => $item)
			{
				$this->arResult["COLS_NAMES"][$key] = $item["name"];
			}
		}

		return $this->arResult["COLS_NAMES"];
	}


	/**
	 * Gets deleted query string params
	 * @method getDeleteParams
	 * @return array params list
	 */
	protected function getDeleteParams()
	{
		$sortParams = array(
			"bxajaxid",
			"AJAX_CALL",
			$this->arParams["SORT_VARS"]["by"],
			$this->arParams["SORT_VARS"]["order"]
		);

		return $sortParams;
	}


	/**
	 * Gets options
	 * @method getOptions
	 * @return array
	 */
	protected function getOptions()
	{
		return $this->getGridOptions()->GetOptions();
	}


	/**
	 * @return Grid\Options|CGridOptions
	 */
	protected function getGridOptions()
	{
		if (!($this->gridOptions instanceof Grid\Options))
		{
			$this->gridOptions = new Grid\Options($this->arParams["GRID_ID"]);
		}

		return $this->gridOptions;
	}


	/**
	 * Gets current view options
	 * @method getCurrentOptions
	 * @return array
	 */
	protected function getCurrentOptions()
	{
		$options = $this->getOptions();
		return $options["views"][$options["current_view"]];
	}


	/**
	 * Gets uri object
	 * @method getUri
	 * @return Bitrix\Main\Web\Uri instance with current request uri
	 */
	protected function getUri()
	{
		if (!($this->uri instanceof Web\Uri))
		{
			$this->uri = new Web\Uri($this->request->getRequestUri());
			$this->uri->deleteParams($this->getDeleteParams());

			if (!empty($this->arParams['TAB_ID']) && !empty($this->arParams["FORM_ID"]))
			{
				$this->uri->addParams(array(
					$this->arParams["FORM_ID"]."_active_tab" => $this->arParams["TAB_ID"]
				));
			}
		}

		return $this->uri;
	}


	/**
	 * Gets sort url
	 * @method getSortUrl
	 * @param  array $headerItem
	 * @return string sort url
	 */
	protected function getSortUrl(array $headerItem)
	{
		$this->getUri()->addParams(array(
			"by" => $headerItem["sort"],
			"order" => $headerItem["next_sort_order"]
		));

		return $this->getUri()->getUri();
	}

	protected function columnExists($id)
	{
		$result = false;

		if (is_array($this->arParams["COLUMNS"]) && !empty($this->arParams["COLUMNS"]))
		{
			foreach ($this->arParams["COLUMNS"] as $key => $column)
			{
				if ($column["id"] == $id)
				{
					$result = true;
				}
			}
		}

		return $result;
	}

	/**
	 * Gets showed columns list
	 * @method getShowedColumnsList
	 * @return array
	 */
	protected function getShowedColumnsList()
	{
		if (empty($this->showedColumnsList))
		{
			$options = $this->getCurrentOptions();
			$tmp = explode(",", $options["columns"]);

			$this->showedColumnsList = array();

			foreach ($tmp as $key => $item)
			{
				$item = trim($item);

				if (!empty($item) && $this->columnExists($item))
				{
					$this->showedColumnsList[] = $item;
				}
			}
		}

		return $this->showedColumnsList;
	}


	/**
	 * Checks is user enabled column
	 * @method isUserShowedColumn
	 * @param  string $id column id
	 * @return boolean
	 */
	protected function isUserShowedColumn($id)
	{
		return in_array($id, $this->getShowedColumnsList());
	}


	/**
	 * Prepares align value
	 * @method prepareAlign
	 * @param  array $headerItem header item
	 * @return string css align property value
	 */
	protected function prepareAlign($headerItem)
	{
		return "left";
	}


	/**
	 * Prepares string
	 * @method prepareString
	 * @param  string $text source string
	 * @return string prepared string
	 */
	protected function prepareString($text)
	{
		return Text\Converter::getHtmlConverter()->encode($text);
	}


	/**
	 * Gets custom column names
	 * @method getCustomNames
	 * @return array [column_id] => column_name
	 */
	protected function getCustomNames()
	{
		$options = $this->getCurrentOptions();
		return $options["custom_names"];

	}


	/**
	 * Prepares header name
	 * @method prepareHeaderName
	 * @param  array $headerItem header item
	 * @return string prepared header name
	 */
	protected function prepareHeaderName(array $headerItem)
	{
		return $headerItem["name"];
	}


	/**
	 * Checks is shown header name for current header item
	 * @method isShowHeaderName
	 * @param  array $headerItem
	 * @return boolean
	 */
	protected function isShowHeaderName(array $headerItem)
	{
		$isShow = true;

		if ($headerItem['showname'] === false || $headerItem['showname'] === "N")
		{
			$isShow = false;
		}

		return $isShow;
	}


	/**
	 * Prepares sort order value
	 * @method prepareSortOrder
	 * @param  array $headerItem header item
	 * @return string sort order value ["desc", "asc"]
	 */
	protected function prepareSortOrder(array $headerItem)
	{
		$sort = $headerItem["ORDER"] === "desc" ? "desc" : "asc";

		if (isset($headerItem["first_order"]) && is_string($headerItem["first_order"]))
		{
			$sort = $headerItem["first_order"];
		}

		return $sort;
	}


	/**
	 * Checks is shown current column
	 * @method isShownColumn
	 * @param  array $headerItem header item
	 * @return boolean
	 */
	protected function isShownColumn(array $headerItem)
	{
		return $headerItem["default"];
	}


	/**
	 * Prepares sort state value
	 * @method prepareSortState
	 * @param  array $headerItem header item
	 * @return string ["desc", "asc"]
	 */
	protected function prepareSortState(array $headerItem)
	{
		$state = null;

		if (isset($this->arParams["SORT"]) &&
			is_array($this->arParams["SORT"]) &&
			(is_string($headerItem["sort"]) || is_int($headerItem["sort"])) &&
			array_key_exists($headerItem["sort"], $this->arParams["SORT"]))
		{
			$state = $this->arParams["SORT"][$headerItem["sort"]];
		}
		else
		{
			$options = $this->getCurrentOptions();

			if ($options["last_sort_by"] === $headerItem["sort"])
			{
				$state = $options["last_sort_order"];
			}
		}

		return $state;
	}

	protected function prepareNextSortOrder(array $headerItem)
	{
		$sortState = $this->prepareSortState($headerItem);

		if ($sortState)
		{
			$nextSort = $sortState === "asc" ? "desc" : "asc";
		}
		else
		{
			$nextSort = $this->prepareSortOrder($headerItem);
		}

		return $nextSort;
	}

	protected function prepareSort($sort)
	{
		return $sort;
	}


	protected function prepareEditable($column)
	{
		$result = isset($column["editable"]) ? $column["editable"] : false;
		//For backward compatibility
		if($result === true)
		{
			$result = array();
		}

		if(is_array($result))
		{
			if(!(isset($result["NAME"]) && is_string($result["NAME"]) && $result["NAME"] !== ""))
			{
				$result["NAME"] = $column["id"];
			}

			$typeName = isset($result["TYPE"]) && is_string($result["TYPE"]) ? $result["TYPE"] : "";
			if($typeName === "")
			{
				$columnTypeName = isset($column["type"]) && is_string($column["type"]) ? $column["type"] : "";
				if($columnTypeName === "")
				{
					$columnTypeName = "text";
				}

				if($columnTypeName === "text")
				{
					$typeName = Grid\Editor\Types::TEXT;
				}
				elseif($columnTypeName === "int" || $columnTypeName === "double" || $columnTypeName === "number")
				{
					$typeName = Grid\Editor\Types::NUMBER;
				}
				elseif($columnTypeName === "checkbox")
				{
					$typeName = Grid\Editor\Types::CHECKBOX;
				}
				elseif($columnTypeName === "date")
				{
					$typeName = Grid\Editor\Types::DATE;
				}
				elseif($columnTypeName === "list")
				{
					$typeName = Grid\Editor\Types::DROPDOWN;
				}
				elseif($columnTypeName === "money")
				{
					$typeName = Grid\Editor\Types::MONEY;
				}
				$result["TYPE"] = $typeName;
			}

			if($result["TYPE"] === Grid\Editor\Types::MONEY	&& is_array($result["CURRENCY_LIST"]))
			{
				$currencyList = is_array($result["CURRENCY_LIST"]) ? $result["CURRENCY_LIST"] : [];
				$result["CURRENCY_LIST"] = [];
				foreach($currencyList as $k => $v)
				{
					$result["CURRENCY_LIST"][] = array("VALUE" => $k, "NAME" => $v);
				}
			}

			if($result["TYPE"] === Grid\Editor\Types::DROPDOWN
				&& $result["items"] && is_array($result["items"])
				&& !(isset($result["DATA"]) && isset($result["DATA"]["ITEMS"]) && is_array($result["DATA"]["ITEMS"])))
			{
				if(!isset($result["DATA"]))
				{
					$result["DATA"] = array();
				}

				if(!isset($result["DATA"]["ITEMS"]))
				{
					$result["DATA"]["ITEMS"] = array();
				}

				foreach($result["items"] as $k => $v)
				{
					$result["DATA"]["ITEMS"][] = array("VALUE" => $k, "NAME" => $v);
				}

				unset($result["items"]);
			}
		}

		return $result;
	}


	/**
	 * Prepares each column
	 * @method prepareColumn
	 * @param  array $column header item
	 * @return array prepared header item
	 */
	protected function prepareColumn(array $column)
	{
		$column["sort_state"] = $this->prepareSortState($column);
		$column["next_sort_order"] = $this->prepareNextSortOrder($column);
		$column["order"] = $this->prepareSortOrder($column);
		$column["sort_url"] = $this->getSortUrl($column);
		$column["sort"] = $this->prepareSort($column["sort"]);
		$column["showname"] = $this->isShowHeaderName($column);
		$column["original_name"] = $column["name"];
		$column["name"] = $this->prepareHeaderName($column);
		$column["align"] = $this->prepareAlign($column);
		$column["is_shown"] = $this->isShownColumn($column);
		$column["class"] = $this->prepareHeaderClass($column);
		$column["width"] = $this->prepareColumnWidth($column);
		$column["editable"] = $this->prepareEditable($column);
		$column["prevent_default"] = $this->preparePreventDefault($column);
		$column["sticked"] = $this->prepareSticked($column);
		$column["sticked_default"] = json_encode($this->prepareSticked($column));
		return $column;
	}

	/**
	 * Prepares sticked value
	 * @param array $column
	 * @return bool
	 */
	protected function prepareSticked($column)
	{
		return is_bool($column["sticked"]) ? $column["sticked"] : false;
	}

	protected function preparePreventDefault(array $column)
	{
		$result = true;

		if (isset($column["prevent_default"]))
		{
			$result = (bool) $column["prevent_default"];
		}

		return $result;
	}

	protected function prepareColumnWidth(array $column)
	{
		$width = null;

		if (is_numeric($column["width"]))
		{
			$width = $column["width"];
		}
		else
		{
			$columns = $this->prepareColumnsResizeMeta();
			if (is_numeric($columns["columns"][$column["id"]]))
			{
				$width = $columns["columns"][$column["id"]];
			}

		}

		return $width;
	}


	/**
	 * Prepares additional class for column header
	 * @param array $headerItem
	 *
	 * @return string
	 */
	protected function prepareHeaderClass(array $headerItem)
	{
		$classList = array();

		if ($headerItem["class"])
		{
			$classList[] = $headerItem["class"];
		}

		if ($align = $this->prepareAlign($headerItem))
		{
			$classList[] = "main-grid-cell-".$align;
		}

		if ($this->prepareSort($headerItem["sort"]) && $this->arParams["ALLOW_SORT"])
		{
			$classList[] = "main-grid-col-sortable";
		}
		else
		{
			$classList[] = "main-grid-col-no-sortable";
		}

		return join(" ", $classList);
	}

	/**
	 * Prepares each header items
	 * @return array prepares header
	 */
	protected function prepareColumnsAll()
	{
		if (empty($this->arResult["COLUMNS_ALL"]))
		{
			$this->arResult["COLUMNS_ALL"] = array();

			foreach ($this->arParams["COLUMNS"] as $key => $item)
			{
				$this->arResult["COLUMNS_ALL"][$item["id"]] = $this->prepareColumn($item);
			}

			$allColumns = array_keys($this->arResult["COLUMNS_ALL"]);
			$allColumns = array_combine($allColumns, $allColumns);
			$showedColumns = $this->getShowedColumnsList();
			$resultColumns = array();
			$counter = 0;

			foreach ($allColumns as $allKey => $allColumn)
			{
				$key = $allKey;

				if (in_array($allKey, $showedColumns))
				{
					$key = $showedColumns[$counter];
					$counter++;
				}

				$resultColumns[$key] = $this->arResult["COLUMNS_ALL"][$key];
			}

			$this->arResult["COLUMNS_ALL"] = $resultColumns;
		}

		return $this->arResult["COLUMNS_ALL"];
	}


	/**
	 * Prepares visible headers
	 * @method prepareHeaders
	 * @return array
	 */
	protected function prepareColumns()
	{
		if (empty($this->arResult["COLUMNS"]))
		{
			$this->arResult["COLUMNS"] = array();

			$index = 0;
			foreach ($this->prepareColumnsAll() as $key => $item)
			{
				if ($item["is_shown"])
				{
					$this->arResult["COLUMNS"][$key] = $item;
					$this->arResult["COLUMNS"][$key]["sort_index"] = $index++;
				}
			}
		}

		return $this->arResult["COLUMNS"];
	}


	protected function prepareDefaultOptions()
	{
		$options = $this->getCurrentOptions();
		$gridOptions = $this->getGridOptions();
		$isNeedSave = false;

		if (!isset($options["columns_sizes"]) ||
			empty($options["columns_sizes"]) ||
			!is_array($options["columns_sizes"]))
		{
			$columns = $this->prepareColumns();
			$columnsSizes = array();
			$isNeedSave = true;

			foreach ($columns as $key => $item)
			{
				if (is_numeric($item["width"]))
				{
					$columnsSizes[$key] = $item["width"];
				}
			}

			$gridOptions->setColumnsSizes(null, $columnsSizes);
		}

		if ($gridOptions->getStickedColumns() === null)
		{
			$stickedColumns = [];

			foreach ($this->arResult["COLUMNS_ALL"] as $key => $column)
			{
				if ($column["sticked"] && $this->isShownColumn($column))
				{
					$stickedColumns[] = $key;
				}
			}

			$gridOptions->setStickedColumns($stickedColumns);
			$isNeedSave = true;
		}

		if ($isNeedSave)
		{
			$gridOptions->Save();
			$this->arResult["OPTIONS"] = $this->getOptions();
		}
	}

	protected function getJsFolder()
	{
		return $this->jsFolder;
	}

	protected function getBlocksFolder()
	{
		return $this->blocksFolder;
	}

	protected function getCssFolder()
	{
		return $this->cssFolder;
	}

	protected function includeScripts($folder)
	{
		$tmpl = $this->getTemplate();
		$absPath = $_SERVER["DOCUMENT_ROOT"].$tmpl->GetFolder().$folder;
		$relPath = $tmpl->GetFolder().$folder;

		if (is_dir($absPath))
		{
			$dir = opendir($absPath);

			if($dir)
			{
				while(($file = readdir($dir)) !== false)
				{
					$ext = getFileExtension($file);

					if ($ext === 'js' && !(mb_strpos($file, 'map.js') !== false || mb_strpos($file, 'min.js') !== false))
					{
						$tmpl->addExternalJs($relPath.$file);
					}
				}

				closedir($dir);
			}
		}
	}

	protected function includeComponentBlocks()
	{
		$blocksFolder = $this->getBlocksFolder();
		$this->includeScripts($blocksFolder);
	}

	protected function includeComponentScripts()
	{
		$scriptsFolder = $this->getJsFolder();
		$this->includeScripts($scriptsFolder);
	}

	protected static function sortMultilevelRows($rows, &$resultRows, $parent = 0, $depth = 0)
	{
		foreach ($rows as $key => $row)
		{
			if ($row["parent_id"] === $parent)
			{
				$row["depth"] = $depth;
				$resultRows[] = $row;
				unset($rows[$key]);
				self::sortMultilevelRows($rows, $resultRows, $row["id"], $depth + 1);
			}
		}
		return $resultRows;
	}

	protected static function normalizeParentIds(&$rows)
	{
		$ids = array();

		foreach ($rows as $key => $row)
		{
			$ids[] = $row["id"];
		}

		foreach ($rows as $key => $row)
		{
			if (!in_array($row["parent_id"], $ids))
			{
				$rows[$key]["parent_id"] = 0;
			}
		}
	}

	protected static function restoreExpandedRowsState(&$rows, $expandedIds)
	{
		if (is_array($expandedIds) && !empty($expandedIds))
		{
			foreach ($rows as $key => $row)
			{
				$rows[$key]["expand"] = !isset($row["custom"]) && in_array($row["id"], $expandedIds);
			}
		}
	}


	protected static function restoreCollapsedGroupsState(&$rows, $collapsedIds)
	{
		if (is_array($collapsedIds) && !empty($collapsedIds))
		{
			foreach ($rows as $key => $row)
			{
				if (isset($row["custom"]))
				{
					$rows[$key]["expand"] = !in_array($row["id"], $collapsedIds);
				}
			}
		}
		else
		{
			foreach ($rows as $key => $row)
			{
				if (isset($row["custom"]))
				{
					$rows[$key]["expand"] = true;
				}
			}
		}
	}

	protected function prepareMultilevelRows()
	{
		$result = array();
		$parent = 0;
		$depth = 0;
		$request = $this->request;

		if ($request["action"] === Grid\Actions::GRID_GET_CHILD_ROWS && $request["parent_id"] !== null)
		{
			$parent = $request["parent_id"];
			$depth = $request["depth"];
		}
		else
		{
			self::normalizeParentIds($this->arParams["ROWS"]);
		}

		self::sortMultilevelRows($this->arParams["ROWS"], $result, $parent, $depth);

		$expandedRows = $this->getGridOptions()->getExpandedRows();
		$collapsedGroups = $this->getGridOptions()->getCollapsedGroups();
		self::restoreExpandedRowsState($result, $expandedRows);
		self::restoreCollapsedGroupsState($result, $collapsedGroups);

		$this->arParams["ROWS"] = $result;
	}

	public function executeComponent()
	{
		if ($this->checkRequiredParams())
		{
			$templateRow = [
				'id' => 'template_0',
				'not_count' => true,
				'attrs' => [
					'hidden' => 'true',
				],
			];

			$this->arParams['ROWS'][] = $templateRow;

			$this->prepareParams();
			$this->prepareResult();
			$this->prepareDefaultOptions();
			$this->applyUserSettings();

			if ($this->arParams["ENABLE_COLLAPSIBLE_ROWS"])
			{
				$this->prepareMultilevelRows();
			}

			$templateName = $this->getTemplateName();
			if ( (empty($templateName) || $templateName === ".default") && $this->arParams['TILE_GRID_MODE'])
			{
				$this->setTemplateName('tilegrid');
			}

			$this->includeComponentTemplate();

			$templateName = $this->getTemplateName();
			if ($templateName !== '.default' && $templateName !== '')
			{
				$this->includeComponentScripts();
				$this->includeComponentBlocks();
			}
		}
	}
}
