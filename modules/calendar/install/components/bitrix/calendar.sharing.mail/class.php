<?php

use Bitrix\Calendar\Sharing;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * mail template for
 * 'EVENT_NAME' => 'CALENDAR_SHARING'
 */
class CalendarSharingMailComponent extends CBitrixComponent
{
	protected const STATUS_COLOR_DEFAULT = '#959CA4';
	protected const STATUS_COLOR_Y = '#8DBB00';
	protected const STATUS_COLOR_N = '#F4433E';

	protected const CALENDAR_COLOR_DEFAULT = '#2FC6F6';
	protected const CALENDAR_COLOR_N = '#FF5752';

	public function executeComponent()
	{
		$this->arParams['COMPONENT_PATH'] = $this->getPath();
		$this->prepareStatusColors($this->arParams['STATUS']);
		$this->prepareStatusPhrases($this->arParams['STATUS']);
		$this->prepareAttendees();

		if (empty($this->arParams['OWNER_PHOTO']))
		{
			$this->arParams['OWNER_PHOTO'] = $this->arParams['COMPONENT_PATH'] . '/templates/.default/images/ui-user.png';
		}

		$this->includeComponentTemplate();
	}

	protected function prepareStatusColors($status): void
	{
		$statusColor = self::STATUS_COLOR_DEFAULT;
		$path = $this->getPath();
		$mailBackground = "$path/templates/.default/images/calendar-sharing-email-bg-success.jpg";
		$calendarColor = self::CALENDAR_COLOR_DEFAULT;

		if ($status === 'Y')
		{
			$statusColor = self::STATUS_COLOR_Y;
		}

		if ($status === 'N')
		{
			$statusColor = self::STATUS_COLOR_N;
			$mailBackground = "$path/templates/.default/images/calendar-sharing-email-bg-error.jpg";
			$calendarColor = self::CALENDAR_COLOR_N;
		}

		$this->arParams['STATUS_COLOR'] = $statusColor;
		$this->arParams['MAIL_BACKGROUND'] = $mailBackground;
		$this->arParams['CALENDAR_COLOR'] = $calendarColor;
	}

	protected function prepareStatusPhrases($status): void
	{
		$this->arParams['MESSAGE_TITLE'] = Loc::getMessage("CALENDAR_SHARING_MAIL_TITLE_{$status}");
		$this->arParams['OWNER_STATUS'] = Sharing\Helper::getStatusLoc($status);
	}

	protected function prepareAttendees(): void
	{
		$this->arParams['ATTENDEES'] = [];
	}

}
