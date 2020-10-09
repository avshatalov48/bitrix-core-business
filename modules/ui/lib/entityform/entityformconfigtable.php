<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Entity;

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