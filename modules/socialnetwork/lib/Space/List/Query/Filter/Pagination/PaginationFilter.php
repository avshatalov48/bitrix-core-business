<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Pagination;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;

final class PaginationFilter implements FilterInterface
{
	public function __construct(private int $offset, private int $limit)
	{}

	public function apply(Query $query): void
	{
		$query->setOffset($this->offset);
		$query->setLimit($this->limit);
	}
}