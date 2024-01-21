<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Id;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;

final class IdListFilter implements FilterInterface
{
	public function __construct(private array $spaceIds)
	{}

	public function apply(Query $query): void
	{
		$query->whereIn('ID', $this->spaceIds);
	}
}
