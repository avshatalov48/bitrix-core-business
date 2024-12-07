<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event\Dto;

final class CreateChannelThreadForEventDto
{
	public function __construct(readonly public int $threadId)
	{
	}
}