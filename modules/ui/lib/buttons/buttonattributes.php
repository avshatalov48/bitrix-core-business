<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Web\Json;

final class ButtonAttributes implements \ArrayAccess, \IteratorAggregate, \Countable, Arrayable
{
	const JSON_OPTIONS_DATA_ATTR = 'data-json-options';

	/** @var array */
	private $attributes = [];
	/** @var array */
	private $dataAttributes = [];

	public function __construct(array $attributes = [])
	{
		$this->setAttributes($attributes);
	}

	public function setAttributes(array $attributes)
	{
		list($this->dataAttributes, $this->attributes) = self::splitDataAttributesAndAnother($attributes);

		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed|null $defaultValue
	 *
	 * @return mixed|null
	 */
	public function getAttribute($name, $defaultValue = null)
	{
		return isset($this[$name])? $this[$name] : $defaultValue;
	}

	/**
	 * @param string $name
	 * @param mixed|null $value
	 *
	 * @return $this
	 */
	public function addDataAttribute($name, $value = null)
	{
		$this[self::addDataPrefix($name)] = $value;

		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed|null $defaultValue
	 *
	 * @return mixed|null
	 */
	public function getDataAttribute($name, $defaultValue = null)
	{
		return $this->getAttribute(self::addDataPrefix($name), $defaultValue);
	}

	/**
	 * @return array
	 */
	public function getDataAttributes()
	{
		return $this->dataAttributes;
	}

	public function addJsonOption($key, $value)
	{
		if (!isset($this[self::JSON_OPTIONS_DATA_ATTR]))
		{
			$this[self::JSON_OPTIONS_DATA_ATTR] = [];
		}

		$this[self::JSON_OPTIONS_DATA_ATTR][$key] = $value;

		return $this;
	}

	public function removeJsonOption($key)
	{
		unset($this[self::JSON_OPTIONS_DATA_ATTR][$key]);

		return $this;
	}

	public function setJsonOptions(array $options)
	{
		$this[self::JSON_OPTIONS_DATA_ATTR] = $options;

		return $this;
	}

	public function getJsonOptions()
	{
		return isset($this[self::JSON_OPTIONS_DATA_ATTR])? $this[self::JSON_OPTIONS_DATA_ATTR] : null;
	}

	/**
	 * @param string $className
	 *
	 * @return $this
	 */
	public function addClass($className)
	{
		if (!isset($this['class']))
		{
			$this['class'] = [];
		}

		if (!in_array($className, $this['class'], true))
		{
			$this['class'][] = $className;
		}

		return $this;
	}

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	public function hasClass($className)
	{
		return isset($this['class']) && in_array($className, $this['class'], true);
	}

	public function setClassList(array $classList)
	{
		$this['class'] = $classList;

		return $this;
	}

	/**
	 * @param string $className
	 *
	 * @return $this
	 */
	public function removeClass($className)
	{
		if (!isset($this['class']))
		{
			$this['class'] = [];
		}

		$index = array_search($className, $this['class'], true);
		if ($index !== false)
		{
			unset($this['class'][$index]);
		}

		return $this;
	}

	/**
	 * Retrieve an external iterator
	 * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->toArray());
	}

	/**
	 * Whether a offset exists
	 * @link https://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset)
	{
		$offset = mb_strtolower($offset);

		$asAttribute = isset($this->attributes[$offset]) || array_key_exists($offset, $this->attributes);
		if ($asAttribute)
		{
			return true;
		}

		if (!self::hasDataPrefix($offset))
		{
			return false;
		}

		$offset = self::deleteDataPrefix($offset);

		return isset($this->dataAttributes[$offset]) || array_key_exists($offset, $this->dataAttributes);
	}

	/**
	 * Offset to retrieve
	 * @link https://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function &offsetGet($offset)
	{
		$offset = mb_strtolower($offset);
		if (isset($this->attributes[$offset]) || array_key_exists($offset, $this->attributes))
		{
			return $this->attributes[$offset];
		}

		if (!self::hasDataPrefix($offset))
		{
			return null;
		}

		$offset = self::deleteDataPrefix($offset);
		if (isset($this->dataAttributes[$offset]) || array_key_exists($offset, $this->dataAttributes))
		{
			return $this->dataAttributes[$offset];
		}

		return null;
	}

	/**
	 * Offset to set
	 * @link https://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value)
	{
		if($offset === null)
		{
			$this->attributes[] = $value;
		}
		else
		{
			$offset = mb_strtolower($offset);
			if (self::hasDataPrefix($offset))
			{
				$this->dataAttributes[self::deleteDataPrefix($offset)] = $value;
			}
			else
			{
				$this->attributes[$offset] = $value;
			}
		}
	}

	/**
	 * Offset to unset
	 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset)
	{
		$offset = mb_strtolower($offset);
		if (isset($this->attributes[$offset]) || array_key_exists($offset, $this->attributes))
		{
			unset($this->attributes[$offset]);

			return;
		}

		if (!self::hasDataPrefix($offset))
		{
			return null;
		}

		$offset = self::deleteDataPrefix($offset);
		if (isset($this->dataAttributes[$offset]) || array_key_exists($offset, $this->dataAttributes))
		{
			unset($this->dataAttributes[$offset]);
		}
	}

	/**
	 * Count elements of an object
	 * @link https://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count()
	{
		return count($this->dataAttributes) + count($this->attributes);
	}

	/**
	 * @return string
	 */
	public function toString()
	{
		return (string)$this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$string = '';
		foreach ($this as $key => $value)
		{
			if (is_int($key))
			{
				$string .= "{$value} ";
			}
			else
			{
				if ($key === 'class')
				{
					$value = self::convertClassesToString($value);
				}
				elseif ($key === 'style')
				{
					$value = self::convertStylesToString($value);
				}
				elseif ($key === self::JSON_OPTIONS_DATA_ATTR)
				{
					$value = Json::encode($this->getJsonOptions());
				}

				$value = htmlspecialcharsbx($value);
				$string .= "{$key}=\"{$value}\" ";
			}
		}

		return $string;
	}

	protected static function convertClassesToString($classes)
	{
		if (is_string($classes))
		{
			return $classes;
		}

		return implode(' ', $classes);
	}

	protected static function convertStylesToString($styles)
	{
		if (is_string($styles))
		{
			return $styles;
		}

		$string = '';
		foreach ($styles as $name => $value)
		{
			$string .= "{$name}:{$value};";
		}

		return $string;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array_merge(
			$this->attributes,
			self::convertDataAttributesToAttributes($this->dataAttributes)
		);
	}

	protected static function addDataPrefix($name)
	{
		return "data-{$name}";
	}

	protected static function hasDataPrefix($name)
	{
		return is_string($name) && mb_substr($name, 0, 5) === 'data-';
	}

	protected static function deleteDataPrefix($name)
	{
		if (self::hasDataPrefix($name))
		{
			return mb_substr($name, 5);
		}

		return $name;
	}

	protected static function convertDataAttributesToAttributes(array $dataAttributes)
	{
		$attributes = [];
		foreach ($dataAttributes as $name => $attribute)
		{
			$attributes[self::addDataPrefix($name)] = $attribute;
		}

		return $attributes;
	}

	protected static function splitDataAttributesAndAnother(array $attributes)
	{
		$anotherAttributes = $dataAttributes = [];
		foreach ($attributes as $name => $attribute)
		{
			$name = mb_strtolower($name);
			if (self::hasDataPrefix($name))
			{
				$dataAttributes[mb_substr($name, 5)] = $attribute;
			}
			else
			{
				$anotherAttributes[$name] = $attribute;
			}
		}

		return [$dataAttributes, $anotherAttributes];
	}
}