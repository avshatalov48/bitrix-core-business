<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\SerializeObject;

class MailUser
{
	use SerializeObject;
	protected int $id;
	protected string $email;
	protected ?string $lastName;
	protected ?string $name;

	public static function createInstance(
		int $id,
		string $email,
		string $name = null,
		string $lastName = null
	): static
	{
		return new static($id, $email, $name, $lastName);
	}
	public function __construct(
		int $id,
		string $email,
		string $name = null,
		string $lastName = null
	)
	{
		$this->id = $id;
		$this->email = $email;
		$this->name = $name;
		$this->lastName = $lastName;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	public function setLastName(?string $lastName): void
	{
		$this->lastName = $lastName;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): void
	{
		$this->name = $name;
	}
}