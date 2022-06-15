<?php

namespace Bitrix\Seo\Media;

use Bitrix\Seo\LeadAds;
use Bitrix\Seo\Media\Services\MediaVkontakte;

/**
 * Class Service
 *
 * @package Bitrix\Seo\LeadAds
 */
class Service extends LeadAds\Service
{
	public const GROUP = 'media';
	public const TYPE_VKONTAKTE = 'vkontakte';

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypes(): array
	{
		return [
			static::TYPE_VKONTAKTE,
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getMethodPrefix(): string
	{
		return 'media';
	}

	public static function getVideo(string $videoId)
	{
		$service = new MediaVkontakte();
		$service->setService(static::getInstance());

		return $service->getVideo($videoId);
	}
}

