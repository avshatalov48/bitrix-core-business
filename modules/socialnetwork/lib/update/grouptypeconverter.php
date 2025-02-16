<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Update\Stepper;
use Bitrix\Socialnetwork\Internals\Group\GroupEntity;
use Bitrix\Socialnetwork\Internals\Group\GroupEntityCollection;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\WorkgroupTable;

final class GroupTypeConverter extends Stepper
{
	private const LIMIT = 500;

	protected static $moduleId = 'socialnetwork';

	private GroupEntityCollection $groups;

	/** @var GroupEntityCollection[] */
	private array $storage;

	private array $option;

	public function execute(array &$option): bool
	{
		$this->option = &$option;

		$this->fetchGroups();

		if ($this->groups->isEmpty())
		{
			return self::FINISH_EXECUTION;
		}

		$this->setLastId();
		$this->convert();
		$this->save();

		return self::CONTINUE_EXECUTION;
	}

	private function fetchGroups(): void
	{
		$query = WorkgroupTable::query()
			->setSelect(['ID', 'PROJECT', 'SCRUM_MASTER_ID'])
			->whereNull('TYPE')
			->where('ID', '>', $this->getLastId())
			->setOrder(['ID' => 'ASC'])
			->setLimit(self::LIMIT);

		$this->groups = $query->exec()->fetchCollection();
	}

	private function convert(): void
	{
		foreach ($this->groups as $group)
		{
			$this->setType($group);
		}
	}

	private function save(): void
	{
		foreach ($this->storage as $collection)
		{
			$collection->save(true);
		}
	}

	private function setType(GroupEntity $group): void
	{
		if ($group->getScrumMasterId() > 0 && $group->getProject())
		{
			$group->setType(Type::Scrum->value);
		}
		elseif ($group->getProject())
		{
			$group->setType(Type::Project->value);
		}
		else
		{
			$group->setType(Type::getDefault()->value);
		}

		$this->store($group);
	}

	private function store(GroupEntity $group): void
	{
		if (!isset($this->storage[$group->getType()]))
		{
			$this->storage[$group->getType()] = new GroupEntityCollection();
		}

		$this->storage[$group->getType()]->add($group);
	}

	private function getLastId(): int
	{
		return (int)($this->option['lastId'] ?? 0);
	}

	private function setLastId(): void
	{
		$this->option['lastId'] = max($this->groups->getIdList());
	}
}