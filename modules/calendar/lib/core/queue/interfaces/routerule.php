<?php

namespace Bitrix\Calendar\Core\Queue\Interfaces;

use Bitrix\Calendar\Core\Queue\Message\HandledMessage;

interface RouteRule
{
	public function route(Message $message): ?HandledMessage;
}