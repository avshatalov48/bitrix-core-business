<?php

namespace Bitrix\Im\V2\Chat\EntityLink;

use Bitrix\Im\V2\Chat\EntityLink;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;

class CalendarType extends EntityLink
{
	protected const HAS_URL = true;

	protected function getUrl(): string
	{
		if (!Loader::includeModule('calendar'))
		{
			return '';
		}

		$uri = new Uri(\CCalendar::GetPathForCalendarEx($this->getContext()->getUserId()));
		$uri->addParams(['EVENT_ID' => $this->entityId]);
		$url = $uri->getUri();

		return $url;
	}
}