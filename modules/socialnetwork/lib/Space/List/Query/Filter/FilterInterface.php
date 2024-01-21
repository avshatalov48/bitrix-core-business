<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter;

use Bitrix\Main\ORM\Query\Query;

interface FilterInterface
{
	public function apply(Query $query): void;
}