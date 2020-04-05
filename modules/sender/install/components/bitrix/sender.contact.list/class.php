<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Entity;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Security;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Internals\DataExport;

use Bitrix\Sender\UI\PageNavigation;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderContactListComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_LIST'] = isset($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : '';
		$this->arParams['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ? $this->arParams['PATH_TO_USER_PROFILE'] : '';
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_CONTACT_LIST_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';

		$this->arParams['SHOW_SETS'] = isset($this->arParams['SHOW_SETS']) ? (bool) $this->arParams['SHOW_SETS'] : false;
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifySegments();

		if (isset($this->arParams['LIST_ID']))
		{
			$this->arParams['LIST_ID'] = (int) $this->arParams['LIST_ID'];
		}
		else
		{
			$this->arParams['LIST_ID'] = (int) $this->request->get('listId');
		}
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
					Entity\Contact::removeById($id);
				}
				break;
		}
	}

	protected function prepareExport()
	{
		$list = ContactTable::getList(array(
			'select' => $this->getDataSelectedFields(),
			'filter' => $this->getDataFilter(),
			'order' => $this->getGridOrder()
		));

		DataExport::toCsv(
			$this->getUiGridColumns(),
			$list,
			function ($item)
			{
				$item['TYPE_ID'] = Recipient\Type::getName($item['TYPE_ID']);
				return $item;
			}
		);
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_CONTACT_LIST_TITLE1'));
		}

		if (!Security\Access::current()->canViewSegments())
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
		$this->setUiFilterPresets();

		// set ui grid columns
		$this->setUiGridColumns();

		// export
		if ($this->request->get('export'))
		{
			$this->prepareExport();
		}

		// create nav
		$nav = new PageNavigation("page-sender-contact-list");
		$nav->allowAllRecords(false)->setPageSize(10)->initFromUri();

		// get rows
		$list = ContactTable::getList(array(
			'select' => $this->getDataSelectedFields(),
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		));
		foreach ($list as $item)
		{
			$item['TYPE_NAME'] = Recipient\Type::getName($item['TYPE_ID']);
			$item['URLS'] = array(
				'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
				'RECIPIENT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_RECIPIENT']),
			);

			$item['HAS_STATISTICS'] = true;//$item['IS_READ'] === 'Y' || $item['IS_CLICK'] === 'Y' || $item['IS_UNSUB'] === 'Y' || $item['IP'] || $item['AGENT'];

			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		return true;
	}

	protected function getDataSelectedFields()
	{
		return [
			'ID', 'NAME', 'TYPE_ID', 'CODE', 'BLACKLISTED', 'DATE_INSERT',
			'IS_READ', 'IS_CLICK', 'IS_UNSUB', 'IP', 'AGENT'
		];
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
		if (isset($requestFilter['TYPE_ID']) && $requestFilter['TYPE_ID'])
		{
			$filter['=TYPE_ID'] = $requestFilter['TYPE_ID'];
		}
		if (isset($requestFilter['IS_SUBSCRIBED']))
		{
			if ($requestFilter['IS_SUBSCRIBED'] === 'Y')
			{
				$filter['>MAILING_SUBSCRIPTION.MAILING_ID'] = 0;
			}
			elseif ($requestFilter['IS_SUBSCRIBED'] === 'N')
			{
				$filter['=MAILING_SUBSCRIPTION.MAILING_ID'] = null;
			}
			else
			{
				$filter['=MAILING_SUBSCRIPTION.MAILING_ID'] = $requestFilter['IS_SUBSCRIBED'];
			}
		}
		if (isset($requestFilter['IS_UNSUBSCRIBED']))
		{
			if ($requestFilter['IS_UNSUBSCRIBED'] === 'Y')
			{
				$filter['>MAILING_UNSUBSCRIPTION.MAILING_ID'] = 0;
			}
			elseif ($requestFilter['IS_UNSUBSCRIBED'] === 'N')
			{
				$filter['=MAILING_UNSUBSCRIPTION.MAILING_ID'] = null;
			}
			else
			{
				$filter['=MAILING_UNSUBSCRIPTION.MAILING_ID'] = $requestFilter['IS_UNSUBSCRIBED'];
			}
		}


		if ($this->arParams['LIST_ID'])
		{
			$filter['=CONTACT_LIST.LIST_ID'] = $this->arParams['LIST_ID'];
		}

		if ($requestFilter['SET_ID'])
		{
			$filter['=CONTACT_LIST.LIST_ID'] = $requestFilter['SET_ID'];
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
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_DATE_INSERT'),
				"sort" => "DATE_INSERT",
				"default" => false
			),
			array(
				"id" => "TYPE_ID",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_TYPE_ID'),
				"sort" => "TYPE_ID",
				"default" => true,
			),
			array(
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_CODE'),
				"sort" => "CODE",
				"default" => true
			),
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_NAME'),
				"sort" => "NAME",
				"default" => true
			),
			array(
				"id" => "STAT",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_STAT'),
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
		$campaignList = [];
		$list = Entity\Campaign::getList([
			'select' => ['ID', 'NAME'],
			'order' => ['NAME' => 'ASC']
		])->fetchAll();
		if (count($list) > 0)
		{
			foreach ($list as $item)
			{
				$campaignList[$item['ID']] = $item['NAME'];
			}
		}

		$setList = [];
		$list = ListTable::getList([
			'select' => ['ID', 'NAME'],
			'order' => ['ID' => 'DESC']
		])->fetchAll();
		if (count($list) > 0)
		{
			foreach ($list as $item)
			{
				$setList[$item['ID']] = $item['NAME'];
			}
		}

		$this->arResult['FILTERS'] = array(
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_NAME'),
				"default" => true,
			),
			array(
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_CODE'),
				"default" => true,
			),
			array(
				"id" => "TYPE_ID",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_TYPE_ID'),
				"default" => true,
				"type" => "list",
				"params" => array('multiple' => 'Y'),
				"items" => array(
					Recipient\Type::EMAIL => Recipient\Type::getName(Recipient\Type::EMAIL),
					Recipient\Type::PHONE => Recipient\Type::getName(Recipient\Type::PHONE),
				)
			),
			array(
				"id" => "IS_SUBSCRIBED",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_IS_SUBSCRIBED'),
				"default" => true,
				"type" => "list",
				"params" => array('multiple' => 'N'),
				"items" => [
					'Y' => Loc::getMessage('SENDER_CONTACT_LIST_UI_YES'),
					'N' => Loc::getMessage('SENDER_CONTACT_LIST_UI_NO'),
				] + $campaignList
			),
			array(
				"id" => "IS_UNSUBSCRIBED",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_IS_UNSUBSCRIBED'),
				"default" => true,
				"type" => "list",
				"params" => array('multiple' => 'N'),
				"items" => [
					'Y' => Loc::getMessage('SENDER_CONTACT_LIST_UI_YES'),
					'N' => Loc::getMessage('SENDER_CONTACT_LIST_UI_NO'),
				] + $campaignList
			),
		);

		if ($this->arParams['SHOW_SETS'])
		{
			$this->arResult['FILTERS'][] = [
				"id" => "SET_ID",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_SET_ID'),
				"default" => true,
				"type" => "list",
				"params" => array('multiple' => 'N'),
				"items" => $setList
			];
		}
	}

	protected function getUiFilterPresets()
	{
		return array(
			'filter_contacts_sub' => array(
				'name' => Loc::getMessage('SENDER_CONTACT_LIST_COMP_UI_PRESET_SUB'),
				'fields' => array(
					'IS_SUBSCRIBED' => 'Y',
				)
			),
			'filter_contacts_unsub' => array(
				'name' => Loc::getMessage('SENDER_CONTACT_LIST_COMP_UI_PRESET_UNSUB'),
				'fields' => array(
					'IS_UNSUBSCRIBED' => 'Y',
				)
			),
			'filter_contacts_all' => array(
				'name' => Loc::getMessage('SENDER_CONTACT_LIST_COMP_UI_PRESET_ALL'),
				'default' => true,
				'fields' => []
			),
		);
	}

	protected function setUiFilterPresets()
	{
		$this->arResult['FILTER_PRESETS'] = $this->getUiFilterPresets();
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
		$this->errors = new \Bitrix\Main\ErrorCollection();
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