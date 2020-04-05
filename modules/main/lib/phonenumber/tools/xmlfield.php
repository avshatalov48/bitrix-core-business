<?php

namespace Bitrix\Main\PhoneNumber\Tools;

class XmlField
{
	/** @var string */
	protected $name = '';
	/** @var bool */
	protected $multiple = false;
	/** @var \Bitrix\Main\PhoneNumber\Tools\XmlParser */
	protected $subParser = null;

	public function __construct($name, array $options = array())
	{
		$this->name = $name;
		if(array_key_exists('multiple', $options))
			$this->multiple = (bool)$options['multiple'];

		if(array_key_exists('subParser', $options) && is_object($options['subParser']))
			$this->subParser = $options['subParser'];
	}

	public function getName()
	{
		return $this->name;
	}

	public function isMultiple()
	{
		return $this->multiple;
	}

	/**
	 * @return \Bitrix\PropertiesService\XmlParser|null
	 */
	public function getSubParser()
	{
		return $this->subParser;
	}

	/**
	 * Modifier for the field value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function decodeValue($value)
	{
		return $value;
	}

	public static function getClass()
	{
		return get_called_class();
	}
}