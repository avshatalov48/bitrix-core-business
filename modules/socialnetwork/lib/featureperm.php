<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

/**
 * Class FeaturePermTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FeaturePerm_Query query()
 * @method static EO_FeaturePerm_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FeaturePerm_Result getById($id)
 * @method static EO_FeaturePerm_Result getList(array $parameters = [])
 * @method static EO_FeaturePerm_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_FeaturePerm createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_FeaturePerm_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_FeaturePerm wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_FeaturePerm_Collection wakeUpCollection($rows)
 */
class FeaturePermTable extends Entity\DataManager
{
	/** @see SONET_ROLES_OWNER */
	public const PERM_OWNER = 'A';

	/** @see SONET_ROLES_MODERATOR */
	public const PERM_MODERATOR = 'E';

	/** @see SONET_ROLES_USER */
	public const PERM_USER = 'K';

	/** @see SONET_ROLES_AUTHORIZED */
	public const PERM_AUTHORIZED = 'L';

	/** @see SONET_ROLES_ALL */
	public const PERM_ALL = 'N';

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
