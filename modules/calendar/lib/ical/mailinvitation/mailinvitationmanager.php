<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\ICal\Builder\AttendeesCollection;
use Bitrix\Calendar\ICal\Builder\AttachCollection;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
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
		$serializedSenders = str_replace("\'", "'", $serializedSenders);
		$serializedSenders = \Bitrix\Main\Text\Emoji::decode($serializedSenders);
		$sendersCollection = self::unserializeMailSendersBatch($serializedSenders);

		if (!is_iterable($sendersCollection))
		{
			AddMessage2Log('Ical senders collection is not iterable', 'calendar', 4);
		}
		else
		{
			$unsuccessfulSent = [];
			$failSent = [];
			foreach ($sendersCollection as $sender)
			{
				if ($sender instanceof SenderInvitation)
				{
					self::setLanguageId();
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

			if (!empty($unsuccessfulSent))
			{
				self::createAgentSent($unsuccessfulSent);
			}

			if (!empty($failSent))
			{
				self::sentFailSentNotify($failSent);
			}
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
		// TODO: it's better to avoid serialized data in the agent parameters, maybe use QueueManager here
		$serializedData = str_replace("'", "\'", serialize($sendersCollection));
		$agentName = "\\Bitrix\\Calendar\\ICal\\MailInvitation\\MailInvitationManager::manageSendingInvitation('"
			. $serializedData
			. "');";
		$agentName = \Bitrix\Main\Text\Emoji::encode($agentName);

		// Workaround to avoid deserialization bug like mantis#162578
		// We need length in bytes not in symbols
		if (strlen($agentName) < 65000)
		{
			CAgent::addAgent(
				$agentName,
				"calendar",
				"N",
				0,
				"",
				"Y",
				""
			);
		}
	}

	/**
	 * @param array $failSent
	 */
	private static function sentFailSentNotify(array $failSent): void
	{
		foreach ($failSent as $parentId => $item)
		{
			if (isset($item[0]))
			{
				$item = $item[0];
			}
			CCalendarNotify::Send([
				'mode' => 'fail_ical_invite',
				'eventId' => $parentId,
				'userId' => $item['userId'],
				'guestId' => $item['userId'],
				'items' => $item,
				'name' => $item['name'],
				'icalMethod' => $item['method'],
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

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function setLanguageId(): void
	{
		$siteDb = \Bitrix\Main\SiteTable::getById(SITE_ID);
		if ($site = $siteDb->fetchObject())
		{
			Loc::setCurrentLang($site->getLanguageId());
		}
	}
}
