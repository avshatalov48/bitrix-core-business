<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Public;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\SerializeObject;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CEvent;
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
	abstract protected function getTemplateParams();
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

		$fields = $this->getMailEventFields();
		$params = array_merge($this->getBaseTemplateParams(), $this->getTemplateParams());

		$status = CEvent::sendImmediate(
			self::MAIL_TEMPLATE,
			SITE_ID,
			array_merge($fields, $params),
			'Y',
			'',
			[],
			'',
			$content,
		);

		return $status === Event::SEND_RESULT_SUCCESS;
	}

	protected function getMailEventFields(): array
	{
		return [
			"=Reply-To" => "{$this->getAddresser()->getFullName()} <{$this->getAddresser()->getEmail()}>",
			"=From" => "{$this->getAddresser()->getFullName()} <{$this->getAddresser()->getEmail()}>",
			"=Message-Id" => $this->getMessageId(),
			"=In-Reply-To" => $this->getMessageReplyTo(),
			'EMAIL_FROM' => $this->getAddresser()->getEmail(),
			'EMAIL_TO' => $this->getReceiver()->getEmail(),
			'MESSAGE_SUBJECT' => $this->getSubjectMessage(),
			'MESSAGE_PHP' => $this->getBodyMessage(),
		];
	}

	private function getBaseTemplateParams(): array
	{
		$detailLink = Public\PublicEvent::getDetailLink(
			$this->getEventId(),
			$this->getEventOwnerId(),
			$this->getEventDateCreateTimestamp(),
		);

		return [
			'EVENT_NAME' => $this->event['NAME'],
			'DATE_FROM' => $this->event['DATE_FROM'],
			'DATE_TO' => $this->event['DATE_TO'],
			'IS_FULL_DAY' => $this->event['DT_SKIP_TIME'] === 'Y',
			'TZ_FROM' =>  $this->event['TZ_FROM'],
			'TZ_TO' =>  $this->event['TZ_TO'],
			'AVATARS' => $this->event['AVATARS'],
			'OWNER_STATUS' => $this->event['OWNER_STATUS'],
			'RRULE' => $this->getRRuleString(),

			'DETAIL_LINK' => $detailLink,
			'ICS_LINK' => $detailLink . Public\PublicEvent::ACTION_ICS,
			'DECISION_YES_LINK' => $detailLink . Public\PublicEvent::ACTION_ACCEPT,
			'DECISION_NO_LINK' => $detailLink . Public\PublicEvent::ACTION_DECLINE,
			'BITRIX24_LINK' => Sharing\Helper::getBitrix24Link(),
		];
	}

	protected function getMessageReplyTo(): string
	{
		return $this->getMessageId();
	}

	/**
	 * @return string
	 */
	protected function getRRuleString(): string
	{
		$rrule = \CCalendarEvent::ParseRRULE($this->event['RRULE']);
		if (is_array($rrule))
		{
			return Helper::getIcalTemplateRRule(
				$rrule,
				[
					'DATE_FROM' => $this->event['DATE_FROM'],
				],
			);
		}

		return '';
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
	 * @return int
	 */
	protected function getEventDateCreateTimestamp(): int
	{
		return (int) Util::getTimestamp($this->event['DATE_CREATE']);
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
		$this->event['DATE_CREATE'] = $this->event['DATE_CREATE']->toString();
		$this->event['TIMESTAMP_X'] = $this->event['TIMESTAMP_X']->toString();

		$this->event['MEETING'] = unserialize($this->event['MEETING'], ['allowed_classes' => false]);
		$this->event['REMIND'] = unserialize($this->event['REMIND'], ['allowed_classes' => false]);
		$this->event['RRULE'] = \CCalendarEvent::ParseRRULE($this->event['RRULE']);
		$this->event['ATTENDEES_CODES'] = !empty($this->event['ATTENDEES_CODES']) && is_string($this->event['ATTENDEES_CODES'])
			? explode(',', $this->event['ATTENDEES_CODES'])
			: []
		;
		$this->event['ICAL_ORGANIZER'] = Helper::getAttendee($this->event['MEETING_HOST'], $this->event['PARENT_ID'], false);
		$this->event['ICAL_ATTACHES'] = Helper::getMailAttaches(
			null,
			$this->event['MEETING_HOST'],
			$this->event['PARENT_ID']
		);

		$event = (new Core\Mappers\Event())->getByArray($this->event);
		$this->event['DESCRIPTION'] = Public\PublicEvent::prepareEventDescriptionForIcs($event);

		$attendees = \CCalendarEvent::GetAttendees([$this->event['PARENT_ID']], false)[$this->event['PARENT_ID']] ?? [];

		$ownerId = (int)$this->event['OWNER_ID'];
		$owner = current(array_filter(
			$attendees,
			static fn($attendee) => (int)$attendee['USER_ID'] === $ownerId,
		));

		$this->event['OWNER_STATUS'] = $owner['STATUS'];

		$this->event['AVATARS'] = [];
		if (!$this->event['MEETING']['HIDE_GUESTS'])
		{
			usort($attendees, static fn($a, $b) => ((int)$b['USER_ID'] === $ownerId) - ((int)$a['USER_ID'] === $ownerId));
			foreach ($attendees as $attendee)
			{
				$this->event['AVATARS'][] = $attendee['AVATAR'];
			}
			$this->event['ICAL_ATTENDEES'] = Helper::getAttendeesByEventParentId($this->event['PARENT_ID']);
		}
	}
}
