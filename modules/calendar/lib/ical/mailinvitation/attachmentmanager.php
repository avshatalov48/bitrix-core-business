<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Basic\RecurrenceRuleProperty;
use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Mail\User;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\Date;

abstract class AttachmentManager
{
	/**
	 * @var array
	 */
	protected $event = [];
	/**
	 * @var string
	 */
	protected $uid;

	abstract public function getContent(): string;

	/**
	 * @param array $event
	 * @return AttachmentManager
	 */
	public static function createInstance(array $event): AttachmentManager
	{
		return new static($event);
	}

	/**
	 * AttachmentManager constructor.
	 * @param array $event
	 */
	public function __construct(array $event)
	{
		$this->event = $event;
	}

	/**
	 * @return string
	 */
	public function getUid(): ?string
	{
		return $this->uid;
	}

	/**
	 * @param string|null $description
	 * @return string
	 */
	protected function prepareDescription(string $description = null): ?string
	{
		if (
			empty($description)
			&& (empty($this->event['ICAL_ATTACHES'])
				|| empty($this->event['ICAL_ATTACHES']->getCollection())
			)
		)
		{
			return null;
		}

		$description = $this->parseText($description);

		if (empty($this->event['ICAL_ATTACHES']->getCollection()))
		{
			return str_replace("\r\n", " \n", $description);
		}

		return str_replace("\r\n", " \n", $description . "\n" . $this->getFilesDescription());
	}

	/**
	 * @param mixed $rrule
	 * @return RecurrenceRuleProperty|null
	 */
	protected function prepareRecurrenceRule($rrule): ?RecurrenceRuleProperty
	{
		return is_array($rrule)
			? new RecurrenceRuleProperty($rrule)
			: null;
	}

	/**
	 * @return string
	 */
	protected function getFilesDescription(): string
	{
		if (empty($this->event['ICAL_ATTACHES']->getCollection()))
		{
			return '';
		}

		$filesDescription = [];
		if (is_iterable($this->event['ICAL_ATTACHES']))
		{
			foreach ($this->event['ICAL_ATTACHES'] as $attach)
			{
				if ($attach instanceof Attach)
				{
					$filesDescription[] = "{$attach->getName()} ({$attach->getLink()})";
				}
			}
		}

		return Loc::getMessage('EC_FILES_TITLE') . ":\n" . implode("\n", $filesDescription) ."";
	}

	/**
	 * @param string|null $exDates
	 * @return Date[]|null
	 * @throws ObjectException
	 */
	protected function prepareExDate(string $exDates = null): ?Date
	{
		return !$exDates
			? null
			: array_map(function ($exDate) {
				return new Date($exDate, 'd.m.Y');
			}, explode(';', $exDates));
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function getOrganizerMailTo(): string
	{
		if (Loader::includeModule('mail'))
		{
			$boxes = \Bitrix\Mail\MailboxTable::getUserMailboxes($this->event['MEETING_HOST']);
			$organizer = $this->event['ICAL_ORGANIZER'];
			if ($organizer === null)
			{
				$user = Helper::getUserById($this->event['MEETING_HOST']);
				$organizer = Attendee::createInstance(
					$user['EMAIL'],
					$user['NAME'],
					$user['LAST_NAME'],
					null,
					null,
					null,
					$user['EMAIL']
				);
			}

			foreach ($boxes as $box)
			{
				/** @var Attendee $organizer */
				if ($box['EMAIL'] === $organizer->getMailTo())
				{
					return $organizer->getMailTo();
				}
			}

			return $this->getReplyAddress();
		}
	}

	protected function getReplyAddress(): string
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

			return $replyTo;
		}
	}

	/**
	 * @param string|null $description
	 * @return string
	 */
	protected function parseText(?string $description): string
	{
		if (!$description)
		{
			return '';
		}

		return \CTextParser::clearAllTags($description);
	}
}
