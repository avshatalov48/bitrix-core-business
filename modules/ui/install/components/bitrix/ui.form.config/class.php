<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Grid\Options;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
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
			$moduleId = $request->get('MODULE_ID');
			$scopeAccess = ScopeAccess::getInstance($moduleId);

			if ($request->getPost('action_button_editor_scopes') === 'edit')
			{
				foreach ($request->getPost('FIELDS') as $scopeId => $row)
				{
					if (!empty($row['NAME']) && $scopeAccess->canUpdate($scopeId))
					{
						Scope::getInstance()->updateScopeName($scopeId, $row['NAME']);
					}
				}
			}
			elseif ($request->getPost('action_button_editor_scopes') === 'delete')
			{
				$scopeId = $request->getPost('ID');
				if ($scopeAccess->canDelete($scopeId))
				{
					Scope::getInstance()->removeByIds($scopeId);
				}
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

		$gridOptions = new Options($gridId);
		$navParams = $gridOptions->getNavParams(['nPageSize' => 10]);
		$pageSize = (int)$navParams['nPageSize'];

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

		$snippet = new Snippet();
		$grid['ACTION_PANEL'] = [
			'GROUPS' => [
				'TYPE' => [
					'ITEMS' => [
						$snippet->getRemoveButton(),
						$snippet->getEditButton(),
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
			],
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
}
