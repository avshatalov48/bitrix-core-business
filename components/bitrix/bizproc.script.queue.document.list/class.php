<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;

class BizprocScriptQueueDocumentListComponent extends \CBitrixComponent
{
	protected $gridId = 'bizproc_script_queue_doc_list';

	public function executeComponent()
	{
		global $APPLICATION;

		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		$queue = \Bitrix\Bizproc\Script\Manager::getQueueById($this->arParams['QUEUE_ID']);
		$script = $queue ? \Bitrix\Bizproc\Script\Manager::getById($queue->getScriptId()) : null;

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle($script ? $script->getName() : GetMessage('BIZPROC_SCRIPT_QDL_PAGE_TITLE'));
		}

		if (!$queue || !$script)
		{
			ShowError(GetMessage("BIZPROC_SCRIPT_QDL_SCRIPT_NOT_FOUND"));
			return;
		}

		$pageNav = $this->getPageNavigation();

		$this->arResult['GridId'] = $this->gridId;
		$this->arResult['GridColumns'] = $this->getGridColumns();
		$this->arResult['GridRows'] = $this->getGridRows($queue, $script, $pageNav);
		$this->arResult['PageNavigation'] = $pageNav;
		$this->arResult['PageSizes'] = $this->getPageSizes();

		return $this->includeComponentTemplate();
	}

	protected function getGridColumns(): array
	{
		return [
			['id' => 'DOCUMENT_ID', 'name' => GetMessage('BIZPROC_SCRIPT_QDL_COLUMN_DOCUMENT_ID'), 'default' => true],
			['id' => 'STATUS', 'name' => GetMessage('BIZPROC_SCRIPT_QDL_COLUMN_STATUS'), 'default' => true],
			['id' => 'WORKFLOW_ID', 'name' => GetMessage('BIZPROC_SCRIPT_QDL_COLUMN_WORKFLOW_ID'), 'default' => true],
		];
	}

	protected function getGridRows(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueue $queue, \Bitrix\Bizproc\Script\Entity\EO_Script $script,  \Bitrix\Main\UI\PageNavigation $pageNavigation): array
	{
		$docService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$docType = [$script->getModuleId(), $script->getEntity(), $script->getDocumentType()];

		$rows = [];
		$order = ['ID' => 'desc'];

		$dbResult = \Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable::getList([
			'filter' => [
				'=QUEUE_ID' => $queue->getId(),
			],
			'order' => $order,
			'limit' => $pageNavigation->getLimit(),
			'offset' => $pageNavigation->getOffset(),
		]);

		$pageNavigation->setRecordCount(\Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable::getCount([
			'=QUEUE_ID' => $queue->getId(),
		]));

		while($row = $dbResult->fetch())
		{
			$rowData = $row;
			$docId = [$docType[0], $docType[1], $row['DOCUMENT_ID']];

			$rowData += [
				'DOCUMENT_NAME' => $docService->getDocumentName($docId),
				'DOCUMENT_URL' => $docService->getDocumentAdminPage($docId),
			];

			$rowActions = [];

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