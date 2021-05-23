<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Security;
use Bitrix\Sender\UI\PageNavigation;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderContactSetListComponent extends \CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_CONTACT_LIST'] = isset($this->arParams['PATH_TO_CONTACT_LIST']) ? $this->arParams['PATH_TO_CONTACT_LIST'] : '';

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_CONTACT_SET_LIST_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::getInstance()->canModifySegments();

	}

	protected function preparePost()
	{
		$action = $this->request->get('action_button_' . $this->arParams['GRID_ID']);
		switch ($action)
		{
			case 'edit':
				$editFields = $this->request->get('FIELDS');
				$editFields = \Bitrix\Main\Text\Encoding::convertEncoding($editFields, 'UTF-8', LANG_CHARSET);
				if (!is_array($editFields))
				{
					$editFields = [];
				}

				foreach ($editFields as $id => $fields)
				{
					$fields = $this->filterActionFieldsByGridColumns($fields);
					ListTable::update($id, $fields);
				}
				break;
			case 'delete':
				$ids = $this->request->get('ID');
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				foreach ($ids as $id)
				{
					ListTable::delete($id);
				}
				break;
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_CONTACT_SET_LIST_TITLE'));
		}

		if (!Security\Access::getInstance()->canViewSegments())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ERRORS'] = [];
		$this->arResult['ROWS'] = [];

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		// set ui filter
		$this->setUiFilter();
		$this->setUiFilterPresets();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page-sender-set-list");
		$nav->allowAllRecords(false)->setPageSize(10)->initFromUri();

		// get rows
		$list = ListTable::getList(array(
			'select' => array(
				'ID', 'NAME', 'CODE',
			),
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		));
		foreach ($list as $item)
		{
			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		return true;
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString = trim($filterOptions->getSearchString());

		$filter = [];
		if ($searchString)
		{
			$filter['NAME'] = '%' . $searchString . '%';

		}
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['NAME'] = '%' . $requestFilter['NAME'] . '%';
		}
		if (isset($requestFilter['CODE']) && $requestFilter['CODE'])
		{
			$filter['CODE'] = '%' . $requestFilter['CODE'] . '%';
		}

		return $filter;
	}

	protected function filterActionFieldsByGridColumns(array $fields)
	{
		$list = [];
		foreach ($this->getUiGridColumns() as $column)
		{
			if (!isset($column['editable']) || !$column['editable'])
			{
				continue;
			}

			if (!isset($fields[$column['id']]))
			{
				continue;
			}

			$list[$column['id']] = $fields[$column['id']];
		}

		return $list;
	}

	protected function getGridOrder()
	{
		$defaultSort = array('ID' => 'DESC');

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$sorting = $gridOptions->getSorting(array('sort' => $defaultSort));

		$by = key($sorting['sort']);
		$order = mb_strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

		$list = array();
		foreach ($this->getUiGridColumns() as $column)
		{
			if (!isset($column['sort']) || !$column['sort'])
			{
				continue;
			}

			$list[] = $column['sort'];
		}

		if (!in_array($by, $list))
		{
			return $defaultSort;
		}

		return array($by => $order);
	}

	protected function setUiGridColumns()
	{
		$this->arResult['COLUMNS'] = $this->getUiGridColumns();
	}

	protected function getUiGridColumns()
	{
		return [
			[
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			],
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CONTACT_SET_LIST_UI_COLUMN_NAME'),
				"sort" => "NAME",
				"default" => true,
				"editable" => ["TYPE" => Grid\Editor\Types::TEXT]
			],
			[
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_CONTACT_SET_LIST_UI_COLUMN_CODE'),
				"sort" => "CODE",
				"default" => true,
				"editable" => ["TYPE" => Grid\Editor\Types::TEXT]
			],
		];
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = array(
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CONTACT_SET_LIST_UI_COLUMN_NAME'),
				"default" => true,
			),
			array(
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_CONTACT_SET_LIST_UI_COLUMN_CODE'),
				"default" => true,
			),
		);
	}

	protected function getUiFilterPresets()
	{
		return [];
	}

	protected function setUiFilterPresets()
	{
		$this->arResult['FILTER_PRESETS'] = $this->getUiFilterPresets();
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Bitrix\Main\Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();
			return;
		}

		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}