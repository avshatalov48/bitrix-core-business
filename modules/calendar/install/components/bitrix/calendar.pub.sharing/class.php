<?php

use Bitrix\Main;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sharing;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

class CalendarPubSharingComponent extends CBitrixComponent
{
	protected array $allowedActions = [Sharing\Helper::CANCEL, Sharing\Helper::CONFERENCE, Sharing\Helper::ICS];

	protected ?Sharing\Link\Factory $factory = null;

	/**
	 * @param $component
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	protected function setBrowserLanguageForLoc(): void
	{
		$language = $this->getBrowserLanguage();
		$portalLanguage = Loc::getCurrentLang();
		$language = $this->isLanguageAvailable($language) ? $language : $portalLanguage;
		Loc::setCurrentLang($language);
	}

	protected function getBrowserLanguage(): string
	{
		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}

	protected function isLanguageAvailable(string $language): bool
	{
		$defaultLanguages = ['ru', 'de', 'en'];

		$installedLanguages = LanguageTable::getList([
			'select' => ['ID'],
			'filter' => ['=ACTIVE' => 'Y'],
		])->fetchAll();
		$installedLanguages = array_column($installedLanguages, 'ID');

		$availableLanguages = array_unique(array_merge($installedLanguages, $defaultLanguages));

		return in_array($language, $availableLanguages, true);
	}

	/**
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function executeComponent()
	{
		$showAlert = false;
		if (!Main\Loader::includeModule('calendar') || !\Bitrix\Calendar\Sharing\SharingFeature::isEnabled())
		{
			$showAlert = true;
		}

		$link = $this->getLinkInfo($this->arParams['HASH']);
		$this->arResult['LINK'] = $link;

		if (!$link)
		{
			$showAlert = true;
		}

		if ($link)
		{
			$link['type'] = $link['type'] ?? null;
			$link['active'] = $link['active'] ?? null;
			$link['eventId'] = $link['eventId'] ?? null;
			$link['userId'] = $link['userId'] ?? $link['ownerId'] ?? null;

			$owner = Sharing\Helper::getOwnerInfo($link['userId']);
			$this->arResult['OWNER'] = [
				'id' => $owner['id'],
				'name' => htmlspecialcharsbx($owner['name']),
				'lastName' => htmlspecialcharsbx($owner['lastName']),
				'photo' => $owner['photo'],
			];

			$this->setRichLink();
			$this->setBrowserLanguageForLoc();
			Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

			if ($link['type'] === Sharing\Link\Helper::USER_SHARING_TYPE)
			{
				$this->prepareCalendarParams($link);
			}

			else if ($link['type'] === Sharing\Link\Helper::EVENT_SHARING_TYPE && $link['eventId'])
			{
				$this->prepareEventParams($link);

				if (!$this->arResult['EVENT'])
				{
					$showAlert = true;
				}
			}

			else if (
				$link['type'] === Sharing\Link\Helper::CRM_DEAL_SHARING_TYPE
				&& Main\Loader::includeModule('crm')
			)
			{
				$this->prepareCrmDealParams($link);
			}
		}

		if (!empty($this->arResult['OWNER'] ?? null) && !$showAlert)
		{
			return $this->includeComponentTemplate();
		}

		$this->setBrowserLanguageForLoc();
		return $this->includeComponentTemplate('alert');
	}

	/**
	 * @param string|null $hash
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getLinkInfo(?string $hash): ?array
	{
		return $this->getSharingLinkFactory()->getLinkArrayByHash($hash);
	}

	/**
	 * @param array $link
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException|Main\LoaderException
	 */
	protected function prepareCalendarParams(array $link): void
	{
		$userId = $link['userId'];

		$this->arResult['SHARING_USER'] = $this->getSharingUserInfo($userId);
		$this->arResult['PAGE_TITLE'] = $this->getPageTitle(
			$this->arResult['OWNER'],
			Loc::getMessage('CALENDAR_SHARING_COMPONENT_CLASS_CALENDAR_TITLE')
		);
		$this->arResult['CURRENT_LANG'] = Loc::getCurrentLang();

		if ($link['active'] === true)
		{
			$this->prepareAdditionalCalendarParams($link, $userId);
		}

		$this->arResult['BITRIX24_LINK'] = $this->getBitrix24Link();
		$this->arResult['ABUSE_LINK'] = $this->getAbuseLink($link);
	}

	/**
	 * @param array $link
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException|Main\LoaderException
	 */
	protected function prepareCrmDealParams(array $link): void
	{
		$link['userId'] = (int)$link['ownerId'];
		$this->prepareCalendarParams($link);
	}

	/**
	 * @param array $link
	 * @param int $userId
	 * @return void
	 * @throws Main\LoaderException
	 */
	protected function prepareAdditionalCalendarParams(array $link, int $userId): void
	{
		$this->arResult['USER_ACCESSIBILITY'] = $this->getUserAccessibility($userId);
		$this->arResult['TIMEZONE_LIST'] = \CCalendar::GetTimezoneList();

		$this->arResult['CALENDAR_SETTINGS'] = [
			'workTimeStart' => $this->getWorkStart($userId),
			'workTimeEnd' => $this->getWorkEnd($userId),
			'weekHolidays' => explode('|', COption::GetOptionString('calendar', 'week_holidays', 'SA|SU')),
			'yearHolidays' => $this->getYearHolidays(),
			'weekStart' => CCalendar::GetWeekStart(),
			'phoneFeatureEnabled' => Sharing\Helper::isPhoneFeatureEnabled(),
			'mailFeatureEnabled' => Sharing\Helper::isMailFeatureEnabled(),
		];

		$this->arResult['HAS_CONTACT_DATA'] = !empty($link['contactType']) && !empty($link['contactId']);
	}

	/**
	 * @param array $link
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws Main\LoaderException
	 */
	protected function prepareEventParams(array $link): void
	{
		$this->arResult['EVENT'] = $this->getEventById($link);
		$this->arResult['SHARING_USER'] = $this->getSharingUserInfo($link['ownerId']);
		$this->arResult['ACTION'] = $this->getAction();
		$this->arResult['PAGE_TITLE'] = $this->getPageTitle(
			$this->arResult['OWNER'],
			Loc::getMessage('CALENDAR_SHARING_COMPONENT_CLASS_EVENT_TITLE')
		);
		$this->arResult['CURRENT_LANG'] = Loc::getCurrentLang();


		if (isset($link['parentLinkHash']) && $link['parentLinkHash'])
		{
			$parentLink = $this->getLinkInfo($link['parentLinkHash']);

			if ($parentLink && in_array($parentLink['type'], [Sharing\Link\Helper::USER_SHARING_TYPE, Sharing\Link\Helper::CRM_DEAL_SHARING_TYPE], true))
			{
				$this->arResult['PARENT_LINK'] = $parentLink;
				$this->prepareAdditionalCalendarParams($parentLink, $link['ownerId']);
			}
		}

		$this->arResult['BITRIX24_LINK'] = $this->getBitrix24Link();
		$this->arResult['ABUSE_LINK'] = $this->getAbuseLink($link);
	}

	protected function getAbuseLink(array $link): ?string
	{
		$ownerId = $link['userId'] ?? $link['ownerId'];
		$calendarLink = $link['url'];

		return Sharing\Helper::getPageAbuseLink($ownerId, $calendarLink);
	}

	protected function getBitrix24Link(): ?string
	{
		return Sharing\Helper::getBitrix24Link();
	}

	/**
	 * @param int $userId
	 * @return array
	 */
	protected function getUserAccessibility(int $userId): array
	{
		$date = new \Bitrix\Main\Type\Date();
		$arrayKey = $date->format('n') . '.' . $date->format('Y');
		$monthStart = strtotime('first day of this month 00:00:00');
		$monthEnd = strtotime('last day of this month 23:59:59');

		$result = (new Sharing\SharingAccessibilityManager([
			'userId' => $userId,
			'timestampFrom' => $monthStart,
			'timestampTo' => $monthEnd
		]))->getUserAccessibilitySegmentsInUtc();

		return [$arrayKey => $result];
	}

	/**
	 * @param $userId
	 * @return float
	 */
	protected function getWorkStart($userId): float
	{
		$timezoneOffset = \CCalendar::GetCurrentOffsetUTC($userId) / 3600;
		$workStart = (float)COption::GetOptionString('calendar', 'work_time_start', 9);
		$workStart = floor($workStart) + 5 * ($workStart - floor($workStart)) / 3;

		return $workStart - $timezoneOffset;
	}

	/**
	 * @param $userId
	 * @return float
	 */
	protected function getWorkEnd($userId): float
	{
		$timezoneOffset = \CCalendar::GetCurrentOffsetUTC($userId) / 3600;
		$workEnd = (float)COption::GetOptionString('calendar', 'work_time_end', 19);
		$workEnd = floor($workEnd) + 5 * ($workEnd - floor($workEnd)) / 3;

		return $workEnd - $timezoneOffset;
	}

	/**
	 * @return array
	 */
	protected function getYearHolidays(): array
	{
		$result = [];
		$holidays = explode(',', COption::GetOptionString('calendar', 'year_holidays', Loc::getMessage('EC_YEAR_HOLIDAYS_DEFAULT')));

		foreach ($holidays as $day)
		{
			$result[$day] = $day;
		}

		return $result;
	}

	/**
	 * @param array $link
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getEventById(array $link): array
	{
		$event = \Bitrix\Calendar\Internals\EventTable::query()
			->setSelect([
				'DELETED',
				'DATE_FROM',
				'DATE_TO',
				'TZ_FROM',
				'TZ_TO',
				'EVENT_TYPE',
				'DT_SKIP_TIME',
				'MEETING_STATUS',
			])
			->where('PARENT_ID', $link['eventId'])
			->where('OWNER_ID', $link['ownerId'])
			->setLimit(1)
			->exec()->fetch()
		;

		if (!$event)
		{
			return [];
		}

		$eventTsFromUTC = Sharing\Helper::getEventTimestampUTC($event['DATE_FROM'], $event['TZ_FROM']);
		$eventTsToUTC = Sharing\Helper::getEventTimestampUTC($event['DATE_TO'], $event['TZ_TO']);

		if ($event['DT_SKIP_TIME'] === 'Y')
		{
			$eventTsToUTC += \CCalendar::GetDayLen();
		}

		$ownerName = ($owner['name'] ?? '') . ' ' . ($owner['lastName'] ?? '');

		return [
			'id' => $link['eventId'],
			'deleted' => $event['DELETED'],
			'name' => Sharing\SharingEventManager::getSharingEventNameByUserName($ownerName),
			'dateFrom' => $event['DATE_FROM'],
			'dateTo' => $event['DATE_TO'],
			'timezone' => $event['TZ_FROM'],
			'eventType' => $event['EVENT_TYPE'],
			'timestampFromUTC' => $eventTsFromUTC,
			'timestampToUTC' => $eventTsToUTC,
			'meetingStatus' => $event['MEETING_STATUS'],
			'canceledTimestamp' => $link['canceledTimestamp'] ?? null,
			'externalUserName' => $link['externalUserName'] ?? null,
		];
	}

	/**
	 * @param int|null $ownerId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getSharingUserInfo(?int $ownerId = null): array
	{
		$result = [
			'ownerCreated' => false,
			'userId' => null,
			'userName' => '',
			'personalPhone' => '',
			'personalMailbox' => ''
		];
		$currentUserId = \Bitrix\Calendar\Sharing\SharingUser::getInstance()->login();

		if ($currentUserId && $ownerId && (int)$currentUserId === $ownerId)
		{
			$result['ownerCreated'] = true;
			$result['userId'] = $currentUserId;

			return $result;
		}

		if ($this->getOwnerCreated())
		{
			$result['ownerCreated'] = true;
		}

		if ($currentUserId)
		{
			$user = CUser::GetByID($currentUserId)->Fetch();

			$ownerCreated = $result['ownerCreated'];
			$result = [
				'ownerCreated' => $ownerCreated,
				'userId' => $currentUserId,
				'userName' => $user['NAME'],
				'personalPhone' => $user['PERSONAL_PHONE'],
				'personalMailbox' => $user['PERSONAL_MAILBOX'],
			];
		}

		return $result;
	}

	protected function getSharingUserWithAvailableSenders(array $sharingUser, bool $phoneFeatureEnabled, bool $mailFeatureEnabled): array
	{
		if (!$phoneFeatureEnabled)
		{
			$sharingUser['personalPhone'] = '';
		}
		if (!$mailFeatureEnabled)
		{
			$sharingUser['personalMailbox'] = '';
		}

		return $sharingUser;
	}

	protected function getPageTitle(array $owner, ?string $phrase = ''): ?string
	{
		$result = $phrase;

		if ($owner['name'] ?? null)
		{
			$result .= ': ' . $owner['name'];
		}

		if ($owner['lastName'] ?? null)
		{
			$result .= ' ' . $owner['lastName'];
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getAction(): string
	{
		$request = Main\Context::getCurrent()->getRequest();
		$action = (string)$request->getQueryList()->get(Sharing\Helper::ACTION);

		if (in_array($action, $this->allowedActions, true))
		{
			return $action;
		}

		return '';
	}

	protected function getOwnerCreated(): bool
	{
		$request = Main\Context::getCurrent()->getRequest();
		$ownerCreatedParam = (string)$request->getQueryList()->get(Sharing\Helper::OWNER_CREATED);

		return $ownerCreatedParam === 'Y';
	}

	protected function getSharingLinkFactory(): Sharing\Link\Factory
	{
		if (!$this->factory)
		{
			$this->factory = new Sharing\Link\Factory();
		}

		return $this->factory;
	}

	protected function setRichLink(): bool
	{
		Main\Page\Asset::getInstance()->addString(
			'<meta name="robots" content="noindex, nofollow, noarchive, nocache" />'
		);

		$siteUri = \CCalendar::GetServerPath();

		if (!empty($this->arResult['OWNER'] ?? null) && $this->arResult['OWNER']['name'])
		{
			$title = $this->arResult['OWNER']['name'];

			if ($this->arResult['OWNER']['lastName'])
			{
				$title .= ' ' . $this->arResult['OWNER']['lastName'];
			}

			$title .= ': ' . Loc::getMessage('CALENDAR_SHARING_COMPONENT_OG_TITLE');
		}
		else
		{
			$title = Loc::getMessage('CALENDAR_SHARING_COMPONENT_OG_TITLE');
		}

		Main\Page\Asset::getInstance()->addString(
			'<meta name="description" content="' . Loc::getMessage('CALENDAR_SHARING_COMPONENT_OG_DESCRIPTION') . '" />'
		);

		Main\Page\Asset::getInstance()->addString(
			'<meta property="og:title" content="' . $title . '" />'
		);
		Main\Page\Asset::getInstance()->addString(
			'<meta property="og:description" content="' . Loc::getMessage('CALENDAR_SHARING_COMPONENT_OG_DESCRIPTION') . '" />'
		);
		Main\Page\Asset::getInstance()->addString(
			'<meta property="og:url" content="' . ($this->arResult['LINK'] ? $this->arResult['LINK']['url'] : $siteUri) . '" />'
		);
		Main\Page\Asset::getInstance()->addString(
			'<meta property="og:type" content="website" />'
		);

		if (!empty($this->arResult['OWNER'] ?? null) && $this->arResult['OWNER']['photo'])
		{
			$imagePath = $this->arResult['OWNER']['photo'];
		}
		else
		{
			$imagePath = $this->getPath() . '/images/og_image.png';
		}

		Main\Page\Asset::getInstance()->addString(
			'<meta property="og:image" content="' . $imagePath . '" />'
		);

		return true;
	}
}