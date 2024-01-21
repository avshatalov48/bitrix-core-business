<?php

namespace Bitrix\Main\Mail;

use Bitrix\Mail\Internals\UserSignatureTable;
use Bitrix\Main;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Internal\SenderTable;
use Bitrix\Main\Event;

class Sender
{
	public const MAIN_SENDER_SMTP_LIMIT_DECREASE = 'MainSenderSmtpLimitDecrease';

	public static function add(array $fields)
	{
		if (empty($fields['OPTIONS']) || !is_array($fields['OPTIONS']))
		{
			$fields['OPTIONS'] = array();
		}

		self::checkEmail($fields, $error, $errors);
		if ($error || $errors)
		{
			return array('error' => $error, 'errors' => $errors);
		}

		if (empty($fields['IS_CONFIRMED']))
		{
			$fields['OPTIONS']['confirm_code'] = \Bitrix\Main\Security\Random::getStringByCharsets(5, '0123456789abcdefghjklmnpqrstuvwxyz');
			$fields['OPTIONS']['confirm_time'] = time();
		}

		$senderId = 0;
		$result = Internal\SenderTable::add($fields);
		if ($result->isSuccess())
		{
			$senderId = $result->getId();
		}

		if (empty($fields['IS_CONFIRMED']))
		{
			$mailEventFields = array(
				'DEFAULT_EMAIL_FROM' => $fields['EMAIL'],
				'EMAIL_TO' => $fields['EMAIL'],
				'MESSAGE_SUBJECT' => getMessage('MAIN_MAIL_CONFIRM_MESSAGE_SUBJECT'),
				'CONFIRM_CODE' => mb_strtoupper($fields['OPTIONS']['confirm_code']),
			);

			if (!empty($smtpConfig))
			{
				\Bitrix\Main\EventManager::getInstance()->addEventHandlerCompatible(
					'main',
					'OnBeforeEventSend',
					function (&$eventFields, &$message, $context) use (&$smtpConfig)
					{
						$context->setSmtp($smtpConfig);
					}
				);
			}

			\CEvent::sendImmediate('MAIN_MAIL_CONFIRM_CODE', SITE_ID, $mailEventFields);
		}
		else
		{
			if (isset($fields['OPTIONS']['__replaces']) && $fields['OPTIONS']['__replaces'] > 0)
			{
				Internal\SenderTable::delete(
					(int) $fields['OPTIONS']['__replaces']
				);
			}
		}

		return ['senderId' => $senderId, 'confirmed' => !empty($fields['IS_CONFIRMED'])];
	}

	/**
	 * Check smtp connection
	 * @param $fields
	 * @param null $error
	 * @param Main\ErrorCollection|null $errors
	 */
	public static function checkEmail(&$fields, &$error = null, Main\ErrorCollection &$errors = null)
	{

		if (empty($fields['IS_CONFIRMED']) && !empty($fields['OPTIONS']['smtp']))
		{
			$smtpConfig = $fields['OPTIONS']['smtp'];
			$smtpConfig = new Smtp\Config(array(
				'from' => $fields['EMAIL'],
				'host' => $smtpConfig['server'],
				'port' => $smtpConfig['port'],
				'protocol' => $smtpConfig['protocol'],
				'login' => $smtpConfig['login'],
				'password' => $smtpConfig['password'],
				'isOauth' => $smtpConfig['isOauth'] ?? false,
			));

			if ($smtpConfig->canCheck())
			{
				if ($smtpConfig->check($error, $errors))
				{
					$fields['IS_CONFIRMED'] = true;
				}
			}
		}
	}

	public static function confirm($ids)
	{
		if (!empty($ids))
		{
			$res = Internal\SenderTable::getList(array(
				'filter' => array(
					'@ID' => (array) $ids,
				),
			));

			while ($item = $res->fetch())
			{
				Internal\SenderTable::update(
					(int) $item['ID'],
					array(
						'IS_CONFIRMED' => true,
					)
				);

				if (isset($item['OPTIONS']['__replaces']) && $item['OPTIONS']['__replaces'] > 0)
				{
					Internal\SenderTable::delete(
						(int) $item['OPTIONS']['__replaces']
					);
				}
			}
		}
	}

	public static function delete($ids)
	{
		$userId = CurrentUser::get()->getId();

		if(!is_array($ids))
		{
			$ids = [$ids];
		}
		if(empty($ids))
		{
			return;
		}
		$smtpConfigs = [];

		$senders = SenderTable::getList([
			'order' => [
				'ID' => 'desc',
			],
			'filter' => [
				'=USER_ID' => $userId,
				'@ID' => $ids,
				'IS_CONFIRMED' => true]
			]
		)->fetchAll();

		$userFormattedName = CurrentUser::get()->getFormattedName();
		foreach ($senders as $sender)
		{
			if (Loader::includeModule('mail') && $userId)
			{
				$senderName = sprintf(
					'%s <%s>',
					empty($sender['NAME']) ? $userFormattedName : $sender['NAME'],
					$sender['EMAIL'],
				);

				$signatures = UserSignatureTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=USER_ID' => $userId,
						'SENDER' => $senderName
					],
				])->fetchAll();

				foreach ($signatures as $signature)
				{
					UserSignatureTable::delete($signature['ID']);
				}
			}

			if(!empty($sender['OPTIONS']['smtp']['server']) && empty($sender['OPTIONS']['smtp']['encrypted']) && !isset($smtpConfigs[$sender['EMAIL']]))
			{
				$smtpConfigs[$sender['EMAIL']] = $sender['OPTIONS']['smtp'];
			}
		}
		if(!empty($smtpConfigs))
		{
			$senders = SenderTable::getList([
				'order' => [
					'ID' => 'desc',
				],
				'filter' => [
					'@EMAIL' => array_keys($smtpConfigs),
					'!ID' => $ids
				]
			])->fetchAll();
			foreach($senders as $sender)
			{
				if(isset($smtpConfigs[$sender['EMAIL']]))
				{
					$options = $sender['OPTIONS'];
					$options['smtp'] = $smtpConfigs[$sender['EMAIL']];
					$result = SenderTable::update($sender['ID'], [
						'OPTIONS' => $options,
					]);
					if($result->isSuccess())
					{
						unset($smtpConfigs[$sender['EMAIL']]);
						static::clearCustomSmtpCache($sender['EMAIL']);
					}
					if(empty($smtpConfigs))
					{
						break;
					}
				}
			}
		}
		foreach ((array) $ids as $id)
		{
			Internal\SenderTable::delete(
				(int) $id
			);
		}
	}

	public static function clearCustomSmtpCache($email)
	{
		$cache = new \CPHPCache();
		$cache->clean($email, '/main/mail/smtp');
	}

	public static function getCustomSmtp($email)
	{
		static $smtp = array();

		if (!isset($smtp[$email]))
		{
			$config = false;

			$cache = new \CPHPCache();

			if ($cache->initCache(30*24*3600, $email, '/main/mail/smtp'))
			{
				$config = $cache->getVars();
			}
			else
			{
				$res = Internal\SenderTable::getList(array(
					'filter' => array(
						'IS_CONFIRMED' => true,
						'=EMAIL' => $email,
					),
					'order' => array(
						'ID' => 'DESC',
					),
				));
				while ($item = $res->fetch())
				{
					if (!empty($item['OPTIONS']['smtp']['server']) && empty($item['OPTIONS']['smtp']['encrypted']))
					{
						$config = $item['OPTIONS']['smtp'];
						break;
					}
				}

				$cache->startDataCache();
				$cache->endDataCache($config);
			}

			if ($config)
			{
				$config = new Smtp\Config(array(
					'from' => $email,
					'host' => $config['server'],
					'port' => $config['port'],
					'protocol' => $config['protocol'],
					'login' => $config['login'],
					'password' => $config['password'],
					'isOauth' => $config['isOauth'],
				));
				if ($config->getIsOauth() && \CModule::includeModule('mail'))
				{
					$expireGapSeconds = self::getOAuthTokenExpireGapSeconds();
					$token = \Bitrix\Mail\Helper\OAuth::getTokenByMeta($config->getPassword(), $expireGapSeconds);
					$config->setPassword($token);
				}
			}

			$smtp[$email] = $config;
		}

		return $smtp[$email];
	}

	/**
	 * get sending limit by email, returns null if no limit.
	 * @param $email
	 * @return int|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getEmailLimit($email): ?int
	{
		$address = new \Bitrix\Main\Mail\Address($email);

		if (!$address->validate())
		{
			return null;
		}

		$email = $address->getEmail();
		static $mailLimit = array();

		if (!isset($mailLimit[$email]))
		{
			$cache = new \CPHPCache();

			if ($cache->initCache(3600, $email, '/main/mail/limit'))
			{
				$mailLimit[$email]  = $cache->getVars();
			}
			else
			{
				$res = Internal\SenderTable::getList(array(
					'filter' => array(
						'IS_CONFIRMED' => true,
						'=EMAIL' => $email,
					),
					'order' => array(
						'ID' => 'DESC',
					),
				));
				$limit = null;
				while ($item = $res->fetch())
				{
					if ($item['OPTIONS']['smtp']['limit'] !== null)
					{
						$limit = (int)$item['OPTIONS']['smtp']['limit'];
						break;
					}
				}

				$mailLimit[$email] = $limit;

				$cache->startDataCache();
				$cache->endDataCache($mailLimit[$email]);
			}
		}

		return $mailLimit[$email] < 0 ? 0 : $mailLimit[$email];
	}

	/**
	 * Set sender limit by email. Finding all senders with same email and set up limit from option
	 * Returns true if change some email limit.
	 * Returns false if has no changes.
	 * @param string $email
	 * @param int $limit
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function setEmailLimit(string $email, int $limit, bool $quite = true): bool
	{
		$address = new \Bitrix\Main\Mail\Address($email);

		if (!$address->validate())
		{
			return false;
		}

		$email = $address->getEmail();

		$cache = new \CPHPCache();
		$cache->clean($email, '/main/mail/limit');

		$res = Internal\SenderTable::getList(array(
			'filter' => array(
				'IS_CONFIRMED' => true,
				'=EMAIL' => $email,
			),
			'order' => array(
				'ID' => 'DESC',
			),
		));

		if ($limit < 0)
		{
			$limit = 0;
		}

		$hasChanges = false;
		while ($item = $res->fetch())
		{
			$oldLimit = (int)($item['OPTIONS']['smtp']['limit'] ?? 0);
			if ($item['OPTIONS']['smtp'] && $limit !== $oldLimit)
			{
				$item['OPTIONS']['smtp']['limit'] = $limit;
				$updateResult = Internal\SenderTable::update($item['ID'], ['OPTIONS' => $item['OPTIONS']]);
				$hasChanges = true;
				if (!$quite && ($limit < $oldLimit || $oldLimit <= 0) && $updateResult->isSuccess())
				{
					$event = new Event('main', self::MAIN_SENDER_SMTP_LIMIT_DECREASE, ['EMAIL'=>$email]);
					$event->send();
				}
			}
		}

		return $hasChanges;
	}

	/**
	 * Remove limit from all connected senders.
	 * @param string $email
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function removeEmailLimit(string $email): bool
	{
		$address = new \Bitrix\Main\Mail\Address($email);

		if (!$address->validate())
		{
			return false;
		}

		$email = $address->getEmail();
		$cache = new \CPHPCache();
		$cache->clean($email, '/main/mail/limit');

		$res = Internal\SenderTable::getList(array(
			'filter' => array(
				'IS_CONFIRMED' => true,
				'=EMAIL' => $email,
			),
			'order' => array(
				'ID' => 'DESC',
			),
		));

		while ($item = $res->fetch())
		{
			if (isset($item['OPTIONS']['smtp']['limit']))
			{
				unset($item['OPTIONS']['smtp']['limit']);
				Internal\SenderTable::update($item['ID'], ['OPTIONS' => $item['OPTIONS']]);
			}
		}

		return true;
	}

	public static function applyCustomSmtp($event)
	{
		$headers = $event->getParameter('arguments')->additional_headers;
		$context = $event->getParameter('arguments')->context;

		if (empty($context) || !($context instanceof Context))
		{
			return;
		}

		if ($context->getSmtp() && $context->getSmtp()->getHost())
		{
			return;
		}

		if (preg_match('/X-Bitrix-Mail-SMTP-Host:/i', $headers))
		{
			return;
		}

		$eol = Mail::getMailEol();
		$eolh = preg_replace('/([a-f0-9]{2})/i', '\x\1', bin2hex($eol));

		if (preg_match(sprintf('/(^|%1$s)From:(.+?)(%1$s([^\s]|$)|$)/is', $eolh), $headers, $matches))
		{
			$address = new Address(preg_replace(sprintf('/%s\s+/', $eolh), '', $matches[2]));
			if ($address->validate())
			{
				if ($customSmtp = static::getCustomSmtp($address->getEmail()))
				{
					$context->setSmtp($customSmtp);
				}
			}
		}
	}

	public static function prepareUserMailboxes($userId = null)
	{
		global $USER;

		static $mailboxes = array();

		if (!($userId > 0))
		{
			if (is_object($USER) && $USER->isAuthorized())
			{
				$userId = $USER->getId();
			}
		}

		if (!($userId > 0))
		{
			return array();
		}

		if (array_key_exists($userId, $mailboxes))
		{
			return $mailboxes[$userId];
		}

		$mailboxes[$userId] = array();

		if (is_object($USER) && $USER->isAuthorized() && $USER->getId() == $userId)
		{
			$userData = array(
				'ID' => $USER->getId(),
				'TITLE' => $USER->getParam("TITLE"),
				'NAME' => $USER->getFirstName(),
				'SECOND_NAME' => $USER->getSecondName(),
				'LAST_NAME' => $USER->getLastName(),
				'LOGIN' => $USER->getLogin(),
				'EMAIL' => $USER->getEmail(),
			);

			$isAdmin = in_array(1, $USER->getUserGroupArray());
		}
		else
		{
			$userData = Main\UserTable::getList(array(
				'select' => array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN', 'EMAIL'),
				'filter' => array('=ID' => $userId),
			))->fetch();

			$isAdmin = in_array(1, \CUser::getUserGroup($userId));
		}

		$userNameFormated = \CUser::formatName(\CSite::getNameFormat(), $userData, true, false);

		if (\CModule::includeModule('mail'))
		{
			foreach (\Bitrix\Mail\MailboxTable::getUserMailboxes($userId) as $mailbox)
			{
				if (!empty($mailbox['EMAIL']))
				{
					$mailboxName = trim($mailbox['USERNAME']) ?: trim($mailbox['OPTIONS']['name']) ?: $userNameFormated;

					$key = hash('crc32b', mb_strtolower($mailboxName).$mailbox['EMAIL']);
					$mailboxes[$userId][$key] = array(
						'name'  => $mailboxName,
						'email' => $mailbox['EMAIL'],
						'showEditHint' => true,
					);
				}
			}
		}

		// @TODO: query
		$crmAddress = new Address(Main\Config\Option::get('crm', 'mail', ''));
		if ($crmAddress->validate())
		{
			$key = hash('crc32b', mb_strtolower($userNameFormated).$crmAddress->getEmail());

			$mailboxes[$userId][$key] = [
				'name'  => $crmAddress->getName() ?: $userNameFormated,
				'email' => $crmAddress->getEmail(),
			];
		}

		$res = SenderTable::getList(array(
			'filter' => array(
				'IS_CONFIRMED' => true,
				array(
					'LOGIC' => 'OR',
					'=USER_ID' => $userId,
					'IS_PUBLIC' => true,
				),
			),
			'order' => array(
				'ID' => 'ASC',
			),
		));
		while ($item = $res->fetch())
		{
			$item['NAME']  = trim($item['NAME']) ?: $userNameFormated;
			$item['EMAIL'] = mb_strtolower($item['EMAIL']);
			$key = hash('crc32b', mb_strtolower($item['NAME']).$item['EMAIL']);

			if (!isset($mailboxes[$userId][$key]))
			{
				$mailboxes[$userId][$key] = [
					'id' => $item['ID'],
					'name' => $item['NAME'],
					'email' => $item['EMAIL'],
					'can_delete' => $userId == $item['USER_ID'] || $item['IS_PUBLIC'] && $isAdmin,
				];
			}
			else if (!isset($mailboxes[$userId][$key]['id']))
			{
				$mailboxes[$userId][$key]['id'] =  $item['ID'];
				$mailboxes[$userId][$key]['can_delete'] =  $userId == $item['USER_ID'] || $item['IS_PUBLIC'] && $isAdmin;
			}
		}

		foreach ($mailboxes[$userId] as $key => $item)
		{
			$mailboxes[$userId][$key]['formated'] = sprintf(
				$item['name'] ? '%s <%s>' : '%s%s',
				$item['name'], $item['email']
			);

			$mailboxes[$userId][$key]['userId'] = $userId;
		}

		$mailboxes[$userId] = array_values($mailboxes[$userId]);

		return $mailboxes[$userId];
	}

	private static function getOAuthTokenExpireGapSeconds(): int
	{
		// we use 55 minutes because providers give tokens for 1 hour or more,
		// 5 minutes is left for not refresh token too frequent, for mass send
		$default = isModuleInstalled('bitrix24') ? 55 * 60 : 10;

		return (int)Main\Config\Option::get('main', '~oauth_token_expire_gap_seconds', $default);
	}

}
