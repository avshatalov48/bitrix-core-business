<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\SerializeObject;
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
	protected const ATTACHMENT_NAME = 'invite.ics';
	public const CHARSET = 'utf-8';
	public const CONTENT_TYPE = 'text/calendar';
	protected const MAIL_TEMPLATE = 'SEND_ICAL_INVENT';

	/**
	 * @var int
	 */
	protected $counterInvitations = 0;
	/**
	 * @var array
	 */
	protected $event;
	/**
	 * @var Context
	 */
	protected $context;
	/**
	 * @var string
	 */
	protected $uid = '';

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
//		$this->checkOrganizerEmail();
		$this->prepareEventFields();

		$status = CEvent::sendImmediate(
			self::MAIL_TEMPLATE,
			SITE_ID,
			$this->getMailEventField(),
			"Y",
			"",
			[],
			'',
			$this->getContent()
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
		return $this->event;
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
		return (int) $this->event['PARENT_ID'];
	}

	/**
	 * @throws LoaderException
	 */
	protected function checkOrganizerEmail(): void
	{
		if (Loader::includeModule('mail') && empty($this->context->getAddresser()->getMailto()))
		{
			$boxes = Mail\MailboxTable::getUserMailboxes($this->event['MEETING_HOST']);
			if (is_array($boxes) && !empty($boxes))
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
			return "[$siteName] ";
		}

		return '';
	}

	/**
	 * @return string
	 */
	protected function getSubjectMessage(): string
	{
		return "{$this->getSiteName()}" . $this->getSubjectTitle();
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
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function prepareEventFields(): void
	{
		$this->event['DESCRIPTION'] = Helper::getEventDescriptionById((int) $this->event['ID']);
	}
}
