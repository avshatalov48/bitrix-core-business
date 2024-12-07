<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Im;

use Bitrix\ImBot\Bot\Giphy;
use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\DB\Exception;
Loc::loadMessages(__FILE__);

class App
{
	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/app/';

	public static function register(array $fields)
	{
		$moduleId = $fields['MODULE_ID'];
		if ($moduleId == '')
		{
			return false;
		}

		$iframe = '';
		$iframeWidth = 350;
		$iframeHeight = 250;
		$iframePopup = 'N';
		$jscommand = isset($fields['JS'])? $fields['JS']: '';

		if (isset($fields['IFRAME']) && $fields['IFRAME'])
		{
			$check = parse_url($fields['IFRAME']);
			if (!isset($check['scheme']) && !isset($check['host']))
			{
				if (mb_strpos($fields['IFRAME'], '/desktop_app/iframe/') !== 0)
				{
					return false;
				}
			}
			else if (!in_array($check['scheme'], Array('http', 'https')) || empty($check['host']))
			{
				return false;
			}
			$iframe = $fields['IFRAME'].(isset($check['query'])? '&': '?');
			if (isset($fields['IFRAME_WIDTH']))
			{
				$iframeWidth = intval($fields['IFRAME_WIDTH']) > 250? $fields['IFRAME_WIDTH']: 250;
			}
			if (isset($fields['IFRAME_HEIGHT']))
			{
				$iframeHeight = intval($fields['IFRAME_HEIGHT']) > 50? $fields['IFRAME_HEIGHT']: 50;
			}
			$iframePopup = isset($fields['IFRAME_POPUP']) && $fields['IFRAME_POPUP'] == 'Y'? 'Y': $iframePopup;
		}
		else if (!$jscommand)
		{
			return false;
		}

		$code = $fields['CODE'];
		if (!$code)
		{
			return false;
		}

		$iconFileId = intval($fields['ICON_ID']);

		$botId = isset($fields['BOT_ID'])? intval($fields['BOT_ID']): 0;
		$hash = isset($fields['HASH']) && !empty($fields['HASH'])? mb_substr($fields['HASH'], 0, 32) : md5($botId.$fields['CODE'].\CMain::GetServerUniqID());
		$context = isset($fields['CONTEXT'])? $fields['CONTEXT']: 'ALL';
		$registered = isset($fields['REGISTERED']) && $fields['REGISTERED'] == 'N'? 'N': 'Y';
		$hidden = isset($fields['HIDDEN']) && $fields['HIDDEN'] == 'Y'? 'Y': 'N';
		if ($botId > 0 && (!\Bitrix\Im\User::getInstance($botId)->isExists() || !\Bitrix\Im\User::getInstance($botId)->isBot()))
		{
			$botId = 0;
		}

		$extranetSupport = isset($fields['EXTRANET_SUPPORT']) && $fields['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		$livechatSupport = isset($fields['LIVECHAT_SUPPORT']) && $fields['LIVECHAT_SUPPORT'] == 'Y'? 'Y': 'N';

		/* vars for module install */
		$class = isset($fields['CLASS'])? $fields['CLASS']: '';
		$methodLangGet = isset($fields['METHOD_LANG_GET'])? $fields['METHOD_LANG_GET']: '';

		/* vars for rest install */
		$restAppId = isset($fields['APP_ID'])? $fields['APP_ID']: '';
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

		$apps = self::getListCache();
		foreach ($apps as $cmd)
		{
			if ($botId)
			{
				if ($botId == $cmd['BOT_ID'] && $code == $cmd['CODE'])
				{
					return $cmd['ID'];
				}
			}
			else if ($restAppId)
			{
				if ($restAppId == $cmd['APP_ID'] && $code == $cmd['CODE'])
				{
					return $cmd['ID'];
				}
			}
			else if ($moduleId == $cmd['MODULE_ID'] && $code == $cmd['CODE'])
			{
				return $cmd['ID'];
			}
		}

		$result = \Bitrix\Im\Model\AppTable::add(Array(
			'HASH' => $hash,
			'BOT_ID' => $botId,
			'MODULE_ID' => $moduleId,
			'CODE' => $code,
			'ICON_FILE_ID' => $iconFileId,
			'CONTEXT' => mb_strtolower($context),
			'HIDDEN' => $hidden,
			'REGISTERED' => $registered,
			'IFRAME' => $iframe,
			'IFRAME_WIDTH' => $iframeWidth,
			'IFRAME_HEIGHT' => $iframeHeight,
			'IFRAME_POPUP' => $iframePopup,
			'JS' => $jscommand,
			'EXTRANET_SUPPORT' => $extranetSupport,
			'LIVECHAT_SUPPORT' => $livechatSupport,
			'CLASS' => $class,
			'METHOD_LANG_GET' => $methodLangGet,
			'APP_ID' => $restAppId
		));

		if (!$result->isSuccess())
			return false;

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		$appId = $result->getId();

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
					\Bitrix\Im\Model\AppLangTable::add(array(
						'APP_ID' => $appId,
						'LANGUAGE_ID' => mb_strtolower($lang['LANGUAGE_ID']),
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

		return $appId;
	}

	public static function unRegister(array $app)
	{
		$appId = intval($app['ID']);
		$moduleId = isset($app['MODULE_ID'])? $app['MODULE_ID']: '';
		$restAppId = isset($app['APP_ID'])? $app['APP_ID']: '';

		if (intval($appId) <= 0)
			return false;

		if (!isset($app['FORCE']) || $app['FORCE'] == 'N')
		{
			$icons = self::getListCache();
			if (!isset($icons[$appId]))
				return false;

			if ($moduleId <> '' && $icons[$appId]['MODULE_ID'] != $moduleId)
				return false;

			if ($restAppId <> '' && $icons[$appId]['APP_ID'] != $restAppId)
				return false;
		}

		\Bitrix\Im\Model\AppTable::delete($appId);

		$orm = \Bitrix\Im\Model\AppLangTable::getList(Array(
			'filter' => Array('=APP_ID' => $appId)
		));
		while ($row = $orm->fetch())
		{
			\Bitrix\Im\Model\AppLangTable::delete($row['ID']);
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			\CPullStack::AddShared(Array(
				'module_id' => 'im',
				'command' => 'appDeleteIcon',
				'params' => Array(
					'iconId' => $appId
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public static function update(array $app, array $updateFields)
	{
		$appId = intval($app['ID']);
		$userId = intval($app['USER_ID']);
		$moduleId = isset($app['MODULE_ID'])? $app['MODULE_ID']: '';
		$restAppId = isset($app['APP_ID'])? $app['APP_ID']: '';

		if (intval($appId) <= 0)
			return false;

		$apps = self::getListCache();
		if (!isset($apps[$appId]))
			return false;

		if ($moduleId <> '' && $apps[$appId]['MODULE_ID'] != $moduleId)
			return false;

		if ($restAppId <> '' && $apps[$appId]['APP_ID'] != $restAppId)
			return false;

		if (isset($updateFields['LANG']) && $apps[$appId]['MODULE_ID'] == 'rest')
		{
			$orm = \Bitrix\Im\Model\AppLangTable::getList(Array(
				'filter' => Array('=APP_ID' => $appId)
			));
			while ($row = $orm->fetch())
			{
				\Bitrix\Im\Model\AppLangTable::delete($row['ID']);
			}
			foreach ($updateFields['LANG'] as $lang)
			{
				if (!isset($lang['LANGUAGE_ID']) || empty($lang['LANGUAGE_ID']))
					continue;

				if (!isset($lang['TITLE']) && empty($lang['TITLE']))
					continue;

				try
				{
					\Bitrix\Im\Model\AppLangTable::add(array(
						'APP_ID' => $appId,
						'LANGUAGE_ID' => mb_strtolower($lang['LANGUAGE_ID']),
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
			$update['CONTEXT'] = mb_strtolower($updateFields['CONTEXT']);
		}
		if (isset($updateFields['HASH']) && !empty($updateFields['HASH']))
		{
			$update['HASH'] = $updateFields['HASH'];
		}
		if (isset($updateFields['HIDDEN']))
		{
			$update['HIDDEN'] = $updateFields['HIDDEN'] == 'Y'? 'Y': 'N';
		}
		if (isset($updateFields['REGISTERED']))
		{
			$update['REGISTERED'] = $updateFields['REGISTERED'] == 'N'? 'N': 'Y';
		}
		if (isset($updateFields['IFRAME']) && !empty($updateFields['IFRAME']))
		{
			$check = parse_url($updateFields['IFRAME']);
			if (!isset($check['scheme']) && !isset($check['host']))
			{
				if (mb_strpos($updateFields['IFRAME'], '/desktop_app/iframe/') !== 0)
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
			$update['IFRAME_WIDTH'] = intval($updateFields['IFRAME_WIDTH']) > 250? intval($updateFields['IFRAME_WIDTH']): 250;
		}
		if (isset($updateFields['IFRAME_HEIGHT']))
		{
			$update['IFRAME_HEIGHT'] = intval($updateFields['IFRAME_HEIGHT']) > 50? intval($updateFields['IFRAME_HEIGHT']): 50;
		}
		if (isset($updateFields['IFRAME_POPUP']))
		{
			$update['IFRAME_POPUP'] = $updateFields['IFRAME_POPUP'] == 'Y'? 'Y': 'N';
		}
		if (isset($updateFields['ICON_ID']) && $updateFields['ICON_ID'])
		{
			$update['ICON_FILE_ID'] = intval($updateFields['ICON_ID']);
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
		if (isset($updateFields['LIVECHAT_SUPPORT']))
		{
			$update['LIVECHAT_SUPPORT'] = $updateFields['LIVECHAT_SUPPORT'] == 'Y'? 'Y': 'N';
		}

		if (!empty($update))
		{
			\Bitrix\Im\Model\AppTable::update($appId, $update);

			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->cleanDir(self::CACHE_PATH);
		}

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			if (
				$update['REGISTERED']
				|| $update['DOMAIN_HASH']
				|| $update['CONTEXT']
				|| $update['IFRAME']
				|| $update['JS']
				|| $update['IFRAME_WIDTH']
				|| $update['IFRAME_HEIGHT']
				|| $update['IFRAME_POPUP']
			)
			{
				\CPullStack::AddShared(Array(
					'module_id' => 'im',
					'command' => 'appUpdateIcon',
					'params' => Array(
						'iconId' => $appId,
						'userId' => $userId,
						'domainHash' => $update['HASH'],
						'context' => $update['CONTEXT'],
						'js' => $update['JS'],
						'iframe' => $update['IFRAME'],
						'iframeWidth' => $update['IFRAME_WIDTH'],
						'iframeHeight' => $update['IFRAME_HEIGHT'],
						'iframePopup' => $update['IFRAME_POPUP'],
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));
			}
			else if ($update['ICON_ID'])
			{
			}
			else
			{
				\CPullStack::AddShared(Array(
					'module_id' => 'im',
					'command' => 'appDeleteIcon',
					'params' => Array(
						'iconId' => $appId
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));
			}
		}

		return true;
	}

	public static function addToken(array $params)
	{
		$botId = intval($params['BOT_ID']);
		$userId = intval($params['USER_ID']);

		if (!$botId && !$userId || empty($params['DIALOG_ID']))
		{
			return false;
		}

		Bot\Token::add($botId, $params['DIALOG_ID']);
		Bot\Token::add($userId, $params['DIALOG_ID']);

		if (!Common::isChatId($params['DIALOG_ID']))
		{
			Bot\Token::add($botId, $params['USER_ID']);
		}

		return true;
	}

	public static function addMessage(array $app, array $messageFields)
	{
		$appId = intval($app['ID']);
		$moduleId = isset($app['MODULE_ID'])? $app['MODULE_ID']: '';
		$restAppId = isset($app['APP_ID'])? $app['APP_ID']: '';

		if ($appId <= 0)
			return false;

		$apps = self::getListCache();
		if (!isset($apps[$appId]))
			return false;

		if ($moduleId <> '' && $apps[$appId]['MODULE_ID'] != $moduleId)
			return false;

		if ($restAppId <> '' && $apps[$appId]['APP_ID'] != $restAppId)
			return false;

		$botId = intval($apps[$appId]['BOT_ID']);

		if (Common::isChatId($messageFields['DIALOG_ID']))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($messageFields['DIALOG_ID']);
			$relations = \CIMChat::GetRelationById($chatId, false, true, false);
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

		if (Common::isChatId($messageFields['DIALOG_ID']))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($messageFields['DIALOG_ID']);
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

				$ar['MESSAGE'] = "[B]".$apps[$appId]['TITLE']."[/B]\n".$ar['MESSAGE'];
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

				$ar['MESSAGE'] = "[B]".$apps[$appId]['TITLE']."[/B]\n".$ar['MESSAGE'];
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

	public static function getListCache($lang = LANGUAGE_ID)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_v3_'.$lang, self::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$loadRestLang = false;
			$result = Array();
			$orm = \Bitrix\Im\Model\AppTable::getList();
			while ($row = $orm->fetch())
			{
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
						$localize = call_user_func_array(array($row["CLASS"], $row["METHOD_LANG_GET"]), Array($row['CODE'], $lang));
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
				$result[$row['ID']] = $row;
			}

			if ($loadRestLang)
			{
				$langSet = Array();
				$orm = \Bitrix\Im\Model\AppLangTable::getList();
				while ($row = $orm->fetch())
				{
					if (!isset($result[$row['APP_ID']]))
						continue;

					$langSet[$row['APP_ID']][$row['LANGUAGE_ID']]['TITLE'] = $row['TITLE'];
					$langSet[$row['APP_ID']][$row['LANGUAGE_ID']]['DESCRIPTION'] = $row['DESCRIPTION'];
					$langSet[$row['APP_ID']][$row['LANGUAGE_ID']]['COPYRIGHT'] = $row['COPYRIGHT'];
				}

				$langAlter = \Bitrix\Im\Bot::getDefaultLanguage();
				foreach ($result as $appId => $commandData)
				{
					if (isset($langSet[$appId][$lang]))
					{
						$result[$appId]['TITLE'] = $langSet[$appId][$lang]['TITLE'];
						$result[$appId]['DESCRIPTION'] = $langSet[$appId][$lang]['DESCRIPTION'];
						$result[$appId]['COPYRIGHT'] = $langSet[$appId][$lang]['COPYRIGHT'];
					}
					else if (isset($langSet[$appId][$langAlter]))
					{
						$result[$appId]['TITLE'] = $langSet[$appId][$langAlter]['TITLE'];
						$result[$appId]['DESCRIPTION'] = $langSet[$appId][$langAlter]['DESCRIPTION'];
						$result[$appId]['COPYRIGHT'] = $langSet[$appId][$langAlter]['COPYRIGHT'];
					}
					else if (isset($langSet[$appId]))
					{
						$langSetCommand = array_values($langSet[$appId]);
						$result[$appId]['TITLE'] = $langSetCommand[0]['TITLE'];
						$result[$appId]['DESCRIPTION'] = $langSetCommand[0]['DESCRIPTION'];
						$result[$appId]['COPYRIGHT'] = $langSetCommand[0]['COPYRIGHT'];
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

		return self::filterList($result);
	}

	protected static function filterList(array $list): array
	{
		if (!Main\Loader::includeModule('imbot') || !Giphy::getBotId() || Giphy::isAvailable())
		{
			return $list;
		}

		foreach ($list as $appId => $app)
		{
			if ((int)$app['BOT_ID'] === Giphy::getBotId())
			{
				$list[$appId]['HIDDEN'] = 'Y';
			}
		}

		return $list;
	}

	public static function getListForJs($lang = LANGUAGE_ID)
	{
		$apps = self::getListCache($lang);

		$userId = $GLOBALS['USER']? $GLOBALS['USER']->GetId(): 0;
		$isExtranet = $userId && \Bitrix\Im\User::getInstance($userId)->isExtranet();
		$isConnector = $userId && \Bitrix\Im\User::getInstance($userId)->isConnector();

		$result = Array();
		foreach ($apps as $app)
		{
			if ($isConnector && $app['LIVECHAT_SUPPORT'] != 'Y')
				continue;
			else if ($isExtranet && $app['EXTRANET_SUPPORT'] != 'Y')
				continue;

			$botData = \Bitrix\Im\Bot::getCache($app['BOT_ID']);
			$result[] = Array(
				'id' => $app['ID'],
				'botId' => $app['BOT_ID'],
				'botCode' => htmlspecialcharsbx($botData['CODE'] ?? ''),
				'domainHash' => self::getDomainHash($app['HASH']),
				'userHash' => self::getUserHash($userId, $app['HASH']),
				'code' => htmlspecialcharsbx($app['CODE']),
				'url' => $app['ICON_URL'],
				'iframe' => $app['IFRAME'],
				'iframeWidth' => $app['IFRAME_WIDTH'],
				'iframeHeight' => $app['IFRAME_HEIGHT'],
				'iframePopup' => $app['IFRAME_POPUP'] == 'Y',
				'js' => $app['JS'],
				'context' => mb_strtolower($app['CONTEXT']),
				'hidden' => $app['HIDDEN'] == 'Y',
				'title' => $app['TITLE'],
				'description' => $app['DESCRIPTION'],
				'copyright' => $app['COPYRIGHT'],
			);
		}

		return $result;
	}

	public static function getUserHash($userId, $hash = 'register')
	{
		if ($hash == 'register')
			$result = md5($userId.\CMain::GetServerUniqID());
		else
			$result = md5($userId.$hash);

		return $result;
	}

	public static function getDomainHash($hash)
	{
		$result = md5($_SERVER['SERVER_NAME'].$hash);

		return $result;
	}

	public static function clearCache()
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH);

		return true;
	}
}