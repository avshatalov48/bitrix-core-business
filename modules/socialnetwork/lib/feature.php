<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

/*
create table b_sonet_features
(
  ID int not null auto_increment,
  ENTITY_TYPE char(1) not null default 'G',
  ENTITY_ID int not null,
  FEATURE varchar(50) not null,
  FEATURE_NAME varchar(250) null,
  ACTIVE char(1) not null default 'Y',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  primary key (ID),
  unique IX_SONET_GROUP_FEATURES_1(ENTITY_TYPE, ENTITY_ID, FEATURE)
);

*/
/**
 * Class FeatureTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Feature_Query query()
 * @method static EO_Feature_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Feature_Result getById($id)
 * @method static EO_Feature_Result getList(array $parameters = [])
 * @method static EO_Feature_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_Feature createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_Feature_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_Feature wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_Feature_Collection wakeUpCollection($rows)
 */
class FeatureTable extends Entity\DataManager
{
	const FEATURE_ENTITY_TYPE_GROUP = 'G';
	const FEATURE_ENTITY_TYPE_USER = 'U';

	public static function getTableName()
	{
		return 'b_sonet_features';
	}

	public static function getMap()
	{
		$fieldsMap = [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			],
			'ENTITY_TYPE' => [
				'data_type' => 'string'
			],
			'ENTITY_ID' => [
				'data_type' => 'integer'
			],
			'FEATURE' => [
				'data_type' => 'string'
			],
			'FEATURE_NAME' => [
				'data_type' => 'string'
			],
			'ACTIVE' => [
				'data_type' => 'string'
			],
			'DATE_CREATE' => [
				'data_type' => 'datetime'
			],
			'DATE_UPDATE' => [
				'data_type' => 'datetime'
			]
		];

		return $fieldsMap;
	}

}
