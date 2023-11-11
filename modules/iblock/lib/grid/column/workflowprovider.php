<?php

namespace Bitrix\Iblock\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;

/*
 * STATUS - WF_STATUS_ID (order not worked)
 */
class WorkflowProvider extends LockStatusProvider
{
	public function prepareColumns(): array
	{
		$result = parent::prepareColumns();

		if (!$this->isSkuSelectorEnabled() && $this->usedWorkflow())
		{
			$result['WF_STATUS_ID'] = $this->createColumn('WF_STATUS_ID', [
				'type' => Grid\Column\Type::CUSTOM,
				'name' => Loc::getMessage('IBLOCK_WORKFLOW_COLUMN_PROVIDER_FIELD_WF_STATUS_ID'),
				'necessary' => true,
				'sort' => 'WF_STATUS_ID',
			]);
		}

		return $result;
	}
}
