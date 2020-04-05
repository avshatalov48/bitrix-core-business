<?php

namespace Bitrix\Report\VisualConstructor\Fields;

use Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable;

/**
 * Class Container
 * @package Bitrix\Report\VisualConstructor\Fields
 */
class Container extends Base
{
	/**
	 * @var Base[] $elements
	 */
	private $elements = array();

	/**
	 * @return void
	 */
	public function printContent()
	{
		foreach ($this->elements as $element)
		{
			$element->render();
		}
	}

	/**
	 * Setter for name.
	 *
	 * @param string $name Name value.
	 * @return void
	 */
	public function setName($name)
	{
		foreach ($this->elements as $element)
		{
			if ($element instanceof BaseValuable)
			{
				$element->setName($name);
			}
		}
	}

	/**
	 * Add element before target element.
	 *
	 * @param Base $newField Element to insert to container.
	 * @param Base $targetField Element before which will insert.
	 * @return void
	 */
	public function addElementBefore(Base $newField, Base $targetField)
	{
		$indexToInsert = null;
		$newFieldsList = array();
		foreach ($this->elements as $key => $field)
		{
			if ($field === $targetField)
			{
				if ($newField->getKey())
				{
					$newFieldsList[$newField->getKey()] = $newField;
				}
				else
				{
					$newFieldsList[] = $newField;
				}
			}
			$newFieldsList[$key] = $field;
		}

		$this->elements = $newFieldsList;
	}

	/**
	 * Add element ager target element.
	 *
	 * @param Base $newField Element to insert to container.
	 * @param Base $targetField Element after which will insert.
	 * @return void
	 */
	public function addElementAfter(Base $newField, Base $targetField)
	{
		$indexToInsert = null;
		$newFieldsList = array();
		foreach ($this->elements as $key => $field)
		{
			$newFieldsList[$key] = $field;

			if ($field === $targetField)
			{
				if ($newField->getKey())
				{
					$newFieldsList[$newField->getKey()] = $newField;
				}
				else
				{
					$newFieldsList[] = $newField;
				}
			}
		}

		$this->elements = $newFieldsList;
	}

	/**
	 * @param Base $element Element insert to container.
	 * @return void
	 */
	public function addElement(Base $element)
	{
		if ($element->getKey())
		{
			$this->elements[$element->getKey()] = $element;
		}
		else
		{
			$this->elements[] = $element;
		}

	}

	/**
	 * @return Base[]
	 */
	public function getElements()
	{
		return $this->elements;
	}

	/**
	 * Find element in container elemen list.
	 *
	 * @param string $key Unique key for find element in container.
	 * @return Base|null
	 */
	public function getElement($key)
	{
		if (!isset($this->elements[$key]))
		{
			return null;
		}
		return $this->elements[$key];
	}

	/**
	 * Set multiple elements to container.
	 *
	 * @param Base[] $elements Element list.
	 * @return void
	 */
	public function setElements($elements)
	{
		$this->elements = $elements;
	}
}