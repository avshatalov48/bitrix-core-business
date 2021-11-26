<?php

namespace Bitrix\Bizproc\Workflow\Type\Entity;

use Bitrix\Main;

class GlobalVarTable extends Main\ORM\Data\DataManager
{
	public static function getTableName(): string
	{
		return 'b_bp_global_var';
	}

	public static function getMap(): array
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
			'PROPERTY_VALUE' => [
				'data_type' => 'string',
				'serialized' => true,
			],
		];
	}

	/**
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	public static function upsertByProperty(string $varId, array $property)
	{
		$property = \Bitrix\Bizproc\FieldType::normalizeProperty($property);

		$fields = [
			'NAME' => $property['Name'],
			'DESCRIPTION' => $property['Description'],
			'PROPERTY_TYPE' => $property['Type'],
			'IS_REQUIRED' => $property['Required'] ? 'Y' : 'N',
			'IS_MULTIPLE' => $property['Multiple'] ? 'Y' : 'N',
			'PROPERTY_OPTIONS' => $property['Options'],
			'PROPERTY_VALUE' => $property['Default'],
		];

		$count = static::getCount(['=ID' => $varId]);
		if ($count > 0)
		{
			$result = static::update($varId, $fields);
		}
		else
		{
			$result = static::add(['ID' => $varId] + $fields);
		}

		return $result;
	}

	public static function convertToProperty(array $fields): array
	{
		return [
			'Name' => $fields['NAME'],
			'Description' => $fields['DESCRIPTION'],
			'Type' => $fields['PROPERTY_TYPE'],
			'Required' => \CBPHelper::getBool($fields['IS_REQUIRED']),
			'Multiple' => \CBPHelper::getBool($fields['IS_MULTIPLE']),
			'Options' => $fields['PROPERTY_OPTIONS'],
			'Default' => $fields['PROPERTY_VALUE'],
		];
	}
}
