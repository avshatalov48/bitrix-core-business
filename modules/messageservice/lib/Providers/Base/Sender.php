<?php

namespace Bitrix\MessageService\Providers\Base;

abstract class Sender implements \Bitrix\MessageService\Providers\Sender
{

	public function prepareMessageBodyForSave(string $text): string
	{
		return $text;
	}

}