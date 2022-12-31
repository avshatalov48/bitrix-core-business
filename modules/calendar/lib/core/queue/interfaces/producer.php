<?php
namespace Bitrix\Calendar\Core\Queue\Interfaces;

use Bitrix\Calendar\Core\Queue\Exception\InvalidDestinationException;
use Bitrix\Calendar\Core\Queue\Exception\InvalidMessageException;

interface Producer
{
	/**
	 * @throws Exception                   if the provider fails to send the message due to some internal error
	 * @throws InvalidDestinationException if a client uses this method with an invalid destination
	 * @throws InvalidMessageException     if an invalid message is specified
	 */
	public function send(Message $message): void;
}