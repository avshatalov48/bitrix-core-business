<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\SerializeObject;
use Serializable;

class MailAddresser implements Serializable
{
	use SerializeObject;
	/**
	 * @var string|null
	 */
	private $email;
	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var string|null
	 */
	private $name;
	/**
	 * @var string|null
	 */
	private $lastName;
	/**
	 * @var string|null
	 */
	private $mailto;

	/**
	 * @param int $id
	 * @param string|null $email
	 * @param string|null $name
	 * @param string|null $lastName
	 * @param string|null $mailto
	 * @return MailAddresser
	 */
	public static function createInstance(
		int $id,
		string $email = null,
		string $name = null,
		string $lastName = null,
		string $mailto = null
	): MailAddresser
	{
		return new self($id, $email, $name, $lastName, $mailto);
	}

	/**
	 * MailAddresser constructor.
	 * @param int $id
	 * @param string|null $email
	 * @param string|null $name
	 * @param string|null $lastName
	 * @param string|null $mailto
	 */
	public function __construct(
		int $id,
		string $email = null,
		string $name = null,
		string $lastName = null,
		string $mailto = null
	)
	{
		$this->id = $id;
		$this->email = $email;
		$this->name = $name;
		$this->lastName = $lastName;
		$this->mailto = $mailto;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email ?? $this->mailto;
	}

	/**
	 * @param string $email
	 * @return $this
	 */
	public function setEmail(string $email): MailAddresser
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @param string $mailto
	 * @return $this
	 */
	public function setMailto(string $mailto): MailAddresser
	{
		$this->mailto = $mailto;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getMailto(): ?string
	{
		return $this->mailto;
	}

	/**
	 * @return string
	 */
	public function getFullName(): string
	{
		if ($this->name || $this->lastName)
		{
			return $this->name . ' ' . $this->lastName;
		}

		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getFullNameWithEmail(): string
	{
		if (!empty($this->email))
		{
			return "{$this->getFullName()} ({$this->email})";
		}

		return $this->getFullName();
	}
}