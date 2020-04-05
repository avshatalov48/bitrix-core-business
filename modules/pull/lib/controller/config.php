<?php

namespace Bitrix\Pull\Controller;
use \Bitrix\Main\Error;

class Config extends \Bitrix\Main\Engine\Controller
{
	public function getAction($cache = true, $reopen = false)
	{
		$config = \Bitrix\Pull\Config::get([
			'CACHE' => $cache,
			'REOPEN' => $reopen,
			'JSON' => true
		]);

		if (!$config)
		{
			$this->errorCollection[] = new Error("Push & Pull server is not configured", "SERVER_ERROR");
			return null;
		}

		return $config;
	}

	public function extendWatchAction(array $tags)
	{
		$currentUserId = $this->getCurrentUser()->getId();

		return \CPullWatch::Extend($currentUserId, $tags);
	}
}