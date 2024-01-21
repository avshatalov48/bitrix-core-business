<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Search;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;

final class NameSearchFilter implements FilterInterface
{
	public function __construct(private string $searchString)
	{}

	public function apply(Query $query): void
	{
		$searchStringPrepared = addcslashes($this->searchString, '%_');
		$query->whereLike('NAME', "%$searchStringPrepared%");
	}
}