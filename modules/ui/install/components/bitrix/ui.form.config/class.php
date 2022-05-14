<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Grid\Options;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Ui\EntityForm\Scope;
use Bitrix\Ui\EntityForm\ScopeAccess;

Extension::load(['ui.icons']);

/**
 * Class UiFormConfig
 */
class UiFormConfig extends CBitrixComponent
{
	protected
		$navParamName = 'page',
		$defaultGridSort = [
		'ID' => 'desc'
	];

	public function executeComponent()
	{
		if (!Loader::includeModule('ui'))
		{
			return;
		}

		$request = $this->request;

		if ($request->isPost() && check_bitrix_sessid())
		{
			$scopeId = $request->getPost('ID');
			$moduleId = $request->get('MODULE_ID');
			if (
				$request->getPost('action_button_editor_scopes') === 'delete'
				&& ($scopeAccess = ScopeAccess::getInstance($moduleId))
				&& $scopeAccess->canDelete($scopeId)
			)
			{
				Scope::getInstance()->removeByIds($scopeId);
			}
		}

		$data = $this->prepareData();

		$this->arResult['grid'] = $data['grid'];
		$this->arResult['jsData'] = $data['jsData'];

		$this->includeComponentTemplate();
	}

	protected function prepareData(): array
	{
		$gridId = $this->getGridId();
		$grid['GRID_ID'] = $gridId;
		$grid['COLUMNS'] = $this->getColumns();

		$userIds = [];

		$gridOptions = new Options($gridId);
		$navParams = $gridOptions->getNavParams(['nPageSize' => 10]);
		$pageSize = (int)$navParams['nPageSize'];
		$gridSort = $gridOptions->GetSorting(['sort' => $this->defaultGridSort]);

		$pageNavigation = new PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$list = Scope::getInstance()->getUserScopes(
			$this->arParams['ENTITY_TYPE_ID'],
			($this->arParams['MODULE_ID'] ?? null)
		);

		$jsData = [];
		$grid['ROWS'] = [];

		if (count($list) > 0)
		{
			foreach ($list as $scopeId => $scope)
			{
				$grid['ROWS'][] = [
					//'id' => $item->getId(),
					'data' => [
						'ID' => $scopeId,
						'NAME' => $scope['NAME'],
						'USERS' => '<div class="ui-editor-config" id="ui-editor-config-' . $scopeId . '"></div>'
					]
				];
				$jsData[] = [
					'scopeId' => $scopeId,
					'members' => $scope['MEMBERS'],
					'moduleId' => $this->arParams['MODULE_ID']
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
			'bitrix:main.ui.grid', '', ''
		);
		$grid['SHOW_PAGESIZE'] = true;
		$grid['PAGE_SIZES'] = [
			['NAME' => '10', 'VALUE' => '10'], ['NAME' => '20', 'VALUE' => '20'], ['NAME' => '50', 'VALUE' => '50']
		];
		$grid['DEFAULT_PAGE_SIZE'] = 20;
		$grid['SHOW_ROW_CHECKBOXES'] = true;
		$grid['SHOW_CHECK_ALL_CHECKBOXES'] = false;
		$grid['SHOW_ACTION_PANEL'] = true;

		$grid['ACTION_PANEL'] = [
			'GROUPS' => [
				'TYPE' => [
					'ITEMS' => [
						[
							'NAME' => 'delete',
							'TYPE' => 'BUTTON',
							'TEXT' => Loc::getMessage('UI_FORM_CONFIG_DELETE'),
							'CLASS' => 'icon remove',
							'ONCHANGE' => $this->getOnRemove()->toArray()
						]
					],
				]
			],
		];

		return [
			'grid' => $grid,
			'jsData' => $jsData
		];
	}

	/**
	 * @return string
	 */
	protected function getGridId(): string
	{
		return 'editor_scopes';
	}

	/**
	 * @return array
	 */
	protected function getColumns(): array
	{
		return [
			['id' => 'ID', 'name' => 'ID', 'default' => true],
			['id' => 'NAME', 'name' => Loc::getMessage('UI_FORM_CONFIG_SCOPE'), 'default' => true],
			['id' => 'USERS', 'name' => Loc::getMessage('UI_FORM_CONFIG_MEMBERS'), 'default' => true],
		];
	}

	/**
	 * @return Onchange
	 */
	protected function getOnChange(): Onchange
	{
		$onchange = new Onchange();

		$onchange->addAction(
			[
				'ACTION' => Actions::CALLBACK,
				'CONFIRM' => false,
				'DATA' => [
					['JS' => 'Grid.editSelectedSave()']
				]
			]
		);

		return $onchange;
	}

	/**
	 * @return Onchange
	 */
	protected function getOnRemove(): Onchange
	{
		$onchange = new Onchange();

		$onchange->addAction(
			[
				'ACTION' => Actions::CALLBACK,
				'CONFIRM' => true,
				'CONFIRM_APPLY_BUTTON' => Loc::getMessage('UI_FORM_CONFIG_APPLY'),
				'DATA' => [
					['JS' => 'Grid.removeSelected()']
				]
			]
		);

		return $onchange;
	}
}
