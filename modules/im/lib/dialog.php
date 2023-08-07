<?php
namespace Bitrix\Im;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

class Dialog
{
	public static function getTitle($dialogId, $userId = null):? string
	{
		if (Common::isChatId($dialogId))
		{
			if (!Dialog::hasAccess($dialogId, $userId))
			{
				return null;
			}

			$chatId = Dialog::getChatId($dialogId);

			$chatData = ChatTable::getRow([
				'select' => ['TITLE'],
				'filter' => ['=ID' => $chatId],
			]);
			if (!$chatData)
			{
				return null;
			}

			return $chatData['TITLE'];
		}

		$userId = Common::getUserId($userId);
		$chatId = \CIMMessage::GetChatId($dialogId, $userId);
		if (!$chatId)
		{
			return null;
		}

		$userNames = [
			User::getInstance($dialogId)->getFullName(false),
			User::getInstance($userId)->getFullName(false),
		];

		return implode(" - ", $userNames);
	}

	public static function getDialogId(int $chatId, $userId = null): string
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$chat = \Bitrix\Im\Chat::getById($chatId);

		if (!$chat)
		{
			return false;
		}

		if ($chat['MESSAGE_TYPE'] !== Chat::TYPE_PRIVATE)
		{
			return "chat{$chat['ID']}";
		}

		$query = ChatTable::query()
			->setSelect(['DIALOG_ID' => 'RELATION.USER_ID'])
			->registerRuntimeField(
				'RELATION',
				(
					new OneToMany('RELATION', RelationTable::class, 'CHAT')
				)->configureJoinType('inner')
			)
			->where('ID', (int)$chatId)
			->where('TYPE', Chat::TYPE_PRIVATE)
			->whereNot('RELATION.USER_ID', $userId)
			->setLimit(1)
		;

		$queryResult = $query->fetch();
		if (!$queryResult)
		{
			return false;
		}

		return $queryResult['DIALOG_ID'];
	}

	public static function getChatId($dialogId, $userId = null)
	{
		if (preg_match('/^chat[0-9]{1,}$/i', $dialogId))
		{
			$chatId = (int)mb_substr($dialogId, 4);
		}
		else if (preg_match('/^\d{1,}$/i', $dialogId))
		{
			$dialogId = intval($dialogId);
			if (!$dialogId)
			{
				return false;
			}

			$userId = \Bitrix\Im\Common::getUserId($userId);
			if (!$userId)
			{
				return false;
			}

			$chatId = \CIMMessage::GetChatId($dialogId, $userId);
			if (!$chatId)
			{
				return false;
			}
		}
		else if (preg_match('/^crm\|\w+?\|\d+?$/i', $dialogId))
		{
			$chatId = \CIMChat::GetCrmChatId(mb_substr($dialogId, 4));
		}
		else if (preg_match('/^sg[0-9]{1,}$/i', $dialogId))
		{
			$chatId = \CIMChat::GetSonetGroupChatId(mb_substr($dialogId, 2));
		}
		else
		{
			$chatId = 0;
		}

		return $chatId;
	}

	public static function getLink($dialogId, $userId = null):? string
	{
		if (!Dialog::hasAccess($dialogId, $userId))
		{
			return null;
		}

		return '/online/?IM_DIALOG='.$dialogId;
	}

	public static function hasAccess($dialogId, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$chatId = self::getChatId($dialogId, $userId);

			return \Bitrix\Im\V2\Chat::getInstance($chatId)->hasAccess($userId);
		}

		return \Bitrix\Im\V2\Entity\User\User::getInstance($dialogId)->hasAccess($userId);
	}

	public static function read($dialogId, $messageId = null, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$chatId = self::getChatId($dialogId);

			$chat = new \CIMChat($userId);
			$result = $chat->SetReadMessage($chatId, $messageId);
		}
		else if ($dialogId === 'notify')
		{
			$notify = new \CIMNotify();
			$notify->MarkNotifyRead(0, true);

			return true;
		}
		else
		{
			$CIMMessage = new \CIMMessage($userId);
			$result = $CIMMessage->SetReadMessage($dialogId, $messageId);
		}

		return $result;
	}

	public static function readAll($userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		\Bitrix\Im\V2\Chat::readAllChats($userId);

		return true;
	}

	public static function unread($dialogId, $messageId = null, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$chatId = self::getChatId($dialogId);

			$chat = new \CIMChat($userId);
			$chat->SetUnReadMessage($chatId, $messageId);
		}
		else
		{
			$CIMMessage = new \CIMMessage($userId);
			$CIMMessage->SetUnReadMessage($dialogId, $messageId);
		}

		return false;
	}

	public static function getRelation($userId1, $userId2, $params = array())
	{
		$userId1 = intval($userId1);
		$userId2 = intval($userId2);

		if ($userId1 <= 0 || $userId2 <= 0)
		{
			return false;
		}

		$chatId = \CIMMessage::GetChatId($userId1, $userId2);
		if (!$chatId)
		{
			return false;
		}

		return Chat::getRelation($chatId, $params);
	}



}