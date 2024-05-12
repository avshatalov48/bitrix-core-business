<?php

namespace Bitrix\Calendar\Core\Queue\Processor;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\Message;
use Bitrix\Calendar\Core\Queue\Producer\Producer;
use Bitrix\Calendar\ICal\MailInvitation\InvitationInfo;
use Bitrix\Calendar\ICal\MailInvitation\SenderInvitation;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Emoji;
use TypeError;

class SendingEmailNotification implements Interfaces\Processor
{
	private const MAX_ATTEMPTS_INVITATION = 3;
	private const LOG_MARKER = 'DEBUG_CALENDAR_EMAIL_NOTIFICATION';

	private Logger $logger;

	public function __construct()
	{
		$this->logger = new Logger(self::LOG_MARKER);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws NotImplementedException
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function process(Interfaces\Message $message): string
	{
		$serializedNotificationData = $this->getSerializedNotificationData($message);
		if (is_null($serializedNotificationData))
		{
			return self::REJECT;
		}

		try
		{
			$notificationData = $this->unserializeNotificationData($serializedNotificationData);
		}
		catch (TypeError $exception)
		{
			$this->logger->log($exception);

			return self::REJECT;
		}

		if (!$this->isNotificationFieldsCorrect($notificationData))
		{
			$this->logger->log($serializedNotificationData);

			return self::REJECT;
		}

		$invitation = new InvitationInfo(
			$notificationData['eventId'],
			$notificationData['addresserId'],
			$notificationData['receiverId'],
			$notificationData['type'],
			$notificationData['changeFields'] ?? [],
			$notificationData['counterInvitation'] + 1,
		);

		$notification = $invitation->getSenderInvitation();
		if (is_null($notification))
		{
			$this->logger->log($serializedNotificationData);

			return self::REJECT;
		}

		$this->setLanguageId();
		if ($notification->send())
		{
			$notification->executeAfterSuccessfulInvitation();

			return self::ACK;
		}

		if ($notification->getCountAttempsSend() < self::MAX_ATTEMPTS_INVITATION)
		{
			self::sendMessageToQueue($invitation->toArray());

			return self::ACK;
		}

		$failSent = [];
		$failSent[$notification->getEventParentId()] = $this->getDataForNotify($notification);
		$this->sendFailSendNotify($failSent);

		return self::REJECT;
	}

	private function getSerializedNotificationData(Interfaces\Message $message): ?string
	{
		$messageBody = $message->getBody();
		if (!is_array($messageBody) || !isset($messageBody['requestInvitation']))
		{
			return null;
		}

		return $messageBody['requestInvitation'];
	}

	private function isNotificationFieldsCorrect (mixed $notification): bool
	{
		if (!is_array($notification))
		{
			return false;
		}

		return isset(
			$notification['eventId'],
			$notification['addresserId'],
			$notification['receiverId'],
			$notification['type'],
			$notification['counterInvitation']
		);
	}

	private function unserializeNotificationData(string $serializeNotificationData): mixed
	{
		$notification = str_replace("\'", "'", $serializeNotificationData);
		$notification = Emoji::decode($notification);

		return unserialize($notification, ['allowed_classes' => false]);
	}

	private function sendFailSendNotify(array $failSent): void
	{
		foreach ($failSent as $parentId => $item)
		{
			if (isset($item[0]))
			{
				$item = $item[0];
			}

			\CCalendarNotify::Send([
				'mode' => 'fail_ical_invite',
				'eventId' => $parentId,
				'userId' => $item['userId'],
				'guestId' => $item['userId'],
				'items' => [$item],
				'name' => $item['name'],
				'icalMethod' => $item['method'],
			]);
		}
	}

	private function getDataForNotify(SenderInvitation $sender): array
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
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function setLanguageId(): void
	{
		$siteDb = SiteTable::getById(SITE_ID);
		if ($site = $siteDb->fetchObject())
		{
			Loc::setCurrentLang($site->getLanguageId());
		}
	}

	/**
	 * @throws BaseException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function sendMessageToQueue(array $invitation): void
	{
		$serializedData = str_replace("'", "\'", serialize($invitation));
		$serializedData = Emoji::encode($serializedData);

		$message = (new Message())
			->setBody(['requestInvitation' => $serializedData])
			->setRoutingKey('calendar:sending_email_notification')
		;

		(new Producer())->send($message);
	}

	/**
	 * @param SenderInvitation[] $invitations
	 * @return void
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public static function sendBatchOfMessagesToQueue(array $invitations): void
	{
		$messages = [];

		if (!is_iterable($invitations))
		{
			AddMessage2Log('Ical senders collection is not iterable', 'calendar', 4);
			return;
		}

		foreach ($invitations as $invitation)
		{
			$serializedData = str_replace("'", "\'", serialize($invitation));
			$serializedData = Emoji::encode($serializedData);

			$messages[] = (new Message())
				->setBody(['requestInvitation' => $serializedData])
				->setRoutingKey('calendar:sending_email_notification')
			;
		}

		(new Producer())->sendBatch($messages);
	}
}