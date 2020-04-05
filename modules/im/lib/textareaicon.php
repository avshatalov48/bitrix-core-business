<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Im;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\DB\Exception;
Loc::loadMessages(__FILE__);

class TextareaIcon
{
	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/textareaicon/';

	public static function register(array $fields)
	{
		$moduleId = $fields['MODULE_ID'];
		if (strlen($moduleId) <= 0)
		{
			return false;
		}
		
		$iframe = '';
		$iframeWidth = 350;
		$iframeHeight = 250;
		$jscommand = isset($fields['JS'])? $fields['JS']: '';
		
		if (isset($fields['IFRAME']))
		{
			$check = parse_url($fields['IFRAME']);
			if (!isset($check['scheme']) && !isset($check['host']))
			{
				if (strpos($fields['IFRAME'], '/desktop_app/iframe/') !== 0)
				{
					return false;
				}
			}
			else if (!in_array($check['scheme'], Array('http', 'https')) || empty($check['host']))
			{
				return false;
			}
			$iframe = $fields['IFRAME'].(isset($check['query'])? '&': '?');
			$iframeWidth = isset($fields['IFRAME_WIDTH']) && intval($fields['IFRAME_WIDTH']) > 250? intval($fields['IFRAME_WIDTH']): $iframeWidth;
			$iframeHeight = isset($fields['IFRAME_HEIGHT']) && intval($fields['IFRAME_HEIGHT']) > 50? intval($fields['IFRAME_HEIGHT']): $iframeHeight;
		}
		else if (!$jscommand)
		{
			return false;
		}
		
		
		$icon = $fields['ICON_CODE'];
		if (!$icon)
		{
			return false;
		}
		
		$iconFileId = intval($fields['ICON_FILE_ID']);
		
		$hash = isset($fields['HASH'])? substr($fields['HASH'], 0, 32): 'register';
		$context = isset($fields['CONTEXT'])? $fields['CONTEXT']: 'ALL';
		$hidden = isset($fields['HIDDEN']) && $fields['HIDDEN'] == 'Y'? 'Y': 'N';
		$botId = isset($fields['BOT_ID'])? intval($fields['BOT_ID']): 0;
		if ($botId > 0 && (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot()))
		{
			$botId = 0;
		}
		
		$extranetSupport = isset($fields['EXTRANET_SUPPORT']) && $fields['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		
		/* vars for module install */
		$class = isset($fields['CLASS'])? $fields['CLASS']: '';
		$methodLangGet = isset($fields['METHOD_LANG_GET'])? $fields['METHOD_LANG_GET']: '';

		/* vars for rest install */
		$appId = isset($fields['APP_ID'])? $fields['APP_ID']: '';
		$langSet = isset($fields['LANG'])? $fields['LANG']: Array();
		
		if ($moduleId == 'rest')
		{
			if (empty($langSet))
			{
				return false;
			}
		}
		else
		{
			if (empty($class) || empty($methodLangGet))
			{
				return false;
			}
		}

		$icons = self::getListCache();
		foreach ($icons as $cmd)
		{
			if ($botId)
			{
				if ($botId == $cmd['BOT_ID'] && $icon == $cmd['ICON_CODE'])
				{
					return $cmd['ID'];
				}
			}
			else if ($appId)
			{
				if ($appId == $cmd['APP_ID'] && $icon == $cmd['ICON_CODE'])
				{
					return $cmd['ID'];
				}
			}
			else if ($moduleId == $cmd['MODULE_ID'] && $icon == $cmd['ICON_CODE'])
			{
				return $cmd['ID'];
			}
		}

		$result = \Bitrix\Im\Model\TextareaIconTable::add(Array(
			'HASH' => $hash,
			'BOT_ID' => $botId,
			'MODULE_ID' => $moduleId,
			'ICON_CODE' => $icon,
			'ICON_FILE_ID' => $iconFileId,
			'CONTEXT' => ToLower($context),
			'HIDDEN' => $hidden,
			'IFRAME' => $iframe,
			'IFRAME_WIDTH' => $iframeWidth,
			'IFRAME_HEIGHT' => $iframeHeight,
			'JS' => $jscommand,
			'EXTRANET_SUPPORT' => $extranetSupport,
			'CLASS' => $class,
			'METHOD_LANG_GET' => $methodLangGet,
			'APP_ID' => $appId
		));

		if (!$result->isSuccess())
			return false;

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		$iconId = $result->getId();

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
					\Bitrix\Im\Model\TextareaIconLangTable::add(array(
						'ICON_ID' => $iconId,
						'LANGUAGE_ID' => strtolower($lang['LANGUAGE_ID']),
						'TITLE' => $lang['TITLE'],
						'DESCRIPTION' => $lang['DESCRIPTION'],
						'COPYRIGHT' => $lang['COPYRIGHT']
					));
				}
				catch(Exception $e)
				{
				}
			}
		}

		return $iconId;
	}

	public static function unRegister(array $icon)
	{
		$iconId = intval($icon['ICON_ID']);
		$moduleId = isset($icon['MODULE_ID'])? $icon['MODULE_ID']: '';
		$appId = isset($icon['APP_ID'])? $icon['APP_ID']: '';

		if (intval($iconId) <= 0)
			return false;

		if (!isset($icon['FORCE']) || $icon['FORCE'] == 'N')
		{
			$icons = self::getListCache();
			if (!isset($icons[$iconId]))
				return false;

			if (strlen($moduleId) > 0 && $icons[$iconId]['MODULE_ID'] != $moduleId)
				return false;

			if (strlen($appId) > 0 && $icons[$iconId]['APP_ID'] != $appId)
				return false;
		}

		\Bitrix\Im\Model\TextareaIconTable::delete($iconId);

		$orm = \Bitrix\Im\Model\TextareaIconLangTable::getList(Array(
			'filter' => Array('=ICON_ID' => $iconId)
		));
		while ($row = $orm->fetch())
		{
			\Bitrix\Im\Model\TextareaIconLangTable::delete($row['ID']);
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			\CPullStack::AddShared(Array(
				'module_id' => 'im',
				'command' => 'deleteTextareaIcon',
				'params' => Array(
					'iconId' => $iconId
				)
			));
		}

		return true;
	}

	public static function update(array $icon, array $updateFields)
	{
		$iconId = intval($icon['ICON_ID']);
		$userId = intval($icon['USER_ID']);
		$moduleId = isset($icon['MODULE_ID'])? $icon['MODULE_ID']: '';
		$appId = isset($icon['APP_ID'])? $icon['APP_ID']: '';

		if (intval($iconId) <= 0)
			return false;

		$commands = self::getListCache();
		if (!isset($commands[$iconId]))
			return false;

		if (strlen($moduleId) > 0 && $commands[$iconId]['MODULE_ID'] != $moduleId)
			return false;

		if (strlen($appId) > 0 && $commands[$iconId]['APP_ID'] != $appId)
			return false;

		if (isset($updateFields['LANG']) && $commands[$iconId]['MODULE_ID'] == 'rest')
		{
			$orm = \Bitrix\Im\Model\TextareaIconLangTable::getList(Array(
				'filter' => Array('=ICON_ID' => $iconId)
			));
			while ($row = $orm->fetch())
			{
				\Bitrix\Im\Model\TextareaIconLangTable::delete($row['ID']);
			}
			foreach ($updateFields['LANG'] as $lang)
			{
				if (!isset($lang['LANGUAGE_ID']) || empty($lang['LANGUAGE_ID']))
					continue;

				if (!isset($lang['TITLE']) && empty($lang['TITLE']))
					continue;

				try
				{
					\Bitrix\Im\Model\TextareaIconLangTable::add(array(
						'ICON_ID' => $iconId,
						'LANGUAGE_ID' => strtolower($lang['LANGUAGE_ID']),
						'TITLE' => $lang['TITLE'],
						'DESCRIPTION' => $lang['DESCRIPTION'],
						'COPYRIGHT' => $lang['COPYRIGHT']
					));
				}
				catch(Exception $e)
				{
				}
			}
		}

		$update = Array();
		if (isset($updateFields['CONTEXT']) && !empty($updateFields['CONTEXT']))
		{
			$update['CONTEXT'] = ToLower($updateFields['CONTEXT']);
		}
		if (isset($updateFields['HASH']) && !empty($updateFields['HASH']))
		{
			$update['HASH'] = $updateFields['HASH'];
		}
		if (isset($updateFields['HIDDEN']))
		{
			$update['HIDDEN'] = $updateFields['HIDDEN'] == 'Y'? 'Y': 'N';
		}
		if (isset($updateFields['IFRAME']) && !empty($updateFields['IFRAME']))
		{
			$check = parse_url($updateFields['IFRAME']);
			if (!isset($check['scheme']) && !isset($check['host']))
			{
				if (strpos($updateFields['IFRAME'], '/desktop_app/iframe/') !== 0)
				{
					return false;
				}
			}
			else if (!in_array($check['scheme'], Array('http', 'https')) || empty($check['host']))
			{
				return false;
			}
			$update['IFRAME'] = $updateFields['IFRAME'].(isset($check['query'])? '&': '?');
		}
		else if (isset($updateFields['JS']) && !empty($updateFields['JS']))
		{
			$update['JS'] = $updateFields['JS'];
		}
		if (isset($updateFields['IFRAME_WIDTH']))
		{
			$update['IFRAME_WIDTH'] = intval($updateFields['IFRAME_WIDTH']) > 250? intval($updateFields['IFRAME_WIDTH']): 350;
		}
		if (isset($updateFields['IFRAME_HEIGHT']))
		{
			$update['IFRAME_HEIGHT'] = intval($updateFields['IFRAME_HEIGHT']) > 50? intval($updateFields['IFRAME_HEIGHT']): 150;
		}
		if (isset($updateFields['ICON_FILE_ID']))
		{
			$update['ICON_FILE_ID'] = intval($updateFields['ICON_FILE_ID']);
		}
		if (isset($updateFields['CLASS']) && !empty($updateFields['CLASS']))
		{
			$update['CLASS'] = $updateFields['CLASS'];
		}
		if (isset($updateFields['METHOD_LANG_GET']))
		{
			$update['METHOD_LANG_GET'] = $updateFields['METHOD_LANG_GET'];
		}
		if (isset($updateFields['EXTRANET_SUPPORT']))
		{
			$update['EXTRANET_SUPPORT'] = $updateFields['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		}
		
		if (!empty($update))
		{
			\Bitrix\Im\Model\TextareaIconTable::update($iconId, $update);

			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->cleanDir(self::CACHE_PATH);
		}
		
		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			if ($update['HASH'] || $update['CONTEXT'] || $update['IFRAME'] || $update['JS']  || $update['IFRAME_WIDTH'] || $update['IFRAME_HEIGHT'])
			{
				\CPullStack::AddShared(Array(
					'module_id' => 'im',
					'command' => 'updateTextareaIcon',
					'params' => Array(
						'iconId' => $iconId,
						'userId' => $userId,
						'hash' => $update['HASH'],
						'context' => $update['CONTEXT'],
						'js' => $update['JS'],
						'iframe' => $update['IFRAME'],
						'iframeWidth' => $update['IFRAME_WIDTH'],
						'iframeHeight' => $update['IFRAME_HEIGHT'],
					)
				));
			}
			else
			{
				\CPullStack::AddShared(Array(
					'module_id' => 'im',
					'command' => 'deleteTextareaIcon',
					'params' => Array(
						'iconId' => $iconId
					)
				));
			}
			
			
		}

		return true;
	}

	public static function addToken(array $params)
	{
		$botId = intval($params['BOT_ID']);
		$userId = intval($params['USER_ID']);
		
		Bot\Token::add($botId, $params['DIALOG_ID']);
		Bot\Token::add($userId, $params['DIALOG_ID']);
		
		if (!self::isChat($params['DIALOG_ID']))
		{
			Bot\Token::add($botId, $params['USER_ID']);
		}

		return true;
	}

	public static function addMessage(array $access, array $messageFields)
	{
		$iconId = intval($access['ICON_ID']);
		$moduleId = isset($access['MODULE_ID'])? $access['MODULE_ID']: '';
		$appId = isset($access['APP_ID'])? $access['APP_ID']: '';

		if ($iconId <= 0)
			return false;

		$icons = self::getListCache();
		if (!isset($icons[$iconId]))
			return false;

		if (strlen($moduleId) > 0 && $icons[$iconId]['MODULE_ID'] != $moduleId)
			return false;

		if (strlen($appId) > 0 && $icons[$iconId]['APP_ID'] != $appId)
			return false;

		$botId = intval($icons[$iconId]['BOT_ID']);
		
		if (self::isChat($messageFields['DIALOG_ID']))
		{
			$relations = \CIMChat::GetRelationById(substr($messageFields['DIALOG_ID'], 4));
		}
		else
		{
			$userId = intval($messageFields['DIALOG_ID']);
			if (!$userId || $botId == $userId)
			{
				return false;
			}
			$relations = \CIMChat::GetPrivateRelation($botId, $userId);
		}
		
		if ($botId && !Bot\Token::isActive($botId, $messageFields['DIALOG_ID']))
		{
			return false;
		}
		
		$messageFields['ATTACH'] = $messageFields['ATTACH']? $messageFields['ATTACH']: null;
		$messageFields['KEYBOARD'] = $messageFields['KEYBOARD']? $messageFields['KEYBOARD']: null;

		$fromUserId = isset($messageFields['FROM_USER_ID'])? $messageFields['FROM_USER_ID']: $botId;
		
		if (self::isChat($messageFields['DIALOG_ID']))
		{
			$chatId = intval(substr($messageFields['DIALOG_ID'], 4));
			if ($chatId <= 0)
				return false;

			if (isset($relations[$fromUserId]) && $messageFields['SYSTEM'] != 'Y')
			{
				$ar = Array(
					"FROM_USER_ID" => $fromUserId,
					"TO_CHAT_ID" => $chatId,
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
					"FROM_USER_ID" => isset($relations[$fromUserId])? $fromUserId: 0,
					"TO_CHAT_ID" => $chatId,
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
					"SYSTEM" => 'Y',
				);
				if (isset($messageFields['MESSAGE']))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
				
				$ar['MESSAGE'] = "[B]".$icons[$iconId]['TITLE']."[/B]\n".$ar['MESSAGE'];
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
			$userId = intval($messageFields['DIALOG_ID']);
			\CModule::IncludeModule('imbot');
			
			if (isset($relations[$fromUserId]) && $messageFields['SYSTEM'] != 'Y')
			{
				$ar = Array(
					"FROM_USER_ID" => $fromUserId,
					"TO_USER_ID" => $userId,
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
					"FROM_USER_ID" => isset($relations[$fromUserId])? $fromUserId: 0,
					"TO_USER_ID" => $userId,
					"ATTACH" => $messageFields['ATTACH'],
					"KEYBOARD" => $messageFields['KEYBOARD'],
					"SYSTEM" => "Y",
				);
				if (isset($messageFields['MESSAGE']))
				{
					$ar['MESSAGE'] = $messageFields['MESSAGE'];
				}
				
				$ar['MESSAGE'] = "[B]".$icons[$iconId]['TITLE']."[/B]\n".$ar['MESSAGE'];
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
	
	public static function isChat($dialogId)
	{
		$isChat = false;
		if (is_string($dialogId) && substr($dialogId, 0, 4) == 'chat')
		{
			$isChat = true;
		}

		return $isChat;
	}
	
	public static function getListCache($lang = LANGUAGE_ID)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_v2_'.$lang, self::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$loadRestLang = false;
			$result = Array();
			$orm = \Bitrix\Im\Model\TextareaIconTable::getList();
			while ($row = $orm->fetch())
			{
				$row['ICON_ID'] = $row['ID'];
				
				if ($row['ICON_FILE_ID'])
				{
					$image = \CFile::ResizeImageGet(
						$row['ICON_FILE_ID'],
						array('width' => 108, 'height' => 66),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						false,
						false,
						true
					);
					$row['ICON_URL'] = empty($image['src'])? '': $image['src'];
				}
				else
				{
					$row['ICON_URL'] = '';
				}
				
				if (!empty($row['CLASS']) && !empty($row['METHOD_LANG_GET']))
				{
					if (\Bitrix\Main\Loader::includeModule($row['MODULE_ID']) && class_exists($row["CLASS"]) && method_exists($row["CLASS"], $row["METHOD_LANG_GET"]))
					{
						$localize = call_user_func_array(array($row["CLASS"], $row["METHOD_LANG_GET"]), Array($row['ICON_CODE'], $lang));
						if ($localize)
						{
							$row['TITLE'] = $localize['TITLE'];
							$row['DESCRIPTION'] = $localize['DESCRIPTION'];
							$row['COPYRIGHT'] = $localize['COPYRIGHT'];
						}
						else
						{
							$row['METHOD_LANG_GET'] = '';
						}
					}
					else
					{
						$row['METHOD_LANG_GET'] = '';
					}
				}
				else
				{
					$row['TITLE'] = '';
					$row['DESCRIPTION'] = '';
					$row['COPYRIGHT'] = '';
					
					if ($row['MODULE_ID'] == 'rest')
					{
						$loadRestLang = true;
					}
				}
				$result[$row['ICON_ID']] = $row;
			}

			if ($loadRestLang)
			{
				$langSet = Array();
				$orm = \Bitrix\Im\Model\TextareaIconLangTable::getList();
				while ($row = $orm->fetch())
				{
					if (!isset($result[$row['ICON_ID']]))
						continue;

					$langSet[$row['ICON_ID']][$row['LANGUAGE_ID']]['TITLE'] = $row['TITLE'];
					$langSet[$row['ICON_ID']][$row['LANGUAGE_ID']]['DESCRIPTION'] = $row['DESCRIPTION'];
					$langSet[$row['ICON_ID']][$row['LANGUAGE_ID']]['COPYRIGHT'] = $row['COPYRIGHT'];
				}

				$langAlter = \Bitrix\Im\Bot::getDefaultLanguage();
				foreach ($result as $iconId => $commandData)
				{
					if (isset($langSet[$iconId][$lang]))
					{
						$result[$iconId]['TITLE'] = $langSet[$iconId][$lang]['TITLE'];
						$result[$iconId]['DESCRIPTION'] = $langSet[$iconId][$lang]['DESCRIPTION'];
						$result[$iconId]['COPYRIGHT'] = $langSet[$iconId][$lang]['COPYRIGHT'];
					}
					else if (isset($langSet[$iconId][$langAlter]))
					{
						$result[$iconId]['TITLE'] = $langSet[$iconId][$langAlter]['TITLE'];
						$result[$iconId]['DESCRIPTION'] = $langSet[$iconId][$langAlter]['DESCRIPTION'];
						$result[$iconId]['COPYRIGHT'] = $langSet[$iconId][$langAlter]['COPYRIGHT'];
					}
					else if (isset($langSet[$iconId]))
					{
						$langSetCommand = array_values($langSet[$iconId]);
						$result[$iconId]['TITLE'] = $langSetCommand[0]['TITLE'];
						$result[$iconId]['DESCRIPTION'] = $langSetCommand[0]['DESCRIPTION'];
						$result[$iconId]['COPYRIGHT'] = $langSetCommand[0]['COPYRIGHT'];
					}
				}

				foreach ($result as $key => $value)
				{
					if (empty($value['TITLE']))
					{
						$row['METHOD_LANG_GET'] = '';
					}
				}
			}
			
			$cache->startDataCache();
			$cache->endDataCache($result);
		}


		return $result;
	}

	public static function getListForJs($lang = LANGUAGE_ID)
	{
		$icons = self::getListCache($lang);

		$userId = $GLOBALS['USER']? $GLOBALS['USER']->GetId(): 0;
		if ($userId && \Bitrix\Im\User::getInstance($userId)->isExtranet())
		{
			return Array();
		}
		
		$result = Array();
		foreach ($icons as $icon)
		{
			$botData = \Bitrix\Im\Bot::getCache($icon['BOT_ID']);
			$result[] = Array(
				'id' => $icon['ICON_ID'],
				'botId' => $icon['BOT_ID'],
				'botCode' => $botData['CODE'],
				'hash' => $icon['HASH'],
				'userHash' => self::getUserHash($userId),
				'code' => $icon['ICON_CODE'],
				'url' => $icon['ICON_URL'],
				'iframe' => $icon['IFRAME'],
				'iframeWidth' => $icon['IFRAME_WIDTH'],
				'iframeHeight' => $icon['IFRAME_HEIGHT'],
				'js' => $icon['JS'],
				'context' => ToLower($icon['CONTEXT']),
				'extranet' => $icon['EXTRANET_SUPPORT'] == 'Y',
				'hidden' => $icon['HIDDEN'] == 'Y',
				'title' => $icon['TITLE'],
				'description' => $icon['DESCRIPTION'],
				'copyright' => $icon['COPYRIGHT'],
			);
		}

		return $result;
	}
	
	public static function getUserHash($userId)
	{
		return md5($userId.\CMain::GetServerUniqID());
	}
	
	public static function clearCache()
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		return true;
	}
}