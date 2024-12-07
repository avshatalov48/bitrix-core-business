<?php

namespace Bitrix\Main\Engine\ActionFilter;


use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Event;
use Bitrix\Main\InvalidOperationException;
use Closure;

final class ClosureWrapper extends Base
{
	/**
	 * @var Closure
	 */
	private $closure;

	/**
	 * ClosureActionFilter constructor.
	 * @param Closure $closure
	 */
	public function __construct(Closure $closure)
	{
		$this->closure = $closure->bindTo($this, $this);
		parent::__construct();
	}

	/**
	 * @throws InvalidOperationException
	 */
	public function onBeforeAction(Event $event)
	{
		$this->ensureClosure();

		return \call_user_func($this->closure, $event);
	}

	/**
	 * @throws InvalidOperationException
	 */
	public function onAfterAction(Event $event)
	{
		$this->ensureClosure();

		return \call_user_func($this->closure, $event);
	}

	private function ensureClosure(): void
	{
		if ($this->closure === null)
		{
			$exception = new InvalidOperationException('Closure has "static" modifier and can\'t be used. Use non-static closure instead.');

			$exceptionHandling = Configuration::getValue('exception_handling');
			if (!empty($exceptionHandling['debug']))
			{
				Application::getInstance()->getExceptionHandler()->writeToLog($exception);
			}

			throw $exception;
		}
	}
}