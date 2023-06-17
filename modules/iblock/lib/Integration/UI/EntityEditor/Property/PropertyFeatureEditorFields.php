<?php

namespace Bitrix\Iblock\Integration\UI\EntityEditor\Property;

use Bitrix\Iblock\Model\PropertyFeature;

/**
 * Object for work with property feature fields.
 *
 * @see \Bitrix\Iblock\Model\PropertyFeature for details.
 */
class PropertyFeatureEditorFields
{
	private array $property;
	private array $fields;

	/**
	 * @param array $propertyFields
	 */
	public function __construct(array $propertyFields)
	{
		$this->property = $propertyFields;
	}

	/**
	 * Feature fields for entity editor.
	 *
	 * @return array
	 */
	public function getEntityFields(): array
	{
		if (isset($this->fields))
		{
			return $this->fields;
		}

		if (!PropertyFeature::isEnabledFeatures())
		{
			return [];
		}

		$this->fields = [];

		$features = PropertyFeature::getPropertyFeatureList($this->property);
		foreach ($features as $feature)
		{
			$index = PropertyFeature::getIndex($feature);

			$this->fields[] = [
				'name' => "FEATURES[{$index}]",
				'title' => $feature['FEATURE_NAME'],
				'type' => 'boolean',
			];
		}

		return $this->fields;
	}

	/**
	 * Property is has feature fields.
	 *
	 * @return bool
	 */
	public function isHasFields(): bool
	{
		return !empty($this->getEntityFields());
	}
}
