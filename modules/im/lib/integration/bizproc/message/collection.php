<?php

namespace Bitrix\Im\Integration\Bizproc\Message;

use Bitrix\Main\Localization\Loc;

class Collection
{
	public const TEMPLATE_PLAIN = 'plain';
	public const TEMPLATE_NEWS = 'news';
	public const TEMPLATE_NOTIFY = 'notify';
	public const TEMPLATE_IMPORTANT = 'important';
	public const TEMPLATE_ALERT = 'alert';

	public static function getTemplateList(): array
	{
		return [
			static::TEMPLATE_PLAIN => Loc::getMessage('IM_BIZPROC_MESSAGE_COLLECTION_PlAIN'),
			static::TEMPLATE_NEWS => Loc::getMessage('IM_BIZPROC_MESSAGE_COLLECTION_NEWS'),
			static::TEMPLATE_NOTIFY => Loc::getMessage('IM_BIZPROC_MESSAGE_COLLECTION_NOTIFY'),
			static::TEMPLATE_IMPORTANT => Loc::getMessage('IM_BIZPROC_MESSAGE_COLLECTION_IMPORTANT'),
			static::TEMPLATE_ALERT => Loc::getMessage('IM_BIZPROC_MESSAGE_COLLECTION_ALERT'),
		];
	}

	public static function makeTemplate(string $type): Template
	{
		return match ($type)
		{
			static::TEMPLATE_NEWS => new NewsTemplate(),
			static::TEMPLATE_NOTIFY => new NotifyTemplate(),
			static::TEMPLATE_IMPORTANT => new ImportantTemplate(),
			static::TEMPLATE_ALERT => new AlertTemplate(),
			default => new PlainTemplate(),
		};
	}
}