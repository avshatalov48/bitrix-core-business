<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Role as RoleCore;
use \Bitrix\Landing\PublicActionResult;

Loc::loadMessages(__FILE__);

class Role
{
	/**
	 * Check feature enabled and if current user is admin.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function init()
	{
		static $internal = true;

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if (!Rights::isAdmin())
		{
			$error->addError(
				'IS_NOT_ADMIN',
				Loc::getMessage('LANDING_IS_NOT_ADMIN_ERROR')
			);
			$result->setError($error);
		}
		else if (!Manager::checkFeature(Manager::FEATURE_PERMISSIONS_AVAILABLE))
		{
			$error->addError(
				'FEATURE_NOT_AVAIL',
				\Bitrix\Landing\Restriction\Manager::getSystemErrorMessage(
					'limit_sites_access_permissions'
				)
			);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Gets available roles.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList()
	{
		$result = new PublicActionResult();

		$roles = [];
		foreach (RoleCore::fetchAll() as $item)
		{
			$roles[] = [
				'ID' => $item['ID'],
				'TITLE' => $item['TITLE'],
				'XML_ID' => $item['XML_ID']
			];
		}

		$result->setResult($roles);

		return $result;
	}

	/**
	 * Gets rights for each site in this role.
	 * @param int $id Role id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getRights($id)
	{
		$id = (int)$id;
		$result = new PublicActionResult();
		$result->setResult(
			RoleCore::getRights($id)
		);
		return $result;
	}

	/**
	 * Set rights for role.
	 * @param int $id Role id.
	 * @param array $rights Rights array ([[site_id] => [right1, right2]].
	 * @param array $additional Addition rights array ([Rights::ADDITIONAL_RIGHTS]].
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function setRights($id, array $rights, $additional = null)
	{
		static $mixedParams = ['additional'];

		$id = (int)$id;
		$result = new PublicActionResult();
		$result->setResult(true);
		RoleCore::setRights(
			$id,
			$rights,
			($additional !== null) ? $additional : null
		);

		return $result;
	}

	/**
	 * Set new access codes for role and refresh all rights.
	 * @param int $id Role id.
	 * @param array $codes Codes array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function setAccessCodes($id, array $codes = array())
	{
		$result = new PublicActionResult();
		$result->setResult(true);
		RoleCore::setAccessCodes((int)$id, $codes);
		return $result;
	}

	/**
	 * Return true if role model is switch on.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function isEnabled()
	{
		$result = new PublicActionResult();
		$result->setResult(
			!Rights::isExtendedMode()
		);
		return $result;
	}

	/**
	 * Switch on/off role model.
	 * @param bool $mode Mode: on/off.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function enable($mode)
	{
		$result = new PublicActionResult();
		$extended = Rights::isExtendedMode();
		if (
			$mode && $extended ||
			!$mode && !$extended
		)
		{
			Rights::switchMode();
		}
		$result->setResult(true);
		return $result;
	}
}