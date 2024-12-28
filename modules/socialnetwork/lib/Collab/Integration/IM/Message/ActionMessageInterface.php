<?php

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

interface ActionMessageInterface
{
	public function runAction(array $recipientIds = [], array $parameters = []): int;
}