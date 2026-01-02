<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Trait;

use Bitrix\Iblock\Public\Service\RestValidator\Format\BaseValidator;
use Bitrix\Main\Engine\Response\Converter;

trait CanonicalNameTrait
{
	protected array $canonicalNames = [];

	abstract protected function fillCanonicalNames(): void;

	protected function setCanonicalNames(array $canonicalNames): void
	{
		$this->canonicalNames = $canonicalNames;
	}

	protected function getCanonicalNames(): array
	{
		return $this->canonicalNames;
	}

	protected function replaceCanonicalNames(array $fields, array $canonicalNames): array
	{
		if (empty($fields) || empty($canonicalNames))
		{
			return $fields;
		}

		$canonicalNames = array_flip($canonicalNames);
		foreach (array_keys($fields) as $index)
		{
			$currentField = $fields[$index];
			if (!isset($canonicalNames[$currentField]))
			{
				continue;
			}
			$fields[$currentField] = $canonicalNames[$currentField];
			unset($fields[$index]);
		}

		return $fields;
	}

	protected function getCamelCaseAliases(array $fields): array
	{
		if (empty($fields))
		{
			return [];
		}

		$converter = Converter::toJson();
		$result = [];
		foreach ($fields as $index => $fieldName)
		{
			$resultIndex = (is_string($index) ? $index : $fieldName);
			$result[$resultIndex] = $converter->process($fieldName);
		}

		return $result;
	}

	protected function getFieldAliases(BaseValidator $validator): array
	{
		$fields = $validator->getFields();
		if (empty($fields))
		{
			return [];
		}
		$fields = $this->replaceCanonicalNames($fields, $this->getCanonicalNames());

		return $this->getCamelCaseAliases($fields);
	}
}
