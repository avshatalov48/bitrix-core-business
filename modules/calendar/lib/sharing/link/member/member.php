<?php

namespace Bitrix\Calendar\Sharing\Link\Member;

class Member
{
	private int $id = 0;
	private string $name = '';
	private string $lastName = '';
	private string $avatar = '';

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Member
	 */
	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return Member
	 */
	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastName(): string
	{
		return $this->lastName;
	}

	/**
	 * @param string $lastName
	 * @return Member
	 */
	public function setLastName(string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAvatar(): string
	{
		return $this->avatar;
	}

	/**
	 * @param string $avatar
	 * @return Member
	 */
	public function setAvatar(string $avatar): self
	{
		$this->avatar = $avatar;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'lastName' => $this->getLastName(),
			'avatar' => $this->getAvatar(),
		];
	}
}