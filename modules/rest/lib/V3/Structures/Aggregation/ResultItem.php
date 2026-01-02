<?php

namespace Bitrix\Rest\V3\Structures\Aggregation;

class ResultItem
{
	/**
	 * @param AggregationType $aggregation
	 * @param string $field
	 * @param mixed $value
	 */
	public function __construct(
		public AggregationType $aggregation,
		public string $field,
		public mixed $value,
	) {
	}
}
