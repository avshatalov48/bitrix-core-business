<?php
namespace Bitrix\Landing\Site;

use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Landing\Internals\HookDataTable;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ORM\Data\AddResult;
use \Bitrix\Main\ORM\Data\UpdateResult;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\UserConsent\Consent;
use \Bitrix\Main\UserConsent\Agreement;
use \Bitrix\Main\UserConsent\Internals\AgreementTable;

Loc::loadMessages(__FILE__);

class Cookies
{
	/**
	 * System cookie codes.
	 */
	const SYSTEM_COOKIES = [
		'ga' => ['type' => 'analytic'],// google analytics
		'gtm' => ['type' => 'analytic'],// google tag manager
		'ym' => ['type' => 'analytic'],// yandex metrika
		'fbp' => ['type' => 'analytic'],// facebook pixel
		'vkp' => ['type' => 'analytic'],// vkontakte pixel
		'yt' => ['type' => 'technical'],// youtube
		'gmap' => ['type' => 'technical']// google maps
	];

	/**
	 * Unique code for user consent table.
	 */
	const USER_CONSENT_CODE = 'landing_cookies';

	/**
	 * Creates new agreement for current language, if not exists.
	 * @param int|null Agreement id.
	 * @return array
	 */
	public static function getMainAgreement(?int $agreementId = null): ?array
	{
		$currentLang = LANGUAGE_ID;
		$agreementCode = 'landing_cookie_agreement';
		$fields = [
			'ID' => 0,
			'NAME' => Loc::getMessage('LANDING_COOKIES_MAIN_AGREEMENT_TITLE'),
			'AGREEMENT_TEXT' => Loc::getMessage('LANDING_COOKIES_MAIN_AGREEMENT_TEXT'),
			'LABEL_TEXT' => Loc::getMessage('LANDING_COOKIES_MAIN_AGREEMENT_LABEL'),
		];

		if (!$fields['NAME'])
		{
			return null;
		}

		// current from database (actualize in db)
		$res = AgreementTable::getList([
			'select' => [
				'ID', 'NAME', 'AGREEMENT_TEXT', 'LABEL_TEXT'
			],
			'filter' =>
				$agreementId
				? [
					'ID' => $agreementId
				]
				: [
				'=ACTIVE' => 'Y',
				'=CODE' => $agreementCode,
				'=LANGUAGE_ID' => $currentLang
			]
		]);
		if ($row = $res->fetch())
		{
			return $row;
		}
		else
		{
			$res = AgreementTable::add([
				'CODE' => $agreementCode,
				'LANGUAGE_ID' => $currentLang,
				'TYPE' => Agreement::TYPE_CUSTOM,
				'NAME' => $fields['NAME'],
				'AGREEMENT_TEXT' => $fields['AGREEMENT_TEXT'],
				'LABEL_TEXT' => $fields['LABEL_TEXT'],
				'IS_AGREEMENT_TEXT_HTML' => 'Y'
			]);
			if ($res->isSuccess())
			{
				$fields['ID'] = $res->getId();
				return $fields;
			}
		}

		return null;
	}

	/**
	 * Returns cookie type by cookie code.
	 * @param string $code Cookie code.
	 * @return string
	 */
	protected static function getCookieType(string $code): string
	{
		if (isset(self::SYSTEM_COOKIES[$code]))
		{
			return self::SYSTEM_COOKIES[$code]['type'];
		}
		else
		{
			return 'other';
		}
	}

	/**
	 * Returns system agreements for site.
	 * @param int $siteId Site id.
	 * @param bool $viewMode Skip raw data with tilda-key and prepare content.
	 * @return array
	 */
	public static function getAgreements(int $siteId, bool $viewMode = false): array
	{
		$agreements = [];

		if (!$siteId)
		{
			return $agreements;
		}

		//get zone
		$zone = '';
		if (Loader::includeModule('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/ru")
			&& !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/ua"))
		{
			$zone = 'ru';
		}

		// first get system messages
		foreach (self::SYSTEM_COOKIES as $code => $cookieItem)
		{
			if (in_array($code, ['ym', 'vkp']) && !Manager::availableOnlyForZone('ru'))
			{
				continue;
			}
			if ($code === 'fbp' && $zone === 'ru')
			{
				continue;
			}
			$codeUp = strtoupper($code);
			$agreements[$code] = [
				'ID' => 0,
				'CODE' => $code,
				'TYPE' => $cookieItem['type'],
				'SYSTEM' => 'Y',
				'ACTIVE' => 'Y',
				'TITLE' => Loc::getMessage('LANDING_COOKIES_SYS_' . $codeUp . '_TITLE'),
				'~TITLE' => $viewMode ? '' : Loc::getMessage('LANDING_COOKIES_SYS_' . $codeUp . '_TITLE'),
				'CONTENT' => Loc::getMessage('LANDING_COOKIES_SYS_' . $codeUp . '_TEXT'),
				'~CONTENT' => $viewMode ? '' : Loc::getMessage('LANDING_COOKIES_SYS_' . $codeUp . '_TEXT')
			];
		}

		if ($siteId < 0)
		{
			return $agreements;
		}

		// then get custom messages from DB
		$res = CookiesAgreement::getList([
			'select' => [
				'ID', 'ACTIVE', 'CODE', 'TITLE', 'CONTENT'
			],
			'filter' => [
				'SITE_ID' => $siteId
			]
		]);
		while ($row = $res->fetch())
		{
			if (isset($agreements[$row['CODE']]))
			{
				if (!$row['TITLE'])
				{
					unset($row['TITLE']);
				}
				$agreements[$row['CODE']] = array_merge(
					$agreements[$row['CODE']],
					$row
				);
			}
			else
			{
				$row['SYSTEM'] = 'N';
				$row['TYPE'] = self::getCookieType($row['CODE']);
				$row['~TITLE'] = $viewMode ? '' : $row['TITLE'];
				$row['~CONTENT'] = $viewMode ? '' : $row['CONTENT'];
				$agreements[$row['CODE']] = $row;
			}
		}

		if ($viewMode)
		{
			$agreements = array_map(function($item)
			{
				$parser = new \CTextParser;
				$item['CONTENT'] = $parser->convertText($item['CONTENT']);
				$item['TITLE'] = \htmlspecialcharsbx($item['TITLE']);
				return $item;
			}, $agreements);
		}

		return $agreements;
	}

	/**
	 * Add new agreements for the site.
	 * @param int $siteId Site id.
	 * @param array $fields Data array ([CODE, TITLE, CONTENT]).
	 * @return AddResult
	 */
	public static function addAgreementForSite(int $siteId, array $fields): AddResult
	{
		return CookiesAgreement::add([
			'SITE_ID' => $siteId,
			'CODE' => $fields['CODE'] ?? null,
			'ACTIVE' => $fields['ACTIVE'] ?? 'Y',
			'TITLE' => $fields['TITLE'] ?? null,
			'CONTENT' => $fields['CONTENT'] ?? null
		]);
	}

	/**
	 * Update agreements for the site.
	 * @param int $agreementId Agreement id.
	 * @param array $fields Data array ([TITLE, CONTENT]).
	 * @return UpdateResult
	 */
	public static function updateAgreementForSite(int $agreementId, array $fields): UpdateResult
	{
		return CookiesAgreement::update($agreementId, $fields);
	}

	/**
	 * Removes all agreements for the site.
	 * @param int $siteId Site id.
	 * @param string $code Cookie code.
	 * @return void
	 */
	public static function removeAgreementsForSite(int $siteId, ?string $code = null): void
	{
		$filter = [
			'SITE_ID' => $siteId
		];
		if ($code)
		{
			$filter['=CODE'] = $code;
		}
		$res = CookiesAgreement::getList([
			'select' => [
				'ID'
			],
			'filter' => $filter
		]);
		while ($row = $res->fetch())
		{
			CookiesAgreement::delete($row['ID'])->isSuccess();
		}
	}

	/**
	 * Accepts agreement with specific cookies codes.
	 * @param int $siteId Site id.
	 * @param array $accepted Accepted cookies codes.
	 * @return void
	 */
	public static function acceptAgreement(int $siteId, array $accepted = []): void
	{
		$agreementId = \Bitrix\Landing\Hook\Page\Cookies::getAgreementIdBySiteId($siteId);
		if (!$agreementId)
		{
			return;
		}

		$agreement = self::getMainAgreement($agreementId);
		if (!$agreement)
		{
			return;
		}

		$consentItems = [];
		foreach ($accepted as $key)
		{
			$consentItems[] = [
				'VALUE' => $key
			];
		}
		if (!$consentItems)
		{
			return;
		}

		Consent::addByContext(
			$agreement['ID'],
			self::USER_CONSENT_CODE,
			$siteId,
			[
				'URL' => Site::getPublicUrl($siteId),
				'ITEMS' => $consentItems
			]
		);
	}

	/**
	 * Checks if site (site itself, any site page and any blocks on the pages in this site)
	 * includes any javascript code.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function isSiteIncludesScript(int $siteId): bool
	{
		if (!$siteId)
		{
			return false;
		}

		// first check if site includes script
		$res = HookDataTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=HOOK' => 'HEADBLOCK',
				'=CODE' => 'CODE',
				'=ENTITY_ID' => $siteId,
				'=ENTITY_TYPE' => Hook::ENTITY_TYPE_SITE,
				'=PUBLIC' => 'N',
				'VALUE' => '%<script%'
			]
		]);
		if ($res->fetch())
		{
			return true;
		}

		// then check if any page of this site includes script
		$res = HookDataTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=HOOK' => 'HEADBLOCK',
				'=CODE' => 'CODE',
				'=ENTITY_TYPE' => Hook::ENTITY_TYPE_LANDING,
				'=LANDING.SITE_ID' => $siteId,
				'=PUBLIC' => 'N',
				'VALUE' => '%<script%'
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ReferenceField(
					'LANDING',
					'\Bitrix\Landing\Internals\LandingTable',
					['=this.ENTITY_ID' => 'ref.ID']
				)
			]
		]);
		if ($res->fetch())
		{
			return true;
		}

		// then check any blocks on the pages of the site
		$res = BlockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=CODE' => 'html',
				'=PUBLIC' => 'N',
				'=ACTIVE' => 'Y',
				'=LANDING.SITE_ID' => $siteId,
				'CONTENT' => '%&lt;script%'
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ReferenceField(
					'LANDING',
					'\Bitrix\Landing\Internals\LandingTable',
					['=this.LID' => 'ref.ID']
				)
			]
		]);
		if ($res->fetch())
		{
			return true;
		}

		return false;
	}

	/**
	 * Prepares cookies codes for user consents grid.
	 * @return EventResult
	 */
	public static function onUserConsentProviderList(): EventResult
	{
		$currentSite = [];

		$parameters = [
			[
				'CODE' => self::USER_CONSENT_CODE,
				'NAME' => 'Cookie',
				'DATA' => function ($id = null) use(&$currentSite)
				{
					static $sites = [];

					$id = intval($id);
					if (!$id)
					{
						return null;
					}

					if (!isset($sites[$id]))
					{
						$sites[$id] = Site::getList([
							'select' => [
								'ID', 'TITLE'
							],
							'filter' => [
								'ID' => $id,
								'=DELETED' => ['Y', 'N']
							],
							'limit' => 1
						])->fetch();
					}
					if ($sites[$id])
					{
						$sites[$id]['URL'] = Site::getPublicUrl($id);
						$sites[$id]['AGREEMENTS'] = self::getAgreements($id);
						$currentSite = $sites[$id];
					}
					else
					{
						return null;
					}

					return [
						'NAME' => $sites[$id]['TITLE'],
						'URL' => $sites[$id]['URL']
					];
				},
				'ITEMS' => function ($code = null) use(&$currentSite)
				{
					if (!$currentSite)
					{
						$currentSite = [
							'AGREEMENTS' => self::getAgreements(-1)
						];
					}
					return $currentSite['AGREEMENTS'][$code]['TITLE'] ?? $code;
				},
			]
		];

		return new EventResult(EventResult::SUCCESS, $parameters, 'landing');
	}
}