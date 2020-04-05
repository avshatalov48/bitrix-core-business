<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserConsent\Internals;
use Bitrix\Main\UserConsent\Consent;
use Bitrix\Main\UI\Filter\Options as FilterOptions;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class MainUserConsentConsentListComponent extends CBitrixComponent
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

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'MAIN_USER_CONSENT_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['GRID_FILTER_ID'] : $this->arParams['FILTER_ID'] . '_FILTER';

		$this->arParams['RENDER_FILTER_INTO_VIEW'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW']) ? $this->arParams['RENDER_FILTER_INTO_VIEW'] : '';
		$this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW_SORT']) ? $this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] : 10;

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT']) ? $this->arParams['CAN_EDIT'] : false;
	}

	protected function prepareResult()
	{
		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		// set ui filter
		$this->setUiFilter();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page");
		$nav->allowAllRecords(true)->setPageSize(10)->initFromUri();

		// get rows
		$list = Internals\ConsentTable::getList(array(
			'select' => array(
				'ID', 'DATE_INSERT', 'IP', 'URL',
				'USER_ID', 'ORIGINATOR_ID', 'ORIGIN_ID',
				'USER_LOGIN' => 'USER.LOGIN',
				'USER_NAME' => 'USER.NAME',
				'USER_LAST_NAME' => 'USER.LAST_NAME',
				'USER_SECOND_NAME' => 'USER.SECOND_NAME',
			),
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => array(
				'ID' => 'ASC'
			)
		));
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			// format origin
			$this->setRowColumnOrigin($item);

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
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('MAIN_USER_CONSENTS_COMP_TITLE'));
		}

		return true;
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);

		$filter = array();
		if (isset($requestFilter['AGREEMENT_ID']) && $requestFilter['AGREEMENT_ID'])
		{
			$filter['=AGREEMENT_ID'] = $requestFilter['AGREEMENT_ID'];
		}
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}

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
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_DATE_INSERT'),
				"default" => true
			),
			array(
				"id" => "USER",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_USER'),
				"default" => true
			),
			array(
				"id" => "IP",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_IP'),
				"default" => true
			),
			array(
				"id" => "ORIGIN",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_ORIGIN'),
				"default" => true
			),
			array(
				"id" => "URL",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_URL'),
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
		$agreements = Internals\AgreementTable::getList([
			'select' => ['ID', 'NAME'],
			'order' => ['ID' => 'DESC']]
		)->fetchAll();
		$agreements = array_combine(
			array_column($agreements, 'ID'),
			array_column($agreements, 'NAME')
		);
		$this->arResult['FILTERS'] = array(
			array(
				"id" => "AGREEMENT_ID",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_AGREEMENT_ID'),
				"default" => true,
				"type" => "list",
				"items" => $agreements
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_DATE_INSERT'),
				"type" => "date",
				"default" => true
			),
		);
	}

	protected function setRowColumnOrigin(array &$data)
	{
		$data['ORIGIN'] = '';
		$data['ORIGIN_PATH'] = '';
		if (!$data['ORIGINATOR_ID'])
		{
			return;
		}

		$originData = Consent::getOriginData($data['ORIGINATOR_ID'], $data['ORIGIN_ID']);
		if (!$originData)
		{
			return;
		}

		$data['ORIGIN'] = $originData['NAME'];
		$data['ORIGIN_PATH'] = $originData['URL'];
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