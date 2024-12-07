<?php

use Bitrix\Main\Grid;
use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\Factory\ColumnFactory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Web;
use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

/**
 * Class CMainUIGrid
 */
class CMainUIGrid extends CBitrixComponent
{
	use DeprecatedMethods;

	/**
	 * @see `::getResultColumns`
	 *
	 * @var Column[]
	 */
	private array $resultColumns;
	/**
	 * @see `::getResultColumnsAll`
	 *
	 * @var Column[]
	 */
	private array $resultColumnsAll;

	/** @var ColumnFactory */
	protected ColumnFactory $columnFactory;

	/** @var bool */
	protected bool $needSortColumns = false;

	/** @var CGridOptions $gridOptions */
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

	protected $defaultHeaderSectionId = '';

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->columnFactory = new ColumnFactory();
	}

	protected function validateColumn($column = array())
	{
		return (is_array($column) && !empty($column["id"]) && is_string($column["id"]));
	}

	protected function validateColumns($columns = array())
	{
		$result = true;

		foreach ($columns as $column)
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
			foreach ($messages as $message)
			{
				ShowMessage($message);
			}

			$returns = false;
		}

		return $returns;
	}

	private function prepareTemplateRow(): void
	{
		$templateRow = [
			'id' => 'template_0',
			'not_count' => true,
			'attrs' => [
				'hidden' => 'true',
			],
		];

		foreach ($this->arParams['ROWS'] as $key => $row)
		{
			if (isset($row['id']) && $row['id'] === 'template_0')
			{
				$templateRow = array_merge($row, $templateRow);
				unset($this->arParams['ROWS'][$key]);
				break;
			}
		}

		array_unshift($this->arParams['ROWS'], $templateRow);
	}

	/**
	 * Prepares arParams
	 * @method prepareParams
	 * @return array
	 */
	protected function prepareParams()
	{
		$this->arParams["GRID_ID"] = Grid\Params::prepareString(
			[$this->arParams["GRID_ID"] ?? null],
			""
		);

		$this->arParams["SORT"] = Grid\Params::prepareArray(
			[$this->arParams["SORT"] ?? null],
			[]
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
			[$this->arParams["ROWS"] ?? null],
			[]
		);

		$this->arParams["TOTAL_ROWS_COUNT"] = Grid\Params::prepareInt(
			[
				$this->arParams["TOTAL_ROWS_COUNT"] ?? null,
				$this->arParams["FOOTER"]["TOTAL_ROWS_COUNT"] ?? null,
			],
			null
		);

		$this->arParams["AJAX_ID"] = Grid\Params::prepareString(
			[$this->arParams["AJAX_ID"] ?? null],
			""
		);

		$this->arParams["AGGREGATE_ROWS"] = Grid\Params::prepareArray(
			[$this->arParams["AGGREGATE_ROWS"] ?? null],
			[]
		);

		$this->arParams["NAV_PARAM_NAME"] = Grid\Params::prepareString(
			[$this->arParams["NAV_PARAM_NAME"] ?? null],
			null
		);

		$this->arParams["CURRENT_PAGE"] = Grid\Params::prepareInt(
			[$this->arParams["CURRENT_PAGE"] ?? null],
			null
		);

		$this->arParams["NAV_STRING"] = Grid\Params::prepareString(
			[
				$this->arParams["NAV_STRING"] ?? null,
				$this->arParams["FOOTER"]["NAV_STRING"] ?? null
			],
			""
		);

		$this->arParams["NAV_COMPONENT"] = Grid\Params::prepareString(
			[$this->arParams["NAV_COMPONENT"] ?? null],
			"bitrix:main.pagenavigation"
		);

		$this->arParams["NAV_COMPONENT_TEMPLATE"] = Grid\Params::prepareString(
			[$this->arParams["NAV_COMPONENT_TEMPLATE"] ?? null],
			"grid"
		);

		$this->arParams["ACTIONS_LIST"] = Grid\Params::prepareArray(
			[
				$this->arParams["ACTIONS_LIST"] ?? null,
				$this->arParams["ACTIONS"]["list"] ?? null
			],
			[]
		);

		$this->arParams["PAGE_SIZES"] = Grid\Params::prepareArray(
			[$this->arParams["PAGE_SIZES"] ?? null],
			[]
		);

		$this->arParams["DEFAULT_PAGE_SIZE"] = Grid\Params::prepareInt(
			[$this->arParams["DEFAULT_PAGE_SIZE"] ?? null],
			$this->defaultPageSize
		);

		$this->arParams["ALLOW_INLINE_EDIT"] = Grid\Params::prepareBoolean(
			[
				$this->arParams["ALLOW_INLINE_EDIT"] ?? null,
				$this->arParams["EDITABLE"] ?? null
			],
			true
		);

		$this->arParams["SHOW_ROW_ACTIONS_MENU"] = Grid\Params::prepareBoolean(
			[
				$this->arParams["SHOW_ROW_ACTIONS_MENU"] ?? null,
				$this->arParams["ROW_ACTIONS"] ?? null
			],
			true
		);

		$this->arParams["SHOW_ROW_CHECKBOXES"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_ROW_CHECKBOXES"] ?? null],
			true
		);

		$this->arParams["SHOW_NAVIGATION_PANEL"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_NAVIGATION_PANEL"] ?? null],
			true
		);

		$this->arParams["ALLOW_GROUP_ACTIONS"] = Grid\Params::prepareBoolean(
			[
				$this->arParams["ALLOW_GROUP_ACTIONS"] ?? null,
				$this->arParams["ACTION_ALL_ROWS"] ?? null
			],
			false
		);

		$this->arParams["SHOW_CHECK_ALL_CHECKBOXES"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_CHECK_ALL_CHECKBOXES"] ?? null],
			true
		);

		$this->arParams["ALLOW_CONTEXT_MENU"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_CONTEXT_MENU"] ?? null],
			false
		);

		$this->arParams["SHOW_GROUP_EDIT_BUTTON"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_GROUP_EDIT_BUTTON"] ?? null],
			false
		);

		$this->arParams["SHOW_GROUP_DELETE_BUTTON"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_GROUP_DELETE_BUTTON"] ?? null],
			false
		);

		$this->arParams["SHOW_SELECT_ALL_RECORDS_CHECKBOX"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_SELECT_ALL_RECORDS_CHECKBOX"] ?? null],
			false
		);

		$this->arParams["SHOW_GRID_SETTINGS_MENU"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_GRID_SETTINGS_MENU"] ?? null],
			true
		);

		$this->arParams["SHOW_MORE_BUTTON"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_MORE_BUTTON"] ?? null],
			false
		);

		$this->arParams["SHOW_PAGINATION"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_PAGINATION"] ?? null],
			true
		);

		$this->arParams["SHOW_PAGESIZE"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_PAGESIZE"] ?? null],
			false
		);

		$this->arParams["SHOW_SELECTED_COUNTER"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_SELECTED_COUNTER"] ?? null],
			true
		);

		$this->arParams["SHOW_TOTAL_COUNTER"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_TOTAL_COUNTER"] ?? null],
			true
		);

		$this->arParams["ALLOW_COLUMNS_SORT"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_COLUMNS_SORT"] ?? null],
			true
		);

		$this->arParams["ALLOW_ROWS_SORT"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_ROWS_SORT"] ?? null],
			false
		);

		$this->arParams["ALLOW_ROWS_SORT_IN_EDIT_MODE"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_ROWS_SORT_IN_EDIT_MODE"] ?? null],
			false
		);

		$this->arParams["ALLOW_ROWS_SORT_INSTANT_SAVE"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_ROWS_SORT_INSTANT_SAVE"] ?? null],
			true
		);

		$this->arParams["ALLOW_HORIZONTAL_SCROLL"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_HORIZONTAL_SCROLL"] ?? null],
			true
		);

		$this->arParams["ALLOW_SORT"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_SORT"] ?? null],
			true
		);

		$this->arParams["ALLOW_COLUMNS_RESIZE"] = Grid\Params::prepareBoolean(
			[
				$this->arParams["ALLOW_COLUMNS_RESIZE"] ?? null,
				$this->arParams["ALLOW_COLUMN_RESIZE"] ?? null
			],
			true
		);

		$this->arParams["ALLOW_PIN_HEADER"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_PIN_HEADER"] ?? null],
			false
		);

		$this->arParams["ALLOW_STICKED_COLUMNS"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_STICKED_COLUMNS"] ?? null],
			false
		);

		$this->arParams["DISABLE_HEADERS_TRANSFORM"] = Grid\Params::prepareBoolean(
			[$this->arParams["DISABLE_HEADERS_TRANSFORM"] ?? null],
			false
		);

		$this->arParams["SHOW_ACTION_PANEL"] = Grid\Params::prepareBoolean(
			[$this->arParams["SHOW_ACTION_PANEL"] ?? null],
			true
		);

		$this->arParams["ENABLE_COLLAPSIBLE_ROWS"] = Grid\Params::prepareBoolean(
			[$this->arParams["ENABLE_COLLAPSIBLE_ROWS"] ?? null],
			false
		);

		$this->arParams["TILE_GRID_MODE"] = Grid\Params::prepareBoolean(
			[$this->arParams["TILE_GRID_MODE"] ?? null],
			false
		);

		$this->arParams["JS_CLASS_TILE_GRID_ITEM"] = Grid\Params::prepareString(
			[$this->arParams["JS_CLASS_TILE_GRID_ITEM"] ?? null],
			null
		);

		$this->arParams["TILE_SIZE"] = Grid\Params::prepareString(
			[$this->arParams["TILE_SIZE"] ?? null],
			null
		);

		if (
			isset($this->arParams["ROW_LAYOUT"])
			&& is_array($this->arParams["ROW_LAYOUT"])
			&& !empty($this->arParams["ROW_LAYOUT"])
		)
		{
			$this->arParams["ALLOW_COLUMNS_SORT"] = false;
			$this->arParams["ALLOW_SORT"] = false;
		}

		$this->arParams["ACTION_PANEL"] = Grid\Params::prepareArray(
			[$this->arParams["ACTION_PANEL"] ?? null],
			[]
		);

		$this->arParams["TOP_ACTION_PANEL_CLASS"] = Grid\Params::prepareString(
			[$this->arParams["TOP_ACTION_PANEL_CLASS"] ?? ''],
			[]
		);

		$this->arParams["ACTION_PANEL_OPTIONS"] = Grid\Params::prepareArray(
			[$this->arParams["ACTION_PANEL_OPTIONS"] ?? null],
			[]
		);

		$this->arParams["TOP_ACTION_PANEL_PINNED_MODE"] = Grid\Params::prepareBoolean(
			[$this->arParams["TOP_ACTION_PANEL_PINNED_MODE"] ?? null],
			false
		);

		$this->arParams["ADVANCED_EDIT_MODE"] = Grid\Params::prepareBoolean(
			[$this->arParams["ADVANCED_EDIT_MODE"] ?? null],
			false
		);

		$this->arParams["ALLOW_EDIT_SELECTION"] = Grid\Params::prepareBoolean(
			[$this->arParams["ALLOW_EDIT_SELECTION"] ?? null],
			false
		);

		$this->arParams["SETTINGS_WINDOW_TITLE"] = Grid\Params::prepareString(
			[$this->arParams["SETTINGS_WINDOW_TITLE"] ?? null],
			""
		);

		$this->arParams["HIDE_TOP_BORDER_RADIUS"] = Grid\Params::prepareBoolean(
			[$this->arParams["HIDE_TOP_BORDER_RADIUS"] ?? null],
			false
		);

		$this->arParams["HIDE_BOTTOM_BORDER_RADIUS"] = Grid\Params::prepareBoolean(
			[$this->arParams["HIDE_BOTTOM_BORDER_RADIUS"] ?? null],
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
		$this->arResult["GRID_ID"] = $this->arParams["GRID_ID"] ?? '';
		$this->arResult["FORM_ID"] = $this->arParams["FORM_ID"] ?? '';

		// for correct calls of overridden deprecated methods.
		$isChildComponent = get_called_class() !== CMainUIGrid::class;
		if ($isChildComponent)
		{
			$this->syncResultColumns();
		}

		$this->arResult["ENABLE_FIELDS_SEARCH"] = (
			isset($this->arParams["ENABLE_FIELDS_SEARCH"])
			&& $this->arParams["ENABLE_FIELDS_SEARCH"] === 'Y'
		);

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
		$this->arResult["ALLOW_CONTEXT_MENU"] = $this->arParams["ALLOW_CONTEXT_MENU"];
		$this->arResult["DEFAULT_COLUMNS"] = $this->prepareDefaultColumns();
		$this->arResult["DEPTH"] = $this->prepareDepth();
		$this->arResult["MESSAGES"] = $this->prepareMessages($this->arParams["MESSAGES"] ?? []);
		$this->arResult["LAZY_LOAD"] = $this->arParams["LAZY_LOAD"] ?? false;
		$this->arResult["HAS_STICKED_COLUMNS"] = !empty($this->getGridOptions()->getStickedColumns());
		$this->arResult["HANDLE_RESPONSE_ERRORS"] = (
			isset($this->arParams["HANDLE_RESPONSE_ERRORS"])
			&& $this->arParams["HANDLE_RESPONSE_ERRORS"] === true
		);

		$useCheckboxListForSettingsPopup = (bool)($this->arParams['USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP'] ?? false);
		$this->arResult['USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP'] = $useCheckboxListForSettingsPopup;

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
			foreach ($messages as $message)
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
			if (!empty($commonOptions["views"]["default"]["columns"]))
			{
				$commonColumns = explode(",", $commonOptions["views"]["default"]["columns"]);
			}
		}

		foreach ($this->getResultColumnsAll() as $columnId => $column)
		{
			$columns[$columnId] = $this->convertColumnToArray($column);
			$columns[$columnId]['sort_url'] = $column->getSortUrl();
			$columns[$columnId]['sort_by'] = $column->getSort();
			$columns[$columnId]['sort_order'] = $column->getNextSortOrder();

			if (is_array($commonColumns))
			{
				$columns[$columnId]["default"] = in_array($columnId, $commonColumns);
			}
		}

		return $columns;
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
			$this->arResult["ACTIONS_LIST_JSON"] = $this->arParams["ACTIONS_LIST"] ?? null;
			$this->arResult["ACTIONS_LIST_JSON"] = Web\Json::encode($this->arResult["ACTIONS_LIST_JSON"]);
			$this->arResult["ACTIONS_LIST_JSON"] = Text\Converter::getHtmlConverter()->encode($this->arResult["ACTIONS_LIST_JSON"]);
		}

		return $this->arResult["ACTIONS_LIST_JSON"];
	}


	protected function prepareActionsListCurrentJson()
	{
		if (empty($this->arResult["ACTIONS_LIST_CURRENT_JSON"]))
		{
			$this->arResult["ACTIONS_LIST_CURRENT_JSON"] = $this->arParams["ACTIONS_LIST"][0] ?? null;
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
		if (!isset($this->arResult["AGGREGATE"]) || empty($this->arResult["AGGREGATE"]))
		{
			$this->arResult["AGGREGATE"] = $this->arParams["AGGREGATE"] ?? [];
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

			foreach ($this->getResultColumnsAll() as $column)
			{
				if (!$column->isSticked())
				{
					$column->setSticked(
						in_array($column->getId(), $stickedColumns)
					);
				}

				if ($column->isShown())
				{
					if (!$this->arResult["HAS_STICKED_COLUMNS"] && $column->isSticked())
					{
						$this->arResult["HAS_STICKED_COLUMNS"] = true;
					}
				}
			}

			$this->needSortColumns = $this->arResult["HAS_STICKED_COLUMNS"];
		}
	}

	/**
	 * Applies columns sort setting
	 */
	protected function applyColumnsSort()
	{
		if (!empty($this->getShowedColumnsList()))
		{
			$this->resultColumns = [];
			foreach ($this->getShowedColumnsList() as $i => $columnId)
			{
				$column = $this->getResultColumn($columnId);
				if ($column)
				{
					$column->setSortIndex($i);

					$this->resultColumns[$columnId] = $column;
				}
			}

			$this->needSortColumns = true;
		}
	}


	/**
	 * Applies rows sort setting
	 */
	protected function applyRowsSort()
	{
		$options = $this->getCurrentOptions();

		$this->getUri()->addParams(array(
			"by" => $options["last_sort_by"] ?? '',
			"order" => $options["last_sort_order"] ?? ''
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
		if (is_array($this->getCustomNames()) && !empty($this->getCustomNames()))
		{
			foreach ($this->getCustomNames() as $key => $value)
			{
				$column = $this->getResultColumn($key);
				$column?->setName(
					(string)Bitrix\Main\Text\Converter::getHtmlConverter()->decode($value)
				);
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
		if (!empty($this->getShowedColumnsList()))
		{
			$this->resultColumns = [];

			foreach ($this->getShowedColumnsList() as $i => $columnId)
			{
				$column = $this->getResultColumn($columnId);
				if (isset($column))
				{
					$column->setShown(true);
					$column->setSortIndex($i);

					$this->resultColumns[$columnId] = $column;
				}
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

		foreach ($this->getResultColumns() as $columnId => $column)
		{
			$colSize = $colSizes[$columnId] ?? null;
			if (isset($colSize) && $column->isResizeable())
			{
				$column->setWidth($colSize);
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
		$uri = clone $this->getUri();
		$uri->addParams(array(
			$this->arParams["NAV_PARAM_NAME"] => $this->prepareNextPage()
		));

		$this->arResult["NEXT_PAGE_URL"] = $uri->getUri();

		return $this->arResult["NEXT_PAGE_URL"];
	}


	/**
	 * Prepares next page number
	 * @method prepareNextPage
	 * @return int Page number
	 */
	protected function prepareNextPage()
	{
		if (!isset($this->arResult["NEXT_PAGE"]) || !$this->arResult["NEXT_PAGE"])
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

		if (
			isset($this->arParams["ENABLE_NEXT_PAGE"]) &&
			$this->arParams["ENABLE_NEXT_PAGE"] &&
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
		if (!isset($this->arResult["SHOW_BOTTOM_PANEL"]) || !is_bool($this->arResult["SHOW_BOTTOM_PANEL"]))
		{
			$this->arResult["SHOW_BOTTOM_PANEL"] = $this->showBottomPanel;

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
		if (!isset($this->arResult["TOTAL_ROWS_COUNT"]) || !is_numeric($this->arResult["TOTAL_ROWS_COUNT"]))
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

		if (!isset($this->arResult["NAV_STRING"]) || !is_string($this->arResult["NAV_STRING"]))
		{
			$this->arResult["NAV_STRING"] = $this->navString;

			if ($this->arParams["NAV_STRING"] <> '')
			{
				$this->arResult["NAV_STRING"] = $this->arParams["NAV_STRING"];
			}
			elseif (isset($this->arParams["NAV_OBJECT"]) && is_object($this->arParams["NAV_OBJECT"]))
			{
				if(!isset($this->arParams["~NAV_PARAMS"]) || !is_array($this->arParams["~NAV_PARAMS"]))
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
						$this->arParams['NAV_COMPONENT'],
						$this->arParams['NAV_COMPONENT_TEMPLATE'],
						$params,
						false,
						array(
							'HIDE_ICONS' => 'Y',
						)
					);

					$this->arResult["NAV_STRING"] = ob_get_clean();
				}
				else
				{
					/** @var CDBResult $nav */
					$nav->nPageWindow = 5;
					$this->arResult["NAV_STRING"] = $nav->GetPageNavStringEx($dummy, "", $this->arParams['NAV_COMPONENT_TEMPLATE'], true, null, $this->arParams["~NAV_PARAMS"]);
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
		if (!isset($this->arResult["DATA_FOR_EDIT"]) || !is_array($this->arResult["DATA_FOR_EDIT"]))
		{
			$this->arResult["DATA_FOR_EDIT"] = $this->dataForEdit;

			foreach ($this->prepareRows() as $rowItem)
			{
				if (isset($rowItem["editable"]) && $rowItem["editable"] === false)
				{
					continue;
				}

				$rowId = $rowItem["id"];

				foreach ($this->getResultColumnsAll() as $column)
				{
					if ($column->isEditable())
					{
						$columnId = $column->getId();
						if (isset($rowItem['editable'][$columnId]) && $rowItem['editable'][$columnId] === false)
						{
							$this->arResult['DATA_FOR_EDIT'][$rowId][$columnId] = false;
						}
						else
						{
							$this->arResult['DATA_FOR_EDIT'][$rowId][$columnId] =
								$rowItem['data']['~' . $columnId]
								?? $rowItem['data'][$columnId]
								?? false
							;
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
		if (!isset($this->arResult["ALLOW_INLINE_EDIT_ALL"]) || !is_bool($this->arResult["ALLOW_INLINE_EDIT_ALL"]))
		{
			$this->arResult["ALLOW_INLINE_EDIT_ALL"] = $this->allowInlineEditAll;

			foreach ($this->getResultColumnsAll() as $column)
			{
				if ($column->isEditable())
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
		if (!isset($this->arResult["ALLOW_INLINE_EDIT"]) || !is_bool($this->arResult["ALLOW_INLINE_EDIT"]))
		{
			$this->arResult["ALLOW_INLINE_EDIT"] = $this->allowInlineEdit;

			foreach ($this->prepareRows() as $item)
			{
				if (
					$item['id'] !== 'template_0'
					&& $this->arResult["ALLOW_INLINE_EDIT"] === $this->allowInlineEdit
				)
				{
					$this->arResult["ALLOW_INLINE_EDIT"] = !isset($item["editable"]) || $item["editable"] !== false;
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
		if (!isset($this->arResult["ALLOW_EDIT"]) || !is_bool($this->arResult["ALLOW_EDIT"]))
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
		if (!isset($this->arResult["ALLOW_EDIT_ALL"]) || !is_bool($this->arResult["ALLOW_EDIT_ALL"]))
		{
			if (isset($this->arParams["EDITABLE"]) && $this->arParams["EDITABLE"] && $this->prepareHasActions())
			{
				$this->arResult["ALLOW_EDIT_ALL"] = true;
			}
		}

		return $this->arResult["ALLOW_EDIT_ALL"] ?? false;
	}


	/**
	 * Prepares $this->arResult["EDIT_DATE"]) value
	 * @method prepareEditDate
	 * @return boolean
	 */
	protected function prepareEditDate()
	{
		if (!isset($this->arResult["EDIT_DATE"]) || !is_bool($this->arResult["EDIT_DATE"]))
		{
			$this->arResult["EDIT_DATE"] = $this->editDate;

			foreach ($this->getResultColumnsAll() as $column)
			{
				if (
					isset($this->arParams["EDITABLE"])
					&& $this->arParams["EDITABLE"]
					&& $column->isEditable()
					&& $column->getType() === "date"
				)
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
		if (!isset($this->arResult["HAS_ACTIONS"]) || !is_bool($this->arResult["HAS_ACTIONS"]))
		{
			$this->arResult["HAS_ACTIONS"] = (
				isset($this->arParams["ACTIONS"]) &&
				is_array($this->arParams["ACTIONS"]) &&
				!empty($this->arParams["ACTIONS"])
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

			if (!empty($action["MENU"]) && is_array($action["MENU"]))
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
				if (isset($action["default"]) && $action["default"] === true)
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

					$row["default_action"]["title"] = $action["text"] ?? "";
				}
			}
		}

		return $actions;
	}


	protected function compatibleRow($row, $rowIndex)
	{
		if (!empty($row["actions"]) && is_array($row["actions"]))
		{
			$row["actions"] = $this->compatibleActions($row["actions"], $row);
		}

		if(!isset($row["id"]))
		{
			$row["id"] = $row["data"]["ID"] ?? $rowIndex;
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
			$actualRow = $this->compatibleRow($row, $key);
			if(isset($actualRow["columns"]) || isset($actualRow["data"]))
			{
				foreach($this->getResultColumnsAll() as $columnId => $column)
				{
					if(!isset($actualRow["columns"][$columnId]) && isset($actualRow["data"][$columnId]))
					{
						$actualRow["columns"][$columnId] = $actualRow["data"][$columnId];
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
		if (!isset($this->arParams['TILE_GRID_MODE']) || !$this->arParams['TILE_GRID_MODE'])
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
				'name' => $row['NAME'] ?? null,
				'image' => $row['IMAGE'] ?? null,
			];
		}

		return $items;
	}

	protected function prepareColumnsEditMeta()
	{
		if (!isset($this->arResult["COLS_EDIT_META"]) || !is_array($this->arResult["COLS_EDIT_META"]))
		{
			$this->arResult["COLS_EDIT_META"] = $this->columnsEditMeta;
			$columns = $this->prepareColumnsEditMetaAll();

			foreach ($this->getResultColumns() as $columnId => $column)
			{
				$this->arResult["COLS_EDIT_META"][$columnId] = $columns[$columnId];
			}
		}

		return $this->arResult["COLS_EDIT_META"] ?? [];
	}


	/**
	 * Prepares $this->arResult["COLS_EDIT_META_ALL"] value
	 * @method prepareColumnsEditMetaAll
	 * @return array
	 */
	protected function prepareColumnsEditMetaAll()
	{
		if (!isset($this->arResult["COLS_EDIT_META_ALL"]) || !is_array($this->arResult["COLS_EDIT_META_ALL"]))
		{
			$this->arResult["COLS_EDIT_META_ALL"] = $this->columnsEditMetaAll;

			foreach ($this->getResultColumnsAll() as $columnId => $column)
			{
				$this->arResult["COLS_EDIT_META_ALL"][$columnId] = array(
					"editable" => $column->isEditable(),
					"type" => $column->getType() ?? Grid\Types::GRID_TEXT
				);

				$editableConfig = $column->getEditable();
				if (
					isset($this->arParams["EDITABLE"])
					&& $this->arParams["EDITABLE"]
					&& isset($editableConfig)
				)
				{
					foreach ($editableConfig->toArray() as $attrKey => $attrValue)
					{
						$this->arResult["COLS_EDIT_META_ALL"][$columnId][$attrKey] = $attrValue;
					}
				}
			}
		}

		return $this->arResult["COLS_EDIT_META_ALL"] ?? [];
	}


	/**
	 * Prepares $this->arResult["COLS_RESIZE_META"] value
	 * @method prepareColumnsResizeMeta
	 * @return array
	 */
	protected function prepareColumnsResizeMeta()
	{
		if (!isset($this->arResult["COLS_RESIZE_META"]) || empty($this->arResult["COLS_RESIZE_META"]))
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
		if (!isset($this->arResult["COLS_NAMES"]) || empty($this->arResult["COLS_NAMES"]))
		{
			$this->arResult["COLS_NAMES"] = array();

			foreach ($this->getResultColumnsAll() as $columnId => $column)
			{
				$this->arResult["COLS_NAMES"][$columnId] = $column->getName();
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
			$this->arParams["SORT_VARS"]["by"] ?? null,
			$this->arParams["SORT_VARS"]["order"] ?? null,
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
	 *
	 * @param Column $column
	 *
	 * @return string|null
	 */
	protected function getSortUrlByColumn(Column $column): ?string
	{
		if ($column->getSort() === null)
		{
			return null;
		}

		$uri = clone $this->getUri();
		$uri->addParams([
			'by' => $column->getSort(),
			'order' => $column->getNextSortOrder(),
		]);

		return $uri->getUri();
	}

	protected function columnExists($id)
	{
		return $this->getResultColumn($id) !== null;
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
			$tmp = explode(",", $options["columns"] ?? '');

			$this->showedColumnsList = array();

			foreach ($tmp as $item)
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

		return $options["custom_names"] ?? [];
	}

	/**
	 * Prepares sort state value
	 * @method prepareSortState
	 * @param  array $headerItem header item
	 * @return string ["desc", "asc"]
	 */

	/**
	 * Prepares sort state value
	 *
	 * @param Column $column
	 *
	 * @return string|null returns 'desc' and 'asc' value, or `null` if column does not have the current sorting state.
	 */
	protected function prepareSortStateByColumn(Column $column): ?string
	{
		$state = null;

		$sort = $column->getSort();
		if ($sort === null)
		{
			return null;
		}

		if (isset($this->arParams['SORT'][$sort]))
		{
			$state = $this->arParams['SORT'][$sort];
		}
		else
		{
			$options = $this->getCurrentOptions();

			if (
				isset($options['last_sort_by'], $options['last_sort_order'])
				&& (string)$options['last_sort_by'] === $sort
			)
			{
				$state = $options['last_sort_order'];
			}
		}

		return $state;
	}

	protected function convertColumnToArray(Column $column): array
	{
		$state = $this->prepareSortStateByColumn($column);
		if (isset($state))
		{
			$column->setSortState($state);
		}

		$sortUrl = $this->getSortUrlByColumn($column);
		if (isset($sortUrl))
		{
			$column->setSortUrl($sortUrl);
		}

		$editable = $column->getEditable();

		$result = [
			'id' => $column->getId(),
			'type' => $column->getType(),
			'name' => $column->getName(),
			'original_name' => $column->getName(),
			'title' => $column->getTitle(),
			'default' => $column->isDefault(),
			'is_shown' => $column->isShown(),
			'sort' => $column->getSort(),
			'editable' => isset($editable) ? $editable->toArray() : false,
			'sort_state' => $column->getSortState(),
			'sort_index' => $column->getSortIndex(),
			'next_sort_order' => $column->getNextSortOrder(),
			'first_order' => $column->getFirstOrder(),
			'order' => $column->getSortOrder(),
			'sort_url' => $column->getSortUrl(),
			'showname' => $column->isShowname(),
			'shift' => $column->isShift(),
			'align' => $column->getAlign(),
			'class' => $this->prepareHeaderClassByColumn($column),
			'width' => $this->prepareColumnWidthByColumn($column),
			'section_id' => $column->getSection() ?? $this->defaultHeaderSectionId,
			'resizeable' => $column->isResizeable(),
			'prevent_default' => $column->isPreventDefault(),
			'sticked' => $column->isSticked(),
			'sticked_default' => json_encode($column->isSticked()),
			'layout' => $this->prepareColumnHeaderLayoutByColumn($column),
		];

		$icon = $column->getIcon();
		if (isset($icon))
		{
			$result['iconUrl'] = $icon->getUrl();
			$result['iconTitle'] = $icon->getTitle();
		}

		$hint = $column->getHint();
		if (isset($hint))
		{
			$result['hint'] = $hint->getText();
			$result['hintHtml'] = $hint->isHtml();
			$result['hintInteractivity'] = $hint->isInteractivity();
		}

		$result['class'] .= ' ' . $result['layout']['cell']['class'];
		$result['layout']['cell']['class'] = $result['class'];

		return $result;
	}

	private function prepareColumnHeaderLayoutByColumn(Column $column): array
	{
		$layout = clone $column->getLayout();

		return [
			'cell' => [
				'class' => (string)$layout->getCellAttributes()->remove('class'),
				'attributes' => (string)$layout->getCellAttributes(),
			],
			'container' => [
				'attributes' => (string)$layout->getContainerAttributes(),
			],
		];
	}

	protected function prepareRowClass($row, $options = []): string
	{
		$rowClass = "";
		if (isset($row["not_count"]) && $row["not_count"])
		{
			$rowClass .= " main-grid-not-count";
		}

		if (isset($row["expand"]) && $row["expand"])
		{
			$rowClass .= " main-grid-row-expand";
		}

		if (isset($row["draggable"]) && $row["draggable"] === false)
		{
			$rowClass .= " main-grid-row-drag-disabled";
		}

		if (
			isset($this->arParams["ENABLE_COLLAPSIBLE_ROWS"])
			&& $this->arParams["ENABLE_COLLAPSIBLE_ROWS"]
			&& isset($row["parent_group_id"])
			&& $row["parent_group_id"] === $options['lastCollapsedGroupId']
		)
		{
			$rowClass .= " main-grid-hide";
		}

		return $rowClass;
	}

	protected function prepareRowLayout($row, $options = []): array
	{
		$attributes = [
			"data-child-loaded" => (bool)($row["expand"] ?? null),
			"data-depth" => (int)($row["depth"] ?? null),
			"data-id" => $row["id"],
		];

		if (isset($this->arParams["ENABLE_COLLAPSIBLE_ROWS"]))
		{
			$attributes["data-parent-id"] = $row["parent_id"] ?? null;
		}

		if (!empty($row["default_action"]))
		{
			$attributes["data-default-action"] = $row["default_action"]["js"] ?? '';
			$attributes["title"] = '';
			if (isset($row["default_action"]["title"]))
			{
				$attributes["title"] = Loc::getMessage("interface_grid_dblclick") . $row["default_action"]["title"];
			}
		}

		if (isset($row["attrs"]) && is_array($row["attrs"]))
		{
			$attributes = array_merge($row["attrs"], $attributes);
		}

		return [
			"row" => [
				"class" => $this->prepareRowClass($row, $options),
				"attributes" => static::stringifyAttrs($attributes),
			],
			"columns" => $this->prepareRowColumns($row, $options),
		];
	}

	protected function prepareRowColumns($row, $options): array
	{
		$checkboxInputAttributes = [
			"id" => "checkbox_" . $this->arParams["GRID_ID"] . "_" . $row["id"],
			"name" => "ID[]",
			"value" => $row["id"],
		];

		if (!isset($row["editable"]) || $row["editable"] !== false)
		{
			$checkboxInputAttributes["title"] = Loc::getMessage("interface_grid_check");
		}

		if (!$this->arResult["ALLOW_EDIT"] || (isset($row["editable"]) && $row["editable"] === false))
		{
			$checkboxInputAttributes["data-disabled"] = 1;
			$checkboxInputAttributes["disabled"] = "";
		}

		$columns = [
			"drag" => [
				"cell" => [
					"enabled" => (
						$this->arParams["ALLOW_ROWS_SORT"]
						&& (!isset($row["draggable"]) || $row["draggable"] !== false)
					),
				],
			],
			"checkbox" => [
				"cell" => [
					"enabled" => $this->arParams["SHOW_ROW_CHECKBOXES"],
				],
				"input" => [
					"attributes" => static::stringifyAttrs($checkboxInputAttributes),
				],
			],
			"actions" => [
				"cell" => [
					"enabled" => (
						$this->arParams["SHOW_ROW_ACTIONS_MENU"]
						|| $this->arParams["SHOW_GRID_SETTINGS_MENU"]
					),
				],
				"button" => [
					"enabled" => (
						!empty($row["actions"])
						&& $this->arParams["SHOW_ROW_ACTIONS_MENU"]
					),
					"attributes" => static::stringifyAttrs([
						"data-actions" => $row["actions"] ?? [],
					]),
				],
			],
		];

		foreach ($this->getResultColumns() as $columnId => $column)
		{
			$cellClass = "main-grid-cell";

			$align = $column->getAlign();
			$cellClass .= " main-grid-cell-{$align}";

			if (
				isset($row["columnClasses"])
				&& is_array($row["columnClasses"])
				&& array_key_exists($columnId, $row["columnClasses"])
			)
			{
				$cellClass .= " {$row["columnClasses"][$columnId]}";
			}

			$cellAttributes = [];
			$cellStyle = "";
			if (
				$this->arParams["ENABLE_COLLAPSIBLE_ROWS"]
				&& $column->isShift()
			)
			{
				$cellAttributes["data-shift"] = true;
				if (isset($row["depth"]) && $row["depth"] > 0)
				{
					$offsetLeft = 20;
					$paddingLeft = $row["depth"] * $offsetLeft;
					$cellStyle .= "padding-left: {$paddingLeft}px;";
				}
			}

			$color = $column->getCssColorValue();
			if (isset($color))
			{
				$cellStyle .= "background-color: {$color}";
			}
			elseif ($column->getCssColorClassName() !== null)
			{
				$cellClass .= " " . $column->getCssColorClassName();
			}

			$cellAttributes["data-editable"] = (
				!isset($row["editableColumns"][$columnId])
				|| $row["editableColumns"][$columnId] === true
			);

			$cellAttributes["style"] = $cellStyle;

			$containerAttributes = [];
			$containerAttributes["data-prevent-default"] = $column->isPreventDefault() ? "true" : "false";

			$isPlusButtonEnabled = (
				$this->arParams["ENABLE_COLLAPSIBLE_ROWS"]
				&& isset($row["has_child"]) && $row["has_child"] === true
				&& $column->isShift()
			);

			$isCellActionsEnabled =
				isset($options["hasCellActions"][$columnId]) && $options["hasCellActions"][$columnId] === true
			;

			$cellActions = [];
			if (!empty($row['cellActions'][$columnId]) && is_array($row['cellActions'][$columnId]))
			{
				$isCellActionsEnabled = true;
				foreach ($row['cellActions'][$columnId] as $action)
				{
					$buttonClass = $action['class'];
					if (is_array($buttonClass))
					{
						$buttonClass = implode(' ', $buttonClass);
					}


					$actionAttributes = [];
					if (isset($action['events']) && is_array($action['events']))
					{
						$actionAttributes['data-events'] = $action['events'];
					}

					if (isset($action['attributes']) && is_array($action['attributes']))
					{
						$actionAttributes = array_merge(
							$actionAttributes,
							$action['attributes']
						);
					}

					$cellActions[] = array_merge(
						$action,
						[
							'class' => ' ' . $buttonClass,
							'attributes' => static::stringifyAttrs($actionAttributes),
						]
					);
				}
			}

			$counter = [
				"enabled" => false,
				"inner" => [
					"enabled" => false,
				],
				"counter" => [
					"class" => " " . Grid\Counter\Color::DANGER,
					"secondaryClass" => " " . Grid\Counter\Color::DANGER,
					"attributes" => "",
				],
                "isDouble" => false,
			];
			if (!empty($row["counters"][$columnId]) && is_array($row["counters"][$columnId]))
			{
				$counter["enabled"] = true;

				$counterOptions = $row["counters"][$columnId];
				if (isset($counterOptions["type"]) && is_string($counterOptions["type"]))
				{
					if ($counterOptions["type"] === Grid\Counter\Type::LEFT)
					{
						$counter["class"] = " main-grid-cell-counter-left";
						$counter["align"] = "left";
					}

					if ($counterOptions["type"] === Grid\Counter\Type::LEFT_ALIGNED)
					{
						$counter["class"] = " main-grid-cell-counter-left-aligned";
						$counter["align"] = "left";
					}

					if ($counterOptions["type"] === Grid\Counter\Type::RIGHT)
					{
						$counter["class"] = " main-grid-cell-counter-right";
						$counter["align"] = "right";
					}
				}

				if (
					!empty($counterOptions["value"])
					&& (
						is_string($counterOptions["value"])
						|| is_numeric($counterOptions["value"])
					)
				)
				{
					$counter["inner"]["enabled"] = true;
				}

				if (
					!empty($counterOptions["color"])
					&& is_string($counterOptions["color"])
				)
				{
					$counter["counter"]["class"] = " " . $counterOptions["color"];
				}

				if (
					!empty($counterOptions["size"])
					&& is_string($counterOptions["size"])
				)
				{
					$counter["counter"]["class"] .= " " . $counterOptions["size"];
				}

				if (isset($counterOptions["events"]) && is_array($counterOptions["events"]))
				{
					$counter["counter"]["attributes"] = static::stringifyAttrs([
						"data-events" => $counterOptions["events"],
					]);
				}

				if (isset($counterOptions["class"]) && is_string($counterOptions["class"]))
				{
					$counter["counter"]["class"] .= " " . $counterOptions["class"];
				}

				if (
					!empty($counterOptions["secondaryColor"])
					&& is_string($counterOptions["secondaryColor"])
				)
				{
					$counter["counter"]["secondaryClass"] = " " . $counterOptions["secondaryColor"];
				}

                if (isset($counterOptions["isDouble"]) && is_bool($counterOptions["isDouble"]))
                {
                    $counter["counter"]["isDouble"] = $counterOptions["isDouble"];
                }
			}
			else if ($column->getLayout()->isHasLeftAlignedCounter())
			{
				$counter["enabled"] = true;
				$counter["class"] = " main-grid-cell-counter-left-aligned";
				$counter["align"] = "left";
			}

			$content = [];
			if (
				$column->getType() === Grid\Column\Type::LABELS
				&& isset($row["columns"][$columnId])
				&& is_array($row["columns"][$columnId])
			)
			{
				foreach ($row["columns"][$columnId] as $labelKey => $label)
				{
					$labelClass = "";
					if (isset($label["color"]) && is_string($label["color"]))
					{
						$labelClass .= " " . $label["color"];
					}

					if (isset($label["size"]) && is_string($label["size"]))
					{
						$labelClass .= " " . $label["size"];
					}

					if (!isset($label["light"]) || $label["light"] !== true)
					{
						$labelClass .= " ui-label-fill";
					}

					$labelEvents = [];
					if (isset($label["events"]) && is_array($label["events"]))
					{
						$labelEvents = $label["events"];

						if (isset($label["events"]["click"]))
						{
							$labelClass .= " ui-label-link";
						}
					}

					$removeButton = [
						"enabled" => false,
					];
					if (
						isset($label["removeButton"])
						&& is_array($label["removeButton"])
					)
					{
						$removeButton["enabled"] = true;
						if (
							isset($label["removeButton"]["type"])
							&& is_string($label["removeButton"]["type"])
						)
						{
							$removeButton["class"] = " " . $label["removeButton"]["type"];
							$removeButton["type"] = $label["removeButton"]["type"];
						}
						else
						{
							$removeButton["class"] = " " . Grid\Cell\Label\RemoveButtonType::INSIDE;
							$removeButton["type"] = Grid\Cell\Label\RemoveButtonType::INSIDE;
						}

						if (
							isset($label["removeButton"]["events"])
							&& is_array($label["removeButton"]["events"])
						)
						{
							$removeButton["attributes"] = static::stringifyAttrs([
								"data-events" => $label["removeButton"]["events"],
								"data-target" => ".ui-label",
							]);
						}
					}

					$content[$labelKey] = [
						"class" => Text\HtmlFilter::encode($labelClass),
						"attributes" => static::stringifyAttrs([
							"data-events" => $labelEvents,
						]),
						"removeButton" => $removeButton,
					];
				}
			}

			if (
				$column->getType() === Grid\Column\Type::TAGS
				&& isset($row["columns"][$columnId])
				&& is_array($row["columns"][$columnId])
			)
			{
				$addButtonEvents = [];
				$addButtonEnabled = false;
				if (
					isset($row["columns"][$columnId]["addButton"]["events"])
					&& is_array($row["columns"][$columnId]["addButton"]["events"])
				)
				{
					$addButtonEvents = $row["columns"][$columnId]["addButton"]["events"];
					$addButtonEnabled = true;
				}

				$items = [];
				foreach ($row["columns"][$columnId]["items"] as $tag)
				{
					$item = [];
					$item["class"] = "";
					$item["active"] = false;
					if (isset($tag["active"]) && $tag["active"] === true)
					{
						$item["class"] = " main-grid-tag-active";
						$item["active"] = true;
					}

					$tagEvents = [];
					if (isset($tag["events"]) && is_array($tag["events"]))
					{
						$tagEvents = $tag["events"];
					}

					$item["attributes"] = static::stringifyAttrs([
						"data-events" => $tagEvents,
					]);

					$removeButtonEvents = [];
					if (
						isset($tag["removeButton"]["events"])
						&& is_array($tag["removeButton"]["events"])
					)
					{
						$removeButtonEvents = $tag["removeButton"]["events"];
					}

					$item["removeButton"] = [
						"attributes" => static::stringifyAttrs([
							"data-events" => $removeButtonEvents,
						]),
					];

					$items[] = $item;
				}

				$content = [
					"addButton" => [
						"enabled" => $addButtonEnabled,
						"attributes" => static::stringifyAttrs([
							"data-events" => $addButtonEvents,
						]),
					],
					"items" => $items,
				];
			}

			$columns[$columnId] = [
				"cell" => [
					"class" => $cellClass,
					"attributes" => static::stringifyAttrs($cellAttributes),
				],
				"container" => [
					"attributes" => static::stringifyAttrs($containerAttributes),
				],
				"plusButton" => [
					"enabled" => $isPlusButtonEnabled,
				],
				"cellActions" => [
					"enabled" => $isCellActionsEnabled,
					"items" => $cellActions,
				],
				"counter" => $counter,
				"content" => $content,
			];
		}

		return $columns;
	}

	protected function prepareColumnWidthByColumn(Column $column): ?int
	{
		$width = $column->getWidth();

		if (!isset($width))
		{
			$resizeWidth = $this->prepareColumnsResizeMeta()['columns'][$column->getId()] ?? null;
			if (is_numeric($resizeWidth))
			{
				$width = (int)$resizeWidth;
			}
		}

		return $width;
	}

	/**
	 * Prepares additional class for column header
	 *
	 * @param Column $column
	 *
	 * @return string
	 */
	protected function prepareHeaderClassByColumn(Column $column): string
	{
		$classList = [];

		$value = $column->getCssClassName();
		if (isset($value))
		{
			$classList[] = $value;
		}

		$classList[] = 'main-grid-cell-' . $column->getAlign();

		if ($this->arParams['ALLOW_SORT'] && $column->getSort() !== null)
		{
			$classList[] = 'main-grid-col-sortable';
		}
		else
		{
			$classList[] = 'main-grid-col-no-sortable';
		}

		if ($this->arParams['ALLOW_COLUMNS_SORT'])
		{
			$classList[] = 'main-grid-draggable';
		}

		if ($this->arParams['ALLOW_STICKED_COLUMNS'] && $column->isSticked())
		{
			$classList[] = 'main-grid-sticked-column';
		}

		return ' ' . join(' ', $classList);
	}

	public function getResultColumn(string $columnId): ?Column
	{
		return $this->getResultColumnsAll()[$columnId] ?? null;
	}

	/**
	 * @return Column[] in format `[id => column]`
	 */
	protected function getResultColumns(): array
	{
		if (!isset($this->resultColumns))
		{
			$this->resultColumns = [];

			$sortIndex = 0;
			foreach ($this->getResultColumnsAll() as $columnId => $column)
			{
				if ($column->isShown())
				{
					$this->resultColumns[$columnId] = $column;
					$column->setSortIndex($sortIndex++);
				}
			}
		}

		return $this->resultColumns;
	}

	/**
	 * @return Column[] in format `[id => column]`
	 */
	protected function getResultColumnsAll(): array
	{
		if (!isset($this->resultColumnsAll))
		{
			$this->resultColumnsAll = [];

			foreach ($this->arParams['COLUMNS'] as $item)
			{
				if ($item instanceof Column)
				{
					$this->resultColumnsAll[$item->getId()] = $item;
				}
				elseif (is_array($item))
				{
					$column = $this->columnFactory->createFromArray($item);
					if (isset($column))
					{
						$this->resultColumnsAll[$column->getId()] = $column;
					}
				}
			}
		}

		return $this->resultColumnsAll;
	}

	protected function syncResultColumns(): void
	{
		// all
		$this->arResult['COLUMNS_ALL'] = [];
		foreach ($this->getResultColumnsAll() as $columnId => $column)
		{
			$this->arResult['COLUMNS_ALL'][$columnId] = $this->convertColumnToArray($column);
		}
		$this->arResult['HEADERS_ALL'] = $this->arResult['COLUMNS_ALL'];

		// showed
		$this->arResult['COLUMNS'] = [];
		foreach ($this->getResultColumns() as $columnId => $column)
		{
			$this->arResult['COLUMNS'][$columnId] = $this->arResult['COLUMNS_ALL'][$columnId];
		}
		$this->arResult['HEADERS'] = $this->arResult['COLUMNS'];

		// headers
		if (!empty($this->arParams['HEADERS_SECTIONS']) && is_array($this->arParams['HEADERS_SECTIONS']))
		{
			$this->prepareHeaderSections();
			$this->arResult['COLUMNS_ALL_WITH_SECTIONS'] = $this->getColumnsAllWithSections($this->arResult['COLUMNS_ALL']);
		}
	}

	protected function sortResultColumns(): void
	{
		if ($this->needSortColumns && !empty($this->arResult['COLUMNS']))
		{
			Collection::sortByColumn($this->arResult['COLUMNS'], [
				'sticked' => SORT_DESC,
				'sort_index' => SORT_ASC,
			]);
		}
	}

	protected function getColumnsAllWithSections(array $columns): array
	{
		$result = [];
		foreach($columns as $column)
		{
			if (!empty($column['section_id']))
			{
				$result[$column['section_id']][] = $column;
			}
			else
			{
				$result[$this->defaultHeaderSectionId][] = $column;
			}
		}

		return $result;
	}

	protected function prepareHeaderSections(): void
	{
		foreach($this->arParams['HEADERS_SECTIONS'] as $section)
		{
			$this->arResult['HEADERS_SECTIONS'][$section['id']] = $section;
			if (!empty($section['default']))
			{
				$this->defaultHeaderSectionId = $section['id'];
			}
		}
	}

	protected function prepareDefaultOptions()
	{
		$options = $this->getCurrentOptions();
		$gridOptions = $this->getGridOptions();
		$isNeedSave = false;

		if (empty($options["columns_sizes"]) || !is_array($options["columns_sizes"]))
		{
			$columnsSizes = array();
			$isNeedSave = true;

			foreach ($this->getResultColumns() as $columnId => $column)
			{
				$width = $column->getWidth();
				if (isset($width))
				{
					$columnsSizes[$columnId] = $width;
				}
			}

			$gridOptions->setColumnsSizes(null, $columnsSizes);
		}

		if ($gridOptions->getStickedColumns() === null)
		{
			$stickedColumns = [];

			foreach ($this->getResultColumnsAll() as $columnId => $column)
			{
				if ($column->isSticked() && $column->isShown())
				{
					$stickedColumns[] = $columnId;
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

					if ($ext === 'js' && !(str_contains($file, 'map.js') || str_contains($file, 'min.js')))
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
			if ($row["parent_id"] == $parent)
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

		foreach ($rows as $row)
		{
			$ids[] = $row["id"];
		}

		foreach ($rows as $key => $row)
		{
			if (!isset($row["parent_id"]) || !in_array($row["parent_id"], $ids))
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

	protected function prepareTemplateData()
	{
		$options = [
			"lastCollapsedGroupId" => null,
			"hasCellActions" => [],
		];

		foreach ($this->arParams["ROWS"] as $row)
		{
			if (isset($row["cellActions"]) && is_array($row["cellActions"]))
			{
				foreach ($row["cellActions"] as $actions => $columnCellId)
				{
					if (is_array($actions) && !empty($actions))
					{
						$options["hasCellActions"][$columnCellId] = true;
					}
				}
			}

			if (isset($row["counters"]) && is_array($row["counters"]))
			{
				foreach ($row["counters"] as $columnId => $counter)
				{
					if (
						is_array($counter)
						&& isset($counter["type"])
						&& $counter["type"] === Grid\Counter\Type::LEFT_ALIGNED
					)
					{
						foreach ($this->getResultColumns() as $column)
						{
							$column->getLayout()->setHasLeftAlignedCounter(
								$column->getId() === $columnId
							);
						}
					}
				}
			}
		}

		foreach ($this->arParams["ROWS"] as $rowId => $row)
		{
			if (!empty($row["custom"]))
			{
				$options["lastCollapsedGroupId"] = isset($row["expand"]) && $row["expand"] === false ? $row["group_id"] : null;
			}

			$row["layout"] = $this->prepareRowLayout($row, $options);
			$this->arParams["ROWS"][$rowId] = $row;
		}
	}

	public function executeComponent()
	{
		if ($this->checkRequiredParams())
		{
			if (!isset($this->arParams['ROWS']) || !is_array($this->arParams['ROWS']))
			{
				$this->arParams['ROWS'] = [];
			}

			$this->prepareTemplateRow();
			$this->prepareParams();
			$this->prepareResult();
			$this->prepareDefaultOptions();
			$this->applyUserSettings();

			if ($this->arParams["ENABLE_COLLAPSIBLE_ROWS"])
			{
				$this->prepareMultilevelRows();
			}

			$this->prepareTemplateData();

			$templateName = $this->getTemplateName();
			if ( (empty($templateName) || $templateName === ".default") && $this->arParams['TILE_GRID_MODE'])
			{
				$this->setTemplateName('tilegrid');
			}

			$this->syncResultColumns();
			$this->sortResultColumns();

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

trait DeprecatedMethods
{
	/**
	 * @deprecated use `Layout` class.
	 *
	 * @see \Bitrix\Main\Grid\Column\UI\Layout
	 *
	 * @param mixed $column
	 *
	 * @return string
	 */
	protected function prepareColumnHeaderContainerStyle($column): string
	{
		$style = "";
		if (isset($column["width"]))
		{
			$style .= "width: {$column["width"]}px; ";
		}

		return $style;
	}

	/**
	 * @deprecated use `Layout` class.
	 *
	 * @see \Bitrix\Main\Grid\Column\UI\Layout
	 *
	 * @param mixed $column
	 *
	 * @return string
	 */
	protected function prepareColumnHeaderClass($column): string
	{
		$result = "";
		if (isset($column["class"]) && is_string($column["class"]))
		{
			$result .= " " . $column["class"];
		}

		if ($this->arParams["ALLOW_COLUMNS_SORT"])
		{
			$result .= " main-grid-draggable";
		}

		if ($this->arParams["ALLOW_STICKED_COLUMNS"] && $column["sticked"])
		{
			$result .= " main-grid-sticked-column";
		}

		if (
			isset($column["color"])
			&& is_string($column["color"])
			&& (
				!str_contains($column["color"], "#")
				&& !str_contains($column["color"], "rgb")
				&& !str_contains($column["color"], "hsl")
			)
		)
		{
			$result .= " " . $column["color"];
		}

		return $result;
	}

	/**
	 * @deprecated use `Attributes` class.
	 *
	 * @see \Bitrix\Main\Html\Attributes
	 *
	 * @param mixed $attrs
	 *
	 * @return string
	 */
	protected static function stringifyAttrs($attrs): string
	{
		$attrsString = "";
		if (is_array($attrs) && !empty($attrs))
		{
			$attrsString = " ";
			foreach ($attrs as $key => $value)
			{
				if (is_array($value))
				{
					$escapedValue = "(" . Text\HtmlFilter::encode(Json::encode($value)) . ")";
				}
				else
				{
					if (is_bool($value))
					{
						$escapedValue = $value ? "true" : "false";
					}
					else
					{
						$escapedValue = Text\HtmlFilter::encode($value);
					}
				}

				$escapedKey = Text\HtmlFilter::encode($key);
				$attrsString .= $escapedKey."=\"".$escapedValue."\"";
			}

			$attrsString .= " ";
		}

		return $attrsString;
	}

	/**
	 * @deprecated use `Layout` class.
	 *
	 * @see \Bitrix\Main\Grid\Column\UI\Layout
	 *
	 * @param mixed $column
	 *
	 * @return string
	 */
	protected function prepareColumnHeaderStyle($column): string
	{
		$style = "";
		if (isset($column["width"]))
		{
			$style .= "width: {$column["width"]}px; ";
		}

		if (
			isset($column["color"])
			&& is_string($column["color"])
			&& (
				str_starts_with($column["color"], "#")
				|| str_starts_with($column["color"], "rgb")
				|| str_starts_with($column["color"], "hsl")
			)
		)
		{
			$style .= "background-color: {$column["color"]};";
		}

		return $style;
	}

	/**
	 * @deprecated use `::prepareColumnHeaderLayoutByColumn` method.
	 *
	 * @param mixed $column
	 *
	 * @return array
	 */
	protected function prepareColumnHeaderLayout($column): array
	{
		return [
			"cell" => [
				"class" => $this->prepareColumnHeaderClass($column),
				"attributes" => static::stringifyAttrs([
					"data-edit" => $column["editable"],
					"data-name" => $column["id"],
					"data-sort-url" => $column["sort_url"],
					"data-sort-by" => $column["sort"],
					"data-sort-order" => $column["next_sort_order"],
					"title" => $column["title"] ?? '',
					"style" => $this->prepareColumnHeaderStyle($column),
				]),
			],
			"container" => [
				"attributes" => static::stringifyAttrs([
					"style" => $this->prepareColumnHeaderContainerStyle($column),
				]),
			],
		];
	}

	/**
	 * @deprecated use `Column` class
	 * @see Column
	 *
	 * Prepares sticked value
	 * @param array $column
	 * @return bool
	 */
	protected function prepareSticked($column)
	{
		return isset($column["sticked"]) && is_bool($column["sticked"]) && $column["sticked"];
	}

	/**
	 * @deprecated use `Column` class
	 * @see Column
	 */
	protected function preparePreventDefault(array $column)
	{
		$result = true;

		if (isset($column["prevent_default"]))
		{
			$result = (bool) $column["prevent_default"];
		}

		return $result;
	}

	/**
	 * @deprecated use `getResultColumnsAll` method.
	 *
	 * Prepares each header items
	 * @return array prepares header
	 */
	protected function prepareColumnsAll()
	{
		if (!isset($this->arResult["COLUMNS_ALL"]) || empty($this->arResult["COLUMNS_ALL"]))
		{
			$this->arResult["COLUMNS_ALL"] = array();

			foreach ($this->arParams["COLUMNS"] as $item)
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
	 * @deprecated use `getResultColumns` method.
	 *
	 * Prepares visible headers
	 * @method prepareHeaders
	 * @return array
	 */
	protected function prepareColumns()
	{
		$result = [];

		foreach ($this->getResultColumns() as $columnId => $column)
		{
			$result[$columnId] = $this->convertColumnToArray($column);
		}

		return $result;
	}

	/**
	 * @deprecated use columns objects.
	 *
	 * Checks is shown current column
	 * @method isShownColumn
	 * @param  array $headerItem header item
	 * @return boolean
	 */
	protected function isShownColumn(array $headerItem)
	{
		return isset($headerItem["default"]) && $headerItem["default"];
	}

	/**
	 * @deprecated use `::convertColumnToArray` method.
	 *
	 * Prepares each column
	 * @method prepareColumn
	 * @param  array $column header item
	 * @return array | null prepared header item
	 */
	protected function prepareColumn(array $column)
	{
		$column = $this->columnFactory->createFromArray($column);
		if (isset($column))
		{
			return $this->convertColumnToArray($column);
		}

		return null;
	}

	/**
	 * @deprecated use `::prepareHeaderClassByColumn` method.
	 *
	 * Prepares additional class for column header
	 * @param array $headerItem
	 *
	 * @return string
	 */
	protected function prepareHeaderClass(array $headerItem)
	{
		$classList = array();

		if (isset($headerItem["class"]))
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
	 * @deprecated use columns objects.
	 *
	 * Prepares align value
	 * @method prepareAlign
	 * @param  array $headerItem header item
	 * @return string css align property value
	 */
	protected function prepareAlign($headerItem)
	{
		return $headerItem['align'] ?? "left";
	}

	/**
	 * @deprecated use columns objects.
	 *
	 * Prepares header name
	 * @method prepareHeaderName
	 * @param  array $headerItem header item
	 * @return string prepared header name
	 */
	protected function prepareHeaderName(array $headerItem)
	{
		return $headerItem["name"] ?? '';
	}

	/**
	 * @deprecated use columns objects.
	 *
	 * Checks is shown header name for current header item
	 * @method isShowHeaderName
	 * @param  array $headerItem
	 * @return boolean
	 */
	protected function isShowHeaderName(array $headerItem)
	{
		$isShow = true;

		if (isset($headerItem['showname']) && ($headerItem['showname'] === false || $headerItem['showname'] === "N"))
		{
			$isShow = false;
		}

		return $isShow;
	}

	/**
	 * @deprecated use columns objects.
	 *
	 * @param array $headerItem
	 *
	 * @return string
	 */
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

	/**
	 * @deprecated use columns objects.
	 *
	 * @param mixed $sort
	 *
	 * @return void
	 */
	protected function prepareSort($sort)
	{
		return $sort;
	}

	/**
	 * @deprecated use columns objects.
	 *
	 * Prepares sort order value
	 * @method prepareSortOrder
	 * @param  array $headerItem header item
	 * @return string sort order value ["desc", "asc"]
	 */
	protected function prepareSortOrder(array $headerItem)
	{
		$sort = isset($headerItem["ORDER"]) && $headerItem["ORDER"] === "desc" ? "desc" : "asc";

		if (isset($headerItem["first_order"]) && is_string($headerItem["first_order"]))
		{
			$sort = $headerItem["first_order"];
		}

		return $sort;
	}

	/**
	 * @deprecated use columns objects.
	 *
	 * @param mixed $column
	 *
	 * @return void
	 */
	protected function prepareEditable($column)
	{
		$result = $column["editable"] ?? false;
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
				$columnTypeName =
					isset($column["type"]) && is_string($column["type"])
						? $column["type"]
						: ""
				;
				$result["TYPE"] = Grid\Column\Type::getEditorType($columnTypeName) ?? '';
			}

			if($result["TYPE"] === Grid\Editor\Types::MONEY	&& is_array($result["CURRENCY_LIST"]))
			{
				$currencyList = $result["CURRENCY_LIST"];
				$result["CURRENCY_LIST"] = [];
				foreach($currencyList as $k => $v)
				{
					$result["CURRENCY_LIST"][] = array("VALUE" => $k, "NAME" => $v);
				}
			}

			if (
				(
					$result["TYPE"] === Grid\Editor\Types::DROPDOWN
					|| $result["TYPE"] === Grid\Editor\Types::MULTISELECT
				)
				&& $result["items"] && is_array($result["items"])
				&& !(
					isset($result["DATA"]["ITEMS"])
					&& is_array($result["DATA"]["ITEMS"])
				)
			)
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
	 * @deprecated use columns objects.
	 *
	 * @param mixed $column
	 *
	 * @return bool
	 */
	protected function prepareResizeable($column)
	{
		return !isset($column["resizeable"]) || $column["resizeable"] !== false;
	}

	/**
	 * @deprecated use `::prepareColumnWidthByColumn` method.
	 *
	 * @param array $column
	 *
	 * @return void
	 */
	protected function prepareColumnWidth(array $column)
	{
		$width = null;

		if (isset($column["width"]) && is_numeric($column["width"]))
		{
			$width = $column["width"];
		}
		else
		{
			$columns = $this->prepareColumnsResizeMeta();
			if (isset($columns["columns"][$column["id"]]) && is_numeric($columns["columns"][$column["id"]]))
			{
				$width = $columns["columns"][$column["id"]];
			}

		}

		return $width;
	}

	/**
	 * @deprecated use `::getSortUrlByColumn` method.
	 *
	 * Gets sort url
	 * @method getSortUrl
	 * @param  array $headerItem
	 * @return string sort url
	 */
	protected function getSortUrl(array $headerItem)
	{
		$uri = clone $this->getUri();
		$uri->addParams(array(
			"by" => $headerItem["sort"] ?? '',
			"order" => $headerItem["next_sort_order"] ?? ''
		));

		return $uri->getUri();
	}

	/**
	 * @deprecated use `::prepareSortStateByColumn` method.
	 *
	 * Prepares sort state value
	 * @method prepareSortState
	 * @param  array $headerItem header item
	 * @return string ["desc", "asc"]
	 */
	protected function prepareSortState(array $headerItem)
	{
		$state = null;

		if (
			isset($this->arParams["SORT"]) &&
			is_array($this->arParams["SORT"]) &&
			(isset($headerItem["sort"]) && (is_string($headerItem["sort"]) || is_int($headerItem["sort"]))) &&
			array_key_exists($headerItem["sort"], $this->arParams["SORT"])
		)
		{
			$state = $this->arParams["SORT"][$headerItem["sort"]];
		}
		else
		{
			$options = $this->getCurrentOptions();

			if (
				isset($headerItem["sort"])
				&& isset($options["last_sort_by"])
				&& $options["last_sort_by"] === $headerItem["sort"]
			)
			{
				$state = $options["last_sort_order"];
			}
		}

		return $state;
	}
}
