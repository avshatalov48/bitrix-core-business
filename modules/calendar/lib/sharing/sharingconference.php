<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Event\Event;
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
		/** @var Event $event */
		$event = (new Mappers\Event())->getById($this->eventLink->getObjectId());
		if (!$event)
		{
			return null;
		}

		$attendeesId = [];
		$attendeesCodes = $event->getAttendeesCollection()->getAttendeesCodes();
		foreach ($attendeesCodes as $attendee)
		{
			if (mb_strpos($attendee, 'U') === 0)
			{
				$attendeesId[] = mb_substr($attendee, 1);
			}
		}

		$conference = \Bitrix\Im\Call\Conference::add([
			'USERS' => $attendeesId,
			'TITLE' => $event->getName(),
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