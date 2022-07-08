<?php

use Bitrix\Catalog;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('currency');

class CatalogStoreDocumentListComponent extends CBitrixComponent implements Controllerable
{
	private const GRID_ID = 'catalog_store_documents';
	private const FILTER_ID = 'catalog_store_documents_filter';

	public const ARRIVAL_MODE = 'receipt_adjustment';
	public const MOVING_MODE = 'moving';
	public const DEDUCT_MODE = 'deduct';
	public const OTHER_MODE = 'other';

	private $defaultGridSort = [
		'DATE_MODIFY' => 'desc',
	];
	private $navParamName = 'page';

	private $analyticsSource = '';

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
		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			CAllCrmInvoice::installExternalEntities();
		}
		
		$this->init();
		if (!$this->checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('DOCUMENT_LIST_NO_VIEW_RIGHTS_ERROR');
			$this->includeComponentTemplate();
			return;
		}
		$this->arResult['GRID'] = $this->prepareGrid();
		$this->arResult['FILTER_ID'] = $this->getFilterId();
		$this->prepareToolbar();
		$this->arResult['IS_SHOW_GUIDE'] = $this->isShowGuide();

		$this->arResult['PATH_TO'] = $this->arParams['PATH_TO'];

		$this->initInventoryManagementSlider();

		$this->includeComponentTemplate();
	}

	private function checkDocumentReadRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_read');
	}

	private function checkDocumentWriteRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_store');
	}

	public function configureActions()
	{
	}

	private function getFilterId()
	{
		return self::FILTER_ID . '_' . $this->mode;
	}

	private function getGridId()
	{
		return self::GRID_ID . '_' . $this->mode;
	}

	private function init()
	{
		$this->initMode();

		$this->itemProvider = new \Bitrix\Catalog\Grid\Filter\DocumentDataProvider($this->mode);
		$this->filter = new \Bitrix\Main\Filter\Filter($this->getFilterId(), $this->itemProvider);

		$this->analyticsSource = $this->request->get('inventoryManagementSource') ?? '';
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

	private function prepareGrid()
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

		if ($sortField === 'STATUS')
		{
			$gridSort['sort']['WAS_CANCELLED'] = $gridSort['sort']['STATUS'];
		}

		$result['COLUMNS'] = $gridColumns;

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$this->arResult['GRID']['ROWS'] = $buffer = [];
		$listFilter = $this->getListFilter();
		$select = array_merge(['*'], $this->getUserSelectColumns($this->getUserReferenceColumns()));
		$list = StoreDocumentTable::getList([
			'order' => $gridSort['sort'],
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'filter' => $listFilter,
			'select' => $select,
		])->fetchAll();
		$totalCount = $this->getTotalCount();
		if($totalCount > 0)
		{
			$this->getDocumentStores(array_column($list, 'ID'));
			foreach($list as $item)
			{
				$result['ROWS'][] = [
					'id' => $item['ID'],
					'data' => $item,
					'columns' => $this->getItemColumn($item),
					'actions' => $this->getItemActions($item),
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
		$result['NAV_PARAM_NAME'] = 'page';
		$result['SHOW_PAGESIZE'] = true;
		$result['PAGE_SIZES'] = [['NAME' => 10, 'VALUE' => 10], ['NAME' => 20, 'VALUE' => 20], ['NAME' => 50, 'VALUE' => 50]];
		$result['SHOW_ROW_CHECKBOXES'] = true;
		$result['SHOW_CHECK_ALL_CHECKBOXES'] = true;
		$result['SHOW_ACTION_PANEL'] = true;
		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
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

		$dropdownActions = [
			[
				'NAME' => Loc::getMessage('DOCUMENT_LIST_SELECT_GROUP_ACTION'),
				'VALUE' => 'none',
			],
			[
				'NAME' => Loc::getMessage('DOCUMENT_LIST_CONDUCT_GROUP_ACTION'),
				'VALUE' => 'conduct',
			],
			[
				'NAME' => Loc::getMessage('DOCUMENT_LIST_CANCEL_GROUP_ACTION'),
				'VALUE' => 'cancel',
			]
		];

		$dropdownActionsButton = [
			'TYPE' => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
			'ID' => 'action_button_'. $this->getGridId(),
			'NAME' => 'action_button_'. $this->getGridId(),
			'ITEMS' => $dropdownActions,
		];

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

		$result['ACTION_PANEL'] = [
			'GROUPS' => [
				[
					'ITEMS' => [
						$removeButton,
						$dropdownActionsButton,
						$applyButton,
					],
				],
			]
		];

		return $result;
	}

	private function getUserReferenceColumns()
	{
		return ['RESPONSIBLE', 'CREATED_BY_USER', 'MODIFIED_BY_USER', 'STATUS_BY_USER'];
	}

	private function getUserSelectColumns($userReferenceNames)
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

	private function getItemActions($item)
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
			$actions[] = [
				'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_CONDUCT_TITLE'),
				'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_CONDUCT_TEXT'),
				'ONCLICK' => "BX.Catalog.DocumentGridManager.Instance.conductDocument(" . $item['ID'] . ", '" . $item['DOC_TYPE'] . "')",
			];
			$actions[] = [
				'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_DELETE_TITLE'),
				'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_DELETE_TEXT'),
				'ONCLICK' => "BX.Catalog.DocumentGridManager.Instance.deleteDocument(" . $item['ID'] . ")",
			];
		}
		else
		{
			$actions[] = [
				'TITLE' => Loc::getMessage('DOCUMENT_LIST_ACTION_CANCEL_TITLE'),
				'TEXT' => Loc::getMessage('DOCUMENT_LIST_ACTION_CANCEL_TEXT'),
				'ONCLICK' => "BX.Catalog.DocumentGridManager.Instance.cancelDocument(" . $item['ID'] . ", '" . $item['DOC_TYPE'] . "')",
			];
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

		if ($column['CONTRACTOR_ID'])
		{
			$column['CONTRACTOR_ID'] = htmlspecialcharsbx($this->getContractors()[$column['CONTRACTOR_ID']]['NAME']);
		}

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

		$stores = $this->documentStores[$column['ID']];
		if (!empty($stores))
		{
			$existingStores = $this->getStores();
			foreach ($stores as $store)
			{
				$encodedFilter = Json::encode([
					'STORES' => [$store],
					'STORES_label' => [$existingStores[$store]['TITLE']],
				]);
				$column['STORES']['STORE_LABEL_' . $store] = [
					'text' => $existingStores[$store]['TITLE'] ?: Loc::getMessage('DOCUMENT_LIST_EMPTY_STORE_TITLE'),
					'color' => 'ui-label-light',
					'events' => [
						'click' => 'BX.delegate(function() {BX.Catalog.DocumentGridManager.Instance.applyFilter(' . $encodedFilter . ')})',
					],
				];
			}
		}

		return $column;
	}

	private function prepareTitleView($column)
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

	private function getContractors()
	{
		if (!is_null($this->contractors))
		{
			return $this->contractors;
		}

		$dbResult = \Bitrix\Catalog\ContractorTable::getList(['select' => ['ID', 'COMPANY', 'PERSON_NAME']]);
		while ($contractor = $dbResult->fetch())
		{
			$this->contractors[$contractor['ID']] = [
				'NAME' => $contractor['COMPANY'] ?: $contractor['PERSON_NAME'],
				'ID' => $contractor['ID'],
			];
		}

		return $this->contractors;
	}

	private function getStores()
	{
		if (!is_null($this->stores))
		{
			return $this->stores;
		}

		$dbResult = \Bitrix\Catalog\StoreTable::getList(['select' => ['ID', 'TITLE']]);
		while ($store = $dbResult->fetch())
		{
			$this->stores[$store['ID']] = [
				'TITLE' => $store['TITLE'],
				'ID' => $store['ID'],
			];
		}

		return $this->stores;
	}

	private function getDocumentStores($documentIds)
	{
		if (!is_null($this->documentStores))
		{
			return $this->documentStores;
		}

		$storesResult = \Bitrix\Catalog\StoreDocumentElementTable::getList([
			'select' => ['DOC_ID', 'STORE_FROM', 'STORE_TO'],
			'filter' => [
				'=DOC_ID' => $documentIds
			],
		]);
		while ($store = $storesResult->fetch())
		{
			if (
				(
					!is_array($this->documentStores[$store['DOC_ID']])
					|| !in_array($store['STORE_FROM'], $this->documentStores[$store['DOC_ID']])
				)
				&& isset($store['STORE_FROM']))
			{
				$this->documentStores[$store['DOC_ID']][] = $store['STORE_FROM'];
			}

			if (
				(
					!is_array($this->documentStores[$store['DOC_ID']])
					|| !in_array($store['STORE_TO'], $this->documentStores[$store['DOC_ID']])
				)
				&& isset($store['STORE_TO'])
			)
			{
				$this->documentStores[$store['DOC_ID']][] = $store['STORE_TO'];
			}
		}

		return $this->documentStores;
	}

	private function getUserDisplay($column, $userId, $userReferenceName)
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
			$userAvatar = " style='background-image: url(\"{$photoUrl}\")'";
		}

		$userNameElement = "<span class='documents-grid-avatar ui-icon ui-icon-common-user{$userEmptyAvatar}'><i{$userAvatar}></i></span>"
			."<span class='documents-grid-username-inner'>{$userName}</span>";

		return "<div class='documents-grid-username-wrapper'>"
			."<a class='documents-grid-username' href='/company/personal/user/{$userId}/'>{$userNameElement}</a>"
			."</div>";
	}

	private function getTotalCount()
	{
		$count = StoreDocumentTable::getList([
			'select' => ['CNT'],
			'filter' => $this->getListFilter(),
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			],
		])->fetch()['CNT'];

		return $count;
	}

	private function getTotalCountWithoutUserFilter()
	{
		$count = StoreDocumentTable::getList([
			'select' => ['CNT'],
			'filter' => $this->getDocTypeModeFilter(),
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			],
		])->fetch()['CNT'];

		return $count;
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
		];
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterOptions);

		$addDocumentButton = \Bitrix\UI\Buttons\CreateButton::create([
			'text' => Loc::getMessage('DOCUMENT_LIST_ADD_DOCUMENT_BUTTON'),
			'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
			'dataset' => [
				'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
			],
			'classList' => ['add-document-button'],
		]);
		$analyticsSourcePart = $this->analyticsSource ? '&inventoryManagementSource=' . $this->analyticsSource : '';
		if ($this->mode === self::OTHER_MODE)
		{
			$addDocumentButton->setMenu([
				'items' => [
					[
						'text' => StoreDocumentTable::getTypeList(true)[StoreDocumentTable::TYPE_UNDO_RESERVE],
						'href' => '/shop/settings/cat_store_document_edit.php?DOCUMENT_TYPE=U&publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER' . $analyticsSourcePart,
					],
					[
						'text' => StoreDocumentTable::getTypeList(true)[StoreDocumentTable::TYPE_RETURN],
						'href' => '/shop/settings/cat_store_document_edit.php?DOCUMENT_TYPE=R&publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER' . $analyticsSourcePart,
					],
				]
			]);
		}
		else
		{
			$addDocumentUrl = $this->getUrlToDocumentDetail(0);
			if ($this->mode === self::ARRIVAL_MODE)
			{
				$typeParams =
					$this->isFirstTime()
						? (StoreDocumentTable::TYPE_STORE_ADJUSTMENT . '&firstTime=Y')
						: StoreDocumentTable::TYPE_ARRIVAL
				;
				$addDocumentButton->setLink($addDocumentUrl . '?DOCUMENT_TYPE=' . $typeParams . $analyticsSourcePart);
			}
			if ($this->mode === self::MOVING_MODE)
			{
				$addDocumentButton->setLink($addDocumentUrl . '?DOCUMENT_TYPE=' . StoreDocumentTable::TYPE_MOVING . $analyticsSourcePart);
			}
			if ($this->mode === self::DEDUCT_MODE)
			{
				$addDocumentButton->setLink($addDocumentUrl . '?DOCUMENT_TYPE=' . StoreDocumentTable::TYPE_DEDUCT . $analyticsSourcePart);
			}
		}

		\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addDocumentButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
		$this->arResult['ADD_DOCUMENT_BTN_ID'] = $addDocumentButton->getUniqId();
	}

	private function isFirstTime()
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

		return (
			$this->mode === self::ARRIVAL_MODE
			&& !$isGuideOver
			&& $this->isFirstTime()
			&& Catalog\Component\UseStore::isUsed()
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

		$filter = $this->prepareListFilter($filter);

		return $filter;
	}

	private function prepareListFilter($filter)
	{
		$preparedFilter = $filter;

		if ($preparedFilter['STATUS'])
		{
			$statuses = $preparedFilter['STATUS'];
			unset($preparedFilter['STATUS']);

			$statusFilters = [
				'LOGIC' => 'OR',
			];
			foreach ($statuses as $status)
			{
				$statusFilter = [];
				if ($status === 'Y')
				{
					$statusFilter['STATUS'] = 'Y';
				}
				elseif ($status === 'N')
				{
					$statusFilter['WAS_CANCELLED'] = 'N';
					$statusFilter['STATUS'] = 'N';
				}
				elseif ($status === 'C')
				{
					$statusFilter['STATUS'] = 'N';
					$statusFilter['WAS_CANCELLED'] = 'Y';
				}

				$statusFilters[] = $statusFilter;
			}

			$preparedFilter[] = $statusFilters;
		}

		if ($preparedFilter['DOC_NUMBER'])
		{
			$preparedFilter['DOC_NUMBER'] = '%' . $preparedFilter['DOC_NUMBER'] . '%';
		}

		if ($preparedFilter['STORES'])
		{
			$storeIds = $preparedFilter['STORES'];
			unset($preparedFilter['STORES']);
			$documentsWithStores = StoreDocumentTable::getList([
				'select' => ['ID'],
				'filter' => [
					'LOGIC' => 'OR',
					['ELEMENTS.STORE_FROM' => $storeIds],
					['ELEMENTS.STORE_TO' => $storeIds],
				],
			])->fetchAll();
			$documentsWithStores = array_unique(array_column($documentsWithStores, 'ID'));
			$preparedFilter['ID'] = $documentsWithStores;
		}

		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filter->getID());
		$searchString = $filterOptions->getSearchString();
		if ($searchString)
		{
			$preparedFilter['TITLE'] = '%' . $searchString . '%';
		}

		return $preparedFilter;
	}

	private function getDocTypeModeFilter()
	{
		switch ($this->mode)
		{
			case self::ARRIVAL_MODE:
				return ['=DOC_TYPE' => [StoreDocumentTable::TYPE_ARRIVAL, StoreDocumentTable::TYPE_STORE_ADJUSTMENT]];
			case self::MOVING_MODE:
				return ['=DOC_TYPE' => StoreDocumentTable::TYPE_MOVING];
			case self::DEDUCT_MODE:
				return ['=DOC_TYPE' => StoreDocumentTable::TYPE_DEDUCT];
			case self::OTHER_MODE:
				return ['=DOC_TYPE' => [StoreDocumentTable::TYPE_RETURN, StoreDocumentTable::TYPE_UNDO_RESERVE]];
		}

		return [];
	}

	private function getUrlToDocumentDetail($documentId)
	{
		if ($this->mode === self::OTHER_MODE)
		{
			return '/shop/settings/cat_store_document_edit.php?publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER&ID=' . $documentId;
		}

		$pathToDocumentDetail = $this->arParams['PATH_TO']['DOCUMENT'] ?? '';
		if ($pathToDocumentDetail === '')
		{
			return $pathToDocumentDetail;
		}

		$url = str_replace('#DOCUMENT_ID#', $documentId, $pathToDocumentDetail);
		return $url;
	}

	private function isUserFilterApplied()
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
		if (Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
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
}
