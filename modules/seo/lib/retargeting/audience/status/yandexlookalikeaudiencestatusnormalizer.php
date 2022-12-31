<?php

namespace Bitrix\Seo\Retargeting\Audience\Status;

use Bitrix\Seo\Retargeting\Services\AudienceYandex;

class YandexLookalikeAudienceStatusNormalizer extends AbstractAudienceStatusNormalizer
{
	protected const AUDIENCE_TYPE_CODE = AudienceYandex::TYPE_CODE;

	protected array $originalStatusToNormalizedMap = [
		'uploaded' => self::NORMALIZED_STATUS_PROCESSING,
		'is_processed' => self::NORMALIZED_STATUS_PROCESSING,
		'processed' => self::NORMALIZED_STATUS_READY,
		'processing_failed' => self::NORMALIZED_STATUS_OTHER,
		'is_updated' => self::NORMALIZED_STATUS_PROCESSING,
		'few_data' => self::NORMALIZED_STATUS_OTHER,
	];

	public function isEnabled(?string $originalStatus): bool
	{
		return $originalStatus === 'processed';
	}
}