<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Syspage as SyspageCore;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Landing\Rights;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Syspage
{
	/**
	 * Set new system page for site.
	 * @param int $id Site id.
	 * @param string $type System page type.
	 * @param int $lid Landing id (if not set, ref was deleted).
	 * @return void
	 */
	public static function set($id, $type, $lid = false)
	{
		if (
			Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['sett']) &&
			(!$lid || Rights::hasAccessForLanding($lid, Rights::ACCESS_TYPES['sett']))
		)
		{
			SyspageCore::set($id, $type, $lid);
		}
	}

	/**
	 * Get pages for site.
	 * @param integer $id Site id.
	 * @param bool $active Only active items.
	 * @return PublicActionResult
	 */
	public static function get($id, $active = false)
	{
		$result = new PublicActionResult();
		if (Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['read']))
		{
			$result->setResult(
				SyspageCore::get($id, Utils::isTrue($active))
			);
		}
		return $result;
	}

	/**
	 * Delete all sys pages by site id.
	 * @param integer $id Site id.
	 * @return void
	 */
	public static function deleteForSite($id)
	{
		if (Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['sett']))
		{
			SyspageCore::deleteForSite($id);
		}
	}

	/**
	 * Delete all sys pages by id.
	 * @param integer $id Landing id.
	 * @return void
	 */
	public static function deleteForLanding($id)
	{
		if (Rights::hasAccessForLanding($id, Rights::ACCESS_TYPES['sett']))
		{
			SyspageCore::deleteForLanding($id);
		}
	}

	/**
	 * Get url of special page of site.
	 * @param int $siteId Site id.
	 * @param string $type Type of special page.
	 * @param array $additional Additional params for uri.
	 * @return PublicActionResult
	 */
	public static function getSpecialPage($siteId, $type, array $additional = [])
	{
		$result = new PublicActionResult();
		if (Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['read']))
		{
			$result->setResult(
				SyspageCore::getSpecialPage($siteId, $type, $additional)
			);
		}
		return $result;
	}
}
