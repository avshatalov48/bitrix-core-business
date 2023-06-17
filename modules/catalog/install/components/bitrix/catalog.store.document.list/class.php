<?php

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\Url\InventoryManagementSourceBuilder;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Buttons\CreateButton;
use Bitrix\UI\Buttons\LockedButton;
use Bitrix\Catalog\v2\Contractor\Provider\Manager;
use Bitrix\Catalog\ContractorTable;
use Bitrix\Catalog\StoreTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loader::includeModule('catalog');
Loader::includeModule('currency');

class CatalogStoreDocumentListComponent extends CBitrixComponent implements Controllerable
{
	private const GRID_ID = 'catalog_store_documents';
	private const FILTER_ID = 'catalog_store_documents_filter';

	public const ARRIVAL_MODE = 'receipt_adjustment';
	public const MOVING_MODE = 'moving';
	public const DEDUCT_MODE = 'deduct';
	/**
	 * @deprecated not used
	 */
	public const OTHER_MODE = 'other';

	private $defaultGridSort = [
		'DATE_MODIFY' => 'desc',
	];
	private $navParamName = 'page';

	/** @var \Bitrix\Catalog\Grid\Filter\DocumentDataProvider $itemProvider */
	private $itemProvider;
	/** @var \Bitrix\Main\Filter\Filter $filter */
	private $filter;
	/** @var array $contractors */
	private $contractors;
	/** @var array $stores */
	private $stores;
	/** @var string $mode */
	private $mode;
	/** @var array $documentStores */
	private $documentStores;

	/** @var AccessController */
	private $accessController;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->accessController = AccessController::getCurrent();
	}

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
		$crmIncluded = Loader::includeModule('crm');
		if ($crmIncluded)
		{
			CCrmInvoice::installExternalEntities();
		}

		$this->init();
		if (!$this->checkDocumentReadRights())
		{
			if ($crmIncluded)
			{
				$this->arResult['ERROR_MESSAGES'][] = [
					'TITLE' => Loc::getMessage(
						'DOCUMENT_LIST_ERR_ACCESS_DENIED',
						['#DOCUMENT_TYPE_NAME#' => $this->getModeName()]
					),
					'HELPER_CODE' => 15955386,
					'LESSON_ID' => 25010,
					'COURSE_ID' => 48,
				];
			}
			else
			{
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('DOCUMENT_LIST_NO_VIEW_RIGHTS_ERROR');
			}
			$this->includeComponentTemplate();

			return;
		}
		$this->arResult['GRID'] = $this->prepareGrid();
		$this->arResult['FILTER_ID'] = $this->getFilterId();
		$this->prepareToolbar();
		$this->arResult['IS_SHOW_GUIDE'] = $this->isShowGuide();

		$this->arResult['PATH_TO'] = $this->arParams['PATH_TO'];

		$this->arResult['INVENTORY_MANAGEMENT_SOURCE'] =
			InventoryManagementSourceBuilder::getInstance()->getInventoryManagementSource()
		;

		$this->initInventoryManagementSlider();

		$this->includeComponentTemplate();
	}

	/**
	 * Localized mode name.
	 *
	 * @return string
	 */
	private function getModeName(): string
	{
		if ($this->mode === self::ARRIVAL_MODE)
		{
			$type = StoreDocumentTable::TYPE_ARRIVAL;
		}
		elseif ($this->mode === self::MOVING_MODE)
		{
			$type = StoreDocumentTable::TYPE_MOVING;
		}
		elseif ($this->mode === self::DEDUCT_MODE)
		{
			$type = StoreDocumentTable::TYPE_DEDUCT;
		}
		else
		{
			return '';
		}

		return (string)Loc::getMessage('DOCUMENT_LIST_DOC_TYPE_' . $type);
	}

	private function checkDocumentAccessRights(string $action): bool
	{
		if (
			!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			|| !$this->accessController->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS)
		)
		{
			return false;
		}

		switch ($this->mode)
		{
			case self::ARRIVAL_MODE:
				return
					$this->accessController->checkByValue($action, StoreDocumentTable::TYPE_ARRIVAL)
					|| $this->accessController->checkByValue($action, StoreDocumentTable::TYPE_STORE_ADJUSTMENT)
					;

			case self::MOVING_MODE:
				return $this->accessController->checkByValue(
					$action,
					StoreDocumentTable::TYPE_MOVING
				);

			case self::DEDUCT_MODE:
				return $this->accessController->checkByValue(
					$action,
					StoreDocumentTable::TYPE_DEDUCT
				);

			case self::OTHER_MODE:
				return
					$this->accessController->checkByValue($action, StoreDocumentTable::TYPE_RETURN)
					|| $this->accessController->checkByValue($action, StoreDocumentTable::TYPE_UNDO_RESERVE)
					;
		}

		return false;
	}

	private function checkDocumentReadRights(): bool
	{
		return $this->checkDocumentAccessRights(ActionDictionary::ACTION_STORE_DOCUMENT_VIEW);
	}

	private function checkDocumentModifyRights(): bool
	{
		return $this->checkDocumentAccessRights(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY);
	}

	private function checkDocumentDeleteRights(): bool
	{
		return $this->checkDocumentAccessRights(ActionDictionary::ACTION_STORE_DOCUMENT_DELETE);
	}

	private function checkDocumentConductRights(): bool
	{
		return $this->checkDocumentAccessRights(ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT);
	}

	private function checkDocumentCancelRights(): bool
	{
		return $this->checkDocumentAccessRights(ActionDictionary::ACTION_STORE_DOCUMENT_CANCEL);
	}

	public function configureActions()
	{
	}

	private function getFilterId(): string
	{
		return self::FILTER_ID . '_' . $this->mode;
	}

	private function getGridId(): string
	{
		return self::GRID_ID . '_' . $this->mode;
	}

	private function init()
	{
		$this->initMode();

		$this->itemProvider = new \Bitrix\Catalog\Grid\Filter\DocumentDataProvider($this->mode);
		$this->filter = new \Bitrix\Main\Filter\Filter($this->getFilterId(), $this->itemProvider);
	}

	private function initMode()
	{
		if ($this->arParams['MODE'] === self::ARRIVAL_MODE)
		{
			$this->mode = self::ARRIVAL_MODE;
		}
		elseif ($this->arParams['MODE'] === self::MOVING_MODE)
		{
			$this->mode = self::MOVING_MODE;
		}
		elseif ($this->arParams['MODE'] === self::DEDUCT_MODE)
		{
			$this->mode = self::DEDUCT_MODE;
		}
		elseif ($this->arParams['MODE'] === self::OTHER_MODE)
		{
			$this->mode = self::OTHER_MODE;
			// TODO: remove this hack after refactoring OTHER document section
			\Bitrix\Main\UI\Extension::load([
				'admin_interface',
				'sidepanel'
			]);
		}
		else
		{
			// todo: get first valid item from the menu?
			$this->mode = self::ARRIVAL_MODE;
		}

		$this->arResult['MODE'] = $this->mode;
	}

	private function prepareGrid(): array
	{
		$result = [];

		$gridId = $this->getGridId();
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
		switch ($sortField)
		{
			case 'STATUS':
				$direction = $gridSort['sort'][$sortField];
				$gridSort['sort'] = [
					'STATUS' => $direction,
					'WAS_CANCELLED' => $direction,
					'ID' => 'DESC',
				];
				break;
			case 'ID':
				break;
			default:
				$gridSort['sort']['ID'] = 'DESC';
				break;
		}

		$result['COLUMNS'] = $gridColumns;

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$this->arResult['GRID']['ROWS'] = [];
		$listFilter = $this->getListFilter();
		$filteredProducts = [];
		if (!empty($listFilter['PRODUCTS']))
		{
			$filteredProducts = $listFilter['PRODUCTS'];
			unset($listFilter['PRODUCTS']);
		}
		$filteredStores = [];
		if (!empty($listFilter['STORES']))
		{
			$filteredStores = $listFilter['STORES'];
			unset($listFilter['STORES']);
		}
		$filteredStoresFrom = [];
		if (!empty($listFilter['STORES_FROM']))
		{
			$filteredStoresFrom = $listFilter['STORES_FROM'];
			unset($listFilter['STORES_FROM']);
		}
		$filteredStoresTo = [];
		if (!empty($listFilter['STORES_TO']))
		{
			$filteredStoresTo = $listFilter['STORES_TO'];
			unset($listFilter['STORES_TO']);
		}
		$select = array_merge(['*'], $this->getUserSelectColumns($this->getUserReferenceColumns()));
		$query = StoreDocumentTable::query()
			->setOrder($gridSort['sort'])
			->setOffset($pageNavigation->getOffset())
			->setLimit($pageNavigation->getLimit())
			->setFilter($listFilter)
			->setSelect($select);
		if (!empty($filteredProducts))
		{
			$query->withProductList($filteredProducts);
		}
		if (!empty($filteredStores))
		{
			$query->withStoreList($filteredStores);
		}
		if (!empty($filteredStoresFrom))
		{
			$query->withStoreFromList($filteredStoresFrom);
		}
		if (!empty($filteredStoresTo))
		{
			$query->withStoreToList($filteredStoresTo);
		}
		$list = $query->fetchAll();
		$totalCount = $query->queryCountTotal();
		if($totalCount > 0)
		{
			$this->loadDocumentStores(array_column($list, 'ID'));
			foreach($list as $item)
			{
				$result['ROWS'][] = [
					'id' => $item['ID'],
					'data' => $item,
					'columns' => $this->getItemColumn($item),
					'actions' => $this->getItemActions($item),
					'editable' => $this->accessController->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $item['DOC_TYPE'])
				];
			}
		}
		elseif ($this->mode !== self::OTHER_MODE)
		{
			$result['STUB'] = $this->getStub();
		}

		$pageNavigation->setRecordCount($totalCount);
		$result['NAV_PARAM_NAME'] = $this->navParamName;
		$result['CURRENT_PAGE'] = $pageNavigation->getCurrentPage();
		$result['NAV_OBJECT'] = $pageNavigation;
		$result['TOTAL_ROWS_COUNT'] = $totalCount;
		$result['AJAX_MODE'] = 'Y';
		$result['ALLOW_ROWS_SORT'] = false;
		$result['AJAX_OPTION_JUMP'] = "N";
		$result['AJAX_OPTION_STYLE'] = "N";
		$result['AJAX_OPTION_HISTORY'] = "N";
		$result['AJAX_ID'] = \CAjax::GetComponentID("bitrix:main.ui.grid", '', '');
		$result['SHOW_PAGINATION'] = $totalCount > 0;
		$result['SHOW_NAVIGATION_PANEL'] = true;
		$result['SHOW_PAGESIZE'] = true;
		$result['PAGE_SIZES'] = [['NAME' => 10, 'VALUE' => '10'], ['NAME' => 20, 'VALUE' => '20'], ['NAME' => 50, 'VALUE' => '50']];
		$result['SHOW_ROW_CHECKBOXES'] = true;
		$result['SHOW_CHECK_ALL_CHECKBOXES'] = true;
		$result['SHOW_ACTION_PANEL'] = true;

		$actionPanelItems = [];
		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
		if ($this->checkDocumentDeleteRights())
		{
			$removeButton = $snippet->getRemoveButton();
			$snippet->setButtonActions($removeButton, [
				[
					'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'CONFIRM' => true,
					'CONFIRM_APPLY_BUTTON' => Loc::getMessage('DOCUMENT_LIST_ACTION_DELETE_TEXT'),
					'DATA' => [
						[
							'JS' => 'BX.Catalog.DocumentGridManager.Instance.deleteSelectedDocuments()'
						],
					],
				]
			]);

			$actionPanelItems[] = $removeButton;
		}

		$dropdownActions = [];
		if ($this->checkDocumentConductRights())
		{
			$dropdownActions[] = [
				'NAME' => Loc::getMessage('DOCUMENT_LIST_CONDUCT_GROUP_ACTION'),
				'VALUE' => 'conduct',
			];
		}

		if ($this->checkDocumentCancelRights())
		{
			$dropdownActions[] = [
				'NAME' => Loc::getMessage('DOCUMENT_LIST_CANCEL_GROUP_ACTION'),
				'VALUE' => 'cancel',
			];
		}

		if ($dropdownActions)
		{
			array_unshift($dropdownActions, [
				'NAME' => Loc::getMessage('DOCUMENT_LIST_SELECT_GROUP_ACTION'),
				'VALUE' => 'none',
			]);

			$dropdownActionsButton = [
				'TYPE' => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
				'ID' => 'action_button_'. $this->getGridId(),
				'NAME' => 'action_button_'. $this->getGridId(),
				'ITEMS' => $dropdownActions,
			];

			$actionPanelItems[] = $dropdownActionsButton;

			$applyButton = $snippet->getApplyButton([
				'ONCHANGE' => [
					[
						'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
						'DATA' => [
							[
								'JS' => 'BX.Catalog.DocumentGridManager.Instance.processApplyButtonClick()',
							]
						]
					]
				]
			]);
			$actionPanelItems[] = $applyButton;
		}



		$result['ACTION_PANEL'] = [
			'GROUPS' => [
				[
					'ITEMS' => $actionPanelItems,
				],
			]
		];

		return $result;
	}

	private function getUserReferenceColumns(): array
	{
		return ['RESPONSIBLE', 'CREATED_BY_USER', 'MODIFIED_BY_USER', 'STATUS_BY_USER'];
	}

	private function getUserSelectColumns($userReferenceNames): array
	{
		$result = [];
		$fieldsToSelect = ['LOGIN', 'PERSONAL_PHOTO', 'NAME', 'SECOND_NAME', 'LAST_NAME'];

		foreach ($userReferenceNames as $userReferenceName)
		{
			foreach ($fieldsToSelect as $field)
			{
				$result[$userReferenceName . '_' . $field] = $userReferenceName . '.' . $field;
			}
		}

		return $result;
	}

	private function getItemActions($item): array
	{
		$labelText = $item['DOC_TYPE'] === StoreDocumentTable::TYPE_STORE_ADJUSTMENT
			? Loc::getMessage('DOCUMENT_LIST_DOC_TYPE_A')
			: Loc::getMessage('DOCUMENT_LIST_DOC_TYPE_' . $item['DOC_TYPE']);
		$urlToDocumentDetail = $this->getUrlToDocumentDetail($item['ID']);
		$sliderOptions = [
			'cacheable' => false,
		];
		if ($this->mode !== self::OTHER_MODE)
		{
			$sliderOptions['loader'] = 'crm-entity-details-loader';
			$sliderOptions['customLeftBoundary'] = 0;
			$sliderOptions['label'] = ['text' => $labelText];
		}
		$sliderOptions = \CUtil::PhpToJSObject($sliderOptions,false, false, true);
		$actions = [
			[
				'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_OPEN_TITLE'),
				'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_OPEN_TEXT'),
				'ONCLICK' => "BX.SidePanel.Instance.open('" . $urlToDocumentDetail . "', " . $sliderOptions . ")",
				'DEFAULT' => true,
			],
		];
		if ($item['STATUS'] === 'N')
		{
			if ($this->accessController->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT, $item['DOC_TYPE']))
			{
				$actions[] = [
					'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_CONDUCT_TITLE'),
					'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_CONDUCT_TEXT'),
					'ONCLICK' => "BX.Catalog.DocumentGridManager.Instance.conductDocument(" . $item['ID'] . ", '" . $item['DOC_TYPE'] . "')",
				];
			}

			if ($this->accessController->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_DELETE, $item['DOC_TYPE']))
			{
				$actions[] = [
					'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_DELETE_TITLE'),
					'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_DELETE_TEXT'),
					'ONCLICK' => "BX.Catalog.DocumentGridManager.Instance.deleteDocument(" . $item['ID'] . ")",
				];
			}
		}
		else
		{
			if ($this->accessController->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_CANCEL, $item['DOC_TYPE']))
			{
				$actions[] = [
					'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_CANCEL_TITLE'),
					'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_CANCEL_TEXT'),
					'ONCLICK' => "BX.Catalog.DocumentGridManager.Instance.cancelDocument(" . $item['ID'] . ", '" . $item['DOC_TYPE'] . "')",
				];
			}
		}

		return $actions;
	}

	private function getStub()
	{
		if ($this->isUserFilterApplied() && $this->getTotalCountWithoutUserFilter() > 0)
		{
			return [
				'title' => Loc::getMessage('DOCUMENT_LIST_STUB_NO_DATA_TITLE'),
				'description' => Loc::getMessage('DOCUMENT_LIST_STUB_NO_DATA_DESCRIPTION'),
			];
		}

		switch ($this->mode)
		{
			case self::ARRIVAL_MODE:
				return '
				<div class="main-grid-empty-block-title">' . Loc::getMessage('DOCUMENT_LIST_STUB_TITLE_ARRIVAL_2') . '</div>
				<div class="main-grid-empty-block-description document-list-stub-description">' . Loc::getMessage('DOCUMENT_LIST_STUB_DESCRIPTION_ARRIVAL_2') . '</div>
				<div class="catalog-store-document-stub-transfer-content">
					<div class="catalog-store-document-stub-transfer-info">
						<div class="catalog-store-document-stub-transfer-info-text">
							' . Loc::getMessage('DOCUMENT_LIST_STUB_MIGRATION_TITLE') . '
						</div>
						' . $this->getStubLogoList() . '
					</div>
					<div class="catalog-store-document-stub-transfer-btn-block">
						<a href="#" onclick="openInventoryMarketplaceSlider()" class="ui-btn ui-btn-primary ui-btn-round">
							' . Loc::getMessage('DOCUMENT_LIST_STUB_MIGRATION_LINK') . '
						</a>
					</div>
				</div>
				';
			case self::MOVING_MODE:
				return '
					<div class="main-grid-empty-block-title">' . Loc::getMessage('DOCUMENT_LIST_STUB_TITLE_MOVING') . '</div>
					<div class="main-grid-empty-block-description document-list-stub-description">' . Loc::getMessage('DOCUMENT_LIST_STUB_DESCRIPTION_MOVING') . '</div>
					<a href="#" class="ui-link ui-link-dashed documents-grid-link" onclick="BX.Catalog.DocumentGridManager.Instance.openHowToControlGoodsMovement()">' . Loc::getMessage('DOCUMENT_LIST_STUB_LINK_CONTROL') . '</a>
				';
			case self::DEDUCT_MODE:
				return '
					<div class="main-grid-empty-block-title">' . Loc::getMessage('DOCUMENT_LIST_STUB_TITLE_DEDUCT') . '</div>
					<div class="main-grid-empty-block-description document-list-stub-description">' . Loc::getMessage('DOCUMENT_LIST_STUB_DESCRIPTION_DEDUCT') . '</div>
					<a href="#" class="ui-link ui-link-dashed documents-grid-link" onclick="BX.Catalog.DocumentGridManager.Instance.openHowToAccountForLosses()">' . Loc::getMessage('DOCUMENT_LIST_STUB_LINK_LOSSES') . '</a>
				';
			default:
				return [];
		}

	}

	private function getItemColumn($item)
	{
		$column = $item;

		$column['TITLE'] = $this->prepareTitleView($column);

		if ($column['DOC_NUMBER'])
		{
			$column['DOC_NUMBER'] = htmlspecialcharsbx($column['DOC_NUMBER']);
		}

		if ($column['DOC_TYPE'])
		{
			$columnDescription = [
				'text' => Loc::getMessage('DOCUMENT_LIST_DOC_TYPE_' . $column['DOC_TYPE']) ?: Loc::getMessage('DOCUMENT_LIST_DOC_TYPE_DEFAULT'),
				'color' => 'ui-label-light',
			];

			if ($this->mode === self::ARRIVAL_MODE)
			{
				$encodedFilter = Json::encode(
					[
						'DOC_TYPE' => [$column['DOC_TYPE']],
					],
					// JSON_FORCE_OBJECT flag has been added so that the output complies with the filter's API
					JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_FORCE_OBJECT
				);

				$columnDescription['events'] = [
					'click' => 'BX.delegate(function() {BX.Catalog.DocumentGridManager.Instance.applyFilter(' . $encodedFilter . ')})',
				];
			}

			$column['DOC_TYPE'] = [
				'DOC_TYPE_LABEL' => $columnDescription,
			];
		}

		if ($column['STATUS'])
		{
			if ($column['STATUS'] === 'N')
			{
				if ($column['WAS_CANCELLED'] === 'Y')
				{
					$labelColor = 'ui-label-lightorange';
					$labelText = Loc::getMessage('DOCUMENT_LIST_STATUS_CANCELLED');
					$filterLetter = 'C';
				}
				else
				{
					$labelColor = 'ui-label-light';
					$labelText = Loc::getMessage('DOCUMENT_LIST_STATUS_N');
					$filterLetter = 'N';
				}
			}
			else
			{
				$labelColor = 'ui-label-lightgreen';
				$labelText = Loc::getMessage('DOCUMENT_LIST_STATUS_Y');
				$filterLetter = 'Y';
			}

			$encodedFilter = Json::encode(
				[
					'STATUS' => [$filterLetter],
				],
				// JSON_FORCE_OBJECT flag has been added so that the output complies with the filter's API
				JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_FORCE_OBJECT
			);

			$labelColor .= ' label-uppercase';
			$column['STATUS'] = [
				'STATUS_LABEL' => [
					'text' => $labelText,
					'color' => $labelColor,
					'events' => [
						'click' => 'BX.delegate(function() {BX.Catalog.DocumentGridManager.Instance.applyFilter(' . $encodedFilter . ')})',
					],
				],
			];
		}

		$column['CONTRACTOR_ID'] = htmlspecialcharsbx($this->getContractorName($column));

		if (isset($column['TOTAL']))
		{
			$column['TOTAL'] = CCurrencyLang::CurrencyFormat($column['TOTAL'], $column['CURRENCY']);
		}
		else
		{
			$column['TOTAL'] = CCurrencyLang::CurrencyFormat(0, \Bitrix\Currency\CurrencyManager::getBaseCurrency());
		}

		if ($column['RESPONSIBLE_ID'])
		{
			$column['RESPONSIBLE_ID'] = $this->getUserDisplay($column, $column['RESPONSIBLE_ID'], 'RESPONSIBLE');
		}

		if ($column['CREATED_BY'])
		{
			$column['CREATED_BY'] = $this->getUserDisplay($column, $column['CREATED_BY'], 'CREATED_BY_USER');
		}

		if ($column['MODIFIED_BY'])
		{
			$column['MODIFIED_BY'] = $this->getUserDisplay($column, $column['MODIFIED_BY'], 'MODIFIED_BY_USER');
		}

		if ($column['STATUS_BY'])
		{
			$column['STATUS_BY'] = $this->getUserDisplay($column, $column['STATUS_BY'], 'STATUS_BY_USER');
		}

		if ($column['DATE_DOCUMENT'])
		{
			$column['DATE_DOCUMENT'] = (new \Bitrix\Main\Type\Date($column['DATE_DOCUMENT']))->toString();
		}

		$storesFrom = $this->documentStores[$column['ID']]['STORES_FROM'] ?? [];
		$storesTo = $this->documentStores[$column['ID']]['STORES_TO'] ?? [];
		$stores = array_unique(array_merge($storesFrom, $storesTo));
		if (!empty($stores))
		{
			if ($this->mode === self::MOVING_MODE)
			{
				$column = $this->addStoresToColumn($column, $storesFrom, 'STORES_FROM');
				$column = $this->addStoresToColumn($column, $storesTo, 'STORES_TO');
			}
			else
			{
				$column = $this->addStoresToColumn($column, $stores, 'STORES');
			}
		}

		return $column;
	}

	private function addStoresToColumn(array $column, array $stores, string $fieldName): array
	{
		$existingStores = $this->getStores();

		$resultColumn = $column;
		foreach ($stores as $store)
		{
			$existingStoreTitle = $existingStores[$store]['TITLE'] ?? '';

			$encodedFilter = Json::encode([
				$fieldName => [$store],
				$fieldName . '_label' => [$existingStoreTitle],
			]);
			$resultColumn[$fieldName][$fieldName . '_LABEL_' . $store] = [
				'text' => $existingStoreTitle ?: Loc::getMessage('DOCUMENT_LIST_EMPTY_STORE_TITLE'),
				'color' => 'ui-label-light',
				'events' => [
					'click' => 'BX.delegate(function() {BX.Catalog.DocumentGridManager.Instance.applyFilter(' . $encodedFilter . ')})',
				],
			];
		}

		return $resultColumn;
	}

	private function prepareTitleView($column): string
	{
		$urlToDocumentDetail = $this->getUrlToDocumentDetail($column['ID']);
		if ($column['TITLE'])
		{
			$result = '<a target="_top" href="' . $urlToDocumentDetail . '">' . htmlspecialcharsbx($column['TITLE']) . '</a>';
		}
		else
		{
			$result = '<a target="_top"  href="' . $urlToDocumentDetail . '">' . StoreDocumentTable::getTypeList(true)[$column['DOC_TYPE']] . '</a>';
		}

		$dateTimestamp = (new DateTime($column['DATE_CREATE']))->getTimestamp();
		$date = FormatDate(Context::getCurrent()->getCulture()->getLongDateFormat(), $dateTimestamp);
		$result .= '<div>' . Loc::getMessage('DOCUMENT_LIST_TITLE_DOCUMENT_DATE', ['#DATE#' => $date]) . '</div>';

		return $result;
	}

	private function getContractors(): array
	{
		if (!is_null($this->contractors))
		{
			return $this->contractors;
		}

		$this->contractors = [];

		$dbResult = ContractorTable::getList(['select' => ['ID', 'COMPANY', 'PERSON_NAME']]);
		while ($contractor = $dbResult->fetch())
		{
			$this->contractors[$contractor['ID']] = [
				'NAME' => $contractor['COMPANY'] ?: $contractor['PERSON_NAME'],
				'ID' => $contractor['ID'],
			];
		}

		return $this->contractors;
	}

	private function getStores(): array
	{
		if (!is_null($this->stores))
		{
			return $this->stores;
		}

		$this->stores = [];

		$dbResult = StoreTable::getList(['select' => ['ID', 'TITLE']]);
		while ($store = $dbResult->fetch())
		{
			$this->stores[$store['ID']] = [
				'TITLE' => $store['TITLE'],
				'ID' => $store['ID'],
			];
		}

		return $this->stores;
	}

	private function loadDocumentStores($documentIds): array
	{
		if (!is_null($this->documentStores))
		{
			return $this->documentStores;
		}

		$this->documentStores = [];

		$storesResult = \Bitrix\Catalog\StoreDocumentElementTable::getList([
			'select' => ['DOC_ID', 'STORE_FROM', 'STORE_TO'],
			'filter' => [
				'=DOC_ID' => $documentIds
			],
		]);
		while ($store = $storesResult->fetch())
		{
			$documentId = $store['DOC_ID'];
			if (!isset($this->documentStores[$documentId]))
			{
				$this->documentStores[$documentId] = [
					'STORES_FROM' => [],
					'STORES_TO' => [],
				];
			}

			if ($store['STORE_FROM'] && !in_array($store['STORE_FROM'], $this->documentStores[$documentId]['STORES_FROM'], true))
			{
				$this->documentStores[$documentId]['STORES_FROM'][] = $store['STORE_FROM'];
			}

			if ($store['STORE_TO'] && !in_array($store['STORE_TO'], $this->documentStores[$documentId]['STORES_TO'], true))
			{
				$this->documentStores[$documentId]['STORES_TO'][] = $store['STORE_TO'];
			}
		}

		return $this->documentStores;
	}

	private function getUserDisplay($column, $userId, $userReferenceName): string
	{
		$userEmptyAvatar = ' documents-grid-avatar-empty';
		$userAvatar = '';

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

		$fileInfo = \CFile::ResizeImageGet(
			(int)$column[$userReferenceName . '_PERSONAL_PHOTO'],
			['width' => 60, 'height' => 60],
			BX_RESIZE_IMAGE_EXACT
		);
		if (is_array($fileInfo) && isset($fileInfo['src']))
		{
			$userEmptyAvatar = '';
			$photoUrl = $fileInfo['src'];
			$userAvatar = ' style="background-image: url(\'' . Uri::urnEncode($photoUrl) . '\')"';
		}

		$userNameElement = "<span class='documents-grid-avatar ui-icon ui-icon-common-user{$userEmptyAvatar}'><i{$userAvatar}></i></span>"
			."<span class='documents-grid-username-inner'>{$userName}</span>";

		return "<div class='documents-grid-username-wrapper'>"
			."<a class='documents-grid-username' href='/company/personal/user/{$userId}/'>{$userNameElement}</a>"
			."</div>";
	}

	private function getTotalCountWithoutUserFilter()
	{
		$filter = $this->getDocTypeModeFilter();

		$accessFilter = $this->getAccessFilter();
		if ($accessFilter)
		{
			$filter[] = $accessFilter;
		}

		return StoreDocumentTable::getCount($filter);
	}

	private function prepareToolbar()
	{
		$filterOptions = [
			'GRID_ID' => $this->getGridId(),
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

		$addDocumentButton = $this->getAddDocumentButton();
		if ($addDocumentButton)
		{
			\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addDocumentButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
			$this->arResult['ADD_DOCUMENT_BTN_ID'] = $addDocumentButton->getUniqId();
		}
	}

	private function getAddDocumentButton(): ?\Bitrix\UI\Buttons\Button
	{
		if (!$this->checkDocumentModifyRights())
		{
			return LockedButton::create([
				'text' => Loc::getMessage('DOCUMENT_LIST_ADD_DOCUMENT_BUTTON_2'),
				'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
				'hint' => Loc::getMessage('DOCUMENT_LIST_ADD_DOCUMENT_BUTTON_DISABLE_HINT'),
				'classList' => [
					'add-document-button',
					'add-document-button-disabled',
				],
			]);
		}

		$addDocumentButton = CreateButton::create([
			'text' => Loc::getMessage('DOCUMENT_LIST_ADD_DOCUMENT_BUTTON_2'),
			'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
			'dataset' => [
				'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
			],
			'classList' => ['add-document-button'],
		]);

		if ($this->mode === self::OTHER_MODE)
		{
			$addDocumentButton->setMenu([
				'items' => [
					[
						'text' => StoreDocumentTable::getTypeList(true)[StoreDocumentTable::TYPE_UNDO_RESERVE],
						'href' => $this->getUrlToNewDocumentDetail(StoreDocumentTable::TYPE_UNDO_RESERVE),
					],
					[
						'text' => StoreDocumentTable::getTypeList(true)[StoreDocumentTable::TYPE_RETURN],
						'href' => $this->getUrlToNewDocumentDetail(StoreDocumentTable::TYPE_RETURN),
					],
				]
			]);
		}
		else
		{
			if ($this->mode === self::ARRIVAL_MODE)
			{
				if ($this->isFirstTime())
				{
					$addDocumentButton->setLink($this->getUrlToNewDocumentDetail(StoreDocumentTable::TYPE_STORE_ADJUSTMENT, 'Y'));
				}
				else
				{
					$addDocumentButton->setLink($this->getUrlToNewDocumentDetail(StoreDocumentTable::TYPE_ARRIVAL));
				}
			}
			if ($this->mode === self::MOVING_MODE)
			{
				$addDocumentButton->setLink($this->getUrlToNewDocumentDetail(StoreDocumentTable::TYPE_MOVING));
			}
			if ($this->mode === self::DEDUCT_MODE)
			{
				$addDocumentButton->setLink($this->getUrlToNewDocumentDetail(StoreDocumentTable::TYPE_DEDUCT));
			}
		}

		return $addDocumentButton;
	}

	private function getUrlToNewDocumentDetail(string $documentType, bool $isFirstTime = false): string
	{
		if ($isFirstTime)
		{
			$uriEntity = new Uri($this->getUrlToDocumentDetail(0, $documentType, 'Y'));
		}
		else
		{
			$uriEntity = new Uri($this->getUrlToDocumentDetail(0, $documentType));
		}

		$uriEntity->addParams(['focusedTab' => 'tab_products']);

		return $uriEntity->getUri();
	}

	private function isFirstTime(): bool
	{
		static $doIncomeDocsExist = null;

		if ($doIncomeDocsExist === null)
		{
			$doIncomeDocsExist = (bool)StoreDocumentTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=DOC_TYPE' => [StoreDocumentTable::TYPE_ARRIVAL, StoreDocumentTable::TYPE_STORE_ADJUSTMENT],
				],
				'limit' => 1,
			])->fetch();
		}

		return !$doIncomeDocsExist;
	}

	private function isShowGuide(): bool
	{
		$documentListUserOptions = CUserOptions::GetOption('catalog', 'document-list', []);
		$isGuideOver = $documentListUserOptions['isDocumentCreateGuideOver'] ?? false;
		if (is_string($isGuideOver))
		{
			$isGuideOver = filter_var($isGuideOver, FILTER_VALIDATE_BOOLEAN);
		}

		$canModifyAdjustDocument = AccessController::getCurrent()->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT
		);

		return (
			$this->mode === self::ARRIVAL_MODE
			&& !$isGuideOver
			&& $this->isFirstTime()
			&& Catalog\Component\UseStore::isUsed()
			&& $canModifyAdjustDocument
		);
	}

	private function getUserFilter(): array
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filter->getID());
		$filterFields = $this->filter->getFieldArrays();

		return $filterOptions->getFilterLogic($filterFields);
	}

	private function getListFilter()
	{
		$filter = array_merge($this->getUserFilter(), $this->getDocTypeModeFilter());

		$accessFilter = $this->getAccessFilter();
		if ($accessFilter)
		{
			$filter[] = $accessFilter;
		}

		$filter = $this->prepareListFilter($filter);

		return $filter;
	}

	private function prepareListFilter($filter)
	{
		$preparedFilter = $filter;

		if (isset($preparedFilter['STATUS']))
		{
			$statuses = $preparedFilter['STATUS'];
			unset($preparedFilter['STATUS']);

			$statusFilters = [];
			foreach ($statuses as $status)
			{
				$statusFilter = StoreDocumentTable::getOrmFilterByStatus($status);
				if (!empty($statusFilter))
				{
					$statusFilters[] = $statusFilter;
				}
			}
			if (!empty($statusFilters))
			{
				$preparedFilter[] = array_merge(
					[
						'LOGIC' => 'OR',
					],
					$statusFilters
				);
			}
		}

		if (isset($preparedFilter['DOC_NUMBER']))
		{
			$preparedFilter['DOC_NUMBER'] = '%' . $preparedFilter['DOC_NUMBER'] . '%';
		}

		if (Manager::getActiveProvider())
		{
			Manager::getActiveProvider()::setDocumentsGridFilter($preparedFilter);
		}

		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filter->getID());
		$searchString = $filterOptions->getSearchString();
		if ($searchString)
		{
			$preparedFilter['TITLE'] = '%' . $searchString . '%';
		}

		return $preparedFilter;
	}

	private function getAccessFilter(): ?array
	{
		// default check access
		$isCheckAccess = ($this->arParams['CHECK_ACCESS'] ?? 'Y') === 'Y';
		if (!$isCheckAccess)
		{
			return null;
		}

		$result = [];

		$docTypeFilter = $this->accessController->getEntityFilter(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			StoreDocumentTable::class
		);
		if ($docTypeFilter)
		{
			$result[] = $docTypeFilter;
		}

		$storeFilter = $this->accessController->getEntityFilter(
			ActionDictionary::ACTION_STORE_VIEW,
			StoreDocumentTable::class
		);
		if ($storeFilter)
		{
			$result[] = $storeFilter;
		}

		return $result;
	}

	private function getDocTypeModeFilter(): array
	{
		$docTypes = [];

		switch ($this->mode)
		{
			case self::ARRIVAL_MODE:
				$docTypes = [
					StoreDocumentTable::TYPE_ARRIVAL,
					StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
				];
				break;

			case self::MOVING_MODE:
				$docTypes = [
					StoreDocumentTable::TYPE_MOVING,
				];
				break;

			case self::DEDUCT_MODE:
				$docTypes = [
					StoreDocumentTable::TYPE_DEDUCT,
				];
				break;

			case self::OTHER_MODE:
				$docTypes = [
					StoreDocumentTable::TYPE_RETURN,
					StoreDocumentTable::TYPE_UNDO_RESERVE,
				];
				break;
		}

		return $docTypes ? ['=DOC_TYPE' => $docTypes] : [];
	}

	private function getUrlToDocumentDetail($documentId, $documentType = null, $firstTime = null): string
	{
		if ($this->mode === self::OTHER_MODE)
		{
			if ($documentType)
			{
				$pathToDocumentDetail =
					'/shop/settings/cat_store_document_edit.php?DOCUMENT_TYPE='
					. $documentType
					. '&publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER'
				;
			}
			else
			{
				$pathToDocumentDetail =
					'/shop/settings/cat_store_document_edit.php?publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER&ID='
					. $documentId
				;
			}

			return InventoryManagementSourceBuilder::getInstance()->addInventoryManagementSourceParam($pathToDocumentDetail);
		}

		$pathToDocumentDetailTemplate = $this->arParams['PATH_TO']['DOCUMENT'] ?? '';
		if ($pathToDocumentDetailTemplate === '')
		{
			return $pathToDocumentDetailTemplate;
		}

		$pathToDocumentDetail = str_replace('#DOCUMENT_ID#', $documentId, $pathToDocumentDetailTemplate);

		if ($documentType)
		{
			$pathToDocumentDetail .= '?DOCUMENT_TYPE=' . $documentType;
			if ($firstTime)
			{
				$pathToDocumentDetail .= '&firstTime=' . $firstTime;
			}
		}

		return InventoryManagementSourceBuilder::getInstance()->addInventoryManagementSourceParam($pathToDocumentDetail);
	}

	private function isUserFilterApplied(): bool
	{
		return !empty($this->getUserFilter());
	}

	private function initInventoryManagementSlider()
	{
		$context = Main\Application::getInstance()->getContext();
		/** @var \Bitrix\Main\HttpRequest $request */
		$request = $context->getRequest();

		$this->arResult['OPEN_INVENTORY_MANAGEMENT_SLIDER'] =
			Catalog\Component\UseStore::needShowSlider()
			&& $request->get(Catalog\Component\UseStore::URL_PARAM_STORE_MASTER_HIDE) !== 'Y'
		;
		$this->arResult['OPEN_INVENTORY_MANAGEMENT_SLIDER_ON_ACTION'] = !Catalog\Component\UseStore::isUsed();

		$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
		$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');
		if ($this->arResult['INVENTORY_MANAGEMENT_SOURCE'])
		{
			$sliderPath .= '?inventoryManagementSource=' . $this->arResult['INVENTORY_MANAGEMENT_SOURCE'];
		}
		$this->arResult['MASTER_SLIDER_URL'] = $sliderPath;
	}

	private function getStubLogoList()
	{
		$quickbooksIconPath = $this->getPath() . '/images/document-list-quickbooks.png';
		$zohoIconPath = $this->getPath() . '/images/document-list-zoho.png';

		$logoList = '
		<div class="catalog-store-document-stub-transfer-info-systems-item">
			<img src="' . $zohoIconPath . '" alt="Zoho Inventory">
		</div>
		<div class="catalog-store-document-stub-transfer-info-systems-item">
			<img src="' . $quickbooksIconPath . '" alt="QuickBooks">
		</div>
		';

		if (in_array($this->getZone(), ['ru', 'kz', 'by']))
		{
			$mystoreIconPath = $this->getPath() . '/images/document-list-mystore.svg';

			$companyNames = Loc::loadLanguageFile(
				$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/regionalsystemfields/companynames.php',
				'ru'
			);
			$logoList .= '
			<div class="catalog-store-document-stub-transfer-info-systems-item">
				<img src="' . $mystoreIconPath . '" alt="' . $companyNames['COMPANY_NAME_MY_STORE'] . '">
			</div>
			';
		}

		return '
			<div class="catalog-store-document-stub-transfer-info-systems">
				' . $logoList . '
				<div class="catalog-store-document-stub-transfer-info-systems-item">
				' . Loc::getMessage('DOCUMENT_LIST_STUB_MIGRATION_MORE') . '
				</div>
			</div>
		';
	}

	private function getZone()
	{
		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		else
		{
			$iterator = Bitrix\Main\Localization\LanguageTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=DEF' => 'Y',
					'=ACTIVE' => 'Y'
				]
			]);
			$row = $iterator->fetch();
			$zone = $row['ID'];
		}

		return $zone;
	}

	/**
	 * @param array $column
	 * @return string
	 */
	private function getContractorName(array $column): string
	{
		if (Manager::getActiveProvider())
		{
			$contractor = Manager::getActiveProvider()::getContractorByDocumentId((int)$column['ID']);

			return $contractor ? $contractor->getName() : '';
		}

		$contractorId = (int)$column['CONTRACTOR_ID'];
		$contractors = $this->getContractors();

		if (!isset($contractors[$contractorId]))
		{
			return '';
		}

		return (string)$contractors[$contractorId]['NAME'];
	}
}
