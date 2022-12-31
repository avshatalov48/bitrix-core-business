<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\MessageService\Providers\Edna;

class Registrar extends Edna\Registrar
{
	protected string $channelType = Edna\Constants\ChannelType::WHATSAPP;

	protected function getCallbackTypeList(): array
	{
		return [
			Edna\Constants\CallbackType::MESSAGE_STATUS,
			Edna\Constants\CallbackType::INCOMING_MESSAGE,
		];
	}
}