<?php


namespace Bitrix\Calendar\ICal;


use Bitrix\Calendar\ICal\Basic\{AttachmentManager, Dictionary};
use Bitrix\Mail\User;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\ICal\Builder\
{
	AttendeesCollection,
	Calendar,
	Event,
	EventFactoryInterface,
	StandardObservances,
	Timezone};
use Bitrix\Calendar\Util;

class OutcomingAttachmentManager extends AttachmentManager
{
	private ?array $event;
	private ?AttendeesCollection $attendees;
	private ?string $attachment = '';
	private ?string $method;
	private ?string $uid = '';

	public function __construct($data, $attendees, $method)
	{
		$this->event = $data;
		$this->attendees = $attendees;
		$this->method = $method;
	}

	public function prepareRequestAttachment(): OutcomingAttachmentManager
	{
		$requestEvent = $this->prepareRequestEvent();
		$this->uid = $requestEvent['DAV_XML_ID'];

		$event = Event::create($requestEvent, EventFactoryInterface::REQUEST)
			->setAttendees($this->attendees)
			->setOrganizer($this->attendees[$requestEvent['MEETING_HOST']], $this->getReplyAddress());

		$this->attachment = Calendar::createInstance()
			->setMethod(Dictionary::METHODS[$this->method])
			->setTimezones(Timezone::createInstance()
				->setTimezoneId($requestEvent['TZ_FROM'])
				->setObservance(StandardObservances::createInstance()
					->setOffsetFrom($requestEvent['TZ_FROM'])
					->setOffsetTo($requestEvent['TZ_TO'])
					->setDTStart()
				)
			)
			->addEvent($event)
			->get();

		return $this;
	}

	public function prepareReplyAttachment(): OutcomingAttachmentManager
	{
		$this->uid = $this->event['DAV_XML_ID'];

		$event = Event::create($this->event, EventFactoryInterface::REPLY)
			->setAttendees($this->attendees);

		$this->attachment = Calendar::createInstance()
			->setMethod(Dictionary::METHODS[$this->method])
			->addEvent($event)
			->get();

		return $this;
	}

	public function prepareCancelAttachment(): OutcomingAttachmentManager
	{
		$event = Event::create($this->event, EventFactoryInterface::CANCEL)
			->setAttendees($this->attendees)
			->setOrganizer($this->attendees[$this->event['MEETING_HOST']], $this->getReplyAddress());

		$this->attachment = Calendar::createInstance()
			->setMethod(Dictionary::METHODS[$this->method])
			->addEvent($event)
			->get();

		return $this;
	}

	public function getAttachment(): string
	{
		return $this->attachment;
	}

	public function getUid(): ?string
	{
		return $this->uid;
	}

	private function getReplyAddress(): string
	{
		if (Loader::includeModule('mail'))
		{
			[$replyTo, $backUrl] = User::getReplyTo(
				SITE_ID,
				$this->event['OWNER_ID'],
				'ICAL_INVENT',
				$this->event['PARENT_ID'],
				SITE_ID
			);
		}

		return $replyTo;
	}

	private function prepareRequestEvent(): array
	{
		$event = $this->event;

		if (!empty($event['ATTACHES']))
		{
			$filesDesc = [];
			foreach ($event['ATTACHES'] as $attach)
			{
				$filesDesc[] = $attach['name'] . ' (' . $attach['link'] . ')';
			}

			if (!empty($event['DESCRIPTION']))
			{
				$event['DESCRIPTION'] .= "\r\n";
			}
			$event['DESCRIPTION'] .= Loc::getMessage('EC_FILES_TITLE') . ': ' . implode(', ', $filesDesc);
		}

		return $event;
	}
}
