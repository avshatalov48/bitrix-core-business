<?php

namespace Bitrix\Report\VisualConstructor\Entity;

use Bitrix\Report\VisualConstructor\BaseConfigField;
use Bitrix\Report\VisualConstructor\Fields\Base;
use Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable;
use Bitrix\Report\VisualConstructor\Internal\Model;

/**
 * Class Configuration
 * @package Bitrix\Report\VisualConstructor\Entity
 */
class Configuration extends Model
{
	protected $gId;
	protected $weight = 0;
	protected $value;
	protected $fieldClassName = '';
	protected $key;


	/**
	 * Returns the list of pair for mapping data and object properties.
	 * Key is field in DataManager, value is object property.
	 *
	 * @return array
	 */
	public static function getMapAttributes()
	{
		$attributes = parent::getMapAttributes();
		$attributes['GID'] = 'gId';
		$attributes['UKEY'] = 'key';
		$attributes['SETTINGS'] = 'value';
		$attributes['CONFIGURATION_FIELD_CLASS'] = 'fieldClassName';
		$attributes['WEIGHT'] = 'weight';
		return $attributes;
	}

	/**
	 * Gets the fully qualified name of table class which belongs to current model.
	 *
	 * @return string
	 */
	public static function getTableClassName()
	{
		return ConfigurationSettingTable::getClassName();
	}


	/**
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Weight value for sorting.
	 *
	 * @param int $weight Weight value.
	 * @return void
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * @return mixed
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param mixed $key Unique key for configuration in context.
	 * @return void
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return unserialize($this->value, ['allowed_classes' => false]);
	}

	/**
	 * Serialize and set value.
	 *
	 * @param mixed $value Value to set.
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = serialize($value);
	}

	/**
	 * @return string
	 */
	public function getFieldClassName()
	{
		return $this->fieldClassName;
	}

	/**
	 * Set field class name.
	 *
	 * @see Base::getClassName()
	 * @param string $fieldClassName Field class name.
	 * @return void
	 */
	public function setFieldClassName($fieldClassName)
	{
		$this->fieldClassName = $fieldClassName;
	}

	/**
	 * Load configuration list by ids,
	 * query to db and after build configuration entities list
	 *
	 * @param array $ids Array of id.
	 * @return static[]
	 */
	public static function loadByIds(array $ids)
	{
		return static::getModelList(array(
			'select' => array('*'),
			'filter' => array('ID' => $ids),
		));
	}

	/**
	 * @return string
	 */
	public function getGId()
	{
		return $this->gId;
	}

	/**
	 * Setter for gId value.
	 *
	 * @param string $gId Value of gId.
	 * @return void
	 */
	public function setGId($gId)
	{
		$this->gId = $gId;
	}

}