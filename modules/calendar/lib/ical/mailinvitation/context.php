<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

class Context
{
	private MailAddresser $addresser;
	private MailReceiver $receiver;
	private ?array $changeFields;

	public function __construct(MailAddresser $addresser, MailReceiver $receiver, array $changeFields = null)
	{
		$this->addresser = $addresser;
		$this->receiver = $receiver;
		$this->changeFields = $changeFields;
	}

	public static function createInstance(
		MailAddresser $addresser,
		MailReceiver $receiver,
		array $changeFields = null
	): Context
	{
		return new self($addresser, $receiver, $changeFields);
	}

	public function getAddresser(): MailAddresser
	{
		return $this->addresser;
	}

	public function setAddresser(MailAddresser $addresser): void
	{
		$this->addresser = $addresser;
	}


	public function getReceiver(): MailReceiver
	{
		return $this->receiver;
	}


	public function setReceiver(MailReceiver $receiver): void
	{
		$this->receiver = $receiver;
	}

	public function getChangeFields(): ?array
	{
		return $this->changeFields;
	}

	public function setChangeFields(?array $changeFields): void
	{
		$this->changeFields = $changeFields;
	}
}