<?php

namespace Bitrix\Main\Mail;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\SenderTable;
use Bitrix\Main\Event;
use Bitrix\Main\Mail\Sender\UserSenderDataProvider;

class Sender
{
	public const MAIN_SENDER_SMTP_LIMIT_DECREASE = 'MainSenderSmtpLimitDecrease';
	private const MAIN_SENDER_SMTP_SERVER_PATTERN = '/^([a-z0-9-]+\.)+[a-z0-9-]{2,20}$/i';

	public static function add(array $fields)
	{
		$fields['NAME'] = $fields['NAME'] ?? '';
		if (
			$fields['NAME'] !== ''
			&& $fields['NAME'] !== Sender\UserSenderDataProvider::getUserFormattedName()
		)
		{
			$checkResult = self::checkSenderNameCharacters($fields['NAME']);
			if (!$checkResult->isSuccess())
			return [
				'errors' => $checkResult->getErrorCollection(),
			];
		}

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
				'MESSAGE_SUBJECT' => Loc::getMessage('MAIN_MAIL_CONFIRM_MESSAGE_SUBJECT'),
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

	public static function updateSender(int $senderId, array $fields, bool $checkSenderAccess = true): UpdateResult
	{
		$updateFields = [];
		$result = new UpdateResult();

		if ($checkSenderAccess)
		{
			$checkResult = self::canEditSender($senderId);
			if (!$checkResult->isSuccess())
			{
				$result->addErrors($checkResult->getErrors());

				return $result;
			}
		}

		$sender = Internal\SenderTable::getById($senderId)->fetch();

		if (!empty($fields['EMAIL']) && $fields['EMAIL'] !== $sender['EMAIL'])
		{
			$updateFields['EMAIL'] = (string)$fields['EMAIL'];
		}

		if (!empty($fields['IS_PUBLIC']) && $fields['IS_PUBLIC'] !== $sender['IS_PUBLIC'])
		{
			$updateFields['IS_PUBLIC'] = (int)$fields['IS_PUBLIC'] === 1 ? 1 : 0;
		}

		if (!empty($fields['OPTIONS']['smtp']) && empty($fields['OPTIONS']['smtp']['password']))
		{
			$fields['OPTIONS']['smtp']['password'] = $sender['OPTIONS']['smtp']['password'];
		}
		if (
			!empty($fields['OPTIONS']['smtp'])
			&& $fields['OPTIONS']['smtp'] !== $sender['OPTIONS']['smtp']
		)
		{
			$smtp = $fields['OPTIONS']['smtp'];
			$checkResult = self::prepareSmtpConfigForSender($smtp);
			if (!$checkResult->isSuccess())
			{
				$result->addErrors($checkResult->getErrors());

				return $result;
			}
			$sender['OPTIONS']['smtp'] = $smtp;
			$updateFields['OPTIONS'] = $sender['OPTIONS'];
		}

		if (
			!is_null($fields['NAME'])
			&& $fields['NAME'] !== $sender['NAME']
		)
		{
			$name = (string)$fields['NAME'];
			$checkResult = self::checkSenderNameCharacters($name);
			if (!$checkResult->isSuccess())
			{
				$result->addErrors($checkResult->getErrors());

				return $result;
			}

			if ($sender['PARENT_MODULE_ID'] === 'mail' && Main\Loader::includeModule('mail'))
			{
				$result = \Bitrix\Mail\MailboxTable::update($sender['PARENT_ID'], ['USERNAME' => $name]);
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
			$updateFields['NAME'] = $name;
		}

		if (!empty($updateFields))
		{
			$result = Internal\SenderTable::update($senderId, $updateFields);

			if (!empty($updateFields['OPTIONS']['smtp']['limit']))
			{
				self::setEmailLimit($sender['EMAIL'], $updateFields['OPTIONS']['smtp']['limit']);
			}
		}

		return $result;
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

	public static function deleteSenderByMailboxId(int $mailboxId): void
	{
		if(!Main\Loader::includeModule('mail'))
		{
			return;
		}

		$sender = Internal\SenderTable::getList([
			'filter' => [
				'=PARENT_MODULE_ID' => 'mail',
				'=PARENT_ID' => $mailboxId,
			],
		])->fetch();

		if ($sender)
		{
			self::delete([(int)$sender['ID']]);
		}
	}

	public static function delete(array $sendersId): void
	{
		foreach ($sendersId as $senderId)
		{
			$id = (int)$senderId;
			$currentSender = Internal\SenderTable::getById($id)->fetch();

			if (!$currentSender)
			{
				continue;
			}

			$result = Internal\SenderTable::delete($id);
			if (!$result->isSuccess())
			{
				continue;
			}

			$aliasesForPossibleDeletion = [];
			if (!empty($currentSender['OPTIONS']['smtp']['server']) && empty(self::getPublicSmtpSenderByEmail($currentSender['EMAIL'], $id)) && $currentSender['USER_ID'])
			{
				$res = \Bitrix\Main\Mail\Internal\SenderTable::getList([
					'filter' => [
						'=EMAIL' => $currentSender['EMAIL'],
						'=USER_ID' => $currentSender['USER_ID'],
					],
				]);

				while ($sender = $res->fetch())
				{
					$aliasesForPossibleDeletion[$sender['USER_ID']][] = $sender;
				}
			}

			if (!$aliasesForPossibleDeletion)
			{
				continue;
			}

			foreach ($aliasesForPossibleDeletion as $userId => $aliases)
			{
				if (self::hasUserAvailableSmtpSenderByEmail($currentSender['EMAIL'], $userId, true))
				{
					continue;
				}

				foreach ($aliases as $alias)
				{
					SenderTable::delete($alias['ID']);
				}
			}
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
				(new Main\Mail\Smtp\OAuthConfigPreparer())->prepareBeforeSendIfNeed($config);
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

		return UserSenderDataProvider::getUserAvailableSenders($userId);
	}

	public static function prepareSmtpConfigForSender(array &$smtp): Main\Result
	{
		$result = new Main\Result();

		if (!empty($smtp['limit']))
		{
			$limit = (int)$smtp['limit'];
			$limit = max($limit, 0);
		}

		$smtp['protocol'] = self::isSmtpsConfigured($smtp) ? 'smtps' : 'smtp';

		$smtp = [
			'server' => mb_strtolower(trim($smtp['server'] ?? '')),
			'port' => (int)($smtp['port'] ?? 0),
			'protocol' => $smtp['protocol'],
			'login' => $smtp['login'] ?? '',
			'password' => $smtp['password'] ?? '',
			'isOauth' => (bool)$smtp['isOauth'] ?? false,
			'limit' => $limit ?? null,
		];

		if (!preg_match(self::MAIN_SENDER_SMTP_SERVER_PATTERN, $smtp['server']))
		{
			$message = Loc::getMessage(
				empty($smtp['server'])
					? 'MAIN_SENDER_EMPTY_SMTP_SERVER'
					: 'MAIN_SENDER_INVALID_SMTP_SERVER'
			);

			return $result->addError(new Error($message));
		}

		if (!preg_match('/^[0-9]+$/i', $smtp['port']) || $smtp['port'] < 1 || $smtp['port'] > 65535)
		{
			$errorMessage = Loc::getMessage(
				empty($smtp['port'])
					? 'MAIN_SENDER_EMPTY_SMTP_PORT'
					: 'MAIN_SENDER_INVALID_SMTP_PORT'
			);

			return $result->addError(new Error($errorMessage));
		}

		if (empty($smtp['login']))
		{
			$errorMessage = Loc::getMessage('MAIN_SENDER_EMPTY_SMTP_LOGIN');

			return $result->addError(new Error($errorMessage));
		}

		if (empty($smtp['password']))
		{
			$errorMessage = Loc::getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD');

			return $result->addError(new Error($errorMessage));
		}

		if (preg_match('/^\^/', $smtp['password']))
		{
			$errorMessage = Loc::getMessage('MAIN_SENDER_INVALID_SMTP_PASSWORD');

			return $result->addError(new Error($errorMessage));
		}

		$smtpConfig = new Smtp\Config([
			'from' => $smtp['login'],
			'host' => $smtp['server'],
			'port' => $smtp['port'],
			'protocol' => $smtp['protocol'],
			'login' => $smtp['login'],
			'password' => $smtp['password'],
			'isOauth' => $smtp['isOauth'],
		]);

		if ($smtpConfig->canCheck())
		{
			$smtpConfig->check($error, $errors);
		}

		if (!empty($error))
		{
			$result->addError(new Error($error));
		}
		else if (!empty($errors) && $errors instanceof Main\ErrorCollection)
		{
			$result->addErrors($errors->toArray());
		}

		return $result;
	}

	/**
	 * checks if the user has a non-mailbox sender with the given email
	*/
	public static function hasUserSenderWithEmail(string $email, int $userId = null): bool
	{
		if (!($userId > 0))
		{
			global $USER;

			if (is_object($USER) && $USER->isAuthorized())
			{
				$userId = $USER->getId();
			}
		}

		$filter = [
			'=IS_CONFIRMED' => true,
			'=EMAIL' => $email,
			'=USER_ID' => $userId,
			'=PARENT_MODULE_ID' => 'main',
		];

		$res = Internal\SenderTable::getList([
			'filter' => $filter,
		]);

		while ($item = $res->fetch())
		{
			if (!empty($item['OPTIONS']['smtp']['server']))
			{
				return true;
			}
		}

		return false;
	}

	public static function canEditSender(int $senderId): Main\Result
	{
		$result = new Main\Result();

		$sender = Internal\SenderTable::getById($senderId)->fetch();
		if (!$sender)
		{
			$result->addError(new Error(Loc::getMessage('MAIN_MAIL_SENDER_UNKNOWN_SENDER_ERROR')));

			return $result;
		}

		$userId = (int)CurrentUser::get()->getId();
		if (!$userId)
		{
			$result->addError(new Error('User is not authorized'));

			return $result;
		}

		if (
			(int)$sender['USER_ID'] !== $userId
			&& !UserSenderDataProvider::isAdmin()
		)
		{
			$result->addError(new Error(Loc::getMessage('MAIN_MAIL_SENDER_EDIT_ERROR')));
		}

		return $result;
	}

	/**
	 * get first public sender with smtp-server settings, one sender can be excluded by id
	 */
	public static function 	getPublicSmtpSenderByEmail(string $email, int $senderId = null, bool $onlyWithSmtp = true): ?int
	{
		$filter = [
			'=IS_CONFIRMED' => true,
			'=EMAIL' => $email,
			'=IS_PUBLIC' => true,
			'!=ID' => $senderId,
		];

		$res = Internal\SenderTable::getList([
			'filter' => $filter,
		]);

		while ($item = $res->fetch()) {
			if (
				(!empty($item['OPTIONS']['smtp']['server'])  && empty($item['OPTIONS']['smtp']['encrypted']))
				|| !$onlyWithSmtp
			)
			{
				return $item['ID'];
			}
		}

		return null;
	}

	public static function hasUserAvailableSmtpSenderByEmail(string $email, int $userId, bool $onlyWithSmtp = false): bool
	{
		if (self::getPublicSmtpSenderByEmail($email, onlyWithSmtp: $onlyWithSmtp))
		{
			return true;
		}

		$senders = UserSenderDataProvider::getUserAvailableSendersByEmail($email, $userId);

		$requiredTypes = [
			UserSenderDataProvider::SENDER_TYPE,
			UserSenderDataProvider::MAILBOX_SENDER_TYPE,
		];

		foreach ($senders as $sender)
		{
			if (in_array($sender['type'],$requiredTypes) || !$onlyWithSmtp)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the sender's name contains invalid characters
	 *
	 * @param string $name
	 * @return Main\Result
	 */
	public static function checkSenderNameCharacters(string $name): Main\Result
	{
		$result = new Main\Result();
		// regex checks for characters other than letters of the alphabet, numbers, spaces
		// and special characters ("-", ".", "'", "(", ")", ",")
		$pattern = '/[^\p{L}\p{N}\p{Zs}\-.\'(),]+/u';
		if (preg_match($pattern, $name))
		{
			$result->addError(new Error(Loc::getMessage('MAIN_MAIL_SENDER_INVALID_NAME')));
		}

		return $result;
	}

	private static function isSmtpsConfigured(array $smtpSettings): bool
	{
		return
			($smtpSettings['protocol'] ?? '') === 'smtps'
			|| ($smtpSettings['ssl'] ?? '') === 'Y'
		;
	}

}
