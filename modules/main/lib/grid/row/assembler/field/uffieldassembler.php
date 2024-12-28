<?php

namespace Bitrix\Main\Grid\Row\Assembler\Field;

use Bitrix\Main\Grid\Row\FieldAssembler;

class UfFieldAssembler extends FieldAssembler
{
	private string $entityId;
	private array $fields;

	public function __construct(string $entityId)
	{
		$this->entityId = $entityId;

		parent::__construct(
			array_keys(
				$this->getFields()
			)
		);
	}

	private function getFields(): array
	{
		global $USER_FIELD_MANAGER;

		/**
		 * @var \CUserTypeManager $USER_FIELD_MANAGER
		 */

		if (!isset($this->fields))
		{
			$this->fields = [];

			$fields = $USER_FIELD_MANAGER->GetUserFields($this->entityId);
			foreach ($fields as $field)
			{
				if ($field['SHOW_IN_LIST'] !== 'Y')
				{
					continue;
				}

				$this->fields[$field['FIELD_NAME']] = $field;
			}
		}

		return $this->fields;
	}

	protected function prepareRow(array $row): array
	{
		if (empty($this->getColumnIds()))
		{
			return $row;
		}

		$row['columns'] ??= [];

		foreach ($this->getColumnIds() as $columnId)
		{
			$row['columns'][$columnId] = $this->prepareUf($row['data'][$columnId] ?? null, $columnId);
		}

		return $row;
	}

	private function prepareUf(mixed $value, string $columnId)
	{
		$field = $this->getFields()[$columnId] ?? null;
		if (empty($field))
		{
			return $value;
		}

		$callback = $field['USER_TYPE']['VIEW_CALLBACK'] ?? null;
		if (is_callable($callback))
		{
			$field['VALUE'] = $value;

			return call_user_func($callback, $field);
		}

		return $value;
	}
}
