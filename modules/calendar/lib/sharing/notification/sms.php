<?php
namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\Sharing;
use Bitrix\Main\Loader;
use Bitrix\Notifications;
use Bitrix\Main\PhoneNumber;

class Sms extends Service
{
	private const TEMPLATE_Q = 'SHARING_EVENT_INVITE';
	private const TEMPLATE_Y = 'SHARING_EVENT_ACCEPTED';
	private const TEMPLATE_N = 'SHARING_EVENT_DECLINED';
	private const TEMPLATE_N_NO_LINK = 'SHARING_EVENT_DECLINED_2';

	/**
	 * This method includes *notifications* module and checks account
	 *
	 * returns **true** if notifications service is available, otherwise returns **false**
	 */
	public function includeNotificationsModule(): bool
	{
		if (!Loader::includeModule('notifications'))
		{
			return false;
		}

		return Notifications\Account::isServiceAvailable() && Notifications\Account::isConnected();
	}

	public function notifyAboutMeetingStatus(string $to): void
	{
		if (!$this->includeNotificationsModule())
		{
			return;
		}

		$parsedPhone = PhoneNumber\Parser::getInstance()->parse($to);
		if (!$parsedPhone->isValid() || mb_strtolower($parsedPhone->getCountry()) !== 'ru')
		{
			return;
		}
		$phoneNumberE164 = $parsedPhone->format(PhoneNumber\Format::E164);

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

		Notifications\Model\Message::create([
			'PHONE_NUMBER' => $phoneNumberE164,
			'TEMPLATE_CODE' => $templateCode,
			'LANGUAGE_ID' => LANGUAGE_ID,
			'PLACEHOLDERS' => $this->getPlaceholders($templateCode, $owner, $calendarLink),
		])->enqueue();
	}

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

	protected function getPlaceholders(string $templateCode, array $owner, ?string $calendarLink): array
	{
		$eventName = $this->event->getName();
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