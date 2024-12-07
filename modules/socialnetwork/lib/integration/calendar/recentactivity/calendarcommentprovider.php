<?php

namespace Bitrix\Socialnetwork\Integration\Calendar\RecentActivity;

use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait\SecondaryEntityLoadTrait;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;

final class CalendarCommentProvider extends CalendarProvider
{
	use SecondaryEntityLoadTrait;

	public function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['calendar_comment'];
	}
}
