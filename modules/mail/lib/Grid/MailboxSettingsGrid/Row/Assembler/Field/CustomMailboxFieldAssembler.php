<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field;

use Bitrix\Intranet\User\Grid\Settings\UserSettings;
use Bitrix\Main\Grid\Row\FieldAssembler;

/**
 * @method UserSettings getSettings()
 */
abstract class CustomMailboxFieldAssembler extends FieldAssembler
{
	protected function prepareRow(array $row): array
	{
		if (empty($this->getColumnIds()))
		{
			return $row;
		}

		$row['columns'] ??= [];

		foreach ($this->getColumnIds() as $columnId)
		{
			if ($this->getSettings()->isExcelMode())
			{
				$row['columns'][$columnId] = $this->prepareColumnForExport($row['data']);
			}
			else
			{
				$row['columns'][$columnId] = $this->prepareColumn($row['data']);
			}
		}

		return $row;
	}

	protected function prepareColumnForExport(array $data): string
	{
		return $this->prepareColumn($data);
	}
}
