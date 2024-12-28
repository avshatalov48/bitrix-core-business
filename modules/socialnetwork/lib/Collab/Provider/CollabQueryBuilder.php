<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

class CollabQueryBuilder
{
	protected CollabQuery $collabQuery;
	protected Query $ormQuery;

	public static function build(CollabQuery $collabQuery): Query
	{
		return (new static($collabQuery))
			->buildSelect()
			->buildWhere()
			->buildGroup()
			->buildOrder()
			->buildNavigation()
			->buildAccess()
			->getOrmQuery();
	}

	private function __construct(CollabQuery $collabQuery)
	{
		$this->collabQuery = $collabQuery;

		$this->init();
	}

	protected function buildSelect(): static
	{
		$this->ormQuery->setDistinct($this->collabQuery->getDistinct());
		$this->ormQuery->setSelect($this->collabQuery->getSelect());

		return $this;
	}

	protected function buildWhere(): static
	{
		if ($this->collabQuery->getWhere() !== null)
		{
			$this->ormQuery->where($this->collabQuery->getWhere());
		}

		return $this;
	}

	protected function buildOrder(): static
	{
		$order = $this->collabQuery->getOrder();
		if (!empty($order))
		{
			$this->ormQuery->setOrder($order);
		}

		return $this;
	}

	protected function buildGroup(): static
	{
		$group = $this->collabQuery->getGroup();
		if (!empty($group))
		{
			$this->ormQuery->setGroup($group);
		}

		return $this;
	}

	protected function buildNavigation(): static
	{
		$limit = $this->collabQuery->getLimit();
		if ($limit > 0)
		{
			$this->ormQuery->setLimit($limit);
		}

		$offset = $this->collabQuery->getOffset();
		if ($offset > 0)
		{
			$this->ormQuery->setOffset($offset);
		}


		return $this;
	}

	protected function buildAccess(): static
	{
		if (!$this->collabQuery->getAccessCheck())
		{
			return $this;
		}

		$memberField = new ReferenceField(
			'MEMBERS',
			UserToGroupTable::getEntity(),
			Join::on('this.ID', 'ref.GROUP_ID')
				->where('ref.USER_ID', $this->collabQuery->getUserId())
				->whereIn('ref.ROLE', UserToGroupTable::getRolesMember()),
		);

		$this->ormQuery->registerRuntimeField($memberField);

		$conditions = Query::filter();

		$openGroupsCondition = Query::filter()->where('VISIBLE', 'Y');

		$memberCondition = Query::filter()->whereNotNull('MEMBERS.USER_ID');

		$conditions
			->logic('or')
			->where($openGroupsCondition)
			->where($memberCondition);

		$this->ormQuery->where($conditions);

		return $this;
	}

	protected function getOrmQuery(): Query
	{
		return $this->ormQuery;
	}

	protected function init(): void
	{
		$this->ormQuery = WorkgroupTable::query();
	}
}