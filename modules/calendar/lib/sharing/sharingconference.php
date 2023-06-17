<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Sharing\Link\EventLink;
use Bitrix\Calendar\Sharing\Link\EventLinkMapper;
use Bitrix\Main\Loader;

class SharingConference
{
	private const CONFERENCE_PATH = 'video/';
	private const CONFERENCE_TYPE = 'VIDEOCONF';
	/** @var EventLink  */
	private EventLink $eventLink;

	/**
	 * @param EventLink $eventLink
	 */
	public function __construct(EventLink $eventLink)
	{
		$this->eventLink = $eventLink;
	}

	/**
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getConferenceChatId(): ?int
	{
		if (!$this->checkPossibilityOfCreatingLink())
		{
			return null;
		}

		if ($this->eventLink->getConferenceId())
		{
			return $this->getConferenceChatIdByAlias($this->eventLink->getConferenceId());
		}

		$conference = $this->createConference();

		if (!$conference)
		{
			return null;
		}

		$this->eventLink->setConferenceId($conference->getData()['ALIAS_DATA']['ALIAS']);

		(new EventLinkMapper())->update($this->eventLink);

		return $conference->getData()['CHAT_ID'];
	}

	/**
	 * @return string|null
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getConferenceLink(): ?string
	{
		if (!$this->checkPossibilityOfCreatingLink())
		{
			return null;
		}

		if ($this->eventLink->getConferenceId())
		{
			return $this->getConferenceUrl($this->eventLink->getConferenceId());
		}

		$conference = $this->createConference();

		if (!$conference)
		{
			return null;
		}

		$this->eventLink->setConferenceId($conference->getData()['ALIAS_DATA']['ALIAS']);

		(new EventLinkMapper())->update($this->eventLink);

		return $this->getConferenceUrl($this->eventLink->getConferenceId());
	}

	private function createConference()
	{
		$event = \CCalendarEvent::GetList([
			'arFilter' => [
				'ID' => $this->eventLink->getObjectId(),
			],
			'fetchAttendees' => true,
			'checkPermissions' => false,
		]);

		$event = $event[0] ?? false;
		if (
			!$event
			|| !in_array(
				$event['EVENT_TYPE'] ?? null,
				[Dictionary::EVENT_TYPE['shared_crm'], Dictionary::EVENT_TYPE['shared']],
				true
			)
		)
		{
			return false;
		}

		$attendeesId = [];
		$attendeesCodes = $event['ATTENDEE_LIST'] ?? [];
		foreach ($attendeesCodes as $attendee)
		{
			if (
				isset($attendee['id'])
				&& in_array($attendee['status'] ?? null, Dictionary::MEETING_STATUS, true)
				&& $attendee['status'] !== Dictionary::MEETING_STATUS['Host']
			)
			{
				$attendeesId[] = $attendee['id'];
			}
		}

		$conference = \Bitrix\Im\Call\Conference::add([
			'USERS' => $attendeesId,
			'TITLE' => $event['NAME'],
		]);

		if ($conference->getErrors())
		{
			return null;
		}

		return $conference;
	}

	/**
	 * @param string $alias
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getConferenceChatIdByAlias(string $alias): ?int
	{
		$aliasInfo = \Bitrix\Im\Model\AliasTable::query()
			->setSelect(['*'])
			->where('ALIAS', $alias)
			->where('ENTITY_TYPE', self::CONFERENCE_TYPE)
			->exec()->fetch()
		;

		if (!$aliasInfo)
		{
			return null;
		}

		return (int)$aliasInfo['ENTITY_ID'];
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function checkPossibilityOfCreatingLink(): bool
	{
		return !(!Loader::includeModule('im') || !Loader::includeModule('voximplant'));
	}

	/**
	 * @param $conferenceId
	 * @return string
	 */
	private function getConferenceUrl($conferenceId): string
	{
		$serverPath = \CCalendar::GetServerPath();

		return $serverPath . '/' . self::CONFERENCE_PATH . $conferenceId;
	}
}