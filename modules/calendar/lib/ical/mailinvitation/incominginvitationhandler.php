<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\ICal\Builder\AttendeesCollection;

abstract class IncomingInvitationHandler
{
	abstract public function handle();

	protected function getAttendeesCollection(?array $parsedAttendees): AttendeesCollection
	{
		$attendeesCollection = AttendeesCollection::createInstance();

		if (!is_null($parsedAttendees))
		{
			foreach($parsedAttendees as $attendee)
			{
				$participant = new Attendee();
				$participant->setMailto(explode(':', $attendee['value']))[1];
				$participant->setEmail($attendee['parameter']['email'] ?? $attendee->getMailTo());
				$name = explode(" ", trim($attendee['parameter']['cn'], '"'), 2);
				if (empty($name[0]))
				{
					$participant->setName($participant->getEmail());
				}
				else
				{
					$participant->setName($name[0]);
					$participant->setLastName($name[1] ?? '');
				}
				$participant->setStatus($attendee['parameter']['partstat']);
				$participant->setRole($attendee['parameter']['role']);
				$participant->setCutype($attendee['parameter']['cutype']);

				$attendeesCollection->add($participant);
			}
		}

		return $attendeesCollection;
	}

	/**
	 * @param string|null $value
	 * @return string|null
	 */
	protected function getMailTo(?string $value): ?string
	{
		return mb_strpos($value, ':')
			? (explode(':', $value))[1]
			: $value;
	}
}