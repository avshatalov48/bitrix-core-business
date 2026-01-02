<?php

namespace Bitrix\Rest\V3\Structures\Aggregation;

enum AggregationType: string
{
	case Avg = 'avg';

	case Sum = 'sum';

	case Min = 'min';

	case Max = 'max';

	case Count = 'count';

	case CountDistinct = 'countDistinct';
}
