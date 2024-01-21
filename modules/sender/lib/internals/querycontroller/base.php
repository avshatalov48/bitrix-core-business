<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Base
 * @package Bitrix\Sender\Internals\QueryController
 */
class Base
{
	/** @var callable[] $checkers Checkers. */
	protected $checkers = array();

	/** @var callable[] $responseModifiers Response modifiers. */
	protected $responseModifiers = array();

	/**
	 * Add checker.
	 *
	 * @param callable $checker Checker.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function addChecker($checker)
	{
		if (!is_callable($checker))
		{
			throw new ArgumentException("Argument 'checker' should be callable.");
		}

		$this->checkers[] = $checker;
		return $this;
	}
	/**
	 * Set checkers.
	 *
	 * @param callable[] $checkers Checkers.
	 * @return $this
	 */
	public function setCheckers(array $checkers)
	{
		foreach ($checkers as $checker)
		{
			$this->addChecker($checker);
		}

		return $this;
	}

	/**
	 * Add response modifier.
	 *
	 * @param callable $modifier Modifier.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function addResponseModifier($modifier)
	{
		if (!is_callable($modifier))
		{
			throw new ArgumentException("Argument 'modifier' should be callable.");
		}

		$this->responseModifiers[] = $modifier;
		return $this;
	}

	/**
	 * Set response modifiers.
	 *
	 * @param callable[] $modifiers Modifiers.
	 * @return $this
	 */
	public function setResponseModifiers(array $modifiers)
	{
		foreach ($modifiers as $modifier)
		{
			$this->addResponseModifier($modifier);
		}

		return $this;
	}

	/**
	 * Get checkers.
	 *
	 * @return array
	 */
	public function getCheckers()
	{
		return $this->checkers;
	}

	/**
	 * Get response modifiers.
	 *
	 * @return array
	 */
	public function getResponseModifiers()
	{
		return $this->responseModifiers;
	}

	/**
	 * Call.
	 *
	 * @param callable $callee Callee.
	 * @param array $parameters Parameters.
	 * @return mixed
	 * @throws ArgumentException
	 */
	public static function call($callee, array $parameters = array())
	{
		if (!is_callable($callee))
		{
			throw new ArgumentException("Argument 'callee' should be callable.");
		}

		return call_user_func_array($callee, $parameters);
	}
}
