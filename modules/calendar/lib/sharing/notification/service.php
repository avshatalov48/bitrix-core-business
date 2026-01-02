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
	protected ?CrmDealLink $crmDealLink;
	protected ?Event $oldEvent = null;
	protected ?int $initiatorId = null;
	private array $users = [];

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

	public function setInitiatorId(?int $id): self
	{
		$this->initiatorId = $id;

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
		$ownerId = (int)$this->eventLink->getOwnerId();

		return $this->getUser($ownerId);
	}

	protected function getInitiator(): array
	{
		if ($this->initiatorId === null)
		{
			return $this->getOwner();
		}

		return $this->getUser($this->initiatorId);
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
		$attendeeListResult = \CCalendarEvent::getAttendeeList([$this->event->getId()]);

		return $attendeeListResult['attendeeList'][$this->event->getId()];
	}

	private function getUser(int $id): array
	{
		if (in_array($id, $this->users, true))
		{
			return $this->users[$id];
		}

		$userInfo = Sharing\Helper::getUserInfo($id);

		$eventAttendees = $this->getAttendeesList();
		$userAttendeeInfo = current(array_filter($eventAttendees, static function(array $attendee) use ($id): bool {
			$attendeeId = $attendee['id'] ?? null;

			return $attendeeId === $id;
		}));

		if (isset($userAttendeeInfo['status']) && !in_array($userAttendeeInfo['status'], ['Q', 'Y', 'N'], true))
		{
			$userAttendeeInfo['status'] = 'Q';
		}

		$this->users[$id] = [
			'ID' => $id,
			'NAME' => "{$userInfo['name']} {$userInfo['lastName']}",
			'PHOTO' => $userInfo['photo'],
			'GENDER' => $userInfo['gender'],
			'STATUS' => $userAttendeeInfo['status'] ?? 'Q',
		];

		return $this->users[$id];
	}

	/**
	 * @param string $to
	 * @return bool
	 */
	abstract public function notifyAboutMeetingStatus(string $to): bool;

	/**
	 * @param string $to
	 * @return bool
	 */
	abstract public function notifyAboutSharingEventEdit(string $to): bool;
}
