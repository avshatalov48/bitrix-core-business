<?php

namespace Bitrix\Rest\Configuration\Core;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Rest\Configuration\Structure;

use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class ConfigurationStorageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CREATE_TIME datetime mandatory
 * <li> CONTEXT string(16) mandatory
 * <li> CODE string(16) mandatory
 * <li> DATA text mandatory
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Storage_Query query()
 * @method static EO_Storage_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Storage_Result getById($id)
 * @method static EO_Storage_Result getList(array $parameters = array())
 * @method static EO_Storage_Entity getEntity()
 * @method static \Bitrix\Rest\Configuration\EO_Storage createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\Configuration\EO_Storage_Collection createCollection()
 * @method static \Bitrix\Rest\Configuration\EO_Storage wakeUpObject($row)
 * @method static \Bitrix\Rest\Configuration\EO_Storage_Collection wakeUpCollection($rows)
 */
class StorageTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_configuration_storage';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			new DatetimeField(
				'CREATE_TIME',
				[
					'required' => true,
				]
			),
			new StringField(
				'CONTEXT',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateContext'],
				]
			),
			new StringField(
				'CODE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateCode'],
				]
			),
			new ArrayField(
				'DATA',
				[
					'required' => true,
				]
			),
		];
	}

	/**
	 * Returns validators for CONTEXT field.
	 *
	 * @return array
	 */
	public static function validateContext()
	{
		return [
			new LengthValidator(null, 128),
		];
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return [
			new LengthValidator(null, 32),
		];
	}

	public static function deleteByFilter($filter)
	{
		$res = static::getList(
			[
				'filter' => $filter
			]
		);
		while ($item = $res->fetch())
		{
			static::deleteFile($item);
			static::delete($item['ID']);
		}
	}

	public static function deleteFile($item)
	{
		if (
			isset($item['DATA']['ID'])
			&& (int) $item['DATA']['ID'] > 0
			&& (
				$item['CODE'] === Structure::CODE_CONFIGURATION_FILES_LIST
				|| mb_strpos($item['CODE'], Structure::CODE_CUSTOM_FILE) !== false
			)
		)
		{
			\CFile::Delete((int) $item['DATA']['ID']);
		}
	}
}