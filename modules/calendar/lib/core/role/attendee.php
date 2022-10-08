<?php

namespace Bitrix\Calendar\Core\Role;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\BaseProperty;

class Attendee extends BaseProperty implements RoleEntityInterface
{
	public const TYPE = 'attendee';
	/**
	 * @var bool
	 */
	protected bool $reInvite = false;
	/**
	 * @var int|null
	 */
	protected ?int $id = null;
	protected ?string $lastName = null;
	protected ?string $name = null;
	protected RoleEntityInterface $roleEntity;

	public function __construct(RoleEntityInterface $roleEntity)
	{
		$this->roleEntity = $roleEntity;
	}

	public static function createInstance(RoleEntityInterface $roleEntity): RoleEntityInterface
	{
			$attendee = new static($roleEntity);
			$attendee->setReInvite(false);

			return $attendee;
	}

	/**
	 * @param string $email
	 * @return $this
	 */
	public function setEmail(string $email): Attendee
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}

	/**
	 * @param bool $reInvite
	 * @return $this
	 */
	public function setReInvite(bool $reInvite): Attendee
	{
		$this->reInvite = $reInvite;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isReInvite(): bool
	{
		return $this->reInvite;
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->getFullName();
	}

	public function getFullName(): string
	{
		return $this->name . ' ' . $this->lastName;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getType(): string
	{
		return self::TYPE;
	}

	public function getFields(): array
	{
		return [
			'reInvite',
			'id',
			'name',
			'lastName',
			'roleEntity',
		];
	}
}
