<?php

namespace Bitrix\Im\V2\Controller\Settings;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Settings\UserConfiguration;

class Notify extends BaseController
{
	public function listAction(int $userId)
	{
		$userConfiguration = new UserConfiguration($userId);

		return $userConfiguration->getNotifySettings();
	}

	public function updateAction(int $userId, string $moduleId, string $name, string $type, bool $value)
	{
		$userConfiguration = new UserConfiguration($userId);
		$userConfiguration->updateNotifySetting([
			'name' => $name,
			'value' => $value,
			'moduleId' => $moduleId,
			'type' => $type
		]);

		return true;
	}

}