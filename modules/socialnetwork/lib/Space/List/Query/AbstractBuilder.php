<?php

namespace Bitrix\Socialnetwork\Space\List\Query;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;

abstract class AbstractBuilder
{
	abstract protected function getBaseQuery(): Query;

	/** @var array<FilterInterface> $filters */
	protected array $filters = [];

	public function __construct(protected int $userId)
	{}

	protected function addFilter(FilterInterface $filter): self
	{
		$this->filters[] = $filter;

		return $this;
	}

	public function build(): Query
	{
		$query = $this->getBaseQuery();
		foreach ($this->filters as $filler)
		{
			$filler->apply($query);
		}

		return $query;
	}
}