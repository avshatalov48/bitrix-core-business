<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Main\Loader;

final class ChatService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function deleteUserFromChat(int $userId, int $chatId): bool
	{
		if (!$this->isAvailable())
		{
			return false;
		}

		return (new \CIMChat(0))->DeleteUser(
			chatId: $chatId,
			userId: $userId,
			checkPermission: false,
			additionalParams: ['SKIP_RIGHTS' => true],
		);
	}

	public function addUserToChat(int $userId, int $chatId): bool
	{
		if (!$this->isAvailable())
		{
			return false;
		}

		return (new \CIMChat(0))->AddUser(
			chatId: $chatId,
			userId: $userId,
			hideHistory: true,
		);
	}

	public function isAvailable(): bool
	{
		return Loader::includeModule('im');
	}

	private function __construct()
	{
	}
}
