<?php

namespace Bitrix\Pull\Push\Service;

interface PushService
{
	function getBatch(array $messageList): string;
}