<?php

namespace Bitrix\Im;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Bot
{
	const INSTALL_TYPE_SYSTEM = 'system';
	const INSTALL_TYPE_USER = 'user';
	const INSTALL_TYPE_SILENT = 'silent';

	const LOGIN_START = 'bot_';
	const EXTERNAL_AUTH_ID = 'bot';

	const LIST_ALL = 'all';
	const LIST_OPENLINE = 'openline';

	const TYPE_HUMAN = 'H';
	const TYPE_BOT = 'B';
	const TYPE_SUPERVISOR = 'S';
	const TYPE_NETWORK = 'N';
	const TYPE_OPENLINE = 'O';

	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/bot/';

	/**
	 * @param array $fields
	 * @return int|bool
	 */
	public static function register(array $fields)
	{
		$code = isset($fields['CODE'])? $fields['CODE']: '';
		$type = in_array($fields['TYPE'], [self::TYPE_HUMAN, self::TYPE_BOT, self::TYPE_SUPERVISOR, self::TYPE_NETWORK, self::TYPE_OPENLINE])
			? $fields['TYPE']
			: self::TYPE_BOT;
		$moduleId = $fields['MODULE_ID'];
		$installType = in_array($fields['INSTALL_TYPE'], [self::INSTALL_TYPE_SYSTEM, self::INSTALL_TYPE_USER, self::INSTALL_TYPE_SILENT])
			? $fields['INSTALL_TYPE']
			: self::INSTALL_TYPE_SILENT;
		$botFields = $fields['PROPERTIES'];
		$language = isset($fields['LANG'])? $fields['LANG']: null;

		/* vars for module install */
		$class = isset($fields['CLASS'])? $fields['CLASS']: '';
		$methodBotDelete = isset($fields['METHOD_BOT_DELETE'])? $fields['METHOD_BOT_DELETE']: '';
		$methodMessageAdd = isset($fields['METHOD_MESSAGE_ADD'])? $fields['METHOD_MESSAGE_ADD']: '';
		$methodMessageUpdate = isset($fields['METHOD_MESSAGE_UPDATE'])? $fields['METHOD_MESSAGE_UPDATE']: '';
		$methodMessageDelete = isset($fields['METHOD_MESSAGE_DELETE'])? $fields['METHOD_MESSAGE_DELETE']: '';
		$methodWelcomeMessage = isset($fields['METHOD_WELCOME_MESSAGE'])? $fields['METHOD_WELCOME_MESSAGE']: '';
		$textPrivateWelcomeMessage = isset($fields['TEXT_PRIVATE_WELCOME_MESSAGE'])? $fields['TEXT_PRIVATE_WELCOME_MESSAGE']: '';
		$textChatWelcomeMessage = isset($fields['TEXT_CHAT_WELCOME_MESSAGE'])? $fields['TEXT_CHAT_WELCOME_MESSAGE']: '';
		$openline = isset($fields['OPENLINE']) && $fields['OPENLINE'] == 'Y'? 'Y': 'N';

		/* rewrite vars for openline type */
		if ($type == self::TYPE_OPENLINE)
		{
			$openline = 'Y';
			$installType = self::INSTALL_TYPE_SILENT;
		}

		/* vars for rest install */
		$appId = isset($fields['APP_ID'])? $fields['APP_ID']: '';
		$verified = isset($fields['VERIFIED']) && $fields['VERIFIED'] == 'Y'? 'Y': 'N';

		if ($moduleId == 'rest')
		{
			if (empty($appId))
			{
				return false;
			}
		}
		else
		{
			if (empty($class) || empty($methodMessageAdd))
			{
				return false;
			}
			if (!(!empty($methodWelcomeMessage) || isset($fields['TEXT_PRIVATE_WELCOME_MESSAGE'])))
			{
				return false;
			}
		}

		$bots = self::getListCache();
		if ($moduleId && $code)
		{
			foreach ($bots as $bot)
			{
				if ($bot['MODULE_ID'] == $moduleId && $bot['CODE'] == $code)
				{
					return $bot['BOT_ID'];
				}
			}
		}

		$userCode = $code? $moduleId.'_'.$code: $moduleId;

		$color = null;
		if (isset($botFields['COLOR']))
		{
			$color = $botFields['COLOR'];
			unset($botFields['COLOR']);
		}

		$userId = 0;
		if ($installType == self::INSTALL_TYPE_USER)
		{
			if (isset($fields['USER_ID']) && intval($fields['USER_ID']) > 0)
			{
				$userId = intval($fields['USER_ID']);
			}
			else
			{
				global $USER;
				if (is_object($USER))
				{
					$userId = $USER->GetID() > 0? $USER->GetID(): 0;
				}
			}
			if ($userId <= 0)
			{
				$installType = self::INSTALL_TYPE_SYSTEM;
			}
		}

		if ($moduleId == '')
		{
			return false;
		}

		if (!(isset($botFields['NAME']) || isset($botFields['LAST_NAME'])))
		{
			return false;
		}

		$botFields['LOGIN'] = mb_substr(self::LOGIN_START. mb_substr($userCode, 0, 40). '_'. randString(5), 0, 50);
		$botFields['PASSWORD'] = md5($botFields['LOGIN'].'|'.rand(1000,9999).'|'.time());
		$botFields['CONFIRM_PASSWORD'] = $botFields['PASSWORD'];
		$botFields['EXTERNAL_AUTH_ID'] = self::EXTERNAL_AUTH_ID;

		unset($botFields['GROUP_ID']);

		$botFields['ACTIVE'] = 'Y';

		unset($botFields['UF_DEPARTMENT']);

		$botFields['WORK_POSITION'] = isset($botFields['WORK_POSITION'])? trim($botFields['WORK_POSITION']): '';
		if (empty($botFields['WORK_POSITION']))
		{
			$botFields['WORK_POSITION'] = Loc::getMessage('BOT_DEFAULT_WORK_POSITION');
		}

		$user = new \CUser;
		$botId = $user->Add($botFields);
		if (!$botId)
		{
			return false;
		}

		$result = \Bitrix\Im\Model\BotTable::add(Array(
			'BOT_ID' => $botId,
			'CODE' => $code? $code: $botId,
			'MODULE_ID' => $moduleId,
			'CLASS' => $class,
			'TYPE' => $type,
			'LANG' => $language? $language: '',
			'METHOD_BOT_DELETE' => $methodBotDelete,
			'METHOD_MESSAGE_ADD' => $methodMessageAdd,
			'METHOD_MESSAGE_UPDATE' => $methodMessageUpdate,
			'METHOD_MESSAGE_DELETE' => $methodMessageDelete,
			'METHOD_WELCOME_MESSAGE' => $methodWelcomeMessage,
			'TEXT_PRIVATE_WELCOME_MESSAGE' => $textPrivateWelcomeMessage,
			'TEXT_CHAT_WELCOME_MESSAGE' => $textChatWelcomeMessage,
			'APP_ID' => $appId,
			'VERIFIED' => $verified,
			'OPENLINE' => $openline
		));

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		if ($result->isSuccess())
		{
			if (\Bitrix\Main\Loader::includeModule('pull'))
			{
				if ($color)
				{
					\CIMStatus::SetColor($botId, $color);
				}

				self::sendPullNotify($botId, 'botAdd');

				if ($installType != self::INSTALL_TYPE_SILENT)
				{
					$message = '';
					if ($installType == self::INSTALL_TYPE_USER && \Bitrix\Im\User::getInstance($userId)->isExists())
					{
						$userName = '[USER='.$userId.'][/USER]';
						$userGender = \Bitrix\Im\User::getInstance($userId)->getGender();
						$message = Loc::getMessage('BOT_MESSAGE_INSTALL_USER'.($userGender == 'F'? '_F':''), Array('#USER_NAME#' => $userName));
					}
					if (empty($message))
					{
						$message = Loc::getMessage('BOT_MESSAGE_INSTALL_SYSTEM');
					}

					$attach = new \CIMMessageParamAttach(null, $color);
					$attach->AddBot(Array(
						"NAME" => \Bitrix\Im\User::getInstance($botId)->getFullName(),
						"AVATAR" => \Bitrix\Im\User::getInstance($botId)->getAvatar(),
						"BOT_ID" => $botId,
					));
					$attach->addMessage(\Bitrix\Im\User::getInstance($botId)->getWorkPosition());

					\CIMChat::AddGeneralMessage(Array(
						'MESSAGE' => $message,
						'ATTACH' => $attach
					));
				}
			}

			\Bitrix\Main\Application::getInstance()->getTaggedCache()->clearByTag("IM_CONTACT_LIST");
		}
		else
		{
			$user->Delete($botId);
			$botId = 0;
		}

		return $botId;
	}

	/**
	 * @param array $bot
	 * @return bool
	 */
	public static function unRegister(array $bot)
	{
		$botId = intval($bot['BOT_ID']);
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		if (intval($botId) <= 0)
		{
			return false;
		}

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
		{
			return false;
		}

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
		{
			return false;
		}

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
		{
			return false;
		}

		\Bitrix\Im\Model\BotTable::delete($botId);

		$orm = \Bitrix\Im\Model\BotChatTable::getList(Array(
			'filter' => Array('=BOT_ID' => $botId)
		));
		if ($row = $orm->fetch())
		{
			\Bitrix\Im\Model\BotChatTable::delete($row['ID']);
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		$user = new \CUser;
		$user->Delete($botId);

		if (\Bitrix\Main\Loader::includeModule($bots[$botId]['MODULE_ID']) && $bots[$botId]["METHOD_BOT_DELETE"] && class_exists($bots[$botId]["CLASS"]) && method_exists($bots[$botId]["CLASS"], $bots[$botId]["METHOD_BOT_DELETE"]))
		{
			call_user_func_array(array($bots[$botId]["CLASS"], $bots[$botId]["METHOD_BOT_DELETE"]), Array($botId));
		}

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImBotDelete") as $event)
		{
			\ExecuteModuleEventEx($event, Array($bots[$botId], $botId));
		}

		$orm = \Bitrix\Im\Model\CommandTable::getList(Array(
			'filter' => Array('=BOT_ID' => $botId)
		));
		while ($row = $orm->fetch())
		{
			\Bitrix\Im\Command::unRegister(Array('COMMAND_ID' => $row['ID'], 'FORCE' => 'Y'));
		}

		$orm = \Bitrix\Im\Model\AppTable::getList(Array(
			'filter' => Array('=BOT_ID' => $botId)
		));
		while ($row = $orm->fetch())
		{
			\Bitrix\Im\App::unRegister(Array('ID' => $row['ID'], 'FORCE' => 'Y'));
		}

		self::sendPullNotify($botId, 'botDelete');

		\Bitrix\Main\Application::getInstance()->getTaggedCache()->clearByTag("IM_CONTACT_LIST");

		return true;
	}

	/**
	 * @param array $bot
	 * @param array $updateFields
	 * @return bool
	 */
	public static function update(array $bot, array $updateFields)
	{
		$botId = intval($bot['BOT_ID']);
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		if ($botId <= 0)
		{
			return false;
		}

		if (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot())
		{
			return false;
		}

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
		{
			return false;
		}

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
		{
			return false;
		}

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
		{
			return false;
		}

		if (isset($updateFields['PROPERTIES']))
		{
			$update = $updateFields['PROPERTIES'];

			// update user properties
			unset(
				$update['ACTIVE'],
				$update['LOGIN'],
				$update['PASSWORD'],
				$update['CONFIRM_PASSWORD'],
				$update['GROUP_ID'],
				$update['UF_DEPARTMENT']
			);

			$update['EXTERNAL_AUTH_ID'] = self::EXTERNAL_AUTH_ID;

			if (isset($update['NAME']) && trim($update['NAME']) == '')
			{
				unset($update['NAME']);
			}
			if (isset($update['WORK_POSITION']) && trim($update['WORK_POSITION']) == '')
			{
				$update['WORK_POSITION'] = Loc::getMessage('BOT_DEFAULT_WORK_POSITION');
			}

			$botAvatar = false;
			$previousBotAvatar = false;
			if (isset($update['PERSONAL_PHOTO']))
			{
				$previousBotAvatar = (int)\Bitrix\Im\User::getInstance($botId)->getAvatarId();

				if (is_numeric($update['PERSONAL_PHOTO']) && (int)$update['PERSONAL_PHOTO'] > 0)
				{
					$botAvatar = (int)$update['PERSONAL_PHOTO'];
					unset($update['PERSONAL_PHOTO']);
				}
			}

			$user = new \CUser;
			$user->Update($botId, $update);

			if ($botAvatar > 0 && $botAvatar !== $previousBotAvatar)
			{
				$connection = Main\Application::getConnection();
				$connection->query("UPDATE b_user SET PERSONAL_PHOTO = ".(int)$botAvatar." WHERE ID = ".(int)$botId);
			}

			if ($previousBotAvatar > 0)
			{
				\CFile::Delete($previousBotAvatar);
			}
		}

		$update = Array();
		if (isset($updateFields['CLASS']) && !empty($updateFields['CLASS']))
		{
			$update['CLASS'] = $updateFields['CLASS'];
		}
		if (isset($updateFields['TYPE']) && !empty($updateFields['TYPE']))
		{
			$update['TYPE'] = $updateFields['TYPE'];
		}
		if (isset($updateFields['CODE']) && !empty($updateFields['CODE']))
		{
			$update['CODE'] = $updateFields['CODE'];
		}
		if (isset($updateFields['APP_ID']) && !empty($updateFields['APP_ID']))
		{
			$update['APP_ID'] = $updateFields['APP_ID'];
		}
		if (isset($updateFields['LANG']))
		{
			$update['LANG'] = $updateFields['LANG']? $updateFields['LANG']: '';
		}
		if (isset($updateFields['METHOD_BOT_DELETE']))
		{
			$update['METHOD_BOT_DELETE'] = $updateFields['METHOD_BOT_DELETE'];
		}
		if (isset($updateFields['METHOD_MESSAGE_ADD']))
		{
			$update['METHOD_MESSAGE_ADD'] = $updateFields['METHOD_MESSAGE_ADD'];
		}
		if (isset($updateFields['METHOD_MESSAGE_UPDATE']))
		{
			$update['METHOD_MESSAGE_UPDATE'] = $updateFields['METHOD_MESSAGE_UPDATE'];
		}
		if (isset($updateFields['METHOD_MESSAGE_DELETE']))
		{
			$update['METHOD_MESSAGE_DELETE'] = $updateFields['METHOD_MESSAGE_DELETE'];
		}
		if (isset($updateFields['METHOD_WELCOME_MESSAGE']))
		{
			$update['METHOD_WELCOME_MESSAGE'] = $updateFields['METHOD_WELCOME_MESSAGE'];
		}
		if (isset($updateFields['TEXT_PRIVATE_WELCOME_MESSAGE']))
		{
			$update['TEXT_PRIVATE_WELCOME_MESSAGE'] = $updateFields['TEXT_PRIVATE_WELCOME_MESSAGE'];
		}
		if (isset($updateFields['TEXT_CHAT_WELCOME_MESSAGE']))
		{
			$update['TEXT_CHAT_WELCOME_MESSAGE'] = $updateFields['TEXT_CHAT_WELCOME_MESSAGE'];
		}
		if (isset($updateFields['VERIFIED']))
		{
			$update['VERIFIED'] = $updateFields['VERIFIED'] == 'Y'? 'Y': 'N';
		}
		if (!empty($update))
		{
			\Bitrix\Im\Model\BotTable::update($botId, $update);

			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->cleanDir(self::CACHE_PATH);
		}

		self::sendPullNotify($botId, 'botUpdate');

		\Bitrix\Main\Application::getInstance()->getTaggedCache()->clearByTag("IM_CONTACT_LIST");

		return true;
	}

	/**
	 * @param int $botId Bot Id.
	 * @param string $messageType Notify type - addBot|updateBot|deleteBot
	 *
	 * @return bool
	 */
	public static function sendPullNotify($botId, $messageType): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return false;
		}

		$botForJs = self::getListForJs();
		if (!isset($botForJs[$botId]))
		{
			return false;
		}

		if ($messageType === 'botAdd' || $messageType === 'botUpdate')
		{

			$userData = \CIMContactList::GetUserData([
				'ID' => $botId,
				'DEPARTMENT' => 'Y',
				'USE_CACHE' => 'N',
				'SHOW_ONLINE' => 'N',
				'PHONES' => 'N'
			]);

			return \CPullStack::AddShared([
				'module_id' => 'im',
				'command' => $messageType,
				'params' => [
					'bot' => $botForJs[$botId],
					'user' => $userData['users'][$botId],
					'userInGroup' => $userData['userInGroup'],
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
		elseif ($messageType === 'botDelete')
		{
			return \CPullStack::AddShared([
				'module_id' => 'im',
				'command' => $messageType,
				'params' => [
					'botId' => $botId
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return false;
	}

	/**
	 * @param int $botId Bot Id.
	 * @param int|null $userId User Id.
	 *
	 * @return bool
	 */
	public static function sendPullOpenDialog(int $botId, int $userId = null): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return false;
		}

		$userId = Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$botForJs = self::getListForJs();
		if (!isset($botForJs[$botId]))
		{
			return false;
		}

		return \Bitrix\Pull\Event::add($userId, [
			'module_id' => 'im',
			'expiry' => 10,
			'command' => 'dialogChange',
			'params' => [
				'dialogId' => $botId
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	public static function onMessageAdd($messageId, $messageFields)
	{
		$botExecModule = self::getBotsForMessage($messageFields);
		if (!$botExecModule)
		{
			return true;
		}

		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$messageFields['MESSAGE_ORIGINAL'] = $messageFields['MESSAGE'];
			if (preg_match("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", $messageFields['MESSAGE'], $matches))
			{
				$messageFields['TO_USER_ID'] = $matches[1];
			}
			else
			{
				$messageFields['TO_USER_ID'] = 0;
			}
			$messageFields['MESSAGE'] = trim(preg_replace('#\[(?P<tag>USER)=\d+\].+?\[/(?P=tag)\],?#', '', $messageFields['MESSAGE']));
		}

		$messageFields['DIALOG_ID'] = self::getDialogId($messageFields);
		$messageFields = self::removeFieldsToEvent($messageFields);

		foreach ($botExecModule as $params)
		{
			if (!$params['MODULE_ID'] || !\Bitrix\Main\Loader::includeModule($params['MODULE_ID']))
			{
				continue;
			}

			$messageFields['BOT_ID'] = $params['BOT_ID'];

			if ($params["METHOD_MESSAGE_ADD"] && class_exists($params["CLASS"]) && method_exists($params["CLASS"], $params["METHOD_MESSAGE_ADD"]))
			{
				\Bitrix\Im\Model\BotTable::update($params['BOT_ID'], array(
					"COUNT_MESSAGE" => new \Bitrix\Main\DB\SqlExpression("?# + 1", "COUNT_MESSAGE")
				));

				call_user_func_array(array($params["CLASS"], $params["METHOD_MESSAGE_ADD"]), Array($messageId, $messageFields));
			}
			else if (class_exists($params["CLASS"]) && method_exists($params["CLASS"], "onMessageAdd"))
			{
				call_user_func_array(array($params["CLASS"], "onMessageAdd"), Array($messageId, $messageFields));
			}
		}
		unset($messageFields['BOT_ID']);

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImBotMessageAdd") as $event)
		{
			\ExecuteModuleEventEx($event, Array($botExecModule, $messageId, $messageFields));
		}

		if (
			$messageFields['CHAT_ENTITY_TYPE'] == 'LINES'
			&& trim($messageFields['MESSAGE']) === '0'
			&& \Bitrix\Main\Loader::includeModule('imopenlines')
		)
		{
			$chat = new \Bitrix\Imopenlines\Chat($messageFields['TO_CHAT_ID']);
			$chat->endBotSession();
		}

		return true;
	}

	public static function onMessageUpdate($messageId, $messageFields)
	{
		$botExecModule = self::getBotsForMessage($messageFields);
		if (!$botExecModule)
		{
			return true;
		}

		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$messageFields['MESSAGE_ORIGINAL'] = $messageFields['MESSAGE'];
			if (preg_match("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", $messageFields['MESSAGE'], $matches))
			{
				$messageFields['TO_USER_ID'] = $matches[1];
			}
			else
			{
				$messageFields['TO_USER_ID'] = 0;
			}
			$messageFields['MESSAGE'] = trim(preg_replace('#\[(?P<tag>USER)=\d+\].+?\[/(?P=tag)\],?#', '', $messageFields['MESSAGE']));
		}

		$messageFields['DIALOG_ID'] = self::getDialogId($messageFields);
		$messageFields = self::removeFieldsToEvent($messageFields);

		foreach ($botExecModule as $params)
		{
			if (!$params['MODULE_ID'] || !\Bitrix\Main\Loader::includeModule($params['MODULE_ID']))
			{
				continue;
			}

			$messageFields['BOT_ID'] = $params['BOT_ID'];

			if ($params["METHOD_MESSAGE_UPDATE"] && class_exists($params["CLASS"]) && method_exists($params["CLASS"], $params["METHOD_MESSAGE_UPDATE"]))
			{
				call_user_func_array(array($params["CLASS"], $params["METHOD_MESSAGE_UPDATE"]), Array($messageId, $messageFields));
			}
			else if (class_exists($params["CLASS"]) && method_exists($params["CLASS"], "onMessageUpdate"))
			{
				call_user_func_array(array($params["CLASS"], "onMessageUpdate"), Array($messageId, $messageFields));
			}
		}
		unset($messageFields['BOT_ID']);

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImBotMessageUpdate") as $event)
		{
			\ExecuteModuleEventEx($event, Array($botExecModule, $messageId, $messageFields));
		}

		return true;
	}

	public static function onMessageDelete($messageId, $messageFields)
	{
		$botExecModule = self::getBotsForMessage($messageFields);
		if (!$botExecModule)
		{
			return true;
		}

		$messageFields['DIALOG_ID'] = self::getDialogId($messageFields);
		$messageFields = self::removeFieldsToEvent($messageFields);

		foreach ($botExecModule as $params)
		{
			if (!$params['MODULE_ID'] || !\Bitrix\Main\Loader::includeModule($params['MODULE_ID']))
			{
				continue;
			}

			$messageFields['BOT_ID'] = $params['BOT_ID'];

			if ($params["METHOD_MESSAGE_DELETE"] && class_exists($params["CLASS"]) && method_exists($params["CLASS"], $params["METHOD_MESSAGE_DELETE"]))
			{
				call_user_func_array(array($params["CLASS"], $params["METHOD_MESSAGE_DELETE"]), Array($messageId, $messageFields));
			}
			else if (class_exists($params["CLASS"]) && method_exists($params["CLASS"], "onMessageDelete"))
			{
				call_user_func_array(array($params["CLASS"], "onMessageDelete"), Array($messageId, $messageFields));
			}
		}
		unset($messageFields['BOT_ID']);

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImBotMessageDelete") as $event)
		{
			\ExecuteModuleEventEx($event, Array($botExecModule, $messageId, $messageFields));
		}

		return true;
	}

	public static function onJoinChat($dialogId, $joinFields)
	{
		$bots = self::getListCache();
		if (empty($bots))
		{
			return true;
		}

		if (!isset($joinFields['BOT_ID']) || !$bots[$joinFields['BOT_ID']])
		{
			return false;
		}

		$bot = $bots[$joinFields['BOT_ID']];

		if (!\Bitrix\Main\Loader::includeModule($bot['MODULE_ID']))
		{
			return false;
		}

		if ($joinFields['CHAT_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$updateCounter = array("COUNT_USER" => new \Bitrix\Main\DB\SqlExpression("?# + 1", "COUNT_USER"));
		}
		else
		{
			$updateCounter = array("COUNT_CHAT" => new \Bitrix\Main\DB\SqlExpression("?# + 1", "COUNT_CHAT"));
		}
		\Bitrix\Im\Model\BotTable::update($joinFields['BOT_ID'], $updateCounter);

		if ($joinFields['CHAT_TYPE'] != IM_MESSAGE_PRIVATE && $bot['TYPE'] == self::TYPE_SUPERVISOR)
		{
			\CIMMessenger::Add(Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE_TYPE' => $joinFields['CHAT_TYPE'],
				'MESSAGE' => str_replace(Array('#BOT_NAME#'), Array('[USER='.$joinFields['BOT_ID'].'][/USER]'), $joinFields['ACCESS_HISTORY']? Loc::getMessage('BOT_SUPERVISOR_NOTICE_ALL_MESSAGES'): Loc::getMessage('BOT_SUPERVISOR_NOTICE_NEW_MESSAGES')),
				'SYSTEM' => 'Y',
				'SKIP_COMMAND' => 'Y',
				'PARAMS' => Array(
					"CLASS" => "bx-messenger-content-item-system"
				),
			));
		}

		if ($bot["METHOD_WELCOME_MESSAGE"] && class_exists($bot["CLASS"]) && method_exists($bot["CLASS"], $bot["METHOD_WELCOME_MESSAGE"]))
		{
			call_user_func_array(array($bot["CLASS"], $bot["METHOD_WELCOME_MESSAGE"]), Array($dialogId, $joinFields));
		}
		else if (
			$bot["TEXT_PRIVATE_WELCOME_MESSAGE"] <> ''
			&& $joinFields['CHAT_TYPE'] == IM_MESSAGE_PRIVATE
			&& $joinFields['FROM_USER_ID'] != $joinFields['BOT_ID']
		)
		{
			if ($bot['TYPE'] == self::TYPE_HUMAN)
			{
				self::startWriting(Array('BOT_ID' => $joinFields['BOT_ID']), $dialogId);
			}

			$userName = \Bitrix\Im\User::getInstance($joinFields['USER_ID'])->getName();
			self::addMessage(Array('BOT_ID' => $joinFields['BOT_ID']), Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => str_replace(Array('#USER_NAME#'), Array($userName), $bot["TEXT_PRIVATE_WELCOME_MESSAGE"]),
			));
		}
		else if (
			$bot["TEXT_CHAT_WELCOME_MESSAGE"] <> ''
			&& (
				$joinFields['CHAT_TYPE'] == IM_MESSAGE_CHAT
				|| $joinFields['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE
			)
			&& $joinFields['FROM_USER_ID'] != $joinFields['BOT_ID']
		)
		{
			if ($bot['TYPE'] == self::TYPE_HUMAN)
			{
				self::startWriting(Array('BOT_ID' => $joinFields['BOT_ID']), $dialogId);
			}
			$userName = \Bitrix\Im\User::getInstance($joinFields['USER_ID'])->getName();
			self::addMessage(Array('BOT_ID' => $joinFields['BOT_ID']), Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => str_replace(Array('#USER_NAME#'), Array($userName), $bot["TEXT_CHAT_WELCOME_MESSAGE"]),
			));
		}

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImBotJoinChat") as $event)
		{
			\ExecuteModuleEventEx($event, Array($bot, $dialogId, $joinFields));
		}

		return true;
	}

	public static function onLeaveChat($dialogId, $leaveFields)
	{
		$bots = self::getListCache();
		if (empty($bots))
		{
			return true;
		}

		if (!isset($leaveFields['BOT_ID']) || !$bots[$leaveFields['BOT_ID']])
		{
			return false;
		}

		$bot = $bots[$leaveFields['BOT_ID']];

		if (!\Bitrix\Main\Loader::includeModule($bot['MODULE_ID']))
		{
			return false;
		}

		if ($leaveFields['CHAT_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$updateCounter = array("COUNT_USER" => new \Bitrix\Main\DB\SqlExpression("?# - 1", "COUNT_USER"));
		}
		else
		{
			$updateCounter = array("COUNT_CHAT" => new \Bitrix\Main\DB\SqlExpression("?# - 1", "COUNT_CHAT"));
		}
		\Bitrix\Im\Model\BotTable::update($leaveFields['BOT_ID'], $updateCounter);

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImBotLeaveChat") as $event)
		{
			\ExecuteModuleEventEx($event, Array($bot, $dialogId, $leaveFields));
		}

		return true;
	}

	public static function startWriting(array $bot, $dialogId, $userName = '')
	{
		$botId = $bot['BOT_ID'];
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		if (intval($botId) <= 0)
		{
			return false;
		}

		if (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot())
		{
			return false;
		}

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
		{
			return false;
		}

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
		{
			return false;
		}

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
		{
			return false;
		}

		\CIMMessenger::StartWriting($dialogId, $botId, $userName);

		return true;
	}

	/**
	 * @param array $bot
	 * <pre>
	 * [
	 * 	(int) BOT_ID
	 * 	(string) MODULE_ID
	 * 	(string) APP_ID
	 * ]
	 * </pre>
	 * @param array $messageFields
	 * <pre>
	 * [
	 * 	(int) MESSAGE_ID
	 * 	(int) FROM_USER_ID
	 * 	(int) TO_USER_ID
	 * 	(string|int) DIALOG_ID
	 * 	(array) PARAMS
	 * 	(array) ATTACH
	 * 	(array) KEYBOARD
	 * 	(array) MENU
	 * 	(string) MESSAGE
	 * 	(string) URL_PREVIEW - Y|N
	 * 	(string) SYSTEM - Y|N
	 * 	(string) SKIP_CONNECTOR - Y|N
	 * 	(string) EDIT_FLAG - Y|N
	 * ]
	 * </pre>
	 *
	 * @return bool
	 */
	public static function addMessage(array $bot, array $messageFields)
	{
		$botId = $bot['BOT_ID'];
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		if (intval($botId) <= 0)
		{
			return false;
		}

		if (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot())
		{
			return false;
		}

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
		{
			return false;
		}

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
		{
			return false;
		}

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
		{
			return false;
		}

		$isPrivateSystem = false;
		if (($messageFields['FROM_USER_ID'] ?? null) && ($messageFields['TO_USER_ID'] ?? null))
		{
			$messageFields['SYSTEM'] = 'Y';
			$messageFields['DIALOG_ID'] = $messageFields['TO_USER_ID'];
			$isPrivateSystem = true;
		}
		else if (empty($messageFields['DIALOG_ID']))
		{
			return false;
		}

		$messageFields['MENU'] ??= null;
		$messageFields['ATTACH'] ??= null;
		$messageFields['KEYBOARD'] ??= null;
		$messageFields['PARAMS'] ??= [];

		if (Common::isChatId($messageFields['DIALOG_ID']))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($messageFields['DIALOG_ID']);
			if ($chatId <= 0)
			{
				return false;
			}

			if (\CIMChat::GetGeneralChatId() == $chatId && !\CIMChat::CanSendMessageToGeneralChat($botId))
			{
				return false;
			}
			else
			{
				$ar = Array(
					"FROM_USER_ID" => $botId,
					"TO_CHAT_ID" => $chatId,
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
					"MENU" => $messageFields['MENU'],
					"PARAMS" => $messageFields['PARAMS'],
				);
				if (isset($messageFields['MESSAGE']) && (!empty($messageFields['MESSAGE']) || $messageFields['MESSAGE'] === "0"))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
				if (isset($messageFields['SYSTEM']) && $messageFields['SYSTEM'] == 'Y')
				{
					$ar['SYSTEM'] = 'Y';
					$ar['MESSAGE'] = \Bitrix\Im\User::getInstance($botId)->getFullName().":[br]".$ar['MESSAGE'];
				}
				if (isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == 'N')
				{
					$ar['URL_PREVIEW'] = 'N';
				}
				if (isset($messageFields['SKIP_CONNECTOR']) && $messageFields['SKIP_CONNECTOR'] == 'Y')
				{
					$ar['SKIP_CONNECTOR'] = 'Y';
					$ar['SILENT_CONNECTOR'] = 'Y';
				}
				$ar['SKIP_COMMAND'] = 'Y';
				$id = \CIMChat::AddMessage($ar);
			}
		}
		else
		{
			if ($isPrivateSystem)
			{
				$fromUserId = intval($messageFields['FROM_USER_ID']);
				if ($botId > 0)
				{
					$messageFields['MESSAGE'] = Loc::getMessage("BOT_MESSAGE_FROM", Array("#BOT_NAME#" => "[USER=".$botId."][/USER][BR]")).$messageFields['MESSAGE'];
				}
			}
			else
			{
				$fromUserId = $botId;
			}

			$userId = intval($messageFields['DIALOG_ID']);
			$ar = Array(
				"FROM_USER_ID" => $fromUserId,
				"TO_USER_ID" => $userId,
				"ATTACH" => $messageFields['ATTACH'],
				"KEYBOARD" => $messageFields['KEYBOARD'],
				"MENU" => $messageFields['MENU'],
				"PARAMS" => $messageFields['PARAMS'],
			);
			if (isset($messageFields['MESSAGE']) && (!empty($messageFields['MESSAGE']) || $messageFields['MESSAGE'] === "0"))
			{
				$ar['MESSAGE'] = $messageFields['MESSAGE'];
			}
			if (isset($messageFields['SYSTEM']) && $messageFields['SYSTEM'] == 'Y')
			{
				$ar['SYSTEM'] = 'Y';
			}
			if (isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == 'N')
			{
				$ar['URL_PREVIEW'] = 'N';
			}
			if (isset($messageFields['SKIP_CONNECTOR']) && $messageFields['SKIP_CONNECTOR'] == 'Y')
			{
				$ar['SKIP_CONNECTOR'] = 'Y';
				$ar['SILENT_CONNECTOR'] = 'Y';
			}
			$ar['SKIP_COMMAND'] = 'Y';
			$id = \CIMMessage::Add($ar);
		}

		return $id;
	}

	/**
	 * @param array $bot
	 * <pre>
	 * [
	 * 	(int) BOT_ID
	 * 	(string) MODULE_ID
	 * 	(string) APP_ID
	 * ]
	 * </pre>
	 * @param array $messageFields
	 * <pre>
	 * [
	 * 	(int) MESSAGE_ID
	 * 	(array) ATTACH
	 * 	(array) KEYBOARD
	 * 	(array) MENU
	 * 	(string) MESSAGE
	 * 	(string) URL_PREVIEW - Y|N
	 * 	(string) SKIP_CONNECTOR - Y|N
	 * 	(string) EDIT_FLAG - Y|N
	 * ]
	 * </pre>
	 *
	 * @return bool
	 */
	public static function updateMessage(array $bot, array $messageFields)
	{
		$botId = $bot['BOT_ID'];
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		if (intval($botId) <= 0)
			return false;

		if (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot())
			return false;

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
			return false;

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
			return false;

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
			return false;

		$messageId = intval($messageFields['MESSAGE_ID']);
		if ($messageId <= 0)
			return false;

		$message = \CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $messageId, $botId);
		if (!$message)
			return false;

		if (isset($messageFields['ATTACH']))
		{
			if (empty($messageFields['ATTACH']) || $messageFields['ATTACH'] == 'N')
			{
				\CIMMessageParam::Set($messageId, Array('ATTACH' => Array()));
			}
			else if ($messageFields['ATTACH'] instanceof \CIMMessageParamAttach)
			{
				if ($messageFields['ATTACH']->IsAllowSize())
				{
					\CIMMessageParam::Set($messageId, Array('ATTACH' => $messageFields['ATTACH']));
				}
			}
		}

		if (isset($messageFields['KEYBOARD']))
		{
			if (empty($messageFields['KEYBOARD']) || $messageFields['KEYBOARD'] == 'N')
			{
				\CIMMessageParam::Set($messageId, Array('KEYBOARD' => 'N'));
			}
			else if ($messageFields['KEYBOARD'] instanceof \Bitrix\Im\Bot\Keyboard)
			{
				if ($messageFields['KEYBOARD']->isAllowSize())
				{
					\CIMMessageParam::Set($messageId, Array('KEYBOARD' => $messageFields['KEYBOARD']));
				}
			}
		}

		if (isset($messageFields['MENU']))
		{
			if (empty($messageFields['MENU']) || $messageFields['MENU'] == 'N')
			{
				\CIMMessageParam::Set($messageId, Array('MENU' => 'N'));
			}
			else if ($messageFields['MENU'] instanceof \Bitrix\Im\Bot\ContextMenu)
			{
				if ($messageFields['MENU']->isAllowSize())
				{
					\CIMMessageParam::Set($messageId, Array('MENU' => $messageFields['MENU']));
				}
			}
		}

		if (isset($messageFields['MESSAGE']))
		{
			$urlPreview = isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == "N"? false: true;
			$skipConnector = isset($messageFields['SKIP_CONNECTOR']) && $messageFields['SKIP_CONNECTOR'] == "Y"? true: false;
			$editFlag = isset($messageFields['EDIT_FLAG']) && $messageFields['EDIT_FLAG'] == "Y"? true: false;

			$res = \CIMMessenger::Update($messageId, $messageFields['MESSAGE'], $urlPreview, $editFlag, $botId, $skipConnector);
			if (!$res)
			{
				return false;
			}
		}
		\CIMMessageParam::SendPull($messageId, Array('KEYBOARD', 'ATTACH', 'MENU'));

		return true;
	}

	public static function deleteMessage(array $bot, $messageId)
	{
		$botId = $bot['BOT_ID'];
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		$messageId = intval($messageId);
		if ($messageId <= 0)
			return false;

		if (intval($botId) <= 0)
			return false;

		if (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot())
			return false;

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
			return false;

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
			return false;

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
			return false;

		return \CIMMessenger::Delete($messageId, $botId);
	}

	public static function likeMessage(array $bot, $messageId, $action = 'AUTO')
	{
		$botId = $bot['BOT_ID'];
		$moduleId = isset($bot['MODULE_ID'])? $bot['MODULE_ID']: '';
		$appId = isset($bot['APP_ID'])? $bot['APP_ID']: '';

		$messageId = intval($messageId);
		if ($messageId <= 0)
			return false;

		if (intval($botId) <= 0)
			return false;

		if (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot())
			return false;

		$bots = self::getListCache();
		if (!isset($bots[$botId]))
			return false;

		if ($moduleId <> '' && $bots[$botId]['MODULE_ID'] != $moduleId)
			return false;

		if ($appId <> '' && $bots[$botId]['APP_ID'] != $appId)
			return false;

		return \CIMMessenger::Like($messageId, $action, $botId);
	}

	public static function getDialogId($messageFields)
	{
		if ($messageFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$dialogId = $messageFields['FROM_USER_ID'];
		}
		else
		{
			$dialogId = 'chat'.($messageFields['TO_CHAT_ID'] ?? $messageFields['CHAT_ID']);
		}

		return $dialogId;
	}

	private static function findBots($fields)
	{
		$result = Array();
		if (intval($fields['BOT_ID']) <= 0)
			return $result;

		$bots = self::getListCache();
		if ($fields['TYPE'] == IM_MESSAGE_PRIVATE)
		{
			if (isset($bots[$fields['BOT_ID']]))
			{
				$result = $bots[$fields['BOT_ID']];
			}
		}
		else
		{
			if (isset($bots[$fields['BOT_ID']]))
			{
				$chats = self::getChatListCache($fields['BOT_ID']);
				if (isset($chats[$fields['CHAT_ID']]))
				{
					$result = $bots[$fields['BOT_ID']];
				}
			}
		}

		return $result;
	}

	public static function getCache($botId)
	{
		$botList = self::getListCache();
		return isset($botList[$botId])? $botList[$botId]: false;
	}

	public static function clearCache()
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		return true;
	}

	public static function getListCache($type = self::LIST_ALL)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_r5', self::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$result = Array();
			$orm = \Bitrix\Im\Model\BotTable::getList();
			while ($row = $orm->fetch())
			{
				$row['LANG'] = $row['LANG']? $row['LANG']: null;
				$result[$row['BOT_ID']] = $row;
			}

			$cache->startDataCache();
			$cache->endDataCache($result);
		}

		if ($type == self::LIST_OPENLINE)
		{
			foreach ($result as $botId => $botData)
			{
				if ($botData['OPENLINE'] != 'Y' || $botData['CODE'] == 'marta')
				{
					unset($result[$botId]);
				}
			}
		}

		return $result;
	}

	public static function getListForJs()
	{
		$result = Array();
		$bots = self::getListCache();
		foreach ($bots as $bot)
		{
			$type = 'bot';
			$code = $bot['CODE'];

			if ($bot['TYPE'] == self::TYPE_HUMAN)
			{
				$type = 'human';
			}
			else if ($bot['TYPE'] == self::TYPE_NETWORK)
			{
				$type = 'network';

				if ($bot['CLASS'] == 'Bitrix\ImBot\Bot\Support24')
				{
					$type = 'support24';
					$code = 'network_cloud';
				}
				else if ($bot['CLASS'] == 'Bitrix\ImBot\Bot\Partner24')
				{
					$type = 'support24';
					$code = 'network_partner';
				}
				else if ($bot['CLASS'] == 'Bitrix\ImBot\Bot\SupportBox')
				{
					$type = 'support24';
					$code = 'network_box';
				}
			}
			else if ($bot['TYPE'] == self::TYPE_OPENLINE)
			{
				$type = 'openline';
			}
			else if ($bot['TYPE'] == self::TYPE_SUPERVISOR)
			{
				$type = 'supervisor';
			}

			$result[$bot['BOT_ID']] = Array(
				'id' => $bot['BOT_ID'],
				'code' => $code,
				'type' => $type,
				'openline' => $bot['OPENLINE'] == 'Y',
			);
		}

		return $result;
	}

	private static function removeFieldsToEvent($messageFields)
	{
		unset(
			$messageFields['BOT_IN_CHAT'],
			$messageFields['MESSAGE_OUT'],
			$messageFields['NOTIFY_EVENT'],
			$messageFields['NOTIFY_MODULE'],
			$messageFields['URL_PREVIEW'],
			$messageFields['DATE_CREATE'],
			$messageFields['EMAIL_TEMPLATE'],
			$messageFields['RECENT_ADD'],
			$messageFields['SKIP_USER_CHECK'],
			$messageFields['DATE_CREATE'],
			$messageFields['EMAIL_TEMPLATE'],
			$messageFields['NOTIFY_TYPE'],
			$messageFields['NOTIFY_TAG'],
			$messageFields['NOTIFY_TITLE'],
			$messageFields['NOTIFY_BUTTONS'],
			$messageFields['NOTIFY_READ'],
			$messageFields['NOTIFY_READ'],
			$messageFields['IMPORT_ID'],
			$messageFields['NOTIFY_SUB_TAG'],
			$messageFields['CHAT_PARENT_ID'],
			$messageFields['CHAT_PARENT_MID'],
			$messageFields['DATE_MODIFY']
		);

		return $messageFields;
	}

	private static function getChatListCache($botId)
	{
		$botId = intval($botId);
		if ($botId <= 0)
			return Array();

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'chat'.$botId, self::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$result = Array();
			$orm = \Bitrix\Im\Model\BotChatTable::getList(Array(
				'filter' => Array('=BOT_ID' => $botId)
			));
			while ($row = $orm->fetch())
			{
				$result[$row['CHAT_ID']] = $row;
			}

			$cache->startDataCache();
			$cache->endDataCache($result);
		}

		return $result;
	}

	public static function changeChatMembers($chatId, $botId, $append = true)
	{
		$chatId = intval($chatId);
		$botId = intval($botId);

		if ($chatId <= 0 || $botId <= 0)
			return false;

		$chats = self::getChatListCache($botId);

		if ($append)
		{
			if (isset($chats[$chatId]))
			{
				return true;
			}
			\Bitrix\Im\Model\BotChatTable::add(Array(
				'BOT_ID' => $botId,
				'CHAT_ID' => $chatId
			));
		}
		else
		{
			if (!isset($chats[$chatId]))
			{
				return true;
			}

			$orm = \Bitrix\Im\Model\BotChatTable::getList(Array(
				'filter' => Array('=BOT_ID' => $botId, '=CHAT_ID' => $chatId)
			));
			if ($row = $orm->fetch())
			{
				\Bitrix\Im\Model\BotChatTable::delete($row['ID']);
			}
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->clean('chat'.$botId, self::CACHE_PATH);

		return true;
	}

	public static function getDefaultLanguage()
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'language_v2', '/bx/im/'))
		{
			$languageId = $cache->getVars();
		}
		else
		{
			$languageId = '';

			$siteIterator = \Bitrix\Main\SiteTable::getList(array(
				'select' => array('LANGUAGE_ID'),
				'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
			));
			if ($site = $siteIterator->fetch())
				$languageId = (string)$site['LANGUAGE_ID'];

			if ($languageId == '')
			{
				if (\Bitrix\Main\Loader::includeModule('bitrix24'))
				{
					$languageId = \CBitrix24::getLicensePrefix();
				}
				else
				{
					$languageId = LANGUAGE_ID;
				}
			}
			if ($languageId == '')
			{
				$languageId = 'en';
			}

			$languageId = mb_strtolower($languageId);

			$cache->startDataCache();
			$cache->endDataCache($languageId);
		}

		return $languageId;
	}

	public static function deleteExpiredTokenAgent()
	{
		$orm = \Bitrix\Im\Model\BotTokenTable::getList(Array(
			'filter' => array(
				'<DATE_EXPIRE' => new \Bitrix\Main\Type\DateTime(),
			),
			'select' => array('ID'),
			'limit' => 1
		));
		if ($token = $orm->fetch())
		{
			$application = \Bitrix\Main\Application::getInstance();
			$connection = $application->getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$connection->query("
				DELETE FROM b_im_bot_token
				WHERE DATE_EXPIRE < ".$sqlHelper->getCurrentDateTimeFunction()."
			");
		}

		return "\\Bitrix\\Im\\Bot::deleteExpiredTokenAgent();";
	}

	/**
	 * @param $messageFields
	 * @return array
	 */
	private static function getBotsForMessage($messageFields): array
	{
		$bots = self::getListCache();
		if (empty($bots))
		{
			return [];
		}

		if (isset($messageFields['FROM_USER_ID'], $bots[$messageFields['FROM_USER_ID']]))
		{
			return [];
		}
		if (
			$messageFields['MESSAGE_TYPE'] === \IM_MESSAGE_CHAT
			&& $messageFields['CHAT_ENTITY_TYPE'] === 'SUPPORT24_QUESTION' /** @see \Bitrix\ImBot\Bot\Support24::CHAT_ENTITY_TYPE */
			&& isset($bots[$messageFields['AUTHOR_ID']])
		)
		{
			return [];
		}

		$botExecModule = [];
		if ($messageFields['MESSAGE_TYPE'] === \IM_MESSAGE_PRIVATE)
		{
			if (isset($bots[$messageFields['TO_USER_ID']]))
			{
				$botExecModule[$messageFields['TO_USER_ID']] = $bots[$messageFields['TO_USER_ID']];
			}
		}
		else
		{
			$botFound = [];
			$message = $messageFields['MESSAGE'] ?? null;
			if (
				$messageFields['CHAT_ENTITY_TYPE'] === 'LINES'
				|| $messageFields['CHAT_ENTITY_TYPE'] === 'SUPPORT24_QUESTION' /** @see \Bitrix\ImBot\Bot\Support24::CHAT_ENTITY_TYPE */
			)
			{
				$botFound = $messageFields['BOT_IN_CHAT'];
			}
			else if (preg_match_all("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", $message, $matches))
			{
				foreach ($matches[1] as $userId)
				{
					if (isset($bots[$userId]) && isset($messageFields['BOT_IN_CHAT'][$userId]))
					{
						$botFound[$userId] = $userId;
					}
				}
			}

			foreach ($messageFields['BOT_IN_CHAT'] as $botId)
			{
				if (isset($bots[$botId]) && $bots[$botId]['TYPE'] == self::TYPE_SUPERVISOR)
				{
					$botFound[$botId] = $botId;
				}
			}

			if (!empty($botFound))
			{
				foreach ($botFound as $botId)
				{
					if (!isset($bots[$botId]))
					{
						continue;
					}
					if ($messageFields['CHAT_ENTITY_TYPE'] == 'LINES' && $bots[$botId]['OPENLINE'] == 'N')
					{
						continue;
					}
					$botExecModule[$botId] = $bots[$botId];
				}
			}
		}

		return $botExecModule;
	}
}