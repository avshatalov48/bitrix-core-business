<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;

class EntityFieldType
{
	public const PRODUCT_PROPERTY = 'productproperty';
	public const PRODUCT_PROPERTY_SETTINGS = 'productpropertysettings';

	public static function prepareProductField(array $field, array $description, array $attributes): array
	{
		$field['NAME'] = $description['NAME'] ?? '';
		if (($description['TYPE'] ?? null) === self::PRODUCT_PROPERTY)
		{
			$field = static::prepareProductPropertyField($field, $description, $attributes);
		}

		return $field;
	}

	protected static function prepareProductPropertyField(array $field, array $description, array $attributes): array
	{
		$field['IS_DYNAMIC'] = true;
		$field['IS_MULTIPLE'] = in_array(Attributes::MULTIPLE, $attributes, true);
		$field['PROPERTY_TYPE'] = $description['PROPERTY_TYPE'];
		$field['USER_TYPE'] = $description['USER_TYPE'];
		if (isset($description['VALUES']))
		{
			$field['VALUES'] = $description['VALUES'];
		}

		return $field;
	}
}
