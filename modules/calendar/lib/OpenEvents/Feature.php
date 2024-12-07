<?php

namespace Bitrix\Calendar\OpenEvents;

use Bitrix\Main\Config;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Core\Common;

final class Feature
{
	private const FEATURE_OPTION = 'feature_calendar_open_events_enabled';

	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function isAvailable(?int $userId = null): bool
	{
		if ($userId === null)
		{
			$userId = (int)CurrentUser::get()->getId();
		}

		return $this->isEnabled($userId) && Loader::includeModule('im');
	}

	public function enableForUser(int $userId): void
	{
		\CUserOptions::SetOption(
			category: Common::CALENDAR_MODULE_ID,
			name: $this->getOptionName(),
			value: 'Y',
			user_id: $userId,
		);
	}

	public function disableForUser(int $userId): void
	{
		\CUserOptions::DeleteOption(
			category: Common::CALENDAR_MODULE_ID,
			name: $this->getOptionName(),
			user_id: $userId,
		);
	}

	public function enableForEveryone(): void
	{
		Config\Option::set(
			moduleId: Common::CALENDAR_MODULE_ID,
			name: $this->getOptionName(),
			value: 'Y',
		);
	}

	public function disableForEveryone(): void
	{
		Config\Option::set(
			moduleId: Common::CALENDAR_MODULE_ID,
			name: $this->getOptionName(),
			value: 'N',
		);
	}

	private function isEnabled(int $userId): bool
	{
		$enabledForEveryone = $this->isEnabledForEveryone();

		if ($enabledForEveryone)
		{
			return true;
		}

		return $this->isEnabledForUser($userId);
	}

	private function isEnabledForEveryone(): bool
	{
		return Config\Option::get(
			moduleId: Common::CALENDAR_MODULE_ID,
			name: $this->getOptionName(),
			default: 'N',
		) === 'Y';
	}

	private function isEnabledForUser(int $userId): bool
	{
		return \CUserOptions::GetOption(
			category: Common::CALENDAR_MODULE_ID,
			name: $this->getOptionName(),
			default_value: 'N',
			user_id: $userId,
		) === 'Y';
	}

	private function getOptionName(): string
	{
		return self::FEATURE_OPTION;
	}

	private function __construct()
	{
	}
}
