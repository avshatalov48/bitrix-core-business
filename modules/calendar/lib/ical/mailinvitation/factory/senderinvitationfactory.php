<?php

namespace Bitrix\Calendar\ICal\MailInvitation\Factory;

use Bitrix\Calendar\ICal\MailInvitation\Context;
use Bitrix\Calendar\ICal\MailInvitation\InvitationInfo;
use Bitrix\Calendar\ICal\MailInvitation\SenderCancelInvitation;
use Bitrix\Calendar\ICal\MailInvitation\SenderEditInvitation;
use Bitrix\Calendar\ICal\MailInvitation\SenderInvitation;
use Bitrix\Calendar\ICal\MailInvitation\SenderRequestInvitation;

class SenderInvitationFactory
{
	private const TYPE_REQUEST = InvitationInfo::TYPE_REQUEST;
	private const TYPE_CANCEL = InvitationInfo::TYPE_CANCEL;
	private const TYPE_EDIT = InvitationInfo::TYPE_EDIT;

	public function __construct(
		private string $type,
		private ?array $event,
		private ?Context $context,
		private int $counterInvitations
	)
	{
	}

	public function getInvitation(): ?SenderInvitation
	{
		if (is_null($this->event) || is_null($this->context))
		{
			return null;
		}

		/** @var SenderInvitation $class */
		$class = $this->getMap()[$this->type] ?? null;
		if (is_null($class))
		{
			return null;
		}

		return $class::createInstance($this->event, $this->context)
			->setCounterInvitations($this->counterInvitations);
	}

	private function getMap(): array
	{
		return [
			static::TYPE_REQUEST => SenderRequestInvitation::class,
			static::TYPE_EDIT => SenderEditInvitation::class,
			static::TYPE_CANCEL => SenderCancelInvitation::class,
		];
	}
}