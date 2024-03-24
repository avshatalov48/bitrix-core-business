<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\SerializeObject;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use \Serializable;

/**
 * Class SenderInvitation
 * @package Bitrix\Calendar\ICal\MailInvitation
 */
abstract class SenderInvitation implements Serializable
{
	use SerializeObject;

	public const CHARSET = 'utf-8';
	public const CONTENT_TYPE = 'text/calendar';
	public const DECISION_YES = 'Y';
	public const DECISION_NO = 'N';
	protected const ATTACHMENT_NAME = 'invite.ics';
	protected const MAIL_TEMPLATE = 'SEND_ICAL_INVENT';

	/**
	 * @var int
	 */
	protected int $counterInvitations = 0;
	/**
	 * @var array|null
	 */
	protected ?array $event = null;
	/**
	 * @var Context|null
	 */
	protected ?Context $context = null;
	/**
	 * @var string|null
	 */
	protected ?string $uid = '';

	abstract public function executeAfterSuccessfulInvitation();
	abstract protected function getContent();
	abstract protected function getMailEventField();
	abstract protected function getSubjectTitle();

	public static function createInstance(array $event, Context $context): SenderInvitation
	{
		return new static($event, $context);
	}

	public function __construct(array $event, Context $context)
	{
		$this->event = $event;
		$this->context = $context;
	}

	public function setCounterInvitations(?int $counterInvitations): static
	{
		$this->counterInvitations = $counterInvitations ?? 0;
		return $this;
	}

	public function setEvent(?array $event): static
	{
		$this->event = $event;
		return $this;
	}

	/**
	 * @return bool
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws NotImplementedException
	 */
	public function send(): bool
	{
		if ($this->event === null || $this->context === null)
		{
			return false;
		}

		$this->prepareEventFields();

		$content = $this->getContent();
		if (!$content)
		{
			return false;
		}

		$status = \CEvent::sendImmediate(
			self::MAIL_TEMPLATE,
			SITE_ID,
			$this->getMailEventField(),
			"Y",
			"",
			[],
			'',
			$content
		);

		return $status === Event::SEND_RESULT_SUCCESS;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return static::METHOD;
	}

	/**
	 * @return array
	 */
	public function getEvent(): array
	{
		return $this->event ?? [];
	}

	/**
	 *
	 */
	public function incrementCounterInvitations(): void
	{
		$this->counterInvitations++;
	}

	/**
	 * @return string
	 */
	public function getUid(): string
	{
		return $this->uid;
	}

	/**
	 * @return int
	 */
	public function getCountAttempsSend(): int
	{
		return $this->counterInvitations;
	}

	/**
	 * @return MailAddresser
	 */
	public function getAddresser():MailAddresser
	{
		return $this->context->getAddresser();
	}

	/**
	 * @return MailReceiver
	 */
	public function getReceiver(): MailReceiver
	{
		return $this->context->getReceiver();
	}

	/**
	 * @return int
	 */
	public function getEventId(): ?int
	{
		return $this->event['ID'] ?? null;
	}

	/**
	 * @return int
	 */
	public function getEventParentId(): int
	{
		return (int) ($this->event['PARENT_ID'] ?? 0);
	}

	/**
	 * @return string
	 */
	protected function getBodyMessage(): string
	{
		//@TODO edit body message
		return 'ical body message';
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function getDateForTemplate(): string
	{
		$res = Helper::getIcalTemplateDate([
			'DATE_FROM' => $this->event['DATE_FROM'],
			'DATE_TO' => $this->event['DATE_TO'],
			'TZ_FROM' => $this->event['TZ_FROM'],
			'TZ_TO' => $this->event['TZ_TO'],
			'FULL_DAY' => $this->event['SKIP_TIME'],
		]);
		$offset = (Helper::getDateObject(null, false, $this->event['TZ_FROM']))->format('P');
		$res .= ' (' . $this->event['TZ_FROM'] . ', ' . 'UTC' . $offset . ')';

		if (isset($this->event['RRULE']['FREQ']) && $this->event['RRULE']['FREQ'] !== 'NONE')
		{
			$rruleString = Helper::getIcalTemplateRRule($this->event['RRULE'],
				[
					'DATE_FROM' => $this->event['DATE_FROM'],
					'DATE_TO' => $this->event['DATE_TO'],
					'TZ_FROM' => $this->event['TZ_FROM'],
					'TZ_TO' => $this->event['TZ_TO'],
					'FULL_DAY' => $this->event['SKIP_TIME'],
				]
			);
			$res .= ', (' . $rruleString . ')';
		}

		return $res;
	}

	/**
	 * @return string
	 */
	protected function getFilesLink(): string
	{
		$result = "";

		if (is_iterable($this->event['ICAL_ATTACHES']))
		{
			foreach ($this->event['ICAL_ATTACHES'] as $attach)
			{
				if ($attach instanceof Attach)
				{
					$result .= "<a href=\"{$attach->getLink()}\"> {$attach->getName() }</a> <br />";
				}
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getSiteName(): string
	{
		if (!empty($siteName = \COption::GetOptionString("main", "site_name", '', '-')))
		{
			return "[{$siteName}]";
		}

		return '';
	}

	/**
	 * @return string
	 */
	protected function getSubjectMessage(): string
	{
		return $this->getSiteName(). ' ' . $this->getSubjectTitle();
	}

	/**
	 * @return string
	 */
	protected function getMessageId(): string
	{
		$serverName = \COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
		return "<CALENDAR_EVENT_{$this->getEventParentId()}@{$serverName}>";
	}

	/**
	 * @return string
	 */
	protected function getAttendeesListForTemplate(): string
	{
		if ($this->event['MEETING']['HIDE_GUESTS'])
		{
			return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_HIDE_GUESTS_INFORMATION');
		}

		return $this->event['ICAL_ATTENDEES'];
	}

	/**
	 * @return int
	 */
	protected function getEventDateCreateTimestamp(): int
	{
		return (int) Util::getTimestamp($this->event['CREATED']);
	}

	/**
	 * @return int
	 */
	protected function getEventOwnerId(): int
	{
		return (int) $this->event['OWNER_ID'];
	}

	private function getFormattedDate(DateTime $formattedDate, string $dtSkipTime): string
	{
		$timestamp = \CCalendar::Timestamp($formattedDate, false, $dtSkipTime !== 'Y');

		return \CCalendar::Date($timestamp);
	}


	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws NotImplementedException
	 */
	protected function prepareEventFields(): void
	{
		$dtSkipTime = $this->event['DT_SKIP_TIME'];
		$this->event['DATE_FROM'] = $this->getFormattedDate($this->event['DATE_FROM'], $dtSkipTime);
		$this->event['DATE_TO'] = $this->getFormattedDate($this->event['DATE_TO'], $dtSkipTime);
		$this->event['CREATED'] = $this->getFormattedDate($this->event['DATE_CREATE'], false);
		$this->event['MODIFIED'] = $this->getFormattedDate($this->event['TIMESTAMP_X'], false);

		$this->event['SKIP_TIME'] = $dtSkipTime === 'Y';
		$this->event['MEETING'] = unserialize($this->event['MEETING'], ['allowed_classes' => false]);
		$this->event['REMIND'] = unserialize($this->event['REMIND'], ['allowed_classes' => false]);
		$this->event['RRULE'] = \CCalendarEvent::ParseRRULE($this->event['RRULE']);
		$this->event['ATTENDEES_CODES'] = !empty($this->event['ATTENDEES_CODES']) && is_string($this->event['ATTENDEES_CODES'])
			? explode(',', $this->event['ATTENDEES_CODES'])
			: []
		;
		$this->event['ICAL_ATTENDEES'] = Helper::getAttendeesByEventParentId($this->event['PARENT_ID']);
		$this->event['ICAL_ORGANIZER'] = Helper::getAttendee($this->event['MEETING_HOST'], $this->event['PARENT_ID'], false);
		$this->event['TEXT_LOCATION'] = \CCalendar::GetTextLocation($this->event['LOCATION'] ?? null);
		$this->event['ICAL_ATTACHES'] = Helper::getMailAttaches(
			null,
			$this->event['MEETING_HOST'],
			$this->event['PARENT_ID']
		);

		unset($this->event['DT_SKIP_TIME'], $this->event['DATE_CREATE'], $this->event['TIMESTAMP_X']);
	}
}
