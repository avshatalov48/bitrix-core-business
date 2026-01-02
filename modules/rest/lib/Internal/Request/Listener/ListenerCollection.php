<?php

namespace Bitrix\Rest\Internal\Request\Listener;

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;

class ListenerCollection
{
	/** @var Listener[] */
	private array $listeners = [];

	public function addListener(Listener $listener): void
	{
		$this->listeners[] = $listener;
	}

	public function handleRequest(HttpRequest $request = null): void
	{
		$request ??= Application::getInstance()->getContext()->getRequest();

		foreach ($this->listeners as $listener)
		{
			$listener->handle($request);
		}
	}
}
