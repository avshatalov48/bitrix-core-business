<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Helper\Analytics;

use Bitrix\Main\Analytics\AnalyticsEvent;

class FeedAnalytics extends Analytics
{
	public const TOOL_FEED = 'feed';
	public const CATEGORY_POSTS_OPERATIONS = 'posts_operations';
	public const CATEGORY_COMMENTS_OPERATIONS = 'comments_operations';
	public const EVENT_POST_CREATE = 'post_create';
	public const EVENT_COMMENT_CREATE = 'comment_create';

	public const TYPE_POST = 'post';
	public const TYPE_POLL = 'poll';
	public const TYPE_ANNOUNCEMENT = 'announcement';
	public const TYPE_APPRECIATION = 'appreciation';

	public const SECTION_FEED = 'feed';
	public const SECTION_PROJECT = 'project';

	public function onPostCreate(
		?string $section,
		?string $element,
		bool $status,
		string $type,
		array $params = [],
	): void
	{
		$analyticsEvent = new AnalyticsEvent(
			self::EVENT_POST_CREATE,
			self::TOOL_FEED,
			self::CATEGORY_POSTS_OPERATIONS,
		);

		$this->sendAnalytics(
			$analyticsEvent,
			$type,
			$section,
			$element,
			null,
			$status,
			$params,
		);
	}

	public function onCommentAdd(
		?string $section,
		?string $element,
		bool $status,
		string $type,
		array $params = [],
	): void
	{
		$analyticsEvent = new AnalyticsEvent(
			self::EVENT_COMMENT_CREATE,
			self::TOOL_FEED,
			self::CATEGORY_COMMENTS_OPERATIONS,
		);

		$this->sendAnalytics(
			$analyticsEvent,
			$type,
			$section,
			$element,
			null,
			$status,
			$params,
		);
	}
}