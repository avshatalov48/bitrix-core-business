<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Socialnetwork\Control\Member\AbstractMemberService;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

final class ServiceFactory
{
	protected static ?self $instance = null;

	public static function getInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getMemberService(int $entityId): AbstractMemberService
	{
		$entity = GroupRegistry::getInstance()->get($entityId);
		if ($entity === null)
		{
			throw new ObjectNotFoundException("Entity {$entityId} not found");
		}

		$type = $entity->getType();
		if ($type === null)
		{
			throw new ObjectNotFoundException("Entity {$entityId} has wrong type");
		}

		return $this->getMemberServiceByEntityType($type);
	}

	public function getMemberServiceByEntityType(Type $type): AbstractMemberService
	{
		$locator = ServiceLocator::getInstance();

		return match ($type)
		{
			Type::Collab => $locator->get('socialnetwork.collab.member.facade'),
			default => $locator->get('socialnetwork.group.member.service'),
		};
	}

	/**
	 * @throws ObjectNotFoundException
	 */
	public function getServiceByEntityId(int $entityId): AbstractGroupService
	{
		$entity = GroupRegistry::getInstance()->get($entityId);
		if ($entity === null)
		{
			throw new ObjectNotFoundException("Entity {$entityId} not found");
		}

		$type = $entity->getType();
		if ($type === null)
		{
			throw new ObjectNotFoundException("Entity {$entityId} has wrong type");
		}

		return $this->getServiceByEntityType($type);
	}

	public function getServiceByEntityType(Type $type): AbstractGroupService
	{
		$locator = ServiceLocator::getInstance();

		return match ($type)
		{
			Type::Collab => $locator->get('socialnetwork.collab.service'),
			default => $locator->get('socialnetwork.group.service'),
		};
	}
}