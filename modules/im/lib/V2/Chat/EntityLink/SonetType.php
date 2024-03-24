<?php

namespace Bitrix\Im\V2\Chat\EntityLink;

use Bitrix\Im\V2\Chat\EntityLink;
use Bitrix\Main\Loader;

class SonetType extends EntityLink
{
	protected const HAS_URL = true;

	protected function getUrl(): string
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return '';
		}

		$url = \COption::GetOptionString('socialnetwork', 'workgroups_page', '/workgroups/', SITE_ID);
		$url .= "group/{$this->entityId}/";

		return $url;
	}
}