<?php

namespace Bitrix\Im\V2\Controller\Settings;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\SettingsCheckAccess;
use Bitrix\Im\V2\Settings\UserConfiguration;

class Notify extends BaseController
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new SettingsCheckAccess(),
			]
		);
	}

	public function listAction(int $userId)
	{
		$userConfiguration = new UserConfiguration($userId);

		return $userConfiguration->getNotifySettings();
	}

	public function updateAction(int $userId, string $moduleId, string $name, string $type, string $value)
	{
		$userConfiguration = new UserConfiguration($userId);
		$userConfiguration->updateNotifySetting([
			'name' => $name,
			'value' => $this->convertCharToBool($value),
			'moduleId' => $moduleId,
			'type' => $type
		]);

		return true;
	}

}