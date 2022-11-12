<?php
namespace Bitrix\Bizproc\Workflow\Type\Entity;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;

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

		if ($fields['CREATED_BY'] === null)
		{
			unset($fields['CREATED_BY']);
			unset($fields['CREATED_DATE']);
		}

		if ($fields['MODIFIED_BY'] === null)
		{
			unset($fields['MODIFIED_BY']);
			unset($fields['MODIFIED_DATE']);
		}

		$count = static::getCount(['=ID' => $constId]);
		if ($count > 0)
		{
			$result = static::update($constId, $fields);
		}
		else
		{
			$result = static::add($fields + ['ID' => $constId]);
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

		$normalized['Visibility'] = $property['Visibility'] ? (string)$property['Visibility'] : 'GLOBAL';
		$normalized['CreatedBy'] = ((int)$property['CreatedBy'] !== 0) ? (int)$property['CreatedBy'] : $userId;
		try
		{
			$normalized['CreatedDate'] = $property['CreatedDate']
				? new Main\Type\DateTime($property['CreatedDate'])
				: new Main\Type\DateTime()
			;
		}
		catch (\Bitrix\Main\ObjectException $e)
		{
		}

		$normalized['ModifiedBy'] = $userId;
		$normalized['ModifiedDate'] = new Main\Type\DateTime();

		return array_merge($normalized, $normalizedAsField);
	}
}
