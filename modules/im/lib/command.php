<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Im;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\DB\Exception;
Loc::loadMessages(__FILE__);

class Command
{
	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/command/';

	public static function register(array $fields)
	{
		$moduleId = $fields['MODULE_ID'];
		$command = mb_substr($fields['COMMAND'], 0, 1) == '/'? mb_substr($fields['COMMAND'], 1) : $fields['COMMAND'];

		$botId = isset($fields['BOT_ID'])? intval($fields['BOT_ID']): 0;
		if ($botId > 0 && (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot()))
		{
			$botId = 0;
		}

		$common = isset($fields['COMMON']) && $fields['COMMON'] == 'Y'? 'Y': 'N';
		if ($botId <= 0)
		{
			$common = 'Y';
		}

		$hidden = isset($fields['HIDDEN']) && $fields['HIDDEN'] == 'Y'? 'Y': 'N';
		$sonetSupport = isset($fields['SONET_SUPPORT']) && $fields['SONET_SUPPORT'] == 'Y'? 'Y': 'N';
		$extranetSupport = isset($fields['EXTRANET_SUPPORT']) && $fields['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';

		/* vars for module install */
		$class = isset($fields['CLASS'])? $fields['CLASS']: '';
		$methodCommandAdd = isset($fields['METHOD_COMMAND_ADD'])? $fields['METHOD_COMMAND_ADD']: '';
		$methodLangGet = isset($fields['METHOD_LANG_GET'])? $fields['METHOD_LANG_GET']: '';

		/* vars for rest install */
		$appId = isset($fields['APP_ID'])? $fields['APP_ID']: '';
		$langSet = isset($fields['LANG'])? $fields['LANG']: Array();

		if ($moduleId == '')
		{
			return false;
		}
		if ($moduleId == 'rest')
		{
			if (empty($appId) || empty($langSet) && $hidden == 'N')
			{
				return false;
			}
		}
		else
		{
			if (empty($class) || empty($methodCommandAdd))
			{
				return false;
			}
			if (empty($methodLangGet))
			{
				$hidden = 'Y';
			}
		}

		$commands = self::getListCache();
		foreach ($commands as $cmd)
		{
			if ($botId)
			{
				if ($botId == $cmd['BOT_ID'] && $command == $cmd['COMMAND'])
				{
					return $cmd['ID'];
				}
			}
			else if ($appId)
			{
				if ($appId == $cmd['APP_ID'] && $command == $cmd['COMMAND'])
				{
					return $cmd['ID'];
				}
			}
			else if ($moduleId == $cmd['MODULE_ID'] && $command == $cmd['COMMAND'])
			{
				return $cmd['ID'];
			}
		}

		$result = \Bitrix\Im\Model\CommandTable::add(Array(
			'BOT_ID' => $botId,
			'MODULE_ID' => $moduleId,
			'COMMAND' => $command,
			'COMMON' => $common,
			'HIDDEN' => $hidden,
			'SONET_SUPPORT' => $sonetSupport,
			'EXTRANET_SUPPORT' => $extranetSupport,
			'CLASS' => $class,
			'METHOD_COMMAND_ADD' => $methodCommandAdd,
			'METHOD_LANG_GET' => $methodLangGet,
			'APP_ID' => $appId
		));

		if (!$result->isSuccess())
			return false;

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		$commandId = $result->getId();

		if ($moduleId == 'rest')
		{
			foreach ($langSet as $lang)
			{
				if (!isset($lang['LANGUAGE_ID']) || empty($lang['LANGUAGE_ID']))
					continue;

				if (!isset($lang['TITLE']) && empty($lang['TITLE']))
					continue;

				try
				{
					\Bitrix\Im\Model\CommandLangTable::add(array(
						'COMMAND_ID' => $commandId,
						'LANGUAGE_ID' => mb_strtolower($lang['LANGUAGE_ID']),
						'TITLE' => $lang['TITLE'],
						'PARAMS' => isset($lang['PARAMS'])? $lang['PARAMS']: ''
					));
				}
				catch(Exception $e)
				{
				}
			}
		}

		return $commandId;
	}

	public static function unRegister(array $command)
	{
		$commandId = intval($command['COMMAND_ID']);
		$moduleId = isset($command['MODULE_ID'])? $command['MODULE_ID']: '';
		$appId = isset($command['APP_ID'])? $command['APP_ID']: '';

		if (intval($commandId) <= 0)
			return false;

		if (!isset($command['FORCE']) || $command['FORCE'] == 'N')
		{
			$commands = self::getListCache();
			if (!isset($commands[$commandId]))
				return false;

			if ($moduleId <> '' && $commands[$commandId]['MODULE_ID'] != $moduleId)
				return false;

			if ($appId <> '' && $commands[$commandId]['APP_ID'] != $appId)
				return false;
		}

		\Bitrix\Im\Model\CommandTable::delete($commandId);

		$orm = \Bitrix\Im\Model\CommandLangTable::getList(Array(
			'filter' => Array('=COMMAND_ID' => $commandId)
		));
		while ($row = $orm->fetch())
		{
			\Bitrix\Im\Model\CommandLangTable::delete($row['ID']);
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			\CPullStack::AddShared(Array(
				'module_id' => 'im',
				'command' => 'commandDelete',
				'params' => Array(
					'commandId' => $commandId
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public static function update(array $command, array $updateFields)
	{
		$commandId = $command['COMMAND_ID'];
		$moduleId = isset($command['MODULE_ID'])? $command['MODULE_ID']: '';
		$appId = isset($command['APP_ID'])? $command['APP_ID']: '';

		if (intval($commandId) <= 0)
			return false;

		$commands = self::getListCache();
		if (!isset($commands[$commandId]))
			return false;

		if ($moduleId <> '' && $commands[$commandId]['MODULE_ID'] != $moduleId)
			return false;

		if ($appId <> '' && $commands[$commandId]['APP_ID'] != $appId)
			return false;

		if (isset($updateFields['LANG']) && $commands[$commandId]['MODULE_ID'] == 'rest')
		{
			$orm = \Bitrix\Im\Model\CommandLangTable::getList(Array(
				'filter' => Array('=COMMAND_ID' => $commandId)
			));
			while ($row = $orm->fetch())
			{
				\Bitrix\Im\Model\CommandLangTable::delete($row['ID']);
			}
			foreach ($updateFields['LANG'] as $lang)
			{
				if (!isset($lang['LANGUAGE_ID']) || empty($lang['LANGUAGE_ID']))
					continue;

				if (!isset($lang['TITLE']) && empty($lang['TITLE']))
					continue;

				try
				{
					\Bitrix\Im\Model\CommandLangTable::add(array(
						'COMMAND_ID' => $commandId,
						'LANGUAGE_ID' => mb_strtolower($lang['LANGUAGE_ID']),
						'TITLE' => $lang['TITLE'],
						'PARAMS' => isset($lang['PARAMS'])? $lang['PARAMS']: ''
					));
				}
				catch(Exception $e)
				{
				}
			}
		}

		$update = Array();
		if (isset($updateFields['CLASS']) && !empty($updateFields['CLASS']))
		{
			$update['CLASS'] = $updateFields['CLASS'];
		}
		if (isset($updateFields['METHOD_COMMAND_ADD']))
		{
			$update['METHOD_COMMAND_ADD'] = $updateFields['METHOD_COMMAND_ADD'];
		}
		if (isset($updateFields['METHOD_LANG_GET']))
		{
			$update['METHOD_LANG_GET'] = $updateFields['METHOD_LANG_GET'];
		}
		if (isset($updateFields['COMMON']))
		{
			if ($commands[$commandId]['BOT_ID'] <= 0)
			{
				$update['COMMON'] = 'Y';
			}
			else
			{
				$update['COMMON'] = $updateFields['COMMON'] == 'Y'? 'Y': 'N';
			}
		}
		if (isset($updateFields['HIDDEN']))
		{
			$update['HIDDEN'] = $updateFields['HIDDEN'] == 'Y'? 'Y': 'N';
		}
		if (isset($updateFields['EXTRANET_SUPPORT']))
		{
			$update['EXTRANET_SUPPORT'] = $updateFields['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		}
		if (isset($updateFields['SONET_SUPPORT']))
		{
			$update['SONET_SUPPORT'] = $updateFields['SONET_SUPPORT'] == 'Y'? 'Y': 'N';
		}
		if (!empty($update))
		{
			\Bitrix\Im\Model\CommandTable::update($commandId, $update);

			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->cleanDir(self::CACHE_PATH);
		}

		return true;
	}

	public static function onCommandAdd($messageId, $messageFields)
	{
		$skipCommand = $messageFields['SKIP_COMMAND'] ?? null;
		$isSystem = $messageFields['SYSTEM'] ?? null;

		if ($skipCommand === 'Y' || $isSystem === 'Y')
		{
			return true;
		}

		$commands = self::getListCache();
		if (empty($commands))
		{
			return false;
		}

		$commandList = Array();
		$message = $messageFields['MESSAGE'] ?? null;
		if (preg_match_all("/^\\/(?P<COMMAND>[^\\040\\n]*)(\\040?)(?P<PARAMS>.*)$/m", $message, $matches))
		{
			foreach($matches['COMMAND'] as $idx => $cmd)
			{
				$commandData = self::findCommands(Array('COMMAND' => $cmd, 'EXEC_PARAMS' => $matches['PARAMS'][$idx], 'MESSAGE_FIELDS' => $messageFields));
				if (!$commandData)
				{
					continue;
				}

				$commandList = array_merge($commandList, $commandData);
			}
		}
		if (empty($commandList))
		{
			return false;
		}

		$messageFields['DIALOG_ID'] = self::getDialogId($messageFields);
		unset(
			$messageFields['MESSAGE_OUT'],
			$messageFields['NOTIFY_EVENT'],
			$messageFields['NOTIFY_MODULE'],
			$messageFields['URL_PREVIEW']
		);

		$count = 0;
		$executed = Array();
		foreach ($commandList as $params)
		{
			if (!$params['MODULE_ID'] || !\Bitrix\Main\Loader::includeModule($params['MODULE_ID']))
			{
				continue;
			}
			$hash = md5($params['EXEC_PARAMS'].$params['COMMAND_ID']);
			if (isset($executed[$hash]) && $executed[$hash])
			{
				continue;
			}
			$executed[$hash] = true;

			if ($count >= 10)
			{
				break;
			}
			$count++;

			if ($params['BOT_ID'] > 0)
			{
				Bot\Token::add($params['BOT_ID'], $messageFields['DIALOG_ID']);
				if ($messageFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
				{
					Bot\Token::add($params['BOT_ID'], $messageFields['TO_USER_ID']);
				}
			}

			$messageFields['COMMAND'] = $params['COMMAND'];
			$messageFields['COMMAND_ID'] = $params['COMMAND_ID'];
			$messageFields['COMMAND_PARAMS'] = $params['EXEC_PARAMS'];

			if ($params["METHOD_COMMAND_ADD"] && class_exists($params["CLASS"]) && method_exists($params["CLASS"], $params["METHOD_COMMAND_ADD"]))
			{
				if ($params['BOT_ID'] > 0)
				{
					\Bitrix\Im\Model\BotTable::update($params['BOT_ID'], array(
						"COUNT_COMMAND" => new \Bitrix\Main\DB\SqlExpression("?# + 1", "COUNT_COMMAND")
					));
				}

				call_user_func_array(array($params["CLASS"], $params["METHOD_COMMAND_ADD"]), Array($messageId, $messageFields));
			}
		}
		unset(
			$messageFields['COMMAND'],
			$messageFields['COMMAND_ID'],
			$messageFields['COMMAND_PARAMS'],
			$messageFields['COMMAND_CONTEXT']
		);

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "onImCommandAdd") as $event)
		{
			ExecuteModuleEventEx($event, Array($commandList, $messageId, $messageFields));
		}

		return true;
	}

	public static function addMessage(array $access, array $messageFields)
	{
		$messageId = intval($access['MESSAGE_ID']);
		$commandId = intval($access['COMMAND_ID']);
		$moduleId = isset($access['MODULE_ID'])? $access['MODULE_ID']: '';
		$appId = isset($access['APP_ID'])? $access['APP_ID']: '';

		if ($messageId <= 0 || $commandId <= 0)
			return false;

		$commands = self::getListCache();
		if (!isset($commands[$commandId]))
			return false;

		if ($moduleId <> '' && $commands[$commandId]['MODULE_ID'] != $moduleId)
			return false;

		if ($appId <> '' && $commands[$commandId]['APP_ID'] != $appId)
			return false;

		$botId = intval($commands[$commandId]['BOT_ID']);

		$orm = \Bitrix\Im\Model\MessageTable::getById($messageId);
		if (!($message = $orm->fetch()))
			return false;

		$orm = \Bitrix\Im\Model\ChatTable::getById($message['CHAT_ID']);
		if (!($chat = $orm->fetch()))
			return false;

		$relations = \CIMChat::GetRelationById($message['CHAT_ID'], false, true, false);

		$chatWithBot = false;
		foreach ($relations as $userId => $relation)
		{
			if ($relation['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
			{
				$messageFields['DIALOG_ID'] = 'chat'.$message['CHAT_ID'];
				break;
			}
			if ($message['AUTHOR_ID'] == $botId)
			{
				$chatWithBot = true;
				if ($botId != $relation['USER_ID'])
				{
					$messageFields['DIALOG_ID'] = $relation['USER_ID'];
					break;
				}
			}
			else if ($message['AUTHOR_ID'] != $relation['USER_ID'])
			{
				$messageFields['DIALOG_ID'] = $relation['USER_ID'];
				break;
			}
		}

		if ($botId > 0)
		{
			$grantAccess = false;
			if (Bot\Token::isActive($botId, $messageFields['DIALOG_ID']))
			{
				$grantAccess = true;
			}
		}
		else
		{
			$grantAccess = false;
			if (preg_match_all("/^\\/(?P<COMMAND>[^\\040\\n]*)(\\040?)(?P<PARAMS>.*)$/m", $message['MESSAGE'], $matches))
			{
				foreach($matches['COMMAND'] as $idx => $cmd)
				{
					if ($commands[$commandId]['COMMAND'] == $cmd)
					{
						$grantAccess = true;
						break;
					}
				}
			}
		}
		if (!$grantAccess)
			return true;

		$messageFields['ATTACH'] = $messageFields['ATTACH']? $messageFields['ATTACH']: null;
		$messageFields['KEYBOARD'] = $messageFields['KEYBOARD']? $messageFields['KEYBOARD']: null;
		$messageFields['MENU'] = $messageFields['MENU']? $messageFields['MENU']: null;

		if (Common::isChatId($messageFields['DIALOG_ID']))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($messageFields['DIALOG_ID']);
			if ($chatId <= 0)
				return false;

			if (isset($relations[$botId]) && $messageFields['SYSTEM'] != 'Y')
			{
				$ar = Array(
					"FROM_USER_ID" => $botId,
					"TO_CHAT_ID" => $chatId,
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
					"MENU" => $messageFields['MENU'],
				);
				if (isset($messageFields['MESSAGE']))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
			}
			else
			{
				$ar = Array(
					"FROM_USER_ID" => $botId,
					"TO_CHAT_ID" => $chatId,
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
					"MENU" => $messageFields['MENU'],
					"SYSTEM" => 'Y',
				);
				if (isset($messageFields['MESSAGE']))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
				if ($botId > 0)
				{
					$ar['MESSAGE'] = Loc::getMessage("COMMAND_BOT_ANSWER", Array("#BOT_NAME#" => "[USER=".$botId."][/USER]")) . "\n " . $ar['MESSAGE'];
				}
				else
				{
					$ar['MESSAGE'] = "[B]".Loc::getMessage("COMMAND_SYSTEM_ANSWER", Array("#COMMAND#" => "/".$commands[$commandId]['COMMAND']))."[/B]\n ".$ar['MESSAGE'];
				}
			}

			if (isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == 'N')
			{
				$ar['URL_PREVIEW'] = 'N';
			}

			$ar['SKIP_USER_CHECK'] = 'Y';
			$ar['SKIP_COMMAND'] = 'Y';

			$id = \CIMChat::AddMessage($ar);
		}
		else
		{
			if ($chatWithBot)
			{
				$message['AUTHOR_ID'] = intval($messageFields['DIALOG_ID']);
				$userId = $botId;
			}
			else
			{
				$userId = intval($messageFields['DIALOG_ID']);
			}
			\CModule::IncludeModule('imbot');
			if ($botId == $userId && $messageFields['SYSTEM'] != 'Y')
			{
				$ar = Array(
					"FROM_USER_ID" => $userId,
					"TO_USER_ID" => $message['AUTHOR_ID'],
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
				);
				if (isset($messageFields['MESSAGE']))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
			}
			else
			{
				$ar = Array(
					"FROM_USER_ID" => $message['AUTHOR_ID'],
					"TO_USER_ID" => $userId,
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
					"SYSTEM" => "Y",
				);
				if (isset($messageFields['MESSAGE']))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
				if ($botId > 0)
				{
					$ar['MESSAGE'] = Loc::getMessage("COMMAND_BOT_ANSWER", Array("#BOT_NAME#" => "[USER=".$botId."][/USER]")) . "\n " . $ar['MESSAGE'];
				}
				else
				{
					$ar['MESSAGE'] = "[B]".Loc::getMessage("COMMAND_SYSTEM_ANSWER", Array("#COMMAND#" => "/".$commands[$commandId]['COMMAND']))."[/B]\n ".$ar['MESSAGE'];
				}
			}

			if (isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == 'N')
			{
				$ar['URL_PREVIEW'] = 'N';
			}

			$ar['SKIP_COMMAND'] = 'Y';
			$id = \CIMMessage::Add($ar);
		}

		return $id;
	}

	public static function getDialogId($messageFields)
	{
		if ($messageFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$dialogId = $messageFields['FROM_USER_ID'];
		}
		else
		{
			$dialogId = 'chat'.$messageFields['TO_CHAT_ID'];
		}

		return $dialogId;
	}

	private static function findCommands($fields)
	{
		$command = mb_substr($fields['COMMAND'], 0, 1) == '/'? mb_substr($fields['COMMAND'], 1) : $fields['COMMAND'];
		$execParams = isset($fields['EXEC_PARAMS'])? $fields['EXEC_PARAMS']: '';
		$messageFields = isset($fields['MESSAGE_FIELDS'])? $fields['MESSAGE_FIELDS']: Array();

		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$relations = \CIMChat::GetRelationById($messageFields['TO_CHAT_ID'], false, true, false);
		}

		$result = Array();
		if ($command == '')
			return $result;

		$isExtranet = \Bitrix\Im\User::getInstance($messageFields['FROM_USER_ID'])->isExtranet();

		$commands = self::getListCache();
		$bots = Bot::getListCache();
		foreach ($commands as $value)
		{
			$chatEntityType = $messageFields['CHAT_ENTITY_TYPE'] ?? null;
			if (
				$chatEntityType === 'LIVECHAT'
				|| ($chatEntityType === 'LINES' && $bots[$value['BOT_ID']]['OPENLINE'] != 'Y')
			)
			{
				continue;
			}
			if ($value['COMMAND'] == $command)
			{
				if ($value['EXTRANET_SUPPORT'] == 'N' && $isExtranet)
				{
					continue;
				}
				if ($value['COMMON'] == 'N')
				{
					if ($messageFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
					{
						if ($messageFields['TO_USER_ID'] != $value['BOT_ID'])
						{
							continue;
						}
					}
					else
					{
						if (!isset($relations[$value['BOT_ID']]))
						{
							continue;
						}
					}
				}

				$result[$value['COMMAND_ID']] = $value;
				$result[$value['COMMAND_ID']]['CONTEXT'] = $messageFields['COMMAND_CONTEXT'];
				$result[$value['COMMAND_ID']]['EXEC_PARAMS'] = $execParams;
			}
		}

		return $result;
	}

	private static function mergeWithDefaultCommands($commands)
	{
		$defaultCommands = Array(
			Array('COMMAND' => 'me', 'TITLE' => Loc::getMessage("COMMAND_DEF_ME_TITLE"), 'PARAMS' => Loc::getMessage("COMMAND_DEF_ME_PARAMS"), 'HIDDEN' => 'N', 'EXTRANET_SUPPORT' => 'Y'),
			Array('COMMAND' => 'loud', 'TITLE' => Loc::getMessage("COMMAND_DEF_LOUD_TITLE"), 'PARAMS' => Loc::getMessage("COMMAND_DEF_LOUD_PARAMS"), 'HIDDEN' => 'N', 'EXTRANET_SUPPORT' => 'Y'),
			Array('COMMAND' => '>>', 'TITLE' => Loc::getMessage("COMMAND_DEF_QUOTE_TITLE"), 'PARAMS' => Loc::getMessage("COMMAND_DEF_QUOTE_PARAMS"), 'HIDDEN' => 'N', 'EXTRANET_SUPPORT' => 'Y'),
			Array('COMMAND' => 'rename', 'TITLE' => Loc::getMessage("COMMAND_DEF_RENAME_TITLE"), 'PARAMS' => Loc::getMessage("COMMAND_DEF_RENAME_PARAMS"), 'HIDDEN' => 'N', 'EXTRANET_SUPPORT' => 'Y', 'CATEGORY' => Loc::getMessage("COMMAND_DEF_CATEGORY_CHAT"), 'CONTEXT' => 'chat'),
			Array('COMMAND' => 'getDialogId', 'TITLE' => Loc::getMessage("COMMAND_DEF_DIALOGID_TITLE"), 'HIDDEN' => 'N', 'EXTRANET_SUPPORT' => 'N', 'CATEGORY' => Loc::getMessage("COMMAND_DEF_CATEGORY_CHAT")),
			Array('COMMAND' => 'webrtcDebug', 'TITLE' => Loc::getMessage("COMMAND_DEF_WD_TITLE"), 'HIDDEN' => 'N', 'EXTRANET_SUPPORT' => 'Y', 'CATEGORY' => Loc::getMessage("COMMAND_DEF_CATEGORY_DEBUG"), 'CONTEXT' => 'call'),
		);

		$imCommands = Array();
		foreach ($defaultCommands as $i => $command)
		{
			$newCommand['ID'] = 'def'.$i;
			$newCommand['BOT_ID'] = 0;
			$newCommand['APP_ID'] = '';
			$newCommand['COMMAND'] = $command['COMMAND'];
			$newCommand['HIDDEN'] = isset($command['HIDDEN'])? $command['HIDDEN']: 'N';
			$newCommand['COMMON'] = 'Y';
			$newCommand['EXTRANET_SUPPORT'] = isset($command['EXTRANET_SUPPORT'])? $command['EXTRANET_SUPPORT']: 'N';
			$newCommand['SONET_SUPPORT'] = isset($command['SONET_SUPPORT'])? $command['SONET_SUPPORT']: 'N';
			$newCommand['CLASS'] = '';
			$newCommand['METHOD_COMMAND_ADD'] = '';
			$newCommand['METHOD_LANG_GET'] = '';
			if (!$command['TITLE'])
			{
				$newCommand['HIDDEN'] = 'Y';
			}
			$newCommand['MODULE_ID'] = 'im';
			$newCommand['COMMAND_ID'] = $newCommand['ID'];
			$newCommand['CATEGORY'] = isset($command['CATEGORY'])? $command['CATEGORY']: Loc::getMessage('COMMAND_IM_CATEGORY');
			$newCommand['CONTEXT'] = isset($command['CONTEXT'])? $command['CONTEXT']: '';
			$newCommand['TITLE'] = isset($command['TITLE'])? $command['TITLE']: '';
			$newCommand['PARAMS'] = isset($command['PARAMS'])? $command['PARAMS']: '';

			$imCommands[$newCommand['COMMAND_ID']] = $newCommand;
		}

		$result = $imCommands;
		if (is_array($commands))
		{
			foreach ($commands as $i => $v)
			{
				$result[$i] = $v;
			}
		}

		return $result;
	}

	public static function getListCache($lang = LANGUAGE_ID)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_v5_'.$lang, self::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$loadRestLang = false;
			$result = Array();
			$orm = \Bitrix\Im\Model\CommandTable::getList();
			while ($row = $orm->fetch())
			{
				$row['COMMAND_ID'] = $row['ID'];
				$row['CONTEXT'] = '';

				if ($row['BOT_ID'] > 0)
				{
					$row['CATEGORY'] = \Bitrix\Im\User::getInstance($row['BOT_ID'])->getFullName();
				}
				else if ($row['MODULE_ID'] == 'im')
				{
					$row['CATEGORY'] = Loc::getMessage('COMMAND_IM_CATEGORY');
				}
				else
				{
					$moduleClass = new \CModule();
					$module = $moduleClass->createModuleObject($row['MODULE_ID']);
					$row['CATEGORY'] = $module->MODULE_NAME;
				}

				if (!empty($row['CLASS']) && !empty($row['METHOD_LANG_GET']))
				{
					if (\Bitrix\Main\Loader::includeModule($row['MODULE_ID']) && class_exists($row["CLASS"]) && method_exists($row["CLASS"], $row["METHOD_LANG_GET"]))
					{
						$localize = call_user_func_array(array($row["CLASS"], $row["METHOD_LANG_GET"]), Array($row['COMMAND'], $lang));
						if ($localize)
						{
							$row['TITLE'] = $localize['TITLE'];
							$row['PARAMS'] = $localize['PARAMS'];
						}
						else
						{
							$row['HIDDEN'] = 'Y';
							$row['METHOD_LANG_GET'] = '';
						}
					}
					else
					{
						$row['HIDDEN'] = 'Y';
						$row['METHOD_LANG_GET'] = '';
					}
				}
				else
				{
					$row['TITLE'] = '';
					$row['PARAMS'] = '';
					if ($row['MODULE_ID'] == 'rest')
					{
						$loadRestLang = true;
						if ($row['BOT_ID'] <= 0 && $row['APP_ID'])
						{
							$res = \Bitrix\Rest\AppTable::getList([
								'filter' => array('=CLIENT_ID' => $row['APP_ID']),
							]);
							if ($app = $res->fetch())
							{
								$row['CATEGORY'] = !empty($app['APP_NAME'])
									? $app['APP_NAME']
									: (!empty($app['APP_NAME_DEFAULT'])
										? $app['APP_NAME_DEFAULT']
										: $app['CODE']
									)
								;
							}
						}
					}
				}
				$result[$row['COMMAND_ID']] = $row;
			}

			if ($loadRestLang)
			{
				$langSet = Array();
				$orm = \Bitrix\Im\Model\CommandLangTable::getList();
				while ($row = $orm->fetch())
				{
					if (!isset($result[$row['COMMAND_ID']]))
						continue;

					$langSet[$row['COMMAND_ID']][$row['LANGUAGE_ID']]['TITLE'] = $row['TITLE'];
					$langSet[$row['COMMAND_ID']][$row['LANGUAGE_ID']]['PARAMS'] = $row['PARAMS'];
				}

				$langAlter = \Bitrix\Im\Bot::getDefaultLanguage();
				foreach ($result as $commandId => $commandData)
				{
					if (isset($langSet[$commandId][$lang]))
					{
						$result[$commandId]['TITLE'] = $langSet[$commandId][$lang]['TITLE'];
						$result[$commandId]['PARAMS'] = $langSet[$commandId][$lang]['PARAMS'];
					}
					else if (isset($langSet[$commandId][$langAlter]))
					{
						$result[$commandId]['TITLE'] = $langSet[$commandId][$langAlter]['TITLE'];
						$result[$commandId]['PARAMS'] = $langSet[$commandId][$langAlter]['PARAMS'];
					}
					else if (isset($langSet[$commandId]))
					{
						$langSetCommand = array_values($langSet[$commandId]);
						$result[$commandId]['TITLE'] = $langSetCommand[0]['TITLE'];
						$result[$commandId]['PARAMS'] = $langSetCommand[0]['PARAMS'];
					}
				}

				foreach ($result as $key => $value)
				{
					if (empty($value['TITLE']))
					{
						$result[$key]['HIDDEN'] = 'Y';
						$row['METHOD_LANG_GET'] = '';
					}
				}
			}

			if (!empty($result))
			{
				\Bitrix\Main\Type\Collection::sortByColumn(
					$result,
					Array('MODULE_ID' => SORT_ASC),
					'',
					null,
					true
				);
			}

			$result = self::mergeWithDefaultCommands($result);

			$cache->startDataCache();
			$cache->endDataCache($result);
		}


		return $result;
	}

	public static function getListForJs($lang = LANGUAGE_ID)
	{
		$commands = self::getListCache($lang);

		$result = Array();
		foreach ($commands as $command)
		{
			if ($command['HIDDEN'] == 'Y')
				continue;

			$result[] = Array(
				'id' => $command['COMMAND_ID'],
				'bot_id' => $command['BOT_ID'],
				'command' => '/'.$command['COMMAND'],
				'category' => $command['CATEGORY'],
				'common' => $command['COMMON'] == 'Y',
				'context' => $command['CONTEXT'],
				'title' => $command['TITLE'],
				'params' => $command['PARAMS'],
				'extranet' => $command['EXTRANET_SUPPORT'] == 'Y',
			);
		}

		return $result;
	}

	public static function getListSonetForJs($lang = LANGUAGE_ID)
	{
		$commands = self::getListCache($lang);

		$result = Array();
		foreach ($commands as $command)
		{
			if ($command['HIDDEN'] == 'Y')
				continue;

			if ($command['SONET_SUPPORT'] != 'Y')
				continue;

			$result[] = Array(
				'id' => $command['COMMAND_ID'],
				'command' => '/'.$command['COMMAND'],
				'title' => $command['TITLE'],
				'params' => $command['PARAMS'],
				'extranet' => $command['EXTRANET_SUPPORT'] == 'Y',
			);
		}

		return $result;
	}

	public static function clearCache()
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		return true;
	}
}