<?php

namespace Bitrix\Catalog\v2\PropertyValue;

use Bitrix\Catalog\v2\BaseEntity;

/**
 * Class PropertyValue
 *
 * @package Bitrix\Catalog\v2\PropertyValue
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyValue extends BaseEntity
{
	// ToDo property cast map (int)1 => '1'
	public function setValue($value): self
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $property */
		$property = $this->getParent();

		if ($property->getPropertyType() === 'S' && $property->getUserType() === 'HTML')
		{
			$value = $this->prepareValueForHtmlProperty($value);
		}

		return $this->setField('VALUE', $value);
	}

	// ToDo do we need to create HtmlProperty entity (descendant of PropertyValue)?
	protected function prepareValueForHtmlProperty($value)
	{
		if (!is_array($value) || !isset($value['TYPE']))
		{
			$oldValue = $this->getField('VALUE');
			$value = [
				'TEXT' => $value,
				'TYPE' => $oldValue['TYPE'] ?? 'HTML',
			];
		}

		return $value;
	}

	public function getValue()
	{
		return $this->getField('VALUE');
	}

	public function setDescription($description): self
	{
		return $this->setField('DESCRIPTION', $description);
	}

	public function getDescription()
	{
		return $this->getField('DESCRIPTION');
	}
}