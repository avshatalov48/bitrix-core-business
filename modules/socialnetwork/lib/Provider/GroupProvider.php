<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Internals\Registry\Event\GroupLoadedEvent;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class GroupProvider
{
	use InstanceTrait;

	protected static array $checkedGroups = [];
	protected static array $groupTypes = [];

	public function isExistingGroup(string $name, int $groupId = 0): bool
	{
		if (empty($name))
		{
			return false;
		}

		if (isset(static::$checkedGroups[$name]))
		{
			return static::$checkedGroups[$name];
		}

		$query = WorkgroupTable::query()
			->setSelect(['ID'])
			->where('NAME', $name);

		if ($groupId > 0)
		{
			$query->whereNot('ID', $groupId);
		}

		$groups = $query->exec()->fetchAll();

		static::$checkedGroups[$name] = !empty($groups);

		return static::$checkedGroups[$name];
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function getGroupType(int $groupId): ?Type
	{
		if (isset(static::$groupTypes[$groupId]))
		{
			return static::$groupTypes[$groupId];
		}

		$this->loadGroupTypes($groupId);

		return static::$groupTypes[$groupId] ?? null;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function loadGroupTypes(int ...$groupIds): void
	{
		$uncachedGroups = [];
		foreach ($groupIds as $groupId)
		{
			if (!isset(static::$groupTypes[$groupId]))
			{
				$uncachedGroups[] = $groupId;
			}
		}

		if (empty($uncachedGroups))
		{
			return;
		}

		$groups = WorkgroupTable::query()
			->setSelect(['ID', 'TYPE', 'SCRUM_MASTER_ID', 'PROJECT'])
			->setFilter(['ID' => $uncachedGroups])
			->fetchAll()
		;

		foreach ($groups as $group)
		{
			static::$groupTypes[$group['ID']] = $this->getTypeByFields($group);
		}
	}

	public function onObjectLoaded(GroupLoadedEvent $event): void
	{
		$group = $event->getGroup();
		$type = $group->getType();

		if ($type !== null)
		{
			static::$groupTypes[$group->getId()] = $type;
		}
	}

	private function getTypeByFields(array $fields): ?Type
	{
		if (isset($fields['TYPE']))
		{
			return Type::tryFrom($fields['TYPE']);
		}

		if (isset($fields['SCRUM_MASTER_ID']))
		{
			return Type::Scrum;
		}

		if (($fields['PROJECT'] ?? 'N') === 'Y')
		{
			return Type::Project;
		}

		return Type::getDefault();
	}
}