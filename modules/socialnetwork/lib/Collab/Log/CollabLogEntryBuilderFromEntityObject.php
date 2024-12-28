<?php

namespace Bitrix\Socialnetwork\Collab\Log;

use Bitrix\Main\EventManager;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntityFactory;
use Bitrix\Socialnetwork\Collab\Internals\EO_CollabLog;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddCalendarEventLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddFileLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddFilePublicLinkLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddTaskLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddUserToCollabLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\ChangeCollabMemberRoleLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\CompleteTaskLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\CopyInvitationLinkLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\CreateCollabLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\DeleteCalendarEventLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\DeleteFileLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\DeleteTaskLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\RemoveUserFromCollabLogEntry;
use Bitrix\Socialnetwork\Collab\Log\Entry\UpdateCollabLogEntry;

class CollabLogEntryBuilderFromEntityObject
{
	private ?array $logEntryClasses = null;

	public function build(EO_CollabLog $entityObject): ?AbstractCollabLogEntry
	{
		$logEntryClasses = $this->getLogEntryClasses();

		$logEntryClass = $logEntryClasses[$entityObject->getType()] ?? null;

		if (!is_string($logEntryClass) || !is_subclass_of($logEntryClass, AbstractCollabLogEntry::class))
		{
			return null;
		}

		$logEntry = new $logEntryClass(
			id: $entityObject->getId(),
			userId: $entityObject->getUserId(),
			collabId: $entityObject->getCollabId(),
			dateTime: $entityObject->getDateTime(),
		);

		if ($entityObject->getEntityId() > 0)
		{
			$logEntry->setEntity(CollabEntityFactory::getById($entityObject->getEntityId(), $entityObject->getEntityType()));
		}

		$logEntry->setData($entityObject->getData());

		return $logEntry;
	}

	private function getLogEntryClasses(): array
	{
		if (is_array($this->logEntryClasses))
		{
			return $this->logEntryClasses;
		}

		$classes = $this->getSocialNetworkLogEntryClasses();

		$events = EventManager::getInstance()->findEventHandlers('socialnetwork', 'onCollabLogEntryClassesFetch');
		foreach ($events as $event)
		{
			$fetchedClasses = ExecuteModuleEventEx($event);

			if (is_array($fetchedClasses))
			{
				$classes = array_merge($classes, $fetchedClasses);
			}
		}

		$this->logEntryClasses = $classes;

		return $this->logEntryClasses;
	}

	private function getSocialNetworkLogEntryClasses(): array
	{
		return [
			AddCalendarEventLogEntry::getEventType() => AddCalendarEventLogEntry::class,
			AddFileLogEntry::getEventType() => AddFileLogEntry::class,
			AddFilePublicLinkLogEntry::getEventType() => AddFilePublicLinkLogEntry::class,
			AddTaskLogEntry::getEventType() => AddTaskLogEntry::class,
			AddUserToCollabLogEntry::getEventType() => AddUserToCollabLogEntry::class,
			ChangeCollabMemberRoleLogEntry::getEventType() => ChangeCollabMemberRoleLogEntry::class,
			CompleteTaskLogEntry::getEventType() => CompleteTaskLogEntry::class,
			CopyInvitationLinkLogEntry::getEventType() => CopyInvitationLinkLogEntry::class,
			CreateCollabLogEntry::getEventType() => CreateCollabLogEntry::class,
			DeleteCalendarEventLogEntry::getEventType() => DeleteCalendarEventLogEntry::class,
			DeleteFileLogEntry::getEventType() => DeleteFileLogEntry::class,
			DeleteTaskLogEntry::getEventType() => DeleteTaskLogEntry::class,
			RemoveUserFromCollabLogEntry::getEventType() => RemoveUserFromCollabLogEntry::class,
			UpdateCollabLogEntry::getEventType() => UpdateCollabLogEntry::class,
		];
	}
}
