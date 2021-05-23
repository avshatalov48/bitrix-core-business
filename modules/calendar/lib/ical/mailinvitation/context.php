<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


class Context
{
	private $addresser;
	private $receiver;
	private $changeFields;

	public static function createInstance(MailAddresser $addresser, MailReceiver $receiver): Context
	{
		return new self($addresser, $receiver);
	}

	public function __construct(MailAddresser $addresser, MailReceiver $receiver)
	{
		$this->addresser = $addresser;
		$this->receiver = $receiver;
	}

	public function setAddresser(MailAddresser $addresser): Context
	{
		$this->addresser = $addresser;

		return $this;
	}

	public function setReceiver(MailReceiver $receiver): Context
	{
		$this->receiver = $receiver;

		return $this;
	}

	public function setChangeFields(array $changeFields): Context
	{
		$this->changeFields = $changeFields;

		return $this;
	}

	public function getAddresser(): MailAddresser
	{
		return $this->addresser;
	}

	public function getReceiver(): MailReceiver
	{
		return $this->receiver;
	}

	public function getChangeFields(): array
	{
		return $this->changeFields;
	}
}