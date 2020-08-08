<?php
namespace Bitrix\Iblock\Component\Selector;

use Bitrix\Main,
	Bitrix\Main\UI,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

abstract class Entity extends \CBitrixComponent implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	const RESULT_ACTION_TYPE_NONE = 'none';
	const RESULT_ACTION_TYPE_EVENT = 'event';
	const RESULT_ACTION_TYPE_CALLBACK = 'callback';
	const RESULT_ACTION_TYPE_CLASS_METHOD = 'method';
	const RESULT_ACTION_TYPE_SLIDER = 'slider';

	const RESULT_DATA_TYPE_NONE = 'none';
	const RESULT_DATA_TYPE_SET = 'set';
	const RESULT_DATA_TYPE_FILTER = 'filter';

	const MODE_PAGE = 'page';
	const MODE_DIALOG = 'dialog';
	const MODE_SLIDER = 'slider';

	const STORAGE_GRID = 'GRID';
	const STORAGE_GRID_FILTER = 'GRID_FILTER';
	const STORAGE_ENTITY_IBLOCK = 'IBLOCK_SETTINGS';

	protected $useMode = self::MODE_PAGE;

	/** @var  Main\ErrorCollection */
	protected $errorCollection = null;

	/** @var Main\Grid\Options $gridConfig */
	protected $gridConfig = null;

	protected $storage = [];

	protected $defaultSettings = [];

	protected $rows = [];

	/** @var UI\PageNavigation $navigation */
	protected $navigation = null;

	protected $navigationString = '';

	protected $implicitPageNavigation = false;

	/** @var bool use filter in selector */
	protected $useGridFilter = true;

	/** @var UI\Filter\Options */
	protected $gridFilterConfig = null;

	protected $resultAction = [
		'TYPE' => self::RESULT_ACTION_TYPE_NONE,
		'NAME' => '',
		'DATA_TYPE' => self::RESULT_DATA_TYPE_NONE,
		'DATA_SET' => []
	];

	protected $resultActionMap = [
		self::MODE_PAGE => [
			self::RESULT_ACTION_TYPE_EVENT,
			self::RESULT_ACTION_TYPE_CALLBACK,
			self::RESULT_ACTION_TYPE_CLASS_METHOD
		],
		self::MODE_DIALOG => [
			self::RESULT_ACTION_TYPE_EVENT,
			self::RESULT_ACTION_TYPE_CALLBACK,
			self::RESULT_ACTION_TYPE_CLASS_METHOD
		],
		self::MODE_SLIDER => [
			self::RESULT_ACTION_TYPE_EVENT,
			self::RESULT_ACTION_TYPE_CALLBACK,
			self::RESULT_ACTION_TYPE_CLASS_METHOD,
			self::RESULT_ACTION_TYPE_SLIDER
		]
	];

	/**
	 * Base constructor.
	 * @param \CBitrixComponent|null $component		Component object if exists.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new Main\ErrorCollection();
	}

	/**
	 * @return void
	 */
	public function onIncludeComponentLang()
	{
		Loc::loadMessages(__FILE__);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$gridId = '';
		if (isset($params['GRID_ID']) && is_string($params['GRID_ID']))
		{
			$gridId = preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', $params['GRID_ID']);
		}
		$params['GRID_ID'] = $gridId;
		unset($gridId);

		$filterId = '';
		if (isset($params['FILTER_ID']) && is_string($params['FILTER_ID']))
		{
			$filterId = preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', $params['FILTER_ID']);
		}
		$params['FILTER_ID'] = $filterId;
		unset($filterId);

		$navigationId = '';
		if (isset($params['NAVIGATION_ID']) && is_string($params['NAVIGATION_ID']))
		{
			$navigationId = preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', $params['NAVIGATION_ID']);
		}
		$params['NAVIGATION_ID'] = $navigationId;
		unset($navigationId);

		if (!empty($params['GRID_ID']))
		{
			if (empty($params['FILTER_ID']))
			{
				$params['FILTER_ID'] = static::createFilterId($params['GRID_ID']);
			}
			if (empty($params['NAVIGATION_ID']))
			{
				$params['NAVIGATION_ID'] = static::createNavigationId($params['GRID_ID']);
			}
		}

		$params['MULTIPLE_SELECT'] = (isset($params['MULTIPLE_SELECT']) && $params['MULTIPLE_SELECT'] === 'Y');

		$params['BASE_LINK'] = (isset($params['BASE_LINK']) ? $params['BASE_LINK'] : '');

		$params['IBLOCK_ID'] = (isset($params['IBLOCK_ID']) ? (int)$params['IBLOCK_ID'] : 0);
		if ($params['IBLOCK_ID'] < 0)
			$params['IBLOCK_ID'] = 0;

		$params['USE_MODE'] = (isset($params['USE_MODE']) && is_string($params['USE_MODE'])
			? trim($params['USE_MODE'])
			: self::MODE_PAGE
		);

		$params['RESULT_ACTION_TYPE'] = (isset($params['RESULT_ACTION_TYPE']) && is_string($params['RESULT_ACTION_TYPE'])
			? trim($params['RESULT_ACTION_TYPE'])
			: self::RESULT_ACTION_TYPE_NONE
		);

		if (!isset($params['RESULT_ACTION_NAME']))
			$params['RESULT_ACTION_NAME'] = '';
		if (!is_string($params['RESULT_ACTION_NAME']) && !is_array($params['RESULT_ACTION_NAME']))
			$params['RESULT_ACTION_NAME'] = '';
		$params['RESULT_ACTION_NAME'] = preg_replace('/[^a-zA-Z0-9]/', '', $params['RESULT_ACTION_NAME']);
		if (empty($params['RESULT_ACTION_NAME']))
			$params['RESULT_ACTION_TYPE'] = self::RESULT_ACTION_TYPE_NONE;

		$params['RESULT_DATA_TYPE'] = (isset($params['RESULT_DATA_TYPE']) && is_string($params['RESULT_DATA_TYPE'])
			? $params['RESULT_DATA_TYPE']
			: self::RESULT_DATA_TYPE_NONE
		);
		if ($params['RESULT_DATA_TYPE'] == self::RESULT_DATA_TYPE_SET)
			$params['MULTIPLE_SELECT'] = false;

		$params['RESULT_DATA_SET_LIST'] = (isset($params['RESULT_DATA_SET_LIST']) && is_array($params['RESULT_DATA_SET_LIST'])
			? $params['RESULT_DATA_SET_LIST']
			: []
		);

		$params['PAGETITLE_FILTER'] = (isset($params['PAGETITLE_FILTER']) && $params['PAGETITLE_FILTER'] === 'Y' ? 'Y' : 'N');

		return $params;
	}

	/**
	 * @return void
	 */
	public function executeComponent()
	{
		$this->checkModules();
		$this->prepareRequest();
		$this->initDefaultSettings();
		$this->initSettings();
		$this->prepareResult();
		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @param string $code
	 * @return Main\Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * @return array|Main\Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * @return void
	 */
	protected function useImplicitPageNavigation()
	{
		$this->implicitPageNavigation = true;
	}

	/**
	 * @return bool
	 */
	protected function isUsedImplicitPageNavigation()
	{
		return $this->implicitPageNavigation;
	}

	/**
	 * @return void
	 */
	protected function disableGridFilter()
	{
		$this->useGridFilter = false;
	}

	/**
	 * @return bool
	 */
	protected function isUsedGridFilter()
	{
		return $this->useGridFilter;
	}

	/**
	 * @param string $mode
	 * @return void
	 */
	protected function setUseMode($mode)
	{
		if (!isset($this->resultActionMap[$mode]))
			return;
		$this->useMode = $mode;
	}

	/**
	 * @return string
	 */
	protected function getUseMode()
	{
		return $this->useMode;
	}

	/**
	 * @return bool
	 */
	protected function isPageMode()
	{
		return ($this->useMode == self::MODE_PAGE);
	}

	/**
	 * @return bool
	 */
	protected function isDialogWindowMode()
	{
		return ($this->useMode == self::MODE_DIALOG);
	}

	/**
	 * @return bool
	 */
	protected function isSliderMode()
	{
		return ($this->useMode == self::MODE_SLIDER);
	}

	/**
	 * @return void
	 */
	protected function initResultDescription()
	{
		$mode = $this->getUseMode();
		$resultAction = $this->arParams['RESULT_ACTION_TYPE'];
		if (!in_array($resultAction, $this->resultActionMap[$mode]))
			$resultAction = self::RESULT_ACTION_TYPE_NONE;
		$dataType = $this->arParams['RESULT_DATA_TYPE'];
		if (
			$dataType != self::RESULT_DATA_TYPE_SET
			&& $dataType != self::RESULT_DATA_TYPE_FILTER
		)
			$dataType = self::RESULT_DATA_TYPE_NONE;
		switch ($dataType)
		{
			case self::RESULT_DATA_TYPE_SET:
				if (empty($this->arParams['RESULT_DATA_SET_LIST']))
				{
					$dataType = self::RESULT_DATA_TYPE_NONE;
				}
				break;
			case self::RESULT_DATA_TYPE_FILTER:
				if (!$this->isUsedGridFilter())
				{
					$dataType = self::RESULT_DATA_TYPE_NONE;
				}
				break;
		}

		$this->resultAction['TYPE'] = $resultAction;
		$this->resultAction['NAME'] = $this->arParams['RESULT_ACTION_NAME'];
		$this->resultAction['DATA_TYPE'] = $dataType;
		if ($dataType == self::RESULT_DATA_TYPE_SET)
			$this->resultAction['DATA_SET'] = $this->arParams['RESULT_DATA_SET_LIST'];
	}

	/**
	 * @return bool
	 */
	protected function isUsedSliderFilter()
	{
		return (
			$this->isSliderMode()
			&& $this->resultAction['TYPE'] == self::RESULT_ACTION_TYPE_SLIDER
			&& $this->resultAction['DATA_TYPE'] == self::RESULT_DATA_TYPE_FILTER
		);
	}

	/**
	 * @return void
	 */
	protected function initEntitySettings()
	{
		$description = [
			'IBLOCK_ID' => null,
			'IBLOCK_NAME' => null,
			'IBLOCK_TYPE_ID' => null,
			'IBLOCK_SECTIONS' => 'N',
			'IBLOCK_SECTIONS_NAME' => null,
			'IBLOCK_ELEMENTS_NAME' => null,
			'SHOW_XML_ID' => (string)Main\Config\Option::get('iblock', 'show_xml_id') === 'Y',
			'FILTER_ALL' => Loc::getMessage('ENTITY_SELECTOR_SLIDER_FILTER_EMPTY')
		];
		if ($this->arParams['IBLOCK_ID'] > 0)
		{
			$iterator = Iblock\IblockTable::getList([
				'select' => ['ID', 'IBLOCK_TYPE_ID', 'NAME'],
				'filter' => ['=ID' => $this->arParams['IBLOCK_ID'], '=ACTIVE' => 'Y']
			]);
			$iblock = $iterator->fetch();
			unset($iterator);
			if (!empty($iblock))
			{
				$description['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
				$description['NAME'] = $iblock['NAME'];
				$iterator = Iblock\TypeTable::getList([
					'select' => ['ID', 'SECTIONS'],
					'filter' => ['=ID' => $iblock['IBLOCK_TYPE_ID']]
				]);
				$type = $iterator->fetch();
				unset($iterator);
				if (!empty($type))
				{
					$description['IBLOCK_TYPE_ID'] = $iblock['IBLOCK_TYPE_ID'];
					$description['IBLOCK_SECTIONS'] = $type['SECTIONS'];

					$iterator = Iblock\TypeLanguageTable::getList([
						'select' => ['IBLOCK_TYPE_ID', 'SECTIONS_NAME', 'ELEMENTS_NAME'],
						'filter' => ['=IBLOCK_TYPE_ID' => $iblock['IBLOCK_TYPE_ID'], '=LANGUAGE_ID' => LANGUAGE_ID]
					]);
					$messages = $iterator->fetch();
					unset($iterator);
					if (!empty($messages))
					{
						$description['IBLOCK_SECTIONS_NAME'] = $messages['SECTIONS_NAME'];
						$description['IBLOCK_ELEMENTS_NAME'] = $messages['ELEMENTS_NAME'];
					}
					unset($messages);
				}
				unset($type);
				$description['FILTER_ALL'] = Loc::getMessage('ENTITY_SELECTOR_SLIDER_FILTER_ALL_ELEMENTS');
			}
			unset($iblock);
		}
		$this->fillStorageNode(self::STORAGE_ENTITY_IBLOCK, $description);
		unset($description);
	}

	/**
	 * @return void
	 */
	protected function checkModules() {}

	/**
	 * @return void
	 */
	protected function prepareRequest() {}

	/**
	 * @return void
	 */
	protected function initDefaultSettings()
	{
		$this->defaultSettings = [
			'GRID_ID' => $this->getDefaultGridId()
		];
		$this->defaultSettings['FILTER_ID'] = static::createFilterId($this->defaultSettings['GRID_ID']);
		$this->defaultSettings['NAVIGATION_ID'] = static::createNavigationId($this->defaultSettings['GRID_ID']);
		$this->defaultSettings['PAGE_SIZES'] = [5, 10, 20, 50, 100];
	}

	/**
	 * @return string
	 */
	protected function getDefaultGridId()
	{
		return preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', get_called_class());
	}

	/**
	 * @param string $gridId
	 * @return string
	 */
	protected static function createFilterId($gridId)
	{
		return $gridId.'_FILTER';
	}

	/**
	 * @param string $gridId
	 * @return string
	 */
	protected static function createNavigationId($gridId)
	{
		return $gridId.'_NAVIGATION';
	}

	/**
	 * @return void
	 */
	protected function initSettings()
	{
		$this->setUseMode($this->arParams['USE_MODE']);
		$this->initResultDescription();

		$this->initEntitySettings();

		$paramsList = [
			self::STORAGE_GRID => [
				'GRID_ID', 'NAVIGATION_ID', 'PAGE_SIZES'
			],
			self::STORAGE_GRID_FILTER => [
				'FILTER_ID'
			]
		];
		foreach ($paramsList as $entity => $list)
		{
			foreach ($list as $param)
			{
				$value = (!empty($this->arParams[$param])
					? $this->arParams[$param]
					: $this->defaultSettings[$param]
				);
				$this->setStorageItem($entity, $param, $value);
			}
		}
		unset($param, $list, $entity, $paramsList);

		$this->initGrid();
		$this->initGridFilter();
	}

	/**
	 * @return void
	 */
	protected function prepareResult()
	{
		$this->initClientScope();

		$this->getData();

		$filter = [];
		if ($this->isUsedGridFilter())
		{
			$filter = [
				'FILTER_ID' => $this->getFilterId(),
				'GRID_ID' => $this->getGridId(),
				'FILTER' => $this->getGridFilterRows(),
				'FILTER_PRESETS' => [],
				'DISABLE_SEARCH' => $this->getQuickSearchField() === null,
				'ENABLE_LABEL' => true,
				'ENABLE_LIVE_SEARCH' => true
			];
		}

		$grid = [
			'GRID_ID' => $this->getGridId(),
			'COLUMNS' => array_values($this->getColumns()),

			'NAV_OBJECT' => $this->navigation,
			'~NAV_PARAMS' => ['SHOW_ALWAYS' => false],
			'SHOW_ROW_CHECKBOXES' => $this->arParams['MULTIPLE_SELECT'],
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_PAGINATION' => true,
			'SHOW_PAGESIZE' => true,
			'SHOW_SELECTED_COUNTER' => $this->arParams['MULTIPLE_SELECT'],
			'SHOW_TOTAL_COUNTER' => true,
			//'ACTION_PANEL' => $controlPanel,
			"TOTAL_ROWS_COUNT" => $this->navigation->getRecordCount(),

			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => true,
			'ALLOW_SORT' => true,
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N'
		];
		if ($this->isUsedImplicitPageNavigation())
			$grid['NAV_STRING'] = $this->navigationString;

		$grid['PAGE_SIZES'] = [];
		foreach ($this->getPageSizes() as $size)
		{
			$grid['PAGE_SIZES'][] = [
				'NAME' => (string)$size,
				'VALUE' => $size
			];
		}
		unset($size);

		$grid['SORT'] = $this->getStorageItem(self::STORAGE_GRID, 'GRID_ORDER');
		$grid['SORT_VARS'] = $this->getStorageItem(self::STORAGE_GRID, 'GRID_ORDER_VARS');

		$grid['ROWS'] = $this->getGridRows();

		$settings = [
			'USE_MODE' => $this->getUseMode(),
			'RESULT_ACTION' => $this->resultAction,
			'FILTER' => [
				'PAGETITLE' => $this->arParams['PAGETITLE_FILTER']
			]
		];

		$this->arResult = [
			'FILTER' => $filter,
			'GRID' => $grid,
			'SETTINGS' => $settings
		];
		unset($grid, $filter);
	}

	/* Client library, styles, etc tools */

	/**
	 * @return void
	 */
	protected function initClientScope()
	{
		global $APPLICATION;

		UI\Extension::load($this->getClientExtensions());

		foreach ($this->getClientStyles() as $styleList)
		{
			$APPLICATION->SetAdditionalCSS($styleList);
		}
	}

	/**
	 * @return array
	 */
	protected function getClientExtensions()
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected function getClientStyles()
	{
		return [
			'/bitrix/css/main/grid/webform-button.css'
		];
	}

	/* Client library, styles, etc tools finish */

	/* Storage tools */

	/**
	 * @param string $node
	 * @param array $nodeValues
	 * @return void
	 */
	protected function fillStorageNode($node, array $nodeValues)
	{
		$node = (string)$node;
		if ($node === '' || empty($nodeValues))
			return;
		if (!isset($this->storage[$node]))
			$this->storage[$node] = [];
		$this->storage[$node] = array_merge($this->storage[$node], $nodeValues);
	}

	/**
	 * @param $node
	 * @return array|null
	 */
	protected function getStorageNode($node)
	{
		if (isset($this->storage[$node]))
			return $this->storage[$node];
		return null;
	}

	/**
	 * @param string $node
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	protected function setStorageItem($node, $item, $value)
	{
		$this->fillStorageNode($node, [$item => $value]);
	}

	/**
	 * @param string $node
	 * @param string $item
	 * @return mixed|null
	 */
	protected function getStorageItem($node, $item)
	{
		if (isset($this->storage[$node][$item]))
			return $this->storage[$node][$item];
		return null;
	}

	/**
	 * @return boolean
	 */
	protected function getShowXmlId()
	{
		return $this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'SHOW_XML_ID');
	}

	/**
	 * @return string
	 */
	protected function getGridId()
	{
		return $this->getStorageItem(self::STORAGE_GRID, 'GRID_ID');
	}

	/**
	 * @return string
	 */
	protected function getFilterId()
	{
		return $this->getStorageItem(self::STORAGE_GRID_FILTER, 'FILTER_ID');
	}

	/**
	 * @return string
	 */
	protected function getNavigationId()
	{
		return $this->getStorageItem(self::STORAGE_GRID, 'NAVIGATION_ID');
	}

	/**
	 * @return array
	 */
	protected function getPageSizes()
	{
		return $this->getStorageItem(self::STORAGE_GRID, 'PAGE_SIZES');
	}

	/* Storage tools finish */

	/**
	 * @return void
	 */
	protected function initGrid()
	{
		$this->initGridConfig();
		$this->initGridColumns();
		$this->initGridPageNavigation();
		$this->initGridOrder();
	}

	/**
	 * @return void
	 */
	protected function initGridConfig()
	{
		$this->gridConfig = new Main\Grid\Options($this->getGridId());
	}

	/**
	 * @return void
	 */
	protected function initGridColumns()
	{
		$visibleColumns = [];
		$visibleColumnsMap = [];

		$defaultList = true;
		$userColumnsIndex = [];
		$userColumns = $this->getUserGridColumnIds();
		if (!empty($userColumns))
		{
			$defaultList = false;
			$userColumnsIndex = array_fill_keys($userColumns, true);
		}

		$columns = $this->getGridColumnsDescription();
		foreach (array_keys($columns) as $index)
		{
			if (
				$defaultList
				|| isset($userColumnsIndex[$index])
			)
			{
				$visibleColumnsMap[$index] = true;
				$visibleColumns[$index] = $columns[$index];
			}
		}
		unset($index);

		unset($userColumns, $userColumnsIndex, $defaultList);

		$this->fillStorageNode(
			self::STORAGE_GRID,
			[
				'COLUMNS' => $columns,
				'VISIBLE_COLUMNS' => $visibleColumns,
				'VISIBLE_COLUMNS_MAP' => $visibleColumnsMap
			]
		);

		//$this->setStorageValue('COLUMNS', $columns);
		//$this->setStorageValue('VISIBLE_COLUMNS', $visibleColumns);
		//$this->setStorageValue('VISIBLE_COLUMNS_MAP', $visibleColumnsMap);

		unset($visibleColumnsMap, $visibleColumns, $columns);
	}

	/**
	 * @return void
	 */
	protected function initGridPageNavigation()
	{
		$naviParams = $this->getGridNavigationParams();
		$this->navigation = new UI\PageNavigation($this->getNavigationId());
		$this->navigation->setPageSizes($this->getPageSizes());
		$this->navigation->allowAllRecords(false);
		$this->navigation->setPageSize($naviParams['nPageSize']);
		if (!$this->isUsedImplicitPageNavigation())
			$this->navigation->initFromUri();
		unset($naviParams);
	}

	/**
	 * @return array
	 */
	protected function getGridNavigationParams()
	{
		return $this->gridConfig->getNavParams(['nPageSize' => 20]);
	}

	/**
	 * @return array
	 */
	protected function getGridFilterDefinition()
	{
		$result = [];
		$result['NAME'] = [
			'id' => 'NAME',
			'type' => 'string',
			'name' => Loc::getMessage('ENTITY_SELECTOR_FILTER_FIELD_NAME'),
			'quickSearch' => true,
			'operators' => [
				'default' => '%',
				'quickSearch' => '?'
			],
			'default' => true
		];
		$result['ID'] = [
			'id' => 'ID',
			'name' => Loc::getMessage('ENTITY_SELECTOR_FILTER_FIELD_ID'),
			'type' => 'number',
			'operators' => [
				'default' => '=',
				'exact' => '=',
				'range' => '><',
				'more' => '>',
				'less' => '<'
			],
			'default' => true
		];
		$result['ACTIVE'] = [
			'id' => 'ACTIVE',
			'name' => Loc::getMessage('ENTITY_SELECTOR_FILTER_FIELD_ACTIVE'),
			'type' => 'list',
			'items' => $this->getBinaryDictionary(),
			'operators' => [
				'default' => '=',
				'exact' => '='
			],
			'default' => false
		];
		if ($this->getShowXmlId())
		{
			$result['XML_ID'] = [
				'id' => 'XML_ID',
				'name' => Loc::getMessage('ENTITY_SELECTOR_FILTER_FIELD_XML_ID'),
				'type' => 'string',
				'operators' => [
					'default' => '='
				],
				'default' => false
			];
		}
		$result['CODE'] = [
			'id' => 'CODE',
			'name' => Loc::getMessage('ENTITY_SELECTOR_FILTER_FIELD_CODE'),
			'type' => 'string',
			'operators' => [
				'default' => '='
			],
			'default' => false
		];

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getGridFilterRows()
	{
		return array_filter($this->getGridFilterDefinition(), [__CLASS__, 'isGridFilterRow']);
	}

	/**
	 * @return string|null
	 */
	protected function getQuickSearchField()
	{
		return $this->getStorageItem(self::STORAGE_GRID_FILTER, 'QUICK_SEARCH_FIELD');
	}

	/**
	 * @return array
	 */
	protected function getQuickSearchDescription()
	{
		return $this->getStorageItem(self::STORAGE_GRID_FILTER, 'QUICK_SEARCH_DESCRIPTION');
	}

	/**
	 * @return array
	 */
	protected function getGridColumnsDescription()
	{
		$result = [];

		$result['ID'] = [
			'id' => 'ID',
			'name' => 'ID',
			'sort' => 'ID',
			'default' => true
		];
		$result['NAME'] = [
			'id' => 'NAME',
			'name' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_NAME'),
			'sort' => 'NAME',
			'default' => true
		];
		$result['ACTIVE'] = [
			'id' => 'ACTIVE',
			'name' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_ACTIVE'),
			'title' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_TITLE_ACTIVE'),
			'sort' => 'ACTIVE',
			'default' => true
		];
		if ($this->getShowXmlId())
		{
			$result['XML_ID'] = [
				'id' => 'XML_ID',
				'name' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_XML_ID'),
				'sort' => 'XML_ID',
				'default' => false
			];
		}
		$result['CODE'] = [
			'id' => 'CODE',
			'name' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_CODE'),
			'sort' => 'CODE',
			'default' => false
		];
		$result['SORT'] = [
			'id' => 'SORT',
			'name' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_SORT'),
			'title' => Loc::getMessage('ENTITY_SELECTOR_GRID_COLUMN_TITLE_SORT'),
			'sort' => 'SORT',
			'default' => false
		];

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getUserGridColumnIds()
	{
		$result = $this->gridConfig->GetVisibleColumns();
		if (empty($result))
		{
			$oldOptions = \CUserOptions::GetOption('list', $this->getGridId(), []);
			if (!empty($oldOptions['columns']))
			{
				$oldGridColumns = [];
				$rawColumns = explode(',', $oldOptions['columns']);
				foreach ($rawColumns as $id)
				{
					$id = trim($id);
					if ($id !== '')
						$oldGridColumns[] = $id;
				}
				unset($id, $rawColumns);
				if (!empty($oldGridColumns))
					$result = $oldGridColumns;
				unset($oldGridColumns);
			}
			unset($oldOptions);

			if (!empty($result))
				$this->gridConfig->SetVisibleColumns($result);
		}
		if (!empty($result) && !in_array('ID', $result))
			array_unshift($result, 'ID');
		return $result;
	}

	/**
	 * @return array
	 */
	protected function getColumns()
	{
		return $this->getStorageItem(self::STORAGE_GRID, 'COLUMNS');
	}

	/**
	 * @return array
	 */
	protected function getVisibleColumns()
	{
		return $this->getStorageItem(self::STORAGE_GRID, 'VISIBLE_COLUMNS');
	}

	/**
	 * @return void
	 */
	protected function initGridOrder()
	{
		$result = ['ID' => 'DESC'];

		$sorting = $this->gridConfig->getSorting(['sort' => $result]);

		$order = mb_strtolower(reset($sorting['sort']));
		if ($order !== 'asc')
			$order = 'desc';
		$field = key($sorting['sort']);
		$found = false;

		foreach ($this->getVisibleColumns() as $column)
		{
			if (!isset($column['sort']))
				continue;
			if ($column['sort'] == $field)
			{
				$found = true;
				break;
			}
		}
		unset($column);

		if ($found)
			$result = [$field => $order];

		$this->fillStorageNode(
			self::STORAGE_GRID,
			[
				'GRID_ORDER' => $this->modifyGridOrder($result),
				'GRID_ORDER_VARS' => $sorting['vars']
			]
		);

		//$this->setStorageValue('GRID_ORDER', $this->modifyGridOrder($result));
		//$this->setStorageValue('GRID_ORDER_VARS', $sorting['vars']);

		unset($found, $field, $order, $sorting, $result);
	}

	/**
	 * @param array $order
	 * @return array
	 */
	protected function modifyGridOrder(array $order)
	{
		return $order;
	}

	/**
	 * @return void
	 */
	protected function initGridFilter()
	{
		if (!$this->isUsedGridFilter())
			return;
		$this->initGridFilterConfig();
		$this->initGridFilterSettings();
		$this->initGridFilterCurrentPreset();
	}

	/**
	 * @return void
	 */
	protected function initGridFilterConfig()
	{
		$this->gridFilterConfig = new UI\Filter\Options($this->getFilterId());
	}

	/**
	 * @return void
	 */
	protected function initGridFilterSettings()
	{
		if (!$this->isUsedGridFilter())
			return;
		$result = [
			'QUICK_SEARCH_FIELD' => null,
			'QUICK_SEARCH_DESCRIPTION' => [
				'FIELD' => null,
				'NAME' => null
			]
		];
		$fields = $this->getGridFilterDefinition();
		if (!empty($fields))
		{
			foreach (array_keys($fields) as $index)
			{
				$row = $fields[$index];
				if (
					(!isset($row['quickSearch']) && !isset($row['quickSearchOnly']))
					|| (isset($row['entity']) && $row['entity'] != 'master')
				)
					continue;

				$result['QUICK_SEARCH_FIELD'] = $row['id'];
				$result['QUICK_SEARCH_DESCRIPTION']['FIELD'] = $row['id'];
				$result['QUICK_SEARCH_DESCRIPTION']['NAME'] = $row['name'];
			}
			unset($index, $row);
		}
		unset($fields);
		$this->fillStorageNode(self::STORAGE_GRID_FILTER, $result);
		unset($fields, $result);
	}

	/**
	 * @return void
	 */
	protected function initGridFilterCurrentPreset()
	{
		if (!$this->isUsedGridFilter())
			return;

		$preset = $this->prepareGridFilterCurrentPreset();
		if (!empty($preset))
		{
			$this->gridFilterConfig->setFilterSettings(
				UI\Filter\Options::TMP_FILTER,
				[
					'name' => '',
					'fields' => $preset
				],
				true,
				false
			);
			$this->gridFilterConfig->save();
		}
		unset($preset);
	}

	/**
	 * @return array
	 */
	protected function prepareGridFilterCurrentPreset()
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected function getDataOrder()
	{
		return $this->getStorageItem(self::STORAGE_GRID, 'GRID_ORDER');
	}

	/**
	 * @return array
	 */
	protected function getDataFields()
	{
		$fields = $this->getStorageItem(self::STORAGE_GRID, 'VISIBLE_COLUMNS_MAP');
		$titleField = $this->getDataTitleField();
		if ($titleField !== '')
			$fields[$titleField] = true;
		unset($titleField);
		return array_keys($fields);
	}

	/**
	 * @return array
	 */
	protected function getDataFilter()
	{
		$result = $this->getInternalFilter();

		$userFilter = $this->getUserFilter();
		if (!empty($userFilter))
			$result = array_merge($userFilter, $result);
		unset($userFilter);

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getDataTitleField()
	{
		return 'NAME';
	}

	/**
	 * @return array
	 */
	protected function getInternalFilter()
	{
		$result = [];
		$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID');
		if ($iblockId > 0)
			$result['IBLOCK_ID'] = $iblockId;

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getUserFilter()
	{
		if (!$this->isUsedGridFilter())
			return [];

		$result = $this->prepareUserFilter();
		return $this->compileUserFilter($result);
	}

	/**
	 * @return array
	 */
	protected function prepareUserFilter()
	{
		if (!$this->isUsedGridFilter())
			return [];

		$fields = $this->getGridFilterDefinition();
		$filterValues = $this->gridFilterConfig->getFilter($fields);
		$filterRows = UI\Filter\Options::getRowsFromFields($filterValues);

		if (empty($filterRows))
			return [];

		$result = [];
		$quickSearchField = $this->getQuickSearchField();
		$checkQuickSearch = (
			$quickSearchField !== null
			&& isset($filterValues['FIND'])
			&& is_string($filterValues['FIND'])
			&& trim($filterValues['FIND']) != ''
		);

		if ($checkQuickSearch)
		{
			$this->addFilterQuickSearchValue($result, $filterValues, $quickSearchField, $fields[$quickSearchField]);
		}

		foreach ($filterRows as $id)
		{
			if (!isset($fields[$id]))
				continue;

			if ($checkQuickSearch && $id == $quickSearchField)
				continue;

			switch ($fields[$id]['type'])
			{
				case "number":
					$this->addFilterNumberValue($result, $filterValues, $id, $fields[$id]);
					break;
				case "date":
					$this->addFilterDateValue($result, $filterValues, $id, $fields[$id]);
					break;
				case "list":
					$this->addFilterListValue($result, $filterValues, $id, $fields[$id]);
					break;
				case "custom_entity":
				case "custom":
					break;
				case "checkbox":
					$this->addFilterCheckboxValue($result, $filterValues, $id, $fields[$id]);
					break;
				case "dest_selector":
					$this->addFilterDestSelectorValue($result, $filterValues, $id, $fields[$id]);
					break;
				/*				case "custom_date":
									break; */
				case "string":
				default:
					$this->addFilterStringValue($result, $filterValues, $id, $fields[$id]);
					break;
			}
		}
		unset($id);
		unset($checkQuickSearch, $quickSearchField);
		unset($filterRows, $filterValues, $fields);

		return $result;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	protected function compileUserFilter(array $filter)
	{
		return (isset($filter['master']) ? $filter['master'] : []);
	}

	/**
	 * @param array $field
	 * @param string $operator
	 * @return string
	 */
	private function getFilterOperator(array $field, $operator)
	{
		$result = '';
		if ($operator === '')
			$operator = 'default';
		if (!empty($field['operators']) && is_array($field['operators']))
		{
			if (isset($field['operators'][$operator]))
				$result = $field['operators'][$operator];
			elseif (isset($field['operators']['default']))
				$result = $field['operators']['default'];
		}
		return $result;
	}

	/**
	 * @param array &$result
	 * @param array $items
	 * @param array $field
	 * @return void
	 */
	private function addFilterItems(array &$result, array $items, array $field)
	{
		if (empty($items))
			return;

		$entity = (isset($field['entity']) ? $field['entity'] : 'master');
		if ($entity !== '')
		{
			if (!isset($result[$entity]))
				$result[$entity] = [];
			$result[$entity] = array_merge($result[$entity], $items);
		}
		unset($entity);
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterQuickSearchValue(array &$result, array $filter, $fieldId, array $field)
	{
		$findValue = trim($filter['FIND']);
		$operator = $this->getFilterOperator($field, 'quickSearch');
		if (is_string($operator))
			$fieldId = $operator.$fieldId;
		unset($operator);
		$this->addFilterItems($result, [$fieldId => $findValue], $field);
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterNumberValue(array &$result, array $filter, $fieldId, array $field)
	{
		$valueTypeIndex = $fieldId.'_numsel';

		if (isset($filter[$valueTypeIndex]) && is_string($filter[$valueTypeIndex]))
		{
			$items = [];

			$minIndex = $fieldId.'_from';
			$maxIndex = $fieldId.'_to';

			$minValue = (isset($filter[$minIndex]) && is_string($filter[$minIndex]) ? trim($filter[$minIndex]) : '');
			$maxValue = (isset($filter[$maxIndex]) && is_string($filter[$maxIndex]) ? trim($filter[$maxIndex]) : '');

			switch ($filter[$valueTypeIndex])
			{
				case 'exact':
					if ($minValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'exact');
						if (is_string($operator))
							$fieldId = $operator.$fieldId;
						unset($operator);
						$items[$fieldId] = $minValue;
					}
					break;
				case 'range':
					if ($minValue !== '' && $maxValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'range');
						if (is_string($operator))
							$fieldId = $operator.$fieldId;
						unset($operator);
						$items[$fieldId] = [$minValue, $maxValue];
					}
					break;
				case 'more':
					if ($minValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'more');
						if (is_string($operator))
							$fieldId = $operator.$fieldId;
						unset($operator);
						$items[$fieldId] = $minValue;
					}
					break;
				case 'less':
					if ($maxValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'less');
						if (is_string($operator))
							$fieldId = $operator.$fieldId;
						unset($operator);
						$items[$fieldId] = $maxValue;
					}
					break;
			}
			unset($maxValue, $minValue, $maxIndex, $minIndex);

			$this->addFilterItems($result, $items, $field);
			unset($items);
		}
		unset($valueTypeIndex);
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterDateValue(array &$result, array $filter, $fieldId, array $field)
	{
		$valueTypeIndex = $fieldId.'_datesel';

		if (isset($filter[$valueTypeIndex]) && is_string($filter[$valueTypeIndex]))
		{
			$items = [];

			$minIndex = $fieldId.'_from';
			$maxIndex = $fieldId.'_to';

			$minValue = (isset($filter[$minIndex]) && is_string($filter[$minIndex]) ? trim($filter[$minIndex]) : '');
			$maxValue = (isset($filter[$maxIndex]) && is_string($filter[$maxIndex]) ? trim($filter[$maxIndex]) : '');

			switch ($filter[$valueTypeIndex])
			{
				case 'EXACT':
					if ($minValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'default');
						if (is_string($operator))
							$fieldId = $operator.$fieldId;
						unset($operator);
						$items[$fieldId] = $minValue;
					}
					break;
				case 'RANGE':
				default:
					if ($minValue !== '' && $maxValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'range');
						if (is_string($operator))
							$fieldId = $operator.$fieldId;
						unset($operator);
						$items[$fieldId] = [$minValue, $maxValue];
					}
					break;
			}
			unset($maxValue, $minValue, $maxIndex, $minIndex);

			$this->addFilterItems($result, $items, $field);
			unset($items);
		}
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterListValue(array &$result, array $filter, $fieldId, array $field)
	{
		$multiple = isset($field['params']['multiple']) && $field['params']['multiple'] == 'Y';
		if (isset($filter[$fieldId]))
		{
			if ($multiple)
				$validRawValue = !empty($filter[$fieldId]) && is_array($filter[$fieldId]);
			else
				$validRawValue = is_string($filter[$fieldId]) || is_int($filter[$fieldId]);
			if ($validRawValue)
			{

				if ($multiple)
				{
					$value = [];
					foreach ($filter[$fieldId] as $item)
					{
						if (isset($field['items'][$item]))
							$value[] = $item;
					}
					unset($item);
					$check = !empty($value);
				}
				else
				{
					$value = $filter[$fieldId];
					$check = isset($field['items'][$value]);
				}
				if ($check)
				{
					$operator = $this->getFilterOperator($field, ($multiple ? 'enum' : 'exact'));
					if (is_string($operator))
						$fieldId = $operator.$fieldId;
					unset($operator);
					$this->addFilterItems($result, [$fieldId => $value], $field);
				}
				unset($check, $value);
			}
			unset($validRawValue);
		}
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterCheckboxValue(array &$result, array $filter, $fieldId, array $field)
	{
		if (isset($filter[$fieldId]))
		{
			$value = $filter[$fieldId];
			if ($value === 'Y' || $value === 'N')
			{
				$operator = $this->getFilterOperator($field, 'exact');
				if (is_string($operator))
					$fieldId = $operator.$fieldId;
				unset($operator);
				$this->addFilterItems($result, [$fieldId => $value], $field);
			}
			unset($value);
		}
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterDestSelectorValue(array &$result, array $filter, $fieldId, array $field)
	{
		if (isset($filter[$fieldId]))
		{
			$multiple = isset($field['params']['multiple']) && $field['params']['multiple'] == 'Y';
			if ($multiple)
				$validRawValue = !empty($filter[$fieldId]) && is_array($filter[$fieldId]);
			else
				$validRawValue = is_string($filter[$fieldId]) && ($filter[$fieldId] !== '');
			if ($validRawValue)
			{
				$operator = $this->getFilterOperator($field, ($multiple ? 'enum' : 'exact'));
				if (is_string($operator))
					$fieldId = $operator.$fieldId;
				unset($operator);
				$this->addFilterItems($result, [$fieldId => $filter[$fieldId]], $field);
			}
			unset($validRawValue);
		}
	}

	/**
	 * @param array &$result
	 * @param array $filter
	 * @param string $fieldId
	 * @param array $field
	 * @return void
	 */
	private function addFilterStringValue(array &$result, array $filter, $fieldId, array $field)
	{
		if (isset($filter[$fieldId]) && is_string($filter[$fieldId]))
		{
			$value = trim($filter[$fieldId]);
			if ($value !== '')
			{
				$operator = $this->getFilterOperator($field, 'default');
				if (is_string($operator))
					$fieldId = $operator.$fieldId;
				unset($operator);
				$this->addFilterItems($result, [$fieldId => $value], $field);
			}
			unset($value);
		}
	}
	/**
	 * @return void
	 */
	protected function getData() {}

	/**
	 * @param \CDBResult $iterator
	 * @return void
	 */
	protected function setImplicitNavigationData(\CDBResult $iterator)
	{
		if (!$this->isUsedImplicitPageNavigation())
			return;

		$navComponentObject = null;
		$navComponentParameters = [];
		if ($this->arParams['BASE_LINK'] !== '')
		{
			$navComponentParameters["BASE_LINK"] = \CHTTP::urlAddParams(
				$this->arParams['BASE_LINK'],
				[],
				['encode' => true]
			);
		}
		$this->navigationString = $iterator->GetPageNavStringEx(
			$navComponentObject,
			$this->getNavigationTitle(),
			'grid',
			true,
			null,
			$navComponentParameters
		);
		$this->navigation->setRecordCount($iterator->NavRecordCount);
		unset($navComponentParameters, $navComponentObject);
	}

	/**
	 * @return string
	 */
	protected function getNavigationTitle()
	{
		return '';
	}

	/**
	 * @return array
	 */
	protected function getGridRows()
	{
		if (!empty($this->rows))
		{
			$returnDataSet = ($this->resultAction['DATA_TYPE'] == self::RESULT_DATA_TYPE_SET);

			$editable = array_fill_keys(array_keys($this->getColumns()), false);

			foreach (array_keys($this->rows) as $index)
			{
				$rawItem = $this->rows[$index];

				$item = [
					'id' => $rawItem['ID'],
					'columns' => $rawItem,
					'editableColumns' => $editable
				];
				if ($returnDataSet)
				{
					$action = $this->getRowAction($rawItem);
					if (!empty($action))
					{
						$item['actions'] = [
							[
								'DEFAULT' => true,
								'TEXT' => Loc::getMessage('ENTITY_SELECTOR_GRID_ACTION_SELECT'),
								'ONCLICK' => $action
							]
						];
					}
					unset($action);
				}

				$this->rows[$index] = $item;
			}
			unset($item, $rawItem, $index);

			unset($editable, $returnDataSet);
		}
		return $this->rows;
	}

	/**
	 * @return array
	 */
	protected function getBinaryDictionary()
	{
		return [
			'Y' => Loc::getMessage('ENTITY_SELECTOR_SELECT_YES'),
			'N' => Loc::getMessage('ENTITY_SELECTOR_SELECT_NO')
		];
	}

	/**
	 * @param array $row
	 * @return string|null
	 */
	protected function getRowAction(array $row)
	{
		$result = null;
		if ($this->resultAction['DATA_TYPE'] != self::RESULT_DATA_TYPE_SET)
			return $result;
		switch ($this->resultAction['TYPE'])
		{
			case self::RESULT_ACTION_TYPE_EVENT:
				break;
			case self::RESULT_ACTION_TYPE_CALLBACK:
				break;
			case self::RESULT_ACTION_TYPE_CLASS_METHOD:
				break;
			case self::RESULT_ACTION_TYPE_SLIDER:
				$set = $this->getSliderResultDataSet($row);
				if (!empty($set))
				{
					$convertedSet = \CUtil::PhpToJSObject($set, false, true, false);
					$result = 'top.BX.SidePanel.Instance.postMessageTop(window, \''.$this->resultAction['NAME'].'\', {filter: '.$convertedSet.'}); top.BX.SidePanel.Instance.getTopSlider().close(true);';
					unset($convertedSet);
				}
				break;
		}
		return $result;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected function getSliderResultDataSet(array $row)
	{
		return [];
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	protected static function isGridFilterRow(array $row)
	{
		return (!isset($row['quickSearchOnly']));
	}
}