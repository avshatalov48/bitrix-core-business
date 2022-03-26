<?php
namespace Bitrix\Im;

use Bitrix\Im\Model\ChatTable;

class Dialog
{
	static $accessCache = [];

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
		else if (preg_match('/^crm[0-9]{1,}$/i', $dialogId))
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

		if (isset(self::$accessCache[$dialogId][$userId]))
		{
			return self::$accessCache[$dialogId][$userId];
		}

		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($dialogId, $userId);

			$sql =
				'SELECT C.ID CHAT_ID, R.ID RID,
 					C.TYPE CHAT_TYPE, C.ENTITY_TYPE CHAT_ENTITY_TYPE, C.ENTITY_ID CHAT_ENTITY_ID, 
 					C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1, C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2, C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3
				FROM b_im_chat C
				LEFT JOIN b_im_relation R ON R.CHAT_ID = C.ID AND R.USER_ID = '.$userId.'
				WHERE C.ID = '.$chatId;
			$chatData = \Bitrix\Main\Application::getInstance()->getConnection()->query($sql)->fetch();
			if (!$chatData)
			{
				self::$accessCache[$dialogId][$userId] = false;
				return false;
			}

			if ($chatData['RID'] > 0)
			{
				self::$accessCache[$dialogId][$userId] = true;
				return true;
			}
			else if (
				$chatData['CHAT_TYPE'] == Chat::TYPE_SYSTEM
				|| $chatData['CHAT_TYPE'] == Chat::TYPE_PRIVATE
			)
			{
				self::$accessCache[$dialogId][$userId] = false;
				return false;
			}
			else if ($chatData['CHAT_TYPE'] == Chat::TYPE_OPEN)
			{
				if (\Bitrix\Im\User::getInstance($userId)->isExtranet())
				{
					self::$accessCache[$dialogId][$userId] = false;
					return false;
				}
				else
				{
					self::$accessCache[$dialogId][$userId] = true;
					return true;
				}
			}
			else if (
				$chatData['CHAT_TYPE'] == Chat::TYPE_OPEN_LINE
				|| $chatData['CHAT_TYPE'] == Chat::TYPE_GROUP && $chatData['CHAT_ENTITY_TYPE'] == 'LINES'
			)
			{
				if (\Bitrix\Main\Loader::includeModule('imopenlines'))
				{
					$crmEntityType = null;
					$crmEntityId = null;

					if ($chatData['CHAT_ENTITY_DATA_1'] <> '')
					{
						$fieldData = explode("|", $chatData['CHAT_ENTITY_DATA_1']);
						if ($fieldData[0] == 'Y')
						{
							$crmEntityType = $fieldData[1];
							$crmEntityId = $fieldData[2];
						}
					}

					$result = \Bitrix\ImOpenLines\Config::canJoin($chatId, $crmEntityType, $crmEntityId);
					self::$accessCache[$dialogId][$userId] = $result;

					return $result;
				}
				else
				{
					self::$accessCache[$dialogId][$userId] = false;
					return false;
				}
			}
			else
			{
				self::$accessCache[$dialogId][$userId] = false;
				return false;
			}
		}
		else if (!preg_match('/^\d{1,}$/i', $dialogId))
		{
			self::$accessCache[$dialogId][$userId] = false;
			return false;
		}
		else if ($dialogId == $userId)
		{
			self::$accessCache[$dialogId][$userId] = true;
			return true;
		}
		else if (
			!\Bitrix\Im\User::getInstance($userId)->isExists()
			|| !\Bitrix\Im\User::getInstance($dialogId)->isExists()
		)
		{
			self::$accessCache[$dialogId][$userId] = false;
			return false;
		}
		else if (
			\Bitrix\Im\User::getInstance($userId)->isBot()
			&& \Bitrix\Im\User::getInstance($dialogId)->isExtranet()
		)
		{
			self::$accessCache[$dialogId][$userId] = true;
			return true;
		}
		else
		{
			if (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
			{
				if (
					!\Bitrix\Im\User::getInstance($userId)->isExtranet()
					&& \Bitrix\Im\User::getInstance($dialogId)->isNetwork()
				)
				{
					self::$accessCache[$dialogId][$userId] = true;
					return true;
				}
				else if (
					\Bitrix\Im\User::getInstance($userId)->isExtranet()
					|| \Bitrix\Im\User::getInstance($dialogId)->isExtranet()
				)
				{
					$inGroup = \Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($dialogId, $userId);
					if ($inGroup)
					{
						self::$accessCache[$dialogId][$userId] = true;
						return true;
					}

					global $USER;
					if (
						\Bitrix\Im\User::getInstance($userId)->isExtranet()
						&& \Bitrix\Im\User::getInstance($dialogId)->isBot()
						&& $userId == $USER->GetID()
					)
					{
						if ($USER->IsAdmin())
						{
							self::$accessCache[$dialogId][$userId] = true;
							return true;
						}
						else if (\CModule::IncludeModule('bitrix24'))
						{
							if (\CBitrix24::IsPortalAdmin($userId))
							{
								self::$accessCache[$dialogId][$userId] = true;
								return true;
							}
							else if (\Bitrix\Bitrix24\Integrator::isIntegrator($userId))
							{
								self::$accessCache[$dialogId][$userId] = true;
								return true;
							}
						}
					}

					self::$accessCache[$dialogId][$userId] = false;
					return false;
				}

				self::$accessCache[$dialogId][$userId] = true;
				return true;
			}
			else
			{
				if (
					\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_MESSAGE) == \CIMSettings::PRIVACY_RESULT_CONTACT
					&& \CModule::IncludeModule('socialnetwork')
					&& \CSocNetUser::IsFriendsAllowed()
					&& !\CSocNetUserRelations::IsFriends($dialogId, $userId))
				{
					self::$accessCache[$dialogId][$userId] = false;
					return false;
				}
				else if
				(
					\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_MESSAGE, $dialogId) == \CIMSettings::PRIVACY_RESULT_CONTACT
					&& \CModule::IncludeModule('socialnetwork')
					&& \CSocNetUser::IsFriendsAllowed()
					&& !\CSocNetUserRelations::IsFriends($dialogId, $userId)
				)
				{
					self::$accessCache[$dialogId][$userId] = false;
					return false;
				}
				else
				{
					self::$accessCache[$dialogId][$userId] = true;
					return true;
				}
			}
		}
	}

	public static function clearAccessCache($dialogId)
	{
		unset(self::$accessCache[$dialogId]);
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

		\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_relation R
				INNER JOIN b_im_chat C on C.ID = R.CHAT_ID
				SET R.LAST_ID = C.LAST_MESSAGE_ID,
				R.UNREAD_ID = 0,
				R.LAST_READ = NOW(),
				R.STATUS = " . IM_STATUS_READ . ",
				R.COUNTER = 0
				WHERE R.MESSAGE_TYPE NOT IN ('" . IM_MESSAGE_OPEN_LINE . "', '" . IM_MESSAGE_SYSTEM . "')
				AND R.COUNTER > 0
				AND R.USER_ID = " . $userId
		);

		\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_recent R
			SET R.UNREAD = 'N'
			WHERE R.UNREAD = 'Y'"
		);

		if (\CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($userId, [
				'module_id' => 'im',
				'command' => 'readAllChats',
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

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