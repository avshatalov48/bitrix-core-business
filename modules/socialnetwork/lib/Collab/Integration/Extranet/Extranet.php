<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\Extranet;

use Bitrix\Extranet\PortalSettings;
use Bitrix\Main\Loader;
use CExtranet;

class Extranet
{
	public const EXTRANET_SITE_NAME = 'extranet';

	public static function getSiteId(): string
	{
		if (!Loader::includeModule('extranet'))
		{
			return '';
		}

		return (string)CExtranet::GetExtranetSiteID();
	}

	public static function getSiteName(): string
	{
		if (!Loader::includeModule('extranet'))
		{
			return '';
		}

		return static::EXTRANET_SITE_NAME;
	}

	public static function isEnabledCollabersInvitation(): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return PortalSettings::getInstance()->isEnabledCollabersInvitation();
	}
}