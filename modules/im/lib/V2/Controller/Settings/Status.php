<?php

namespace Bitrix\Im\V2\Controller\Settings;

use Bitrix\Im\V2\Controller\Filter\SettingsCheckAccess;
use Bitrix\Im\V2\Settings\UserConfiguration;

class Status extends \Bitrix\Im\V2\Controller\BaseController
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
	 * @restMethod im.v2.Settings.Status.update
	 */
	public function updateAction(int $userId, string $status): bool
	{
		return (new UserConfiguration($userId))->updateStatus($status);
	}
}