<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Search;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;

final class SearchFilter implements FilterInterface
{
	public function __construct(private string $searchString)
	{}

	public function apply(Query $query): void
	{
		$searchStringPrepared = addcslashes($this->searchString, '%_');

		$query->where(Query::filter()
			->logic(ConditionTree::LOGIC_OR)
			->whereLike('NAME', "%$searchStringPrepared%")
			->whereLike('TAG.NAME', "%$searchStringPrepared%")
		);

		$query->setGroup('ID');
	}
}