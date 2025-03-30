<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Recent;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;
use Bitrix\ImOpenLines\Config;
use Bitrix\ImOpenLines\Model\ChatIndexTable;
use Bitrix\ImOpenLines;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Pull\Event;

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
	protected bool $isSessionFilled = false;
	protected ?RelationCollection $fakeRelation = null;
	protected ?ImOpenLines\V2\Session\Session $session = null;

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

	public function getRelations(): RelationCollection
	{
		if (isset($this->fakeRelation))
		{
			return $this->fakeRelation;
		}

		return parent::getRelations();
	}

	public function setFakeRelation(array $userIds): self
	{
		$this->fakeRelation = RelationCollection::createFake($userIds, $this);

		return $this;
	}

	public function unsetFakeRelation(): self
	{
		$this->fakeRelation = null;

		return $this;
	}

	protected function onBeforeMessageSend(Message $message, SendingConfig $config): Result
	{
		if ($config->fakeRelation())
		{
			$this->setFakeRelation([$config->fakeRelation()]);
		}
		elseif (Loader::includeModule('imopenlines'))
		{
			$userIds = (new ImOpenLines\Relation($this->getId()))->getRelationUserIds();
			if (!empty($userIds))
			{
				$this->setFakeRelation($userIds);
			}
		}

		return parent::onBeforeMessageSend($message, $config);
	}

	public function getLineData(): ?array
	{
		$session = $this->getSession();

		if (!isset($session))
		{
			return null;
		}

		return [
			'id' => $session->getId(),
			'status' => $session->getStatus(),
			'date_create' => $session->getDateCreate(),
			'statusGroup' => $session->getStatusGroup(),
			'queueId' => $session->getConfigId(),
			'pinned' => $session->getPinMode(),
			'isClosed' => $session->isClosed(),
			'operatorId' => $session->getOperatorId(),
		];
	}

	public function getSessionId(): int
	{
		if (!isset($this->session))
		{
			return $this->getSession()?->getSessionId() ?? 0;
		}

		return $this->session->getSessionId();
	}

	protected function getSessionIdByEntityData(): int
	{
		if ($this->getEntityData1())
		{
			return (int)(explode('|', $this->getEntityData1())[5] ?? 0);
		}

		return 0;
	}

	public function getSession(): ?ImOpenLines\V2\Session\Session
	{
		if (isset($this->session))
		{
			return $this->session;
		}

		if (!Loader::includeModule('imopenlines'))
		{
			return null;
		}

		if ($sessionId = $this->getSessionIdByEntityData())
		{
			$this->session = ImOpenLines\V2\Session\Session::getInstance($sessionId);
		}
		else
		{
			$this->session = ImOpenLines\V2\Session\Session::getInstanceByChatId($this->chatId);
		}

		return $this->session;
	}

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN_LINE;
	}

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_LINE;
	}

	protected function checkAccessInternal(int $userId): Result
	{
		$checkResult = parent::checkAccessInternal($userId);

		if ($checkResult->isSuccess())
		{
			return $checkResult;
		}

		$result = new Result();

		if (!Loader::includeModule('imopenlines'))
		{
			return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		$entityData = $this->getEntityData(true);
		$canJoin = Config::canJoin(
			$entityData['entityId']['lineId'] ?? 0,
			$entityData['crmEntityType'] ?? null,
			$entityData['crmEntityId'] ?? null
		);

		if (!$canJoin && !$this->checkAccessByRecentTable($userId))
		{
			$result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		return $result;
	}

	protected function checkAccessByRecentTable(int $userId): bool
	{
		$result = ImOpenLines\Model\RecentTable::query()
			->setSelect(['USER_ID'])
			->where('CHAT_ID', $this->getChatId())
			->where('USER_ID', $userId)
			->setLimit(1)
			->fetch()
		;

		return $result !== false;
	}

	public function canUpdateOwnMessage(): bool
	{
		if (!Loader::includeModule('imopenlines'))
		{
			return false;
		}

		[$connectorType] = explode("|", $this->getEntityId() ?? '');

		return in_array($connectorType, \Bitrix\ImOpenlines\Connector::getListCanUpdateOwnMessage(), true);
	}

	public function canDeleteOwnMessage(): bool
	{
		if (!Loader::includeModule('imopenlines'))
		{
			return false;
		}

		[$connectorType] = explode("|", $this->getEntityId() ?? '');

		return in_array($connectorType, \Bitrix\ImOpenlines\Connector::getListCanDeleteOwnMessage(), true);
	}

	public function canDeleteMessage(): bool
	{
		if (!Loader::includeModule('imopenlines'))
		{
			return false;
		}

		[$connectorType] = explode("|", $this->getEntityId() ?? '');

		return in_array($connectorType, \Bitrix\ImOpenlines\Connector::getListCanDeleteMessage(), true);
	}

	protected function needToSendMessageUserDelete(): bool
	{
		return true;
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

	public function needToSendPublicPull(): bool
	{
		return true;
	}

	protected function updateRecentAfterMessageSend(Message $message, SendingConfig $config): Result
	{
		if (
			$this->getSessionId()
			&& Loader::includeModule('imopenlines')
			&& ImOpenLines\Recent::isRecentAvailableByStatus($this->getSession()?->getStatus())
		)
		{
			ImOpenLines\Recent::update($message);

			return new Result();
		}

		return parent::updateRecentAfterMessageSend($message, $config);
	}

	protected function updateRelationsAfterMessageSend(Message $message): Result
	{
		if ($this->hasFakeRelations())
		{
			return new Result();
		}

		return parent::updateRelationsAfterMessageSend($message);
	}

	protected function updateCountersAfterMessageSend(Message $message, SendingConfig $sendingConfig): Result
	{
		if ($this->hasFakeRelations())
		{
			$counter = 1;
			if ($this->getSession()?->isClosed())
			{
				$counter = 0;
			}

			$counters = [];
			foreach ($this->fakeRelation as $fakeRelation)
			{
				$counters[$fakeRelation->getUserId()] = $counter;
			}

			return (new Result())->setResult(['COUNTERS' => $counters]);
		}

		return parent::updateCountersAfterMessageSend($message, $sendingConfig);
	}

	protected function getFieldsForRecent(int $userId, Message $message): array
	{
		$fields = parent::getFieldsForRecent($userId, $message);
		if (!empty($fields))
		{
			$fields['ITEM_OLID'] = $this->getSessionIdByEntityData();
		}

		return $fields;
	}

	protected function getUpdatedFieldsForRecent(Message $message): array
	{
		$fields = parent::getUpdatedFieldsForRecent($message);
		if (!empty($fields))
		{
			$fields['ITEM_OLID'] = $this->getSessionIdByEntityData();
		}

		return $fields;
	}

	protected function isValidToAdd(int $userId): bool
	{
		if(!parent::isValidToAdd($userId))
		{
			return false;
		}

		$user = User::getInstance($userId);

		return $user->isConnector() || (!$user->isExtranet() && !$user->isConnector());
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

	protected function addUsersToRelation(array $usersToAdd, AddUsersConfig $config): void
	{
		$config->setHideHistory(false);
		parent::addUsersToRelation($usersToAdd, $config);
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

	protected function hasFakeRelations(): bool
	{
		return isset($this->fakeRelation);
	}

	public function getPopupData(array $excludedList = []): \Bitrix\Im\V2\Rest\PopupData
	{
		$popupData = parent::getPopupData($excludedList);
		$session = $this->getSession();

		if (isset($session))
		{
			$popupData->add($session);
		}

		return $popupData;
	}

	protected function prepareMessageParamsFromUserDelete(string $message, bool $skipRecent): array
	{
		return [
			'TO_CHAT_ID' => $this->getId(),
			'MESSAGE' => $message,
			'FROM_USER_ID' => $this->getContext()->getUserId(),
			'SYSTEM' => 'Y',
			'RECENT_ADD' => $skipRecent ? 'N' : 'Y',
			'PARAMS' => ['CODE' => 'CHAT_LEAVE', 'NOTIFY' => 'Y'],
			"PUSH" => 'N',
			'SKIP_USER_CHECK' => 'Y',
		];
	}

	protected function sendNotificationUserDelete(int $userId, DeleteUserConfig $config): void
	{
		return;
	}

	protected function sendPushOnChangeUsers(RelationCollection $relations, array $pushMessage): void
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return;
		}

		$userIds = $relations->getUserIds();

		foreach ($relations as $relation)
		{
			if ($relation->getUser()->getExternalAuthId() === 'imconnector')
			{
				unset($relations[$relation->getUserId()]);
			}
		}

		Event::add(array_values($userIds), $pushMessage);

		if ($this->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->getId(), $pushMessage);
		}
	}
}
