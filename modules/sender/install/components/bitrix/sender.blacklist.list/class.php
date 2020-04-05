<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Sender\UI\PageNavigation;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Entity;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderBlackListListComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_LIST'] = isset($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : '';
		$this->arParams['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ? $this->arParams['PATH_TO_USER_PROFILE'] : '';
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_BLACKLIST_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';

		$this->arParams['RENDER_FILTER_INTO_VIEW'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW']) ? $this->arParams['RENDER_FILTER_INTO_VIEW'] : '';
		$this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW_SORT']) ? $this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] : 10;

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifyBlacklist();
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
					Entity\Contact::removeFromBlacklistById($id);
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
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_BLACKLIST_LIST_TITLE'));
		}

		if (!Security\Access::current()->canViewBlacklist())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
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
		$nav = new PageNavigation("page-sender-blacklist");
		$nav->allowAllRecords(false)->setPageSize(10)->initFromUri();

		// get rows
		$list = ContactTable::getList(array(
			'select' => array(
				'ID', 'NAME', 'TYPE_ID', 'CODE', 'DATE_INSERT'
			),
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		));
		foreach ($list as $item)
		{
			$item['TYPE_NAME'] = Recipient\Type::getName($item['TYPE_ID']);
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

		$filter = array('BLACKLISTED' => true);
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['NAME'] = '%' . $requestFilter['NAME'] . '%';
		}
		if (isset($requestFilter['CODE']) && $requestFilter['CODE'])
		{
			$filter['CODE'] = '%' . $requestFilter['CODE'] . '%';
		}
		if (isset($requestFilter['TYPE_ID']) && $requestFilter['TYPE_ID'])
		{
			$filter['=TYPE_ID'] = $requestFilter['TYPE_ID'];
		}

		return $filter;
	}

	protected function getGridOrder()
	{
		$defaultSort = array('ID' => 'DESC');

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$sorting = $gridOptions->getSorting(array('sort' => $defaultSort));

		$by = key($sorting['sort']);
		$order = strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

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
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_DATE_INSERT'),
				"sort" => "DATE_INSERT",
				"default" => false
			),
			array(
				"id" => "TYPE_ID",
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_TYPE_ID'),
				"sort" => "TYPE_ID",
				"default" => true,
			),
			array(
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_CODE'),
				"sort" => "CODE",
				"default" => true
			),
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_NAME'),
				"sort" => "NAME",
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = array(
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_NAME'),
				"default" => true,
			),
			array(
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_CODE'),
				"default" => true,
			),
			array(
				"id" => "TYPE_ID",
				"name" => Loc::getMessage('SENDER_BLACKLIST_LIST_UI_COLUMN_TYPE_ID'),
				"default" => true,
				"type" => "list",
				"params" => array('multiple' => 'Y'),
				"items" => array(
					Recipient\Type::EMAIL => Recipient\Type::getName(Recipient\Type::EMAIL),
					Recipient\Type::PHONE => Recipient\Type::getName(Recipient\Type::PHONE),
				)
			),

		);
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
		$data['USER'] = \CAllUser::FormatName(
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
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('sender'))
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