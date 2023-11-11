<?php

namespace Bitrix\Catalog\Filter;

use Bitrix\Main\Filter\Field;

/**
 * Trait for working data provider fields with prefixes.
 *
 * The basic work looks like this:
 * 1. added prefixes to the need fields using the `::prepareFieldsByPrefix` method (most likely in `\Bitrix\Main\Filter\DataProvider::prepareFields` method);
 * 2. processed fields with prefixes in the filter using the `::splitPrefixFilterValues` method (most likely in `\Bitrix\Main\Filter\DataProvider::prepareFilterValue` method);
 *
 * @see \Bitrix\Main\Filter\DataProvider
 */
trait PrefixableDataProviderTrait
{
	/**
	 * Split the filter values into two arrays:
	 * - with prefix;
	 * - other;
	 *
	 * @param string $prefix
	 * @param array $fields
	 *
	 * @return array[] in format [ $fieldsWithPrefix, $otherFields ]
	 */
	protected function splitPrefixFilterValues(string $prefix, array $fields): array
	{
		$otherFields = [];
		$prefixFields = [];

		foreach ($fields as $nameWithPrefix => $value)
		{
			$name = $this->removePrefix($prefix, $nameWithPrefix);

			if ($name === $nameWithPrefix)
			{
				$otherFields[$name] = $value;
			}
			else
			{
				$prefixFields[$name] = $value;
			}
		}

		return [$prefixFields, $otherFields];
	}

	protected function removePrefix(string $prefix, string $fieldId): string
	{
		return str_replace($prefix, '', $fieldId);
	}

	protected function addPrefix(string $prefix, string $fieldId): string
	{
		return $prefix . $fieldId;
	}

	/**
	 * Append prefix name.
	 *
	 * @param string $prefixNameTemplate template must contain the variable #NAME#
	 * @param string $fieldName
	 *
	 * @return string
	 */
	protected function appendPrefixName(string $prefixNameTemplate, string $fieldName): string
	{
		return str_replace('#NAME#', $fieldName, $prefixNameTemplate);
	}

	/**
	 * Adds a prefix to the fields, and also performs additional processing, depending on the parameters.
	 *
	 * @param string $prefix
	 * @param Field[] $fields
	 * @param string|null $prefixNameTemplate for details see method `::appendPrefixName`
	 * @param array|null $iconParams args for `Bitrix\Main\Filter\Field::setIconParams` method.
	 * @param string|null $sectionId args for `Bitrix\Main\Filter\Field::setSectionId` method.
	 *
	 * @return Field[]
	 */
	protected function prepareFieldsByPrefix(string $prefix, array $fields, ?string $prefixNameTemplate = null, ?array $iconParams = null, ?string $sectionId = null): array
	{
		$result = [];

		foreach ($fields as $id => $field)
		{
			if ($field instanceof Field)
			{
				$newId = $this->addPrefix($prefix, $id);
				$field->setID($newId);

				$name = $field->getName();
				if ($name)
				{
					if (isset($prefixNameTemplate))
					{
						$name = $this->appendPrefixName($prefixNameTemplate, $name);
					}

					$field->setName($name);
				}

				if (isset($iconParams))
				{
					$field->setIconParams($iconParams);
				}

				if (isset($sectionId))
				{
					$field->setSectionId($sectionId);
				}

				$result[$newId] = $field;
			}
		}

		return $result;
	}
}
