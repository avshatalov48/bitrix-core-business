<?php

namespace Bitrix\Mail\Helper\User;

final class User
{
	private int $id;
	private string $name;
	private array $avatar;
	private string $pathToProfile;
	private ?string $position;

	public function __construct(array $userData)
	{
		$this->id = $userData['id'];
		$this->name = $userData['name'];
		$this->avatar = $userData['avatar'];
		$this->pathToProfile = $userData['pathToProfile'];
		$this->position = $userData['position'];
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAvatar(): array
	{
		return $this->avatar;
	}

	public function getPathToProfile(): string
	{
		return $this->pathToProfile;
	}

	public function getPosition(): string
	{
		return $this->position;
	}

	/**
	 * @return array{
	 *     id: int,
	 *     name: string,
	 *     avatar: array{
	 *         src: string,
	 *         width: int,
	 *         height: int,
	 *         size: int,
	 *     },
	 *     pathToProfile: string,
	 *     position: ?string,
	 * }
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'avatar' => $this->avatar,
			'pathToProfile' => $this->pathToProfile,
			'position' => $this->position,
		];
	}
}
