<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main;

/**
 * Class Error
 * @package Bitrix\Sale\PaySystem
 */
final class Error extends Main\Error
{
	private const BINDING_LEVEL_BUYER = 10;

	private $bindingLevel = null;

	protected function setBindingLevel($level) : self
	{
		$this->bindingLevel = $level;

		return $this;
	}

	public function isVisibleForBuyer() : bool
	{
		return $this->bindingLevel === self::BINDING_LEVEL_BUYER;
	}

	public static function createForBuyer($message, $code = 0, $customData = null) : self
	{
		$error = new static($message, $code, $customData);

		return $error->setBindingLevel(self::BINDING_LEVEL_BUYER);
	}

	public static function create($message, $code = 0, $customData = null) : self
	{
		return new static($message, $code, $customData);
	}
}