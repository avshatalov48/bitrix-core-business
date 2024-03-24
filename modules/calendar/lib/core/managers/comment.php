<?php

namespace Bitrix\Calendar\Core\Managers;

use Bitrix\Calendar\Integration\SocialNetwork\SpaceService;

final class Comment
{
	private SpaceService $spaceService;

	public function __construct()
	{
		$this->spaceService = new SpaceService();
	}

	public function onEventCommentAdd(array $event): void
	{
		$this->spaceService->addEvent(
			'onCalendarEventCommentAdd',
			$event,
		);
	}
}