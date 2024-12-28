<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Filter\Condition;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\CollabCollection;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Internals\Registry\UserRegistry;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Provider\FeatureProvider;
use Bitrix\Socialnetwork\Provider\GroupProvider;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class CollabProvider
{
	use InstanceTrait;

	protected bool $useCache = true;

	public function getList(CollabQuery $query): CollabCollection
	{
		$ormQuery = CollabQueryBuilder::build($query);

		$items = $ormQuery->exec()->fetchAll();

		$collection = new CollabCollection();
		foreach ($items as $item)
		{
			$collab = new Collab($item);
			$collection[$collab->getId()] = $collab;
		}

		return $collection;
	}

	public function getCountByUserId(int $userId): int
	{
		$query = (new CollabQuery($userId))->setAccessCheck();

		return $this->getCount($query);
	}

	public function getCount(CollabQuery $query): int
	{
		$query->setSelect([Query::expr('COUNT')->countDistinct('ID')]);

		$ormQuery = CollabQueryBuilder::build($query);

		$result = $ormQuery->exec()->fetch();

		return (int)$result['COUNT'];
	}

	public function getListByUserId(int $userId, ?CollabQuery $query = null): CollabCollection
	{
		$registry = UserRegistry::getInstance($userId);
		if (!$this->useCache)
		{
			$registry->invalidate($userId);
		}

		$userGroups = $registry->getUserGroups(UserRegistry::MODE_COLLAB);

		$this->enableCache();

		$userGroups = array_keys($userGroups);

		Collection::normalizeArrayValuesByInt($userGroups, false);

		if (empty($userGroups))
		{
			return new CollabCollection();
		}

		if ($query === null || $query->isOnlyId())
		{
			$collabs = array_map(static fn (int $id) => Collab::createFromId($id), $userGroups);

			return new CollabCollection(...$collabs);
		}

		$query->addWhere(new Condition('ID', 'in', $userGroups));

		$collabs = $this->getList($query);

		return $collabs;
	}

	public function getCollab(int $id): ?Collab
	{
		$registry = CollabRegistry::getInstance();
		if (!$this->useCache)
		{
			$registry->invalidate($id);
		}

		$collab = $registry->get($id);

		$this->enableCache();

		if ($collab === null)
		{
			return null;
		}


		$optionsProvider = CollabOptionProvider::getInstance();

		$collab->setOptions(...$optionsProvider->get($id));

		$featuresProvider = FeatureProvider::getInstance();

		$collab->setFeatures(...$featuresProvider->getFeatures($id));

		$collab->setPermissions(...$featuresProvider->getPermissions($id));

		return $collab;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function isCollab(int $id): bool
	{
		return GroupProvider::getInstance()->getGroupType($id) === Type::Collab;
	}

	public function isExistingGroup(string $name, int $groupId = 0): bool
	{
		return GroupProvider::getInstance()->isExistingGroup($name, $groupId);
	}

	public function enableCache(): static
	{
		$this->useCache = true;

		return $this;
	}

	public function disableCache(): static
	{
		$this->useCache = false;

		return $this;
	}
}