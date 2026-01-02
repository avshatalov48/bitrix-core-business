<?php

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

interface ActionMessageInterface
{
	public function send(array $recipientIds = [], array $parameters = []): int;
}