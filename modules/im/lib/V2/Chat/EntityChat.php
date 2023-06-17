<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Localization\Loc;

class EntityChat extends GroupChat
{
	protected const ENTITY_SEPARATOR = '|';

	protected const ENTITY_MAP_FIELDS = ['entityId', 'entityData1', 'entityData2', 'entityData3'];

	protected $entityMap = [
		'entityId' => [],
		'entityData1' => [],
		'entityData2' => [],
		'entityData3' => [],
	];

	protected $entityData = [];

	public function setEntityMap(array $entityMap): self
	{
		foreach ($entityMap as $field => $map)
		{
			if (in_array($field, self::ENTITY_MAP_FIELDS, true) && is_array($map))
			{
				$this->entityMap[$field] = array_values($map);
			}
		}

		return $this;
	}

	public function getEntityMap(): array
	{
		return $this->entityMap;
	}

	/**
	 * @param bool $force
	 * @return array
	 */
	public function getEntityData(bool $force = false): array
	{
		if (!count($this->entityData) || $force)
		{
			$this->entityData = $this->unmapEntity();
		}

		return $this->entityData;
	}

	private function unmapEntity(): array
	{
		$result = [];
		foreach ($this->getEntityMap() as $entityType => $entityFields)
		{
			if (!count($entityFields))
			{
				continue;
			}

			if ($this->$entityType)
			{
				$data = explode(self::ENTITY_SEPARATOR, $this->$entityType);
				if (count($entityFields) === count($data))
				{
					$result[$entityType] = array_combine($entityFields, $data);
				}
				else
				{
					$result[$entityType] = $data;
				}
			}
			else
			{
				$result[$entityType] = array_fill_keys($entityFields, null);
			}
		}
		return $result;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$paramsResult = $this->prepareParams($params);
		if ($paramsResult->isSuccess())
		{
			$params = $paramsResult->getResult();
		}
		else
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		$chat = new EntityChat($params);
		$chat->setExtranet($chat->checkIsExtranet());
		$chat->save();

		if (!$chat->getChatId())
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}
		else
		{
			foreach ($chat->getUserIds() as $userId)
			{
				if ($chat->getAuthorId() == $userId)
				{
					$isManager = 'Y';
				}
				else
				{
					$isManager = in_array($userId, $params['MANAGERS']) ? 'Y' : 'N';
				}

				RelationTable::add([
					'CHAT_ID' => $chat->getChatId(),
					'MESSAGE_TYPE' => \IM_MESSAGE_CHAT,
					'USER_ID' => $userId,
					'STATUS' => \IM_STATUS_READ,
					'MANAGER' => $isManager,
				]);

				if (\Bitrix\Im\V2\Entity\User\User::getInstance($userId)->isBot())
				{
					\Bitrix\Im\Bot::changeChatMembers($chat->getChatId(), $userId);
					\Bitrix\Im\Bot::onJoinChat('chat' . $chat->getChatId(), [
						'CHAT_TYPE' => $chat->getType(),
						'MESSAGE_TYPE' => \IM_MESSAGE_CHAT,
						'BOT_ID' => $userId,
						'USER_ID' => $params['USER_ID'],
						'CHAT_AUTHOR_ID' => $chat->getAuthorId(),
						'CHAT_ENTITY_TYPE' => $chat->getEntityType(),
						'CHAT_ENTITY_ID' => $chat->getEntityId(),
						'ACCESS_HISTORY' => true,
					]);
				}
			}
		}

		$chat->updateIndex();

		$result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);

		return $result;
	}

	/**
	 * @param array $params
	 * <pre>
	 * [
	 * 	string ENTITY_TYPE
	 * 	string ENTITY_ID
	 * ]
	 * </pre>
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		$result = new Result;

		if (empty($params['ENTITY_TYPE']) || empty($params['ENTITY_ID']))
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARAMETER));
		}

		$row = ChatTable::query()
			->setSelect(['ID'])
			->where('ENTITY_TYPE', $params['ENTITY_TYPE'])
			->where('ENTITY_ID', $params['ENTITY_ID'])
			->fetch()
		;

		if ($row)
		{
			$result->setResult([
				'ID' => (int)$row['ID']
			]);
		}

		return $result;
	}
}
