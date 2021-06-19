<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CalendarEventPreviewComponent extends \CBitrixComponent
{
	protected function prepareParams()
	{
		$this->arParams['AVATAR_SIZE'] = $this->arParams['AVATAR_SIZE'] ?: 24;
		if(Main\Loader::includeModule('socialnetwork'))
		{
			CSocNetLogComponent::processDateTimeFormatParams($this->arParams);
		}
	}

	protected function prepareData()
	{
		$events = \CCalendarEvent::getList(
			[
				'arFilter' => [
					'ID' => $this->arParams['eventId'],
					'DELETED' => false
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			]
		);

		if (!$events || !is_array($events[0]))
		{
			return false;
		}

		$this->arResult['EVENT'] = $events[0];
		$this->arResult['EVENT']['ACCEPTED_ATTENDEES'] = [];
		$attendeesIndex = 0;
		$this->arResult['EVENT']['ACCEPTED_ATTENDEES_LIMIT'] = 3;

		if (!empty($this->arResult['EVENT']['ATTENDEE_LIST']))
		{
			$userIndex = \CCalendarEvent::getUsersDetails(array_map(function($attendee) { return (int)$attendee['id']; }, $this->arResult['EVENT']['ATTENDEE_LIST']), [
				'fillAvatar' => false
			]);
			foreach ($this->arResult['EVENT']['ATTENDEE_LIST'] as $attendee)
			{
				if (!isset($userIndex[$attendee['id']]))
				{
					continue;
				}

				if (
					$attendee['status'] === 'Y'
					|| $attendee['status'] === 'H'
				)
				{
					$attendeesIndex++;

					if ($attendeesIndex <= $this->arResult['EVENT']['ACCEPTED_ATTENDEES_LIMIT'])
					{
						$this->arResult['EVENT']['ACCEPTED_ATTENDEES'][] = $userIndex[$attendee["id"]];
					}
				}
			}
		}
		$this->arResult['EVENT']['ACCEPTED_ATTENDEES_COUNT'] = $attendeesIndex;


		$fromTs = \CCalendar::Timestamp($this->arResult['EVENT']['DATE_FROM']);
		$toTs = \CCalendar::Timestamp($this->arResult['EVENT']['DATE_TO']);
		if ($this->arResult['EVENT']['DT_SKIP_TIME'] === 'Y')
		{
			$toTs += \CCalendar::DAY_LENGTH;
		}
		else
		{
			$fromTs -= $this->arResult['EVENT']['~USER_OFFSET_FROM'];
			$toTs -= $this->arResult['EVENT']['~USER_OFFSET_TO'];
		}

		$this->arResult['EVENT']['~FROM_TO_HTML'] = \CCalendar::GetFromToHtml(
			$fromTs,
			$toTs,
			$this->arResult['EVENT']['DT_SKIP_TIME'] === 'Y',
			$this->arResult['EVENT']['DT_LENGTH']
		);

/*
		if(Main\Loader::includeModule('socialnetwork'))
		{
			$this->arResult["TASK"]["CREATED_DATE_FORMATTED"] = CSocNetLogComponent::getDateTimeFormatted(
					MakeTimeStamp($this->arResult["TASK"]["CREATED_DATE"]),
					array(
							"DATE_TIME_FORMAT" => $this->arParams["DATE_TIME_FORMAT"],
							"DATE_TIME_FORMAT_WITHOUT_YEAR" => $this->arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
							"TIME_FORMAT" => $this->arParams["TIME_FORMAT"]
					));
		}
		else
		{
			$this->arResult["TASK"]["CREATED_DATE_FORMATTED"] = FormatDateFromDB($this->arResult["TASK"]["CREATED_DATE"], "SHORT");
		}
*/
		return true;
	}

	public function executeComponent()
	{
		$this->prepareParams();
		if($this->prepareData())
		{
			$this->includeComponentTemplate();
		}
	}
}
