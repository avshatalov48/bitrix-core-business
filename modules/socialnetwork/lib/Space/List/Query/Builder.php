<?php

namespace Bitrix\Socialnetwork\Space\List\Query;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlHelper;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Internals\Space\RecentActivity\SpaceUserLatestActivityTable;
use Bitrix\Socialnetwork\Internals\Space\RecentActivity\SpaceUserRecentActivityTable;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;
use Bitrix\Socialnetwork\Space\List\Query\Filter;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupTagTable;

final class Builder extends AbstractBuilder
{
	private const SELECT = [
		'ID',
		'NAME',
		'DATE_ACTIVITY',
		'DATE_CREATE',
		'RECENT_ACTIVITY_DATE',
		'IMAGE_ID',
		'AVATAR_TYPE',
		'VISIBLE',
		'OPENED',
		'ROLE' => 'MEMBER.ROLE',
		'ROLE_INIT_BY_TYPE' => 'MEMBER.INITIATED_BY_TYPE',
		'RECENT_ACTIVITY_ID' => 'RECENT_ACTIVITY.ID',
		'RECENT_ACTIVITY_ENTITY_ID' => 'RECENT_ACTIVITY.ENTITY_ID',
		'RECENT_ACTIVITY_TYPE_ID' => 'RECENT_ACTIVITY.TYPE_ID',
		'RECENT_ACTIVITY_DATETIME' => 'RECENT_ACTIVITY.DATETIME',
		'RECENT_ACTIVITY_SECONDARY_ENTITY_ID' => 'RECENT_ACTIVITY.SECONDARY_ENTITY_ID',
		'PIN_ID' => 'PIN.ID',
	];

	private SqlHelper $sqlHelper;

	private bool $searchMode = false;

	public function __construct(int $userId)
	{
		parent::__construct($userId);

		$this->sqlHelper = Application::getConnection()->getSqlHelper();
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

	public function addSearchFilter(string $searchString): self
	{
		$this->searchMode = true;

		return $this->addFilter(new Filter\Search\SearchFilter($searchString));
	}

	protected function getBaseQuery(): Query
	{
		$groupJoin =
			Join::on('this.ID', 'ref.GROUP_ID')
				->where('ref.USER_ID', $this->userId)
		;
		$spaceJoin =
			Join::on('this.ID', 'ref.SPACE_ID')
				->where('ref.USER_ID', $this->userId)
		;

		$query = WorkgroupTable::query();
		$query
			->setSelect(self::SELECT)
			->addOrder('RECENT_ACTIVITY_DATE', 'DESC')
			->registerRuntimeField(
				(new Reference(
					'PIN',
					WorkgroupPinTable::class,
					$groupJoin,
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
			->registerRuntimeField(
				(new Reference(
					'LATEST_ACTIVITY',
					SpaceUserLatestActivityTable::class,
					$spaceJoin,
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
			->registerRuntimeField(
				(new Reference(
					'RECENT_ACTIVITY',
					SpaceUserRecentActivityTable::class,
					Join::on('this.LATEST_ACTIVITY.ACTIVITY_ID','ref.ID'),
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
			->registerRuntimeField(
				'RECENT_ACTIVITY_DATE',
				new ExpressionField(
					'RECENT_ACTIVITY_DATE',
					$this->sqlHelper->getIsNullFunction('%s', '%s'),
					['RECENT_ACTIVITY.DATETIME', 'DATE_ACTIVITY'],
				)
			)
			->registerRuntimeField(
				(new Reference(
					'MEMBER',
					UserToGroupTable::class,
					$groupJoin,
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
		;

		if ($this->searchMode)
		{
			$query->registerRuntimeField(
				(new Reference(
					'TAG',
					WorkgroupTagTable::class,
					Join::on('this.ID', 'ref.GROUP_ID'),
				))
					->configureJoinType(Join::TYPE_LEFT)
			);
		}

		return $query;
	}
}