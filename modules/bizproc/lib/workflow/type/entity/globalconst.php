<?php
namespace Bitrix\Bizproc\Workflow\Type\Entity;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;
use Bitrix\Main\ORM\Event;

/**
 * Class GlobalConstTable
 * @package Bitrix\Bizproc\Workflow\Type\Entity
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GlobalConst_Query query()
 * @method static EO_GlobalConst_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_GlobalConst_Result getById($id)
 * @method static EO_GlobalConst_Result getList(array $parameters = [])
 * @method static EO_GlobalConst_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection wakeUpCollection($rows)
 */
class GlobalConstTable extends Main\ORM\Data\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_global_const';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'string',
				'primary' => true,
			],
			'NAME' => [
				'data_type' => 'string',
			],
			'DESCRIPTION' => [
				'data_type' => 'string',
			],

			'PROPERTY_TYPE' => [
				'data_type' => 'string',
			],

			'IS_REQUIRED' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
			],
			'IS_MULTIPLE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
			],

			'PROPERTY_OPTIONS' => [
				'data_type' => 'string',
				'serialized' => true,
			],
			'PROPERTY_SETTINGS' => [
				'data_type' => 'string',
				'serialized' => true,
			],
			'PROPERTY_VALUE' => [
				'data_type' => 'string',
				'serialized' => true,
			],
			'CREATED_DATE' => [
				'data_type' => 'datetime',
			],
			'CREATED_BY' => [
				'data_type' => 'integer',
			],
			'VISIBILITY' => [
				'data_type' => 'string',
			],
			'MODIFIED_DATE' => [
				'data_type' => 'datetime',
			],
			'MODIFIED_BY' => [
				'data_type' => 'integer',
			],
		];
	}

	public static function upsertByProperty($constId, array $property, int $userId = null)
	{
		$property = static::normalizePropertyForUpsert($property, $userId);

		// PROPERTY_SETTINGS ?
		$fields = [
			'NAME' => trim($property['Name']),
			'DESCRIPTION' => $property['Description'],
			'PROPERTY_TYPE' => $property['Type'],
			'IS_REQUIRED' => $property['Required'] ? 'Y' : 'N',
			'IS_MULTIPLE' => $property['Multiple'] ? 'Y' : 'N',
			'PROPERTY_OPTIONS' => $property['Options'],
			'PROPERTY_VALUE' => $property['Default'],
			'VISIBILITY' => $property['Visibility'],
			'CREATED_BY' => $property['CreatedBy'],
			'CREATED_DATE' => $property['CreatedDate'],
			'MODIFIED_DATE' => $property['ModifiedDate'],
			'MODIFIED_BY' => $property['ModifiedBy'],
		];

		if ($userId === null)
		{
			unset($fields['CREATED_BY'], $fields['CREATED_DATE'], $fields['MODIFIED_BY'], $fields['MODIFIED_DATE']);
		}

		$oldProperty = static::getByPrimary((string)$constId)->fetch();
		if ($oldProperty)
		{
			if (isset($oldProperty['CREATED_BY']))
			{
				unset($fields['CREATED_BY'], $fields['CREATED_DATE']);
			}

			$result = static::update($constId, $fields);
		}
		else
		{
			$fields['ID'] = $constId;
			$result = static::add($fields);
		}

		return $result;
	}

	public static function convertToProperty(array $fields)
	{
		// Settings ?
		return [
			'Name' => $fields['NAME'],
			'Description' => $fields['DESCRIPTION'],
			'Type' => $fields['PROPERTY_TYPE'],
			'Required' => \CBPHelper::getBool($fields['IS_REQUIRED']),
			'Multiple' => \CBPHelper::getBool($fields['IS_MULTIPLE']),
			'Options' => $fields['PROPERTY_OPTIONS'],
			'Default' => $fields['PROPERTY_VALUE'],
			'CreatedBy' => (int)$fields['CREATED_BY'],
			'CreatedDate' => $fields['CREATED_DATE'],
			'Visibility' => $fields['VISIBILITY'],
			'ModifiedBy' => (int)$fields['MODIFIED_BY'],
			'ModifiedDate' => $fields['MODIFIED_DATE'],
		];
	}

	private static function normalizePropertyForUpsert($property, int $userId = null): array
	{
		$normalized = [];
		$normalizedAsField = FieldType::normalizeProperty($property);

		$normalized['Visibility'] = isset($property['Visibility']) ? (string)$property['Visibility'] : 'GLOBAL';
		$normalized['ModifiedBy'] = $userId;
		$normalized['CreatedBy'] = $userId;
		$normalized['ModifiedDate'] = new Main\Type\DateTime();
		$normalized['CreatedDate'] = $normalized['ModifiedDate'];

		return array_merge($normalized, $normalizedAsField);
	}

	public static function onBeforeUpdate(Event $event)
	{
		$result = new Main\ORM\EventResult();
		$result->unsetFields(['PROPERTY_TYPE', 'IS_REQUIRED', 'IS_MULTIPLE', 'VISIBILITY']);

		return $result;
	}
}
