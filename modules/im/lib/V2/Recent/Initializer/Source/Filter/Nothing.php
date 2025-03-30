<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source\Filter;

use Bitrix\Im\V2\Recent\Initializer\Source\Filter;
use Bitrix\Main\ORM\Query\Query;

class Nothing implements Filter
{
	public function apply(Query $query, string $userIdFieldName): ?Query
	{
		return null;
	}
}