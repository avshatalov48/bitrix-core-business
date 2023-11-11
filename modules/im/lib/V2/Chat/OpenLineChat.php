<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Recent;
use Bitrix\Im\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\PushFormat;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;
use Bitrix\ImOpenLines\Config;
use Bitrix\ImOpenLines\Model\ChatIndexTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class OpenLineChat extends EntityChat
{
	protected const EXTRANET_CAN_SEE_HISTORY = true;

	protected $entityMap = [
		'entityId' => [
			'connectorId',
			'lineId',
			'connectorChatId',
			'connectorUserId',
		],
		'entityData1' => [
			'crmEnabled',
			'crmEntityType',
			'crmEntityId',
			'pause',
			'waitAction',
			'sessionId',
			'dateCreate',
			'lineId',
			'blockDate',
			'blockReason',
		],
		'entityData2' => [
			'u0',
			'leadId',
			'u2',
			'companyId',
			'u4',
			'contactId',
			'u6',
			'dealId',
		],
		'entityData3' => [
			'silentMode',
		],
	];

	public function setEntityMap(array $entityMap): EntityChat
	{
		return $this;
	}

	public function read(bool $onlyRecent = false, bool $byEvent = false, bool $forceRead = false): Result
	{
		Recent::unread($this->getDialogId(), false, $this->getContext()->getUserId());

		if ($onlyRecent)
		{
			$lastId = $this->getReadService()->getLastMessageIdInChat($this->chatId);

			return (new Result())->setResult([
				'CHAT_ID' => $this->chatId,
				'LAST_ID' => $lastId,
				'COUNTER' => $this->getReadService()->getCounterService()->getByChat($this->chatId),
				'VIEWED_MESSAGES' => [],
			]);
		}

		return $this->readAllMessages($byEvent, $forceRead);
	}

	public function readAllMessages(bool $byEvent = false, bool $forceRead = false): Result
	{
		$result = $this->readMessages(null, $byEvent, $forceRead);

		$userId = $this->getContext()->getUserId();
		Application::getInstance()->addBackgroundJob(function () use ($byEvent, $forceRead, $userId) {
			$chat = $this->withContextUser($userId);

			if ($chat->getSelfRelation() === null)
			{
				$chat->readMessages(null, $byEvent, $forceRead);
			}
		});

		return $result;
	}

	public function readMessages(?MessageCollection $messages, bool $byEvent = false, bool $forceRead = false): Result
	{
		if (!$forceRead && $this->getAuthorId() === 0)
		{
			return new Result();
		}

		return parent::readMessages($messages, $byEvent);
	}

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN_LINE;
	}

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_LINE;
	}

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		$inChat = parent::checkAccessWithoutCaching($userId);

		if ($inChat)
		{
			return true;
		}

		if (!Loader::includeModule('imopenlines'))
		{
			return false;
		}

		$entityData = $this->getEntityData(true);
		return Config::canJoin(
			$entityData['lineId'] ?? 0,
			$entityData['crmEntityType'] ?? null,
			$entityData['crmEntityId'] ?? null
		);
	}

	protected function prepareParams(array $params = []): Result
	{
		$params['AUTHOR_ID'] = 0;
		return parent::prepareParams($params);
	}

	public function extendPullWatch(): void
	{
		if (Loader::includeModule('pull'))
		{
			\CPullWatch::Add($this->getContext()->getUserId(), "IM_PUBLIC_{$this->getId()}", true);
		}
	}

	/**
	 * @param Message $message
	 * @return void
	 */
	public function riseInRecent(Message $message): void
	{
		/** @var Relation $relation */
		foreach ($this->getRelations() as $relation)
		{
			if (!User::getInstance($relation->getUserId())->isActive())
			{
				continue;
			}

			$sessionId = 0;
			if ($this->getEntityType() == self::ENTITY_TYPE_LINE)
			{
				if (User::getInstance($relation->getUserId())->getExternalAuthId() == 'imconnector')
				{
					continue;
				}

				if ($this->getEntityData1())
				{
					//todo: replace it with method
					$fieldData = explode("|", $this->getEntityData1());
					$sessionId = (int)$fieldData[5];
				}
			}

			\CIMContactList::SetRecent([
				'ENTITY_ID' => $this->getChatId(),
				'MESSAGE_ID' => $message->getMessageId(),
				'CHAT_TYPE' => $this->getType(),
				'USER_ID' => $relation->getUserId(),
				'CHAT_ID' => $relation->getChatId(),
				'RELATION_ID' => $relation->getId(),
				'SESSION_ID' => $sessionId,
			]);

			if ($relation->getUserId() == $message->getAuthorId())
			{
				$relation
					->setLastId($message->getMessageId())
					->save();
			}
		}
	}

	protected function filterUsersToAdd(array $userIds): array
	{
		$filteredUsers = parent::filterUsersToAdd($userIds);

		foreach ($filteredUsers as $key => $userId)
		{
			$user = \Bitrix\Im\V2\Entity\User\User::getInstance($userId);
			if (!$user->isConnector() && ($user->isExtranet() || $user->isNetwork()))
			{
				unset($filteredUsers[$key]);
			}
		}

		return $filteredUsers;
	}

	public function setExtranet(?bool $extranet): \Bitrix\Im\V2\Chat
	{
		return $this;
	}

	public function getExtranet(): ?bool
	{
		return false;
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): self
	{
		parent::updateStateAfterUsersAdd($usersToAdd);

		if (Loader::includeModule('pull'))
		{
			foreach ($usersToAdd as $userId)
			{
				\CPullWatch::Delete($userId, 'IM_PUBLIC_' . $this->getId());
			}
		}

		return $this;
	}

	protected function sendPushUsersAdd(array $usersToAdd, RelationCollection $oldRelations): array
	{
		$pushMessage = parent::sendPushUsersAdd($usersToAdd, $oldRelations);

		if (Loader::includeModule('pull'))
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->getId(), $pushMessage);
		}

		return $pushMessage;
	}

	protected function addUsersToRelation(array $usersToAdd, array $managerIds = [], ?bool $hideHistory = null)
	{
		parent::addUsersToRelation($usersToAdd, $managerIds, false);
	}

	public function startRecordVoice(): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		parent::startRecordVoice();
		$pushFormatter = new PushFormat();
		\CPullWatch::AddToStack('IM_PUBLIC_'.$this->getId(), $pushFormatter->formatStartRecordVoice($this));
	}

	protected function addIndex(): \Bitrix\Im\V2\Chat
	{
		if (!Loader::includeModule('imopenlines'))
		{
			return $this;
		}

		ChatIndexTable::addIndex($this->getId(), $this->getTitle());

		return $this;
	}

	protected function updateIndex(): \Bitrix\Im\V2\Chat
	{
		if (!Loader::includeModule('imopenlines'))
		{
			return $this;
		}

		ChatIndexTable::updateIndex($this->getId(), $this->getTitle());

		return $this;
	}
}