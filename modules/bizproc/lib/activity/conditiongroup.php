<?php

namespace Bitrix\Bizproc\Activity;

use Bitrix\Bizproc\FieldType;

class ConditionGroup
{
	public const JOINER_AND = 'AND'; // 0
	public const JOINER_OR = 'OR'; // 1

	protected array $items = [];
	protected array $result = [];
	protected array $parameterDocumentId = [];

	public function __construct(array $params = [])
	{
		if (!empty($params))
		{
			if (isset($params['items']) && is_array($params['items']))
			{
				foreach ($params['items'] as $item)
				{
					$this->addItem($item);
				}
			}
			if (isset($params['parameterDocumentId']) && is_array($params['parameterDocumentId']))
			{
				$this->parameterDocumentId = $params['parameterDocumentId'];
			}
		}
	}

	public function addItem(array $item): ConditionGroup
	{
		if (!empty($item))
		{
			$this->items[] = [
				'condition' => new Condition($item),
				'joiner' => isset($item['joiner']) && $item['joiner'] === 0 ? static::JOINER_AND : self::JOINER_OR,
				'valueToCheck' => $item['valueToCheck'] ?? null,
				'fieldType' => $item['fieldType'] ?? new FieldType([], [], 'Bitrix\Bizproc\BaseType\StringType')
			];
		}

		return $this;
	}

	public function getItems(): array
	{
		return $this->items;
	}

	public function getParameterDocumentId(): array
	{
		return $this->parameterDocumentId;
	}

	public function evaluate(): bool
	{
		$result = [0 => true];
		$i = 0;

		$this->result = [];

		foreach ($this->getItems() as $item)
		{
			$joiner = $item['joiner'];
			/** @var Condition $condition */
			$condition = $item['condition'];

			if ($condition->getOperator() === 'modified')
			{
				$conditionResult =
					is_array($condition->getValue()) && in_array($item['valueToCheck'], $condition->getValue(), true)
				;
			}
			else
			{
				$conditionResult = $condition->checkValue($item['valueToCheck'], $item['fieldType'], $this->parameterDocumentId);
			}

			if ($joiner === static::JOINER_OR)
			{
				++$i;
				$result[$i] = $conditionResult;
			}
			elseif (!$conditionResult)
			{
				$result[$i] = false;
			}

			$this->result[] = $conditionResult;
		}

		return (count(array_filter($result)) > 0);
	}

	public function getEvaluateResults(): array
	{
		return $this->result;
	}
}
