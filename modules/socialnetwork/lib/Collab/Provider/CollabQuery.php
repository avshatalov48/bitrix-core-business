<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Main\ORM\Query\Filter\Condition;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

class CollabQuery
{
	protected ?ConditionTree $where = null;
	protected array $select = ['ID'];
	protected array $groupBy = [];
	protected array $order = [];
	protected int $offset = 0;
	protected int $limit = 0;
	protected bool $distinct = false;

	protected bool $checkAccess = false;

	protected int $userId;

	final public function __construct(int $userId = 0)
	{
		$this->userId = $userId;

		$this->init();
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getGroup(): array
	{
		return $this->groupBy;
	}

	public function setGroupBy(array $groupBy): CollabQuery
	{
		$this->groupBy = $groupBy;

		return $this;
	}

	public function getOffset(): int
	{
		return $this->offset;
	}

	public function setOffset(int $offset): CollabQuery
	{
		$this->offset = $offset;

		return $this;
	}

	public function getLimit(): int
	{
		return $this->limit;
	}

	public function setLimit(int $limit): CollabQuery
	{
		$this->limit = $limit;

		return $this;
	}

	public function getWhere(): ?ConditionTree
	{
		return $this->where;
	}

	public function setWhere(ConditionTree $where): static
	{
		$this->where->where($where);

		return $this;
	}

	public function addWhere(Condition $condition): static
	{
		$this->where->addCondition($condition);

		return $this;
	}

	public function getSelect(): array
	{
		return $this->select;
	}

	public function setSelect(array $select): static
	{
		$this->select = $select;

		if (empty($this->select))
		{
			$this->applyIdSelect();
		}

		return $this;
	}

	public function getDistinct(): bool
	{
		return $this->distinct;
	}

	public function setDistinct(bool $distinct = true): static
	{
		$this->distinct = $distinct;

		return $this;
	}

	public function getOrder(): array
	{
		return $this->order;
	}

	public function setOrder(array $order): static
	{
		$this->order = $order;

		return $this;
	}

	public function isOnlyId(): bool
	{
		return count($this->select) === 1 && in_array('ID', $this->select, true);
	}

	public function getAccessCheck(): bool
	{
		return $this->checkAccess;
	}

	public function setAccessCheck(bool $check = true): static
	{
		$this->checkAccess = $check;

		return $this;
	}

	protected function applyIdSelect(): void
	{
		$this->select[] = 'ID';
	}

	final protected function init(): void
	{
		$this->where = Query::filter();
		$this->where->where('TYPE', Type::Collab->value);
	}
}