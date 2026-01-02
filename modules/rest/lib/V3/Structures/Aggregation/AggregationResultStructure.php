<?php

namespace Bitrix\Rest\V3\Structures\Aggregation;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Rest\V3\Interaction\Request\Request;
use Bitrix\Rest\V3\Structures\Structure;

final class AggregationResultStructure extends Structure implements Arrayable
{
	public static function create(mixed $value, string $dtoClass, Request $request): Structure
	{
		return new self();
	}

	/** @var ResultItem[] */
	protected array $items;

	public function add(ResultItem $item): self
	{
		$this->items[] = $item;

		return $this;
	}

	public function toArray(): array
	{
		$byAggregation = [];

		foreach ($this->items as $item)
		{
			if (!isset($byAggregation[$item->aggregation->value]))
			{
				$byAggregation[$item->aggregation->value] = [];
			}

			$byAggregation[$item->aggregation->value][$item->field] = $item->value;
		}

		return $byAggregation;
	}
}
