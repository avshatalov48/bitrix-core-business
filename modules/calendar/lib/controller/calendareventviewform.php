<?php

namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Integration\AI;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;
use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Security\Sign\Signer;

class CalendarEventViewForm extends Controller
{

	public function getCalendarViewSliderParamsAction(int $entryId, string $dateFrom, int $timezoneOffset = 0): array
	{
		$responseParams = [];
		$userId = \CCalendar::GetCurUserId();

		if (
			Loader::includeModule('intranet')
			&& !ToolsManager::getInstance()->checkAvailabilityByToolId('calendar')
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND')));

			return [
				'isAvailable' => false,
			];
		}

		if ($entryId)
		{
			$entry = \CCalendarEvent::getEventForViewInterface($entryId, [
				'eventDate' => $dateFrom,
				'timezoneOffset' => $timezoneOffset,
				'userId' => $userId
			]);
		}
		else
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_01'));
			return [];
		}

		if (!$entry || !$entry['ID'])
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_02'));
			return [];
		}

		$responseParams['userId'] = $userId;
		$responseParams['userTimezone'] = \CCalendar::GetUserTimezoneName($userId);
		$responseParams['entry'] = $entry;
		$responseParams['userIndex'] = \CCalendarEvent::getUserIndex();
		$responseParams['userSettings'] = UserSettings::get($userId);
		$responseParams['plannerFeatureEnabled'] = Bitrix24Manager::isPlannerFeatureEnabled();
		$responseParams['entryUrl'] = \CHTTP::urlAddParams(
			\CCalendar::GetPath($entry['CAL_TYPE'], $entry['OWNER_ID'], true),
			[
				'EVENT_ID' => (int)$entry['ID'],
				'EVENT_DATE' => urlencode($entry['DATE_FROM'])
			]
		);
		$responseParams['dayOfWeekMonthFormat'] = (
			\Bitrix\Main\Context::getCurrent()
				->getCulture()
				->getDayOfWeekMonthFormat()
		);

		$sections = \CCalendarSect::GetList([
			'arFilter' => [
				'ID' => $entry['SECTION_ID'],
				'ACTIVE' => 'Y',
			],
			'checkPermissions' => false,
			'getPermissions' => true
		]);
		$responseParams['section'] = $sections[0] ?? null;

		if (!$responseParams['section'])
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'SECTION_NOT_FOUND'));
			return [];
		}

		$params = array_merge([
			'event' => $entry,
			'type' => \CCalendar::GetType(),
			'bIntranet' => \CCalendar::IsIntranetEnabled(),
			'bSocNet' => \CCalendar::IsSocNet(),
			'AVATAR_SIZE' => 21,
		], $responseParams);

		$userId = \CCalendar::GetCurUserId();
		$event = $params['event'];

		$timezoneHint = Util::getTimezoneHint($userId, $event);

		$UF = \CCalendarEvent::GetEventUserFields($event);

		if (isset($event['UF_CRM_CAL_EVENT']))
		{
			$event['UF_CRM_CAL_EVENT'] = $UF['UF_CRM_CAL_EVENT'];
			if (empty($event['UF_CRM_CAL_EVENT']['VALUE']))
			{
				$event['UF_CRM_CAL_EVENT'] = false;
			}
		}

		if (isset($event['UF_WEBDAV_CAL_EVENT']))
		{
			$event['UF_WEBDAV_CAL_EVENT'] = $UF['UF_WEBDAV_CAL_EVENT'];
			if(empty($event['UF_WEBDAV_CAL_EVENT']['VALUE']))
			{
				$event['UF_WEBDAV_CAL_EVENT'] = false;
			}
		}

		$event['REMIND'] = \CCalendarReminder::GetTextReminders($event['REMIND'] ?? []);

		$event['permissions'] = \CCalendarEvent::getEventPermissions($event, $userId);

		$curUserStatus = '';
		$userId = \CCalendar::GetCurUserId();

		$viewComments = $event['permissions']['view_comments'];

		//get meeting host and attendees
		$meetingHost = false;
		if ($event['IS_MEETING'])
		{
			$userIndex = \CCalendarEvent::getUserIndex();
			$attendees = ['y' => [], 'n' => [], 'q' => [], 'i' => []];

			if (isset($event['ATTENDEE_LIST']) && is_array($event['ATTENDEE_LIST']))
			{
				foreach ($event['ATTENDEE_LIST'] as $attendee)
				{
					if ($userId === (int)$attendee['id'])
					{
						$curUserStatus = $attendee['status'];
						$viewComments = true;
					}

					$status = (mb_strtolower($attendee['status']) === 'h' || empty($attendee['status']))
						? 'y'
						: $attendee['status']
					;
					$attendees[mb_strtolower($status)][] = $userIndex[$attendee['id']];
					if ($attendee['status'] === 'H')
					{
						$meetingHost = $userIndex[$attendee['id']];
						$meetingHost['ID'] = $attendee['id'];
					}
				}
			}
		}

		if (!$meetingHost && isset($event['MEETING_HOST']))
		{
			$meetingHost = \CCalendar::GetUser($event['MEETING_HOST'], true);
		}

		if ($meetingHost && is_array($meetingHost))
		{
			$meetingHost['DISPLAY_NAME'] = \CCalendar::GetUserName($meetingHost);
			if (!isset($meetingHost['AVATAR']))
			{
				$meetingHost['AVATAR'] = \CCalendar::GetUserAvatarSrc($meetingHost);
			}
			$meetingHost['URL'] = \CCalendar::GetUserUrl($meetingHost["ID"], ($params["PATH_TO_USER"] ?? ''));
		}

		$params['id'] = 'calendar_view_slider_'.mt_rand();
		$params['event'] = $event;
		$params['eventId'] = $event['ID'];
		$params['parentId'] = $event['PARENT_ID'];
		$params['name'] = $event['NAME'];
		$params['fromToHtml'] = $this->getFromToHtml($event);
		$params['timezoneHint'] = $timezoneHint;
		$params['isMeeting'] = $event['IS_MEETING'];
		$params['isRemind'] = $event['REMIND'];
		$params['isRrule'] = $event['RRULE'];
		$params['rruleDescription'] = \CCalendarEvent::GetRRULEDescription($event, false);

		$params['avatarSize'] = 34;
		$params['attendees'] = $attendees ?? [];

		$params['curUserStatus'] = $curUserStatus;
		$params['meetingHost'] = $meetingHost;
		$params['meetingHostDisplayName'] = $meetingHost['DISPLAY_NAME'] ?? null;
		$params['meetingHostWorkPosition'] = htmlspecialcharsbx($meetingHost['WORK_POSITION'] ?? null);

		$meetingCreator = $this->getMeetingCreator($event);
		$params['meetingCreatorUrl'] = $meetingCreator['URL'] ?? null;
		$params['meetingCreatorDisplayName'] = $meetingCreator['DISPLAY_NAME'] ?? null;

		$params['isHighImportance'] = $event['IMPORTANCE'] === 'high';
		$params['description'] = $event['~DESCRIPTION'] ?? null;

		$params['isWebdavEvent'] = $event['UF_WEBDAV_CAL_EVENT'] ?? null;
		$params['isCrmEvent'] = $event['UF_CRM_CAL_EVENT'] ?? null;

		$params['accessibility'] = $event['ACCESSIBILITY'];
		$params['isIntranetEnabled'] = \CCalendar::IsIntranetEnabled();
		$params['isPrivate'] = $event['PRIVATE_EVENT'];

		$params['location'] = htmlspecialcharsbx(\CCalendar::GetTextLocation($event['LOCATION'] ?? null));

		$params['canEditCalendar'] = $event['permissions']['edit'];
		$params['canDeleteEvent'] = $event['permissions']['delete'];

		$params['showComments'] = $viewComments;

		//views
		if (!empty($params['isWebdavEvent']))
		{
			$params['filesView'] = $this->getFilesView($event)->getContent();
		}
		if (!empty($params['isCrmEvent']))
		{
			$params['crmView'] = $this->getCrmView($event)->getContent();
		}

		$signedEvent = [
			'UF_CRM_CAL_EVENT' => $params['event']['UF_CRM_CAL_EVENT'] ?? null,
			'UF_WEBDAV_CAL_EVENT' => $params['event']['UF_WEBDAV_CAL_EVENT'] ?? null,
			'PARENT_ID' => $params['event']['PARENT_ID'],
			'ID' => $params['event']['ID'],
			'CREATED_BY' => $params['event']['CREATED_BY'],
		];

		$params['event'] = \CCalendarEvent::FixCommentsIfEventIsBroken($params['event']); //TODO: remove 30.06.2025

		if (
			isset($params['event']['RELATIONS']['COMMENT_XML_ID'])
			&& $params['event']['RELATIONS']['COMMENT_XML_ID']
		)
		{
			$signedEvent['ENTITY_XML_ID'] = $params['event']['RELATIONS']['COMMENT_XML_ID'];
		}
		else
		{
			$signedEvent['ENTITY_XML_ID'] = \CCalendarEvent::GetEventCommentXmlId($params['event']);
		}

		$params['signedEvent'] = (new Signer())->sign(Json::encode($signedEvent));

		return $params;
	}

	//get components actions
	public function getCrmViewAction(string $signedEvent): ?Component
	{
		try
		{
			$event = Json::decode((new Signer())->unsign($signedEvent));
		}
		catch (\Exception $e)
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_01'));
			return null;
		}

		return $this->getCrmView($event);
	}

	public function getFilesViewAction(string $signedEvent): ?Component
	{
		try
		{
			$event = Json::decode((new Signer())->unsign($signedEvent));
		}
		catch (\Exception $e)
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_01'));
			return null;
		}

		return $this->getFilesView($event);
	}

	public function getCommentsViewAction(string $signedEvent): ?Component
	{
		try
		{
			$event = Json::decode((new Signer())->unsign($signedEvent));
		}
		catch (\Exception $e)
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_01'));
			return null;
		}

		return $this->getCommentsView($event);
	}

	private function getFromToHtml(array $event): string
	{
		$skipTime = $event['DT_SKIP_TIME'] === "Y";
		$fromTs = \CCalendar::Timestamp($event['DATE_FROM']);
		$toTs = \CCalendar::Timestamp($event['DATE_TO']);
		if ($skipTime)
		{
			$toTs += \CCalendar::DAY_LENGTH;
		}
		else
		{
			$fromTs -= $event['~USER_OFFSET_FROM'];
			$toTs -= $event['~USER_OFFSET_TO'];
		}

		return \CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $event['DT_LENGTH']);
	}

	private function getMeetingCreator(array $event): array
	{
		$meetingCreator = [];
		if (
			$event['IS_MEETING']
			&& $event['MEETING']['MEETING_CREATOR']
			&& $event['MEETING']['MEETING_CREATOR'] !== $event['MEETING_HOST']
		)
		{
			$meetingCreator = \CCalendar::GetUser($event['MEETING']['MEETING_CREATOR'], true);
			$meetingCreator['DISPLAY_NAME'] = \CCalendar::GetUserName($meetingCreator);
			$meetingCreator['URL'] = \CCalendar::GetUserUrl(
				$meetingCreator["ID"],
				$meetingCreator["PATH_TO_USER"] ?? null
			);
		}
		return $meetingCreator;
	}

	private function getCrmView(array $event): Component
	{
		return new Component(
			"bitrix:system.field.view",
			$event['UF_CRM_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
			array("arUserField" => $event['UF_CRM_CAL_EVENT']),
			array("HIDE_ICONS"=>"Y")
		);
	}

	private function getFilesView(array $event): Component
	{
		return new Component(
			"bitrix:system.field.view",
			$event['UF_WEBDAV_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
			array("arUserField" => $event['UF_WEBDAV_CAL_EVENT']),
			array("HIDE_ICONS"=>"Y")
		);
	}

	private function getCommentsView(array $event): Component
	{
		$userId = \CCalendar::GetCurUserId();
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
		$set = \CCalendar::GetSettings();
		$eventCommentId = $event['PARENT_ID'] ?: $event['ID'];

		return new Component(
			"bitrix:forum.comments", "bitrix24", [
			"FORUM_ID" => $set['forum_id'],
			"ENTITY_TYPE" => "EV",
			"ENTITY_ID" => $eventCommentId,
			"ENTITY_XML_ID" => $event['ENTITY_XML_ID'],
			"PERMISSION" => $permission,
			"URL_TEMPLATES_PROFILE_VIEW" => $set['path_to_user'],
			"SHOW_RATING" => \COption::GetOptionString('main', 'rating_vote_show', 'N'),
			"SHOW_LINK_TO_MESSAGE" => "N",
			"BIND_VIEWER" => "Y",
			'LHE' => [
				'isCopilotImageEnabledBySettings' => AI\Settings::isImageCommentAvailable(),
				'isCopilotTextEnabledBySettings' => AI\Settings::isTextCommentAvailable(),
				'copilotParams' => [
					'moduleId' => 'calendar',
					'contextId' => 'calendar_comments_' . $event['ENTITY_XML_ID'],
					'category' => 'calendar_comments',
				],
			],
		],
			['HIDE_ICONS' => 'Y']
		);
	}

}
