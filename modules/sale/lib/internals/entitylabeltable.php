<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class EntityLabelTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENTITY_ID int mandatory
 * <li> ENTITY_TYPE string(255) mandatory
 * <li> LABEL_NAME string(255) mandatory
 * <li> LABEL_VALUE text optional
 * </ul>
 *
 * @package Bitrix\Sale
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EntityLabel_Query query()
 * @method static EO_EntityLabel_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EntityLabel_Result getById($id)
 * @method static EO_EntityLabel_Result getList(array $parameters = [])
 * @method static EO_EntityLabel_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_EntityLabel createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_EntityLabel_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_EntityLabel wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_EntityLabel_Collection wakeUpCollection($rows)
 */

class EntityLabelTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_entity_label';
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
			new IntegerField(
				'ENTITY_ID',
				[
					'required' => true,
				]
			),
			new StringField(
				'ENTITY_TYPE',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new StringField(
				'LABEL_NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new TextField(
				'LABEL_VALUE',
			),
		];
	}
}
