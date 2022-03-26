<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

/**
 * Entity field class for object data type
 * @package bitrix
 * @subpackage main
 */
class ObjectField extends ScalarField
{
	/** @var string[] */
	protected $objectClasses = [];

	/** @var callable */
	protected $encodeFunction;

	/** @var callable */
	protected $decodeFunction;

	/**
	 * ObjectField constructor.
	 *
	 * @param string $name
	 * @param array $parameters
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($name, $parameters = [])
	{
		$this->addSaveDataModifier([$this, 'encode']);
		$this->addFetchDataModifier([$this, 'decode']);

		parent::__construct($name, $parameters);
	}

	/**
	 * @return string[]
	 */
	public function getObjectClasses()
	{
		return $this->objectClasses;
	}

	/**
	 * @param string|string[] $objectClass
	 *
	 * @return $this
	 */
	public function configureObjectClass($objectClass)
	{
		$classes = is_array($objectClass) ? $objectClass : [$objectClass];

		foreach ($classes as $class)
		{
			if (substr($class, 0, 1) !== '\\')
			{
				$class = '\\'.$class;
			}

			$this->objectClasses[] = $class;
		}

		return $this;
	}

	/**
	 * Custom encode handler
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function configureSerializeCallback($callback)
	{
		$this->encodeFunction = $callback;

		return $this;
	}

	/**
	 * Custom decode handler
	 *
	 * @param $callback
	 *
	 * @return $this
	 */
	public function configureUnserializeCallback($callback)
	{
		$this->decodeFunction = $callback;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function encode($value)
	{
		$callback = $this->encodeFunction;
		return $callback($value);
	}

	/**
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function decode($value)
	{
		$callback = $this->decodeFunction;
		return $callback($value);
	}

	/**
	 * @inheritDoc
	 * @return mixed
	 */
	public function cast($value)
	{
		// try to check type
		if (!empty($this->objectClasses))
		{
			$foundClass = false;

			foreach ($this->objectClasses as $objectType)
			{
				if ($value instanceof $objectType)
				{
					$foundClass = true;
					break;
				}
			}

			if (!$foundClass)
			{
				trigger_error(sprintf(
						'Invalid value type `%s` for `%s` field',
						gettype($value) === 'object' ? get_class($value) : gettype($value),
						$this->entity->getFullName().':'.$this->name
					), E_USER_WARNING
				);
			}
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbString($value);
	}

	/**
	 * @inheritDoc
	 */
	public function convertValueToDb($value)
	{
		return $value === null && $this->is_nullable
			? $value
			: $this->getConnection()->getSqlHelper()->convertToDbString($value);
	}

	/**
	 * @inheritDoc
	 */
	public function getGetterTypeHint()
	{
		return !empty($this->objectClasses)
			? join('|', $this->objectClasses)
			: 'mixed';
	}

	/**
	 * @inheritDoc
	 */
	public function getSetterTypeHint()
	{
		return !empty($this->objectClasses)
			? join('|', $this->objectClasses)
			: 'mixed';
	}
}