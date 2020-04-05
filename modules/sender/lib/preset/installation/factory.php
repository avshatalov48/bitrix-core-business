<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Preset\Installation;

use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\CodeBasedFactory;

/**
 * Class Factory
 * @package Bitrix\Sender\Preset\Installation
 */
class Factory extends CodeBasedFactory
{
	/**
	 * Get transport instances.
	 *
	 * @return iInstallable[]
	 */
	public static function getInstallable()
	{
		$list = static::getObjectInstances(__NAMESPACE__ . '\iInstallable');
		static::reset();

		return $list;
	}

	protected static function getInterface()
	{
		return __NAMESPACE__ . '\iInstallable';
	}

	protected static function getClasses()
	{
		return array(
			iInstallable::EVENT_NAME => Integration\EventHandler::onSenderPresetList(),
		);
	}
}