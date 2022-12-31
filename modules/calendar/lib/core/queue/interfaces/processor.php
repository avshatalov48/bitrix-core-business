<?php
namespace Bitrix\Calendar\Core\Queue\Interfaces;

interface Processor
{
	/**
	 * Use this constant when the message is processed successfully and the message could be removed from the queue.
	 */
	public const ACK = 'enqueue.ack';

	/**
	 * Use this constant when the message is not valid or could not be processed
	 * The message is removed from the queue.
	 */
	public const REJECT = 'enqueue.reject';

	/**
	 * Use this constant when the message is not valid or could not be processed right now, but we can try again later
	 * The original message is removed from the queue but a copy is published to the queue again.
	 */
	public const REQUEUE = 'enqueue.requeue';

	/**
	 * The method has to return either self::ACK, self::REJECT, self::REQUEUE string.
	 *
	 * The method also can return an object.
	 * It must implement __toString method and the method must return one of the constants from above.
	 *
	 * @param Message $message
	 // * @param Context $context // removed - It's not clear why it's here
	 *
	 * @return string|object with __toString method implemented
	 */
	public function process(Message $message): string;
}