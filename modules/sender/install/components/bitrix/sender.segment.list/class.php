<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Entity;
use Bitrix\Sender\GroupTable;
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

class SenderSegmentListComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!Bitrix\Main\Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['GRID_ID'] =
			isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_SEGMENT_GRID';
		parent::initParams();
	}

	protected function preparePost()
	{
		$ids = $this->request->get('ID');
		$action = $this->request->get('action_button_' . $this->arParams['GRID_ID']);
		switch ($action)
		{
			case 'delete':
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				foreach ($ids as $id)
				{
					Entity\Segment::removeById($id);
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
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_SEGMENT_LIST_COMP_TITLE'));
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		// set ui filter
		$this->setUiFilter();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page-sender-segments");
		$nav->allowAllRecords(true)->setPageSize(10)->initFromUri();

		// get rows
		$list = GroupTable::getList(array(
			'select' => array(
				'CNT', 'ID', 'DATE_INSERT', 'NAME', 'ADDRESS_COUNT', 'USE_COUNT'
			),
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT',
					'COUNT(ID)'
				)
			),
			'order' => $this->getGridOrder()
		));

		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			$item['URLS'] = array(
				'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
			);

			$item['ADDRESS_COUNTER'] = Entity\Segment::getAddressCounter($item['ID'])->getArray();
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
		$searchString = $filterOptions->getSearchString();

		$filter = [];
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['NAME'] = '%' . $requestFilter['NAME'] . '%';
		}
		if ($searchString)
		{
			$filter['NAME'] = '%' . $searchString . '%';
		}
		if (isset($requestFilter['SYSTEM']) && $requestFilter['SYSTEM'])
		{
			$filter['=IS_SYSTEM'] = true;
		}

		if (!isset($requestFilter['HIDDEN']) || !$requestFilter['HIDDEN'])
		{
			$filter['=HIDDEN'] = false;
		}
		elseif (in_array($requestFilter['HIDDEN'], ['Y', 'N']))
		{
			$filter['=HIDDEN'] = $requestFilter['HIDDEN'] === 'Y';
		}

		return $filter;
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
		return array(
			array(
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"sort" => "DATE_INSERT",
				"default" => false
			),
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_TITLE'),
				"sort" => "NAME",
				"default" => true
			),
			array(
				"id" => "USE_COUNT",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_USE_COUNT'),
				"sort" => "USE_COUNT",
				"default" => true
			),
			array(
				"id" => "ADDRESS_COUNT",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_ADDRESS_COUNT'),
				"sort" => "ADDRESS_COUNT",
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = [
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_TITLE'),
				"default" => true
			],
			[
				"id" => "SYSTEM",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_SYSTEM'),
				"type" => "checkbox",
				"default" => true
			],
			[
				"id" => "HIDDEN",
				"name" => Loc::getMessage('SENDER_SEGMENT_LIST_COMP_UI_COLUMN_HIDDEN'),
				"type" => "checkbox",
				"default" => true
			],
		];
	}

	protected function setRowColumnUser(array &$data)
	{
		$data['USER'] = '';
		$data['USER_PATH'] = '';
		if (!$data['USER_ID'])
		{
			return;
		}

		$data['USER_PATH'] = str_replace('#id#', $data['USER_ID'], $this->arParams['PATH_TO_USER_PROFILE']);
		$data['USER'] = \CUser::FormatName(
			$this->arParams['NAME_TEMPLATE'],
			array(
				'LOGIN' => $data['USER_LOGIN'],
				'NAME' => $data['USER_NAME'],
				'LAST_NAME' => $data['USER_LAST_NAME'],
				'SECOND_NAME' => $data['USER_SECOND_NAME']
			),
			true, false
		);
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
		parent::executeComponent();
		$this->prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_SEGMENT_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SEGMENT_VIEW;
	}
}