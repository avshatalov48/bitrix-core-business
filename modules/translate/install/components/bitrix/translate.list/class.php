<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	return;
}

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;


class TranslateListComponent extends Translate\ComponentBase
{
	public const ACTION_FILE_LIST = 'FILE_LIST';
	public const ACTION_SEARCH_FILE = 'SEARCH_FILE';
	public const ACTION_SEARCH_PHRASE = 'SEARCH_PHRASE';
	public const ACTION_EDIT = 'EDIT';

	/** @var string */
	private $action;

	public const VIEW_MODE_COUNT_PHRASES = 'CountPhrases';
	public const VIEW_MODE_COUNT_FILES = 'CountFiles';
	public const VIEW_MODE_UNTRANSLATED = 'UntranslatedPhrases';
	public const VIEW_MODE_UNTRANSLATED_FILES = 'UntranslatedFiles';
	public const VIEW_MODE_HIDE_EMPTY_FOLDERS = 'HideEmptyFolders';
	public const VIEW_MODE_SHOW_DIFF_LINKS = 'ShowDiffLinks';

	/** @var string */
	private $filterId = 'translate_filter';
	/** @var string */
	private $gridId = 'translate_list';

	/** @var Main\Grid\Options */
	private $gridOptions;

	/** @var Main\UI\Filter\Options */
	private $filterOptions;

	/** @var boolean Request include new filter state. */
	private $filterApplying = false;

	/** @var Main\UI\PageNavigation */
	private $pageNavigation;

	/** @var Translate\Filter */
	private $filter;

	/** @var Translate\Index\PathIndex */
	private $topIndexPath;

	/** @var string[] */
	private $viewMode = array();

	/** @var array */
	private $fileData  = array();

	/** @var array */
	private $dirData = array();

	/** @var array */
	private $indexData = array();

	/** @var int */
	private $totalItemsFound = 0;


	/**
	 * @return void
	 */
	protected function prepareParams()
	{
		parent::prepareParams();

		$paramsIn =& $this->getParams();

		$paramsIn['TAB_ID'] = $this->detectTabId();
		$paramsIn['GRID_ID'] = $this->gridId;
		$paramsIn['FILTER_ID'] = $this->filterId;

		$paramsIn['DIFF_LINKS_LIMIT'] = isset($paramsIn['DIFF_LINKS_LIMIT']) ? (int)$paramsIn['DIFF_LINKS_LIMIT'] : 30;

		// view mode
		$paramsIn['VIEW_MODE'] = $this->detectViewMode();
		$paramsIn['HIDE_EMPTY_FOLDERS'] = in_array(self::VIEW_MODE_HIDE_EMPTY_FOLDERS, $this->viewMode, true);
		$paramsIn['SHOW_DIFF_LINKS'] = in_array(self::VIEW_MODE_SHOW_DIFF_LINKS, $this->viewMode, true);
		$paramsIn['SHOW_COUNT_PHRASES'] = in_array(self::VIEW_MODE_COUNT_PHRASES, $this->viewMode, true);
		$paramsIn['SHOW_COUNT_FILES'] = in_array(self::VIEW_MODE_COUNT_FILES, $this->viewMode, true);
		$paramsIn['SHOW_UNTRANSLATED_PHRASES'] = in_array(self::VIEW_MODE_UNTRANSLATED, $this->viewMode, true);
		$paramsIn['SHOW_UNTRANSLATED_FILES'] = in_array(self::VIEW_MODE_UNTRANSLATED_FILES, $this->viewMode, true);
	}


	/**
	 * @return void
	 */
	public function executeComponent()
	{
		if (!$this->checkModuleAvailability() || !$this->checkPermissionView())
		{
			return;
		}

		$this->prepareParams();
		$paramsIn =& $this->getParams();

		$this->arResult['INIT_FOLDERS'] = Translate\Config::getInitPath();

		$this->arResult['LANGUAGES'] = $this->getLanguages();
		$this->arResult['COMPATIBLE_LANGUAGES'] = $this->getCompatibleLanguages();
		$this->arResult['LANGUAGES_TITLE'] = $this->getLanguagesTitle($this->arResult['LANGUAGES']);

		$this->arResult['FILTER_PRESETS'] = $this->getFilterPresetsDefinition();
		$this->arResult['FILTER_DEFINITION'] = $this->getFilterDefinition();

		// grid
		$this->gridOptions = new Main\Grid\Options($this->gridId, $this->arResult['FILTER_PRESETS']);

		// filter
		$this->filterOptions = new Main\UI\Filter\Options($this->filterId, $this->arResult['FILTER_PRESETS']);

		// languages selected on grid
		$gridLangs = [];
		$gridOption = $this->gridOptions->getCurrentOptions();
		if (!empty($gridOption['columns']))
		{
			$gridColumns = explode(',', $gridOption['columns']);
			foreach ($this->arResult['LANGUAGES'] as $langId)
			{
				if (!empty($gridColumns) && in_array(mb_strtoupper($langId).'_LANG', $gridColumns, true))
				{
					$gridLangs[] = $langId;
				}
			}
			if (!in_array($paramsIn['CURRENT_LANG'], $gridLangs, true))
			{
				array_unshift($gridLangs, $paramsIn['CURRENT_LANG']);
			}
		}
		$this->arResult['GRID_LANGUAGES'] = $gridLangs;


		// Per page navigation
		$navParams = $this->gridOptions->getNavParams();
		$this->pageNavigation = new Main\UI\PageNavigation('page');
		$this->pageNavigation
			->allowAllRecords(false)
			->setPageSize($navParams['nPageSize']);

		if ($this->arResult['IS_AJAX_REQUEST'])
		{
			if (($page = $this->request->get($this->pageNavigation->getId())) !== null)
			{
				$params = explode("-", $page);
				for ($i = 0, $n = count($params); $i < $n; $i += 2)
				{
					$navParams[$params[$i]] = $params[$i + 1];
				}
			}
			if (!empty($navParams['page']) && (int)$navParams['page'] >= 1)
			{
				$this->pageNavigation->setCurrentPage((int)$navParams['page']);
			}
		}
		else
		{
			$this->pageNavigation->initFromUri();
		}

		// init filter
		$this->detectFilter();


		// action
		$this->arResult['ACTION'] = $this->detectAction();

		// path
		$this->detectPath();
		$this->arResult['PATH'] = Translate\IO\Path::replaceLangId($this->path, $paramsIn['CURRENT_LANG']);
		$this->arResult['STARTING_PATH'] = $this->detectStartingPath($this->path);

		if (!$this->arResult['IS_AJAX_REQUEST'])
		{
			$presetId = \Bitrix\Main\UI\Filter\Options::TMP_FILTER;
			$filterFieldsValue = array();
			foreach ($this->filter as $key => $val)
			{
				if (in_array($key, ['tabId', 'FILTER_APPLIED', 'FILTER_ID', 'FIND'], true))
				{
					continue;
				}
				if ($key === 'PATH')
				{
					$key = 'FIND';
					$val = Translate\IO\Path::replaceLangId($this->path, $paramsIn['CURRENT_LANG']);
				}
				if ($key === 'PRESET_ID')
				{
					$presetId = $val;
				}
				$filterFieldsValue[$key] = $val;
			}

			$this->filterOptions->reset();
			$this->filterOptions->setFilterSettings(
				$presetId,
				array(
					'fields' => $filterFieldsValue
				),
				true,
				false
			);
			$this->filterOptions->save();
		}


		$this->arResult['GRID_DATA'] = array();
		$this->arResult['HEADERS'] = array();

		switch ($this->action)
		{
			case self::ACTION_SEARCH_FILE:
				$this->executeSearchFileAction();
				break;

			case self::ACTION_SEARCH_PHRASE:
				$this->executeSearchPhraseAction();
				break;

			case self::ACTION_FILE_LIST:
			default:
				$this->executeFileListAction();
				break;
		}

		if ($this->hasErrors())
		{
			if ($this->isAjaxRequest())
			{
				$this->sendJsonResponse($this->getFirstError());
			}
			else
			{
				$this->includeComponentTemplate(self::TEMPLATE_ERROR);
				return;
			}
		}

		// Sorting order
		$this->arResult['ALLOW_SORT'] = ($this->action !== self::ACTION_FILE_LIST);
		if ($this->arResult['ALLOW_SORT'])
		{
			$this->arResult['SORT'] = $this->getOrder();
		}

		$this->pageNavigation->setRecordCount($this->totalItemsFound);
		$this->arResult['TOTAL_ROWS_COUNT'] = $this->totalItemsFound;
		$this->arResult['CURRENT_PAGE'] = $this->pageNavigation->getCurrentPage();
		$this->arResult['NAV_OBJECT'] = $this->pageNavigation;

		$this->arResult['HEADERS'] = $this->getHeaderDefinition();

		$this->includeComponentTemplate();
	}

	/**
	 * Returns ui grid headers definition.
	 *
	 * @return array
	 */
	private function getHeaderDefinition()
	{
		static $result;
		if (empty($result))
		{
			$paramsIn =& $this->getParams();

			/*
				sort_state
				next_sort_order
				order
				sort_url
				sort
				showname
				original_name
				name
				align
				is_shown
				class
				width
				editable
				prevent_default
				sticked
				sticked_default
			*/

			$result = array();
			$customNames = array();

			$customNames[] = 'TITLE';
			$result[] = array(
				'id' => 'TITLE',
				'name' => ($this->action === self::ACTION_FILE_LIST ?
					Loc::getMessage('TR_LIST_COLUMN_TITLE') : Loc::getMessage('TR_LIST_COLUMN_FILE_NAME')),
				'default' => true,
				'sticked' => true,
				'prevent_default' => false,
				'class' => 'translate-column-title',
				'editable' => false,
				'resizeable' => true,
				'type' => '',
			);

			$customNames[] = 'PATH';
			$result[] = array(
				'id' => 'PATH',
				'name' => Loc::getMessage('TR_LIST_COLUMN_PATH'),
				'default' => true,
				'sticked' => true,
				'prevent_default' => false,
				'class' => 'translate-column-path',
				'editable' => false,
				'resizeable' => true,
				'type' => '',
			);

			if ($this->action !== self::ACTION_SEARCH_FILE)
			{
				$customNames[] = 'PHRASE_CODE';
				$result[] = array(
					'id' => 'PHRASE_CODE',
					'name' => Loc::getMessage('TR_LIST_COLUMN_PHRASE_CODE'),
					'default' => true,
					'sticked' => true,
					'prevent_default' => false,
					'class' => 'translate-column-code',
					'editable' => false,
					'resizeable' => true,
					'type' => '',
				);
			}

			/*
			todo: Revert module assigment

			$customNames[] = 'MODULE_ID';
			$result[] = array(
				'id' => 'MODULE_ID',
				'name' => Loc::getMessage('TR_LIST_COLUMN_MODULE_ID'),
				'sort' => 'MODULE_ID',
				'default' => false,
				'first_order' => 'ASC',
				'prevent_default' => false,
				'class' => 'translate-column-module',
			);
			*/

			/*
			todo: Revert type assigment

			$customNames[] = 'ASSIGNMENT';
			$result[] = array(
				'id' => 'ASSIGNMENT',
				'name' => Loc::getMessage('TR_LIST_COLUMN_ASSIGNMENT'),
				'sort' => 'ASSIGNMENT',
				'default' => false,
				'first_order' => 'ASC',
				'prevent_default' => false,
				'class' => 'translate-column-assignment',
			);
			*/

			$languagesList = $this->getLanguages();

			// move current language to the first position
			unset($languagesList[array_search($paramsIn['CURRENT_LANG'], $languagesList)]);
			array_unshift($languagesList, $paramsIn['CURRENT_LANG']);

			$titles = $this->getLanguagesTitle($languagesList);

			foreach ($languagesList as $langId)
			{
				$columnId = mb_strtoupper($langId).'_LANG';
				$customNames[] = $columnId;
				$result[] = array(
					'id' => $columnId,
					'name' => $langId. ($langId == $paramsIn['CURRENT_LANG'] ? '*' : ''),
					'default' => true,
					'sticked' => true,
					'class' => 'translate-column-lang',
					'title' => $titles[$langId],
					'editable' => false,
					'resizeable' => true,
					'type' => '',
				);
			}

			// switch on sorting
			if ($this->action != self::ACTION_FILE_LIST)
			{
				foreach ($result as &$field)
				{
					$field['sort'] = $field['id'];
					$field['first_order'] = 'asc';
				}
			}

			$gridOption = $this->gridOptions->getCurrentOptions();
			if (!isset($gridOption['columns']))
			{
				$customNames = implode(',', $customNames);
				$this->gridOptions->setColumns($customNames);
			}
		}

		return $result;
	}


	/**
	 * Returns filter fields definitions.
	 *
	 * @return array
	 */
	private function getFilterDefinition()
	{
		static $result;
		if (empty($result))
		{
			$result = array();
			/*
			 	\Bitrix\Main\UI\Filter\FieldAdapter::adapt

				$field['type'] = string|list|date|number|custom|custom_entity|checkbox|custom_date|dest_selector

				$field['allow_years_switcher']
				$field['exclude']
				$field['html']
				$field['id']
				$field['include']
				$field['items']
				$field['lightweight']
				$field['messages']
				$field['name']
				$field['params']
				$field['placeholder']
				$field['style']
				$field['time']
				$field['value']
				$field['valueType']
				$field['params']['multiple']
			*/

			$result['FOLDER_NAME'] = array(
				'id' => 'FOLDER_NAME',
				'name' => Loc::getMessage('TR_LIST_COLUMN_FOLDER_NAME'),
				'type' => 'string',
			);

			$result['FILE_NAME'] = array(
				'id' => 'FILE_NAME',
				'name' => Loc::getMessage('TR_LIST_COLUMN_FILE_NAME'),
				'type' => 'string',
				'default' => true,
			);

			$result['PHRASE_CODE'] = array(
				'id' => 'PHRASE_CODE',
				'name' => Loc::getMessage('TR_LIST_COLUMN_PHRASE_CODE'),
				'type' => 'string',
				'default' => true,
			);
			$result['CODE_ENTRY'] = array(
				'id' => 'CODE_ENTRY',
				'name' => Loc::getMessage('TR_SEARCH_CODE_ENTRY'),
				'type' => 'list',
				'params' => array('multiple' => 'Y'),
				'items' => array(
					Index\PhraseIndexSearch::SEARCH_METHOD_CASE_SENSITIVE => Loc::getMessage('TR_SEARCH_METHOD_CASE_SENSITIVE'),
					Index\PhraseIndexSearch::SEARCH_METHOD_EQUAL => Loc::getMessage('TR_SEARCH_METHOD_EQUAL'),
					Index\PhraseIndexSearch::SEARCH_METHOD_START_WITH => Loc::getMessage('TR_SEARCH_METHOD_START_WITH'),
					Index\PhraseIndexSearch::SEARCH_METHOD_END_WITH => Loc::getMessage('TR_SEARCH_METHOD_END_WITH'),
				),
				'group_values' => array(
					array(
						Index\PhraseIndexSearch::SEARCH_METHOD_EQUAL,
						Index\PhraseIndexSearch::SEARCH_METHOD_START_WITH,
						Index\PhraseIndexSearch::SEARCH_METHOD_END_WITH,
					),
				),
				'default' => true,
			);

			$result['INCLUDE_PHRASE_CODES'] = array(
				'id' => 'INCLUDE_PHRASE_CODES',
				'name' => Loc::getMessage('TR_LIST_COLUMN_INCLUDE_PHRASE_CODES'),
				'type' => 'textarea',
				'default' => false,
			);
			$result['EXCLUDE_PHRASE_CODES'] = array(
				'id' => 'EXCLUDE_PHRASE_CODES',
				'name' => Loc::getMessage('TR_LIST_COLUMN_EXCLUDE_PHRASE_CODES'),
				'type' => 'textarea',
				'default' => false,
			);

			$result['PHRASE_TEXT'] = array(
				'id' => 'PHRASE_TEXT',
				'name' => Loc::getMessage('TR_LIST_COLUMN_PHRASE_TEXT'),
				'type' => 'string',
				'default' => true,
			);
			$result['LANGUAGE_ID'] = array(
				'id' => 'LANGUAGE_ID',
				'name' => Loc::getMessage('TR_LIST_COLUMN_LANGUAGE_ID'),
				'type' => 'list',
				'items' => $this->getLanguagesTitle($this->getLanguages()),
				'default' => true,
			);

			$result['PHRASE_ENTRY'] = array(
				'id' => 'PHRASE_ENTRY',
				'name' => Loc::getMessage('TR_SEARCH_PHRASE_ENTRY'),
				'type' => 'list',
				'params' => array('multiple' => 'Y'),
				'items' => array(
					Index\PhraseIndexSearch::SEARCH_METHOD_CASE_SENSITIVE => Loc::getMessage('TR_SEARCH_METHOD_CASE_SENSITIVE'),
					Index\PhraseIndexSearch::SEARCH_METHOD_EXACT_WORD => Loc::getMessage('TR_SEARCH_METHOD_EXACT_WORD'),
					Index\PhraseIndexSearch::SEARCH_METHOD_EQUAL => Loc::getMessage('TR_SEARCH_METHOD_EQUAL_PHRASE'),
					Index\PhraseIndexSearch::SEARCH_METHOD_START_WITH => Loc::getMessage('TR_SEARCH_METHOD_START_WITH'),
					Index\PhraseIndexSearch::SEARCH_METHOD_END_WITH => Loc::getMessage('TR_SEARCH_METHOD_END_WITH'),
				),
				'group_values' => array(
					array(
						Index\PhraseIndexSearch::SEARCH_METHOD_START_WITH,
						Index\PhraseIndexSearch::SEARCH_METHOD_END_WITH,
					),
					array(
						Index\PhraseIndexSearch::SEARCH_METHOD_EXACT_WORD,
						Index\PhraseIndexSearch::SEARCH_METHOD_EQUAL,
					)
				),
				'default' => true,
			);

			$result['INCLUDE_PATHS'] = array(
				'id' => 'INCLUDE_PATHS',
				'name' => Loc::getMessage('TR_LIST_COLUMN_INCLUDE_PATHS'),
				'type' => 'textarea',
				'default' => false,
			);
			$result['EXCLUDE_PATHS'] = array(
				'id' => 'EXCLUDE_PATHS',
				'name' => Loc::getMessage('TR_LIST_COLUMN_EXCLUDE_PATHS'),
				'type' => 'textarea',
				'default' => false,
			);

			/*
			todo: Revert type assigment

			$items = array();
			foreach (\Bitrix\Translate\ASSIGNMENT_TYPES as $assignmentId)
			{
				$items[$assignmentId] = $this->getAssignmentTitle($assignmentId);
			}
			asort($items);
			$result['ASSIGNMENT'] = array(
				'id' => 'ASSIGNMENT',
				'name' => Loc::getMessage('TR_LIST_COLUMN_ASSIGNMENT'),
				'type' => 'list',
				'items' => $items,
				//'default' => true,
				'params' => array('multiple' => 'Y'),
			);
			*/

			/*
			todo: Revert module assigment

			$items = array();
			foreach ($this->getModuleList() as $moduleId)
			{
				$items[$moduleId] = $this->getModuleTitle($moduleId);
				if ($items[$moduleId] != $moduleId)
				{
					$items[$moduleId] = $this->getModuleTitle($moduleId).' ('.$moduleId.')';
				}
			}
			$result['MODULE_ID'] = array(
				'id' => 'MODULE_ID',
				'name' => Loc::getMessage('TR_LIST_COLUMN_MODULE_ID'),
				'type' => 'list',
				'items' => $items,
				//'default' => true,
				'params' => array('multiple' => 'Y'),
			);
			*/
		}

		return $result;
	}

	/**
	 * Returns filter presets definitions.
	 *
	 * @return array
	 */
	private function getFilterPresetsDefinition()
	{
		static $result;

		if (empty($result))
		{
			$result = array();
			/*
				fields
				name
				disallow_for_all
				default
			*/
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getOrder($defaultSort = array('TITLE' => 'asc'), $aliases = array())
	{
		if ($this->gridOptions instanceof Main\Grid\Options)
		{
			$sorting = $this->gridOptions->getSorting(array('sort' => $defaultSort));

			$by = key($sorting['sort']);
			if (isset($aliases[$by]))
			{
				$by = $aliases[$by];
			}
			$order = mb_strtolower(current($sorting['sort'])) === 'asc' ? 'asc' : 'desc';

			$list = array();
			foreach ($this->getHeaderDefinition() as $column)
			{
				if (!isset($column['sort']) || !$column['sort'])
				{
					continue;
				}

				if (isset($aliases[$column['sort']]))
				{
					$list[] = $aliases[$column['sort']];
				}
				else
				{
					$list[] = $column['sort'];
				}
			}

			if (in_array($by, $list, true))
			{
				return array($by => $order);
			}
		}

		return $defaultSort;
	}



	/**
	 * Returns filter object.
	 *
	 * @return Translate\Filter
	 */
	private function getFilter($reset = false)
	{
		if (empty($this->filter) || $reset)
		{
			$this->filter = new Translate\Filter();
		}

		return $this->filter;
	}

	/**
	 * Returns established filter.
	 *
	 * @return Translate\Filter
	 */
	private function detectFilter()
	{
		$this->getFilter();
		$this->filter->restore($this->tabId);

		if (
			$this->request->isPost() &&
			$this->request->get('apply_filter') === 'Y'
		)
		{
			$filterSource = $this->filterOptions->getFilter($this->getFilterDefinition());

			if ($filterSource['FILTER_APPLIED'] === true)
			{
				$this->filterApplying = true;

				$this->getFilter(true);
				$this->filter->tabId = $this->tabId;

				foreach ($filterSource as $key => $value)
				{
					$this->filter[$key] = $value;
				}
				if (!empty($filterSource['FIND']) && !preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $filterSource['FIND']))
				{
					$path = Translate\IO\Path::normalize($filterSource['FIND']);
					if (Translate\Permission::isAllowPath($path))
					{
						$this->filter['PATH'] = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');
					}
				}
			}

			// save filter
			$this->filter->store();
		}

		return $this->filter;
	}


	/**
	 * Action actual dir/file date from disk.
	 *
	 * @return void
	 */
	private function executeFileListAction()
	{
		$paramsIn =& $this->getParams();

		if ($paramsIn['SET_TITLE'])
		{
			$this->getApplication()->setTitle(Loc::getMessage('TR_LIST_TITLE'));
		}

		$enabledLanguages = !empty($this->arResult['GRID_LANGUAGES']) ? $this->arResult['GRID_LANGUAGES'] : $this->arResult['LANGUAGES'];
		$languageUpperKeys = array_combine($enabledLanguages, array_map('mb_strtoupper', $enabledLanguages));

		// go up
		if (preg_match("|.+/lang/#LANG_ID#$|", $this->path))
		{
			$parentPath = mb_substr($this->path, 0, mb_strrpos($this->path, '/lang/#LANG_ID#'));
		}
		else
		{
			$parentPath = mb_substr($this->path, 0, mb_strrpos($this->path, '/'));
		}
		if (mb_strlen($parentPath) > 1)
		{
			$this->arResult['GRID_DATA'][$parentPath] = array(
				'depth' => 0,
				'editable' => false,
				'draggable' => false,
				'expand' => false,
				'not_count' => true,
				'columns' => array(
					'IS_UP' => true,
					'IS_DIR' => false,
					'IS_FILE' => false,
					'PATH' => $parentPath,
				),
				'attrs' => array(
					'data-path' => htmlspecialcharsbx($parentPath),
				),
			);
		}

		// group action
		$this->arResult['GROUP_ACTIONS'] = $this->getGridGroupAction(self::ACTION_FILE_LIST);


		// load actual data from disk files
		$this->totalItemsFound = $this->loadActualFileData();

		// Per page navigation
		$entityPos = 0;
		$pageSize = $this->pageNavigation->getPageSize();
		$pageOffset = $this->pageNavigation->getOffset();

		// view mode
		$showDiffLinks = $paramsIn['SHOW_DIFF_LINKS'];
		$showCountPhrases = $paramsIn['SHOW_COUNT_PHRASES'];
		$showCountFiles = $paramsIn['SHOW_COUNT_FILES'];
		$showUntranslatedPhrases = $paramsIn['SHOW_UNTRANSLATED_PHRASES'];
		$showUntranslatedFiles = $paramsIn['SHOW_UNTRANSLATED_FILES'];
		$hideEmptyFolders = $paramsIn['HIDE_EMPTY_FOLDERS'];

		// top indexed folder
		$this->arResult['IS_INDEXED'] = false;
		$topIndexPath = $this->detectTopIndexPath();
		if ($topIndexPath instanceof Translate\Index\PathIndex)
		{
			$this->arResult['IS_INDEXED'] = $topIndexPath->getIndexed();
		}

		if ($hideEmptyFolders)
		{
			// to hide empty folder load index before
			$this->loadIndexFileData([], $showDiffLinks);
		}

		// folders data
		$folderPathList = array();
		if (count($this->dirData) > 0)
		{
			foreach ($this->dirData as $pathId => &$row)
			{
				// check if folder exists in index
				if ($hideEmptyFolders && !isset($this->indexData[$pathId]))
				{
					continue;
				}

				// Per page navigation
				$withinPageWindow = ($entityPos >= $pageOffset && $entityPos < ($pageOffset + $pageSize));
				$entityPos ++;
				if (!$withinPageWindow)
				{
					continue;
				}

				$this->arResult['GRID_DATA'][$pathId] = $row;
				$folderPathList[] = $pathId;
			}
			unset($pathId, $row);
		}

		if (count($this->fileData) > 0)
		{
			foreach ($this->fileData as $pathId => &$row)
			{
				// Per page navigation
				$withinPageWindow = ($entityPos >= $pageOffset && $entityPos < ($pageOffset + $pageSize));
				$entityPos ++;
				if (!$withinPageWindow)
				{
					continue;
				}

				$settings = !empty($row['settings']) ? $row['settings'] : array();
				$indexData = !empty($row['index']) ? $row['index'] : array();
				$ethalon = !empty($indexData[$paramsIn['CURRENT_LANG']]) ? $indexData[$paramsIn['CURRENT_LANG']] : 0;

				foreach ($languageUpperKeys as $langId => $langUpper)
				{
					$isObligatory = true;
					if (!empty($settings['languages']))
					{
						$isObligatory = in_array($langId, $settings['languages'], true);
					}

					$columnId = "{$langUpper}_LANG";
					$columnExcess = "{$langUpper}_EXCESS";
					$columnDeficiency = "{$langUpper}_DEFICIENCY";
					$indexExcess = "{$langId}_excess";
					$indexDeficiency = "{$langId}_deficiency";

					$count = !empty($indexData[$langId]) ? $indexData[$langId] : 0;
					$excess = isset($indexData[$indexExcess]) ? $indexData[$indexExcess] : 0;
					$deficiency = isset($indexData[$indexDeficiency]) ?  $indexData[$indexDeficiency] : 0;

					if ($isObligatory && $ethalon > 0)
					{
						$deficiency = $count > 0 ? $deficiency : $ethalon;
					}
					elseif ($count > 0)
					{
						$excess = $count;
						$deficiency = 0;
					}

					$columns = array(
						$columnId => $count,
						$columnExcess => $excess,
						$columnDeficiency => $deficiency,
					);

					$row['columns'] = array_merge($row['columns'], $columns);
				}

				$this->arResult['GRID_DATA'][$pathId] = $row;
			}
			unset($pathId, $row);
		}

		$this->totalItemsFound = $entityPos;

		// index data
		if (!$hideEmptyFolders && count($folderPathList) > 0)
		{
			$this->loadIndexFileData($folderPathList, $showDiffLinks);
		}
		if (count($this->indexData) > 0)
		{
			// append data from index
			foreach ($this->indexData as $pathId => $index)
			{
				if (!isset($this->arResult['GRID_DATA'][$pathId]))
				{
					continue;
				}

				$this->arResult['GRID_DATA'][$pathId]['index'] = $index;

				foreach ($languageUpperKeys as $langId => $langUpper)
				{
					$indexData = !empty($index[$langId]) ? $index[$langId] : array();

					$columnId = "{$langUpper}_LANG";
					$columnExcess = "{$langUpper}_EXCESS";
					$columnDeficiency = "{$langUpper}_DEFICIENCY";

					if ($showCountFiles || $showUntranslatedFiles)
					{
						$columns = array(
							$columnId => $indexData['file_count'],
							$columnExcess => $indexData['file_excess'],
							$columnDeficiency => $indexData['file_deficiency'],
						);
					}
					elseif ($showCountPhrases || $showUntranslatedPhrases)
					{
						$columns = array(
							$columnId => $indexData['phrase_count'],
							$columnExcess => $indexData['phrase_excess'],
							$columnDeficiency => $indexData['phrase_deficiency'],
						);
					}

					$this->arResult['GRID_DATA'][$pathId]['columns'] =
						array_merge($this->arResult['GRID_DATA'][$pathId]['columns'], $columns);
				}
			}
		}
	}


	/**
	 * Searches file by index.
	 *
	 * @return void
	 */
	private function executeSearchFileAction()
	{
		$paramsIn =& $this->getParams();

		if ($paramsIn['SET_TITLE'])
		{
			$this->getApplication()->setTitle(Loc::getMessage('TR_LIST_SEARCH'));
		}

		$select = array('PATH_ID', 'PATH', 'IS_LANG', 'IS_DIR', 'TITLE');

		$enabledLanguages = !empty($this->arResult['GRID_LANGUAGES']) ? $this->arResult['GRID_LANGUAGES'] : $this->arResult['LANGUAGES'];
		$languageUpperKeys = array_combine($enabledLanguages, array_map('mb_strtoupper', $enabledLanguages));
		foreach ($languageUpperKeys as $langId => $langUpper)
		{
			$alias = "{$langUpper}_LANG";
			$select[] = $alias;
		}
		unset($langId, $langUpper, $alias);

		try
		{
			/** @var Main\ORM\Query\Result $cursor */
			$cursor = Index\FileIndexSearch::getList([
				'select' => $select,
				'filter' => $this->getFilter(),
				'order' => $this->getOrder(),
				'offset' => $this->pageNavigation->getOffset(),
				'limit' => $this->pageNavigation->getLimit(),
				'count_total' => true,
			]);

			$this->totalItemsFound = $cursor->getCount();

			if ($this->totalItemsFound > 0)
			{
				$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
				foreach ($cursor as $row)
				{
					$pathId = $row['PATH'];

					$entry = array(
						'index' => array(),
						'depth' => 0,
						'editable' => true,
						'draggable' => false,
						'expand' => false,
						'not_count' => false,
						'columns' => array(
							'IS_UP' => false,
							'IS_DIR' => ($row['IS_DIR'] == 'Y'),
							'IS_LANG' => ($row['IS_LANG'] == 'Y'),
							'IS_FILE' => ($row['IS_DIR'] == 'N'),
							'TITLE' => $row['TITLE'],
							'PATH' => $pathId,
						),
						'attrs' => array(
							'data-path' => htmlspecialcharsbx($pathId),
						),
					);
					foreach ($languageUpperKeys as $langId => $langUpper)
					{
						$columnId = "{$langUpper}_LANG";
						$entry['columns'][$columnId] = $row[$columnId];
						$entry['index'][$langId] = $row[$columnId];
					}
					if ($useTranslationRepository)
					{
						$entry['columns']['IS_EXIST'] = ($row['IS_EXIST'] == 1);
					}
					else
					{
						$entry['columns']['IS_EXIST'] = null;
					}

					if (isset($this->fileData[$pathId]))
					{
						$this->fileData[$pathId]['columns'] = array_merge($this->fileData[$pathId]['columns'], $entry['columns']);
					}
					else
					{
						$this->fileData[$pathId] = $entry;
					}
				}


				foreach ($this->fileData as $pathId => $row)
				{
					$indexData = !empty($row['index']) ? $row['index'] : array();

					foreach ($languageUpperKeys as $langId => $langUpper)
					{
						$columnId = "{$langUpper}_LANG";
						$columnExcess = "{$langUpper}_EXCESS";
						$columnDeficiency = "{$langUpper}_DEFICIENCY";

						$ethalon = !empty($indexData[$paramsIn['CURRENT_LANG']]) ? $indexData[$paramsIn['CURRENT_LANG']] : 0;
						$count = !empty($indexData[$langId]) ? $indexData[$langId] : 0;
						$diff = $count - $ethalon;

						$columns = array(
							$columnId => $count,
							$columnExcess => ($diff > 0 ? $diff : 0),
							$columnDeficiency => ($diff < 0 ? abs($diff) : 0),
						);

						$this->fileData[$pathId]['columns'] = array_merge($this->fileData[$pathId]['columns'], $columns);
					}
				}
			}

			$this->arResult['GRID_DATA'] = $this->fileData;

			// group action
			$this->arResult['GROUP_ACTIONS'] = $this->getGridGroupAction(self::ACTION_SEARCH_FILE);

		}
		catch (Main\SystemException $exception)
		{
			$this->addError(new Error($exception->getMessage(), $exception->getCode()));
		}
	}


	/**
	 * Searches phrase by index.
	 *
	 * @return void
	 */
	private function executeSearchPhraseAction()
	{
		$paramsIn =& $this->getParams();

		if ($paramsIn['SET_TITLE'])
		{
			$this->getApplication()->setTitle(Loc::getMessage('TR_LIST_SEARCH'));
		}

		$select = array('PATH_ID', 'PHRASE_CODE', 'FILE_PATH', 'TITLE');

		if (!empty($this->filter['PHRASE_CODE']))
		{
			$this->arResult['HIGHLIGHT_SEARCHED_CODE'] = true;
			$this->arResult['CODE_SEARCH'] = $this->filter['PHRASE_CODE'];
			$this->arResult['CODE_SEARCH_METHOD'] = !empty($this->filter['CODE_ENTRY']) ? $this->filter['CODE_ENTRY'] : [];
			$this->arResult['CODE_SEARCH_CASE'] =
				in_array(Index\PhraseIndexSearch::SEARCH_METHOD_CASE_SENSITIVE, $this->arResult['CODE_SEARCH_METHOD'], true);
		}

		$enabledLanguages = !empty($this->arResult['GRID_LANGUAGES']) ? $this->arResult['GRID_LANGUAGES'] : $this->arResult['LANGUAGES'];
		$languageUpperKeys = array_combine($enabledLanguages, array_map('mb_strtoupper', $enabledLanguages));
		foreach ($languageUpperKeys as $langId => $langUpper)
		{
			$alias = "{$langUpper}_LANG";
			$select[] = $alias;

			if (!empty($this->filter['PHRASE_TEXT']) && ($langId === $this->filter['LANGUAGE_ID']))
			{
				$this->arResult['HIGHLIGHT_SEARCHED_PHRASE'] = true;
				$this->arResult['PHRASE_SEARCH'] = $this->filter['PHRASE_TEXT'];
				$this->arResult['PHRASE_SEARCH_LANGUAGE_ID'] = $langId;
				$this->arResult['PHRASE_SEARCH_METHOD'] = !empty($this->filter['PHRASE_ENTRY']) ? $this->filter['PHRASE_ENTRY'] : [];
				$this->arResult['PHRASE_SEARCH_CASE'] =
					in_array(Index\PhraseIndexSearch::SEARCH_METHOD_CASE_SENSITIVE, $this->arResult['PHRASE_SEARCH_METHOD'], true);
			}
		}
		unset($langId, $langUpper, $alias);

		try
		{
			/** @var Main\ORM\Query\Result $cursor */
			$cursor = Index\PhraseIndexSearch::getList([
				'select' => $select,
				'filter' => $this->getFilter(),
				'order' => $this->getOrder(['TITLE' => 'asc'], ['PATH' => 'FILE_PATH']),
				'offset' => $this->pageNavigation->getOffset(),
				'limit' => $this->pageNavigation->getLimit(),
				'count_total' => true,
			]);

			$this->totalItemsFound = $cursor->getCount();

			if ($this->totalItemsFound > 0)
			{
				$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
				while ($row = $cursor->fetchRaw())
				{
					$inx = $row['PATH_ID'].':'.$row['PHRASE_CODE'];

					$entry = array(
						'depth' => 0,
						'editable' => true,
						'draggable' => false,
						'expand' => false,
						'not_count' => false,
						'columns' => array(
							'IS_FILE' => true,
							'TITLE' => $row['TITLE'],
							'PHRASE_CODE' => $row['PHRASE_CODE'],
							'PATH' => $row['FILE_PATH'],
						),
						'attrs' => array(
							'data-path' => htmlspecialcharsbx($row['FILE_PATH']),
							'data-code' => htmlspecialcharsbx($row['PHRASE_CODE']),
						),
					);
					foreach ($languageUpperKeys as $langId => $langUpper)
					{
						$columnId = "{$langUpper}_LANG";

						if (!isset($row[$columnId]) || !is_string($row[$columnId]) || (empty($row[$columnId]) && $row[$columnId] !== '0'))
						{
							continue;
						}

						$isCompatible = in_array($langId, $this->arResult['COMPATIBLE_LANGUAGES'], true);

						if (!$isCompatible)
						{
							$entry['columns'][$columnId] =
								'<span title="'. Loc::getMessage('TR_UNCOMPATIBLE_ENCODING'). '">'.
								Translate\Text\StringHelper::htmlSpecialChars($row[$columnId]).
								'</span>';
						}
						else
						{
							$entry['columns'][$columnId] =
								Translate\Text\StringHelper::htmlSpecialChars($row[$columnId]);
						}

						// suppose there is a problem with collation
						if (Translate\Text\StringHelper::getPosition($row[$columnId], '?') !== false)
						{
							$fileIndex = Translate\Index\FileIndex::wakeUp(['ID' => $row["{$langUpper}_FILE_ID"]]);
							$fileIndex->fill();
							$langFile = Translate\File::instantiateByIndex($fileIndex);
							if ($langFile->load())
							{
								$phrase = $langFile[$row['PHRASE_CODE']];
								if (!empty($phrase) && $phrase != $row[$columnId])
								{
									if (!$isCompatible)
									{
										$entry['columns'][$columnId] =
											'<span title="'. Loc::getMessage('TR_UNCOMPATIBLE_ENCODING'). '">'.
											Translate\Text\StringHelper::htmlSpecialChars($langFile[$row['PHRASE_CODE']]).
											'</span>';
									}
									else
									{
										$entry['columns'][$columnId] =
											Translate\Text\StringHelper::htmlSpecialChars($langFile[$row['PHRASE_CODE']]);
									}
								}
							}
						}
					}
					if ($useTranslationRepository)
					{
						$entry['columns']['IS_EXIST'] = ($row['IS_EXIST'] == 1);
					}
					else
					{
						$entry['columns']['IS_EXIST'] = null;
					}

					if (isset($this->fileData[$inx]))
					{
						$this->fileData[$inx]['columns'] = array_merge($this->fileData[$inx]['columns'], $entry['columns']);
					}
					else
					{
						$this->fileData[$inx] = $entry;
					}
				}
			}

			$this->arResult['GRID_DATA'] = $this->fileData;

			// group action
			$this->arResult['GROUP_ACTIONS'] = $this->getGridGroupAction(self::ACTION_SEARCH_PHRASE);
		}
		catch (Main\SystemException $exception)
		{
			$this->addError(new Error($exception->getMessage(), $exception->getCode()));
		}
	}



	/**
	 *  Restores current view mode.
	 *
	 * @return string[]
	 */
	private function detectViewMode()
	{
		if ($this->request->get('viewMode') !== null)
		{
			$viewMode = $this->request->get('viewMode');
		}
		else
		{
			$viewMode = \CUserOptions::getOption('translate', 'list_mode', '');
		}

		if (!empty($viewMode))
		{
			$viewMode = explode(',', $viewMode);
			$this->viewMode = array_intersect($viewMode, array(
				self::VIEW_MODE_COUNT_PHRASES,
				self::VIEW_MODE_COUNT_FILES,
				self::VIEW_MODE_UNTRANSLATED,
				self::VIEW_MODE_UNTRANSLATED_FILES,
				self::VIEW_MODE_HIDE_EMPTY_FOLDERS,
				self::VIEW_MODE_SHOW_DIFF_LINKS,
			));

			\CUserOptions::setOption('translate', 'list_mode', implode(',', $this->viewMode));
		}
		if (empty($this->viewMode))
		{
			$this->viewMode = array(self::VIEW_MODE_COUNT_PHRASES);
		}

		return $this->viewMode;
	}


	/**
	 *  Finds requested path from.
	 *
	 * @return string
	 */
	private function detectPath($inpName = 'path')
	{
		$path = null;

		// from filter
		$path1 = $this->filter['PATH'];

		// from request
		$path2 = $this->request->get($inpName);

		if ($this->filterApplying && !empty($path1))
		{
			$path = $path1;
		}
		elseif ($this->request->get('grid_action') === 'pagination' && !empty($path1))
		{
			$path = $path1;
		}
		elseif (!empty($path2))
		{
			$path = $path2;
		}

		if (!empty($path) && !preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
		{
			$path = '/'. trim($path, '/.\\');
			$path = Translate\IO\Path::normalize($path);
			if (Translate\Permission::isAllowPath($path))
			{
				$path = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');
				$this->path = $path;

				// update filter
				$this->filter['PATH'] = $path;
				$this->filter->store();
			}
		}

		if (empty($this->path))
		{
			$this->path = $this->detectStartingPath();
		}

		return $this->path;
	}


	/**
	 *  Finds top folder for request.
	 *
	 * @return Translate\Index\PathIndex|null
	 */
	private function detectTopIndexPath()
	{
		if (empty($this->topIndexPath) && !empty($this->path))
		{
			$this->topIndexPath = Translate\Index\PathIndex::loadByPath($this->path);
		}

		return $this->topIndexPath;
	}

	/**
	 * Loads actual data from disk files. Initializes $this->dirData and $this->fileData.
	 *
	 * @return int
	 */
	private function loadActualFileData()
	{
		$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');
		$paramsIn =& $this->getParams();

		$enabledLanguages = $this->getLanguages();
		$translationLanguages = [];
		if (Localization\Translation::useTranslationRepository())
		{
			$translationLanguages = array_intersect(
				Translate\Config::getTranslationRepositoryLanguages(),
				$enabledLanguages
			);
		}

		$languageList = [];
		foreach ($enabledLanguages as $langId)
		{
			if (!empty($this->arResult['GRID_LANGUAGES']) && !in_array($langId, $this->arResult['GRID_LANGUAGES'], true))
			{
				continue;
			}

			if (in_array($langId, $translationLanguages, true))
			{
				$languageList[] = $langId;
			}
			else
			{
				array_unshift($languageList, $langId);
			}
		}


		$topFolder = new Main\IO\Directory(Translate\IO\Path::tidy($documentRoot.'/'.$this->path.'/'));
		$isTopLang =  ($topFolder->getName() == 'lang') || Translate\IO\Path::isLangDir($topFolder->getPath());

		// settings
		if ($isTopLang)
		{
			if ($langSettings = Translate\Settings::instantiateByPath($topFolder->getPath()))
			{
				if (!$langSettings->isExists() || !$langSettings->load())
				{
					unset($langSettings);
				}
			}
		}

		$nonexistentList = array();
		$mergeChildrenList = function(&$childrenList1, $childrenList2, $langId) use (&$nonexistentList, $translationLanguages)
		{
			$collectNonexistentList = in_array($langId, $translationLanguages, true);
			foreach ($childrenList2 as $childPath)
			{
				$name = basename($childPath);
				if (in_array($name, Translate\IGNORE_FS_NAMES, true))
				{
					continue;
				}
				if ($collectNonexistentList)
				{
					if (!isset($childrenList1[$name]))
					{
						$nonexistentList[$name] = true;
					}
				}
				if (!isset($childrenList1[$name]))
				{
					$childrenList1[$name] = [];
				}
				$childrenList1[$name][$langId] = $childPath;
			}
		};

		/**
		 * @return \Generator|array
		 */
		$iterateDirectory =
			function (
				$topFullPath,
				$topRelPath,
				$isTopLang = false
			)
			use (
				/** @var Translate\Settings */
				$langSettings,
				&$mergeChildrenList,
				$languageList,
				&$nonexistentList,
				$paramsIn
			)
			{
				$topLangId = $paramsIn['CURRENT_LANG'];

				if ($isTopLang)
				{
					if (basename($topFullPath) == 'lang')
					{
						$topFullPath = Translate\IO\Path::tidy($topFullPath.'/'.$topLangId);
						$topRelPath .= '/#LANG_ID#';
					}
					else
					{
						$topFullPath = Translate\IO\Path::replaceLangId($topFullPath, $topLangId);
					}
				}

				$childrenList = array();
				$prevFullPath = '';
				foreach ($languageList as $langId)
				{
					$trFullPath = Translate\IO\Path::replaceLangId($topFullPath, $langId);
					$trFullPath = Localization\Translation::convertLangPath($trFullPath, $langId);

					if ($prevFullPath != $trFullPath)
					{
						$mergeChildrenList($childrenList, Translate\IO\FileSystemHelper::getFolderList($trFullPath), $langId);
						$prevFullPath = $trFullPath;
					}
				}
				unset($langId, $prevFullPath, $trFullPath);

				if (!empty($childrenList))
				{
					$ignoreDev = implode('|', Translate\IGNORE_MODULE_NAMES);
					foreach ($childrenList as $name => $children)
					{
						$relPath = $topRelPath. '/'. $name;

						if (in_array($relPath, Translate\IGNORE_BX_NAMES, true))
						{
							continue;
						}

						// /bitrix/modules/[smth]/dev/
						if (preg_match("#^bitrix/modules/[^/]+/({$ignoreDev})$#", trim($relPath, '/')))
						{
							continue;
						}

						if ($isTopLang && in_array($name, Translate\IGNORE_LANG_NAMES, true))
						{
							continue;
						}

						$entry = array(
							'depth' => 0,
							'editable' => true,
							'draggable' => false,
							'expand' => false,
							'not_count' => false,
							'columns' => array(
								'IS_DIR' => true,
								'IS_UP' => false,
								'IS_FILE' => false,
								'IS_LANG' => $isTopLang,
								'IS_EXIST' => (isset($nonexistentList[$name]) !== true),
								'TITLE' => $name,
								'PATH' => $relPath,
							),
							'attrs' => array(
								'data-path' => htmlspecialcharsbx($relPath),
							),
						);

						// settings
						if ($langSettings instanceof Translate\Settings)
						{
							$entry['settings'] = $langSettings->getOptions($relPath);
						}

						yield $entry;
					}
				}

				if ($isTopLang === true)
				{
					$childrenList = array();
					$prevFullPath = '';
					foreach ($languageList as $langId)
					{
						$trFullPath = Translate\IO\Path::replaceLangId($topFullPath, $langId);
						$trFullPath = Localization\Translation::convertLangPath($trFullPath, $langId);

						if ($prevFullPath != $trFullPath)
						{
							$mergeChildrenList($childrenList, Translate\IO\FileSystemHelper::getFileList($trFullPath), $langId);
							$prevFullPath = $trFullPath;
						}
					}
					if (!empty($childrenList))
					{
						foreach ($childrenList as $name => $children)
						{
							$relPath = $topRelPath.'/'.$name;

							$entry = array(
								'editable' => true,
								'depth' => 0,
								'draggable' => false,
								'expand' => false,
								'not_count' => false,
								'columns' => array(
									'IS_FILE' => true,
									'IS_UP' => false,
									'IS_DIR' => false,
									'IS_EXIST' => (isset($nonexistentList[$name]) !== true),
									'TITLE' => $name,
									'PATH' => $relPath,
								),
								'attrs' => array(
									'data-path' => htmlspecialcharsbx($relPath),
								),
							);

							// settings
							if ($langSettings instanceof Translate\Settings)
							{
								foreach ($children as $langId => $childPath)
								{
									$entry['settings'] = $langSettings->getOptions($childPath);
									break;
								}
							}

							$ethalonFile = null;
							if (isset($children[$paramsIn['CURRENT_LANG']]))
							{
								$ethalonFile = Translate\File::instantiateByPath($children[$paramsIn['CURRENT_LANG']]);
								$ethalonFile->load();
							}

							$index = [];
							foreach ($children as $langId => $childPath)
							{
								try
								{
									$langFile = Translate\File::instantiateByPath($childPath);
									if ($langFile instanceof Translate\File)
									{
										$langFile->load();
										$index[$langId] = $langFile->count(true);

										if ($langId != $paramsIn['CURRENT_LANG'])
										{
											if ($ethalonFile instanceof Translate\File)
											{
												$index["{$langId}_excess"] = $langFile->countExcess($ethalonFile);
												$index["{$langId}_deficiency"] = $langFile->countDeficiency($ethalonFile);
											}
										}
									}
								}
								catch (Main\ArgumentException $ex)
								{
									continue;
								}
							}
							$entry['index'] = $index;

							yield $entry;
						}
					}
				}
			};

		$this->fileData = $this->dirData = array();

		$totalItemsFound = 0;
		foreach ($iterateDirectory($topFolder->getPath(), $this->path, $isTopLang) as $entry)
		{
			$totalItemsFound ++;
			$pathId = $entry['columns']['PATH'];

			if ($entry['columns']['IS_DIR'])
			{
				$this->dirData[$pathId] = $entry;
			}
			else
			{
				$this->fileData[$pathId] = $entry;
			}
		}

		return $totalItemsFound;
	}


	/**
	 * Appends actual file data by date from index. Initializes $this->indexData.
	 *
	 * @param string[] $pathList Folder path list to filter.
	 * @param boolean $loadPathsDiff Load path data for link.
	 *
	 * @return int
	 */
	private function loadIndexFileData(array $pathList = [], $loadPathsDiff = false)
	{
		$paramsIn =& $this->getParams();
		// top folder
		$topIndexPath = $this->detectTopIndexPath();
		if ($topIndexPath instanceof Translate\Index\PathIndex)
		{
			try
			{
				$languages = !empty($this->arResult['GRID_LANGUAGES']) ? $this->arResult['GRID_LANGUAGES'] : $this->arResult['LANGUAGES'];
				$languageUpperKeys = array_combine($languages, array_map('mb_strtoupper', $languages));

				if ($loadPathsDiff)
				{
					$query = Index\Aggregate::buildQuery([
						'PARENT_ID' => $topIndexPath->getId(),
						'CURRENT_LANG' => $paramsIn['CURRENT_LANG'],
						'LANGUAGES' => $languages,
						'PATH_LIST' => $pathList,
					]);
					$query->addSelect( 'PARENT_PATH');
					$query->addSelect( 'FILE_PATH');
					foreach ($languageUpperKeys as $langId => $alias)
					{
						// phrase count
						$query->addSelect("{$alias}_CNT");
						// file count
						$query->addSelect("{$alias}_FILE_CNT");
						// file excess
						$query->addSelect("{$alias}_FILE_EXCESS");
						// phrase excess
						$query->addSelect("{$alias}_EXCESS");

						if ($langId != $paramsIn['CURRENT_LANG'])
						{
							// file deficiency
							$query->addSelect("{$alias}_FILE_DEFICIENCY");
							// phrase deficiency
							$query->addSelect("{$alias}_DEFICIENCY");
						}
					}
				}
				else
				{
					$query = Index\Aggregate::buildAggregateQuery([
						'PARENT_ID' => $topIndexPath->getId(),
						'CURRENT_LANG' => $paramsIn['CURRENT_LANG'],
						'LANGUAGES' => $languages,
						'GROUP_BY' => 'PARENT_PATH',
						'PATH_LIST' => $pathList,
					]);
				}

				$cursor = $query->exec();

				$this->indexData = array();
				foreach ($cursor as $row)
				{
					$parentPath = $row['PARENT_PATH'];

					if (!isset($this->indexData[$parentPath]))
					{
						$this->indexData[$parentPath] = array();
					}

					foreach ($languageUpperKeys as $langId => $langUpper)
					{
						$deficiency = $excess = 0;
						if (!isset($this->indexData[$parentPath][$langId]))
						{
							$this->indexData[$parentPath][$langId] = array(
								'file_count' => 0,
								'phrase_count' => 0,
								'file_excess' => 0,
								'phrase_excess' => 0,
								'file_deficiency' => 0,
								'phrase_deficiency' => 0,
							);
							if ($loadPathsDiff)
							{
								$this->indexData[$parentPath][$langId]['deficiency_links'] = array();
								$this->indexData[$parentPath][$langId]['excess_links'] = array();
							}
						}

						$this->indexData[$parentPath][$langId]['file_count'] += (int)$row["{$langUpper}_FILE_CNT"];
						$this->indexData[$parentPath][$langId]['phrase_count'] += (int)$row["{$langUpper}_CNT"];

						$this->indexData[$parentPath][$langId]['file_excess'] += (int)$row["{$langUpper}_FILE_EXCESS"];
						$excess = (int)$row["{$langUpper}_EXCESS"];
						$this->indexData[$parentPath][$langId]['phrase_excess'] += $excess;

						if ($langId != $paramsIn['CURRENT_LANG'])
						{
							$this->indexData[$parentPath][$langId]['file_deficiency'] += (int)$row["{$langUpper}_FILE_DEFICIENCY"];
							$deficiency = (int)$row["{$langUpper}_DEFICIENCY"];
							$this->indexData[$parentPath][$langId]['phrase_deficiency'] += $deficiency;
						}

						if ($loadPathsDiff)
						{
							if ($deficiency > 0)
							{
								if (count($this->indexData[$parentPath][$langId]['deficiency_links']) <= $paramsIn['DIFF_LINKS_LIMIT'])
								{
									$this->indexData[$parentPath][$langId]['deficiency_links'][] = array(
										'path' => $row['FILE_PATH'],
										'deficiency' => $deficiency,
									);
								}
								else
								{
									$this->indexData[$parentPath][$langId]['deficiency_links_more'] = true;
								}
							}
							elseif ($excess > 0)
							{
								if (count($this->indexData[$parentPath][$langId]['excess_links']) <= $paramsIn['DIFF_LINKS_LIMIT'])
								{
									$this->indexData[$parentPath][$langId]['excess_links'][] = array(
										'path' => $row['FILE_PATH'],
										'excess' => $excess,
									);
								}
								else
								{
									$this->indexData[$parentPath][$langId]['excess_links_more'] = true;
								}
							}
						}
					}
				}
			}
			catch (Main\SystemException $exception)
			{
				$this->addError(new Error($exception->getMessage(), $exception->getCode()));
			}
		}

		return count($this->indexData);
	}


	/**
	 * todo: Revert module assigment
	 *
	 * @return string[]
	 */
	private function getModuleList()
	{
		static $modulesList;

		if (empty($modulesList))
		{
			$modulesList = array();

			$pathModulesRes = Index\Internals\PathIndexTable::getList([
				'filter' => [
					'=PATH' => '/bitrix/modules'
				],
				'select' => ['ID']
			]);
			while ($pathModules = $pathModulesRes->fetch())
			{
				$pathList = Index\Internals\PathIndexTable::getList([
					'filter' => [
						'=PARENT_ID' => $pathModules['ID'],
						'!=MODULE_ID' => null,
					],
					'select' => ['ID', 'NAME'],
					'order' => ['NAME' => 'ASC'],
				]);
				while ($module = $pathList->fetch())
				{
					$modulesList[] = $module['NAME'];
				}
			}
		}

		return $modulesList;
	}

	/**
	 * todo: Revert module assigment
	 *
	 * @param string $moduleId Module Id.
	 * @return string
	 */
	private function getModuleTitle($moduleId)
	{
		static $title = array();
		if (!isset($title[$moduleId]))
		{
			if ($info = \CModule::CreateModuleObject($moduleId))
			{
				$title[$moduleId] = $info->MODULE_NAME;
			}
		}

		return $title[$moduleId] ?: $moduleId;
	}

	/**
	 * todo: Revert type assigment
	 *
	 * @param string $assignmentId Assignment Id.
	 * @return string
	 */
	private function getAssignmentTitle($assignmentId)
	{
		static $title = array();
		if (!isset($title[$assignmentId]))
		{
			$title[$assignmentId] = Loc::getMessage("TR_ASSIGNMENT_TYPE_".mb_strtoupper($assignmentId));
		}

		return $title[$assignmentId] ?: $assignmentId;
	}


	/**
	 * @return string
	 */
	private function detectAction()
	{
		if (empty($this->action))
		{
			$this->action = self::ACTION_FILE_LIST;

			if (
				$this->filter instanceof Translate\Filter &&
				$this->filter->count() > 0
			)
			{
				if (
					!empty($this->filter['FILE_NAME']) ||
					!empty($this->filter['FOLDER_NAME']) ||
					!empty($this->filter['INCLUDE_PATHS']) ||
					!empty($this->filter['EXCLUDE_PATHS'])
				)
				{
					$this->action = self::ACTION_SEARCH_FILE;
				}
				if (
					!empty($this->filter['PHRASE_CODE']) ||
					!empty($this->filter['INCLUDE_PHRASE_CODES']) ||
					!empty($this->filter['EXCLUDE_PHRASE_CODES']) ||
					!empty($this->filter['PHRASE_TEXT'])
				)
				{
					$this->action = self::ACTION_SEARCH_PHRASE;
				}
			}
		}

		return $this->action;
	}


	/**
	 * Returns items for grid menu with group action.
	 *
	 * @param string $action Component action command.
	 *
	 * @return array
	 */
	protected function getGridGroupAction($action)
	{
		$snippet = new Main\Grid\Panel\Snippet();

		$actionList = array(
			array('NAME' => Loc::getMessage('TR_LIST_GROUP_ACTION_CHOOSE'), 'VALUE' => 'none')
		);

		$applyButton = $snippet->getApplyButton(
			array(
				'ONCHANGE' => array(
					array(
						'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
						'DATA' => array(
							array(
								'JS' => 'BX.Translate.PathList.callGroupAction()'
							)
						)
					)
				)
			)
		);
		switch ($action)
		{
			case self::ACTION_SEARCH_FILE:
			{
				$actionList[] = array(
					'NAME' => Loc::getMessage('TR_LIST_GROUP_ACTION_EXPORT'),
					'VALUE' => Translate\Controller\Export\Csv::ACTION_EXPORT_PATH,
					'ONCHANGE' => array(
						array(
							'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
						)
					)
				);

				break;
			}

			case self::ACTION_SEARCH_PHRASE:
			{
				$actionList[] = array(
					'NAME' => Loc::getMessage('TR_LIST_GROUP_ACTION_EXPORT'),
					'VALUE' => Translate\Controller\Export\Csv::ACTION_EXPORT_PATH,
					'ONCHANGE' => array(
						array(
							'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
						)
					)
				);

				break;
			}

			case self::ACTION_FILE_LIST:
			{
				$actionList[] = array(
					'NAME' => Loc::getMessage('TR_LIST_GROUP_ACTION_EXPORT'),
					'VALUE' => Translate\Controller\Export\Csv::ACTION_EXPORT_PATH,
					'ONCHANGE' => array(
						array(
							'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
						)
					)
				);

				$actionList[] = array(
					'NAME' => Loc::getMessage('TR_LIST_GROUP_ACTION_DELETE_ETHALON'),
					'VALUE' => Translate\Controller\Editor\File::ACTION_CLEAN_ETHALON,
					'ONCHANGE' => array(
						array(
							'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
						)
					)
				);

				break;
			}
		}
		$groupActions = array(
			'GROUPS' => array(
				array(
					'ITEMS' => array(
						array(
							"TYPE" => Main\Grid\Panel\Types::DROPDOWN,
							"ID" => "action_button",
							"NAME" => "action_button",
							"ITEMS" => $actionList
						),
						$applyButton,
					)
				)
			)
		);

		return $groupActions;
	}
}