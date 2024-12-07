<?php

namespace Bitrix\Mail;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Main\Localization\Loc::loadMessages(__FILE__);

class User
{

	/**
	 * Creates mail user
	 *
	 * @param array $fields User fields.
	 * @return int|false
	 */
	public static function create($fields)
	{
		$user = new \CUser;

		$userFields = array(
			'LOGIN'            => $fields["EMAIL"],
			'EMAIL'            => $fields["EMAIL"],
			'NAME'             => (!empty($fields["NAME"]) ? $fields["NAME"] : ''),
			'LAST_NAME'        => (!empty($fields["LAST_NAME"]) ? $fields["LAST_NAME"] : ''),
			'PERSONAL_PHOTO'   => (!empty($fields["PERSONAL_PHOTO_ID"]) ? \CFile::makeFileArray($fields['PERSONAL_PHOTO_ID']) : false),
			'EXTERNAL_AUTH_ID' => 'email',
		);

		if (Main\ModuleManager::isModuleInstalled('intranet'))
		{
			$userFields['UF_DEPARTMENT'] = array();
		}

		if (
			isset($fields['UF'])
			&& is_array($fields['UF'])
		)
		{
			foreach($fields['UF'] as $key => $value)
			{
				if (!empty($value))
				{
					$userFields[$key] = $value;
				}
			}
		}

		$mailGroup = self::getMailUserGroup();
		if (!empty($mailGroup))
		{
			$userFields["GROUP_ID"] = $mailGroup;
		}

		$result = $user->add($userFields);

		return $result;
	}

	/**
	 * Runs user login
	 *
	 * @return void
	 */
	public static function login()
	{
		$eventManager = Main\EventManager::getInstance();
		$handler = $eventManager->addEventHandlerCompatible('main', 'OnUserLoginExternal', array('\Bitrix\Mail\User', 'onLoginExternal'));

		global $USER;
		$USER->login('', '', 'Y');

		$eventManager->removeEventHandler('main', 'OnUserLoginExternal', $handler);
	}

	/**
	 * Returns mail user ID
	 *
	 * @param array &$params Auth params.
	 * @return int|false
	 */
	public static function onLoginExternal(&$params)
	{
		$context = Main\Application::getInstance()->getContext();
		$request = $context->getRequest();

		if ($token = $request->get('token') ?: $request->getCookie('MAIL_AUTH_TOKEN'))
		{
			$userRelation = UserRelationsTable::getList(array(
				'select' => array('USER_ID'),
				'filter' => array(
					'=TOKEN'                 => $token,
					'=USER.EXTERNAL_AUTH_ID' => 'email',
					'USER.ACTIVE'            => 'Y'
				)
			))->fetch();

			if ($userRelation)
			{
				$context->getResponse()->addCookie(new Main\Web\Cookie('MAIL_AUTH_TOKEN', $token));

				return $userRelation['USER_ID'];
			}
		}

		return false;
	}

	/**
	 * Returns User-Entity unique email and entry point URL
	 *
	 * @param string $siteId Site ID.
	 * @param int $userId User ID.
	 * @param string $entityType Entity type ID.
	 * @param int $entityId Entity ID.
	 * @param string $entityLink Entity URL.
	 * @param string $backurl Back URL.
	 * @return array|false
	 */
	public static function getReplyTo($siteId, $userId, $entityType, $entityId, $entityLink = null, $backurl = null)
	{
		$filter = array(
			'=SITE_ID'     => $siteId,
			'=USER_ID'     => $userId,
			'=ENTITY_TYPE' => $entityType,
			'=ENTITY_ID'   => $entityId
		);
		$userRelation = UserRelationsTable::getList(array('filter' => $filter))->fetch();

		if (empty($userRelation))
		{
			$filter['=SITE_ID'] = null;
			$userRelation = UserRelationsTable::getList(array('filter' => $filter))->fetch();
		}

		if (empty($userRelation))
		{
			if (empty($entityLink))
				return false;

			$userRelation = array(
				'SITE_ID'     => $siteId,
				'TOKEN'       => base_convert(md5(time().Main\Security\Random::getBytes(6)), 16, 36),
				'USER_ID'     => $userId,
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID'   => $entityId,
				'ENTITY_LINK' => $entityLink,
				'BACKURL'     => $backurl
			);

			if (!UserRelationsTable::add($userRelation)->isSuccess())
				return false;
		}

		$site    = Main\SiteTable::getByPrimary($siteId)->fetch();
		$context = Main\Application::getInstance()->getContext();

		$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
		$domain = $site['SERVER_NAME'] ?: \COption::getOptionString('main', 'server_name', '');

		if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domain, $matches))
		{
			$domain = $matches['domain'];
			$port   = $matches['port'];
		}
		else
		{
			$port = $context->getServer()->getServerPort();
		}

		$port = in_array($port, array(80, 443)) ? '' : ':'.$port;
		$path = ltrim(trim($site['DIR'], '/') . '/pub/entry.php', '/');

		$replyTo = sprintf('rpl%s@%s', $userRelation['TOKEN'], $domain);
		$backUrl = sprintf('%s://%s%s/%s#%s', $scheme, $domain, $port, $path, $userRelation['TOKEN']);

		return array($replyTo, $backUrl);
	}

	/**
	 * Returns Site-User-Entity unique email
	 *
	 * @param string $siteId Site ID.
	 * @param int $userId User ID.
	 * @param string $entityType Entity type ID.
	 * @return array|false
	 */
	public static function getForwardTo($siteId, $userId, $entityType)
	{
		$cache = new \CPHPCache();

		$cacheKey = sprintf('%s_%s', $userId, $entityType);
		$cacheDir = sprintf('/mail/user/forward/%s', bin2hex($siteId));

		if ($cache->initCache(365*24*3600, $cacheKey, $cacheDir))
		{
			$forwardTo = $cache->getVars();
		}
		else
		{
			$userRelation = UserRelationsTable::getList(array(
				'filter' => array(
					'=SITE_ID'     => $siteId,
					'=USER_ID'     => $userId,
					'=ENTITY_TYPE' => $entityType,
					'=ENTITY_ID'   => null
				)
			))->fetch();

			if (empty($userRelation))
			{
				$userRelation = array(
					'SITE_ID'     => $siteId,
					'TOKEN'       => base_convert(md5(time().Main\Security\Random::getBytes(6)), 16, 36),
					'USER_ID'     => $userId,
					'ENTITY_TYPE' => $entityType
				);

				if (!UserRelationsTable::add($userRelation)->isSuccess())
					return false;

				// for dav addressbook modification label
				$user = new \CUser;
				$user->update($userId, array());
			}

			$site   = Main\SiteTable::getByPrimary($siteId)->fetch();
			$domain = $site['SERVER_NAME'] ?: \COption::getOptionString('main', 'server_name', '');

			if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domain, $matches))
				$domain = $matches['domain'];

			$forwardTo = sprintf('fwd%s@%s', $userRelation['TOKEN'], $domain);

			$cache->startDataCache();
			$cache->endDataCache($forwardTo);
		}

		return array($forwardTo);
	}

	public static function parseEmailRecipient($to)
	{
		if (!preg_match('/^(?<type>rpl|fwd)(?<token>[a-z0-9]+)@(?<domain>.+)/i', $to, $matches))
		{
			return false;
		}
		return $matches;
	}

	public static function getUserRelation($token)
	{
		$userRelation = UserRelationsTable::getList(array(
			'filter' => array(
				'=TOKEN'      => $token,
				'USER.ACTIVE' => 'Y'
			)
		))->fetch();

		if (!$userRelation)
		{
			return false;
		}

		return $userRelation;
	}

	/**
	 * Sends email related events
	 *
	 * @param string $to Recipient email.
	 * @param array $message Message.
	 * @param string &$error Error.
	 * @return bool
	 */
	public static function onEmailReceived($to, $message, $recipient, $userRelation, &$error)
	{
		$type  = $recipient['type'];
		$token = $recipient['token'];

		$message['secret'] = $token;

		switch ($type)
		{
			case 'rpl':
				$content = Message::parseReply($message);
				break;
			case 'fwd':
				$content = Message::parseForward($message);
				break;
		}

		if (empty($content) && empty($message['files']))
		{
			$error = sprintf('Empty message (rcpt: %s)', $to);
			return false;
		}

		$attachments = array_filter(
			array_combine(
				array_column((array) $message['files'], 'name'),
				array_column((array) $message['files'], 'tmp_name')
			)
		);

		$addResult = User\MessageTable::add(array(
			'TYPE' => $type,
			'SITE_ID' => $userRelation['SITE_ID'],
			'ENTITY_TYPE' => $userRelation['ENTITY_TYPE'],
			'ENTITY_ID' => $userRelation['ENTITY_ID'],
			'USER_ID' => $userRelation['USER_ID'],
			'SUBJECT' => $message['subject'],
			'CONTENT' => $content,
			'ATTACHMENTS' => serialize($attachments),
		));

		if ($addResult->isSuccess())
		{
			\CAgent::addAgent(
				"\\Bitrix\\Mail\\User::sendEventAgent(".$addResult->getId().");",
				"mail", //module
				"N", //period
				10 //interval
			);
			return true;
		}

		return false;
	}

	/**
	 * Agent method, retrieves stored user message and sends an event
	 */
	public static function sendEventAgent($messageId = 0, $cnt = 0)
	{
		$messageId = intval($messageId);
		if ($messageId <= 0)
		{
			return;
		}

		$res = User\MessageTable::getList(array(
			'filter' => array(
				'=ID' => $messageId
			)
		));
		if ($messageFields = $res->fetch())
		{
			if (intval($cnt) > 10)
			{
				if (Main\Loader::includeModule('im'))
				{
					$title = trim($messageFields['SUBJECT']);
					if ($title == '')
					{
						$title = trim($messageFields['CONTENT']);
						$title = preg_replace("/\[ATTACHMENT\s*=\s*[^\]]*\]/isu", "", $title);

						$CBXSanitizer = new \CBXSanitizer;
						$CBXSanitizer->delAllTags();
						$title = $CBXSanitizer->sanitizeHtml($title);
					}

					\CIMNotify::add(array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
						"NOTIFY_MODULE" => "mail",
						"NOTIFY_EVENT" => "user_message_failed",
						"TO_USER_ID" => $messageFields['USER_ID'],
						"NOTIFY_MESSAGE" => Loc::getMessage("MAIL_USER_MESSAGE_FAILED", array(
							"#TITLE#" => $title
						))
					));
				}
				User\MessageTable::delete($messageId);
				return;
			}

			switch ($messageFields['TYPE'])
			{
				case 'rpl':
					$eventId = sprintf('onReplyReceived%s', $messageFields['ENTITY_TYPE']);
					break;
				case 'fwd':
					$eventId = sprintf('onForwardReceived%s', $messageFields['ENTITY_TYPE']);
					break;
			}

			if (!empty($eventId))
			{
				$attachments = array();
				if (!empty($messageFields['ATTACHMENTS']))
				{
					$tmpAttachments = unserialize($messageFields['ATTACHMENTS'], ['allowed_classes' => false]);
					if (is_array($tmpAttachments))
					{
						foreach($tmpAttachments as $key => $uploadFile)
						{
							$file = \CFile::makeFileArray($uploadFile);
							if (
								is_array($file)
								&& !empty($file)
							)
							{
								$file['name'] = $key;
								$attachments[$key] = $file;
							}
						}
					}
				}

				$event = new Main\Event(
					'mail', $eventId,
					array(
						'site_id'     => $messageFields['SITE_ID'],
						'entity_id'   => $messageFields['ENTITY_ID'],
						'from'        => $messageFields['USER_ID'],
						'subject'     => $messageFields['SUBJECT'],
						'content'     => $messageFields['CONTENT'],
						'attachments' => $attachments
					)
				);
				$event->send();

				foreach ($event->getResults() as $eventResult)
				{
					if ($eventResult->getType() == \Bitrix\Main\EventResult::ERROR)
					{
						$cnt++;

						global $pPERIOD;
						$pPERIOD = 10 + (60 * $cnt);
						return "\\Bitrix\\Mail\\User::sendEventAgent(".$messageId.", ".$cnt.");";
					}
				}

				User\MessageTable::delete($messageId);
			}
		}

		return;
	}

	/**
	 * Returns email users group
	 *
	 * @return array
	 */
	public static function getMailUserGroup()
	{
		$res = array();
		$mailInvitedGroup = Main\Config\Option::get("mail", "mail_invited_group", false);
		if ($mailInvitedGroup)
		{
			$res[] = intval($mailInvitedGroup);
		}
		return $res;
	}

	public static function getDefaultEmailFrom($serverName = false)
	{
		if (Main\ModuleManager::isModuleInstalled('bitrix24') && defined("BX24_HOST_NAME"))
		{
			if(preg_match("/\\.bitrix24\\.([a-z]+|com\\.br)$/i", BX24_HOST_NAME))
			{
				$domain = BX24_HOST_NAME;
			}
			else
			{
				$domain = str_replace(".", "-", BX24_HOST_NAME).".bitrix24.com";
			}

			$defaultEmailFrom = "info@".$domain;
		}
		else
		{
			$defaultEmailFrom = Main\Config\Option::get('main', 'email_from', '');
			if ($defaultEmailFrom == '')
			{
				$defaultEmailFrom = "info@".($serverName ?: Main\Config\Option::get('main', 'server_name', $GLOBALS["SERVER_NAME"]));
			}
		}

		return $defaultEmailFrom;
	}

	public static function getUserData($userList, $nameTemplate)
	{
		$result = array();

		if (
			!is_array($userList)
			|| empty($userList)
		)
		{
			return $result;
		}

		$filter = array(
			"ID" => $userList,
			"=ACTIVE" => "Y",
			"=EXTERNAL_AUTH_ID" => 'email'
		);

		if (
			\IsModuleInstalled('intranet')
			|| Main\Config\Option::get("main", "new_user_registration_email_confirmation", "N") == "Y"
		)
		{
			$filter["CONFIRM_CODE"] = false;
		}

		$res = \Bitrix\Main\UserTable::getList(array(
			'order' => array(),
			'filter' => $filter,
			'select' => array("ID", "EMAIL", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN")
		));

		while ($user = $res->fetch())
		{
			$result[$user["ID"]] = array(
				"NAME_FORMATTED" => (
					!empty($user["NAME"])
					|| !empty($user["LAST_NAME"])
						? \CUser::formatName($nameTemplate, $user)
						: ''
				),
				"EMAIL" => $user["EMAIL"]
			);
		}

		return $result;
	}

	public static function handleSiteUpdate($fields)
	{
		if (array_key_exists('SERVER_NAME', $fields))
		{
			static::clearTokensCache();
		}
	}

	public static function handleServerNameUpdate()
	{
		static::clearTokensCache();
	}

	public static function clearTokensCache()
	{
		$cache = new \CPHPCache();
		$cache->cleanDir('/mail/user/forward');
	}

}
