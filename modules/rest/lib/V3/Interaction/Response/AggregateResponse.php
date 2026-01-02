<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Rest\V3\Structures\Aggregation\AggregationResultStructure;

class AggregateResponse extends Response
{
	/**
	 * @param AggregationResultStructure $result
	 */
	public function __construct(public AggregationResultStructure $result)
	{
    }
}
