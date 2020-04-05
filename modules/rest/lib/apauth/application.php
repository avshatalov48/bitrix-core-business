<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Rest\APAuth;

use Bitrix\Main\Authentication\ApplicationPasswordTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * @deprecated
 *
 * use \Bitrix\Rest\APAuth\PasswordTable
 */
class Application extends \Bitrix\Main\Authentication\Application
{
	const ID = 'rest';

	protected $validUrls = array(
		"/rest/",
	);

	public static function onApplicationsBuildList()
	{
		return array(
			"ID" => static::ID,
			"NAME" => Loc::getMessage("REST_APP_TITLE"),
			"DESCRIPTION" => Loc::getMessage("REST_APP_DESC"),
			"SORT" => 1000,
			"CLASS" => __CLASS__,
			"VISIBLE" => false,
		);
	}

	/**
	 * Generates AP for REST access.
	 *
	 * @param string $siteTitle Site title for AP description.
	 *
	 * @return bool|string password or false
	 * @throws \Exception
	 */
	public static function generateAppPassword($siteTitle, array $scopeList)
	{
		global $USER;

		$password = ApplicationPasswordTable::generatePassword();

		$res = ApplicationPasswordTable::add(array(
			'USER_ID' => $USER->getID(),
			'APPLICATION_ID' => static::ID,
			'PASSWORD' => $password,
			'DATE_CREATE' => new DateTime(),
			'COMMENT' => Loc::getMessage('REST_APP_COMMENT'),
			'SYSCOMMENT' => Loc::getMessage('REST_APP_SYSCOMMENT', array(
				'#TITLE#' => $siteTitle,
			)),
		));

		if($res->isSuccess())
		{
			$scopeList = array_unique($scopeList);
			foreach($scopeList as $scope)
			{
				PermissionTable::add(array(
					'PASSWORD_ID' => $res->getId(),
					'PERM' => $scope,
				));
			}

			return $password;
		}

		return false;
	}
}
