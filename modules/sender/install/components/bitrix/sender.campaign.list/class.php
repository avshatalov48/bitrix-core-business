<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Grid\Options as GridOptions;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderCampaignListComponent extends CBitrixComponent
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

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_CAMPAIGN_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';

		$this->arParams['RENDER_FILTER_INTO_VIEW'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW']) ? $this->arParams['RENDER_FILTER_INTO_VIEW'] : '';
		$this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW_SORT']) ? $this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] : 10;

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifyLetters();
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
					Entity\Campaign::removeById($id);
				}
				break;
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_CAMPAIGN_COMP_TITLE'));
		}

		if (!Security\Access::current()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		// set ui filter
		$this->setUiFilter();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page-sender-campaigns");
		$nav->allowAllRecords(true)->setPageSize(20)->initFromUri();

		// get rows
		$sites = Entity\Campaign::getSites();
		$list = Entity\Campaign::getList([
			'select' => [
				'ID', 'NAME', 'DATE_INSERT',
				'ACTIVE', 'IS_PUBLIC', 'SITE_ID'
			],
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		]);
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			$item['URLS'] = array(
				'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
			);

			$item['SITE_ID'] = $sites[$item['SITE_ID']];
			$item['IS_PUBLIC'] = $item['IS_PUBLIC'] === 'Y' ? Loc::getMessage('SENDER_CAMPAIGN_COMP_YES') : Loc::getMessage('SENDER_CAMPAIGN_COMP_NO');
			$item['ACTIVE'] = $item['ACTIVE'] === 'Y' ? Loc::getMessage('SENDER_CAMPAIGN_COMP_YES') : Loc::getMessage('SENDER_CAMPAIGN_COMP_NO');

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
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}
		if (isset($requestFilter['ACTIVE']) && $requestFilter['ACTIVE'])
		{
			$filter['=ACTIVE'] = $requestFilter['ACTIVE'];
		}
		if (isset($requestFilter['IS_PUBLIC']) && $requestFilter['IS_PUBLIC'])
		{
			$filter['=IS_PUBLIC'] = $requestFilter['IS_PUBLIC'];
		}
		if (isset($requestFilter['SITE_ID']) && $requestFilter['SITE_ID'])
		{
			$filter['=SITE_ID'] = $requestFilter['SITE_ID'];
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
		return [
			[
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_DATE_INSERT'),
				"sort" => "DATE_INSERT",
				"default" => false
			],
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_NAME'),
				"sort" => "NAME",
				"default" => true
			],
			[
				"id" => "ACTIVE",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_ACTIVE'),
				"sort" => "ACTIVE",
				"default" => true
			],
			[
				"id" => "IS_PUBLIC",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_IS_PUBLIC'),
				"sort" => "IS_PUBLIC",
				"default" => true
			],
			[
				"id" => "SITE_ID",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_SITE_ID'),
				"sort" => "SITE_ID",
				"default" => true
			],
		];
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = array(
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_NAME'),
				"default" => true,
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_DATE_INSERT'),
				"type" => "date",
				"default" => true
			],
			[
				"id" => "ACTIVE",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_ACTIVE'),
				"type" => "checkbox",
				"default" => true
			],
			[
				"id" => "IS_PUBLIC",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_IS_PUBLIC'),
				"type" => "checkbox",
				"default" => true
			],
			[
				"id" => "SITE_ID",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_SITE_ID'),
				"type" => "list",
				"items" => Entity\Campaign::getSites(),
				"default" => true
			],
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