<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CCommentBase
{
	protected $component = null;
	protected $handlers = [];

	function __construct(&$component)
	{
		$this->component = &$component;
		$methods = get_class_methods(static::class);
		foreach ($methods as $method)
		{
			if (mb_stripos($method, "On") === 0)
			{
				$this->addHandler($method, [$this, $method]);
			}
		}
	}

	protected function addHandler($eventName, $callback, $moduleId = "forum")
	{
		$this->handlers[] = [
			"moduleId" => $moduleId,
			"eventName" => $eventName,
			"id" => AddEventHandler($moduleId, $eventName, $callback)
		];
	}

	protected function removeHandler($eventName, $moduleId = "forum")
	{
		$newHandlers = [];
		foreach ($this->handlers as $handler)
		{
			if ($handler["eventName"] === $eventName && $handler["moduleId"] === $moduleId)
			{
				RemoveEventHandler($handler["moduleId"], $handler["eventName"], $handler["id"]);
			}
			else
			{
				$newHandlers[] = $handler;
			}
		}
		$this->handlers = $newHandlers;
	}

	public function OnCommentsFinished($component)
	{
		if ($component !== $this->component)
		{
			return;
		}
		foreach ($this->handlers as $handler)
		{
			RemoveEventHandler($handler["moduleId"], $handler["eventName"], $handler["id"]);
		}
		$this->handlers = [];
	}
}
