<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

abstract class BaseFilterFieldValidator extends BaseValidator implements ValidatorFilterInterface
{
	protected array $scalarFields;

	abstract protected function prepareFilterField(string $field): array;

	public function run(array $rawData): Result
	{
		$result = new Result();
		$this->prepareFilterLevel($rawData, $result);

		return $result;
	}

	protected function getAllowedFilterFields(): array
	{
		return
			$this->isExistsFieldAliases()
				? array_values($this->getFieldAliases())
				: $this->getFields()
		;
	}

	protected function prepareFilterLevel(array $rawData, Result $result): void
	{
		$filterFields = array_fill_keys($this->getAllowedFilterFields(), true);
		$fieldsMap = [];
		if ($this->isExistsFieldAliases())
		{
			$fieldsMap = $this->getFieldAliases();
			$fieldsMap = array_flip($fieldsMap);
		}
		foreach ($rawData as $filterKey => $filterValue)
		{
			if (is_string($filterKey))
			{
				$fieldInfo = $this->prepareFilterField($filterKey);
				$fieldName = $fieldInfo['FIELD'];
				if (!isset($filterFields[$fieldName]))
				{
					continue;
				}
				$realField = $fieldsMap[$fieldName] ?? $fieldName;
				if (!$this->isCorrectValue($realField, $filterValue))
				{
					$result->addError($this->getValueError($filterKey));
				}
			}
			elseif (is_int($filterKey) && is_array($filterValue))
			{
				$this->prepareFilterLevel($filterValue, $result);
			}
		}
	}

	protected function isCorrectValue(string $fieldName, mixed $value): bool
	{
		if ($this->isScalarField($fieldName))
		{
			return is_scalar($value);
		}

		if (is_array($value))
		{
			foreach ($value as $item)
			{
				if (!is_scalar($item))
				{
					return false;
				}
			}

			return true;
		}

		return parent::isCorrectValue($fieldName, $value);
	}

	protected function getValueError($fieldName): Error
	{
		return new Error(
			'Wrong format value of filter field `' . $fieldName . '`.'
		);
	}

	public function setScalarFields(array $fields): static
	{
		$this->scalarFields = array_fill_keys($fields, true);

		return $this;
	}

	public function getScalarFields(): array
	{
		return array_keys($this->scalarFields);
	}

	public function isScalarField(string $fieldName): bool
	{
		return isset($this->scalarFields[$fieldName]);
	}
}
