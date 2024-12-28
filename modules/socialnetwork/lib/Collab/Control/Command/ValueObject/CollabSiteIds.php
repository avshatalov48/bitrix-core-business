<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command\ValueObject;

use Bitrix\Socialnetwork\Site\Site;
use Bitrix\Socialnetwork\Control\Command\ValueObject\SiteIds;

class CollabSiteIds extends SiteIds
{
	public static function createWithDefaultValue(): static
	{
		$value = new static();

		$value->siteIds = Site::getInstance()->getCollabSiteIds();

		return $value;
	}
}