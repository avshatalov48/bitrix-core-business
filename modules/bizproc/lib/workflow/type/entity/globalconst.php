<?php
namespace Bitrix\Bizproc\Workflow\Type\Entity;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;

/**
 * Class GlobalConstTable
 * @package Bitrix\Bizproc\Workflow\Type\Entity
 * @internal
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
				'data_type' => 'string'
			],
			'DESCRIPTION' => [
				'data_type' => 'string'
			],

			'PROPERTY_TYPE' => [
				'data_type' => 'string'
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
		];
	}

	public static function upsertByProperty($constId, array $property)
	{
		$property = FieldType::normalizeProperty($property);

		$fields = [
			'NAME' => $property['Name'],
			'DESCRIPTION' => $property['Description'],
			'PROPERTY_TYPE' => $property['Type'],
			'IS_REQUIRED' => $property['Required'] ? 'Y' : 'N',
			'IS_MULTIPLE' => $property['Multiple'] ? 'Y' : 'N',
			'PROPERTY_OPTIONS' => $property['Options'],
			'PROPERTY_VALUE' => $property['Default'],
		];

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
		return [
			'Name' => $fields['NAME'],
			'Description' => $fields['DESCRIPTION'],
			'Type' => $fields['PROPERTY_TYPE'],
			'Required' => ($fields['IS_REQUIRED'] === 'Y'),
			'Multiple' => ($fields['IS_MULTIPLE'] === 'Y'),
			'Options' => $fields['PROPERTY_OPTIONS'],
			'Default' => $fields['PROPERTY_VALUE'],
		];
	}
}