<?php

namespace Bitrix\Lists\Api\Data;

class Filter
{
	public const ALLOWABLE_FIELDS = [];

	protected array $filter = [];
	protected array $computedFilter = [];
	protected array $keyMatching = [];

	public static function initializeFromArray(array $filter): static
	{
		return (new static())->setFromArray($filter);
	}

	public function getOrmFilter(): array
	{
		$this->execComputedFilter();

		return $this->filter;
	}

	protected function execComputedFilter(): void
	{}

	public function setField(string $fieldId, $value, string $operator = ''): static
	{
		if (in_array($fieldId, static::ALLOWABLE_FIELDS, true))
		{
			if (array_key_exists($fieldId, $this->keyMatching))
			{
				unset($this->filter[$this->keyMatching[$fieldId]]);
			}

			$key = $operator . $fieldId;
			$this->keyMatching[$fieldId] = $key;
			$this->filter[$key] = $value;
		}

		return $this;
	}

	public function hasField(string $fieldId): bool
	{
		return array_key_exists($fieldId, $this->keyMatching) || in_array($fieldId, $this->keyMatching, true);
	}

	public function getFieldValue(string $fieldId)
	{
		if ($this->hasField($fieldId))
		{
			$key = $this->keyMatching[$fieldId] ?? $fieldId;

			return $this->filter[$key];
		}

		return null;
	}

	public function setFromArray(array $filter): static
	{
		$allowableOperations = ['=', '!=', '<', '<=', '>', '>='];

		foreach ($filter as $key => $value)
		{
			$operator = '';
			for ($i = 2; $i > 0; $i--)
			{
				$possibleOperator = mb_substr($key, 0, $i);
				if ($possibleOperator && in_array($possibleOperator, $allowableOperations, true))
				{
					$operator = $possibleOperator;
					$key = mb_substr($key, $i);

					break;
				}
			}

			if (in_array($key, static::ALLOWABLE_FIELDS, true))
			{
				$this->setField($key, $value, $operator);
			}
		}

		return $this;
	}
}
