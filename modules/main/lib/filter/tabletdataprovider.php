<?php

namespace Bitrix\Main\Filter;

use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ScalarField;

class TabletDataProvider extends DataProvider
{
	public function __construct(
		private Settings $settings,
		private Entity $entity,
		private ?array $selectFields = null,
		private ?array $defaultFields = null,
		private bool $isDefaultShow = true,
	)
	{}

	public function getSettings()
	{
		return $this->settings;
	}

	public function prepareFields(): array
	{
		$result = [];

		$isEmptySelectFields = empty($this->selectFields);
		$isEmptyDefaultFields = empty($this->defaultFields);

		foreach ($this->entity->getFields() as $field)
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

				$column = $this->createByField($field);
				$columnId = $column->getId();

				if ($isEmptyDefaultFields)
				{
					$column->markAsDefault(
						$this->isDefaultShow
					);
				}
				else
				{
					$column->markAsDefault(
						in_array($columnId, $this->defaultFields)
					);
				}

				$result[$columnId] = $column;
			}
		}

		return $result;
	}

	protected function createByField(ScalarField $field): Field
	{
		$result = new Field($this, $field->getName());
		$result->setName(
				$field->getTitle() ?: $field->getName()
		);
		$result->setType(
			$this->getTypeByField($field)
		);

		return $result;
	}

	protected function getTypeByField(ScalarField $field): string
	{
		if (
			($field instanceof FloatField)
			|| ($field instanceof IntegerField)
		)
		{
			return 'number';
		}
		elseif ($field instanceof BooleanField)
		{
			return 'select';
		}
		elseif (
			($field instanceof DateField)
			|| ($field instanceof DatetimeField)
		)
		{
			return 'date';
		}

		return 'string';
	}

	public function prepareFieldData($fieldID)
	{
		return null;
	}
}
