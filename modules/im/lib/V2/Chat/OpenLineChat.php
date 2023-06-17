<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Recent;
use Bitrix\Im\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Result;
use Bitrix\ImOpenLines\Config;
use Bitrix\Main\Loader;

class OpenLineChat extends EntityChat
{
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
		return $this->readMessages(null, $byEvent, $forceRead);
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
			$this->getChatId(),
			$entityData['crmEntityType'] ?? null,
			$entityData['crmEntityId'] ?? null
		);
	}

	protected function prepareParams(array $params = []): Result
	{
		$params['AUTHOR_ID'] = 0;
		return parent::prepareParams($params);
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
}
