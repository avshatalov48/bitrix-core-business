<?php
namespace Bitrix\Landing\Restriction;

use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Loader;

class Form
{
	/**
	 * Checks restriction for creating minisites in forms
	 * @return bool
	 */
	public static function isMinisitesAllowed(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return Feature::isFeatureEnabled('landing_allow_minisites');
		}

		return true;
	}
}