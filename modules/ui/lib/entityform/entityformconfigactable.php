<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Entity;
use Bitrix\Main\UserAccessTable;

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