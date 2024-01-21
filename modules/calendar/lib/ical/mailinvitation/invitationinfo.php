<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\ICal\MailInvitation\Exception\MemberNotFoundException;
use Bitrix\Calendar\ICal\MailInvitation\Factory\SenderInvitationFactory;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Main\EO_User;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\UserTable;

class InvitationInfo implements Arrayable
{
	public const TYPE_REQUEST = 'request';
	public const TYPE_CANCEL = 'cancel';
	public const TYPE_EDIT = 'edit';

	private int $eventId;
	private int $addresserId;
	private int $receiverId;
	private string $type;
	private array $changeFields;
	private int $counterInvitations;
	private ?array $event = null;

	private ?Context $context = null;
	private Logger $logger;

	public function __construct(
		int $eventId,
		int $addresserId,
		int $receiverId,
		string $type,
		?array $changeFields = [],
		?int $counterInvitations = 0
	)
	{
		$this->eventId = $eventId;
		$this->addresserId = $addresserId;
		$this->receiverId = $receiverId;
		$this->type = $type;
		$this->changeFields = $changeFields ?? [];
		$this->counterInvitations = $counterInvitations ?? 0;

		$this->init();
	}

	public function getSenderInvitation(): ?SenderInvitation
	{
		return $this
			->getFactory()
			->getInvitation();
	}

	public function toArray(): array
	{
		return [
			'eventId' => $this->eventId,
			'addresserId' => $this->addresserId,
			'receiverId' => $this->receiverId,
			'type' => $this->type,
			'changeFields' => $this->changeFields,
			'counterInvitation' => $this->counterInvitations,
		];
	}

	private function getFactory(): SenderInvitationFactory
	{
		$this
			->setEventById()
			->setContextByEvent();

		return new SenderInvitationFactory($this->type, $this->event, $this->context, $this->counterInvitations);
	}

	private function setEventById(): static
	{
		if (is_null($this->event))
		{
			$this->event = $this->getEvent();
		}

		return $this;
	}

	private function getEvent(): ?array
	{
		try
		{
			return Helper::getEventById($this->eventId);
		}
		catch (SystemException $exception)
		{
			$this->logger->log($exception);
			return null;
		}
	}

	private function setContextByEvent(): static
	{
		$this->setEventById();

		try
		{
			$this->context = Context::createInstance(
				$this->getAddresser($this->event['MEETING'] ?? null),
				$this->getReceiver(),
				$this->changeFields
			);
		}
		catch (MemberNotFoundException $exception)
		{
			$this->logger->log($exception);
		}

		return $this;
	}

	/**
	 * @throws MemberNotFoundException
	 */
	private function getAddresser(?string $meetingInfo): ?MailAddresser
	{
		$addresser = $this->getInfoAboutUser($this->addresserId);
		if (is_null($addresser))
		{
			throw new MemberNotFoundException("Addresser {$this->addresserId} not found or not active");
		}

		return MailAddresser::createInstance(
			$addresser->getId(),
			$this->getSelectedAddresserEmail($meetingInfo) ?? $addresser->getEmail(),
			$addresser->getName(),
			$addresser->getLastName()
		);
	}

	/**
	 * @throws MemberNotFoundException
	 */
	private function getReceiver(): MailReceiver
	{
		$receiver = $this->getInfoAboutUser($this->receiverId);
		if (is_null($receiver))
		{
			throw new MemberNotFoundException("Receiver {$this->receiverId} not found or not active");
		}

		return MailReceiver::createInstance(
			$receiver->getId(),
			$receiver->getEmail(),
			$receiver->getName(),
			$receiver->getLastName()
		);
	}

	private function getInfoAboutUser(int $userId): ?EO_User
	{
		try
		{
			$query = UserTable::query();
			$query
				->setSelect(['ID', 'EMAIL', 'NAME', 'LAST_NAME', 'ACTIVE'])
				->where('ID', $userId)
				->where('ACTIVE', true);

			return $query->exec()->fetchObject();
		}
		catch (SystemException $exception)
		{
			$this->logger->log($exception);
			return null;
		}
	}

	private function getSelectedAddresserEmail(?string $serializedMeetingInfo = null): ?string
	{
		$meetingInfo = unserialize($serializedMeetingInfo, ['allowed_classes' => false]);

		return !empty($meetingInfo) && !empty($meetingInfo['MAIL_FROM'])
			? $meetingInfo['MAIL_FROM']
			: null;
	}

	private function init(): void
	{
		$this->logger = new Logger();
	}
}