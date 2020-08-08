<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

class FeaturePermTable extends Entity\DataManager
{
	const PERM_OWNER = SONET_ROLES_OWNER;
	const PERM_MODERATOR = SONET_ROLES_MODERATOR;
	const PERM_USER = SONET_ROLES_USER;
	const PERM_AUTHORIZED = SONET_ROLES_AUTHORIZED;
	const PERM_ALL = SONET_ROLES_ALL;

	public static function getTableName()
	{
		return 'b_sonet_features2perms';
	}

	public static function getMap()
	{
		$fieldsMap = [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'FEATURE_ID' => [
				'data_type' => 'integer'
			],
			'FEATURE' => array(
				'data_type' => '\Bitrix\Socialnetwork\Feature',
				'reference' => [ '=this.FEATURE_ID' => 'ref.ID' ]
			),
			'OPERATION_ID' => [
				'data_type' => 'string'
			],
			'ROLE' => [
				'data_type' => 'string'
			],
		];

		return $fieldsMap;
	}

}
