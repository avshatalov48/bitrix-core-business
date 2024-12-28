<?php

namespace Bitrix\Main\Grid\Column\DataProvider;

use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\DataProvider;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ScalarField;

class TabletColumnsProvider extends DataProvider
{
	public function __construct(
		private Entity $ormEntity,
		private ?array $selectFields = null,
		private ?array $defaultFields = null,
		private bool $isDefaultShow = true,
	)
	{}

	public function prepareColumns(): array
	{
		$result = [];

		$isEmptySelectFields = empty($this->selectFields);
		$isEmptyDefaultFields = empty($this->defaultFields);

		foreach ($this->ormEntity->getFields() as $field)
		{
			if (
				!$isEmptySelectFields
				&& !in_array($field->getName(), $this->selectFields)
			)
			{
				continue;
			}

			if ($field instanceof ScalarField)
			{
				if ($isEmptySelectFields && $field->isPrivate())
				{
					continue;
				}

				$column = $this->createColumnByField($field);
				$columnId = $column->getId();

				if ($isEmptyDefaultFields)
				{
					$column->setDefault(
						$this->isDefaultShow
					);
				}
				else
				{
					$column->setDefault(
						in_array($columnId, $this->defaultFields)
					);
				}

				$result[$columnId] = $column;
			}
		}

		return $result;
	}

	protected function createColumnByField(ScalarField $field): Column
	{
		$column = new Column($field->getName());
		$column
			->setName(
				$field->getTitle() ?: $field->getName()
			)
			->setType(
				$this->getColumnTypeByField($field)
			)
			->setSort(
				$column->getId()
			)
			->setEditable(true)
		;

		return $column;
	}

	protected function getColumnTypeByField(ScalarField $field): string
	{
		if ($field instanceof FloatField)
		{
			return Type::FLOAT;
		}
		elseif ($field instanceof IntegerField)
		{
			return Type::INT;
		}
		elseif ($field instanceof BooleanField)
		{
			return Type::CHECKBOX;
		}
		elseif (
			($field instanceof DateField)
			|| ($field instanceof DatetimeField)
		)
		{
			return Type::DATE;
		}

		return Type::TEXT;
	}
}
