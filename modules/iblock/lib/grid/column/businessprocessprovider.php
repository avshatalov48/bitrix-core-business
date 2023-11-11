<?php

namespace Bitrix\Iblock\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;

/*
 * BP_PUBLISHED - STATUS - WF_STATUS_ID (order not worked)
 */

class BusinessProcessProvider extends LockStatusProvider
{
	public function prepareColumns(): array
	{
		$result = parent::prepareColumns();

		if (!$this->isSkuSelectorEnabled() && $this->usedBusinessProcesses())
		{
			$result['BP_PUBLISHED'] = $this->createColumn('BP_PUBLISHED', [
				'type' => Grid\Column\Type::CUSTOM,
				'name' => Loc::getMessage(''),
				'necessary' => true,
				'sort' => 'WF_STATUS_ID',
			]);
		}

		return $result;
	}
}
