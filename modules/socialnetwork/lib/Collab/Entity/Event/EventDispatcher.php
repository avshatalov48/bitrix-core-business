<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Entity\Event;

use Bitrix\Im\V2\Integration\Socialnetwork\Collab;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\CollabDictionary;
use Bitrix\Socialnetwork\Collab\Activity\LastActivityTrigger;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabUpdateEvent;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntityFactory;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;
use Bitrix\Socialnetwork\Collab\Internals\CollabLogTable;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddCalendarEventLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddFileLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddFilePublicLinkLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddTaskLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\CompleteTaskLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\DeleteCalendarEventLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\DeleteFileLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\DeleteTaskLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\MoveFileToRecyclebinLogEntry;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Collab\Log\Entry\CopyInvitationLinkLogEntry;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Collab\User\Stepper\SendCollabLeaveMessageStepper;
use Bitrix\Socialnetwork\Integration\Disk\ObjectType;
use Bitrix\Socialnetwork\Integration\Tasks\TaskStatus;

final class EventDispatcher
{
	public static function onCountersRecount(int $collabId, array $counters, string $entityType): void
	{
		if (Loader::includeModule('im'))
		{
			Collab::onEntityCountersUpdate($collabId, $counters, $entityType);
		}
	}

	public static function onTaskAdd(int $taskId, array $fields = [], array $parameters = []): void
	{
		$entity = CollabEntityFactory::getById($taskId, EntityType::Task->value);
		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();
		$executorId = (int)($parameters['USER_ID'] ?? 0);

		self::sendAddEvent($entity);
		LastActivityTrigger::execute($executorId, $collabId);

		$logEntry = new AddTaskLogEntry(userId: $executorId, collabId: $collabId, collabEntity: $entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onTaskUpdate(int $taskId, array $changes = [], array $fields = [], array $parameters = []): void
	{
		// in collab now
		$entity = CollabEntityFactory::getById($taskId, EntityType::Task->value);
		if ($entity !== null)
		{
			self::sendUpdateEvent($changes, $entity);

			if (($changes['STATUS'] ?? null) === TaskStatus::getCompletedStatus())
			{
				$collabId = $entity->getCollab()->getId();
				$executorId = (int)($changes['STATUS_CHANGED_BY'] ?? null);

				if ($executorId <= 0)
				{
					return;
				}

				$logEntry = new CompleteTaskLogEntry(userId: $executorId, collabId: $collabId, collabEntity: $entity);
				$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
				$service->save($logEntry);
			}

			return;
		}

		$oldGroupId = (int)($fields['GROUP_ID'] ?? 0);

		// no collab changes
		if ($oldGroupId <= 0)
		{
			return;
		}

		// removed from collab
		$isCollab = CollabRegistry::getInstance()->get($oldGroupId) !== null;
		if ($isCollab)
		{
			self::sendUpdateEvent($changes);
		}
	}

	public static function onTaskDelete(int $taskId, array $parameters): void
	{
		$entity = CollabEntityFactory::getById($taskId, EntityType::Task->value);
		if ($entity === null)
		{
			return;
		}

		self::sendDeleteEvent($entity);

		$userId = $parameters['USER_ID'] ?? null;

		if ($userId <=0)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();
		$logEntry = new DeleteTaskLogEntry(userId: $userId, collabId: $collabId, collabEntity: $entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function OnAfterCommentAdd(Event $event): void
	{
		$message = $event->getParameter(2)['MESSAGE'] ?? null;
		$messageId = (int)($message['ID'] ?? null);
		if (empty($messageId))
		{
			return;
		}

		$entity = CollabEntityFactory::getById($messageId, EntityType::Comment->value);
		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();
		$executorId = (int)($message['AUTHOR_ID'] ?? 0);

		self::sendAddEvent($entity);
		LastActivityTrigger::execute($executorId, $collabId);
	}

	public static function OnAfterCalendarEventEdit(array $fields, bool $isNew, int $userId): void
	{
		$eventId = (int)($fields['ID'] ?? null);
		if (empty($eventId) || !$isNew)
		{
			return;
		}

		$entity = CollabEntityFactory::getById($eventId, EntityType::CalendarEvent->value);
		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();

		self::sendAddEvent($entity);
		LastActivityTrigger::execute($userId, $collabId);

		$collabId = $entity->getCollab()->getId();
		$logEntry = new AddCalendarEventLogEntry(userId: $userId, collabId: $collabId, collabEntity: $entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onAfterUserDelete(int $userId): void
	{
		if ($userId <= 0)
		{
			return;
		}

		LastActivityTrigger::remove(userIds: [$userId]);

		$userCollabs = CollabProvider::getInstance()->getListByUserId($userId)->getIdList();

		// firedId, whoFiredId, collabs
		SendCollabLeaveMessageStepper::bind(0, [$userId, (int)CurrentUser::get()->getId(), serialize($userCollabs),]);
	}

	public static function onAfterAddFile(Event $event): void
	{
		$file = $event->getParameter(0);

		$fileId = (int)$file->getFileId();
		$userId = (int)$file->getCreateUser()->getId();

		if (empty($fileId) || empty($userId))
		{
			return;
		}

		$entity = CollabEntityFactory::getByInternalObject($file, EntityType::File->value);
		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();

		self::sendAddEvent($entity);
		LastActivityTrigger::execute($userId, $collabId);

		$logEntry = new AddFileLogEntry(userId: $userId, collabId: $collabId);
		$logEntry->setEntity($entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onAfterDeleteFile(Event $event): void
	{
		$params = $event->getParameters();

		$fileId = (int)($params[0] ?? null);
		$deletedBy = (int)($params[1] ?? null);

		if ($fileId <= 0 || $deletedBy <= 0)
		{
			return;
		}

		$fileLog = CollabLogTable::query()
			->setSelect(['ID', 'COLLAB_ID'])
			->where('ENTITY_ID', $fileId)
			->where('ENTITY_TYPE', EntityType::File->value)
			->fetchObject()
		;

		if (!$fileLog)
		{
			return;
		}

		$logEntry = new DeleteFileLogEntry(userId: $deletedBy, collabId: $fileLog->getCollabId());
		$logEntry->setFileId($fileId);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onAfterAddExternalLinkToObject(Event $event): void
	{
		[$file, $data] = $event->getParameters();

		$userId = (int)($data['CREATED_BY'] ?? null);

		$type = (int)$file?->getType();
		$isFile = $type > 0 && $type === (new ObjectType())->getFileType();

		if ($userId <= 0 || !$isFile)
		{
			return;
		}

		$entity = CollabEntityFactory::getByInternalObject($file, EntityType::File->value);
		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();

		$logEntry = new AddFilePublicLinkLogEntry(userId: $userId, collabId: $collabId);
		$logEntry->setEntity($entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onAfterMarkDeletedObject(Event $event): void
	{
		[$file, $deletedBy, $deletedType] = $event->getParameters();
		$deletedBy = (int)$deletedBy;
		$type = (int)$file?->getType();

		$isFile = $type > 0 && $type === (new ObjectType())->getFileType();

		if ($deletedBy <= 0 || !$isFile)
		{
			return;
		}

		$entity = CollabEntityFactory::getByInternalObject($file, EntityType::File->value);
		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();

		$logEntry = new MoveFileToRecyclebinLogEntry(userId: $deletedBy, collabId: $collabId);
		$logEntry->setEntity($entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onAfterCalendarEventDelete(int $eventId, array $entryFields, int $userId): void
	{
		$entity = CollabEntityFactory::getById($eventId, EntityType::CalendarEvent->value);

		if ($entity === null)
		{
			return;
		}

		$collabId = $entity->getCollab()->getId();
		$logEntry = new DeleteCalendarEventLogEntry(userId: $userId, collabId: $collabId, collabEntity: $entity);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onCollabUpdate(CollabUpdateEvent $event): void
	{
		$afterMembers = $event->getCollabAfter()->getUserMemberIds();
		$beforeMembers = $event->getCollabBefore()->getUserMemberIds();
		$excludedMembers = array_values(array_diff($beforeMembers, $afterMembers));

		if (!empty($excludedMembers))
		{
			LastActivityTrigger::remove($event->getCollabAfter()->getId(), $excludedMembers);
		}
	}

	public static function onCopyCollabInviteLink(Event $event): void
	{
		$userId = (int)$event->getParameter('userId');
		$collabId = (int)$event->getParameter('collabId');

		if (!CollabAccessController::can($userId, CollabDictionary::COPY_LINK, $collabId))
		{
			return;
		}

		ActionMessageFactory::getInstance()
			->getActionMessage(ActionType::CopyLink, $collabId, $userId)
			->runAction()
		;

		$logEntry = new CopyInvitationLinkLogEntry(userId: $userId, collabId: $collabId);
		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->save($logEntry);
	}

	public static function onRegenerateCollabInviteLink(Event $event): void
	{
		$userId = (int)$event->getParameter('userId');
		$collabId = (int)$event->getParameter('collabId');

		if ($userId <= 0 || $collabId <= 0)
		{
			return;
		}

		ActionMessageFactory::getInstance()
			->getActionMessage(ActionType::RegenerateLink, $collabId, $userId)
			->runAction()
		;
	}

	private static function sendAddEvent(CollabEntity $entity): void
	{
		$event = new CollabEntityAddEvent($entity);

		$event->send();
	}

	private static function sendUpdateEvent(array $changes, ?CollabEntity $entity = null): void
	{
		$event = new CollabEntityUpdateEvent($entity, $changes);

		$event->send();
	}

	private static function sendDeleteEvent(CollabEntity $entity): void
	{
		$event = new CollabEntityDeleteEvent($entity);

		$event->send();
	}
}
