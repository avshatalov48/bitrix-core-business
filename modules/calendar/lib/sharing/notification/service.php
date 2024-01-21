<?php
namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sharing\Link\EventLink;
use Bitrix\Calendar\Sharing\Link\CrmDealLink;
use Bitrix\Calendar\Sharing;

abstract class Service
{
	protected Event $event;
	protected EventLink $eventLink;
	protected CrmDealLink $crmDealLink;
	protected ?Event $oldEvent = null;
	private ?array $owner = null;

	/**
	 * @return Event
	 */
	public function getEvent(): Event
	{
		return $this->event;
	}

	/**
	 * @param Event $event
	 * @return $this
	 */
	public function setEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	/**
	 * @return EventLink
	 */
	public function getEventLink(): EventLink
	{
		return $this->eventLink;
	}

	/**
	 * @param EventLink $eventLink
	 * @return $this
	 */
	public function setEventLink(EventLink $eventLink): self
	{
		$this->eventLink = $eventLink;

		return $this;
	}

	/**
	 * @param CrmDealLink $crmDealLink
	 * @return $this
	 */
	public function setCrmDealLink(CrmDealLink $crmDealLink): self
	{
		$this->crmDealLink = $crmDealLink;

		return $this;
	}

	/**
	 * @param Event $oldEvent
	 * @return $this
	 */
	public function setOldEvent(Event $oldEvent): self
	{
		$this->oldEvent = $oldEvent;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getEventFormattedDateTime(): string
	{
		$from = $this->event->getStart();
		$to = $this->event->getEnd();
		$isFullDay = $this->event->isFullDayEvent();
		return Sharing\Helper::formatTimeInterval($from, $to, $isFullDay);
	}

	/**
	 * @return array
	 */
	protected function getOwner(): array
	{
		if (is_array($this->owner))
		{
			return $this->owner;
		}

		$ownerId = $this->eventLink->getOwnerId();
		$info = Sharing\Helper::getOwnerInfo($ownerId);
		$ownerAttendee = current(array_filter($this->getAttendeesList(), static function($att) use ($ownerId) {
			return $att['id'] === $ownerId;
		}));

		if (isset($ownerAttendee['status']) && !in_array($ownerAttendee['status'], ['Q', 'Y', 'N'], true))
		{
			$ownerAttendee['status'] = 'Q';
		}

		$this->owner = [
			'ID' => $ownerId,
			'NAME' => "{$info['name']} {$info['lastName']}",
			'PHOTO' => $info['photo'],
			'GENDER' => $info['gender'],
			'STATUS' => $ownerAttendee['status'] ?? 'Q',
		];

		return $this->owner;
	}

	/**
	 * @return string|null
	 */
	protected function getCalendarLink(): ?string
	{
		if ($this->eventLink->getParentLinkHash())
		{
			$sharingLinkFactory = new Sharing\Link\Factory();
			$userLink = $sharingLinkFactory->getLinkByHash($this->eventLink->getParentLinkHash());
			if ($userLink)
			{
				return $userLink->getUrl();
			}
		}
		return null;
	}

	/**
	 * @return array
	 */
	protected function getAttendeesList(): array
	{
		return \CCalendarEvent::getAttendeeList($this->event->getId())['attendeeList'][$this->event->getId()];
	}

	/**
	 * @param string $to
	 * @param int $guestId
	 * @return bool
	 */
	abstract public function notifyAboutMeetingStatus(string $to): bool;

	/**
	 * @param string $to
	 * @return bool
	 */
	abstract public function notifyAboutSharingEventEdit(string $to): bool;
}
