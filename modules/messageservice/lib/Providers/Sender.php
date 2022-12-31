<?php

namespace Bitrix\MessageService\Providers;

use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

interface Sender
{
	public function sendMessage(array $messageFields): SendMessage;
	public function prepareMessageBodyForSave(string $text): string;
	public function prepareMessageBodyForSend(string $text): string;
	public function getMessageStatus(array $messageFields): MessageStatus;
}