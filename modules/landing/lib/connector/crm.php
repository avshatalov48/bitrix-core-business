<?php
namespace Bitrix\Landing\Connector;

use Bitrix\Crm\Requisite\EntityLink;
use Bitrix\Main\Loader;
use Bitrix\SalesCenter\Integration\CrmManager;
use Bitrix\Landing\Manager;

class Crm
{
	protected const CACHE_TAG = 'landing_crm_contacts';
	protected const OPTION_DATA_CODE = 'shop_master_data';

	protected const ID_KEY = 'ID';
	protected const COMPANY_KEY = 'COMPANY';
	protected const PHONE_KEY = 'PHONE';
	protected const EMAIL_KEY = 'EMAIL';

	protected const DEFAULT_COMPANY = 'Company24';
	protected const DEFAULT_PHONE = '+123456789';
	protected const DEFAULT_EMAIL = 'info@company24.com';

	protected const DEFAULT_CONTACTS = [
		self::COMPANY_KEY => self::DEFAULT_COMPANY,
		self::PHONE_KEY => self::DEFAULT_PHONE,
		self::EMAIL_KEY => self::DEFAULT_EMAIL,
	];

	/**
	 * Adds new company to storage contacts.
	 * @param string $title Company title.
	 * @param string $phone Company phone.
	 * @return void
	 */
	protected static function addNewCompany(string $title, string $phone): void
	{
		$company = new \CCrmCompany(false);
		$fields = [
			'TITLE' => $title,
			'OPENED' => 'Y',
			'IS_MY_COMPANY' => 'Y',
			'FM' => [
				'PHONE' => [
					'n0' => [
						'VALUE' => $phone,
						'VALUE_TYPE' => 'WORK'
					]
				]
			]
		];
		$company->add($fields);
	}

	/**
	 * Saves CRM contacts for site.
	 * @param int $siteId Site id.
	 * @param array $data Data to save (COMPANY:string, PHONE:string, EMAIL:string).
	 * @return void
	 */
	public static function setContacts(int $siteId, array $data): void
	{
		$shopStoredData = unserialize(Manager::getOption(self::OPTION_DATA_CODE, ''), ['allowed_classes' => false]);
		$shopStoredData = is_array($shopStoredData) ? $shopStoredData : [];
		if (isset($shopStoredData[$siteId]))
		{
			unset($shopStoredData[$siteId]);
		}
		$shopStoredData[$siteId] = [];

		$contacts = self::getContactsRaw();
		if (!$contacts)
		{
			self::addNewCompany(
				$data[self::COMPANY_KEY],
				$data[self::PHONE_KEY]
			);
		}

		foreach (self::DEFAULT_CONTACTS as $key => $value)
		{
			if (!($data[$key] ?? ''))
			{
				continue;
			}
			if ($data[$key] !== ($contacts[$key] ?? ''))
			{
				$shopStoredData[$siteId][$key] = $data[$key];
			}
		}

		Manager::setOption(self::OPTION_DATA_CODE, serialize($shopStoredData));
		self::clearContactsCache();
	}

	/**
	 * Returns contacts data from DB.
	 * @return array
	 */
	public static function getContactsRaw(): array
	{
		static $contacts = null;

		if ($contacts === null)
		{
			$contacts = [];
		}
		else
		{
			return $contacts;
		}

		if (
			!Loader::includeModule('crm')
			|| !Loader::includeModule('salescenter')
		)
		{
			return $contacts;
		}

		if (!Manager::isB24())
		{
			return $contacts;
		}

		$defaultCompanyId = EntityLink::getDefaultMyCompanyId();

		if (!$contacts && $defaultCompanyId === 0)
		{
			return $contacts;
		}

		$contacts[self::ID_KEY] = $defaultCompanyId;
		$contacts[self::COMPANY_KEY] = CrmManager::getPublishedCompanyName() ?: self::DEFAULT_COMPANY;

		// get just first phone or email

		$phones = CrmManager::getPublishedCompanyPhone();
		$contacts[self::PHONE_KEY] = empty($phones) || $phones['ID'] == 0
			? self::DEFAULT_PHONE
			: $phones['VALUE'];

		$emails = CrmManager::getPublishedCompanyEmail();
		$contacts[self::EMAIL_KEY] = empty($emails)
			? self::DEFAULT_EMAIL
			: $emails['VALUE'];

		return $contacts;
	}

	/**
	 * Returns CRM contacts for default company.
	 * @param int $siteId Site id.
	 * @return array
	 */
	public static function getContacts(int $siteId): array
	{
		$cache = new \CPHPCache;
		$cacheManager = $GLOBALS['CACHE_MANAGER'];
		$cacheTag = self::CACHE_TAG;
		$cacheDir = '/landing/crm_contacts';
		if ($cache->initCache(8640000, $cacheTag . $siteId, $cacheDir))
		{
			return $cache->getVars();
		}

		$cache->startDataCache();
		$cacheManager->startTagCache($cacheDir);
		$cacheManager->registerTag($cacheTag);

		$contacts = self::getContactsRaw();
		if (!$contacts)
		{
			$contacts = self::DEFAULT_CONTACTS;
		}

		$shopData = unserialize(Manager::getOption(self::OPTION_DATA_CODE, ''), ['allowed_classes' => false]);
		if (isset($shopData[$siteId]))
		{
			$contacts = array_merge(
				$contacts,
				$shopData[$siteId]
			);
		}

		foreach ($contacts as &$value)
		{
			if (is_array($value))
			{
				$value = array_shift($value);
			}
		}
		unset($value);

		$cacheManager->endTagCache();
		$cache->endDataCache($contacts);

		return $contacts;
	}

	/**
	 * Clears contacts data cache.
	 * @return void
	 */
	public static function clearContactsCache(): void
	{
		if (defined('BX_COMP_MANAGED_CACHE'))
		{
			Manager::getCacheManager()->clearByTag(self::CACHE_TAG);
		}
	}

	/**
	 * Callback on after add and update crm companies.
	 * @param array $fields Company data.
	 * @return void
	 */
	public static function onAfterCompanyChange(array $fields): void
	{
		$companyId = $fields['ID'] ?? null;
		if ($companyId === EntityLink::getDefaultMyCompanyId())
		{
			self::clearContactsCache();
		}
	}

	/**
	 * Returns replace-array for str_replace.
	 *
	 * @param int $siteId Site id.
	 * @param bool $attributesReplace Return replace for inner attributes.
	 * @return array
	 */
	public static function getReplacesForContent(int $siteId, bool $attributesReplace = true): array
	{
		$replace = [];

		$crmContacts = self::getContacts($siteId);
		$replace['#crmCompanyTitle'] = \htmlspecialcharsbx($crmContacts['COMPANY']);

		if (!empty($crmContacts['PHONE']))
		{
			$phone = $crmContacts['PHONE'];
			$phone = \htmlspecialcharsbx($phone);
			$replace['#crmPhoneTitle1'] = $phone;// a-tag inside
			if ($attributesReplace)
			{
				$replace['#crmPhone1'] = $phone;// a-href inside
			}
		}

		if (!empty($crmContacts['EMAIL']))
		{
			$email = $crmContacts['EMAIL'];
			$email = \htmlspecialcharsbx($email);
			$replace['#crmEmailTitle1'] = $email;// a-tag inside
			if ($attributesReplace)
			{
				$replace['#crmEmail1'] = $email;// a-href inside
			}
		}

		return $replace;
	}
}
