<?php

namespace Bitrix\Rest\APAuth;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;

Loc::loadMessages(__FILE__);

/**
 * Class ApTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> AP string(50) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> TITLE string(255) optional
 * <li> COMMENT string(255) optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_LOGIN datetime optional
 * <li> LAST_IP string(255) optional
 * </ul>
 *
 * @package Bitrix\Rest
 **/
class PasswordTable extends Main\Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';

	const DEFAULT_LENGTH = 16;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_ap';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'PASSWORD' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array(static::INACTIVE, static::ACTIVE),
			),
			'TITLE' => array(
				'data_type' => 'string',
			),
			'COMMENT' => array(
				'data_type' => 'string',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
			),
			'DATE_LOGIN' => array(
				'data_type' => 'datetime',
			),
			'LAST_IP' => array(
				'data_type' => 'string',
			),
		);
	}

	public static function generatePassword($length = self::DEFAULT_LENGTH)
	{
		return Random::getString($length);
	}


	/**
	 * Generates AP for REST access.
	 *
	 * @param string $siteTitle Site title for AP description.
	 *
	 * @return bool|string password or false
	 * @throws \Exception
	 */
	public static function createPassword($userId, array $scopeList, $siteTitle)
	{
		$password = static::generatePassword();

		$res = static::add(array(
			'USER_ID' => $userId,
			'PASSWORD' => $password,
			'DATE_CREATE' => new Main\Type\DateTime(),
			'TITLE' => Loc::getMessage('REST_APP_SYSCOMMENT', array(
				'#TITLE#' => $siteTitle,
			)),
			'COMMENT' => Loc::getMessage('REST_APP_COMMENT'),
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
