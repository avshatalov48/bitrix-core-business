<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;

class BizprocScriptQueueListComponent extends \CBitrixComponent
{
	protected $gridId = 'bizproc_script_queue_list';

	public function executeComponent()
	{
		global $APPLICATION;

		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		$script = \Bitrix\Bizproc\Script\Manager::getById($this->arParams['SCRIPT_ID']);

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle($script ? $script->getName() : GetMessage('BIZPROC_SCRIPT_QL_PAGE_TITLE'));
		}

		if (!$script)
		{
			ShowError(GetMessage("BIZPROC_SCRIPT_QL_SCRIPT_NOT_FOUND"));
			return;
		}

		$pageNav = $this->getPageNavigation();

		$this->arResult['GridId'] = $this->gridId;
		$this->arResult['GridColumns'] = $this->getGridColumns();
		$this->arResult['GridRows'] = $this->getGridRows($script, $pageNav);
		$this->arResult['PageNavigation'] = $pageNav;
		$this->arResult['PageSizes'] = $this->getPageSizes();


		return $this->includeComponentTemplate();
	}

	protected function getGridColumns(): array
	{
		return [
			['id' => 'STARTED_BY', 'name' => GetMessage('BIZPROC_SCRIPT_QL_COLUMN_STARTED_BY'), 'default' => true],
			['id' => 'STARTED_DATE', 'name' => GetMessage('BIZPROC_SCRIPT_QL_COLUMN_STARTED_DATE'), 'default' => true],
			['id' => 'COMPLETED_CNT', 'name' => GetMessage('BIZPROC_SCRIPT_QL_COLUMN_COMPLETED_CNT'), 'default' => true],
			['id' => 'QUEUED_CNT', 'name' => GetMessage('BIZPROC_SCRIPT_QL_COLUMN_QUEUED_CNT'), 'default' => true],
			['id' => 'STATUS', 'name' => GetMessage('BIZPROC_SCRIPT_QL_COLUMN_STATUS'), 'default' => true],
			['id' => 'ENTITY', 'name' => GetMessage('BIZPROC_SCRIPT_QL_COLUMN_ENTITY'), 'default' => true],
		];
	}

	protected function getGridRows(\Bitrix\Bizproc\Script\Entity\EO_Script $script, \Bitrix\Main\UI\PageNavigation $pageNavigation): array
	{
		$docService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$entityName = $docService->getEntityName($script->getModuleId(), $script->getEntity());

		$rows = [];
		$order = ['ID' => 'desc'];

		$dbResult = \Bitrix\Bizproc\Script\Entity\ScriptQueueTable::getList([
			'select' => [
				'*',
				'STARTED_USER_NAME' => 'STARTED_USER.NAME',
				'STARTED_USER_LAST_NAME' => 'STARTED_USER.LAST_NAME',
				'STARTED_USER_SECOND_NAME' => 'STARTED_USER.SECOND_NAME',
				'STARTED_USER_LOGIN' => 'STARTED_USER.LOGIN'
			],
			'filter' => [
				'=SCRIPT_ID' => $script->getId(),
			],
			'order' => $order,
			'limit' => $pageNavigation->getLimit(),
			'offset' => $pageNavigation->getOffset(),
		]);

		$pageNavigation->setRecordCount(\Bitrix\Bizproc\Script\Entity\ScriptQueueTable::getCount([
			'=SCRIPT_ID' => $script->getId(),
		]));

		while($row = $dbResult->fetch())
		{
			$counters = \Bitrix\Bizproc\Script\Entity\ScriptQueueTable::getDocumentCounters($row['ID']);

			$rowData = $row;

			$rowData += [
				'COMPLETED_CNT' => $counters['completed'],
				'QUEUED_CNT' => $counters['all'],
				'ENTITY' => $entityName,
			];

			$rowActions = [];

			if ((int) $row['STATUS'] === \Bitrix\Bizproc\Script\Queue\Status::EXECUTING)
			{
				$rowActions[] = [
					'text' => GetMessage('BIZPROC_SCRIPT_QL_ACTION_TERMINATE'),
					'onclick' => sprintf('BX.Bizproc.ScriptQueueListComponent.Instance.terminateQueue(%d)', $row['ID'])
				];
			}

			$rowActions[] = [
				'text' => GetMessage('BIZPROC_SCRIPT_QL_ACTION_DELETE'),
				'onclick' => sprintf('BX.Bizproc.ScriptQueueListComponent.Instance.deleteQueue(%d)', $row['ID'])
			];


			$rows[] = [
				'data' => $rowData,
				'actions' => $rowActions
			];
		}
		return $rows;
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
}