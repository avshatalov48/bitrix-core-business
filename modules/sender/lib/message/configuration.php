<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;

Loc::getMessage(__FILE__);

class Configuration
{
	/** @var integer|string|null $id ID. */
	protected $id;

	/** @var array $data Data. */
	protected $data = array();

	/** @var callable $view View. */
	protected $view;

	/** @var ConfigurationOption[] $options UI Options. */
	protected $options = array();

	/**
	 * Configuration constructor.
	 * @param array $data Data.
	 */
	public function __construct(array $data = array())
	{
		if ($data)
		{
			$this->data = $data;
		}
	}

	/**
	 * Get ID.
	 *
	 * @return int|string|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param int|string|null $id ID.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get view.
	 *
	 * @return string|null
	 */
	public function getView()
	{
		if (!is_callable($this->view))
		{
			return null;
		}

		return call_user_func_array($this->view, array());
	}

	/**
	 * Set view.
	 *
	 * @param callable $view View.
	 */
	public function setView($view)
	{
		$this->view = $view;
	}

	/**
	 * Set.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
		$option = $this->getOption($key);
		if ($option)
		{
			$option->setValue($value);
		}
	}

	/**
	 * Get.
	 *
	 * @param string $key Key
	 * @param mixed $defaultValue Default value.
	 * @return mixed
	 */
	public function get($key, $defaultValue = null)
	{
		if (isset ($this->data[$key]))
		{
			if (is_callable($this->data[$key]))
			{
				return $this->data[$key]();
			}

			return $this->data[$key];
		}

		$option = $this->getOption($key);
		if ($option)
		{
			return $option->getValue();
		}

		return $defaultValue;
	}

	/**
	 * Get option.
	 *
	 * @param $key
	 * @return ConfigurationOption|null
	 */
	public function getOption($key)
	{
		foreach ($this->options as $option)
		{
			if ($option->getCode() == $key)
			{
				return $option;
			}
		}

		return null;
	}

	/**
	 * Has options.
	 *
	 * @return boolean
	 */
	public function hasOptions()
	{
		return count($this->options) > 0;
	}

	/**
	 * Get options.
	 *
	 * @return ConfigurationOption[]
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Get Array options.
	 *
	 * @return array
	 */
	public function getArrayOptions()
	{
		return self::convertToArray($this->options);
	}

	/**
	 * Convert to array.
	 *
	 * @param ConfigurationOption[] $options Options.
	 * @return array
	 */
	public static function convertToArray(array $options)
	{
		$list = array();
		foreach ($options as $option)
		{
			$list[] = $option->getArray();
		}

		return $list;
	}

	/**
	 * Add option.
	 *
	 * @param ConfigurationOption $option Option.
	 * @param string $targetOptionCode Target option code.
	 * @param bool $isInsertAfter Is insert after.
	 * @return void
	 * @throws ArgumentException
	 */
	public function addOption(ConfigurationOption $option, $targetOptionCode = null, $isInsertAfter = true)
	{
		if ($option->isTemplated() && $this->hasTemplatedOption())
		{
			throw new ArgumentException('Templated option already exists.');
		}

		$uniqueTypes = array(
			ConfigurationOption::TYPE_TEMPLATE_TYPE,
			ConfigurationOption::TYPE_TEMPLATE_ID
		);
		if (in_array($option->getType(), $uniqueTypes) && $this->hasOptionsOfType($option->getType()))
		{
			throw new ArgumentException('Option with type `' . $option->getType() . '` already exists.');
		}

		if ($targetOptionCode)
		{
			$index = array_search($this->getOption($targetOptionCode), $this->options);
			if ($isInsertAfter)
			{
				$index++;
			}
			$this->options = array_merge(
				array_slice($this->options, 0, $index),
				array($option),
				array_slice($this->options, $index)
			);

		}
		else
		{
			$this->options[] = $option;
		}
	}

	/**
	 * Set array options.
	 *
	 * @param array $options Options.
	 * @return void
	 */
	public function setArrayOptions(array $options)
	{
		foreach ($options as $option)
		{
			$this->addOption(new ConfigurationOption($option));
		}
	}

	/**
	 * Get templated option.
	 *
	 * @return ConfigurationOption
	 */
	public function getTemplatedOption()
	{
		foreach ($this->options as $option)
		{
			if ($option->isTemplated())
			{
				return $option;
			}
		}

		return null;
	}

	/**
	 * Has templated option.
	 *
	 * @return bool
	 */
	public function hasTemplatedOption()
	{
		return $this->getTemplatedOption() !== null;
	}

	/**
	 * Get options by group.
	 *
	 * @param integer $group Group.
	 * @return ConfigurationOption[]
	 */
	public function getOptionsByGroup($group)
	{
		$result = array();
		foreach ($this->options as $option)
		{
			if ($option->getGroup() == $group)
			{
				$result[] = $option;
			}
		}

		return $result;
	}

	/**
	 * Get options by type.
	 *
	 * @param string $type Type.
	 * @return ConfigurationOption[]
	 */
	public function getOptionsByType($type)
	{
		$result = array();
		foreach ($this->options as $option)
		{
			if ($option->getType() == $type)
			{
				$result[] = $option;
			}
		}

		return $result;
	}

	/**
	 * Get options of type.
	 *
	 * @param string $type Type.
	 * @return ConfigurationOption|null
	 */
	public function getOptionByType($type)
	{
		return current($this->getOptionsByType($type));
	}

	/**
	 * Has options of type.
	 *
	 * @param string $type Type.
	 * @return bool
	 */
	public function hasOptionsOfType($type)
	{
		return count($this->getOptionsByType($type)) > 0;
	}

	/**
	 * Check options.
	 *
	 * @return Result
	 */
	public function checkOptions()
	{
		$result = new Result;
		$this->checkRequiredOptions($result);

		return $result;
	}

	/**
	 * Check required options.
	 *
	 * @param Result $result Result.
	 * @return Result
	 */
	protected function checkRequiredOptions(Result $result = null)
	{
		if (!$result)
		{
			$result = new Result;
		}

		foreach ($this->getOptions() as $option)
		{
			if (!$option->isRequired())
			{
				continue;
			}

			if ($option->hasValue())
			{
				continue;
			}

			$result->addError(new Error(
				Loc::getMessage(
					'SENDER_MESSAGE_CONFIG_ERROR_EMPTY_REQUIRED_FIELD',
					array('%name%' => $option->getName())
				)
			));
		}

		return $result;
	}
}