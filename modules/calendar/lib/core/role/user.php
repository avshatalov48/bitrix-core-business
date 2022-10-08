<?php

namespace Bitrix\Calendar\Core\Role;

use Bitrix\Calendar\Core\Base\BaseException;

class User extends BaseRole
{
	public const TYPE = 'user';

	/**
	 * @var array
	 */
	public static array $users = [];
	/**
	 * @var string
	 */
	protected string $name;
	/**
	 * @var int|null
	 */
	protected ?int $id = null;
	/**
	 * @var string|null
	 */
	protected ?string $lastName = null;
	/**
	 * @var string|null
	 */
	protected ?string $languageId = null;
	/** @var string|null */
	protected ?string $email = null;

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return [
			'name' => $this->name,
			'lastName' => $this->lastName,
		];
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string|null $lastName
	 * @return $this
	 */
	public function setLastName(string $lastName = null): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	/**
	 * @return string
	 */
	public function getFullName(): string
	{
		return $this->getName() . ' ' . $this->getLastName();
	}

	/**
	 * @return string|null
	 */
	public function getLanguageId(): ?string
	{
		return $this->languageId;
	}

	/**
	 * @param string $languageId
	 * @return User
	 */
	public function setLanguageId(string $languageId): User
	{
		$this->languageId = $languageId;

		return $this;
	}

	/**
	 * @param string|null $email
	 * @return User
	 */
	public function setEmail(?string $email): User
	{
		$this->email = $email;

		return $this;
	}
}
