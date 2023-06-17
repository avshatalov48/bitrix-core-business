<?

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
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

	public function executeComponent()
	{
		$this->errors = new ErrorCollection();

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

	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('ui'))
		{
			$this->errors->setError(new Error('Could not include ui module'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_ADD'] = $this->arParams['PATH_TO_ADD'] ?? '';
		$this->arParams['PATH_TO_EDIT'] = $this->arParams['PATH_TO_EDIT'] ?? '';
		$this->arParams['PATH_TO_CONSENT_LIST'] = $this->arParams['PATH_TO_CONSENT_LIST'] ?? '';

		$this->arParams['GRID_ID'] = $this->arParams['GRID_ID'] ?? 'MAIN_USER_CONSENT_AGREEMENT_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['GRID_FILTER_ID'] : $this->arParams['FILTER_ID'] . '_FILTER';

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;

		$this->arParams['CAN_EDIT'] = $this->arParams['CAN_EDIT'] ?? false;

		$this->arParams['ADMIN_MODE'] = $this->arParams['ADMIN_MODE'] ?? false;
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
		$list = AgreementTable::getList([
			'select' => ['ID', 'DATE_INSERT', 'ACTIVE', 'NAME', 'TYPE'],
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'cache' => ['ttl' => 3600],
			'order' => $this->getGridOrder()
		]);
		foreach ($list as $item)
		{
			$item['ACTIVE'] = $booleanList[$item['ACTIVE']];
			$item['TYPE'] = $typeNames[$item['TYPE']];

			$this->arResult['ROWS'][] = $item;
		}

		$this->prepareRowsActions();

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_TITLE_1'));
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
		if (isset($requestFilter['LANGUAGE_ID']) && $requestFilter['LANGUAGE_ID'])
		{
			$filter['=LANGUAGE_ID'] = $requestFilter['LANGUAGE_ID'];
		}

		return $filter;
	}

	protected function setUiGridColumns(): void
	{
		$this->arResult['COLUMNS'] = $this->getUiGridColumns();
	}

	private function getUiGridColumns(): array
	{
		return [
			[
				"id" => "ID",
				"name" => "ID",
				"default" => false
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"default" => true,
				"sort" => "DATE_INSERT",
			],
			[
				"id" => "NAME",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_NAME'),
				"default" => true,
				"sort" => "NAME",
			],
			[
				"id" => "TYPE",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_TYPE'),
				"default" => true,
				"sort" => "TYPE",
			],
			[
				"id" => "ADDITIONAL",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_TITLE_ADDITIONAL_1'),
				"default" => true
			],
		];
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = [
			[
				"id" => "NAME",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_NAME'),
				"default" => true
			],
			[
				"id" => "TYPE",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_TYPE'),
				"type" => "list",
				"items" => Agreement::getTypeNames(),
				"default" => true
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"type" => "date",
				"default" => true
			],
			[
				"id" => "LANGUAGE_ID",
				"name" => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_COLUMN_LANGUAGE_ID'),
				"type" => "list",
				"items" => $this->getLanguages(),
				"default" => true
			],
		];
	}

	private function getGridOrder()
	{
		$defaultSort = ['ID' => 'DESC'];

		$gridOptions = new Bitrix\Main\Grid\Options($this->arParams['GRID_ID']);
		$sorting = $gridOptions->getSorting(['sort' => $defaultSort]);

		$by = key($sorting['sort']);
		$order = strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

		$list = [];
		foreach ($this->getUiGridColumns() as $column)
		{
			if (!empty($column['sort']))
			{
				$list[] = $column['sort'];
			}
		}

		if (!in_array($by, $list))
		{
			return $defaultSort;
		}

		return [$by => $order];
	}

	private function getLanguages(): array
	{
		$languages = [];

		$queryObject = CLanguage::getList();
		while ($language = $queryObject->fetch())
		{
			$languages[$language['LANGUAGE_ID']] = $language['LANGUAGE_ID'];
		}

		return $languages;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	private function prepareRowsActions()
	{
		foreach ($this->arResult['ROWS'] as $index => $data)
		{
			$pathToEdit = str_replace('#id#', $data['ID'], $this->arParams['PATH_TO_EDIT']);
			$pathToConsentList = str_replace('#id#', $data['ID'], $this->arParams['PATH_TO_CONSENT_LIST']);

			$data['NAME'] = '<a data-bx-slider-href="" href="' . htmlspecialcharsbx($pathToEdit) .
				'">' . htmlspecialcharsbx($data['NAME']) . '</a>';

			$data['ADDITIONAL'] = '<a data-bx-slider-href="" href="' . htmlspecialcharsbx($pathToConsentList) .
				'">' . Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_VIEW_CONSENTS_1') . '</a>';

			$actions = [];
			$actions[] = [
				'text' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_VIEW'),
				'onclick' => 'BX.SidePanel.Instance.open("' . \CUtil::JSEscape($pathToEdit). '")',
				'default' => true
			];
			$actions[] = [
				'text' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_VIEW_CONSENTS_1'),
				'href' => CUtil::JSEscape($pathToConsentList),
			];
			$actions[] = [
				'text' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_REMOVE'),
				'onclick' => 'BX.Main.UserConsent.List.remove(' . \CUtil::JSEscape($data['ID']). ', "' .
					\CUtil::JSEscape($this->arParams['GRID_ID']). '")',
			];

			$this->arResult['ROWS'][$index] = [
				'id' => $data['ID'],
				'columns' => $data,
				'actions' => $actions
			];
		}
	}
}