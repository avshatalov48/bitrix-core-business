<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Entity;
use Bitrix\Main\UserAccessTable;

/**
 * Class EntityFormConfigAcTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EntityFormConfigAc_Query query()
 * @method static EO_EntityFormConfigAc_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_EntityFormConfigAc_Result getById($id)
 * @method static EO_EntityFormConfigAc_Result getList(array $parameters = array())
 * @method static EO_EntityFormConfigAc_Entity getEntity()
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc createObject($setDefaultValues = true)
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection createCollection()
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc wakeUpObject($row)
 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection wakeUpCollection($rows)
 */
class EntityFormConfigAcTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_ui_entity_editor_config_ac';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Entity\StringField('ACCESS_CODE', [
				'required' => true,
				'size' => 10
			]),
			new Entity\ReferenceField(
				'USER_ACCESS',
				UserAccessTable::class,
				array('=this.ACCESS_CODE' => 'ref.ACCESS_CODE')
			),
			new Entity\IntegerField('CONFIG_ID', [
				'required' => true,
				'size' => 10
			]),
			new Entity\ReferenceField(
				'CONFIG',
				EntityFormConfigTable::class,
				array('=this.CONFIG_ID' => 'ref.ID')
			),
		];
	}
}