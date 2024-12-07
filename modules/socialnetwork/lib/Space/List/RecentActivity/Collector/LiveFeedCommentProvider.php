<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;


use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait\SecondaryEntityLoadTrait;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;

final class LiveFeedCommentProvider extends LiveFeedProvider
{
	use SecondaryEntityLoadTrait;

	public function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['livefeed_comment'];
	}
}
