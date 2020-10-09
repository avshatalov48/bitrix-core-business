<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\UserField\UserFieldAccess;
use Bitrix\Main\UserFieldTable;

class ScopeAccess
{
	public const SETTINGS_ENTITYFORM_SCOPE_KEY = 'entityFormScope';
	public const SETTINGS_ACCESS_CLASS_KEY = 'access';

	protected $userId;
	protected $moduleId;

	public function __construct(string $moduleId = null, int $userId = null)
	{
		if($userId === null)
		{
			$userId = $this->getDefaultUserId();
		}

		$this->userId = $userId;
		$this->moduleId = $moduleId;
	}

	protected function getDefaultUserId(): int
	{
		global $USER;
		if($USER instanceof \CUser)
		{
			return (int) CurrentUser::get()->getId();
		}

		return 0;
	}

	public static function getInstance(string $moduleId, int $userId = null): ScopeAccess
	{
		$configuration = Configuration::getInstance($moduleId);

		$value = $configuration->get(static::SETTINGS_ENTITYFORM_SCOPE_KEY);
		if (
			is_array($value)
			&& isset($value[static::SETTINGS_ACCESS_CLASS_KEY])
			&& Loader::includeModule($moduleId)
			&& is_a($value[static::SETTINGS_ACCESS_CLASS_KEY], self::class, true)
		)
		{
			return new $value[static::SETTINGS_ACCESS_CLASS_KEY]($moduleId, $userId);
		}

		throw new ObjectNotFoundException('No settings for ScopeAccess');
	}

	public function canRead(int $scopeId): bool
	{
		return true;
	}

	public function canAdd(): bool
	{
		return true;
	}

	public function canUpdate(int $scopeId): bool
	{
		return $this->canAdd();
	}

	public function canDelete($scopeIds): bool
	{
		return $this->canUpdate($scopeIds);
	}

	public function isAdmin(): bool
	{
		return true;
	}
}