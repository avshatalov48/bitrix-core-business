<?php
namespace Bitrix\Im;

class Dialog
{
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

	public static function getChatId($dialogId, $userId = null)
	{
		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$chatId = substr($dialogId, 4);
		}
		else
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

		return $chatId;
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
			$chatId = intval(substr($dialogId, 4));

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
				return false;
			}

			if ($chatData['RID'] > 0)
			{
				return true;
			}
			else if (
				$chatData['CHAT_TYPE'] == Chat::TYPE_SYSTEM
				|| $chatData['CHAT_TYPE'] == Chat::TYPE_PRIVATE
			)
			{
				return false;
			}
			else if ($chatData['CHAT_TYPE'] == Chat::TYPE_OPEN)
			{
				if (\Bitrix\Im\User::getInstance($userId)->isExtranet())
				{
					return false;
				}
				else
				{
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

					if (strlen($chatData['CHAT_ENTITY_TYPE']) > 0)
					{
						$fieldData = explode("|", $chatData['CHAT_ENTITY_TYPE']);
						if ($fieldData[0] == 'Y')
						{
							$crmEntityType = $fieldData[1];
							$crmEntityId = $fieldData[2];
						}
					}

					return \Bitrix\ImOpenLines\Config::canJoin($chatId, $crmEntityType, $crmEntityId);
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
			{
				if (
					\Bitrix\Im\User::getInstance($userId)->isExtranet()
					|| \Bitrix\Im\User::getInstance($dialogId)->isExtranet()
				)
				{
					if (!\Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($dialogId, $userId))
					{
						return false;
					}
				}
				else
				{
					return true;
				}
			}
			else
			{
				if (
					\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_MESSAGE) == \CIMSettings::PRIVACY_RESULT_CONTACT
					&& \CModule::IncludeModule('socialnetwork')
					&& \CSocNetUser::IsFriendsAllowed()
					&& !\CSocNetUserRelations::IsFriends($dialogId, $userId))
				{
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
					return false;
				}
			}
		}

		return false;
	}
}