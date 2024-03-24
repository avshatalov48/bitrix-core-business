<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Mail\Helper\Mailbox\MailboxConnector;
use Bitrix\Mail\MailServicesTable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Mail\Helper\OAuth;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

/**
 * Class MailboxConnecting
 * Methods for connecting a mailbox and getting the data required for connection
 *
 * @package Bitrix\Mail\Controller
 */
class MailboxConnecting extends Controller
{
	/**
	 * @param string $serviceName
	 * @param string $type
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getUrlOauthAction(string $serviceName, string $type = OAuth::WEB_TYPE): string
	{
		if (!Loader::includeModule('mail'))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_MAILBOX_CONNECTING_ERROR_MAIL_MODULE_IS_NOT_INSTALLED')));
			return '';
		}

		$oauthHelper = OAuth::getInstance($serviceName);

		if (!$oauthHelper)
		{
			$this->addError(new Error(Loc::getMessage('MAIL_MAILBOX_CONNECTING_ERROR_MAIL_MODULE_OAUTH_SERVICE_IS_NOT_CONFIGURED')));
			return false;
		}

		return $oauthHelper->getUrl($type);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getServicesAction(): array
	{
		if (!Loader::includeModule('mail'))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_MAILBOX_CONNECTING_ERROR_MAIL_MODULE_IS_NOT_INSTALLED')));
			return [];
		}

		$services = Mailbox::getServices();

		foreach ($services as &$service)
		{
			if (
				MailServicesTable::getOAuthHelper(['NAME' => $service['name']]) instanceof OAuth
				&& MailboxConnector::isOauthSmtpEnabled($service['name'] ?? '')
			)
			{
				$service['oauthMode'] = true;
			}
			else
			{
				$service['oauthMode'] = false;
			}
		}

		return $services;
	}

	public function getConnectionUrlAction(): string
	{
		$uri = new Uri(UrlManager::getInstance()->getHostUrl().'/bitrix/tools/mobile_oauth.php');
		return $uri->getLocator();
	}

	public function connectMailboxAction(
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
		string $passwordSMTP = ''
	): array
	{
		if (!Loader::includeModule('mail'))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_MAILBOX_CONNECTING_ERROR_MAIL_MODULE_IS_NOT_INSTALLED')));
			return [];
		}

		$mailboxConnector = new MailboxConnector();
		$result = $mailboxConnector->connectMailbox($login, $password, $serviceId, $server, $port, $ssl, $storageOauthUid, $syncAfterConnection, $useSmtp, $serverSmtp, $portSmtp, $sslSmtp, $loginSmtp, $passwordSMTP);
		$this->addErrors($mailboxConnector->getErrors());

		return $result;
	}
}
