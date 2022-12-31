<?php

namespace Bitrix\Seo\Retargeting\Audience\Status;

class OldAudienceStatusNormalizer extends AbstractAudienceStatusNormalizer
{
	public const PROCESSING_ORIGINAL_STATUS = 'is_processed';

	public function getNormalizedStatus(?string $originalStatus): string
	{
		return $originalStatus === self::PROCESSING_ORIGINAL_STATUS
			? self::NORMALIZED_STATUS_PROCESSING
			: self::NORMALIZED_STATUS_READY
		;
	}

	public function isEnabled(?string $originalStatus): bool
	{
		return $this->getNormalizedStatus($originalStatus) === self::NORMALIZED_STATUS_READY;
	}
}