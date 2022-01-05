<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;
use \Bitrix\Landing\Manager;

class Rights
{
	/**
	 * Checks restriction.
	 * @return bool
	 */
	public static function isAllowed(): bool
	{
		if (\Bitrix\Landing\Site\Type::getCurrentScopeId() === 'GROUP')
		{
			return true;
		}

		if (Manager::getOption('permissions_available', 'N') == 'Y')
		{
			return true;
		}

		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return Feature::isFeatureEnabled('landing_permissions_available');
		}

		return true;
	}
}