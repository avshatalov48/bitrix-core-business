<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UserConsent\Agreement;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserConsent\Internals\AgreementTable;
use Bitrix\Main\UI\Filter\Options as FilterOptions;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class MainUserConsentListComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var  Agreement $agreement */
	protected $agreement;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_ADD'] = isset($this->arParams['PATH_TO_ADD']) ? $this->arParams['PATH_TO_ADD'] : '';
		$this->arParams['PATH_TO_EDIT'] = isset($this->arParams['PATH_TO_EDIT']) ? $this->arParams['PATH_TO_EDIT'] : '';
		$this->arParams['PATH_TO_CONSENT_LIST'] = isset($this->arParams['PATH_TO_CONSENT_LIST']) ? $this->arParams['PATH_TO_CONSENT_LIST'] : '';

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'MAIN_USER_CONSENT_AGREEMENT_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['GRID_FILTER_ID'] : $this->arParams['FILTER_ID'] . '_FILTER';

		$this->arParams['RENDER_FILTER_INTO_VIEW'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW']) ? $this->arParams['RENDER_FILTER_INTO_VIEW'] : '';
		$this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW_SORT']) ? $this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] : 10;

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT']) ? $this->arParams['CAN_EDIT'] : false;
	}

	protected function processPostAction()
	{
		if ($this->request->get('grid_id') != $this->arParams['GRID_ID'])
		{
			return;
		}

		switch ($this->request->get('action'))
		{
			case 'deleteRow':
				$agreementId = $this->request->get('id');
				if (!$agreementId)
				{
					return;
				}

				$deleteResult = AgreementTable::delete($agreementId);
				$deleteResult->isSuccess();

				break;
		}
	}

	protected function processPost()
	{
		if ($this->request->get('action') && $this->arParams['CAN_EDIT'])
		{
			$this->processPostAction();
		}
	}

	protected function prepareResult()
	{
		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		if ($this->request->isPost() && check_bitrix_sessid())
		{
			$this->processPost();
		}

		// set ui filter
		$this->setUiFilter();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page");
		$nav->allowAllRecords(true)->setPageSize(10)->initFromUri();

		// get rows
		$booleanList = $this->getBooleanNameList();
		$typeNames = Agreement::getTypeNames();
		$list = AgreementTable::getList(array(
			'select' => array('ID', 'DATE_INSERT', 'ACTIVE', 'NAME', 'TYPE'),
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'cache' => array('ttl' => 3600),
			'order' => array(
				'ID' => 'ASC'
			)
		));
		foreach ($list as $item)
		{

			$item['ACTIVE'] = $booleanList[$item['ACTIVE']];
			$item['TYPE'] = $typeNames[$item['TYPE']];

			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_TITLE'));
		}

		return true;
	}

	protected function getBooleanNameList()
	{
		return array(
			'N' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_N'),
			'Y' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_Y'),
		);
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);

		$filter = array();
		if (isset($requestFilter['TYPE']) && $requestFilter['TYPE'])
		{
			$filter['=TYPE'] = $requestFilter['TYPE'];
		}
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['NAME'] = '%' . $requestFilter['NAME'] . '%';
		}
		/*
		if (isset($requestFilter['ACTIVE']) && $requestFilter['ACTIVE'])
		{
			$filter['=ACTIVE'] = $requestFilter['ACTIVE'];
		}
		*/

		return $filter;
	}

	protected function setUiGridColumns()
	{
		$this->arResult['COLUMNS'] = array(
			array(
				"id" => "ID",
				"name" => "ID",
				"default" => false
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"default" => true
			),
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_NAME'),
				"default" => true
			),
			array(
				"id" => "TYPE",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_TYPE'),
				"default" => true
			),
			/*
			array(
				"id" => "ACTIVE",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_ACTIVE'),
				"default" => true
			)
			*/
		);
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = array(
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_NAME'),
				"default" => true
			),
			array(
				"id" => "TYPE",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_TYPE'),
				"type" => "list",
				"items" => Agreement::getTypeNames(),
				"default" => true
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"type" => "date",
				"default" => true
			),
			/*
			array(
				"id" => "ACTIVE",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_ACTIVE'),
				"type" => "checkbox",
				"default" => true
			),
			*/
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