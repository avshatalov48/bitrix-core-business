<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\SerializeObject;
use Serializable;

class Attendee implements Serializable
{
	use SerializeObject;

	private bool $rsvp = true;

	/**
	 * @var string|null
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
	 * @var string|null
	 */
	private $participationStatus;
	/**
	 * @var string|null
	 */
	private $role;
	/**
	 * @var string|null
	 */
	private $cutype;
	/**
	 * @var string|null
	 */
	private $mailto;

	/**
	 * @param int $id
	 * @param string|null $email
	 * @param string|null $name
	 * @param string|null $lastName
	 * @param string|null $participationStatus
	 * @param string|null $role
	 * @param string|null $cutype
	 * @param string|null $mailto
	 * @return Attendee
	 */
	public static function createInstance(
		string $email = null,
		string $name = null,
		string $lastName = null,
		string $participationStatus = null,
		string $role = null,
		string $cutype = null,
		string $mailto = null,
		bool $rsvp = true
	): Attendee
	{
		return new self(
			$email,
			$name,
			$lastName,
			$participationStatus,
			$role,
			$cutype,
			$mailto,
			$rsvp
		);
	}

	/**
	 * Attendee constructor.
	 * @param int $id
	 * @param string|null $email
	 * @param string|null $name
	 * @param string|null $lastName
	 * @param string|null $participationStatus
	 * @param string|null $role
	 * @param string|null $cutype
	 * @param string|null $mailto
	 */
	public function __construct(
		string $email = null,
		string $name = null,
		string $lastName = null,
		string $participationStatus = null,
		string $role = null,
		string $cutype = null,
		string $mailto = null,
		bool $rsvp = true
	)
	{
		$this->email = $email;
		$this->name = $name;
		$this->lastName = $lastName;
		$this->participationStatus = $participationStatus;
		$this->role = $role;
		$this->cutype = $cutype;
		$this->mailto = $mailto;
		$this->rsvp = $rsvp;
	}

	/**
	 * @return string|null
	 */
	public function getFullName(): ?string
	{
		if ($this->name || $this->lastName)
		{
			return trim("{$this->name} {$this->lastName}");
		}

		return $this->email;
	}

	/**
	 * @return string|null
	 */
	public function getEmail(): ?string
	{
		return $this->email ?? $this->mailto;
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->participationStatus;
	}

	/**
	 * @return string|null
	 */
	public function getRole(): ?string
	{
		return $this->role;
	}

	/**
	 * @return string|null
	 */
	public function getCuType(): ?string
	{
		return $this->cutype;
	}

	/**
	 * @return string|null
	 */
	public function getMailTo(): ?string
	{
		return $this->mailto ?? $this->email;
	}

	/**
	 * @param string|null $status
	 * @return $this
	 */
	public function setStatus(?string $status): Attendee
	{
		$this->participationStatus = $status;

		return $this;
	}

	/**
	 * @param string|null $name
	 * @return $this
	 */
	public function setName(?string $name): Attendee
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @param string|null $lastName
	 * @return $this
	 */
	public function setLastName(?string $lastName): Attendee
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @param string|null $email
	 * @return $this
	 */
	public function setEmail(?string $email): Attendee
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @param string|null $role
	 * @return $this
	 */
	public function setRole(?string $role): Attendee
	{
		$this->role = $role;

		return $this;
	}

	/**
	 * @param string|null $cutype
	 * @return $this
	 */
	public function setCutype(?string $type): Attendee
	{
		$this->cutype = $type;

		return $this;
	}

	/**
	 * @param string|null $mailto
	 * @return $this
	 */
	public function setMailto(?string $mailto): Attendee
	{
		$this->mailto = $mailto;

		return $this;
	}

	/**
	 * @param bool $rsvp
	 * @return Attendee
	 */
	public function setRsvp(bool $rsvp): Attendee
	{
		$this->rsvp = $rsvp;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRsvp(): bool
	{
		return $this->rsvp;
	}
}
