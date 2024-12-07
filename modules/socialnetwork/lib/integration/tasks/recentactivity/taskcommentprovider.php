<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\RecentActivity;


use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait\SecondaryEntityLoadTrait;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;

final class TaskCommentProvider extends TaskProvider
{
	use SecondaryEntityLoadTrait;

	public function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['task_comment'];
	}
}
