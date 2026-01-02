<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Main\Event;

class RegisterTypeEvent extends Event
{
	public function __construct()
	{
		parent::__construct('im', 'OnRegisterExternalChatTypes');
	}
}