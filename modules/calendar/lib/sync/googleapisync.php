<?
namespace Bitrix\Calendar\Sync;

use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use \Bitrix\Main\Web;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\Rooms;
use CDavConnection;

/**
 * @deprecated
 * Class GoogleApiSync
 * @package Bitrix\Calendar\Sync
 */
final class GoogleApiSync
{
	const MAXIMUM_CONNECTIONS_TO_SYNC = 3;
	const ONE_DAY = 86400; //60*60*24;
	const CHANNEL_EXPIRATION = 604800; //60*60**24*7
	const CONNECTION_CHANNEL_TYPE = 'BX_CONNECTION';
	const SECTION_CHANNEL_TYPE = 'BX_SECTION';
	const SECTION_CONNECTION_CHANNEL_TYPE = 'SECTION_CONNECTION';
	const SYNC_EVENTS_LIMIT = 50;
	const SYNC_EVENTS_DATE_INTERVAL = '-1 months';
	const DEFAULT_TIMEZONE = 'UTC';
	const DATE_TIME_FORMAT = 'Y-m-d\TH:i:sP';
	public const END_OF_DATE = "01.01.2038";
	public const EXTERNAL_LINK = 'https://www.bitrix24.com/controller/google_calendar_push.php?target_host=';

	/**
	 * @var GoogleApiTransport
	 */
	private $syncTransport;
	private $nextSyncToken = '',
			$defaultTimezone = self::DEFAULT_TIMEZONE,
			$userId = 0,
			$calendarList = array(),
			$defaultReminderData = array(),
			$calendarColors = false,
			$eventColors = false,
			$eventMapping = array(
				'DAV_XML_ID'	=>	'iCalUID',
				'NAME'			=>	'summary',
//				'DESCRIPTION'	=>	'description',
				'CAL_DAV_LABEL'	=>	'etag'
			);
	/**
	 * @var int
	 */
	private $connectionId;
	/**
	 * @var string
	 */
	private $nextPageToken = '';

	/**
	 * GoogleApiSync constructor.
	 *
	 * @param int $userId
	 * @param int $connectionId
	 */
	public function __construct($userId = 0, $connectionId = 0)
	{
		if (!$userId)
		{
			$userId = \CCalendar::GetUserId();
		}
		$this->userId = $userId;
		$this->connectionId = $connectionId;
		$this->syncTransport = new GoogleApiTransport((int)$userId);
	}

	/**
	 * Closes watch channel and asking google to stop pushes
	 *
	 * @param $channelId
	 * @param $resourceId
	 */
	public function stopChannel($channelId, $resourceId)
	{
		$this->syncTransport->stopChannel($channelId, $resourceId);

		$error = $this->getTransportConnectionError();
		if (is_string($error))
		{
			$this->updateLastResultConnection($error);
			return false;
		}

		return true;
	}

	/**
	 * Creates watch channel for connection
	 * @param $name
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function startWatchCalendarList($name)
	{
		$channel = $this->syncTransport->openCalendarListChannel($this->makeChannelParams($name, self::CONNECTION_CHANNEL_TYPE));
		if (!$this->syncTransport->getErrors())
		{
			$channel['expiration'] = Type\DateTime::createFromTimestamp($channel['expiration']/1000);
			return $channel;
		}

		$error = $this->getTransportConnectionError();
		if (is_string($error))
		{
			$this->updateLastResultConnection($error);
		}

		return [];
	}

	private function makeChannelParams($inputSecretWord, $type)
	{
		if (defined('BX24_HOST_NAME') && BX24_HOST_NAME)
		{
			$externalUrl = self::EXTERNAL_LINK . BX24_HOST_NAME;
		}
		else
		{
			$request = Context::getCurrent()->getRequest();
			if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME)
			{
				$host = SITE_SERVER_NAME;
			}
			else
			{
				$host = Option::get('main', 'server_name', $request->getHttpHost());
			}

			$externalUrl = 'https://' . $host . '/bitrix/tools/calendar/push.php';
		}

		return [
			'id' => $type.'_'.$this->userId.'_'.md5($inputSecretWord. time()),
			'type' => 'web_hook',
			'address' => $externalUrl,
			'expiration' => (time() + self::CHANNEL_EXPIRATION) * 1000,
		];
	}

	/**
	 * Creates watch channel for new events
	 *
	 * @param string $calendarId
	 * @return array|bool
	 */
	public function startWatchEventsChannel($calendarId = 'primary')
	{
		$channel = $this->syncTransport->openEventsWatchChannel(
			$calendarId,
			$this->makeChannelParams($calendarId, self::SECTION_CHANNEL_TYPE)
		);

		if (!$this->syncTransport->getErrors())
		{
			$channel['expiration'] = Type\DateTime::createFromTimestamp($channel['expiration']/1000);
			return $channel;
		}

		if (($error = $this->getTransportConnectionError()) && is_string($error))
		{
			$this->updateLastResultConnection($error);
		}

		return false;
	}

	/**
	 * Test ability to establish google api connection
	 * @return bool
	 */
	public function testConnection()
	{
		$this->setColors();
		if ($this->getTransportErrors())
		{
			return false;
		}
		return true;
	}

	/**
	 * Check if errors from transport exists
	 * @return array
	 */
	public function getTransportErrors()
	{
		return $this->syncTransport->getErrors();
	}

	private function setColors()
	{
		if ($this->calendarColors === false || $this->eventColors === false)
		{
			$cacheTime = 86400 * 7;
			$colorData = null;

			if ($cacheTime)
			{
				$cache = \Bitrix\Main\Data\Cache::createInstance();
				$cacheId = "google_colors";
				$cachePath = 'googlecolors';

				if ($cache->initCache($cacheTime, $cacheId, $cachePath))
				{
					$res = $cache->getVars();
					$colorData = $res["colorData"];
				}
			}

			if (!$cacheTime || empty($colorData))
			{
				$colorData = $this->syncTransport->getColors();
				if ($cacheTime && isset($cache, $cacheId, $cachePath))
				{
					$cache->startDataCache($cacheTime, $cacheId, $cachePath);
					$cache->endDataCache(array(
						"colorData" => $colorData
					));
				}
			}

			$this->calendarColors = array();
			$this->eventColors = array();
			if (is_array($colorData) && !empty($colorData['calendar']) && !empty($colorData['event']))
			{
				foreach ($colorData['calendar'] as $key => $color)
				{
					$this->calendarColors[$key] = $color;
				}

				foreach ($colorData['event'] as $key => $color)
				{
					$this->eventColors[$key] = $color;
				}
			}
		}
	}

	/**
	 * @param int $colorId
	 * @param bool $background
	 * @return string
	 */
	private function getCalendarColor($colorId, $background = true)
	{
		$calendarColors = is_array($this->calendarColors) ? $this->calendarColors : array();
		return $calendarColors[$colorId][($background ? 'background' : 'foreground')];
	}

	/**
	 * @param int $colorId
	 * @param bool $background
	 * @return string
	 */
	private function getEventColor($colorId, $background = true)
	{
		$eventColors = is_array($this->eventColors) ? $this->eventColors : array();
		return $eventColors[$colorId][($background ? 'background' : 'foreground')];
	}

	/**
	 * Returns connection error code in message;
	 *
	 * @return array
	 */
	public function getTransportConnectionError()
	{
		$connectionError = $this->syncTransport->getErrorByCode('CONNECTION');
		if ($connectionError)
		{
			return $connectionError['message'];
		}

		return [];
	}

	/**
	 * get calendar list from google
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getCalendarItems(string $syncToken = null): array
	{
		$this->setColors();
		$response = [];

		if (empty($this->calendarList))
		{
			$response = $this->syncTransport->getCalendarList($this->getCalendarListParams($syncToken));
		}

		if ($response && !empty($response['items']))
		{
			foreach($response['items'] as $calendarItem)
			{
				$calendarItem['backgroundColor'] = $this->getCalendarColor($calendarItem['colorId']);
				$calendarItem['textColor'] = $this->getCalendarColor($calendarItem['colorId'], true);
				$this->calendarList[] = $calendarItem;
			}

			$this->nextSyncToken = $response['nextSyncToken'];
		}


		return $this->calendarList;
	}

	/**
	 * @return string
	 */
	public function getNextSyncToken()
	{
		return $this->nextSyncToken;
	}

	/**
	 * get google calendar events list.
	 * By default selecting primary calendar
	 * @param array $calendarData
	 * @return array
	 */
	public function getEvents(array $calendarData): array
	{
		$this->setColors();
		$this->nextSyncToken = $calendarData['SYNC_TOKEN'] ?? '';
		$this->nextPageToken = $calendarData['PAGE_TOKEN'] ?? '';

		if (!empty($response = $this->runSyncEvents($calendarData['GAPI_CALENDAR_ID'])))
		{
			return $this->processResponseReceivingEvents($response);
		}

		return [];
	}

	/**
	 * get id of Primary (main) calendar
	 *
	 * @return string
	 */
	public function getPrimaryId()
	{
		$calendar = $this->getCalendarById('primary');
		return !empty($calendar) ? $calendar['id'] : '';
	}

	/**
	 * Get calendar data by provided ID
	 *
	 * @param $calendarId
	 * @return array
	 */
	private function getCalendarById($calendarId)
	{
		$this->getCalendarItems();

		foreach ($this->calendarList as $calendar)
		{
			if (($calendar['id'] == $calendarId) || (isset($calendar['primary']) && $calendarId == 'primary'))
			{
				return $calendar;
			}
		}
		return array();
	}

	/**
	 * Delete event from specified google calendar
	 *
	 * @param $eventId
	 * @param $calendarId
	 * @return array|mixed
	 */
	public function deleteEvent($eventId, $calendarId)
	{
		return $this->syncTransport->deleteEvent($eventId, urlencode($calendarId));
	}

	/**
	 * @param $eventData
	 * @param $calendarId
	 * @param array $parameters
	 * @return array|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function saveEvent($eventData, $calendarId, $parameters = []): ?array
	{
		$params['editInstance'] = $parameters['editInstance'] ?? false;
		$params['originalDavXmlId'] = $parameters['originalDavXmlId'] ?? null;
		$params['editParentEvents'] = $parameters['editParentEvents'] ?? false;
		$params['editNextEvents'] = $parameters['editNextEvents'] ?? false;
		$params['calendarId'] = $calendarId;
		$params['instanceTz'] = $parameters['instanceTz'] ?? null;
		$params['originalDateFrom'] = $eventData['ORIGINAL_DATE_FROM'] ?? null;
		$params['gEventId'] = $eventData['G_EVENT_ID'] ?: str_replace('@google.com', '',  $eventData['DAV_XML_ID']);
		$params['syncCaldav'] = $parameters['syncCaldav'] ?? false;

		$newEvent = $this->prepareToSaveEvent($eventData, $params);

		$externalEvent = $this->sendToSaveEvent($newEvent, $params);

		if ($externalEvent)
		{
			return [
				'DAV_XML_ID' => $externalEvent['iCalUID'],
				'CAL_DAV_LABEL' => $externalEvent['etag'],
				'ORIGINAL_DATE_FROM' => $externalEvent['originalStartTime'] ? $eventData['ORIGINAL_DATE_FROM'] : null,
				'G_EVENT_ID' => $externalEvent['id'],
			];
		}

		return null;
	}

	/**
	 * @param array $events
	 * @param string $gApiCalendarId
	 * @param array $params
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function saveBatchEvents(array $events, string $gApiCalendarId, array $params): array
	{
		$responseFields = [];
		$prepareEvents = [];

		foreach ($events as $event)
		{
			$localEvent['gEventId'] = $event['gEventId'];
			$partBody = $this->prepareToSaveEvent($event);
			$localEvent['partBody'] = Web\Json::encode($partBody, JSON_UNESCAPED_SLASHES);
			$prepareEvents[$event['ID']] = $localEvent;
		}

		$externalEvents = $this->syncTransport->sendBatchEvents($prepareEvents, $gApiCalendarId, $params);

		if ($externalEvents)
		{
			foreach ($externalEvents as $key => $externalEvent)
			{
				$responseFields[$key]['DAV_XML_ID'] = $externalEvent['iCalUID'];
				$responseFields[$key]['CAL_DAV_LABEL'] = $externalEvent['etag'];
				$responseFields[$key]['G_EVENT_ID'] = $externalEvent['id'];
				$responseFields[$key]['ORIGINAL_DATE_FROM'] = $externalEvent['originalStartTime'] ? $events[$key]['ORIGINAL_DATE_FROM'] : null;
			}
		}

		return $responseFields;
	}

	public function updateLastResultConnection(string $lastResult): void
	{
		if (Loader::includeModule('dav') && !empty($this->connectionId))
		{
			CDavConnection::Update($this->connectionId, [
				"LAST_RESULT" => $lastResult,
				"SYNCHRONIZED" => ConvertTimeStamp(time(), "FULL"),
			]);
		}

		if (GoogleApiPush::isConnectionError($lastResult))
		{
			AddMessage2Log("Bad interaction with Google calendar for connectionId: " . $this->connectionId . " " .$lastResult, "calendar");
		}
	}

	public function updateSuccessLastResultConnection(): void
	{
		$this->updateLastResultConnection("[200] OK");
	}

	/**
	 * @param $event
	 * @return string[]
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareEvent($event): array
	{
		$returnData = [
			'TZ_FROM' => $this->defaultTimezone,
			'TZ_TO' => $this->defaultTimezone
		];

		foreach ($this->eventMapping as $internalKey => $externalKey)
		{
			$returnData[$internalKey] = (isset($event[$externalKey]) ? $event[$externalKey] : '');
		}

		$returnData['iCalUID'] = $event['iCalUID'];
		$returnData['DAV_XML_ID'] = $event['iCalUID'];
		$returnData['G_EVENT_ID'] = $event['id'];

		if (!empty($event['description']))
		{
			$description = str_replace("<br>", "\r\n", $event['description']);
			$returnData["DESCRIPTION"] = trim(\CTextParser::clearAllTags($description));
		}

		if (empty($event['summary']))
		{
			$returnData['NAME'] = GetMessage('EC_T_NEW_EVENT');
		}

		if (!empty($event['transparency']) && $event['transparency'] == 'transparent')
		{
			$returnData['ACCESSIBILITY'] = 'free';
		}
		else
		{
			$returnData['ACCESSIBILITY'] = 'busy';
		}

		if (!empty($event['visibility']) && $event['visibility'] === 'private')
		{
			$returnData['PRIVATE_EVENT'] = true;
		}
		else
		{
			$returnData['PRIVATE_EVENT'] = false;
		}

		$returnData['OWNER_ID'] = $this->userId;
		$returnData['CREATED_BY'] = $this->userId;
		$returnData['CAL_TYPE'] = 'user';

		if (!empty($event['start']['dateTime']) && !empty($event['end']['dateTime']))
		{
			$returnData['TZ_FROM'] = Util::isTimezoneValid($event['start']['timeZone']) ? $event['start']['timeZone'] : $this->defaultTimezone;
			$returnData['TZ_TO'] = Util::isTimezoneValid($event['end']['timeZone']) ? $event['end']['timeZone'] : $this->defaultTimezone;

			$eventStartDateTime = new Type\DateTime($event['start']['dateTime'], self::DATE_TIME_FORMAT, new \DateTimeZone($this->defaultTimezone));
			$returnData['DATE_FROM'] = \CCalendar::Date(\CCalendar::Timestamp($eventStartDateTime->setTimeZone(new \DateTimeZone($returnData['TZ_FROM']))->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME))));

			$eventStartDateTime = new Type\DateTime($event['end']['dateTime'], self::DATE_TIME_FORMAT, new \DateTimeZone($this->defaultTimezone));
			$returnData['DATE_TO'] = \CCalendar::Date(\CCalendar::Timestamp($eventStartDateTime->setTimeZone(new \DateTimeZone($returnData['TZ_TO']))->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME))));
		}

		if (!empty($event['start']['date']))
		{
			$returnData['DATE_FROM'] = \CCalendar::Date(strtotime($event['start']['date']), false);
		}

		if (!empty($event['end']['date']))
		{
			if ($event['end']['date'] === $event['start']['date'])
			{
				$dateStr = strtotime($event['end']['date']);
			}
			else
			{
				$dateStr = strtotime($event['end']['date']) - self::ONE_DAY;
			}

			$returnData['DATE_TO'] = \CCalendar::Date($dateStr, false);
			$returnData['DT_SKIP_TIME'] = 'Y';
		}
		else
		{
			$returnData['DT_SKIP_TIME'] = 'N';
		}

		$returnData['DATE_CREATE'] = \CCalendar::Date(strtotime($event['created']));

		if (!empty($event['colorId']))
		{
			$returnData['COLOR'] = $this->getEventColor($event['colorId']);
			$returnData['TEXT_COLOR'] = $this->getEventColor($event['colorId'], false);
		}

		$returnData['DATE_CREATE'] = \CCalendar::Date(time());
		$returnData['status'] = $event['status'];
		$returnData['hasMoved'] = "N";
		$returnData['isRecurring'] = "N";

		$exDates = [];

		if ($event['recurrence'])
		{
			foreach ($event['recurrence'] as $recurrence)
			{
				if (preg_match('/(RRULE)/', $recurrence))
				{
					$rRuleData = preg_replace('/(RRULE\:)/', '', $recurrence);
					$rRuleList = explode(';', $rRuleData);
					$rRuleSet = [];
					foreach ($rRuleList as $rRuleElement)
					{
						[$rRuleProp, $rRuleVal] = explode('=', $rRuleElement);
						switch ($rRuleProp)
						{
							case 'FREQ':
								if (in_array($rRuleVal, ['HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY']))
								{
									$rRuleSet['FREQ'] = $rRuleVal;
								}
								break;
							case 'COUNT':
								$rRuleSet['COUNT'] = $rRuleVal;
								break;
							case 'INTERVAL':
								$rRuleSet['INTERVAL'] = $rRuleVal;
								break;
							case 'BYDAY':
								$rRuleByDay = array();
								foreach(explode(',', $rRuleVal) as $day)
								{
									$matches = array();
									if (preg_match('/((\-|\+)?\d+)?(MO|TU|WE|TH|FR|SA|SU)/', $day, $matches))
									{
										$rRuleByDay[$matches[1] === ''
											? $matches[3]
											: $matches[1]] =
											$matches[1] === ''
												? $matches[3]
												: $matches[1];
									}
								}
								if (!empty($rRuleByDay))
								{
									$rRuleSet['BYDAY'] = $rRuleByDay;
								}
								break;
							case 'UNTIL':
								try
								{
									$rRuleValDateTime = new Type\DateTime($rRuleVal, 'Ymd\THis\Z', new \DateTimeZone('UTC'));
									$rRuleValDateTime->setTimeZone(new \DateTimeZone($returnData['TZ_TO']));
									$untilDateTime = explode("T", $rRuleVal);

									if ($untilDateTime[1] === "000000Z")
									{
										$rRuleValDateTime = $rRuleValDateTime->add("-1 day");
									}

									$rRuleSet['UNTIL'] = \CCalendar::Date(\CCalendar::Timestamp($rRuleValDateTime->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME))) - 60, false, false);
								}
								catch(\Exception $e)
								{
									$rRuleSet['UNTIL'] = \CCalendar::Date(strtotime($rRuleVal), false, false);
								}

								break;
						}
					}
					$returnData["RRULE"] = \CCalendarEvent::CheckRRULE($rRuleSet);
				}
				elseif (preg_match('/(\d{4}-?\d{2}-?\d{2})(Z)?/', $recurrence, $date))
				{
					if (!empty($date[1]))
					{
						$exDates[] = \CCalendar::Date(strtotime($date[1]), false);
					}
				}
			}
		}

		if (!empty($event['recurringEventId']))
		{
			$returnData['isRecurring'] = "Y";
			if ($event['status'] === 'cancelled')
			{
				$exDates[] = date(Date::convertFormatToPhp(FORMAT_DATE), strtotime(
				!empty($event['originalStartTime']['dateTime'])
						? $event['originalStartTime']['dateTime']
						: $event['originalStartTime']['date']
				));
			}
			elseif ($event['status'] === 'confirmed' && !empty($event['originalStartTime']))
			{
				$returnData['hasMoved'] = "Y";
				$exDates[] = date(Date::convertFormatToPhp(FORMAT_DATE), strtotime(!empty($event['originalStartTime']['dateTime']) ? $event['originalStartTime']['dateTime'] : $event['originalStartTime']['date']));

				if (!empty($event['originalStartTime']['dateTime']))
				{
					$originalTimeZone = Util::isTimezoneValid($event['originalStartTime']['timeZone']) ? $event['originalStartTime']['timeZone'] : $returnData['TZ_FROM'];
					$eventOriginalDateFrom = new Type\DateTime($event['originalStartTime']['dateTime'], self::DATE_TIME_FORMAT, new \DateTimeZone($this->defaultTimezone));
					$returnData['ORIGINAL_DATE_FROM'] = \CCalendar::Date(\CCalendar::Timestamp($eventOriginalDateFrom->setTimeZone(new \DateTimeZone($originalTimeZone))->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME))));
				}

				if (!empty($event['originalStartTime']['date']))
				{
					$returnData['ORIGINAL_DATE_FROM'] = \CCalendar::Date(strtotime($event['originalStartTime']['date']), false);
				}
			}

			$returnData['recurringEventId'] = $event['recurringEventId'];
		}
		if (!empty($exDates))
		{
			$returnData['EXDATE'] = implode(';', $exDates);
		}

		$returnData['REMIND'] = [];
		if (!empty($event['reminders']['overrides']))
		{
			foreach ($event['reminders']['overrides'] as $remindData)
			{
				$remindTimeout = $remindData['minutes'];
				$returnData['REMIND'][] = [
					'type' => 'min',
					'count' => $remindTimeout
				];
			}
		}
		if (!empty($event['reminders']['useDefault']) && !empty($this->defaultReminderData) && $event['reminders']['useDefault'] === 1)
		{
			foreach ($this->defaultReminderData as $remindData)
			{
				$remindTimeout = $remindData['minutes'];
				$returnData['REMIND'][] = [
					'type' => 'min',
					'count' => $remindTimeout
				];
			}
		}
		if (!empty($event['location']))
		{
			$returnData['LOCATION'] = Rooms\Util::unParseTextLocation($event['location']);
		}

		if (!empty($event['sequence']))
		{
			$returnData['VERSION'] = (int)$event['sequence'] + Util::VERSION_DIFFERENCE;
		}

		return $returnData;
	}

	/**
	 * @param $eventData
	 * @param null $params
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareToSaveEvent($eventData, $params = null): array
	{
		$newEvent = [];
		$newEvent['summary'] = $eventData['NAME'];

		if (!empty($eventData['ATTENDEES_CODES']) && is_string($eventData['ATTENDEES_CODES']))
		{
			$eventData['ATTENDEES_CODES'] = explode(",", $eventData['ATTENDEES_CODES']);
		}

		if (is_string($eventData['MEETING']))
		{
			$eventData['MEETING'] = unserialize($eventData['MEETING'], ['allowed_classes' => false]);
		}
		if (empty($eventData['MEETING']['LANGUAGE_ID']))
		{
			$eventData['MEETING']['LANGUAGE_ID'] = \CCalendar::getUserLanguageId((int)$eventData['OWNER_ID']);
		}

		if (isset($eventData['ATTENDEES_CODES']) && is_countable($eventData['ATTENDEES_CODES']) && count($eventData['ATTENDEES_CODES']) > 1)
		{
			$users = Util::getAttendees($eventData['ATTENDEES_CODES']);
			$newEvent['description'] = Loc::getMessage('ATTENDEES_EVENT', null, $eventData['MEETING']['LANGUAGE_ID']).': '
				. stripcslashes(implode(', ', $users))
				. "\r\n"
				. $eventData["DESCRIPTION"];
		}
		elseif (!empty($eventData['DESCRIPTION']) && is_string($eventData['DESCRIPTION']))
		{
			$newEvent['description'] = $eventData['DESCRIPTION'];
		}

		if (!empty($eventData['ACCESSIBILITY']) && $eventData['ACCESSIBILITY'] === 'busy')
		{
			$newEvent['transparency'] = 'opaque';
		}
		else
		{
			$newEvent['transparency'] = 'transparent';
		}

		if (!empty($eventData['LOCATION']['NEW']) && is_string($eventData['LOCATION']['NEW']))
		{
			$newEvent['location'] = \CCalendar::GetTextLocation($eventData['LOCATION']['NEW']);
		}
		elseif (!empty($eventData['LOCATION']) && is_string($eventData['LOCATION']))
		{
			$newEvent['location'] = \CCalendar::GetTextLocation($eventData['LOCATION']);
		}

		if (!empty($eventData['REMIND']))
		{
			$newEvent['reminders'] = $this->prepareRemind($eventData);
		}

		if ($eventData['DT_SKIP_TIME'] === "Y")
		{
			$newEvent['start']['date'] = Util::getDateObject($eventData['DATE_FROM'])
				->format('Y-m-d');
			$newEvent['end']['date'] =  Util::getDateObject($eventData['DATE_TO'])
				->add('+1 day')
				->format('Y-m-d');

			if (!empty($eventData['G_EVENT_ID']))
			{
				$newEvent['start']['dateTime'] = null;
				$newEvent['end']['dateTime'] = null;
				$newEvent['start']['timeZone'] = null;
				$newEvent['end']['timeZone'] = null;
			}
		}
		else
		{
			$newEvent['start']['dateTime'] = Util::getDateObject($eventData['DATE_FROM'], false, $eventData['TZ_FROM'])
				->format(self::DATE_TIME_FORMAT);
			$newEvent['start']['timeZone'] = Util::prepareTimezone($eventData['TZ_FROM'])->getName();

			$newEvent['end']['dateTime'] = Util::getDateObject($eventData['DATE_TO'], false, $eventData['TZ_TO'])
				->format(self::DATE_TIME_FORMAT);
			$newEvent['end']['timeZone'] = Util::prepareTimezone($eventData['TZ_TO'])->getName();

			if (!empty($eventData['G_EVENT_ID']))
			{
				$newEvent['start']['date'] = null;
				$newEvent['end']['date'] = null;
			}
		}

		if (
			!empty($eventData['RRULE'])
			&& is_array($eventData['RRULE'])
			&& isset($eventData['RRULE']['FREQ'])
			&& $eventData['RRULE']['FREQ'] !== 'NONE'
		)
		{
			$newEvent['recurrence'] = $this->prepareRRule($eventData, $params['editNextEvents']);
		}

		if (isset($eventData['ORIGINAL_DATE_FROM']))
		{
			$newEvent['originalStartTime'] = Util::getDateObject($eventData['ORIGINAL_DATE_FROM'], false, $eventData['TZ_FROM'])
				->format(self::DATE_TIME_FORMAT);
		}

		if (isset($eventData['G_EVENT_ID']) && isset($eventData['RECURRENCE_ID']))
		{
			$newEvent['recurringEventId'] = $eventData['G_EVENT_ID'];
		}

		if ($params['syncCaldav'] || isset($eventData['DAV_XML_ID']))
		{
			$newEvent['iCalUID'] = $eventData['DAV_XML_ID'];
		}

		if (isset($eventData['G_EVENT_ID']))
		{
			$newEvent['id'] = $eventData['G_EVENT_ID'];
		}

		if (!empty($eventData['PRIVATE_EVENT']))
		{
			$newEvent['visibility'] = "private";
		}
		else
		{
			$newEvent['visibility'] = "public";
		}

		if (isset($eventData['VERSION']))
		{
			$newEvent['sequence'] = $eventData['VERSION'] - Util::VERSION_DIFFERENCE;
		}

		return $newEvent;
	}

	/**
	 * @param $newEvent
	 * @param $params
	 * @return array|mixed
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function sendToSaveEvent($newEvent, $params)
	{
		if ($params['editInstance'] === true)
		{
			$eventOriginalStart = Util::getDateObject($params['originalDateFrom'], false, $params['instanceTz']);
			$originalStart = $eventOriginalStart->format(self::DATE_TIME_FORMAT);
			$externalId = $params['gEventId'] ?: $params['originalDavXmlId'];
			$instance = $this->syncTransport->getInstanceRecurringEvent($params['calendarId'], $externalId, $originalStart);

			$newEvent['originalStartTime'] = $originalStart;
			$newEvent['recurringEventId'] = $params['originalDavXmlId'];

			if (is_array($instance['items']))
			{
				return $this->syncTransport->updateEvent($newEvent, urlencode($params['calendarId']), $instance['items'][0]['id']);
			}
		}
		elseif ($params['editParentEvents'] === true)
		{
			return $this->syncTransport->updateEvent($newEvent, urldecode($params['calendarId']), $params['gEventId']);
		}
		elseif ($params['syncCaldav'])
		{
			return $this->syncTransport->importEvent($newEvent, urlencode($params['calendarId']));
		}
		elseif (($params['gEventId']))
		{
			return $this->syncTransport->updateEvent($newEvent, urlencode($params['calendarId']), $params['gEventId']);
		}
		else
		{
			return $this->syncTransport->insertEvent($newEvent, urlencode($params['calendarId']));
		}

		return [];
	}

	/**
	 * @param $calendar
	 * @return array|null
	 */
	public function createCalendar($calendar): ?array
	{
		$externalData = $this->syncTransport->insertCalendar($this->prepareCalendar($calendar));

		return $externalData
			? ['GAPI_CALENDAR_ID' => $externalData['id']]
			: null
		;
	}

	/**
	 * @param $calendar
	 * @return array
	 */
	private function prepareCalendar($calendar): array
	{
		$returnData['summary'] = Emoji::decode($calendar['NAME']);
		if (isset($calendar['EXTERNAL_TYPE']) && $calendar['EXTERNAL_TYPE'] === \CCalendarSect::EXTERNAL_TYPE_LOCAL)
		{
			IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');
			$returnData['summary'] = Loc::getMessage('EC_CALENDAR_BITRIX24_NAME') . " " . $returnData['summary'];
		}

		return $returnData;
	}

	/**
	 * @param string|null $channelId
	 * @return int|null
	 */
	public static function getChannelOwner(string $channelId = null): ?int
	{
		if (empty($channelId))
		{
			return null;
		}

		$matches = [];
		preg_match('/(' . self::CONNECTION_CHANNEL_TYPE . '|' . self::SECTION_CHANNEL_TYPE . ')_(\d+)_.+/', $channelId, $matches);

		return !empty($matches) && (int)$matches[2] > 0
			? (int)$matches[2]
			: null
		;
	}

	public function hasMoreEvents()
	{
		return !empty($this->nextPageToken);
	}

	/**
	 * @return bool
	 */
	private function hasExpiredSyncTokenError(): bool
	{
		return !empty($this->getExpiredSyncTokenError());
	}

	/**
	 * @return array
	 */
	private function getExpiredSyncTokenError(): array
	{
		return array_filter($this->syncTransport->getErrors(), function ($error) {
			return preg_match("/^\[(410)\][a-z0-9 _]*/i", $error['message']);
		});
	}

	/**
	 * @param array $response
	 * @return array
	 */
	private function processResponseReceivingEvents(array $response): array
	{
		$this->setSyncSettings($response);

		return $this->getEventsList($response['items']);
	}

	/**
	 * @return array
	 */
	private function getRequestParamsWithSyncToken(): array
	{
		return [
			'pageToken' => $this->nextPageToken,
			'syncToken' => $this->nextSyncToken,
			'showDeleted' => 'true',
		];
	}

	/**
	 * @return array
	 */
	private function getRequestParamsForFirstSync(): array
	{
		return [
			'pageToken' => $this->nextPageToken,
			'showDeleted' => 'true',
			'maxResults' => self::SYNC_EVENTS_LIMIT,
			'timeMin' => (new Type\DateTime())->add(self::SYNC_EVENTS_DATE_INTERVAL)->format(self::DATE_TIME_FORMAT),
		];
	}

	/**
	 * @param $gApiCalendarId
	 * @return array|mixed
	 */
	private function runSyncEvents($gApiCalendarId)
	{
		$response = !empty($this->nextSyncToken)
			? $this->syncTransport->getEvents($gApiCalendarId, $this->getRequestParamsWithSyncToken())
			: $this->syncTransport->getEvents($gApiCalendarId, $this->getRequestParamsForFirstSync());

		if (!$response && $this->hasExpiredSyncTokenError())
		{
			return $this->syncTransport->getEvents($gApiCalendarId, $this->getRequestParamsForFirstSync());
		}

		return $response;
	}

	/**
	 * @param iterable|null $events
	 * @return array[]
	 */
	private function getEventsList(iterable $events = null): array
	{
		if (empty($events))
			return [];

		$eventsList = [];
		foreach ($events as $event)
		{
			$preparedEvent = $this->prepareEvent($event);
			$eventsList[$preparedEvent['G_EVENT_ID']] = $preparedEvent;
		}

		return $eventsList;
	}

	/**
	 * @param array|null $response
	 */
	private function setSyncSettings(array $response = null): void
	{
		$this->nextPageToken = $response['nextPageToken'] ?? '';
		$this->nextSyncToken = $response['nextSyncToken'] ?? '';
		$this->defaultReminderData = $response['defaultReminders'] ?? $this->defaultReminderData;
		$this->defaultTimezone = Util::isTimezoneValid($response['timeZone']) ? $response['timeZone'] : $this->defaultTimezone;
	}

	/**
	 * @param $eventData
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareRemind($eventData): array
	{
		$reminders = [];
		$reminders['useDefault'] = false;
		$reminders['overrides'] = [];

		if (!is_iterable($eventData['REMIND']))
		{
			return [];
		}

		foreach ($eventData['REMIND'] as $remindRule)
		{
			$minutes = $remindRule['count'];
			if ($remindRule['type'] === 'hour')
			{
				$minutes = 60 * $remindRule['count'];
			}
			elseif ($remindRule['type'] === 'day')
			{
				$minutes = 24 * 60 * $remindRule['count'];
			}
			elseif ($remindRule['type'] === 'daybefore')
			{
				$dateFrom = Util::getDateObject(
					$eventData['DATE_FROM'],
					$eventData['DT_SKIP_TIME'] === 'Y',
					$eventData['TZ_FROM']
				);

				$remind = clone $dateFrom;
				if (method_exists($remind, 'setTime'))
				{
					$remind->setTime(0, 0, 0);
				}
				$remind->add("-{$remindRule['before']} days")->add("{$remindRule['time']} minutes");

				if ($dateFrom->getTimestamp() < $remind->getTimestamp())
				{
					continue;
				}

				$minutes = $this->calculateMinutes($dateFrom, $remind);
			}
			elseif ($remindRule['type'] === 'date')
			{
				$dateFrom = Util::getDateObject(
					$eventData['DATE_FROM'],
					$eventData['DT_SKIP_TIME'] === 'Y',
					$eventData['TZ_FROM']
				);
				$remind = Util::getDateObject(
					$remindRule['value'],
					$eventData['DT_SKIP_TIME'] === 'Y',
					$eventData['TZ_FROM']
				);

				if ($dateFrom->getTimestamp() < $remind->getTimestamp())
				{
					continue;
				}

				$minutes = $this->calculateMinutes($dateFrom, $remind);
			}

			$reminders['overrides'][] = [
				'minutes' => $minutes,
				'method' => 'popup',
			];
		}

		return $reminders;
	}

	/**
	 * @param Type\Date $dateFrom
	 * @param Type\Date $remind
	 * @return int
	 */
	private function calculateMinutes(Type\Date $dateFrom, Type\Date $remind): int
	{
		$diff = $dateFrom->getDiff($remind);
		$days = $diff->format('%d');
		$hours = $diff->format('%h');
		$minutes = $diff->format('%i');

		return ((int)$days * 24 * 60) + ((int)$hours * 60) + (int)$minutes;
	}

	/**
	 * @param $event
	 * @param $editNextEvents
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareRRule($event, $editNextEvents): array
	{
		$rule = [];
		$parsedRule = \CCalendarEvent::ParseRRULE($event['RRULE']);
		$rRule = 'RRULE:';
		$rRule .= 'FREQ=' .$parsedRule['FREQ'];
		$rRule .= !empty($parsedRule['INTERVAL']) ? ';INTERVAL=' . $parsedRule['INTERVAL'] : '';
		if (!empty($parsedRule['BYDAY']))
		{
			if (is_string($parsedRule['BYDAY']))
			{
				$rRule .= ';BYDAY=' . $parsedRule['BYDAY'];
			}
			elseif (is_array($parsedRule['BYDAY']))
			{
				$rRule .= ';BYDAY=' . implode(",", $parsedRule['BYDAY']);
			}
			else
			{
				$rRule = '';
			}
		}

		if (!empty($parsedRule['COUNT']))
		{
			$rRule .=  ';COUNT=' . $parsedRule['COUNT'];
		}
		elseif (!empty($parsedRule['UNTIL']))
		{
			$tsTo = Util::getDateObject($parsedRule['UNTIL']);
			if ($event['DT_SKIP_TIME'] === "N" && $tsTo->getTimestamp() < (new Type\Date(self::END_OF_DATE, "d.m.Y"))->getTimestamp())
			{
				$tsTo->add('+1 day');
			}
			$rRule .= ';UNTIL='.$tsTo->format('Ymd\THis\Z');
		}

		$rule[] = $rRule;

		if (!empty($event['EXDATE']) && $editNextEvents !== true)
		{
			$exDates = explode(';', $event['EXDATE']);
			foreach ($exDates as $exDate)
			{
				if ($event['DT_SKIP_TIME'] === "Y")
				{
					$rule[] = 'EXDATE;VALUE=DATE:' . date('Ymd', strtotime($exDate));
				}
				else
				{
					$rule[] = 'EXDATE;TZID=UTC:'
						. date("Ymd", strtotime($exDate))
						. Util::getDateObject($event['DATE_FROM'], false, $event['TZ_FROM'])
							->setTimeZone(new \DateTimeZone('UTC'))->format('\\THis\\Z');
				}
			}
		}

		return $rule;
	}

	/**
	 * @param string $gApiCalendarId
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function deleteCalendar(string $gApiCalendarId): void
	{
		$this->syncTransport->deleteCalendar($gApiCalendarId);
	}

	/**
	 * @return string[]
	 */
	private function getCalendarListParams(string $syncToken = null): array
	{
		if ($syncToken === null)
		{
			return [];
		}

		return [
			'showDeleted' => 'true',
			'showHidden' => 'true',
			'syncToken' => $syncToken,
		];
	}

	/**
	 * @param string $gApiCalendarId
	 * @param array $calendarData
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function updateCalendar(string $gApiCalendarId, array $calendarData): void
	{
		$this->syncTransport->updateCalendar($gApiCalendarId, $this->prepareCalendar($calendarData));
	}

	/**
	 * @param string $gApiCalendarId
	 * @param array $section
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function updateCalendarList(string $gApiCalendarId, array $section): array
	{
		return $this->syncTransport->updateCalendarList($gApiCalendarId, $this->prepareCalendarList($section));
	}


	/**
	 * @param array $calendar
	 * @return array
	 */
	private function prepareCalendarList(array $calendar): array
	{
		$parameters = [];

		if (isset($calendar['COLOR']))
		{
			$parameters['backgroundColor'] = $calendar['COLOR'];
			$parameters['foregroundColor'] = '#ffffff';
		}

		$parameters['selected'] = 'true';

		return $parameters;
	}

	/**
	 * @return string
	 */
	public function getNextPageToken(): string
	{
		return $this->nextPageToken;
	}
}