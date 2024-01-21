<?php

namespace Bitrix\Mail\Helper\OAuth;
use Bitrix\Main\Web\Json;

class UserData
{
	protected string $email;
	protected string $firstName;
	protected string $lastName;
	protected string $fullName;
	protected string $imageUrl;
	protected bool $emailIsIntended;

	protected string $userPrincipalName = '';

	public function __construct(string $email = '', string $firstName = '', string $lastName = '', string $fullName = '', string $imageUrl = '', $emailIsIntended = false)
	{
		$this->email = $email;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->fullName = $fullName;
		$this->imageUrl = $imageUrl;
		$this->emailIsIntended = $emailIsIntended;
	}

	public function getJson()
	{
		return Json::encode([
			'email' => $this->getEmail(),
			'first_name' => $this->getFirstName(),
			'last_name' => $this->getLastName(),
			'full_name' => $this->getFullName(),
			'image' => $this->getImageUrl(),
			'emailIsIntended' => $this->getEmailIsIntended(),
			'userPrincipalName' => $this->getUserPrincipalName(),
		]);
	}

	public function setEmailIsIntended(bool $emailIsIntended): void
	{
		$this->emailIsIntended = $emailIsIntended;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getFirstName(): string
	{
		return $this->firstName;
	}

	public function getLastName(): string
	{
		return $this->lastName;
	}

	public function getFullName(): string
	{
		return $this->fullName;
	}

	public function getImageUrl(): string
	{
		return $this->imageUrl;
	}

	public function getEmailIsIntended(): bool
	{
		return $this->emailIsIntended;
	}

	public function getUserPrincipalName(): string
	{
		return $this->userPrincipalName;
	}

	public function setUserPrincipalName(string $value): self
	{
		$this->userPrincipalName = $value;

		return $this;
	}
}
