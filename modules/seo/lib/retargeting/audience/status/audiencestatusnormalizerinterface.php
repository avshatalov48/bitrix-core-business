<?php

namespace Bitrix\Seo\Retargeting\Audience\Status;

interface AudienceStatusNormalizerInterface
{
	public function getNormalizedStatus(?string $originalStatus): string;
	public function getNormalizedStatusTranslation(?string $originalStatus): string;
	public function isEnabled(?string $originalStatus): bool;
}