<?php

namespace Bitrix\Socialnetwork\Space\List;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Space\List\Item\Builder;
use Bitrix\Socialnetwork\Space\List\Item\Space;
use Bitrix\Socialnetwork\Space\List\Query\Builder as QueryBuilder;
use Bitrix\Socialnetwork\Space\List\Query\LightweightBuilder;

final class Provider
{
	private const DEFAULT_LOAD_LIMIT = 25;

	private int $offset = 0;
	private Builder $builder;

	public function __construct(private int $userId, private string $mode = Dictionary::FILTER_MODES['all'])
	{
		$this->builder = new Builder($this->userId);
	}

	public function setMode(string $mode): self
	{
		$this->mode = $mode;

		return $this;
	}

	public function setOffset(int $offset): self
	{
		$this->offset = $offset;

		return $this;
	}

	public function getSpaces(): Result
	{
		$result = new Result();
		$limit = self::DEFAULT_LOAD_LIMIT;
		$queryBuilder =
			(new QueryBuilder($this->userId))
				->addModeFilter($this->mode)
				->addPaginationFilter($this->offset, $limit)
		;

		$query = $queryBuilder->build();

		$queryResult = $query->exec()->fetchAll();

		$spaces = $this->builder->buildSpacesFromQueryResult($queryResult);
		$result->setData([
			'spaces' => $spaces,
			'hasMoreSpacesToLoad' => count($spaces) === $limit,
		]);

		return $result;
	}

	public function searchSpaces(string $searchString): Result
	{
		$result = new Result();
		$limit = self::DEFAULT_LOAD_LIMIT;
		$queryBuilder =
			(new QueryBuilder($this->userId))
				->addModeFilter($this->mode)
				->addPaginationFilter($this->offset, $limit)
				->addSearchFilter($searchString)
		;

		$query = $queryBuilder->build();

		$queryResult = $query->exec()->fetchAll();

		$spaces = $this->builder->buildSpacesFromQueryResult($queryResult);
		$result->setData([
			'spaces' => $spaces,
			'hasMoreSpacesToLoad' => count($spaces) === $limit,
		]);

		return $result;
	}

	public function getSpaceById(int $spaceId): ?Space
	{
		if ($spaceId === 0)
		{
			return $this->getCommonSpace();
		}

		$query =
			(new QueryBuilder($this->userId))
				->addModeFilter($this->mode)
				->addSpaceIdFilter($spaceId)
				->build()
		;

		$queryResult = $query->exec()->fetchAll();

		$spaces = $this->builder->buildSpacesFromQueryResult($queryResult);

		return $spaces[0] ?? null;
	}

	/** @return array<Space> */
	public function getSpacesByIds(array $spaceIds): array
	{
		$query =
			(new QueryBuilder($this->userId))
				->addModeFilter($this->mode)
				->addSpaceIdListFilter($spaceIds)
				->addPaginationFilter(0, count($spaceIds))
				->build()
		;

		$queryResult = $query->exec()->fetchAll();

		return $this->builder->buildSpacesFromQueryResult($queryResult);
	}

	public function getCommonSpace(): Space
	{
		return $this->builder->buildCommonSpace();
	}

	/** @return array<int> */
	public function getMySpaceIds(): array
	{
		$query =
			(new LightweightBuilder($this->userId))
				->addModeFilter(Dictionary::FILTER_MODES['my'])
				->build()
		;

		$queryResult = $query->exec()->fetchAll();

		$result = [];
		foreach ($queryResult as $value)
		{
			$result[] = (int)($value['ID'] ?? null);
		}

		return $result;
	}
}