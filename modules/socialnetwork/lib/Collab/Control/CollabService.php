<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabAddCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabDeleteCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Event\BeforeCollabUpdateEvent;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabAddEvent;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabDeleteEvent;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabUpdateEvent;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddInviteHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddLogEntryHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddMemberHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddMessageHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddModeratorsHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddOptionsHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Add\AddThemeHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Delete\DeleteChatHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Delete\DeleteOptionsHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Update\UpdateInviteHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Update\UpdateLogEntryHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Update\UpdateMemberHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Update\UpdateModeratorsHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Update\UpdateOptionsHandler;
use Bitrix\Socialnetwork\Collab\Control\Handler\Update\UpdateOwnerHandler;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageBuffer;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Control\AbstractGroupService;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Control\Handler\Add\AddFeatureHandler;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdatePermissionsHandler;
use Bitrix\Socialnetwork\Item\Workgroup;

class CollabService extends AbstractGroupService
{
	protected function getAddHandlers(): array
	{
		return [
			new AddFeatureHandler(),
			new AddThemeHandler(),
			new AddMessageHandler(),
			new AddInviteHandler(),
			new AddMemberHandler(),
			new AddModeratorsHandler(),
			new AddOptionsHandler(),
			new AddLogEntryHandler(),
		];
	}

	protected function getUpdateHandlers(): array
	{
		return [
			new UpdatePermissionsHandler(),
			new UpdateOptionsHandler(),
			new UpdateOwnerHandler(),
			new UpdateInviteHandler(),
			new UpdateMemberHandler(),
			new UpdateModeratorsHandler(),
			new UpdateLogEntryHandler(),
		];
	}

	protected function getDeleteHandlers(): array
	{
		return [
			new DeleteOptionsHandler(),
			new DeleteChatHandler(),
		];
	}

	protected function init(): void
	{
		parent::init();
		$this->registry = CollabRegistry::getInstance();
	}

	protected function checkAddCommand(AbstractCommand $command): GroupResult
	{
		$result = new GroupResult();
		if (!$command instanceof CollabAddCommand)
		{
			$result->addError(new Error('Wrong command type'));
		}

		return $result;
	}

	protected function checkUpdateCommand(UpdateCommand $command): GroupResult
	{
		$result = new GroupResult();
		if (!$command instanceof CollabUpdateCommand)
		{
			$result->addError(new Error('Wrong command type'));
		}

		return $result;
	}

	protected function checkDeleteCommand(DeleteCommand $command): GroupResult
	{
		$result = new GroupResult();
		if (!$command instanceof CollabDeleteCommand)
		{
			$result->addError(new Error('Wrong command type'));
		}

		return $result;
	}

	protected function finalizeAddResult(GroupResult $result): CollabResult
	{
		return $this->setCollabToResult($result);
	}

	protected function finalizeUpdateResult(GroupResult $result): CollabResult
	{
		return $this->setCollabToResult($result);
	}

	protected function sendAddEvent(AddCommand $command, Workgroup $entity): void
	{
		if (!$command instanceof CollabAddCommand || !$entity instanceof Collab)
		{
			return;
		}

		ActionMessageBuffer::getInstance()->flush();

		$event = new CollabAddEvent($command, $entity);

		$event->send();
	}

	protected function sendBeforeUpdateEvent(UpdateCommand $command, Workgroup $entity): void
	{
		if (!$command instanceof CollabUpdateCommand || !$entity instanceof Collab)
		{
			return;
		}

		$event = new BeforeCollabUpdateEvent($command, $entity);

		$event->send();
	}

	protected function sendUpdateEvent(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): void
	{
		if (!$command instanceof CollabUpdateCommand || !$entityBefore instanceof Collab || !$entityAfter instanceof Collab)
		{
			return;
		}

		ActionMessageBuffer::getInstance()->flush();

		$event = new CollabUpdateEvent($command, $entityBefore, $entityAfter);

		$event->send();
	}

	protected function sendDeleteEvent(DeleteCommand $command, Workgroup $entityBefore): void
	{
		if (!$command instanceof CollabDeleteCommand || !$entityBefore instanceof Collab)
		{
			return;
		}

		$event = new CollabDeleteEvent($command, $entityBefore);

		$event->send();
	}

	private function setCollabToResult(GroupResult $result): CollabResult
	{
		$collabResult = (new CollabResult())->merge($result);
		if (!$collabResult->isSuccess())
		{
			return $collabResult;
		}

		$provider = CollabProvider::getInstance();

		$collab = $provider->getCollab((int)$collabResult->getCollab()?->getId());
		if ($collab === null)
		{
			$collabResult->addError(new Error('Collab not found'));

			return $collabResult;
		}

		$collabResult->setGroup($collab);

		return $collabResult;
	}
}