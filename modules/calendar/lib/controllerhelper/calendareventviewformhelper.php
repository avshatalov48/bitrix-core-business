<?php

namespace Bitrix\Calendar\ControllerHelper;

use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Localization\Loc;
use CCalendar;
use CCalendarEvent;
use COption;

class CalendarEventViewFormHelper
{
	//get parameters
	public static function getTimezoneHint($userId, $event): string
	{
		$skipTime = $event['DT_SKIP_TIME'] === "Y";
		$timezoneHint = '';
		if (
			!$skipTime
			&& (
				(int)$event['~USER_OFFSET_FROM'] !== 0
				|| (int)$event['~USER_OFFSET_TO'] !== 0
				|| $event['TZ_FROM'] !== $event['TZ_TO']
				|| $event['TZ_FROM'] !== CCalendar::GetUserTimezoneName($userId)
			)
		)
		{
			if ($event['TZ_FROM'] === $event['TZ_TO'])
			{
				$timezoneHint = CCalendar::GetFromToHtml(
					CCalendar::Timestamp($event['DATE_FROM']),
					CCalendar::Timestamp($event['DATE_TO']),
					false,
					$event['DT_LENGTH']
				);
				if ($event['TZ_FROM'])
				{
					$timezoneHint .= ' (' . $event['TZ_FROM'] . ')';
				}
			}
			else
			{
				$timezoneHint = Loc::getMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => $event['DATE_FROM'].' ('.$event['TZ_FROM'].')', '#DATE_TO#' => $event['DATE_TO'].' ('.$event['TZ_TO'].')'));
			}
		}
		return $timezoneHint;
	}

	public static function getFromToHtml(array $event): string
	{
		$skipTime = $event['DT_SKIP_TIME'] === "Y";
		$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
		$toTs = CCalendar::Timestamp($event['DATE_TO']);
		if ($skipTime)
		{
			$toTs += CCalendar::DAY_LENGTH;
		}
		else
		{
			$fromTs -= $event['~USER_OFFSET_FROM'];
			$toTs -= $event['~USER_OFFSET_TO'];
		}
		return CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $event['DT_LENGTH']);
	}

	public static function getMeetingCreator(array $event): array
	{
		$meetingCreator = [];
		if (
			$event['IS_MEETING']
			&& $event['MEETING']['MEETING_CREATOR']
			&& $event['MEETING']['MEETING_CREATOR'] !== $event['MEETING_HOST']
		)
		{
			$meetingCreator = CCalendar::GetUser($event['MEETING']['MEETING_CREATOR'], true);
			$meetingCreator['DISPLAY_NAME'] = CCalendar::GetUserName($meetingCreator);
			$meetingCreator['URL'] = CCalendar::GetUserUrl(
				$meetingCreator["ID"],
				$meetingCreator["PATH_TO_USER"] ?? null
			);
		}
		return $meetingCreator;
	}

	//get components
	public static function getCrmView(array $event): Component
	{
		return new Component(
			"bitrix:system.field.view",
			$event['UF_CRM_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
			array("arUserField" => $event['UF_CRM_CAL_EVENT']),
			array("HIDE_ICONS"=>"Y")
		);
	}

	public static function getFilesView(array $event): Component
	{
		return new Component(
			"bitrix:system.field.view",
			$event['UF_WEBDAV_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
			array("arUserField" => $event['UF_WEBDAV_CAL_EVENT']),
			array("HIDE_ICONS"=>"Y")
		);
	}

	public static function getCommentsView(array $event): Component
	{
		$userId = CCalendar::GetCurUserId();
		if (
			$userId === (int)$event['CREATED_BY']
			&& ((int)$event['PARENT_ID'] === (int)$event['ID'] || !$event['PARENT_ID'])
		)
		{
			$permission = "Y";
		}
		else
		{
			$permission = 'M';
		}
		$set = CCalendar::GetSettings();
		$eventCommentId = $event['PARENT_ID'] ?: $event['ID'];

		return new Component(
			"bitrix:forum.comments", "bitrix24", [
			"FORUM_ID" => $set['forum_id'],
			"ENTITY_TYPE" => "EV",
			"ENTITY_ID" => $eventCommentId,
			"ENTITY_XML_ID" => $event['ENTITY_XML_ID'],
			"PERMISSION" => $permission,
			"URL_TEMPLATES_PROFILE_VIEW" => $set['path_to_user'],
			"SHOW_RATING" => COption::GetOptionString('main', 'rating_vote_show', 'N'),
			"SHOW_LINK_TO_MESSAGE" => "N",
			"BIND_VIEWER" => "Y"
		],
			['HIDE_ICONS' => 'Y']
		);
	}

}
