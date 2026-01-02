<?php

namespace Bitrix\Iblock;

use Bitrix\Main\ORM\Data\DataManager;

/**
 * Class InheritedPropertyTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_InheritedProperty_Query query()
 * @method static EO_InheritedProperty_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_InheritedProperty_Result getById($id)
 * @method static EO_InheritedProperty_Result getList(array $parameters = [])
 * @method static EO_InheritedProperty_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_InheritedProperty createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_InheritedProperty_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_InheritedProperty wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_InheritedProperty_Collection wakeUpCollection($rows)
 */
class InheritedPropertyTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_iblock_iproperty';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
			),
			'TEMPLATE' => array(
				'data_type' => 'string',
			)
		);
	}
}
