<?php

namespace Bitrix\Im\V2\Controller\Settings;

use Bitrix\Im\V2\Controller\Filter\SettingsCheckAccess;
use Bitrix\Im\V2\Settings\UserConfiguration;

class General extends \Bitrix\Im\V2\Controller\BaseController
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
		$value = $value === 'N' ? false : ($value === 'Y' ? true : $value);
		$userConfiguration = new UserConfiguration($userId);
		$userConfiguration->updateGeneralSetting([
			'name' => $name,
			'value' => $value
		]);

		return true;
	}
}