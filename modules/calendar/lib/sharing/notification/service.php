<?php
namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sharing\Link\EventLink;
use Bitrix\Calendar\Sharing;

abstract class Service
{
	protected Event $event;
	protected EventLink $eventLink;

	public function getEvent(): Event
	{
		return $this->event;
	}

	public function setEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	public function getEventLink(): EventLink
	{
		return $this->eventLink;
	}

	public function setEventLink(EventLink $eventLink): self
	{
		$this->eventLink = $eventLink;

		return $this;
	}

	protected function getEventFormattedDateTime(): string
	{
		$from = $this->event->getStart();
		$to = $this->event->getEnd();
		$isFullDay = $this->event->isFullDayEvent();
		return Sharing\Helper::formatTimeInterval($from, $to, $isFullDay);
	}

	protected function getOwner(): array
	{
		$ownerId = $this->eventLink->getOwnerId();
		$info = Sharing\Helper::getOwnerInfo($ownerId);
		$ownerAttendee = current(array_filter($this->getAttendeesList(), static function($att) use ($ownerId) {
			return $att['id'] === $ownerId;
		}));

		if (isset($ownerAttendee['status']) && !in_array($ownerAttendee['status'], ['Q', 'Y', 'N'], true))
		{
			$ownerAttendee['status'] = 'Q';
		}

		return [
			'ID' => $ownerId,
			'NAME' => "{$info['name']} {$info['lastName']}",
			'PHOTO' => $info['photo'],
			'STATUS' => $ownerAttendee['status'] ?? 'Q',
		];
	}

	protected function getCalendarLink(): ?string
	{
		if ($this->eventLink->getUserLinkHash())
		{
			$sharingLinkFactory = new Sharing\Link\Factory();
			$userLink = $sharingLinkFactory->getLinkByHash($this->eventLink->getUserLinkHash());
			if ($userLink)
			{
				return $userLink->getUrl();
			}
		}
		return null;
	}

	protected function getAttendeesList(): array
	{
		return \CCalendarEvent::getAttendeeList($this->event->getId())['attendeeList'][$this->event->getId()];
	}

	abstract public function notifyAboutMeetingStatus(string $to): void;
}
