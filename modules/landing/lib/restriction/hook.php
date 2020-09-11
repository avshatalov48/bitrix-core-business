<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;

class Hook
{
	/**
	 * Codes under limits.
	 */
	const MAP = [
		'gtm' => 'limit_sites_google_analytics',
		'gacounter' => 'limit_sites_google_analytics',
		'yacounter' => 'limit_sites_google_analytics',
		'copyright' => 'limit_sites_powered_by',
		'headblock' => 'limit_sites_html_js'
	];

	/**
	 * Returns restriction code by hook code.
	 * @param string $hookCode Hook code.
	 * @return string|null
	 */
	public static function getRestrictionCodeByHookCode(string $hookCode): ?string
	{
		$hookCode = strtolower($hookCode);
		return isset(self::MAP[$hookCode]) ? self::MAP[$hookCode] : null;
	}

	/**
	 * Checks hook restriction by hook code.
	 * @param string $hookCode Hook code.
	 * @return bool
	 */
	public static function isHookAllowed(string $hookCode): bool
	{
		$hookCode = strtolower($hookCode);
		if (isset(self::MAP[$hookCode]))
		{
			return self::isAllowed(self::MAP[$hookCode]);
		}
		return true;
	}

	/**
	 * Checks hook restriction existing by code.
	 * @param string $code Restriction code.
	 * @return bool
	 */
	public static function isAllowed(string $code): bool
	{
		static $mapFlip = [];

		if (!$mapFlip)
		{
			$mapFlip = array_flip(self::MAP);
		}

		if (
			isset($mapFlip[$code]) &&
		    \Bitrix\Main\Loader::includeModule('bitrix24')
		)
		{
			return Feature::isFeatureEnabled('landing_hook_' . $mapFlip[$code]);
		}

		return true;
	}
}