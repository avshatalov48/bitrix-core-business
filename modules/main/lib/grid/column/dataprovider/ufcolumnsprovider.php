<?php

namespace Bitrix\Main\Grid\Column\DataProvider;

use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\DataProvider;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\UserField\Types\BooleanType;
use Bitrix\Main\UserField\Types\DateTimeType;
use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\UserField\Types\DoubleType;
use Bitrix\Main\UserField\Types\IntegerType;

class UfColumnsProvider extends DataProvider
{
	public function __construct(
		private string $entityId,
		private ?array $selectFields = null,
		private ?array $defaultFields = null,
		private bool $isDefaultShow = true,
	)
	{}

	public function prepareColumns(): array
	{
		global $USER_FIELD_MANAGER;

		/**
		 * @var \CUserTypeManager $USER_FIELD_MANAGER
		 */

		$result = [];

		$isEmptyDefaultFields = empty($this->defaultFields);

		$fields = $USER_FIELD_MANAGER->GetUserFields(
			$this->entityId,
			LANG: LANGUAGE_ID,
			selectFields: $this->selectFields
		);
		foreach ($fields as $field)
		{
			if ($field['SHOW_IN_LIST'] !== 'Y')
			{
				continue;
			}

			$column = $this->createColumnByField($field);
			$columnId = $column->getId();

			if ($isEmptyDefaultFields)
			{
				$column->setDefault($this->isDefaultShow);
			}
			else
			{
				$column->setDefault(
					in_array($columnId, $this->defaultFields)
				);
			}

			$result[$columnId] = $column;
		}

		return $result;
	}

	protected function createColumnByField(array $field): Column
	{
		$column = new Column($field['FIELD_NAME']);
		$column->setName(
			$field['LIST_COLUMN_LABEL'] ?? $field['FIELD_NAME']
		);
		$column->setType(
			$this->getColumnTypeByField($field)
		);
		$column->setMultiple(
			$field['MULTIPLE'] === 'Y'
		);

		return $column;
	}

	protected function getColumnTypeByField(array $field): string
	{
		$type = $field['USER_TYPE_ID'] ?? null;

		if ($type === DoubleType::USER_TYPE_ID)
		{
			return Type::FLOAT;
		}
		elseif ($type === IntegerType::USER_TYPE_ID)
		{
			return Type::INT;
		}
		elseif ($type === BooleanType::USER_TYPE_ID)
		{
			return Type::CHECKBOX;
		}
		elseif (
			($type === DateTimeType::USER_TYPE_ID)
			|| ($type === DateType::USER_TYPE_ID)
		)
		{
			return Type::DATE;
		}

		return Type::TEXT;
	}
}
