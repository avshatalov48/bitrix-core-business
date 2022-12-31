<?php

namespace Bitrix\Seo\Retargeting\Audience\Status;

use Bitrix\Sender\Integration\Seo\Ads\MessageLookalikeYandex;
use Bitrix\Sender\Integration\Seo\Ads\MessageYa;
use Bitrix\Seo\Retargeting\Audience;

class AudienceStatusNormalizerFactory
{
	public static function build(string $typeCode, string $messageCode): AudienceStatusNormalizerInterface
	{
		switch (true)
		{
			case Audience::TYPE_YANDEX === $typeCode && $messageCode === MessageYa::CODE:
				return new YandexAudienceStatusNormalizer();
			case Audience::TYPE_YANDEX === $typeCode && $messageCode === MessageLookalikeYandex::CODE:
				return new YandexLookalikeAudienceStatusNormalizer();
			default:
				return new OldAudienceStatusNormalizer();
		}
	}
}