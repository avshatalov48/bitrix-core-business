<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Builders\EventCloner;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\Service\ICloud\ICloudCalendar;
use Bitrix\Calendar\Integration\Dav\Service\ICloud\RecurrenceEventDataBuilder;
use Bitrix\Calendar\Internal\Repository\EventRepository;
use Bitrix\Calendar\Sync\Icloud\EventBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class RequestDataBuilder
{
	public function __construct(
		private readonly EventRepository $eventRepository,
		private readonly EventPreparer $eventPreparer,
	)
	{
	}

	public function buildCreateSectionData(Section $section): string
	{
		$namePart = $this->getSectionNamePart($section->getName(), $section->getExternalType());
		$colorPart = $this->getSectionColorPart($section->getColor());

		return sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>
			<A:mkcol xmlns:A="DAV:" xmlns:A0="urn:ietf:params:xml:ns:caldav" xmlns:A1="http://apple.com/ns/ical/">
				<A:set>
					<A:prop>
						<A:resourcetype>
							<A:collection/>
							<A0:calendar/>
						</A:resourcetype>
						%s
						%s
					</A:prop>
				</A:set>
			</A:mkcol>',
			$namePart,
			$colorPart,
		);
	}

	public function buildUpdateSectionData(Section $section): string
	{
		$namePart = $this->getSectionNamePart($section->getName(), $section->getExternalType());
		$colorPart = $this->getSectionColorPart($section->getColor());

		return sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>
			<A:propertyupdate xmlns:A="DAV:" xmlns:A0="urn:ietf:params:xml:ns:caldav" xmlns:A1="http://apple.com/ns/ical/">
				<A:set>
					<A:prop>
						%s
						%s
					</A:prop>
				</A:set>
			</A:propertyupdate>',
			$namePart,
			$colorPart,
		);
	}

	public function buildGetSectionsData(): string
	{
		return
			'<?xml version="1.0" encoding="UTF-8"?>
			<A:propfind xmlns:A="DAV:"
				xmlns:A0="urn:ietf:params:xml:ns:caldav"
				xmlns:A1="http://calendarserver.org/ns/"
				xmlns:A2="http://apple.com/ns/ical/"
			>
				<A:prop>
					<A0:calendar-home-set/>
					<A1:getctag/>
					<A:displayname/>
					<A0:calendar-description/>
					<A2:calendar-color/>
					<A0:supported-calendar-component-set/>
					<A:resourcetype/>
					<A:owner/>
					<A:current-user-principal/>
					<A:principal-URL/>
					<A:getetag/>
				</A:prop>
			</A:propfind>'
		;
	}

	public function buildGetSectionData(): string
	{
		return
			'<?xml version="1.0" encoding="UTF-8"?>
			<A:propfind xmlns:A="DAV:" xmlns:A0="http://calendarserver.org/ns/">
				<A:prop>
					<A:getetag/>
					<A:resourcetype/>
				</A:prop>
			</A:propfind>'
		;
	}

	public function buildSyncEventsData(?string $syncToken): string
	{
		if ($syncToken)
		{
			return sprintf(
				'<?xml version="1.0" encoding="UTF-8"?>
				<A:sync-collection xmlns:A="DAV:" xmlns:A0="urn:ietf:params:xml:ns:caldav">
					<A:sync-token>%s</A:sync-token>
					<A:sync-level>1</A:sync-level>
					<A:prop>
						<A:getetag/>
						<A:getcontenttype/>
						<A:resourcetype/>
						<A0:calendar-data/>
					</A:prop>
				</A:sync-collection>',
				$syncToken
			);
		}

		$date = date('Ymd\T000000\Z', strtotime('-6 months'));

		return sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>
			<A0:calendar-query xmlns:A="DAV:" xmlns:A0="urn:ietf:params:xml:ns:caldav">
				<A:prop>
					<A:getetag/>
					<A:getcontenttype/>
					<A:resourcetype/>
					<A0:calendar-data/>
				</A:prop>
				<A0:filter>
					<A0:comp-filter name="VCALENDAR">
						<A0:comp-filter name="VEVENT">
							<A0:time-range start="%s"/>
						</A0:comp-filter>
					</A0:comp-filter>
				</A0:filter>
			</A0:calendar-query>',
			$date
		);
	}

	public function buildGetSectionSyncTokenData(): string
	{
		return
			'<?xml version="1.0" encoding="UTF-8"?>
			<A:propfind xmlns:A="DAV:">
				<A:prop>
					<A:sync-token/>
					<A:getetag/>
				</A:prop>
			</A:propfind>'
		;
	}

	/**
	 * @param string[] $hrefs
	 */
	public function buildGetEventsData(array $hrefs): string
	{
		$hrefsPart = '';
		foreach ($hrefs as $href)
		{
			$hrefsPart .= "<A:href>{$href}</A:href>";
		}

		return sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>
			<A0:calendar-multiget xmlns:A="DAV:" xmlns:A0="urn:ietf:params:xml:ns:caldav">
				<A:prop>
					<A:getcontenttype/>
					<A:resourcetype/>
					<A:getetag/>
					<A0:calendar-data/>
				</A:prop>
				%s
			</A0:calendar-multiget>',
			$hrefsPart,
		);
	}

	public function buildGetEventData(): string
	{
		return
			'<?xml version="1.0" encoding="UTF-8"?>
			<A:propfind xmlns:A="DAV:" xmlns:A0="urn:ietf:params:xml:ns:caldav">
				<A:prop>
					<A:getcontenttype/>
					<A:resourcetype/>
					<A:getetag/>
				</A:prop>
			</A:propfind>'
		;
	}

	/**
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws RepositoryReadException
	 * @throws SystemException
	 */
	public function buildSendEventData(Event $event, string $vendorEventId): string
	{
		$event = $this->eventPreparer->prepare($event, $vendorEventId);

		$excludedDates = $event->getExcludedDateCollection();
		if (!$event->getRecurringRule() && !$excludedDates?->getCollection())
		{
			return $this->buildSimpleEventData($event);
		}

		return $this->buildRecurrenceEventData($event, $excludedDates, $vendorEventId);
	}

	/**
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws RepositoryReadException
	 * @throws SystemException
	 */
	private function buildRecurrenceEventData(
		Event $event,
		?ExcludedDatesCollection $excludedDates,
		string $vendorEventId,
	): string
	{
		$event = (new EventCloner($event))->build();

		$instances = $this->eventRepository->getEventInstances($event);

		$instancesDates = [];
		foreach ($instances as $instance)
		{
			$instancesDates[] =
				$instance->getOriginalDateFrom()
					? $instance->getOriginalDateFrom()->format('Ymd')
					: $instance->getStart()->format('Ymd')
			;
		}

		if ($excludedDates)
		{
			foreach ($excludedDates->getCollection() as $key => $excludedDate)
			{
				if (!in_array($excludedDate->format('Ymd'), $instancesDates, true))
				{
					continue;
				}

				$excludedDates->remove($key);
			}
			$event->setExcludedDateCollection($excludedDates);
		}

		$data = [EventBuilder::getInstance()->getContent($event)];

		foreach ($instances as $instance)
		{
			$instance->setUid($vendorEventId);

			$data[] = EventBuilder::getInstance()->getContent($instance);
		}

		return (new RecurrenceEventDataBuilder($data))->render();
	}

	/**
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	private function buildSimpleEventData(Event $event): string
	{
		$data = EventBuilder::getInstance()->getContent($event);

		if ($data === null)
		{
			return '';
		}

		return (new ICloudCalendar($data))->render();
	}

	private function getSectionNamePart(?string $name, ?string $externalType): string
	{
		$namePart = '';

		if (!$name)
		{
			return $namePart;
		}

		if ($externalType === 'local')
		{
			$name = Loc::getMessage(
				'CALENDAR_ICLOUD_REQUEST_DATA_BUILDER_LOCAL_EVENT_PREFIX',
				['#NAME#' => $name],
			);
		}

		return "<A:displayname>{$name}</A:displayname>";
	}

	private function getSectionColorPart(?string $color): string
	{
		$colorPart = '';

		if (!$color)
		{
			return $colorPart;
		}

		return "<A1:calendar-color>{$color}</A1:calendar-color>";
	}
}
