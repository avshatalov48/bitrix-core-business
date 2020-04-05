<?php

namespace Bitrix\Report\VisualConstructor;

/**
 * Class Category, create instance of this class and than you can attach it to your report
 * @property array children
 * @property array parent
 * @package Bitrix\Report\VisualConstructor
 */
class Category
{
	private $key;
	private $label;
	private $parentKey = NULL;
	private $relations = array();

	/**
	 * @return mixed
	 */
	public function getParentKey()
	{
		return $this->parentKey;
	}

	/**
	 * Setter for parent category.
	 *
	 * @param string $parentKey Parent category key.
	 * @return void
	 */
	public function setParentKey($parentKey)
	{
		$this->parentKey = $parentKey;
	}

	/**
	 * @return string Category identify key.
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Setter for key of category.
	 *
	 * @param string $key Category identify key.
	 * @return void
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Setter for category label, this label will appear in categories lists.
	 *
	 * @param string $label Label value.
	 * @return void
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}


	/**
	 * Setter for not exist properties.
	 * All not exist properties will try to write to relations.
	 *
	 * @param string $key Key of set function.
	 * @param mixed $value Value which try to set.
	 * @return $this
	 */
	public function __set($key, $value)
	{
		$this->relations[$key] = $value;
		return $this;
	}

	/**
	 * Getter for not exist properties
	 * Return realtion value.
	 *
	 * @param string $value Relation name.
	 * @return mixed
	 */
	public function __get($value)
	{
		return $this->relations[$value];
	}
}