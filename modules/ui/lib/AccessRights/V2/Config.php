<?php

namespace Bitrix\UI\AccessRights\V2;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Web\Json;

final class Config
{
	private const USER_OPTIONS_CATEGORY = 'ui.accessrights.v2';

	private function __construct(
		private readonly string $context,
		private ?array $config,
	)
	{
	}

	private function __clone(): void
	{
	}

	/**
	 * Gets instance of config by predefined context key.
	 *
	 * @param string $context Predefined context key
	 * @param int|null $userId User for which load context. If null, the current user will be used.
	 *
	 * @return self
	 */
	public static function getInstanceByContext(string $context, ?int $userId = null): self
	{
		$locator = ServiceLocator::getInstance();

		$serviceId = self::makeServiceId($context, $userId);

		if (!$locator->has($serviceId))
		{
			$instance = new self($context, self::loadConfig($context, $userId));
			$locator->addInstance($serviceId, $instance);
		}

		return $locator->get($serviceId);
	}

	private static function makeServiceId(string $context, ?int $userId): string
	{
		$userId ??= (int)\Bitrix\Main\Engine\CurrentUser::get()->getId();

		return 'ui.accessrights.v2.config.' . $context . '.' . $userId;
	}

	public function updateConfig(array $newConfig): bool
	{
		if (!$this->saveConfig($newConfig))
		{
			return false;
		}

		$this->config = $newConfig;

		return true;
	}

	private static function loadConfig(string $context, ?int $userId): ?array
	{
		$config = \CUserOptions::GetOption(
			self::USER_OPTIONS_CATEGORY,
			$context,
			null,
			$userId === null ? false : $userId,
		);

		if (!is_array($config))
		{
			return null;
		}

		return $config;
	}

	private function saveConfig(array $newConfig): bool
	{
		return \CUserOptions::SetOption(
			self::USER_OPTIONS_CATEGORY,
			$this->context,
			$newConfig,
		);
	}

	/**
	 * Returns true, if there is no saved config for this context and user, a.k.a. 'default'
	 */
	public function isNull(): bool
	{
		return $this->config === null;
	}

	public function getContext(): string
	{
		return $this->context;
	}

	public function getUserGroupsSortConfig(): ?array
	{
		return $this->config;
	}

	public function getVisibleUserGroupsIds(): array
	{
		if ($this->isNull())
		{
			return [];
		}

		return array_keys($this->config);
	}
}
