<?php

namespace Bitrix\Socialnetwork\Integration\AI\User;

class Author
{
	private int $userId;
	private array $user;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
		$this->init();
	}

	public function getName(): string
	{
		return trim("{$this->user['NAME']} {$this->user['LAST_NAME']}");
	}

	public function getWorkPosition(): string
	{
		return (string)$this->user['WORK_POSITION'];
	}

	public function toMeta(): array
	{
		return [
			'author' => [
				'name' => $this->getName(),
				'work_position' => $this->getWorkPosition(),
			],
		];
	}

	private function init(): void
	{
		$this->user = \CUser::GetByID($this->userId)->Fetch();
	}
}