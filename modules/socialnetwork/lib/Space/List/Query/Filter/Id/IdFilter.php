<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Id;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;

final class IdFilter implements FilterInterface
{
	public function __construct(private int $spaceId)
	{}

	public function apply(Query $query): void
	{
		$query->where('ID', $this->spaceId);
	}
}
