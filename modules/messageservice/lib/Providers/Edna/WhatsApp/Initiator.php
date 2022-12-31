<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\MessageService\Providers\Edna\Constants\ChannelType;

class Initiator extends \Bitrix\MessageService\Providers\Edna\Initiator
{
	protected string $channelType = ChannelType::WHATSAPP;

}