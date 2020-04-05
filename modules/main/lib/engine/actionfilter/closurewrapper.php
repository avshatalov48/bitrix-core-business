<?php

namespace Bitrix\Main\Engine\ActionFilter;


use Bitrix\Main\Event;
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
		$this->closure = $closure->bindTo($this);
		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		return call_user_func($this->closure, $event);
	}

	public function onAfterAction(Event $event)
	{
		return call_user_func($this->closure, $event);
	}
}