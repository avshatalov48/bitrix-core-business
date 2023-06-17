<?php

namespace Bitrix\Pull\Push\Message;

class AppleVoipMessage extends AppleMessage
{
	protected function getAlertData()
	{
		return $this->text;
	}
}