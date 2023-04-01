<?php

namespace Bitrix\Calendar\Core\Queue\Examples;

use Bitrix\Calendar\Core\Queue\Message\Message;
use Bitrix\Calendar\Core\Queue\Rule\Registry;
use Bitrix\Calendar\Core\Queue;
use CCalendar;

class ProducerClientExample
{
	/**
	 * @return void
	 *
	 * @throws Queue\Exception\InvalidDestinationException
	 * @throws Queue\Exception\InvalidMessageException
	 * @throws Queue\Exception\InvalidRuleException
	 * @throws Queue\Interfaces\Exception
	 */
	public static function run()
	{
		// it's only for example
		// in real cases rule should register in advance
		self::registerRule();

		$message = self::generateMessage();
		self::sendMessage($message);
	}

	/**
	 * @return Message
	 */
	private static function generateMessage(): Message
	{
		return (new Message())
			->setBody([
				'userId' => CCalendar::GetUserId(), // notice recipient
				'content' => 'Message sended ' . date('Y-m-d H:i:s'), // notice text
				'exampleField' => 'dwdwdw', // field for hash
			])
			->setRoutingKey('example')
			;
	}

	/**
	 * @param Message $message
	 *
	 * @return void
	 *
	 * @throws Queue\Exception\InvalidDestinationException
	 * @throws Queue\Exception\InvalidMessageException
	 * @throws Queue\Interfaces\Exception
	 */
	private static function sendMessage(Message $message)
	{
		Queue\Producer\Factory::getProduser()->send($message);
	}

	/**
	 * @return void
	 *
	 * @throws Queue\Exception\InvalidRuleException
	 */
	private static function registerRule()
	{
		Registry::getInstance()->registerRuleClass(RuleExample::class);
	}
}