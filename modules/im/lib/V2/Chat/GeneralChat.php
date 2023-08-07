<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Chat;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class GeneralChat extends GroupChat
{
	public const GENERAL_MESSAGE_TYPE_JOIN = 'join';
	public const GENERAL_MESSAGE_TYPE_LEAVE = 'leave';

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN;
	}

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_GENERAL;
	}

	/**
	 * @param int|array|EO_Chat $source
	 */
	public function load($source = null): Result
	{
		$chatId = -1;

		if (is_numeric($source))
		{
			$chatId = (int)$source;
		}
		elseif ($source instanceof EO_Chat)
		{
			$chatId = $source->getId();
		}
		elseif (is_array($source))
		{
			$chatId = (int)$source['ID'];
		}

		if ($chatId <= 0)
		{
			$generalChatId = self::getGeneralChatId();
			if ($generalChatId)
			{
				$chatId = $generalChatId;
			}
			else
			{
				$chatId = $source;
			}
		}

		return parent::load($chatId);
	}

	public static function getGeneralChatId(): int
	{
		$generalChat = self::find();
		if (!$generalChat->hasResult())
		{
			return 0;
		}

		return $generalChat->getResult()['ID'];
	}

	/**
	 * @param array $params
	 * @param Context|null $context
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		$result = new Result;

		$row = ChatTable::query()
			->setSelect(['ID', 'TYPE', 'ENTITY_TYPE', 'ENTITY_ID'])
			->where('ENTITY_TYPE', self::ENTITY_TYPE_GENERAL)
			->setLimit(1)
			->setOrder(['ID' => 'DESC'])
			->fetch()
		;

		if ($row)
		{
			$result->setResult([
				'ID' => (int)$row['ID'],
				'TYPE' => $row['TYPE'],
				'ENTITY_TYPE' => $row['ENTITY_TYPE'],
				'ENTITY_ID' => $row['ENTITY_ID'],
			]);
		}

		return $result;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$generalChatResult = self::find();
		if ($generalChatResult->hasResult())
		{
			$generalChat = new GeneralChat(['ID' => $generalChatResult->getResult()['ID']]);
			return 	$result->setResult([
				'CHAT_ID' => $generalChat->getChatId(),
				'CHAT' => $generalChat,
			]);
		}

		$params = [
			'TYPE' => self::IM_TYPE_OPEN,
			'ENTITY_TYPE' => self::ENTITY_TYPE_GENERAL,
			'COLOR' => 'AZURE',
			'TITLE' => Loc::getMessage('IM_CHAT_GENERAL_TITLE'),
			'DESCRIPTION' => Loc::getMessage('IM_CHAT_GENERAL_DESCRIPTION'),
			'AUTHOR_ID' => 0
		];

		$chat = new GeneralChat($params);
		$chat->setExtranet(false);
		$chat->save();

		if (!$chat->getChatId())
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		$chat->sendBanner();

		$adminIds = [];
		if (Loader::includeModule('bitrix24'))
		{
			$adminIds = \CBitrix24::getAllAdminId();
		}

		foreach ($this->getUsersForInstall() as $user)
		{
			$relation = new Relation();
			$relation->setChatId($chat->getChatId());
			$relation->setUserId((int)$user['ID']);
			$relation->setManager(in_array((int)$user['ID'], $adminIds, true));
			$relation->setMessageType(self::IM_TYPE_OPEN);
			$relation->setStatus(IM_STATUS_READ);
			$relation->save();
		}

		$chat->updateIndex();

		self::linkGeneralChat($chat->getChatId());

		$result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);

		return $result;
	}

	public static function linkGeneralChat(?int $chatId = null): bool
	{
		if (!$chatId)
		{
			$chatId = self::getGeneralChatId();
		}

		if (!$chatId)
		{
			return false;
		}

		if (Loader::includeModule('pull'))
		{
			\CPullStack::AddShared([
				'module_id' => 'im',
				'command' => 'generalChatId',
				'params' => [
					'id' => $chatId
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	public static function unlinkGeneralChat(): bool
	{
		if (Loader::includeModule('pull'))
		{
			\CPullStack::AddShared([
				'module_id' => 'im',
				'command' => 'generalChatId',
				'params' => [
					'id' => 0
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	public function canJoinGeneralChat(int $userId): bool
	{
		if (
			$userId <= 0
			|| !self::getGeneralChatId()
			|| !Loader::includeModule('intranet')
		)
		{
			return false;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$sql = "
			SELECT DISTINCT U.ID
			FROM
				b_user U
				INNER JOIN b_user_field F ON F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT'
				INNER JOIN b_utm_user UF ON
					UF.FIELD_ID = F.ID
					AND UF.VALUE_ID = U.ID
					AND UF.VALUE_INT > 0
			WHERE
				U.ACTIVE = 'Y'
				AND U.ID = " . $userId . "
				AND F.ENTITY_ID = 'USER'
				AND F.FIELD_NAME = 'UF_DEPARTMENT'
			LIMIT 1
		";
		if ($connection->query($sql)->fetch())
		{
			return true;
		}

		return false;
	}

	private function getUsersForInstall(): array
	{
		if (Loader::includeModule('intranet'))
		{
			$sql = "
				SELECT DISTINCT U.ID
				FROM
					b_user U
					INNER JOIN b_user_field F ON F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT'
					INNER JOIN b_utm_user UF ON
						UF.FIELD_ID = F.ID
						AND UF.VALUE_ID = U.ID
						AND UF.VALUE_INT > 0
				WHERE
					U.ACTIVE = 'Y'
					AND U.EXTERNAL_AUTH_ID IS NULL
					AND F.ENTITY_ID = 'USER'
					AND F.FIELD_NAME = 'UF_DEPARTMENT'
			";
		}
		else
		{
			$sql = "
				SELECT ID
				FROM b_user U
				WHERE 
				    U.ACTIVE = 'Y'
					AND U.EXTERNAL_AUTH_ID IS NULL
			";
		}

		$connection = \Bitrix\Main\Application::getConnection();
		return $connection->query($sql)->fetchAll();
	}

	protected function sendBanner(?int $authorId = null): void
	{
		\CIMMessage::Add([
			'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => 0,
			'MESSAGE' => Loc::getMessage('IM_CHAT_GENERAL_DESCRIPTION'),
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'PARAMS' => [
				'COMPONENT_ID' => 'ChatCreationMessage',
			]
		]);
	}

	public static function getAutoMessageStatus(string $type): bool
	{
		switch ($type)
		{
			case self::GENERAL_MESSAGE_TYPE_JOIN:
				return (bool)\COption::GetOptionString("im", "general_chat_message_join");
			case self::GENERAL_MESSAGE_TYPE_LEAVE:
				return (bool)\COption::GetOptionString("im", "general_chat_message_leave");
			default:
				return false;
		}
	}

	public function getRightsForIntranetConfig(): array
	{
		$result['generalChatCanPostList'] = self::getCanPostList();
		$result['generalChatCanPost'] = $this->getCanPost();
		$result['generalChatShowManagersList'] = self::MANAGE_RIGHTS_MANAGERS;
		$managerIds = $this->getRelations([
			'FILTER' => [
				'MANAGER' => 'Y'
			]
		])->getUserIds();
		$managers = array_map(function ($managerId) {
			return 'U' . $managerId;
		}, $managerIds);
		$result['generalChatManagersList'] = \IntranetConfigsComponent::processOldAccessCodes($managers);

		return $result;
	}

	public static function deleteGeneralChat(): Result
	{
		$generalChat = ChatFactory::getInstance()->getGeneralChat();
		if (!$generalChat)
		{
			return (new Result())->addError(new ChatError(ChatError::NOT_FOUND));
		}

		return $generalChat->deleteChat();
	}
}
