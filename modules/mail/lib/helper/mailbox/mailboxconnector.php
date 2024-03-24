<?php

namespace Bitrix\Mail\Helper\Mailbox;

use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Main;
use Bitrix\Mail;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail\Helper\LicenseManager;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Mail\Address;
use Bitrix\Mail\MailServicesTable;

final class MailboxConnector
{
	private const STANDARD_ERROR_KEY = 1;
	private const LIMIT_ERROR_KEY = 2;
	private const OAUTH_ERROR_KEY = 3;
	private const EXISTS_ERROR_KEY = 4;
	private const NO_MAIL_SERVICES_ERROR_KEY = 5;
	private const SMTP_PASS_BAD_SYMBOLS_ERROR_KEY = 6;
	public const CRM_MAX_AGE = 7;
	public const MESSAGE_MAX_AGE = 7;

	private bool $isSuccess = false;

	private array $errorCollection = [];

	private bool $isSMTPAvailable = false;

	public function getSuccess(): bool
	{
		return $this->isSuccess;
	}

	public function setSuccess(): void
	{
		$this->isSuccess = true;
	}

	public function getErrors(): array
	{
		return $this->errorCollection;
	}

	protected function addError(string $error): void
	{
		$this->errorCollection[] = new Main\Error($error);
	}

	protected function addErrors(
		Main\ErrorCollection $errors,
		bool $isOAuth = false,
		bool $isSender = false
	): void
	{
		$messages = [];
		$details  = [];

		foreach ($errors as $item)
		{
			if ($item->getCode() < 0)
			{
				$details[] = $item;
			}
			else
			{
				$messages[] = $item;
			}
		}

		if (count($messages) == 1 && reset($messages)->getCode() == Mail\Imap::ERR_AUTH)
		{
			$authError = Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_IMAP_AUTH_ERR_EXT');
			if ($isOAuth && Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_ERR_OAUTH'))
			{
				$authError = Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_ERR_OAUTH');
			}
			if ($isOAuth && $isSender && Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_ERR_OAUTH_SMTP'))
			{
				$authError = Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_ERR_OAUTH_SMTP');
			}

			$messages = [
				new Main\Error($authError, Mail\Imap::ERR_AUTH),
			];

			$moreDetailsSection = false;
		}
		else
		{
			$moreDetailsSection = true;
		}

		$reduce = function($error)
		{
			return $error->getMessage();
		};

		if($moreDetailsSection)
		{
			$this->errorCollection[] = new Main\Error(
				implode(': ', array_map($reduce, $messages)),
				0,
				implode(': ', array_map($reduce, $details))
			);
		}
		else
		{
			$this->errorCollection[] = new Main\Error(
				implode(': ', array_map($reduce, $messages)),
				0,
			);
		}
	}

	private function setError(int $code = self::STANDARD_ERROR_KEY): void
	{
		switch ($code) {
			case self::STANDARD_ERROR_KEY:
				$this->addError(Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_FORM_ERROR'));
				break;
			case self::LIMIT_ERROR_KEY:
				$this->addError(Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_LIMIT_ERROR'));
				break;
			case self::OAUTH_ERROR_KEY:
				$this->addError(Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_OAUTH_ERROR'));
				break;
			case self::EXISTS_ERROR_KEY:
				$this->addError(Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_EMAIL_EXISTS_ERROR'));
				break;
			case self::NO_MAIL_SERVICES_ERROR_KEY:
				$this->addError(Loc::getMessage('MAIL_MAILBOX_CONNECTOR_CLIENT_THERE_ARE_NO_MAIL_SERVICES'));
				break;
			case self::SMTP_PASS_BAD_SYMBOLS_ERROR_KEY:
				$this->addError(Loc::getMessage('MAIL_MAILBOX_CONNECTOR_SMTP_PASS_BAD_SYMBOLS'));
				break;
		}
	}

	private static function getUserOwnedMailboxCount()
	{
		global $USER;

		$res = Mail\MailboxTable::getList([
			'select' => [
				new Main\Entity\ExpressionField('OWNED', 'COUNT(%s)', 'ID'),
			],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=USER_ID' => $USER->getId(),
				'=SERVER_TYPE' => 'imap',
			],
		])->fetch();

		return $res['OWNED'];
	}

	public static function canConnectNewMailbox(): bool
	{
		$userMailboxesLimit = LicenseManager::getUserMailboxesLimit();
		if ($userMailboxesLimit >= 0)
		{
			if (self::getUserOwnedMailboxCount() >= $userMailboxesLimit)
			{
				return false;
			}
		}

		return true;
	}

	private function syncMailbox(int $mailboxId): void
	{
		Main\Application::getInstance()->addBackgroundJob(function ($mailboxId) {
			$mailboxHelper = Mailbox::createInstance($mailboxId, false);
			$mailboxHelper->sync();
		},[$mailboxId]);
	}

	private function setIsSmtpAvailable(): void
	{
		$defaultMailConfiguration = Configuration::getValue("smtp");
		$this->isSMTPAvailable = Main\ModuleManager::isModuleInstalled('bitrix24')
			|| $defaultMailConfiguration['enabled'];
	}

	private function getSmtpAvailable(): bool
	{
		return $this->isSMTPAvailable;
	}

	/**
	 * Is OAuth for SMTP enabled for service
	 *
	 * @param string $serviceName Service name
	 *
	 * @return bool
	 */
	public static function isOauthSmtpEnabled(string $serviceName): bool
	{
		switch ($serviceName)
		{
			case 'gmail':
				return Main\Config\Option::get('mail', '~disable_gmail_oauth_smtp') === 'N';
			case 'yandex':
				return Main\Config\Option::get('mail', '~disable_yandex_oauth_smtp') !== 'Y';
			case 'mail.ru':
				return Main\Config\Option::get('mail', '~disable_mailru_oauth_smtp') === 'N';
			case 'office365':
			case 'outlook.com':
			case 'exchangeOnline':
				return Main\Config\Option::get('mail', '~disable_microsoft_oauth_smtp') !== 'Y';
			default:
				return false;
		}
	}

	public static function isValidMailHost(string $host): bool
	{
		if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			// Private addresses can't be used in the cloud
			$ip = \Bitrix\Main\Web\IpAddress::createByName($host);
			if ($ip->isPrivate())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Append SMTP sender, with two attempts for outlook
	 *
	 * @param array $senderFields Sender fields data
	 * @param string $userPrincipalName User Principal Name, appears in outlook oauth data only
	 *
	 * @return array
	 */
	public static function appendSender(array $senderFields, string $userPrincipalName): array
	{
		$result = Main\Mail\Sender::add($senderFields);

		if (empty($result['confirmed']) && $userPrincipalName)
		{
			$address = new Address($userPrincipalName);
			$currentSmtpLogin = $senderFields['OPTIONS']['smtp']['login'] ?? '';
			if ($currentSmtpLogin && $currentSmtpLogin !== $userPrincipalName && $address->validate())
			{
				// outlook workaround, sometimes SMTP auth only works with userPrincipalName
				$senderFields['OPTIONS']['smtp']['login'] = $userPrincipalName;
				$result = Main\Mail\Sender::add($senderFields);
			}
		}
		return $result;
	}

	public function connectMailbox(
		string $login = '',
		string $password = '',
		int $serviceId = 0,
		string $server = '',
		int $port = 993,
		bool $ssl = true,
		string $storageOauthUid = '',
		bool $syncAfterConnection = true,
		bool $useSmtp = true,
		string $serverSmtp = '',
		int $portSmtp = 587,
		bool $sslSmtp = true,
		string $loginSmtp = '',
		string $passwordSMTP = '',
	): array
	{
		$login = trim($login);
		$password = trim($password);
		$server = trim($server);

		$currentSite = \CSite::getById(SITE_ID)->fetch();
		global $USER;

		$this->setIsSmtpAvailable();

		$service = Mail\MailServicesTable::getList([
			'filter' => [
				'=ID' => $serviceId,
				'SERVICE_TYPE' => 'imap',
			],
		])->fetch();

		if (empty($service))
		{
			$this->setError(self::NO_MAIL_SERVICES_ERROR_KEY);
			return [];
		}

		if ($service['ACTIVE'] !== 'Y')
		{
			$this->setError();
			return [];
		}

		if (!$this->canConnectNewMailbox())
		{
			$this->setError(self::LIMIT_ERROR_KEY);
			return [];
		}

		if ($ssl)
		{
			$ssl = 'Y';
		}
		else
		{
			$ssl = 'N';
		}

		if ($sslSmtp)
		{
			$sslSmtp = 'Y';
		}
		else
		{
			$sslSmtp = 'N';
		}

		$mailboxData = [
			'USERNAME' => '',
			'SERVER'   => $service['SERVER'] ?: trim($server),
			'PORT'     => $service['PORT'] ?: $port,
			'USE_TLS'  => $service['ENCRYPTION'] ?: $ssl,
			'LINK'     => $service['LINK'],
			'EMAIL'    => $login,
			'NAME'     => $login,
			'PERIOD_CHECK' => 60 * 24,
			'OPTIONS'  => [
				'flags'     => [],
				'sync_from' => time(),
				'crm_sync_from' => time(),
				'activateSync' => false,
				'name' => '',
			],
		];

		if ('N' == $service['UPLOAD_OUTGOING'] || empty($service['UPLOAD_OUTGOING']))
		{
			$mailboxData['OPTIONS']['flags'][] = 'deny_upload';
		}

		$isOAuth = false;
		if ($storageOauthUid !== '' && $oauthHelper = Mail\MailServicesTable::getOAuthHelper($service))
		{
			$oauthHelper->getStoredToken($storageOauthUid);
			$mailboxData['LOGIN'] = $mailboxData['EMAIL'];
			$mailboxData['PASSWORD'] = $oauthHelper->buildMeta();
			$isOAuth = true;
		}
		else
		{
			$mailboxData['LOGIN'] = $login;
			$mailboxData['PASSWORD'] = $password;
		}

		if (empty($mailbox['EMAIL']))
		{
			$address = new Address($mailboxData['EMAIL']);
			if (!$address->validate())
			{
				$this->setError(self::OAUTH_ERROR_KEY);
				return [];
			}

			$mailboxData['EMAIL'] = $address->getEmail();
			$this->email = $mailboxData['EMAIL'];
		}
		else
		{
			$this->email = $mailbox['EMAIL'];
		}

		if (empty($mailbox))
		{
			$mailbox = Mail\MailboxTable::getList([
				'filter' => [
					'=EMAIL' => $mailboxData['EMAIL'],
					'=USER_ID' => $USER->getId(),
					'=ACTIVE' => 'Y',
					'=LID' => $currentSite['LID'],
				],
			])->fetch();

			if (!empty($mailbox))
			{
				$this->setError(self::EXISTS_ERROR_KEY);
				return [];
			}
		}

		if (empty($mailboxData['NAME']))
		{
			$mailboxData['NAME'] = $mailboxData['EMAIL'];
		}

		if (!in_array($mailboxData['USE_TLS'], array('Y', 'S')))
		{
			$mailboxData['USE_TLS'] = 'N';
		}

		$unseen = Mail\Helper::getImapUnseen($mailboxData, 'inbox', $error, $errors);
		if ($unseen === false)
		{
			if ($errors instanceof Main\ErrorCollection)
			{
				$this->addErrors($errors, $isOAuth);
			}
			else
			{
				$this->setError();
			}
			return [];
		}

		$isSmtpOauthEnabled = !empty(MailServicesTable::getOAuthHelper($service))
			&& self::isOauthSmtpEnabled($service['NAME']);
		$useSmtp = $useSmtp || $isSmtpOauthEnabled;

		if ($this->getSmtpAvailable() && !$useSmtp && !empty($mailbox))
		{
			$res = Main\Mail\Internal\SenderTable::getList(array(
				'filter' => array(
					'IS_CONFIRMED' => true,
					'=EMAIL' => $mailboxData['EMAIL'],
				),
			));
			while ($item = $res->fetch())
			{
				if (!empty($item['OPTIONS']['smtp']['server']))
				{
					unset($item['OPTIONS']['smtp']);
					Main\Mail\Internal\SenderTable::update(
						$item['ID'],
						array(
							'OPTIONS' => $item['OPTIONS'],
						)
					);
				}
			}

			Main\Mail\Sender::clearCustomSmtpCache($mailboxData['EMAIL']);
		}

		if ($this->getSmtpAvailable() && $useSmtp)
		{
			$senderFields = [
				'NAME' => $mailboxData['USERNAME'],
				'EMAIL' => $mailboxData['EMAIL'],
				'USER_ID' => $USER->getId(),
				'IS_CONFIRMED' => false,
				'IS_PUBLIC' => false,
				'OPTIONS' => [
					'source' => 'mail.client.config',
				],
			];

			$res = Main\Mail\Internal\SenderTable::getList(array(
				'filter' => [
					'IS_CONFIRMED' => true,
					'=EMAIL' => $mailboxData['EMAIL'],
				],
				'order' => [
					'ID' => 'DESC',
				],
			));

			while ($item = $res->fetch())
			{
				if (empty($smtpConfirmed))
				{
					if (!empty($item['OPTIONS']['smtp']['server']) && empty($item['OPTIONS']['smtp']['encrypted']))
					{
						$smtpConfirmed = $item['OPTIONS']['smtp'];
					}
				}

				if ($senderFields['USER_ID'] == $item['USER_ID'] && $senderFields['NAME'] == $item['NAME'])
				{
					$senderFields = $item;
					$senderFields['IS_CONFIRMED'] = false;
					$senderFields['OPTIONS']['__replaces'] = $item['ID'];

					unset($senderFields['ID']);

					if (!empty($smtpConfirmed))
					{
						break;
					}
				}
			}
		}

		if (!empty($senderFields))
		{
			$smtpConfig = array(
				'server'   => $service['SMTP_SERVER'] ?: trim($serverSmtp),
				'port'     => $service['SMTP_PORT'] ?: $portSmtp,
				'protocol' => ('Y' == ($service['SMTP_ENCRYPTION'] ?: $sslSmtp) ? 'smtps' : 'smtp'),
				'login'    => $service['SMTP_LOGIN_AS_IMAP'] == 'Y' ? $mailboxData['LOGIN'] : $loginSmtp,
				'password' => '',
			);

			if (!empty($smtpConfirmed) && is_array($smtpConfirmed))
			{
				// server, port, protocol, login, password
				$smtpConfig = array_filter($smtpConfig) + $smtpConfirmed;
			}

			if ($service['SMTP_PASSWORD_AS_IMAP'] == 'Y' && (!$storageOauthUid || $isSmtpOauthEnabled))
			{
				$smtpConfig['password'] = $mailboxData['PASSWORD'];
				$smtpConfig['isOauth'] = !empty($storageOauthUid) && $isSmtpOauthEnabled;
			}
			else if ($passwordSMTP <> '')
			{
				if (preg_match('/^\^/', $passwordSMTP))
				{
					$this->setError(self::SMTP_PASS_BAD_SYMBOLS_ERROR_KEY);
					return [];
				}
				else if (preg_match('/\x00/', $passwordSMTP))
				{
					$this->setError(self::SMTP_PASS_BAD_SYMBOLS_ERROR_KEY);
					return [];
				}

				$smtpConfig['password'] = $passwordSMTP;
				$smtpConfig['isOauth'] = !empty($storageOauthUid) && $isSmtpOauthEnabled;
			}

			if (!$service['SMTP_SERVER'])
			{
				$regex = '/^(?:(?:http|https|ssl|tls|smtp):\/\/)?((?:[a-z0-9](?:-*[a-z0-9])*\.?)+)$/i';
				if (!preg_match($regex, $smtpConfig['server'], $matches) && $matches[1] <> '')
				{
					$this->setError(self::OAUTH_ERROR_KEY);
					return [];
				}

				$smtpConfig['server'] = $matches[1];

				if (!self::isValidMailHost($smtpConfig['server']))
				{
					$this->setError(self::OAUTH_ERROR_KEY);
					return [];
				}
			}

			if (!$service['SMTP_PORT'])
			{
				if ($smtpConfig['port'] <= 0 || $smtpConfig['port'] > 65535)
				{
					$this->setError(self::OAUTH_ERROR_KEY);
					return [];
				}
			}

			$senderFields['OPTIONS']['smtp'] = $smtpConfig;

			if (!empty($smtpConfirmed))
			{
				$senderFields['IS_CONFIRMED'] = !array_diff(
					array('server', 'port', 'protocol', 'login', 'password', 'isOauth'),
					array_keys(array_intersect_assoc($smtpConfig, $smtpConfirmed))
				);
			}
		}

		if (Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled())
		{
			$crmAvailable = $USER->isAdmin() || $USER->canDoOperation('bitrix24_config')
				|| \COption::getOptionString('intranet', 'allow_external_mail_crm', 'Y', SITE_ID) == 'Y';

			$mailboxData['OPTIONS']['sync_from'] = strtotime('today UTC 00:00'.sprintf('-%u days', self::MESSAGE_MAX_AGE));

			if ($crmAvailable)
			{
				$maxAge = self::CRM_MAX_AGE;
				$mailboxData['OPTIONS']['flags'][] = 'crm_connect';
				$mailboxData['OPTIONS']['crm_sync_from'] = strtotime(sprintf('-%u days', $maxAge));
				$mailboxData['OPTIONS']['crm_new_entity_in'] = \CCrmOwnerType::LeadName;
				$mailboxData['OPTIONS']['crm_new_entity_out'] = \CCrmOwnerType::ContactName;
				$mailboxData['OPTIONS']['crm_lead_source'] = 'EMAIL';
				$mailboxData['OPTIONS']['crm_lead_resp'] = [empty($mailbox) ? $USER->getId() : $mailbox['USER_ID']];
			}
		}

		if (!empty($senderFields) && empty($senderFields['IS_CONFIRMED']))
		{
			$result = $this->appendSender($senderFields, (string)($fields['user_principal_name'] ?? ''));

			if (!empty($result['errors']) && $result['errors'] instanceof Main\ErrorCollection)
			{
				$this->addErrors($result['errors'], $isOAuth, true);
				return [];
			}
			else if (!empty($result['error']))
			{
				$this->addError($result['error']);
				return [];
			}
			else if (empty($result['confirmed']))
			{
				$this->addError('MAIL_CLIENT_CONFIG_SMTP_CONFIRM');
				return [];
			}
		}

		$mailboxData['OPTIONS']['version'] = 6;

		if (empty($mailbox))
		{
			$mailboxData = array_merge([
				'LID'         => $currentSite['LID'],
				'ACTIVE'      => 'Y',
				'SERVICE_ID'  => $service['ID'],
				'SERVER_TYPE' => $service['SERVICE_TYPE'],
				'CHARSET'     => $currentSite['CHARSET'],
				'USER_ID'     => $USER->getId(),
				'SYNC_LOCK'   => time()
			], $mailboxData);

			$result = $mailboxId = \CMailbox::add($mailboxData);

			addEventToStatFile('mail', 'add_mailbox', $service['NAME'], ($result > 0 ? 'success' : 'failed'));
		}
		else
		{
			$this->setError(self::EXISTS_ERROR_KEY);
			return [];
		}

		if (!($result > 0))
		{
			$this->setError();
			return [];
		}

		$ownerAccessCode = 'U' . (empty($mailbox) ? $USER->getId() : $mailbox['USER_ID']);
		$access = array($ownerAccessCode);

		foreach (array_unique($access) as $item)
		{
			Mail\Internals\MailboxAccessTable::add(array(
				'MAILBOX_ID' => $mailboxId,
				'TASK_ID' => 0,
				'ACCESS_CODE' => $item,
			));
		}

		$mailboxHelper = Mailbox::createInstance($mailboxId);
		$mailboxHelper->cacheDirs();

		$filterFields = [
			'MAILBOX_ID'	=> $mailboxId,
			'NAME'	=> sprintf('CRM IMAP %u', $mailboxId),
			'ACTION_TYPE'	=> 'crm_imap',
			'WHEN_MAIL_RECEIVED'	=> 'Y',
			'WHEN_MANUALLY_RUN'	=> 'Y',
		];

		\CMailFilter::add($filterFields);

		$this->setSuccess();

		if ($syncAfterConnection)
		{
			$this->syncMailbox($mailboxId);
		}

		return [
			'id' => $mailboxId,
			'email' => trim($mailboxData['EMAIL']),
		];
	}
}
