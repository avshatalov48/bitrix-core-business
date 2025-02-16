<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\Text\Emoji;

/**
 * Class EntityFormConfigTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EntityFormConfig_Query query()
 * @method static EO_EntityFormConfig_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EntityFormConfig_Result getById($id)
 * @method static EO_EntityFormConfig_Result getList(array $parameters = [])
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
			(new ArrayField('CONFIG'))
				->configureSerializeCallback(function ($value){
					return EntityFormConfigTable::serialize($value);
				})
				->configureUnserializeCallback(function ($value) {
					return EntityFormConfigTable::unserialize($value);
				}),
			new Entity\BooleanField('COMMON', [
				'values' => ['N', 'Y'],
				'required' => true,
				'default_value' => 'N'
			]),
			new Entity\BooleanField('AUTO_APPLY_SCOPE', [
				'values' => ['N', 'Y'],
				'required' => true,
				'default_value' => 'N'
			]),
			new Entity\StringField('OPTION_CATEGORY', [
				'required' => true,
				'size' => 50
			])
		];
	}

	private static function unserialize(string $fieldValue): array
	{
		$unserialized = unserialize($fieldValue, ['allowed_classes' => false]);

		if ($unserialized === false)
		{
			return [];
		}

		if (is_array($unserialized))
		{
			array_walk_recursive(
				$unserialized,
				function (&$value) {
					if (is_string($value))
					{
						$value = Emoji::decode($value);
					}
				}
			);
		}
		elseif (is_string($unserialized))
		{
			$unserialized = Emoji::decode($unserialized);
		}

		return is_array($unserialized) ? $unserialized : [$unserialized];
	}

	private static function serialize(array $fieldValue): string
	{
		array_walk_recursive(
			$fieldValue,
			function (&$value) {
				if (is_string($value))
				{
					$value = Emoji::encode($value);
				}
			}
		);

		return serialize($fieldValue);
	}
}