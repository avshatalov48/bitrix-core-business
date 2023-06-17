<?php

namespace Bitrix\Im\V2\Chat;

class ChannelChat extends GroupChat
{
	protected function getDefaultType(): string
	{
		return self::IM_TYPE_CHANNEL;
	}
}
