<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Entity;

/**
 * Class EntityFormConfigTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EntityFormConfig_Query query()
 * @method static EO_EntityFormConfig_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_EntityFormConfig_Result getById($id)
 * @method static EO_EntityFormConfig_Result getList(array $parameters = array())
 * @method static EO_EntityFormConfig_Entity getEntity()
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig createObject($setDefaultValues = true)
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection createCollection()
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig wakeUpObject($row)
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection wakeUpCollection($rows)
 */
class EntityFormConfigTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_ui_entity_editor_config';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Entity\StringField('CATEGORY', [
				'required' => true,
				'size' => 20
			]),
			new Entity\StringField('ENTITY_TYPE_ID', [
				'required' => true,
				'size' => 60
			]),
			new Entity\StringField('NAME', [
				'required' => true,
				'size' => 100
			]),
			new Entity\TextField('CONFIG', [
				'serialized' => true,
				'required' => true
			]),
			new Entity\BooleanField('COMMON', [
				'values' => ['N', 'Y'],
				'required' => true,
				'default_value' => 'N'
			])
		];
	}
}