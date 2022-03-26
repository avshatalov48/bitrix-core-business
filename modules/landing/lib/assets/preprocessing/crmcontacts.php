<?php

namespace Bitrix\Landing\Assets\PreProcessing;

use \Bitrix\Crm\Requisite\EntityLink;
use \Bitrix\Landing\Block;
use \Bitrix\Main\Loader;
use \Bitrix\SalesCenter\Integration\CrmManager;
use \Bitrix\Landing;

class CrmContacts
{
	protected const DEFAULT_COMPANY = 'Company24';
	protected const DEFAULT_PHONES = ['+123456789'];
	protected const DEFAULT_EMAILS = ['info@company24.com'];
	protected const DEFAULT_CONTACTS = [
		self::COMPANY_KEY => self::DEFAULT_COMPANY,
		self::PHONES_KEY => self::DEFAULT_PHONES,
		self::EMAILS_KEY => self::DEFAULT_EMAILS,
	];

	protected const COMPANY_KEY = 'company';
	protected const PHONES_KEY = 'phones';
	protected const EMAILS_KEY = 'emails';

	public const STATUS_CRM_OK = 10;
	public const STATUS_CRM_DEFAULT = 11;
	public const STATUS_CRM_NO_SALESCENTER = 12;
	public const STATUS_CONNECTOR_OK = 20;
	public const STATUS_CONNECTOR_OLD_CRM = 21;
	public const STATUS_CONNECTOR_DEFAULT = 22;
	public const STATUS_SMN_DEFAULT = 30;
	protected static $status;

	/**
	 * @deprecated see \Bitrix\Landing\Connector\Crm::getContacts
	 * Old processing of crm contacts.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function processing(Block $block): void
	{
		$content = $block->getContent();
		if (!$content)
		{
			return;
		}

		// get requisites from my companies
		$contacts = self::getContacts();
		$company = $contacts[self::COMPANY_KEY];
		$phones = $contacts[self::PHONES_KEY];
		$emails = $contacts[self::EMAILS_KEY];

		// todo: just one contact to replace
		// if phones or email found, replace markers
		$replaced = 0;
		$content = preg_replace_callback(
			'/#(PHONE|EMAIL)([\d]+)#/',
			static function ($matches) use ($phones, $emails)
			{
				$key = $matches[2];
				$sources = ($matches[1] === 'PHONE') ? $phones : $emails;

				return $sources[$key] ?? $sources[0];
			},
			$content,
			-1,
			$replaced
		);
		if (mb_strpos($content, '#COMPANY#') !== false)
		{
			$content = str_replace('#COMPANY#', $company, $content);
			$replaced++;
		}

		if ($replaced)
		{
			$block->saveContent($content);
			$block->save();
		}
	}

	/**
	 * Find CRM contacts for default company
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getContacts(): array
	{
		if (Landing\Manager::isB24())
		{
			if (
				!Loader::includeModule('crm')
				|| !Loader::includeModule('salescenter')
			)
			{
				self::$status = self::STATUS_CRM_NO_SALESCENTER;
				return self::DEFAULT_CONTACTS;
			}

			if (EntityLink::getDefaultMyCompanyId() === 0)
			{
				self::$status = self::STATUS_CRM_DEFAULT;
				return self::DEFAULT_CONTACTS;
			}

			$contacts[self::COMPANY_KEY] = CrmManager::getPublishedCompanyName() ?: self::DEFAULT_COMPANY;

			// get just first phone or email
			$phones = CrmManager::getPublishedCompanyPhone();
			$contacts[self::PHONES_KEY] = empty($phones) || $phones['ID'] == 0
				? self::DEFAULT_PHONES
				: [$phones['VALUE']];

			$emails = CrmManager::getPublishedCompanyEmail();
			$contacts[self::EMAILS_KEY] = empty($emails)
				? self::DEFAULT_EMAILS
				: [$emails['VALUE']];

			self::$status = self::STATUS_CRM_OK;
			return $contacts;
		}

		if (Landing\Manager::isB24Connector())
		{
			// todo: now not work in BUS, try later
			// $client = ApClient::init();
			// if ($client)
			// {
			// 	$resContacts = $client->call('salescenter.myMethod');
			// 	if (empty($resContacts['error']))
			// 	{
			// 		// todo: check empty contacts here?
			// 		if (isset($resContacts['result']) && is_array($resContacts['result']))
			// 		{
			// 			self::$status = self::STATUS_CONNECTOR_OK;
			// 			return $resContacts['result'];
			// 		}
			// 	}
			// 	elseif ($resContacts['error'] === 'ERROR_METHOD_NOT_FOUND')
			// 	{
			// 		self::$status = self::STATUS_CONNECTOR_OLD_CRM;
			// 		return self::DEFAULT_CONTACTS;
			// 	}
			// }
			// self::$status = self::STATUS_CONNECTOR_DEFAULT;
			// return self::DEFAULT_CONTACTS;
		}

		// default
		self::$status = self::STATUS_SMN_DEFAULT;
		return self::DEFAULT_CONTACTS;
	}

	/**
	 * Get status. Status is where from getting contacts
	 * @return mixed
	 */
	public static function getStatus()
	{
		return self::$status;
	}
}