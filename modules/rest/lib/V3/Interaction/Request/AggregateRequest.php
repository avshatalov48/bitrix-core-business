<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Structures\Aggregation\AggregationSelectStructure;
use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;

class AggregateRequest extends Request
{
	public AggregationSelectStructure $select;

	public ?FilterStructure $filter = null;
}
