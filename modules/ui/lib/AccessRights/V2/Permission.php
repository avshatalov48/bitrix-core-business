<?php

namespace Bitrix\UI\AccessRights\V2;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

abstract class Permission
{
	public const SETTINGS_ACCESS_RIGHTS_V2_KEY = 'ui.accessrights.v2';
	protected array $params;
	protected int $userId;

	protected function __construct(array $params = [], int $userId = null)
	{
		if($userId === null)
		{
			$userId = $this->getDefaultUserId();
		}

		$this->userId = $userId;
		$this->params = $params;
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

	public static function getInstance(string $moduleId, array $params = [], int $userId = null): ?Permission
	{
		$configuration = Configuration::getInstance($moduleId);

		$value = $configuration->get(static::SETTINGS_ACCESS_RIGHTS_V2_KEY);
		if (
			is_array($value)
			&& isset($value['access'])
			&& Loader::includeModule($moduleId)
			&& is_a($value['access'], self::class, true)
		)
		{
			return new $value['access']($params, $userId);
		}

		return null;
	}

	public function canUpdate(): bool
	{
		return false;
	}
}