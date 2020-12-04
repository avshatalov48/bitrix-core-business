<?
namespace Bitrix\Calendar\Integration\Bitrix24;

class Limitation
{
	const
		ICAL_EVENT_LIMIT_OPTION = "event_with_email_guest_amount";
	/**
	 * Returns limitations for bitrix 24 for unpaid license
	 * @return int (-1 if no limitation)
	 */
	public static function getEventWithEmailGuestLimit()
	{
		$limit = -1;
		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$limit = \Bitrix\Bitrix24\Feature::getVariable('calendar_events_with_email_guests');
			if (is_null($limit)
				&& !\CBitrix24::IsLicensePaid()
				&& !\CBitrix24::IsNfrLicense()
				&& !\CBitrix24::IsDemoLicense())
			{
				$limit = 10;
			}
		}

		return $limit;
	}

	public static function getCountEventWithEmailGuestAmount()
	{
		return \COption::GetOptionInt('calendar', self::ICAL_EVENT_LIMIT_OPTION, 0);
	}

	public static function setCountEventWithEmailGuestAmount($value = 0)
	{
		return \COption::SetOptionInt('calendar', self::ICAL_EVENT_LIMIT_OPTION, $value);
	}

	public static function increaseEventWithEmailGuestAmount()
	{
		return \COption::SetOptionInt('calendar', self::ICAL_EVENT_LIMIT_OPTION, self::getCountEventWithEmailGuestAmount() + 1);
	}

	public static function isEventWithEmailGuestAllowed()
	{
		$limit = self::getEventWithEmailGuestLimit();
		return $limit === -1 || self::getCountEventWithEmailGuestAmount() < $limit;
	}
}