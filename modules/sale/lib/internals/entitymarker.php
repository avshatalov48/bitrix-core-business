<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class EntityMarkerTable
 * @package Bitrix\Sale
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EntityMarker_Query query()
 * @method static EO_EntityMarker_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_EntityMarker_Result getById($id)
 * @method static EO_EntityMarker_Result getList(array $parameters = array())
 * @method static EO_EntityMarker_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_EntityMarker createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_EntityMarker_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_EntityMarker wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_EntityMarker_Collection wakeUpCollection($rows)
 */

class EntityMarkerTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_entity_marker';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'ID',
				array(
				 'autocomplete' => true,
				 'primary' => true,
				)
			),

			new Main\Entity\IntegerField('ORDER_ID'),

			new Main\Entity\StringField(
				'ENTITY_TYPE',
				array(
					'size' => 25,
				)
			),

			new Main\Entity\IntegerField('ENTITY_ID'),

			new Main\Entity\StringField(
				'TYPE',
				array(
					'size' => 10,
				)
			),

			new Main\Entity\StringField(
				'CODE',
				array(
					'size' => 255,
					'validation' => array(__CLASS__, 'validateComment')
				)
			),

			new Main\Entity\StringField(
				'MESSAGE',
				array(
					'size' => 255,
					'validation' => array(__CLASS__, 'validateMessage')
				)
			),

			new Main\Entity\StringField(
				'COMMENT',
				array(
					'size' => 500,
					'validation' => array(__CLASS__, 'validateComment')
				)
			),

			new Main\Entity\IntegerField('USER_ID'),

			new Main\Entity\DatetimeField(
				'DATE_CREATE'
			),

			new Main\Entity\DatetimeField(
				'DATE_UPDATE'
			),

			new Main\Entity\BooleanField(
				'SUCCESS',
				array(
					'size' => 1,
					'validation' => array(__CLASS__, 'validateSuccess')
				)
			),
		);
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for MESSAGE field.
	 *
	 * @return array
	 */
	public static function validateMessage()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	/**
	 * Returns validators for COMMENT field.
	 *
	 * @return array
	 */
	public static function validateComment()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for SUCCESS field.
	 *
	 * @return array
	 */
	public static function validateSuccess()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
}