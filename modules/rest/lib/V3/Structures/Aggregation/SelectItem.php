<?php

namespace Bitrix\Rest\V3\Structures\Aggregation;

class SelectItem
{
	/**
	 * @param AggregationType $aggregation
	 * @param string $field
	 * @param string|null $alias
	 */
	public function __construct(
		public AggregationType $aggregation,
		public string $field,
		public ?string $alias = null,
	) {
	}
}
