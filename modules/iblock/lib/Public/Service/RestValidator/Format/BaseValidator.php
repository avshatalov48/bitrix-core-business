<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

abstract class BaseValidator implements ValidatorInterface
{
	protected static array $instances;

	protected array $fieldAliases = [];

	protected array $fields = [];

	public static function getInstance(): static
	{
		$class = get_called_class();
		if (!isset(self::$instances[$class]))
		{
			self::$instances[$class] = new $class();
		}

		return self::$instances[$class];
	}

	protected function __construct()
	{
		$this->init();
	}

	abstract protected function init(): void;

	public function setFields(array $fields): static
	{
		$this->fields = $fields;

		return $this;
	}

	public function getFields(): array
	{
		return $this->fields ?? [];
	}

	public function isExistsFieldAliases(): bool
	{
		return !empty($this->fieldAliases);
	}

	public function setFieldAliases(array $aliases): static
	{
		$preparedAliases = [];
		foreach ($aliases as $field => $alias)
		{
			if (!is_string($field) || empty($field))
			{
				continue;
			}
			if (!is_string($alias) || empty($alias))
			{
				continue;
			}
			$preparedAliases[$field] = $alias;
		}
		$this->fieldAliases = $preparedAliases;

		return $this;
	}

	public function getFieldAliases(): array
	{
		return $this->fieldAliases;
	}

	public function getAlias(string $fieldName): string
	{
		return $this->fieldAliases[$fieldName] ?? $fieldName;
	}

	public function getRealAlias(string $fieldName): ?string
	{
		return $this->fieldAliases[$fieldName] ?? null;
	}

	public function run(array $rawData): Result
	{
		$result = new Result();

		$useAliases = $this->isExistsFieldAliases();
		foreach ($this->getFields() as $fieldName)
		{
			$index = $useAliases ? $this->getRealAlias($fieldName) : $fieldName;
			if ($index === null)
			{
				continue;
			}
			if (!isset($rawData[$index]))
			{
				continue;
			}
			if (!$this->isCorrectValue($fieldName, $rawData[$index]))
			{
				$result->addError($this->getValueError($index));
			}
		}

		return $result;
	}

	protected function isCorrectValue(string $fieldName, mixed $value): bool
	{
		return is_scalar($value);
	}

	protected function getValueError($fieldName): Error
	{
		return new Error(
			'Wrong format of field `' . $fieldName . '`.'
		);
	}
}
