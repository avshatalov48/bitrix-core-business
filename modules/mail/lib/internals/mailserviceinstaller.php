<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Config\Option;

class MailServiceInstaller
{
	private const SERVICES_VERSION = 2;
	private const MODULE_NAME = 'mail';
	private const OPTION_NAME = 'services_version';

	/**
	 * Install mail services.
	 * @param $siteId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function installServices($siteId)
	{
		$mailServices = array(
			'gmail' => array(
				'SERVER' => 'imap.gmail.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'https://mail.google.com/',
				'SMTP_SERVER' => 'smtp.gmail.com',
				'SMTP_PORT' => 465,
				'SMTP_ENCRYPTION' => 'Y',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'N',
			),
			'icloud' => array(
				'SERVER' => 'imap.mail.me.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'https://www.icloud.com/#mail',
				'SMTP_SERVER' => 'smtp.mail.me.com',
				'SMTP_PORT' => 587,
				'SMTP_ENCRYPTION' => 'N',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'Y',
			),
			'outlook.com' => array(
				'SERVER' => 'imap-mail.outlook.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'https://www.outlook.com/owa',
				'SMTP_SERVER' => 'smtp-mail.outlook.com',
				'SMTP_PORT' => 587,
				'SMTP_ENCRYPTION' => 'N',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'Y',
			),
			'office365' => array(
				'SERVER' => 'outlook.office365.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'http://mail.office365.com/',
				'SMTP_SERVER' => 'smtp.office365.com',
				'SMTP_PORT' => 587,
				'SMTP_ENCRYPTION' => 'N',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'N',
			),
			'yahoo' => array(
				'SERVER' => 'imap.mail.yahoo.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'http://mail.yahoo.com/',
				'SMTP_SERVER' => 'smtp.mail.yahoo.com',
				'SMTP_PORT' => 465,
				'SMTP_ENCRYPTION' => 'Y',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'N',
			),
			'aol' => array(
				'SERVER' => 'imap.aol.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'http://mail.aol.com/',
				'SMTP_SERVER' => 'smtp.aol.com',
				'SMTP_PORT' => 465,
				'SMTP_ENCRYPTION' => 'Y',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'Y',
			),
			'yandex' => array(
				'SERVER' => 'imap.yandex.ru',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'https://mail.yandex.ru/',
				'SMTP_SERVER' => 'smtp.yandex.ru',
				'SMTP_PORT' => 465,
				'SMTP_ENCRYPTION' => 'Y',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'Y',
			),
			'mail.ru' => array(
				'SERVER' => 'imap.mail.ru',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'http://e.mail.ru/',
				'SMTP_SERVER' => 'smtp.mail.ru',
				'SMTP_PORT' => 465,
				'SMTP_ENCRYPTION' => 'Y',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'Y',
			),
			'exchange' => array(),
			'other' => array(),
			'exchangeOnline' => array(
				'SERVER' => 'outlook.office365.com',
				'PORT' => 993,
				'ENCRYPTION' => 'Y',
				'LINK' => 'https://mail.office365.com/',
				'SMTP_SERVER' => 'smtp.office365.com',
				'SMTP_PORT' => 587,
				'SMTP_ENCRYPTION' => 'N',
				'SMTP_LOGIN_AS_IMAP' => 'Y',
				'SMTP_PASSWORD_AS_IMAP' => 'Y',
				'UPLOAD_OUTGOING' => 'N',
			),
		);

		$mailServicesByLang = array(
			'ru' => array(
				100  => 'gmail',
				200  => 'outlook.com',
				300  => 'icloud',
				400  => 'office365',
				550  => 'exchangeOnline',
				600  => 'yahoo',
				700  => 'aol',
				800  => 'yandex',
				900  => 'mail.ru',
				1000 => 'other',
			),
			'ua' => array(
				100  => 'gmail',
				200  => 'outlook.com',
				300  => 'icloud',
				400  => 'office365',
				550  => 'exchangeOnline',
				600  => 'yahoo',
				700  => 'aol',
				800  => 'other',
			),
			'en' => array(
				100 => 'gmail',
				200 => 'outlook.com',
				300 => 'icloud',
				400 => 'office365',
				550 => 'exchangeOnline',
				600 => 'yahoo',
				700 => 'aol',
				800 => 'other'
			),
			'de' => array(
				100 => 'gmail',
				200 => 'outlook.com',
				300 => 'icloud',
				400 => 'office365',
				550 => 'exchangeOnline',
				600 => 'yahoo',
				700 => 'aol',
				800 => 'other'
			)
		);

		$site = \Bitrix\Main\SiteTable::getList(array('filter' => ["=LID" => $siteId]))
			->fetch();

		if (!$site)
			return;

		if (\CModule::IncludeModule('extranet') && \CExtranet::IsExtranetSite($site['LID']))
			return;

		$portalZone = \Bitrix\Main\Loader::includeModule('bitrix24')
			? \CBitrix24::getPortalZone()
			: $site['LANGUAGE_ID']
		;

		$portalZone = $portalZone ?: LANGUAGE_ID;
		$portalZone = !in_array($portalZone, ['ru', 'kz', 'by']) ? $portalZone : 'ru';

		$mailServicesList = isset($mailServicesByLang[$portalZone])
			? $mailServicesByLang[$portalZone]
			: $mailServicesByLang['en'];

		foreach ($mailServicesList as $serviceSort => $serviceName)
		{
			$exists = \Bitrix\Mail\MailServicesTable::getRow([
				'filter' => [
					'=SITE_ID' => $site['LID'],
					'=NAME' => $serviceName,
					'=SERVICE_TYPE' => 'imap',
				]
			]);

			if ($exists)
			{
				continue;
			}

			$serviceSettings = $mailServices[$serviceName];

			$serviceSettings['SITE_ID']      = $site['LID'];
			$serviceSettings['ACTIVE']       = 'Y';
			$serviceSettings['SERVICE_TYPE'] = 'imap';
			$serviceSettings['NAME']         = $serviceName;
			$serviceSettings['SORT']         = $serviceSort;

			\Bitrix\Mail\MailServicesTable::add($serviceSettings);
		}
	}

	/**
	 * Check services installation and update if not installed.
	 * @param $siteId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function checkInstallComplete($siteId)
	{
		$version = (int)Option::get(self::MODULE_NAME, self::OPTION_NAME, 0, $siteId);

		if ($version === self::SERVICES_VERSION)
		{
			return;
		}
		Option::set(self::MODULE_NAME, self::OPTION_NAME, self::SERVICES_VERSION, $siteId);

		self::installServices($siteId);
	}
}