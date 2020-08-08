<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ORM\Objectify\Values;
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
		if (!Loader::includeModule('ui'))
		{
			$this->errors->setError(new Error('Could not include ui module'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_LIST'] = isset($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : '';
		$this->arParams['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ? $this->arParams['PATH_TO_USER_PROFILE'] : '';
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'MAIN_USER_CONSENT_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['GRID_FILTER_ID'] : $this->arParams['FILTER_ID'] . '_FILTER';
		$this->arParams['AGREEMENT_ID'] = isset($this->arParams['AGREEMENT_ID']) ? $this->arParams['AGREEMENT_ID'] : '';

		$this->arParams['RENDER_FILTER_INTO_VIEW'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW']) ? $this->arParams['RENDER_FILTER_INTO_VIEW'] : '';
		$this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW_SORT']) ? $this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] : 10;

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT']) ? $this->arParams['CAN_EDIT'] : false;
		$this->arParams['USE_TOOLBAR'] = isset($this->arParams['USE_TOOLBAR']) ? $this->arParams['USE_TOOLBAR'] == 'Y' : true;
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

		$queryObject = Internals\ConsentTable::getList([
			'select' => [
				'ID', 'DATE_INSERT', 'IP', 'URL',
				'USER_ID', 'ORIGINATOR_ID', 'ORIGIN_ID',
				'USER_LOGIN' => 'USER.LOGIN',
				'USER_NAME' => 'USER.NAME',
				'USER_LAST_NAME' => 'USER.LAST_NAME',
				'USER_SECOND_NAME' => 'USER.SECOND_NAME',
			],
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		]);

		while ($userConsentObject = $queryObject->fetchObject())
		{
			$currentValues = $userConsentObject->collectValues(Values::ACTUAL);

			$items = [];
			foreach ($userConsentObject->fill(['ITEMS'])->getAll() as $itemObject)
			{
				$items[] = $itemObject->collectValues(Values::ACTUAL);
			}

			$userObject = $userConsentObject->getUser();
			if ($userObject)
			{
				$currentValues = $currentValues + $userObject->collectValues(Values::ACTUAL);
			}

			$this->setRowColumnUser($currentValues);

			$this->setRowColumnOrigin($currentValues);

			$this->setRowColumnItems($currentValues, $items);

			$this->arResult['ROWS'][] = $currentValues;
		}

		$this->prepareRowsActions();

		$this->arResult['TOTAL_ROWS_COUNT'] = $queryObject->getCount();

		// set rec count to nav
		$nav->setRecordCount($queryObject->getCount());
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

		$filter = [];
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
		if (!empty($requestFilter['USER.LAST_NAME']))
		{
			$filter['USER.LAST_NAME'] = '%' . $requestFilter['USER.LAST_NAME'] . '%';
		}
		if (!empty($requestFilter['USER.NAME']))
		{
			$filter['USER.NAME'] = '%' . $requestFilter['USER.NAME'] . '%';
		}
		if (!empty($requestFilter['USER.LOGIN']))
		{
			$filter['USER.LOGIN'] = '%' . $requestFilter['USER.LOGIN'] . '%';
		}
		if (!empty($requestFilter['USER.EMAIL']))
		{
			$filter['USER.EMAIL'] = '%' . $requestFilter['USER.EMAIL'] . '%';
		}
		if (!empty($requestFilter['URL']))
		{
			$filter['URL'] = '%' . $requestFilter['URL'] . '%';
		}
		if (!empty($requestFilter['IP']))
		{
			$filter['IP'] = '%' . $requestFilter['IP'] . '%';
		}

		if (isset($this->arParams['AGREEMENT_ID']) && $this->arParams['AGREEMENT_ID'])
		{
			$filterOptions->reset();
			$filterOptions->setFilterSettings(
				FilterOptions::TMP_FILTER,
				[
					'fields' => [
						'AGREEMENT_ID' => $this->arParams['AGREEMENT_ID']
					]
				],
				true,
				false
			);
			$filterOptions->save();
			$filter['=AGREEMENT_ID'] = $this->arParams['AGREEMENT_ID'];
		}

		return $filter;
	}

	protected function setUiGridColumns()
	{
		$this->arResult['COLUMNS'] = $this->getUiGridColumns();
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
		$this->arResult['FILTERS'] = [
			[
				"id" => "USER.LAST_NAME",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_FILTER_USER_LAST_NAME'),
			],
			[
				"id" => "USER.NAME",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_FILTER_USER_NAME'),
			],
			[
				"id" => "USER.LOGIN",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_FILTER_USER_LOGIN'),
			],
			[
				"id" => "USER.EMAIL",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_FILTER_USER_MAIL'),
			],
			[
				"id" => "AGREEMENT_ID",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_AGREEMENT_ID'),
				"default" => true,
				"type" => "list",
				"items" => $agreements
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_DATE_INSERT'),
				"type" => "date",
				"default" => true
			],
			[
				"id" => "IP",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_IP'),
			],
			[
				"id" => "URL",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_URL'),
			],
		];
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

	private function setRowColumnItems(array &$data, array $items)
	{
		$data['ITEMS'] = Consent::getItems($data['ORIGINATOR_ID'], $items);
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
				'LOGIN' => $data['LOGIN'],
				'NAME' => $data['NAME'],
				'LAST_NAME' => $data['LAST_NAME'],
				'SECOND_NAME' => $data['SECOND_NAME']
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

	private function prepareRowsActions()
	{
		foreach ($this->arResult['ROWS'] as $index => $data)
		{
			if ($data['USER'] && $data['USER_PATH'])
			{
				$data['USER'] = '<a href="'.htmlspecialcharsbx($data['USER_PATH']).'" target="_blank">'
					. htmlspecialcharsbx($data['USER'])
					.'</a>';
			}

			if ($data['ORIGIN'] && $data['ORIGIN_PATH'])
			{
				$data['ORIGIN'] = '<a href="'.
					htmlspecialcharsbx(CUtil::JSEscape($data['ORIGIN_PATH'])).
					'" target="_blank">' .htmlspecialcharsbx($data['ORIGIN']) .'</a>';
			}
			else
			{
				$data['ORIGIN'] = '';
				if ($data['ORIGIN_ID'])
				{
					$data['ORIGIN'] .= htmlspecialcharsbx($data['ORIGIN_ID']);
				}
				if ($data['ORIGINATOR_ID'])
				{
					$data['ORIGIN'] .= $data['ORIGIN'] ? '<br>' : '';
					$data['ORIGIN'] .= '<span style="color: #C3C3C3;">' .
						htmlspecialcharsbx($data['ORIGINATOR_ID']) . '</span>';
				}
			}

			if ($data['URL'])
			{
				$styleString = 'max-width: 400px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;';
				$data['URL'] = '<div style="' . $styleString . '"><a href="' .
					htmlspecialcharsbx(CUtil::JSEscape($data['URL'])) . '" target="_blank">'
					.htmlspecialcharsbx($data['URL'])
					.'</a></div>';
			}

			$actions = array();
			$this->arResult['ROWS'][$index] = array(
				'id' => $data['ID'],
				'columns' => $data,
				'actions' => $actions
			);
		}
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
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_DATE_INSERT'),
				"default" => true,
				"sort" => "DATE_INSERT",
			],
			[
				"id" => "USER",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_USER'),
				"default" => true,
				"sort" => "USER.NAME",
			],
			[
				"id" => "IP",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_IP'),
				"default" => true
			],
			[
				"id" => "ORIGIN",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_ORIGIN'),
				"default" => true
			],
			[
				"id" => "ITEMS",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_ITEMS'),
				"default" => true
			],
			[
				"id" => "URL",
				"name" => Loc::getMessage('MAIN_USER_CONSENTS_COMP_UI_COLUMN_URL'),
				"default" => true
			],
		];
	}
}