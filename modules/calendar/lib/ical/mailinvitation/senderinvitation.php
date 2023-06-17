<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\SerializeObject;
use Bitrix\Calendar\Util;
use Bitrix\Mail;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use CEvent;
use COption;
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

	/**
	 * @return bool
	 * @throws LoaderException
	 */
	public function send(): bool
	{
		if ($this->event === null || $this->context === null)
		{
			return false;
		}

		$this->checkEventOrganizer();
		$this->checkAddresserEmail();
		$this->prepareEventFields();

		$content = $this->getContent();
		if (!$content)
		{
			return true;
		}

		$status = CEvent::sendImmediate(
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
	 * @return MailReceiver
	 */
	public function getReceiver(): MailReceiver
	{
		return $this->context->getReceiver();
	}

	/**
	 * @return int
	 */
	public function getEventId(): int
	{
		return (int) $this->event['ID'];
	}

	/**
	 * @return int
	 */
	public function getEventParentId(): int
	{
		return (int) ($this->event['PARENT_ID'] ?? 0);
	}

	/**
	 * @throws LoaderException
	 */
	protected function checkAddresserEmail(): void
	{
		if ($this->context && Loader::includeModule('mail') && empty($this->context->getAddresser()->getMailto()))
		{
			$boxes = Mail\MailboxTable::getUserMailboxes($this->event['MEETING_HOST']);
			if (!empty($boxes) && is_array($boxes))
			{
				$this->context->getAddresser()->setMailto(array_shift($boxes)['EMAIL']);
			}
		}
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
		if (!empty($siteName = COption::GetOptionString("main", "site_name", '', '-')))
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
		$serverName = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
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

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function prepareEventFields(): void
	{
		$this->event['DESCRIPTION'] = Helper::getEventDescriptionById((int) $this->event['ID']);
		$this->event['TEXT_LOCATION'] ??= null;
		if (is_array($this->event['TEXT_LOCATION']) && isset($this->event['TEXT_LOCATION']['NEW']))
		{
			$this->event['TEXT_LOCATION'] = $this->event['TEXT_LOCATION']['NEW'];
		}
		elseif (!is_string($this->event['TEXT_LOCATION']))
		{
			$this->event['TEXT_LOCATION'] = null;
		}
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function checkEventOrganizer(): void
	{
		if (empty($this->event['ICAL_ORGANIZER']) && $user = Helper::getUserById($this->event['MEETING_HOST'] ?? null))
		{
			$this->event['ICAL_ORGANIZER'] = Attendee::createInstance(
				$user['EMAIL'],
				$user['NAME'],
				$user['LAST_NAME'],
				null,
				null,
				null,
				$user['EMAIL']
			);
		}
	}
}
