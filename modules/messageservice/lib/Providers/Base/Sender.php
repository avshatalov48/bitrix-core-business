<?php

namespace Bitrix\MessageService\Providers\Base;

use Bitrix\Main\Text\Emoji;

abstract class Sender implements \Bitrix\MessageService\Providers\Sender
{

	public function prepareMessageBodyForSave(string $text): string
	{
		return Emoji::encode($text);
	}

	public function prepareMessageBodyForSend(string $text): string
	{
		return Emoji::decode($text);
	}

}