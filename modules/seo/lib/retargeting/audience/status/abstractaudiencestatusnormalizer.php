<?php

namespace Bitrix\Seo\Retargeting\Audience\Status;

use Bitrix\Main\Localization\Loc;

abstract class AbstractAudienceStatusNormalizer implements AudienceStatusNormalizerInterface
{
	protected const AUDIENCE_TYPE_CODE = '';

	public const NORMALIZED_STATUS_READY = 'READY';
	public const NORMALIZED_STATUS_PROCESSING = 'PROCESSING';
	public const NORMALIZED_STATUS_OTHER = 'OTHER';

	protected array $originalStatusToNormalizedMap = [];

	/**
	 * Return normalized status by api response status
	 * Normalized status example: self::NORMALIZED_STATUS_READY, self::NORMALIZED_STATUS_PROCESSING
	 * @param string|null $originalStatus
	 * @return string
	 */
	public function getNormalizedStatus(?string $originalStatus): string
	{
		if ($originalStatus === null)
		{
			return self::NORMALIZED_STATUS_READY;
		}

		return $this->originalStatusToNormalizedMap[$originalStatus] ?? self::NORMALIZED_STATUS_PROCESSING;
	}

	/**
	 * Return normalized status translation by api response status
	 * @param string|null $originalStatus
	 * @return string
	 */
	public function getNormalizedStatusTranslation(?string $originalStatus): string
	{
		$normalizedStatus = $this->getNormalizedStatus($originalStatus);

		if (in_array($normalizedStatus, [self::NORMALIZED_STATUS_READY, self::NORMALIZED_STATUS_PROCESSING], true))
		{
			return $this->getDefaultNormalizedStatusesTranslation($normalizedStatus);
		}

		return Loc::getMessage($this->getNormalizedStatusTranslationKey($originalStatus));
	}

	protected function getNormalizedStatusTranslationKey(string $originalStatus): string
	{
		$translationKeyPrefix = 'SEO_RETARGETING_SERVICE_AUDIENCE_STATUS_';
		$audienceTypeCodePrefix = strtoupper(static::AUDIENCE_TYPE_CODE) . '_';
		$originalStatusPostfix = strtoupper($originalStatus);

		return $translationKeyPrefix . $audienceTypeCodePrefix . $originalStatusPostfix;
	}

	/**
	 * @param self::NORMALIZED_STATUS_READY | self::NORMALIZED_STATUS_PROCESSING $status
	 * @return string
	 */
	protected function getDefaultNormalizedStatusesTranslation(string $status): string
	{
		return Loc::getMessage('SEO_RETARGETING_SERVICE_AUDIENCE_STATUS_DEFAULT_' . $status);
	}
}