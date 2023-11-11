<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\UI;

class CatalogAgentContractList extends \CBitrixComponent
{
	private const GRID_ID = 'catalog_agent_contract_list';
	private const FILTER_ID = 'catalog_agent_contract_list_filter';

	private Catalog\Filter\DataProvider\AgentContractDataProvider $itemProvider;
	private Main\Filter\Filter $filter;
	private ?array $contractors = null;

	public function onPrepareComponentParams($arParams)
	{
		return parent::onPrepareComponentParams($arParams);
	}

	private function initResult(): void
	{
		$this->arResult = [
			'GRID_ID' => '',
			'COLUMNS' => [],
			'ROWS' => [],
			'NAV_OBJECT' => null,
			'CREATE_URL' => $this->getDetailComponentPath(),
			'ERROR_MESSAGES' => [],
			'IS_ROWS_EXIST' => $this->isRowsExists(),
		];
	}

	private function isRowsExists(): bool
	{
		return (bool)Catalog\AgentContractTable::getRow([
			'select' => ['ID'],
		]);
	}

	private function initFilter(): void
	{
		$this->itemProvider = new Catalog\Filter\DataProvider\AgentContractDataProvider();
		$this->filter = new Main\Filter\Filter(self::FILTER_ID, $this->itemProvider);
	}

	private function prepareGrid(): void
	{
		$this->prepareNavigation();
		$this->arResult['GRID_ID'] = self::GRID_ID;
		$this->arResult['COLUMNS'] = $this->itemProvider->getGridColumns();
		$this->arResult['ROWS'] = $this->getRows();

		$this->arResult['ACTION_PANEL'] = $this->getGroupActionPanel();

		if (!$this->arResult['IS_ROWS_EXIST'])
		{
			$this->arResult['STUB'] = $this->getStub();
		}
	}

	private function prepareNavigation(): void
	{
		$gridOptions = new Main\Grid\Options(self::GRID_ID);
		$navigationParams = $gridOptions->GetNavParams();

		$navigation = new Main\UI\PageNavigation(self::GRID_ID);
		$navigation->allowAllRecords(true)
			->setPageSize($navigationParams['nPageSize'])
			->initFromUri()
		;

		$this->arResult['NAV_OBJECT'] = $navigation;
	}

	private function getGroupActionPanel(): ?array
	{
		$resultItems = [];

		$snippet = new Main\Grid\Panel\Snippet();

		$removeButton = $snippet->getRemoveButton();
		$snippet->setButtonActions($removeButton, [
			[
				'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
				'CONFIRM' => true,
				'DATA' => [
					[
						'JS' => 'BX.Catalog.Component.AgentContractList.Instance.deleteList()'
					],
				],
			]
		]);

		$resultItems[] = $removeButton;

		return [
			'GROUPS' => [
				[
					'ITEMS' => $resultItems,
				],
			]
		];
	}

	private function getSort(): array
	{
		$gridOptions = new Main\Grid\Options(self::GRID_ID);
		$sort = $gridOptions->GetSorting([
			'sort' => [
				'ID' => 'DESC',
			],
			'vars' => [
				'by' => 'by',
				'order' => 'order',
			],
		]);

		if (key($sort['sort']) !== 'ID')
		{
			$sort['sort']['ID'] = 'desc';
		}

		return $sort['sort'];
	}

	private function getRows(): array
	{
		$listFilter = $this->getListFilter();

		$select = array_column($this->itemProvider->getGridColumns(), 'id');
		$select = array_merge($select, $this->getUserSelectColumns($this->getUserReferenceColumns()));

		$filteredProducts = [];
		if (!empty($listFilter['PRODUCTS']))
		{
			$filteredProducts = $listFilter['PRODUCTS'];
			unset($listFilter['PRODUCTS']);
		}

		$filteredSections = [];
		if (!empty($listFilter['SECTIONS']))
		{
			$filteredSections = $listFilter['SECTIONS'];
			unset($listFilter['SECTIONS']);
		}

		$contractorQuery = Catalog\AgentContractTable::query()
			->setOrder($this->getSort())
			->setOffset($this->arResult['NAV_OBJECT']->getOffset())
			->setLimit($this->arResult['NAV_OBJECT']->getLimit())
			->setFilter($listFilter)
			->setSelect($select)
		;

		if (!empty($filteredProducts))
		{
			$contractorQuery->withProductList($filteredProducts);
		}

		if (!empty($filteredSections))
		{
			$contractorQuery->withSectionList($filteredSections);
		}

		$contractorIterator = $contractorQuery->exec();

		$result = [];
		while ($contractor = $contractorIterator->fetch())
		{
			$result[] = [
				'id' => $contractor['ID'],
				'data' => [
					'ID' => $contractor['ID'],
					'TITLE' => $contractor['TITLE'],
					'DATE_MODIFY' => $contractor['DATE_MODIFY'],
					'DATE_CREATE' => $contractor['DATE_CREATE'],
					'MODIFIED_BY' => $contractor['MODIFIED_BY'],
					'CREATED_BY' => $contractor['CREATED_BY'],
				],
				'actions' => $this->getItemActions($contractor),
				'columns' => $this->getItemColumn($contractor),
			];
		}

		return $result;
	}

	private function prepareToolbar(): void
	{
		$filterOptions = [
			'GRID_ID' => self::GRID_ID,
			'FILTER_ID' => $this->filter->getID(),
			'FILTER' => $this->filter->getFieldArrays(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => Bitrix\Main\UI\Filter\Theme::LIGHT,
		];
		UI\Toolbar\Facade\Toolbar::addFilter($filterOptions);

		$addContractorButton = UI\Buttons\CreateButton::create([
			'click' => new UI\Buttons\JsHandler(
				'BX.Catalog.Component.AgentContractList.Instance.create',
				'BX.Catalog.Component.AgentContractList.Instance',
			),
		]);
		UI\Toolbar\Facade\Toolbar::addButton($addContractorButton, UI\Toolbar\ButtonLocation::AFTER_TITLE);

		$helpButton = UI\Buttons\Button::create([
			'text' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_HELP_BUTTON'),
			'color' => UI\Buttons\Color::LIGHT_BORDER,
			'click' => new UI\Buttons\JsHandler(
				'BX.Catalog.Component.AgentContractList.openHelpDesk'
			),
		]);
		UI\Toolbar\Facade\Toolbar::addButton($helpButton, UI\Toolbar\ButtonLocation::RIGHT);
	}

	private function getListFilter(): array
	{
		$filterOptions = new Main\UI\Filter\Options($this->filter->getID());
		$filterFields = $this->filter->getFieldArrays();

		$filter = $filterOptions->getFilterLogic($filterFields);

		$filter = $this->prepareListFilter($filter);

		return $filter;
	}

	private function prepareListFilter($filter)
	{
		$preparedFilter = $filter;

		$provider = Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
			Catalog\v2\Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT
		);
		if ($provider)
		{
			$provider::setDocumentsGridFilter($preparedFilter);
		}

		$filterOptions = new Main\UI\Filter\Options($this->filter->getID());
		$searchString = $filterOptions->getSearchString();
		if ($searchString)
		{
			$preparedFilter['TITLE'] = '%' . $searchString . '%';
		}

		return $preparedFilter;
	}

	private function getItemActions(array $item): array
	{
		$urlToDetail = $this->getDetailComponentPath($item['ID']);

		$actions[] = [
			'TITLE' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_GRID_ACTION_OPEN'),
			'TEXT' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_GRID_ACTION_OPEN'),
			'ONCLICK' => "BX.Catalog.Component.AgentContractList.Instance.open('$urlToDetail')",
			'DEFAULT' => true,
		];

		$actions[] = [
			'TITLE' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_GRID_ACTION_DELETE'),
			'TEXT' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_GRID_ACTION_DELETE'),
			'ONCLICK' => "BX.Catalog.Component.AgentContractList.Instance.delete({$item['ID']})",
		];

		return $actions;
	}

	private function getItemColumn(array $item): array
	{
		$column = $item;

		$urlToDetail = $this->getDetailComponentPath($item['ID']);
		$column['TITLE'] = '<a target="_top" href="' . $urlToDetail . '">' . htmlspecialcharsbx($column['TITLE']) . '</a>';

		if (isset($column['MODIFIED_BY']))
		{
			$column['MODIFIED_BY'] = $this->getUserDisplay($column, $column['MODIFIED_BY'], 'MODIFIED_BY_USER');
		}

		if (isset($column['CREATED_BY']))
		{
			$column['CREATED_BY'] = $this->getUserDisplay($column, $column['CREATED_BY'], 'CREATED_BY_USER');
		}

		if ($column['DATE_MODIFY'] instanceof Main\Type\DateTime)
		{
			$column['DATE_MODIFY'] = $column['DATE_MODIFY']->toString();
		}

		if ($column['DATE_CREATE'] instanceof Main\Type\DateTime)
		{
			$column['DATE_CREATE'] = $column['DATE_CREATE']->toString();
		}

		$column['CONTRACTOR_ID'] = htmlspecialcharsbx($this->getContractorName($column));

		return $column;
	}

	private function getDetailComponentPath(int $id = 0): string
	{
		$pathToPaymentDetailTemplate = $this->arParams['PATH_TO']['DETAIL'] ?? '';
		if ($pathToPaymentDetailTemplate === '')
		{
			return $pathToPaymentDetailTemplate;
		}

		return str_replace('#AGENT_CONTRACT_ID#', $id, $pathToPaymentDetailTemplate);
	}

	private function getUserDisplay($column, $userId, $userReferenceName): string
	{
		$userEmptyAvatar = ' agent-contract-grid-avatar-empty';
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
			$userAvatar = ' style="background-image: url(\'' . Main\Web\Uri::urnEncode($photoUrl) . '\')"';
		}

		$userNameElement = "<span class='agent-contract-grid-avatar ui-icon ui-icon-common-user{$userEmptyAvatar}'><i{$userAvatar}></i></span>"
			. "<span class='agent-contract-grid-username-inner'>{$userName}</span>"
		;

		$personalUrl = $this->getUserPersonalUrl($userId);

		return "<div class='agent-contract-grid-username-wrapper'>"
			. "<a class='agent-contract-grid-username' href='{$personalUrl}'>{$userNameElement}</a>"
			. "</div>"
		;
	}

	private function getUserPersonalUrl(int $userId): Main\Web\Uri
	{
		$template = $this->getUserPersonalUrlTemplate();

		return new Main\Web\Uri(
			str_replace(
				[
					'#USER_ID#',
					'#ID#',
					'#user_id#',
				],
				$userId,
				$template
			)
		);
	}

	private function getUserPersonalUrlTemplate(): string
	{
		return Main\Config\Option::get('intranet', 'path_user', '/company/personal/user/#USER_ID#/', $this->getSiteId());
	}

	private function getUserReferenceColumns(): array
	{
		return ['MODIFIED_BY_USER', 'CREATED_BY_USER'];
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

	/**
	 * @param array $column
	 * @return string
	 */
	private function getContractorName(array $column): string
	{
		if (Catalog\v2\Contractor\Provider\Manager::getActiveProvider(Catalog\v2\Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT))
		{
			$contractor = Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
				Catalog\v2\Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT
			)::getContractorByDocumentId((int)$column['ID']);

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

	private function getContractors(): array
	{
		if (!is_null($this->contractors))
		{
			return $this->contractors;
		}

		$this->contractors = [];

		$dbResult = Catalog\ContractorTable::getList(['select' => ['ID', 'COMPANY', 'PERSON_NAME']]);
		while ($contractor = $dbResult->fetch())
		{
			$this->contractors[$contractor['ID']] = [
				'NAME' => $contractor['COMPANY'] ?: $contractor['PERSON_NAME'],
				'ID' => $contractor['ID'],
			];
		}

		return $this->contractors;
	}

	private function getStub(): string
	{
		return '
			<div class="main-grid-empty-block-title">' . Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_EMPTYSTATE_TITLE') . '</div>
			<div class="main-grid-empty-block-description agent-contract-list-stub-description">' . Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_EMPTYSTATE_DESCRIPTION') . '</div>
			<a href="#" class="ui-link ui-link-dashed agent-contract-grid-link" onclick="BX.Catalog.Component.AgentContractList.openHelpDesk()">' . Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_EMPTYSTATE_LINK') . '</a>
		';
	}

	private function checkModules(): bool
	{
		if (!Main\Loader::includeModule('catalog'))
		{
			$this->arResult['ERROR_MESSAGES'][] = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_MODULE_CATALOG_NOT_FOUND');
			return false;
		}

		if (!Main\Loader::includeModule('ui'))
		{
			$this->arResult['ERROR_MESSAGES'][] = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_MODULE_UI_NOT_FOUND');
			return false;
		}

		return true;
	}

	private function checkPermission(): bool
	{
		if (!Catalog\v2\AgentContract\AccessController::check())
		{
			$this->arResult['ERROR_MESSAGES'][] = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_PERMISSION_DENIED');
			return false;
		}

		return true;
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkPermission())
		{
			$this->initResult();
			$this->initFilter();

			$this->prepareToolbar();
			$this->prepareGrid();
		}

		$this->includeComponentTemplate();
	}
}