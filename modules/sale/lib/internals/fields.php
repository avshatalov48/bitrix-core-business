<?php
namespace Bitrix\Sale\Internals;

class Fields
	implements \ArrayAccess, \Iterator, \Countable
{
	/** @var  array */
	protected $values = array();

	/** @var array */
	protected $changedValues = array();

	/** @var  array */
	private $originalValues = array();

	/** @var  array */
	private $customFields = array();

	/** @var bool  */
	protected $isClone = false;


	public function __construct(array $values = null)
	{
		if ($values !== null)
			$this->values = $values;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function isChanged($name)
	{
		return isset($this->changedValues[$name]);
	}

	/**
	 * Returns any variable by its name. Null if variable is not set.
	 *
	 * @param string $name
	 * @return string | null
	 */
	public function get($name)
	{
		if (isset($this->values[$name]))
		{
			return $this->values[$name];
		}

		return null;
	}

	/**
	 * @param string $field
	 */
	public function markCustom(string $field)
	{
		$this->customFields[$field] = true;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public function isMarkedCustom(string $field) : bool
	{
		if (!isset($this->customFields[$field]))
		{
			return false;
		}

		return $this->customFields[$field];
	}

	/**
	 * @param string $field
	 */
	public function unmarkCustom(string $field)
	{
		$this->customFields[$field] = false;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function set($name, $value)
	{
		if ($this->markChanged($name, $value))
		{
			$this->values[$name] = $value;
			return true;
		}

		return false;
	}

	/**
	 * @internal
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function init($name, $value)
	{
		$this->values[$name] = $value;
		return true;
	}


	public function clear()
	{
		$this->values = array();
		$this->clearChanged();
	}

	public function clearChanged()
	{
		$this->changedValues = array();
		$this->originalValues = array();
	}

	/**
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @ine
	 * @return array
	 */
	public function getOriginalValues()
	{
		return $this->originalValues;
	}

	/**
	 * @param array $values
	 */
	public function setValues(array $values)
	{
		foreach ($values as $name => $value)
			$this->set($name, $value);
	}

	/**
	 * @param array $values
	 */
	public function resetValues(array $values)
	{
		$this->values = array();
		if ($values !== null)
			$this->values = $values;
	}

	/**
	 * @param $name
	 * @param $value
	 * @return bool
	 */
	protected function markChanged($name, $value)
	{
		$originalValuesIndex = array();
		if (!empty($this->originalValues))
		{
			foreach(array_keys($this->originalValues) as $originalKey)
			{
				$originalValuesIndex[$originalKey] = true;
			}
		}

		$oldValue = $this->get($name);
		if ($oldValue != $value || ($oldValue === null && $value !== null))
		{
			if (!isset($originalValuesIndex[$name]))
			{
				$this->originalValues[$name] = $this->get($name);
			}
			elseif ($this->originalValues[$name] == $value)
			{
				unset($this->changedValues[$name]);
				unset($this->originalValues[$name]);
				return true;
			}

			$this->changedValues[$name] = true;
			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getChangedKeys()
	{
		return array_keys($this->changedValues);
	}

	/**
	 * @return array
	 */
	public function getChangedValues()
	{
		$r = array();
		foreach ($this->values as $k => $v)
		{
			if (isset($this->changedValues[$k]))
				$r[$k] = $v;
		}
		return $r;
	}

	/**
	 * Return the current element
	 */
	public function current()
	{
		return current($this->values);
	}

	/**
	 * Move forward to next element
	 */
	public function next()
	{
		return next($this->values);
	}

	/**
	 * Return the key of the current element
	 */
	public function key()
	{
		return key($this->values);
	}

	/**
	 * Checks if current position is valid
	 */
	public function valid()
	{
		$key = $this->key();
		return ($key != null);
	}

	/**
	 * Rewind the Iterator to the first element
	 */
	public function rewind()
	{
		return reset($this->values);
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->values[$offset]) || array_key_exists($offset, $this->values);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 *
	 * @return null|string
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->values[$offset]);
		if (isset($this->changedValues[$offset]))
			unset($this->changedValues[$offset]);
	}

	/**
	 * Count elements of an object
	 */
	public function count()
	{
		return count($this->values);
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return Fields
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$fieldsClone = clone $this;
		$fieldsClone->isClone = true;

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $fieldsClone;
		}
		
		return $fieldsClone;
	}

	/**
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}
}