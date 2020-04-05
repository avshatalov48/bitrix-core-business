<?php
namespace Bitrix\MessageService\Sender\Sms;

/**
 * Class Dummy. For testing purposes only. It saves SMS to the log by AddMessage2Log().
 * @example $eventManager = \Bitrix\Main\EventManager::getInstance(); $eventManager->registerEventHandler('messageservice', 'onGetSmsSenders', 'messageservice', 'Bitrix\MessageService\Sender\Sms\Dummy', 'onGetSmsSenders');
 */
class Dummy extends \Bitrix\MessageService\Sender\Base
{
	public function getId()
	{
		return 'dummy';
	}

	public function getName()
	{
		return 'Dummy SMS';
	}

	public function getShortName()
	{
		return $this->getName();
	}

	public function getFromList()
	{
		return [
			[
				'id' => 'test',
				'name' => 'test',
			]
		];
	}

	public function sendMessage(array $messageFieldsFields)
	{
		AddMessage2Log($messageFieldsFields);

		$result = new \Bitrix\MessageService\Sender\Result\SendMessage();

		$result->setStatus(\Bitrix\MessageService\MessageStatus::DELIVERED);
		$result->setExternalId(uniqid());

		return $result;
	}

	public function canUse()
	{
		return true;
	}

	public static function onGetSmsSenders()
	{
		return [new self()];
	}
}
