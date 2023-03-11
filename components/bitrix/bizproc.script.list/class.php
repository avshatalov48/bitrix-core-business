<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

class BizprocScriptListComponent extends \CBitrixComponent
{
	protected $gridId = 'bizproc_script_list';

	public function onPrepareComponentParams($params)
	{
		if (isset($params['DOCUMENT_TYPE_SIGNED']))
		{
			$params['DOCUMENT_TYPE_SIGNED'] = htmlspecialcharsback($params['DOCUMENT_TYPE_SIGNED']);
			$params['DOCUMENT_TYPE'] = CBPDocument::unSignDocumentType($params['DOCUMENT_TYPE_SIGNED']);
		}
		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if (!Main\Loader::includeModule('bizproc') || !is_array($this->arParams['DOCUMENT_TYPE']))
		{
			return false;
		}

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle(GetMessage('BIZPROC_SCRIPT_LIST_TITLE'));
		}

		$pageNav = $this->getPageNavigation();

		$this->arResult['GridId'] = $this->gridId;
		$this->arResult['GridColumns'] = $this->getGridColumns();
		$this->arResult['GridRows'] = $this->getGridRows($pageNav);
		$this->arResult['PageNavigation'] = $pageNav;
		$this->arResult['PageSizes'] = $this->getPageSizes();
		$this->arResult['canCreateScript'] = $this->canCreateScript();

		return $this->includeComponentTemplate();
	}

	protected function getGridColumns(): array
	{
		$columns = [
			['id' => 'NAME', 'name' => GetMessage('BIZPROC_SCRIPT_LIST_NAME'), 'sort' => 'NAME', 'default' => true],
			['id' => 'LAST_STARTED_DATE', 'name' => GetMessage('BIZPROC_SCRIPT_LIST_LAST_STARTED_DATE'), 'default' => true],
			['id' => 'QUEUE_CNT', 'name' => GetMessage('BIZPROC_SCRIPT_LIST_QUEUE_CNT'), 'default' => true],
			['id' => 'ACTIVE', 'name' => GetMessage('BIZPROC_SCRIPT_LIST_ACTIVE'), 'default' => false],
		];

		if ($this->canCreateScript())
		{
			$columns[] = ['id' => 'ACTIONS', 'name' => GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS'), 'default' => true];
		}

		return $columns;
	}

	protected function getGridRows(\Bitrix\Main\UI\PageNavigation $pageNavigation): array
	{
		$rows = [];

		$gridTableMapping = [
			'NAME' => 'NAME'
		];

		$order = ['ID' => 'asc'];
		if(array_key_exists($this->request->get('by'), $gridTableMapping))
		{
			$order = [$gridTableMapping[$this->request->get('by')] => $this->request->get('order')];
		}

		$filter = [
			'=MODULE_ID' => $this->arParams['DOCUMENT_TYPE'][0],
			'=ENTITY' => $this->arParams['DOCUMENT_TYPE'][1],
			'=DOCUMENT_TYPE' => $this->arParams['DOCUMENT_TYPE'][2]
		];

		$dbResult = \Bitrix\Bizproc\Script\Entity\ScriptTable::getList([
			'select' => ['ID', 'NAME', 'ACTIVE'],
			'filter' => $filter,
			'order' => $order,
			'limit' => $pageNavigation->getLimit(),
			'offset' => $pageNavigation->getOffset(),
		]);

		$pageNavigation->setRecordCount(\Bitrix\Bizproc\Script\Entity\ScriptTable::getCount($filter));

		$jsHandlerEdit = 'BX.Bizproc.ScriptListComponent.Instance.editScript(%d);';
		$jsHandlerDelete = 'BX.Bizproc.ScriptListComponent.Instance.deleteScript(%d);';
		$jsHandlerView = 'BX.Bizproc.Script.Manager.Instance.showScriptQueueList(%d);';
		$jsHandlerActivate = 'BX.Bizproc.ScriptListComponent.Instance.activateScript(%d);';
		$jsHandlerDeactivate = 'BX.Bizproc.ScriptListComponent.Instance.deactivateScript(%d);';

		while($script = $dbResult->fetch())
		{
			$rowActions = [];
			if ($this->canCreateScript())
			{
				$rowActions = [
					[
						'text' => GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS_SET'),
						'onclick' => sprintf($jsHandlerEdit, $script['ID'])
					],
					[
						'text' => GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS_DELETE'),
						'onclick' => sprintf($jsHandlerDelete, $script['ID'])
					]
				];

				if ($script['ACTIVE'] === 'Y')
				{
					$rowActions[] = [
						'text' => GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS_DEACTIVATE'),
						'onclick' => sprintf($jsHandlerDeactivate, $script['ID'])
					];
				}
				else
				{
					$rowActions[] = [
						'text' => GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS_ACTIVATE'),
						'onclick' => sprintf($jsHandlerActivate, $script['ID'])
					];
				}
			}

			$rows[] = [
				'id' => $script['ID'],
				'data' => [
					"NAME" => $this->renderLinkTag($script['NAME'], sprintf($jsHandlerView, $script['ID']), 'view'),
					'LAST_STARTED_DATE' => \Bitrix\Bizproc\Script\Entity\ScriptTable::getLastStartedDate($script['ID']),
					'QUEUE_CNT' => \Bitrix\Bizproc\Script\Entity\ScriptTable::getQueueCount($script['ID']),
					'ACTIVE' => $this->renderActiveCell($script['ACTIVE']),
					"ACTIONS" =>
						$this->renderLinkTag(GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS_SET'), sprintf($jsHandlerEdit, $script['ID']), 'edit')
						.$this->renderLinkTag(GetMessage('BIZPROC_SCRIPT_LIST_ACTIONS_DELETE'), sprintf($jsHandlerDelete, $script['ID']), 'delete')
				],
				'actions' => $rowActions
			];
		}

		return $rows;
	}

	protected function renderLinkTag(string $text, string $handler, string $actionName)
	{
		$className = 'bizproc-script-list-action-'.$actionName;
		return sprintf(
			'<a class="ui-btn-link %s" onclick="%s" href="#">%s</a>',
			$className,
			htmlspecialcharsbx($handler),
			htmlspecialcharsbx($text)
		);
	}

	protected function renderActiveCell($value)
	{
		return $value === 'Y' ? GetMessage('MAIN_YES') : GetMessage('MAIN_NO');
	}

	protected function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$gridOptions = new Bitrix\Main\Grid\Options($this->gridId);
		$navParams = $gridOptions->GetNavParams();

		$pageNavigation= new Bitrix\Main\UI\PageNavigation($this->gridId);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		return $pageNavigation;
	}

	protected function getPageSizes(): array
	{
		return [
			['NAME' => '5', 'VALUE' => '5'],
			['NAME' => '10', 'VALUE' => '10'],
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100']
		];
	}

	protected function canCreateScript(): bool
	{
		static $can;

		if ($can === null)
		{
			$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);
			$can = \Bitrix\Bizproc\Script\Manager::canUserCreateScript(
				$this->arParams['DOCUMENT_TYPE'],
				$user->getId()
			);
		}

		return $can;
	}
}