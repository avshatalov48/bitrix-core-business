<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ExternalChat\Config;
use Bitrix\Im\V2\Chat\ExternalChat\Event\AfterCreateEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\BeforeCreateEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\BeforeUsersAddEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\AfterDeleteMessagesEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\FilterUsersByAccessEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\GetUsersForRecentEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\AfterReadAllMessagesEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\AfterReadMessagesEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\AfterSendMessageEvent;
use Bitrix\Im\V2\Chat\ExternalChat\Event\AfterUpdateMessageEvent;
use Bitrix\Im\V2\Chat\ExternalChat\ExternalTypeRegistry;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Counter\CounterType;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\ExternalChatRelations;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Message\Delete\DeletionMode;
use Bitrix\Im\V2\Chat\Add\AddResult;

class ExternalChat extends GroupChat
{
	protected Config $config;

	public function add(array $params, ?Context $context = null): AddResult
	{
		$beforeCreateEvent = new BeforeCreateEvent($params);
		$beforeCreateEvent->send();
		$eventResult = $beforeCreateEvent->getResult();

		if (!$eventResult->isSuccess())
		{
			return (new AddResult())->addErrors($eventResult->getErrors());
		}

		$params = $eventResult->getResult()['fields'] ?? $params;

		$addResult = parent::add($params, $context);

		(new AfterCreateEvent($params['ENTITY_TYPE'] ?? '', $addResult))->send();

		return $addResult;
	}

	protected function prepareParams(array $params = []): Result
	{
		if (empty($params['ENTITY_TYPE']))
		{
			return (new Result())->addError(new ChatError(ChatError::ENTITY_TYPE_EMPTY));
		}

		return parent::prepareParams($params);
	}

	protected function checkAccessInternal(int $userId): Result
	{
		$event = new FilterUsersByAccessEvent($this, [$userId]);
		$event->send();
		if (!$event->hasResult())
		{
			return parent::checkAccessInternal($userId);
		}

		$result = new Result();
		$usersWithAccess = $event->getUsersWithAccess();

		if (!in_array($userId, $usersWithAccess, true))
		{
			return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		return $result;
	}

	protected function getUsersToAddToRecent(): array
	{
		$event = new GetUsersForRecentEvent($this);
		$event->send();
		if (!$event->hasResult())
		{
			return parent::getUsersToAddToRecent();
		}

		return $event->getUsersForRecent();
	}

	public function getRelationFacade(): ?ExternalChatRelations
	{
		if ($this->getId())
		{
			$this->chatRelations ??= ExternalChatRelations::getInstance($this->getId());
		}

		return $this->chatRelations;
	}

	public function getCounterType(): CounterType
	{
		// TODO: delete this asap
		if ($this->getEntityType() === 'TASKS_TASK')
		{
			return CounterType::TasksTask;
		}

		return parent::getCounterType();
	}

	public function isAutoJoinEnabled(): bool
	{
		return $this->getConfig()->isAutoJoinEnabled;
	}

	public function addUsers(array $userIds, AddUsersConfig $config = new AddUsersConfig()): Chat
	{
		$event = new BeforeUsersAddEvent($this, $userIds, $config);
		$event->send();
		if (!$event->getResult()->isSuccess())
		{
			return $this;
		}

		$userIds = $event->getNewUserIds() ?? $userIds;
		$config = $event->getNewAddUsersConfig() ?? $config;

		return parent::addUsers($userIds, $config);
	}

	public function getConfig(): Config
	{
		$this->config ??=
			ExternalTypeRegistry::getInstance()->getConfigByType($this->getExtendedType(false))
			?? new Config()
		;

		return $this->config;
	}

	protected function needToSendMessageUserDelete(): bool
	{
		return false;
	}

	protected function onAfterMessageSend(Message $message, SendingService $sendingService): void
	{
		(new AfterSendMessageEvent($this, $message))->send();

		parent::onAfterMessageSend($message, $sendingService);
	}

	public function onAfterMessageUpdate(Message $message): Result
	{
		(new AfterUpdateMessageEvent($this, $message))->send();

		return parent::onAfterMessageUpdate($message);
	}

	public function onAfterMessagesDelete(MessageCollection $messages, DeletionMode $deletionMode): Result
	{
		(new AfterDeleteMessagesEvent($this, $messages, $deletionMode))->send();

		return parent::onAfterMessagesDelete($messages, $deletionMode);
	}

	public function onAfterMessagesRead(MessageCollection $messages, int $readerId): Result
	{
		(new AfterReadMessagesEvent($this, $messages, $readerId))->send();

		return parent::onAfterMessagesRead($messages, $readerId);
	}

	public function onAfterAllMessagesRead(int $readerId): Result
	{
		(new AfterReadAllMessagesEvent($this, $readerId))->send();

		return parent::onAfterAllMessagesRead($readerId);
	}
}
