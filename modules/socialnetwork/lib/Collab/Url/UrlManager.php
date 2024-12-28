<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Url;

use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Site\Site;

class UrlManager
{
	public static function getCollabUrlById(int $collabId, array $parameters = []): string
	{
		$chatId = (int)($parameters['chatId'] ?? 0);
		if ($chatId > 0)
		{
			$site = Site::getInstance();

			return $site->getDirectory() . 'online/?IM_DIALOG=chat' . $chatId;
		}

		$collab = CollabRegistry::getInstance()->get($collabId);
		if ($collab === null)
		{
			return '';
		}

		return static::getCollabUrl($collab);
	}

	public static function getCollabUrlTemplateDialogId(): string
	{
		$site = Site::getInstance();

		return $site->getDirectory() . 'online/?IM_DIALOG=#DIALOG_ID#';
	}

	public static function getCollabUrl(Collab $collab): string
	{
		$site = Site::getInstance();

		return $site->getDirectory() . 'online/?IM_DIALOG=' . $collab->getDialogId();
	}
}