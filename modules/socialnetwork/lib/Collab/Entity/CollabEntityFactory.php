<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Entity;

use Bitrix\Calendar\Integration\SocialNetwork\Collab\Entity\EventEntity;
use Bitrix\Calendar\Integration\SocialNetwork\Collab\Entity\SectionEntity;
use Bitrix\Disk\Integration\Collab\Entity\FileEntity;
use Bitrix\Forum\Integration\Socialnetwork\Collab\Entity\CommentEntity;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectException;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;
use Bitrix\Tasks\Integration\SocialNetwork\Collab\Entity\CheckList\TaskCheckListEntity;
use Bitrix\Tasks\Integration\SocialNetwork\Collab\Entity\TaskEntity;
use ReflectionClass;

class CollabEntityFactory
{
	public static function getById(int $id, EntityType|string $type): ?CollabEntity
	{
		return static::get($type, $id);
	}

	public static function getByInternalObject(mixed $internalObject, EntityType|string $type): ?CollabEntity
	{
		return static::get($type, internalObject: $internalObject);
	}

	private static function get(EntityType|string $type, int $id = null, mixed $internalObject = null): ?CollabEntity
	{
		$entityType = is_string($type) ? EntityType::tryFrom($type) : $type;
		if ($entityType === null)
		{
			return null;
		}

		$config = static::getClassAndModule($entityType);
		if ($config === null)
		{
			return null;
		}

		[$class, $module] = $config;
		if (!Loader::includeModule($module))
		{
			return null;
		}

		if ($internalObject !== null && static::hasGetIdMethod($internalObject))
		{
			$id = (int)$internalObject->getId();
		}

		if ($id === null || $id <= 0)
		{
			return null;
		}

		try
		{
			return new $class($id);
		}
		catch (ObjectException)
		{
			return null;
		}
	}

	private static function hasGetIdMethod(mixed $internalObject): bool
	{
		if (!is_object($internalObject))
		{
			return false;
		}

		$reflection = new ReflectionClass($internalObject);

		return $reflection->hasMethod('getId') && $reflection->getMethod('getId')->isPublic();
	}

	private static function getClassAndModule(EntityType $type): ?array
	{
		return match($type)
		{
			EntityType::Task => [TaskEntity::class, 'tasks'],
			EntityType::CalendarSection => [SectionEntity::class, 'calendar'],
			EntityType::CalendarEvent => [EventEntity::class, 'calendar'],
			EntityType::Comment => [CommentEntity::class, 'forum'],
			EntityType::File => [FileEntity::class, 'disk'],
			EntityType::TaskCheckList => [TaskCheckListEntity::class, 'tasks'],
		};
	}
}
