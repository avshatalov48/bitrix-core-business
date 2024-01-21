<?php

namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\Sharing;
use Bitrix\Main\Loader;
use Bitrix\Notifications;
use Bitrix\Main\PhoneNumber;

class NotificationService extends Service
{
	private const ALLOWED_COUNTRY_CODES = ['ru'];

	private const TEMPLATE_Q = 'SHARING_EVENT_INVITE';
	private const TEMPLATE_Y = 'SHARING_EVENT_ACCEPTED';
	private const TEMPLATE_N = 'SHARING_EVENT_DECLINED';
	private const TEMPLATE_N_NO_LINK = 'SHARING_EVENT_DECLINED_2';
	private const TEMPLATE_CRM_SHARING_AUTO_ACCEPTED = 'CRM_SHARING_AUTO_ACCEPTED';

	/**
	 * This method includes *notifications* module and checks account
	 *
	 * returns **true** if notifications service is available, otherwise returns **false**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function includeNotificationsModule(): bool
	{
		if (\CCalendar::IsBitrix24() && in_array(\CBitrix24::getPortalZone(), self::ALLOWED_COUNTRY_CODES))
		{
			return false;
		}

		if (!Loader::includeModule('notifications'))
		{
			return false;
		}

		return Notifications\Account::isServiceAvailable() && Notifications\Account::isConnected();
	}

	/**
	 * @param string $to
	 * @return bool
	 */
	public function notifyAboutMeetingStatus(string $to): bool
	{
		$owner = $this->getOwner();
		$templateCode = $this->getTemplateCode($owner);
		$calendarLink = null;
		if ($templateCode === self::TEMPLATE_N)
		{
			$calendarLink = $this->getShortCalendarLink();
			if (is_null($calendarLink))
			{
				$templateCode = self::TEMPLATE_N_NO_LINK;
			}
		}
		$placeholders = $this->getPlaceholders($templateCode, $owner, $calendarLink);

		return $this->sendMessage($to, $templateCode, $placeholders);
	}

	public function notifyAboutSharingEventEdit(string $to): bool
	{
		//TODO: add logic
		return true;
	}

	/**
	 * @param string $to
	 * @return bool
	 */
	public function sendCrmSharingAutoAccepted(string $to): bool
	{
		$manager = Sharing\Helper::getOwnerInfo($this->crmDealLink->getOwnerId());
		$placeholders = [
			// for whatsapp
			'MANAGER_NAME' => Sharing\Helper::getPersonFullNameLoc($manager['name'], $manager['lastName']),
			'DATE' => Sharing\Helper::formatDate($this->event->getStart()),
			'EVENT_URL' => Sharing\Helper::getShortUrl($this->eventLink->getUrl()),

			// for sms
			'DATE_SHORT' => Sharing\Helper::formatDateShort($this->event->getStart()),
		];

		return $this->sendMessage($to, self::TEMPLATE_CRM_SHARING_AUTO_ACCEPTED, $placeholders);
	}

	/**
	 * @param string $phoneNumber
	 * @param string $templateCode
	 * @param array $placeholders
	 * @return bool
	 */
	protected function sendMessage(string $phoneNumber, string $templateCode, array $placeholders): bool
	{
		if (!self::includeNotificationsModule())
		{
			return false;
		}

		$parsedPhone = PhoneNumber\Parser::getInstance()->parse($phoneNumber);
		$countryCode = mb_strtolower($parsedPhone->getCountry());
		if (!$parsedPhone->isValid() || in_array($countryCode, self::ALLOWED_COUNTRY_CODES, true))
		{
			return false;
		}
		$phoneNumberE164 = $parsedPhone->format(PhoneNumber\Format::E164);

		return Notifications\Model\Message::create([
			'PHONE_NUMBER' => $phoneNumberE164,
			'TEMPLATE_CODE' => $templateCode,
			'LANGUAGE_ID' => LANGUAGE_ID,
			'PLACEHOLDERS' => $placeholders,
		])->enqueue()->isSuccess();
	}

	/**
	 * @param array $owner
	 * @return string
	 */
	protected function getTemplateCode(array $owner): string
	{
		$templateCode = self::TEMPLATE_Q;

		if ($owner['STATUS'] === 'Y')
		{
			$templateCode = self::TEMPLATE_Y;
		}

		if ($owner['STATUS'] === 'N')
		{
			$templateCode = self::TEMPLATE_N;
		}

		return $templateCode;
	}

	/**
	 * @param string $templateCode
	 * @param array $owner
	 * @param string|null $calendarLink
	 * @return array
	 */
	protected function getPlaceholders(string $templateCode, array $owner, ?string $calendarLink): array
	{
		$eventName = Sharing\SharingEventManager::getSharingEventNameByUserName($owner['NAME']);
		$eventDateTime = $this->getEventFormattedDateTime();
		$calendarOwner = $owner['NAME'];
		$eventLink = Sharing\Helper::getShortUrl($this->eventLink->getUrl());

		if ($templateCode === self::TEMPLATE_Q)
		{
			return [
				'EVENT_NAME' => $eventName,
				'DATE' => $eventDateTime,
				'NAME' => $calendarOwner,
				'URL' => $eventLink,
				'URL_EVENT' => $eventLink, // sms parameter
			];
		}
		if ($templateCode === self::TEMPLATE_Y)
		{
			return [
				'EVENT_NAME' => $eventName,
				'DATE' => $eventDateTime,
				'NAME' => $calendarOwner,
				'URL' => $eventLink,
				'URL_EVENT' => $eventLink, // sms parameter
			];
		}
		if ($templateCode === self::TEMPLATE_N)
		{
			return [
				'EVENT_NAME' => $eventName,
				'DATE' => $eventDateTime,
				'NAME' => $calendarOwner,
				'URL' => $calendarLink,
				'URL_EVENT' => $eventLink, // sms parameter
			];
		}
		if ($templateCode === self::TEMPLATE_N_NO_LINK)
		{
			return [
				'EVENT_NAME' => $eventName,
				'DATE' => $eventDateTime,
				'NAME' => $calendarOwner,
				'URL_EVENT' => $eventLink, // sms parameter
			];
		}
		return [];
	}

	/**
	 * @return string|null
	 */
	protected function getShortCalendarLink(): ?string
	{
		$calendarLink = $this->getCalendarLink();
		if (!is_null($calendarLink))
		{
			return Sharing\Helper::getShortUrl($this->getCalendarLink());
		}

		return null;
	}
}