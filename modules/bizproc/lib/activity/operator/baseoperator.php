<?php

namespace Bitrix\Bizproc\Activity\Operator;

use Bitrix\Bizproc\FieldType;

class BaseOperator
{
	protected $toCheck;
	protected $value;
	protected FieldType $fieldType;

	public static function getCode(): string
	{
		return 'BASE';
	}

	public static function getTitle(): string
	{
		return '';
	}

	public function __construct($toCheck, $value, FieldType $fieldType)
	{
		$this->fieldType = $fieldType;
		$this->toCheck = $toCheck;
		$this->value = $value;

		$this->toBaseType();
	}

	protected function toBaseType(): void
	{
		$baseType = $this->fieldType->getBaseType();
		$documentId = $this->fieldType->getDocumentId();
		if ($baseType === 'user')
		{
			$this->toCheck = \CBPHelper::extractUsers($this->toCheck, $documentId);
			$this->value = \CBPHelper::extractUsers($this->value, $documentId);
		}
		elseif ($baseType === 'select')
		{
			if (is_array($this->toCheck) && \CBPHelper::isAssociativeArray($this->toCheck))
			{
				$this->toCheck = array_keys($this->toCheck);
			}
		}
	}

	public function check(): bool
	{
		$toCheck = $this->valueToArray($this->toCheck);
		$value = $this->valueToArray($this->value);

		$result = false;

		$fieldCount = count($toCheck);
		$valueCount = count($value);
		for ($i = 0; $i < max($fieldCount, $valueCount); $i++)
		{
			$fieldI = ($fieldCount > $i) ? $toCheck[$i] : $toCheck[$fieldCount -1];
			$valueI = ($valueCount > $i) ? $value[$i] : $value[$valueCount - 1];

			[$valueI, $fieldI] = $this->normalizeZeroComparing($valueI, $fieldI);

			$result = $this->compare($fieldI, $valueI);

			if (!$result)
			{
				break;
			}
		}

		return $result;
	}

	protected function compare($toCheck, $value): bool
	{
		return $toCheck === $value;
	}

	protected function valueToArray($value): array
	{
		$value = is_array($value) ? $value : [$value];
		$value = \CBPHelper::isAssociativeArray($value) ? array_keys($value) : $value;
		if (empty($value))
		{
			$value = [null];
		}

		return $value;
	}

	protected function normalizeZeroComparing($value, $field): array
	{
		if ($value === '' && ($field === '0' || $field === 0.0))
		{
			return [null, null];
		}

		if (($value === '0' || $value === 0.0) && $field === '')
		{
			return [null, null];
		}

		return [$value, $field];
	}
}