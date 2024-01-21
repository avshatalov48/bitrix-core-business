<?php

namespace Bitrix\Socialnetwork\Space\List\Query;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;
use Bitrix\Socialnetwork\Space\List\Query\Filter;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\WorkgroupTable;

final class Builder
{
	private const SELECT = [
		'ID',
		'NAME',
		'DATE_ACTIVITY',
		'IMAGE_ID',
		'AVATAR_TYPE',
		'VISIBLE',
		'OPENED',
		'ROLE' => 'MEMBER.ROLE',
//		'USER_ROLE_DATE_UPDATE' => 'MEMBER.DATE_UPDATE',
		'ROLE_INIT_BY_TYPE' => 'MEMBER.INITIATED_BY_TYPE',
//		'USER_ROLE_INIT_BY_USER_ID' => 'MEMBER.INITIATED_BY_USER_ID',
		'PIN_ID' => 'PIN.ID'
	];

	/** @var array<FilterInterface> $filters */
	private array $filters = [];

	public function __construct(private int $userId)
	{
	}

	private function addFilter(FilterInterface $filter): self
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

	public function addModeFilter(string $mode): self
	{
		if ($mode === Dictionary::FILTER_MODES['all'])
		{
			$this->addFilter(new Filter\Mode\AllModeFilter($this->userId));
		}
		elseif ($mode === Dictionary::FILTER_MODES['my'])
		{
			$this->addFilter(new Filter\Mode\MyModeFilter($this->userId));
		}
		elseif ($mode === Dictionary::FILTER_MODES['other'])
		{
			$this->addFilter(new Filter\Mode\OtherModeFilter($this->userId));
		}

		return $this;
	}

	public function addPaginationFilter(int $offset, int $limit): self
	{
		return $this->addFilter(new Filter\Pagination\PaginationFilter($offset, $limit));
	}

	public function addSpaceIdFilter(int $spaceId): self
	{
		return $this->addFilter(new Filter\Id\IdFilter($spaceId));
	}

	public function addSpaceIdListFilter(array $spaceIds): self
	{
		return $this->addFilter(new Filter\Id\IdListFilter($spaceIds));
	}

	public function addNameSearchFilter(string $searchString): self
	{
		return $this->addFilter(new Filter\Search\NameSearchFilter($searchString));
	}

	private function getBaseQuery(): Query
	{
		$join =
			Join::on('this.ID', 'ref.GROUP_ID')
				->where('ref.USER_ID', $this->userId)
		;

		$query = WorkgroupTable::query();
		$query
			->setSelect(self::SELECT)
			->addOrder('DATE_ACTIVITY', 'DESC')
			->registerRuntimeField(
				(new Reference(
					'PIN',
					WorkgroupPinTable::class,
					$join,
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
			->registerRuntimeField(
				(new Reference(
					'MEMBER',
					UserToGroupTable::class,
					$join,
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
		;

		return $query;
	}
}