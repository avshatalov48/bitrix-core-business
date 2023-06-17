<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

\Bitrix\Main\Loader::includeModule('catalog');

class CatalogStoreAdminList extends CBitrixComponent
{
	private const GRID_ID = 'catalog_store';
	private const FILTER_ID = 'catalog_store_filter';

	private $defaultGridSort = [
		'SORT' => 'asc',
	];
	private $navParamName = 'page';

	/** @var \Bitrix\Catalog\Grid\Filter\StoreDataProvider $itemProvider */
	private $itemProvider;
	/** @var \Bitrix\Main\Filter\Filter $filter */
	private $filter;

	/** @var AccessController */
	private $accessController;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->accessController = AccessController::getCurrent();
	}

	public function executeComponent()
	{
		if (!$this->checkStoreAccessRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_NO_VIEW_RIGHTS_ERROR');
			$this->includeComponentTemplate();
			return;
		}

		$this->init();

		$this->processAction();

		$this->arResult['GRID'] = $this->prepareGrid();
		$this->prepareToolbar();

		$this->arResult['PATH_TO'] = $this->arParams['PATH_TO'] ?? [];
		$this->arResult['TARIFF_HELP_LINK'] = Catalog\Config\Feature::getMultiStoresHelpLink();

		$this->includeComponentTemplate();
	}

	private function init()
	{
		$this->itemProvider = new \Bitrix\Catalog\Grid\Filter\StoreDataProvider();
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
		$select = array_merge(['*'], $this->getUserSelectColumns($this->getUserReferenceColumns()));

		$list = StoreTable::getList([
			'order' => $gridSort['sort'],
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'filter' => $listFilter,
			'select' => $select,
		])->fetchAll();

		$isEditable = $this->checkStoreModifyRights();
		foreach($list as $item)
		{
			$result['ROWS'][] = [
				'id' => $item['ID'],
				'data' => $item,
				'editable' => $isEditable,
				'columns' => $this->getItemColumns($item),
				'actions' => $this->getItemActions($item, $isEditable),
			];
		}

		$totalCount = $this->getTotalCount();

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

	private function getTotalCount(): int
	{
		return StoreTable::getCount($this->getListFilter());
	}

	private function getUserReferenceColumns()
	{
		return ['CREATED_BY_USER', 'MODIFIED_BY_USER'];
	}

	private function getUserSelectColumns($userReferenceNames)
	{
		$result = [];
		$fieldsToSelect = ['NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN'];

		foreach ($userReferenceNames as $userReferenceName)
		{
			foreach ($fieldsToSelect as $field)
			{
				$result[$userReferenceName . '_' . $field] = $userReferenceName . '.' . $field;
			}
		}

		return $result;
	}

	private function getItemColumns($item)
	{
		$columns = $item;

		foreach ($columns as $fieldName => $value)
		{
			$checkboxFields = ['ACTIVE', 'ISSUING_CENTER', 'SHIPPING_CENTER', 'IS_DEFAULT'];
			if (in_array($fieldName, $checkboxFields))
			{
				$columns[$fieldName] = $value === 'Y' ? Loc::getMessage('MAIN_YES') : Loc::getMessage('MAIN_NO');
			}

			if ($fieldName === 'IMAGE_ID' && $value)
			{
				$columns[$fieldName] = CFile::ShowImage($value, 100, 100, 'border=0', '', true);
			}

			if ($fieldName === 'USER_ID' && $value)
			{
				$columns['USER_ID'] = $this->getUserDisplay($item, $value, 'CREATED_BY_USER');
			}

			if ($fieldName === 'MODIFIED_BY' && $value)
			{
				$columns['MODIFIED_BY'] = $this->getUserDisplay($item, $value, 'MODIFIED_BY_USER');
			}

			if ($fieldName === 'SITE_ID' && $value)
			{
				$columns['SITE_ID'] = $this->getSiteTitle($value);
			}

			$htmlFields = ['IMAGE_ID', 'USER_ID', 'MODIFIED_BY'];
			if (!in_array($fieldName, $htmlFields))
			{
				$columns[$fieldName] = htmlspecialcharsbx($columns[$fieldName]);
			}
		}

		return $columns;
	}

	private function getItemActions($item, bool $isEditable): array
	{
		$gridId = htmlspecialcharsbx(self::GRID_ID);

		$actions = [
			[
				'TITLE' => Loc::getMessage('STORE_LIST_ACTION_OPEN_TITLE_2'),
				'TEXT' => Loc::getMessage('STORE_LIST_ACTION_OPEN_TEXT_2'),
				'ONCLICK' => "openStoreSlider({$item['ID']})",
				'DEFAULT' => true,
			],
		];

		if ($isEditable)
		{
			$activatePostParams = CUtil::PhpToJSObject([
				'action' => 'activate',
				'storeId' => $item['ID'],
			]);
			$activateAction = [
				'TITLE' => Loc::getMessage('STORE_LIST_ACTION_ACTIVATE_TITLE'),
				'TEXT' => Loc::getMessage('STORE_LIST_ACTION_ACTIVATE_TEXT'),
				'ONCLICK' => "BX.Main.gridManager.getInstanceById('{$gridId}').reloadTable('POST', $activatePostParams)",
			];

			if ($item['IS_DEFAULT'] !== 'Y')
			{
				if ($item['ACTIVE'] !== 'Y')
				{
					$actions[] = $activateAction;
				}
				else
				{
					$deactivatePostParams = CUtil::PhpToJSObject([
						'action' => 'deactivate',
						'storeId' => $item['ID'],
					]);
					$actions[] = [
						'TITLE' => Loc::getMessage('STORE_LIST_ACTION_DEACTIVATE_TITLE'),
						'TEXT' => Loc::getMessage('STORE_LIST_ACTION_DEACTIVATE_TEXT'),
						'ONCLICK' => "BX.Main.gridManager.getInstanceById('{$gridId}').reloadTable('POST', $deactivatePostParams)",
					];
				}

				$setAsDefaultPostParams = CUtil::PhpToJSObject([
					'action' => 'setdefault',
					'storeId' => $item['ID'],
				]);
				$actions[] = [
					'TITLE' => Loc::getMessage('STORE_LIST_ACTION_SET_AS_DEFAULT_TITLE'),
					'TEXT' => Loc::getMessage('STORE_LIST_ACTION_SET_AS_DEFAULT_TEXT'),
					'ONCLICK' => "BX.Main.gridManager.getInstanceById('{$gridId}').reloadTable('POST', $setAsDefaultPostParams)",
				];

				$deletePostParams = CUtil::PhpToJSObject([
					'action' => 'delete',
					'storeId' => $item['ID'],
				]);
				$actions[] = [
					'TITLE' => Loc::getMessage('STORE_LIST_ACTION_DELETE_TITLE'),
					'TEXT' => Loc::getMessage('STORE_LIST_ACTION_DELETE_TEXT'),
					'ONCLICK' => "if (confirm('" . CUtil::JSEscape(Loc::getMessage('STORE_LIST_ACTION_DELETE_CONFIRM')) . "')) BX.Main.gridManager.getInstanceById('{$gridId}').reloadTable('POST', $deletePostParams)",
				];
			}
			else
			{
				if ($item['ACTIVE'] !== 'Y')
				{
					$actions[] = $activateAction;
				}
			}
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
			'CONFIG' => [
				'AUTOFOCUS' => false,
			]
		];
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterOptions);

		$button = null;
		if ($this->checkStoreModifyRights())
		{
			$buttonConfig = null;
			if (Catalog\Config\State::isAllowedNewStore())
			{
				$buttonConfig = [
					'onclick' => 'openStoreCreation',
				];
			}
			else
			{
				$helpLink = Catalog\Config\Feature::getMultiStoresHelpLink();
				if (!empty($helpLink))
				{
					\Bitrix\Main\Loader::includeModule('ui');
					\Bitrix\Main\UI\Extension::load(['ui.info-helper']);
					$buttonConfig = [
						'click' => 'openTariffHelp',
					];
				}
				unset($helpLink);
			}

			if ($buttonConfig)
			{
				$buttonConfig['text'] = Loc::getMessage('STORE_LIST_ADD_STORE_BUTTON');
				$buttonConfig['color'] = \Bitrix\UI\Buttons\Color::PRIMARY;
				$button = \Bitrix\UI\Buttons\CreateButton::create($buttonConfig);
			}
		}
		else
		{
			$button = \Bitrix\UI\Buttons\LockedButton::create([
				'text' => Loc::getMessage('STORE_LIST_ADD_STORE_BUTTON'),
				'hint' => Loc::getMessage('STORE_LIST_ADD_STORE_BUTTON_DENIED_HINT'),
				'color' => \Bitrix\UI\Buttons\Color::PRIMARY,
			]);
		}

		if ($button)
		{
			\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($button, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
		}
	}

	private function getUserDisplay($column, $userId, $userReferenceName)
	{
		$userName = \CUser::FormatName(
			\CSite::GetNameFormat(false),
			[
				'LOGIN' => $column[$userReferenceName . '_LOGIN'],
				'NAME' => $column[$userReferenceName . '_NAME'],
				'LAST_NAME' => $column[$userReferenceName . '_LAST_NAME'],
				'SECOND_NAME' => $column[$userReferenceName . '_SECOND_NAME'],
			],
			true
		);

		return "<a href=\"/company/personal/user/{$userId}/\">{$userName}</a>";
	}

	private function getSiteTitle($siteId)
	{
		static $sites = null;
		$siteTitle = $siteId;

		if (is_null($sites))
		{
			$sites = [];
			$sitesResult = CSite::GetList('id', 'asc', ['ACTIVE' => 'Y']);
			while($site = $sitesResult->GetNext())
			{
				$sites[] = ['ID' => $site['ID'], 'NAME' => $site['NAME']];
			}
		}

		foreach($sites as $site)
		{
			if ($site['ID'] == $siteId)
			{
				$siteTitle = Loc::getMessage('STORE_LIST_SITE_NAME_TEMPLATE', ['#SITE_NAME#' => $site['NAME'], '#SITE_ID#' => $site['ID']]);
				break;
			}
		}

		return $siteTitle;
	}

	private function getListFilter()
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filter->getID());
		$filterFields = $this->filter->getFieldArrays();

		$filter = $filterOptions->getFilterLogic($filterFields);

		$searchString = $filterOptions->getSearchString();
		if ($searchString)
		{
			$filter['TITLE'] = '%' . $searchString . '%';
		}

		$allowedStores = $this->accessController->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW) ?? [];
		if (!in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true))
		{
			$filter['=ID'] = $allowedStores;
		}

		return $filter;
	}

	private function processAction()
	{
		$this->arResult['ERROR_MESSAGES'] = [];
		if (!$this->checkStoreAccessRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_NO_VIEW_RIGHTS_ERROR');
			$this->endResponseWithErrors();
		}

		$action = $this->request->get('action');
		if ($action)
		{
			$this->processSingleAction($action);
		}

		$groupAction = $this->request->get('action_button_' . self::GRID_ID);
		if ($groupAction)
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
		if (!$this->checkStoreModifyRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_NO_VIEW_RIGHTS_ERROR');

			return;
		}

		if ($action === 'delete')
		{
			$storeId = $this->request->get('storeId');
			if (!$storeId)
			{
				return;
			}

			$isDefaultStore = StoreTable::getList([
				'select' => ['IS_DEFAULT'],
				'filter' => ['=ID' => $storeId],
				'limit' => 1,
			])->fetch()['IS_DEFAULT'] === 'Y';

			if ($isDefaultStore)
			{
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_DEFAULT_STORE_DELETE_ERROR');
				return;
			}

			global $APPLICATION;
			$APPLICATION->ResetException();

			$isSuccess = CCatalogStore::Delete($storeId);

			if (!$isSuccess)
			{
				if ($APPLICATION->GetException())
				{
					$this->arResult['ERROR_MESSAGES'][] = $APPLICATION->GetException()->GetString();
				}
				else
				{
					$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_DELETE_ERROR', ['#ID#' => $storeId]);
				}
			}

			return;
		}

		if ($action === 'activate')
		{
			$storeId = $this->request->get('storeId');
			if (!$storeId)
			{
				return;
			}

			global $APPLICATION;
			$APPLICATION->ResetException();

			$isSuccess = CCatalogStore::Update($storeId, ['ACTIVE' => 'Y']);

			if (!$isSuccess)
			{
				if ($APPLICATION->GetException())
				{
					$this->arResult['ERROR_MESSAGES'][] = $APPLICATION->GetException()->GetString();
				}
				else
				{
					$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_ACTIVATE_ERROR', ['#ID#' => $storeId]);
				}
			}

			return;
		}

		if ($action === 'deactivate')
		{
			$storeId = $this->request->get('storeId');
			if (!$storeId)
			{
				return;
			}

			$isDefaultStore = StoreTable::getList([
				'select' => ['IS_DEFAULT'],
				'filter' => ['=ID' => $storeId],
				'limit' => 1,
			])->fetch()['IS_DEFAULT'] === 'Y';

			if ($isDefaultStore)
			{
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_DEFAULT_STORE_DEACTIVATE_ERROR');
				return;
			}

			global $APPLICATION;
			$APPLICATION->ResetException();

			$isSuccess = CCatalogStore::Update($storeId, ['ACTIVE' => 'N']);

			if (!$isSuccess)
			{
				if ($APPLICATION->GetException())
				{
					$this->arResult['ERROR_MESSAGES'][] = $APPLICATION->GetException()->GetString();
				}
				else
				{
					$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_DEACTIVATE_ERROR', ['#ID#' => $storeId]);
				}
			}

			return;
		}

		if ($action === 'setdefault')
		{
			$storeId = $this->request->get('storeId');
			if (!$storeId)
			{
				return;
			}

			$defaultStoreId = (int)StoreTable::getDefaultStoreId();
			if ((int)$storeId === $defaultStoreId)
			{
				return;
			}

			$store = StoreTable::getById($storeId)->fetch();
			if (!$store)
			{
				return;
			}

			if ($store['ACTIVE'] !== 'Y')
			{
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_SET_AS_DEFAULT_NON_ACTIVE_ERROR');
				return;
			}

			$siteId = (string)$store['SITE_ID'];
			$allowedStoreSite = '';
			$siteCount = \Bitrix\Main\SiteTable::getCount([
				'=ACTIVE' => 'Y',
			]);
			if ($siteCount === 1)
			{
				$iterator = \Bitrix\Main\SiteTable::getList([
					'select' => ['LID'],
					'filter' => ['=ACTIVE' => 'Y'],
				]);
				$row = $iterator->fetch();
				$allowedStoreSite = $row['LID'];
				unset($row, $iterator);
			}
			unset($siteCount);
			if ($siteId !== '' && $siteId !== $allowedStoreSite)
			{
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_SET_AS_DEFAULT_SITE_ERROR');
				return;
			}

			global $DB;
			$DB->StartTransaction();
			$unsetCurrentDefaultStoreResult = StoreTable::update($defaultStoreId, ['IS_DEFAULT' => 'N']);
			if (!$unsetCurrentDefaultStoreResult->isSuccess())
			{
				$DB->Rollback();
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage(
					'STORE_LIST_ACTION_SET_AS_DEFAULT_ERROR',
					['#ERROR#' => implode('; ', $unsetCurrentDefaultStoreResult->getErrorMessages())]
				);
				return;
			}

			$setNewDefaultStoreResult = StoreTable::update($storeId, ['IS_DEFAULT' => 'Y']);
			if (!$setNewDefaultStoreResult->isSuccess())
			{
				$DB->Rollback();
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage(
					'STORE_LIST_ACTION_SET_AS_DEFAULT_ERROR',
					['#ERROR#' => implode('; ', $unsetCurrentDefaultStoreResult->getErrorMessages())]
				);
				return;
			}

			$DB->Commit();
		}
	}

	private function processGroupAction($action)
	{
		if (!$this->checkStoreModifyRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_NO_VIEW_RIGHTS_ERROR');

			return;
		}

		if ($action === 'delete' && is_array($this->request->get('ID')))
		{
			global $APPLICATION;

			$defaultStoreId = StoreTable::getDefaultStoreId();

			foreach ($this->request->get('ID') as $storeId)
			{
				$isDefaultStore = $defaultStoreId === (int)$storeId;
				if ($isDefaultStore)
				{
					$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_LIST_ACTION_DEFAULT_STORE_DELETE_ERROR');
					continue;
				}

				$APPLICATION->ResetException();

				CCatalogStore::Delete($storeId);

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

	private function checkStoreAccessRights(): bool
	{
		return
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& $this->accessController->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS)
			&& $this->accessController->check(ActionDictionary::ACTION_STORE_VIEW)
		;
	}

	private function checkStoreModifyRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_MODIFY);
	}
}
