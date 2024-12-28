<?php

namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Analytics\AnalyticsEvent;

class Analytics
{
	protected static ?array $instances = [];

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

	// Elements
	public const ELEMENT_COMMENT_BUTTON = 'comment_button';
	public const ELEMENT_ADD_COMMENT_FIELD = 'add_comment_field';
	public const ELEMENT_REPLY_BUTTON = 'reply_button';
	public const ELEMENT_QUOTE = 'quote';

	public const STATUS_SUCCESS = 'success';
	public const STATUS_ERROR = 'error';

	public static function getInstance(): static
	{
		if (!isset(static::$instances[static::class]))
		{
			static::$instances[static::class] = new static();
		}

		return static::$instances[static::class];
	}

	public static function getTools(): array
	{
		return [
			self::TOOL_FEED,
		];
	}

	public static function getCategories(): array
	{
		return [
			self::CATEGORY_POSTS_OPERATIONS,
			self::CATEGORY_COMMENTS_OPERATIONS,
		];
	}

	public static function getEvents(): array
	{
		return [
			self::EVENT_POST_CREATE,
			self::EVENT_COMMENT_CREATE,
		];
	}

	public static function getTypes(): array
	{
		return [
			self::TYPE_POST,
			self::TYPE_POLL,
			self::TYPE_ANNOUNCEMENT,
			self::TYPE_APPRECIATION,
		];
	}

	public static function getSections(): array
	{
		return [
			self::SECTION_FEED,
			self::SECTION_PROJECT,
		];
	}

	public static function getSubSections(): array
	{
		return [];
	}

	public static function getElements(): array
	{
		return [
			self::ELEMENT_COMMENT_BUTTON,
			self::ELEMENT_ADD_COMMENT_FIELD,
			self::ELEMENT_REPLY_BUTTON,
			self::ELEMENT_QUOTE,
		];
	}

	public static function getStatuses(): array
	{
		return [
			self::STATUS_SUCCESS,
			self::STATUS_ERROR,
		];
	}

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

	protected function sendAnalytics(
		AnalyticsEvent $analyticsEvent,
		?string $type = null,
		?string $section = null,
		?string $element = null,
		?string $subSection = null,
		bool $status = true,
		array $params = [],
	): void
	{
		$analyticsEvent->setStatus($status ? self::STATUS_SUCCESS : self::STATUS_ERROR);

		if (!empty($type))
		{
			$analyticsEvent->setType($type);
		}

		if (!empty($section))
		{
			$analyticsEvent->setSection($section);
		}
		if (!empty($element))
		{
			$analyticsEvent->setElement($element);
		}
		if (!empty($subSection))
		{
			$analyticsEvent->setSubSection($subSection);
		}

		foreach ($params as $pName => $pValue)
		{
			$setter = 'set' . ucfirst($pName);
			if (!is_string($pValue) || empty($pValue))
			{
				continue;
			}

			if (!method_exists($analyticsEvent, $setter))
			{
				continue;
			}

			$analyticsEvent->$setter($pValue);
		}

		$analyticsEvent->send();
	}
}