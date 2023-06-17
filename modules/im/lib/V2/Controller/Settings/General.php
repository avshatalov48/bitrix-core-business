<?php

namespace Bitrix\Im\V2\Controller\Settings;

use Bitrix\Im\Configuration\Configuration;
use Bitrix\Im\V2\Settings\UserConfiguration;

class General extends \Bitrix\Im\V2\Controller\BaseController
{

	/**
	 * @restMethod im.v2.Settings.General.list
	 */
	public function listAction(int $userId): array
	{
		$userConfiguration = new UserConfiguration($userId);

		return $userConfiguration->getGeneralSettings();
	}

	/**
	 * @restMethod im.v2.Settings.General.update
	 */
	public function updateAction(int $userId, string $name, string $value): bool
	{
		$value = $value === '0' ? false : ($value === '1' ? true : $value);
		$userConfiguration = new UserConfiguration($userId);
		$userConfiguration->updateGeneralSetting([
			'name' => $name,
			'value' => $value
		]);

		return true;
	}
}