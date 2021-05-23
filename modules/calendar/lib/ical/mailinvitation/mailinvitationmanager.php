<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\ICal\Builder\AttendeesCollection;
use Bitrix\Calendar\ICal\Builder\AttachCollection;
use Bitrix\Main\LoaderException;
use CAgent;
use CCalendarNotify;

/**
 * Class MailInvitationManager
 * @package Bitrix\Calendar\ICal\MailInvitation
 */
class MailInvitationManager
{
	private const MAX_ATTEMPS_INVITATION = 3;


	/**
	 * @param $serializedSenders
	 * @throws LoaderException
	 */
	public static function manageSendingInvitation($serializedSenders): void
	{
		$sendersCollection = self::unserializeMailSendersBatch($serializedSenders);
		$unsuccessfulSent = [];
		$failSent = [];

		if (!is_iterable($sendersCollection))
		{
			AddMessage2Log('Ical senders collection is not iterable', 'calendar', 4);
		}
		else
		{
			foreach ($sendersCollection as $sender)
			{
				if ($sender instanceof SenderInvitation)
				{
					$sender->incrementCounterInvitations();
					$currentSender = clone $sender;

					if ($sender->send())
					{
						$sender->executeAfterSuccessfulInvitation();
					}
					elseif ($sender->getCountAttempsSend() < self::MAX_ATTEMPS_INVITATION)
					{
						$unsuccessfulSent[] = $currentSender;
					}
					else
					{
						$failSent[$sender->getEventParentId()] = self::getDataForNotify($sender);
					}
				}
			}
		}

		if (count($unsuccessfulSent) > 0)
		{
			self::createAgentSent($unsuccessfulSent);
		}

		if (count($failSent) > 0)
		{
			self::sentFailSentNotify($failSent);
		}
	}

	/**
	 * @param SenderInvitation $sender
	 * @return array
	 */
	private static function getDataForNotify(SenderInvitation $sender): array
	{
		$event = $sender->getEvent();
		return [
			'email' => $sender->getReceiver()->getEmail(),
			'eventId' => $event['PARENT_ID'],
			'name' => $event['NAME'],
			'userId' => $event['MEETING_HOST'],
			'method' => $sender->getMethod(),
		];
	}

	/**
	 * @param array $sendersCollection
	 */
	public static function createAgentSent(array $sendersCollection): void
	{
//		$nextAgentDate = DateTime::createFromTimestamp(strtotime('now') + 10)->format(Date::convertFormatToPhp(FORMAT_DATETIME));
		CAgent::addAgent(
			"\\Bitrix\\Calendar\\ICal\\MailInvitation\\MailInvitationManager::manageSendingInvitation('" . serialize($sendersCollection) . "');",
			"calendar",
			"N",
			0,
			"",
			"Y",
			""
		);
	}

	/**
	 * @param array $failSent
	 */
	private static function sentFailSentNotify(array $failSent): void
	{
		foreach ($failSent as $parentId => $item)
		{
			CCalendarNotify::Send([
				'mode' => 'fail_ical_invite',
				'eventId' => $parentId,
				'userId' => $item[0]['userId'],
				'guestId' => $item[0]['userId'],
				'items' => $item,
				'name' => $item[0]['name'],
				'icalMethod' => $item[0]['method'],
			]);
		}
	}

	/**
	 * @param string $serializedSenders
	 * @return mixed
	 */
	private static function unserializeMailSendersBatch(string $serializedSenders)
	{
		return unserialize($serializedSenders, ['allowed_classes' => [
			AttachCollection::class,
			Attach::class,
			AttendeesCollection::class,
			MailAddresser::class,
			MailReceiver::class,
			Attendee::class,
			SenderRequestInvitation::class,
			SenderEditInvitation::class,
			SenderCancelInvitation::class,
			Context::class
		]]);
	}
}