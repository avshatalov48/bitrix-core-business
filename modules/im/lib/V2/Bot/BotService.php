<?php

namespace Bitrix\Im\V2\Bot;

use Bitrix\Im;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message\Send\SendingConfig;

class BotService
{
	use ContextCustomer;

	private SendingConfig $sendingConfig;

	/**
	 * @param SendingConfig|null $sendingConfig
	 */
	public function __construct(?SendingConfig $sendingConfig = null)
	{
		if ($sendingConfig === null)
		{
			$sendingConfig = new SendingConfig();
		}
		$this->sendingConfig = $sendingConfig;
	}

	/**
	 * @param Chat $chat
	 * @param Message $message
	 * @return void
	 */
	public function runMessageCommand(int $messageId, array $fields): void
	{
		$fields['COMMAND_CONTEXT'] = 'TEXTAREA';
		$result = Im\Command::onCommandAdd($messageId, $fields);
		if (!$result)
		{
			Im\Bot::onMessageAdd($messageId, $fields);
		}
	}
}