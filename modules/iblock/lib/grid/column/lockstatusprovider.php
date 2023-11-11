<?php

namespace Bitrix\Iblock\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;

abstract class LockStatusProvider extends BaseElementProvider
{
	public function prepareColumns(): array
	{
		if ($this->isSkuSelectorEnabled())
		{
			return [];
		}
		if (!$this->usedWorkflow() && !$this->usedBusinessProcesses())
		{
			return [];
		}

		return $this->createColumns([
			'LOCK_STATUS' => [
				'type' => Grid\Column\Type::CUSTOM,
				'name' => Loc::getMessage('IBLOCK_LOCK_STATUS_ELEMENT_COLUMN_PROVIDER_FIELD_LOCK_STATUS'),
				'necessary' => true,
				'editable' => false,
				'multiple' => false,
				'align' => 'center',
			],
		]);
	}

	protected function usedWorkflow(): bool
	{
		return $this->getSettings()->isUseWorkflow();
	}

	protected function usedBusinessProcesses(): bool
	{
		return $this->getSettings()->isUseBusinessProcesses();
	}
}
