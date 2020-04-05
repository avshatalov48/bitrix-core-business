<?php

namespace Bitrix\Pull\Controller;
use \Bitrix\Main\Error;

class Channel extends \Bitrix\Main\Engine\Controller
{
	public function getPublicIdsAction(array $users)
	{
		$config = \Bitrix\Pull\Channel::getPublicIds([
			'TYPE' => \CPullChannel::TYPE_PRIVATE,
			'USERS' => $users,
			'JSON' => true
		]);

		if (!$config)
		{
			$this->errorCollection[] = new Error("Push & Pull server is not configured", "SERVER_ERROR");
			return null;
		}

		return $config;
	}
}
