<?php

namespace Bitrix\Calendar\OpenEvents\Dto\Category;

use Bitrix\Calendar\Event\Service\OpenEventPullService;

final class PullEventUserFieldsBuilder
{
	public static function build(PullEventUserFields $pullEventUserFields): array
	{
		return [
			OpenEventPullService::EVENT_USER_FIELDS_KEY => $pullEventUserFields->toArray(),
		];
	}
}
