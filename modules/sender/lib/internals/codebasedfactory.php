<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Internals;

use Bitrix\Sender\Transport;

/**
 * Class CodeBasedFactory
 * @package Bitrix\Sender\Internals
 */
abstract class CodeBasedFactory
{
	protected static $instances = array();
	protected static $classNames = array();

	public static function reset()
	{
		static::$instances = array();
		static::$classNames = array();
	}

	protected static function getClasses()
	{
		return array();
	}

	protected static function getObjectInstance($interface, $code)
	{
		$classList = static::getObjectClassList($interface);
		foreach ($classList as $className)
		{
			if ($code == $className::CODE)
			{
				return new $className();
			}
		}

		return null;
	}

	protected static function getObjectInstances($interface)
	{
		/** @var Transport\iBase $interface Interface. */
		$eventName = $interface::EVENT_NAME;
		if (isset(static::$instances[$eventName]))
		{
			return static::$instances[$eventName];
		}

		static::$instances[$eventName] = array();
		$classList = static::getObjectClassList($interface);
		foreach ($classList as $className)
		{
			static::$instances[$eventName][] = new $className();
		}

		return static::$instances[$eventName];
	}

	protected static function getObjectClassList($interface)
	{
		$interfaceCode = $interface;
		/** @var Transport\iBase $interface Interface. */
		$eventName = $interface::EVENT_NAME;
		if (isset(static::$classNames[$eventName]))
		{
			return static::$classNames[$eventName];
		}

		static::$classNames[$eventName] = array();
		$classList = static::getClasses();
		$classList = isset($classList[$eventName]) ? $classList[$eventName] : array();
		foreach ($classList as $className)
		{
			$interfaces = class_implements($className);
			if ($interfaces && isset($interfaces[$interfaceCode]))
			{
				static::$classNames[$eventName][] = $className;
			}
		}

		return static::$classNames[$eventName];
	}
}