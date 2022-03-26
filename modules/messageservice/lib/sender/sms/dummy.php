<?php
namespace Bitrix\MessageService\Sender\Sms;

/**
 * Class Dummy. For testing purposes only. It saves SMS to the log by AddMessage2Log().
 *
 * @example $eventManager = \Bitrix\Main\EventManager::getInstance(); $eventManager->registerEventHandler('messageservice', 'onGetSmsSenders', 'messageservice', 'Bitrix\MessageService\Sender\Sms\Dummy', 'onGetSmsSenders');
 */
class Dummy extends \Bitrix\MessageService\Sender\Base
{
	public const ID = 'dummy';

	public function getId()
	{
		return static::ID;
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

		$dialogId = \Bitrix\Main\Config\Option::get('messageservice', 'dummy_dialog_id', '');
		if (
			!empty($dialogId)
			&& \Bitrix\Main\Loader::includeModule('im')
			&& \Bitrix\Im\Common::isChatId($dialogId)
		)
		{
			\CIMChat::AddMessage([
				'DIALOG_ID' => $dialogId,
				'USER_ID' => 0,
				'SYSTEM' => 'Y',
				'MESSAGE' => '[b]MessageService test message[/b] :idea: [br][br]' . print_r($messageFieldsFields, 1),
			]);
		}

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
