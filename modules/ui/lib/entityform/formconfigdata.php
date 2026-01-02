<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Json;
use Bitrix\UI\Integration\HumanResources\HumanResources;

class FormConfigData
{
	private const SETTINGS_ENTITY_FORM_SCOPE_KEY = 'entityFormScope';
	private const SETTINGS_FORM_CONFIG_DATA_CLASS_KEY = 'formConfigData';

	public function __construct(
		protected readonly string $navParamName,
		protected readonly string $moduleId,
		protected readonly string|int $entityTypeId,
	) {
	}

	public static function getInstance(string $navParamName, string $moduleId, string|int $entityTypeId): static
	{
		$configuration = Configuration::getInstance($moduleId);

		$value = $configuration->get(static::SETTINGS_ENTITY_FORM_SCOPE_KEY);
		if (
			is_array($value)
			&& isset($value[static::SETTINGS_FORM_CONFIG_DATA_CLASS_KEY])
			&& Loader::includeModule($moduleId)
			&& is_a($value[static::SETTINGS_FORM_CONFIG_DATA_CLASS_KEY], self::class, true)
		) {
			return new $value[static::SETTINGS_FORM_CONFIG_DATA_CLASS_KEY]($navParamName, $moduleId, $entityTypeId);
		}

		return new self($navParamName, $moduleId, $entityTypeId);
	}

	public function prepare(): array
	{
		$gridId = $this->getGridId();
		$grid['GRID_ID'] = $gridId;
		$grid['COLUMNS'] = $this->getColumns();

		$gridOptions = new Options($gridId);
		$navParams = $gridOptions->getNavParams(['nPageSize' => 10]);
		$pageSize = (int)$navParams['nPageSize'];

		$pageNavigation = new PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$entityTypeId = $this->entityTypeId ?? null;

		if ($entityTypeId)
		{
			$moduleId = $this->moduleId ?? null;
			$list = Scope::getInstance()->getAllUserScopes($entityTypeId, $moduleId);
		}
		else
		{
			$list = [];
		}

		$grid['ROWS'] = [];

		if (!empty($list))
		{
			foreach ($list as $scopeId => $scope)
			{
				$grid['ROWS'][] = [
					'data' => $this->prepareRowData($scopeId, $scope, $entityTypeId),
					'actions' => $this->getContextActions($scopeId, $scope),
				];
			}
		}

		$grid['NAV_PARAM_NAME'] = $this->navParamName;
		$grid['CURRENT_PAGE'] = $pageNavigation->getCurrentPage();
		$grid['NAV_OBJECT'] = $pageNavigation;
		$grid['AJAX_MODE'] = 'Y';
		$grid['ALLOW_ROWS_SORT'] = false;
		$grid['AJAX_OPTION_JUMP'] = 'N';
		$grid['AJAX_OPTION_STYLE'] = 'N';
		$grid['AJAX_OPTION_HISTORY'] = 'N';
		$grid['AJAX_ID'] = \CAjax::GetComponentID(
			'bitrix:main.ui.grid', '', '',
		);
		$grid['SHOW_PAGESIZE'] = true;
		$grid['PAGE_SIZES'] = [
			['NAME' => '10', 'VALUE' => '10'], ['NAME' => '20', 'VALUE' => '20'], ['NAME' => '50', 'VALUE' => '50'],
		];
		$grid['DEFAULT_PAGE_SIZE'] = 20;
		$grid['SHOW_ROW_CHECKBOXES'] = true;
		$grid['SHOW_CHECK_ALL_CHECKBOXES'] = false;
		$grid['SHOW_ACTION_PANEL'] = true;

		$snippet = new Snippet();
		$grid['ACTION_PANEL'] = [
			'GROUPS' => [
				'TYPE' => [
					'ITEMS' => [
						$snippet->getRemoveButton(),
						$snippet->getEditButton(),
					],
				],
			],
		];

		$grid['FILTER'] = $this->getFilter();

		return [
			'grid' => $grid,
		];
	}

	protected function getGridId(): string
	{
		return 'editor_scopes';
	}

	protected function getColumns(): array
	{
		$columns = [
			[
				'id' => 'ID',
				'name' => 'ID',
				'default' => true,
			],
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('UI_FORM_CONFIG_SCOPE'),
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'USERS',
				'name' => Loc::getMessage('UI_FORM_CONFIG_MEMBERS'),
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'AUTO_APPLY_SCOPE',
				'name' => Loc::getMessage('UI_FORM_CONFIG_AUTO_APPLY_SCOPE'),
				'type' => \Bitrix\Main\Grid\Types::GRID_CHECKBOX,
				'default' => true,
				'editable' => true,
			],

		];

		if (ScopeAccess::getInstance($this->moduleId)->canUseOnAddOnUpdateSegregation())
		{
			$columns[] = [
				'id' => 'ON_ADD',
				'name' => Loc::getMessage('UI_FORM_CONFIG_ON_ADD'),
				'type' => \Bitrix\Main\Grid\Types::GRID_CHECKBOX,
				'default' => true,
				'editable' => true,
			];
			$columns[] = [
				'id' => 'ON_UPDATE',
				'name' => Loc::getMessage('UI_FORM_CONFIG_ON_UPDATE'),
				'type' => \Bitrix\Main\Grid\Types::GRID_CHECKBOX,
				'default' => true,
				'editable' => true,
			];
		}

		return $columns;
	}

	protected function getContextActions(int $scopeId, array $scope): array
	{
		return [];
	}

	protected function getFilter(): ?array
	{
		return null;
	}

	protected function prepareRowData(int|string $scopeId, array $scope, string $entityTypeId): array
	{
		return [
			'ID' => $scopeId,
			'NAME' => $scope['NAME'],
			'USERS' => $this->getUserField($scopeId, $scope, $entityTypeId),
			'AUTO_APPLY_SCOPE' => $scope['AUTO_APPLY_SCOPE'],
			'ON_ADD' => $scope['ON_ADD'],
			'ON_UPDATE' => $scope['ON_UPDATE'],
		];
	}

	protected function getUserField(int|string $scopeId, array $scope, $entityTypeId): string
	{
		$params = Json::encode([
			'scopeId' => $scopeId,
			'members' => $scope['MEMBERS'],
			'moduleId' => $this->moduleId,
			'entityTypeId' => $entityTypeId,
			'useHumanResourcesModule' => HumanResources::getInstance()->isAccessCodesCanBeUsed(),
		]);

		return <<<HTML
			<div class="ui-editor-config" id="ui-editor-config-$scopeId"></div>
			<script>
				BX.ready(() => { new BX.Ui.Form.ConfigItem({$params}) });
			</script>
HTML;
	}
}
