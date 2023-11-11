<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\ContractorTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

\Bitrix\Main\Loader::includeModule('catalog');

class CatalogContractorList extends CBitrixComponent
{
	private const GRID_ID = 'catalog_contractor';
	private const FILTER_ID = 'catalog_contractor_filter';

	private $defaultGridSort = [
		'ID' => 'asc',
	];
	private $navParamName = 'page';

	/** @var \Bitrix\Catalog\Filter\DataProvider\ContractorDataProvider $itemProvider */
	private $itemProvider;
	/** @var \Bitrix\Main\Filter\Filter $filter */
	private $filter;

	public function onPrepareComponentParams($arParams)
	{
		if (!isset($arParams['PATH_TO']))
		{
			$arParams['PATH_TO'] = [];
		}
		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		if (!$this->checkReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CONTRACTOR_LIST_NO_VIEW_RIGHTS_ERROR');
			$this->includeComponentTemplate();
			return;
		}

		$this->init();

		$this->processAction();

		$this->arResult['GRID'] = $this->prepareGrid();
		$this->prepareToolbar();

		$this->arResult['PATH_TO'] = $this->arParams['PATH_TO'];

		$this->includeComponentTemplate();
	}

	private function init()
	{
		$this->itemProvider = new \Bitrix\Catalog\Filter\DataProvider\ContractorDataProvider();
		$this->filter = new \Bitrix\Main\Filter\Filter(self::FILTER_ID, $this->itemProvider);
	}

	private function prepareGrid()
	{
		$result = [];

		$gridId = self::GRID_ID;
		$result['GRID_ID'] = $gridId;
		$gridColumns = $this->itemProvider->getGridColumns();

		$gridOptions = new Bitrix\Main\Grid\Options($gridId);
		$navParams = $gridOptions->getNavParams();
		$pageSize = (int)$navParams['nPageSize'];
		$gridSort = $gridOptions->GetSorting(['sort' => $this->defaultGridSort]);

		$sortField = key($gridSort['sort']);
		foreach ($gridColumns as $key => $column)
		{
			if ($column['sort'] === $sortField)
			{
				$gridColumns[$key]['color'] = Bitrix\Main\Grid\Column\Color::BLUE;
				break;
			}
		}

		$result['COLUMNS'] = $gridColumns;

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$this->arResult['GRID']['ROWS'] = [];
		$listFilter = $this->getListFilter();
		$select = ['*'];

		$dbResult = ContractorTable::getList([
			'order' => $gridSort['sort'],
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'filter' => $listFilter,
			'select' => $select,
			'count_total' => true,
		]);

		$list = $dbResult->fetchAll();

		foreach($list as $item)
		{
			$result['ROWS'][] = [
				'id' => $item['ID'],
				'data' => $item,
				'columns' => $this->getItemColumns($item),
				'actions' => $this->getItemActions($item),
			];
		}

		$totalCount = $dbResult->getCount();

		$pageNavigation->setRecordCount($totalCount);
		$result['NAV_PARAM_NAME'] = $this->navParamName;
		$result['CURRENT_PAGE'] = $pageNavigation->getCurrentPage();
		$result['NAV_OBJECT'] = $pageNavigation;
		$result['TOTAL_ROWS_COUNT'] = $totalCount;
		$result['AJAX_MODE'] = 'Y';
		$result['ALLOW_ROWS_SORT'] = false;
		$result['AJAX_OPTION_JUMP'] = 'N';
		$result['AJAX_OPTION_STYLE'] = 'N';
		$result['AJAX_OPTION_HISTORY'] = 'N';
		$result['AJAX_ID'] = \CAjax::GetComponentID('bitrix:main.ui.grid', '', '');
		$result['SHOW_PAGINATION'] = $totalCount > 0;
		$result['SHOW_NAVIGATION_PANEL'] = true;
		$result['NAV_PARAM_NAME'] = 'page';
		$result['SHOW_PAGESIZE'] = true;
		$result['PAGE_SIZES'] = [['NAME' => 10, 'VALUE' => '10'], ['NAME' => 20, 'VALUE' => '20'], ['NAME' => 50, 'VALUE' => '50']];
		$result['SHOW_ROW_CHECKBOXES'] = true;
		$result['SHOW_CHECK_ALL_CHECKBOXES'] = true;
		$result['SHOW_ACTION_PANEL'] = true;
		$result['HANDLE_RESPONSE_ERRORS'] = true;

		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
		$removeButton = $snippet->getRemoveButton();
		$result['ACTION_PANEL'] = [
			'GROUPS' => [
				[
					'ITEMS' => [
						$removeButton,
					],
				],
			]
		];

		return $result;
	}

	private function getItemColumns($item)
	{
		$columns = $item;

		foreach ($columns as $fieldName => $value)
		{
			if ($fieldName === 'PERSON_TYPE')
			{
				$columns[$fieldName] = ContractorTable::getTypeDescriptions()[$value];
			}

			$columns[$fieldName] = htmlspecialcharsbx($columns[$fieldName]);
		}

		return $columns;
	}

	private function getItemActions($item)
	{
		$actions = [
			[
				'TITLE' => Loc::getMessage('CONTRACTOR_LIST_ACTION_OPEN_TITLE'),
				'TEXT' => Loc::getMessage('CONTRACTOR_LIST_ACTION_OPEN_TEXT'),
				'ONCLICK' => "openContractorSlider({$item['ID']})",
				'DEFAULT' => true,
			],
		];

		if ($item['IS_DEFAULT'] !== 'Y')
		{
			$deletePostParams = [
				'action' => 'delete',
				'contractorId' => $item['ID'],
			];
			$deletePostParams = CUtil::PhpToJSObject($deletePostParams);
			$actions[] = [
				'TITLE' => Loc::getMessage('CONTRACTOR_LIST_ACTION_DELETE_TITLE'),
				'TEXT' => Loc::getMessage('CONTRACTOR_LIST_ACTION_DELETE_TEXT'),
				'ONCLICK' => "if (confirm('" . CUtil::JSEscape(Loc::getMessage('CONTRACTOR_LIST_ACTION_DELETE_CONFIRM')) . "')) BX.Main.gridManager.getInstanceById('catalog_contractor').reloadTable('POST', $deletePostParams)",
			];
		}

		return $actions;
	}

	private function prepareToolbar()
	{
		$filterOptions = [
			'GRID_ID' => self::GRID_ID,
			'FILTER_ID' => $this->filter->getID(),
			'FILTER' => $this->filter->getFieldArrays(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => Bitrix\Main\UI\Filter\Theme::LIGHT,
		];
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterOptions);

		$addContractorButton = \Bitrix\UI\Buttons\CreateButton::create([
			'text' => Loc::getMessage('CONTRACTOR_LIST_ADD_CONTRACTOR_BUTTON'),
			'color' => \Bitrix\UI\Buttons\Color::PRIMARY,
			'onclick' => 'openContractorCreation',
		]);
		\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addContractorButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
	}

	private function getListFilter()
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filter->getID());
		$filterFields = $this->filter->getFieldArrays();

		$filter = $filterOptions->getFilterLogic($filterFields);

		$searchString = $filterOptions->getSearchString();
		if ($searchString)
		{
			$filter['PERSON_NAME'] = '%' . $searchString . '%';
		}

		return $filter;
	}

	private function processAction()
	{
		$this->arResult['ERROR_MESSAGES'] = [];

		$action = $this->request->get('action');
		$groupAction = $this->request->get('action_button_' . self::GRID_ID);
		if (!$action && !$groupAction)
		{
			return;
		}

		if (!$this->checkWriteRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CONTRACTOR_LIST_NO_WRITE_RIGHTS_ERROR');
		}
		elseif ($action)
		{
			$this->processSingleAction($action);
		}
		elseif ($groupAction)
		{
			$this->processGroupAction($groupAction);
		}

		if (!empty($this->arResult['ERROR_MESSAGES']))
		{
			$this->endResponseWithErrors();
		}
	}

	private function processSingleAction($action)
	{
		if ($action === 'delete')
		{
			$contractorId = $this->request->get('contractorId');
			if (!$contractorId)
			{
				return;
			}

			global $APPLICATION;
			$APPLICATION->ResetException();

			CCatalogContractor::Delete($contractorId);

			if ($APPLICATION->GetException())
			{
				$this->arResult['ERROR_MESSAGES'][] = $APPLICATION->GetException()->GetString();
			}
		}
	}

	private function processGroupAction($action)
	{
		if ($action === 'delete' && is_array($this->request->get('ID')))
		{
			foreach ($this->request->get('ID') as $contractorId)
			{
				global $APPLICATION;
				$APPLICATION->ResetException();

				CCatalogContractor::Delete($contractorId);

				if ($APPLICATION->GetException())
				{
					$this->arResult['ERROR_MESSAGES'][] = $APPLICATION->GetException()->GetString();
				}
			}
		}
	}

	private function endResponseWithErrors()
	{
		$messages = [];

		foreach ($this->arResult['ERROR_MESSAGES'] as $error)
		{
			$messages[] = [
				'TYPE' => Bitrix\Main\Grid\MessageType::ERROR,
				'TEXT' => $error,
			];
		}

		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		CMain::FinalActions(Json::encode(['messages' => $messages]));
	}

	private function checkReadRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS);
	}

	private function checkWriteRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS);
	}
}
