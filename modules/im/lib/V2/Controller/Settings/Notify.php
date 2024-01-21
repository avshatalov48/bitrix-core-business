<?php

namespace Bitrix\Im\V2\Controller\Settings;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\SettingsCheckAccess;
use Bitrix\Im\V2\Settings\SettingsError;
use Bitrix\Im\V2\Settings\UserConfiguration;
use Bitrix\Main\Engine\CurrentUser;

class Notify extends BaseController
{
	public const ALLOWED_SCHEME = ['simple', 'expert'];

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

	/**
	 * @restMethod im.v2.Settings.General.switchScheme
	 */
	public function switchSchemeAction(string $scheme, CurrentUser $currentUser, ?int $userId = null): ?array
	{
		if (!in_array($scheme, self::ALLOWED_SCHEME, true))
		{
			$this->addError(new SettingsError(SettingsError::WRONG_SCHEME));

			return null;
		}

		$userId ??= (int)$currentUser->getId();
		$userConfiguration = new UserConfiguration($userId);
		$userConfiguration->updateGeneralSetting([
			'name' => \Bitrix\Im\V2\Settings\Entity\General::SCHEME,
			'value' => $scheme,
		]);

		return $userConfiguration->getNotifySettings();
	}
}