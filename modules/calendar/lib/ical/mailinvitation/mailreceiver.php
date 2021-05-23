<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\SerializeObject;
use Serializable;

/**
 * Class MailReceiver
 * @package Bitrix\Calendar\ICal\MailInvitation
 */
class MailReceiver implements Serializable
{
	use SerializeObject;
	/**
	 * @var string
	 */
	private $email;
	/**
	 * @var string|null
	 */
	private $name;
	/**
	 * @var string|null
	 */
	private $lastName;
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @param int $id
	 * @param string $email
	 * @param string|null $name
	 * @param string|null $lastName
	 * @return MailReceiver
	 */
	public static function createInstance(
		int $id,
		string $email,
		string $name = null,
		string $lastName = null
	): MailReceiver
	{
		return new self($id, $email, $name, $lastName);
	}

	/**
	 * MailReceiver constructor.
	 * @param int $id
	 * @param string $email
	 * @param string|null $name
	 * @param string|null $lastName
	 */
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

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * @return string|null
	 */
	public function getFullName(): ?string
	{
		return !empty($this->name) || !empty($this->lastName)
			? trim("{$this->name} {$this->lastName}")
			: null;
	}

	/**
	 * @param string|null $name
	 * @return $this
	 */
	public function setName(string $name = null): MailReceiver
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @param string|null $lastName
	 * @return MailReceiver
	 */
	public function setLastName(string $lastName = null): MailReceiver
	{
		$this->lastName = $lastName;

		return $this;
	}
}