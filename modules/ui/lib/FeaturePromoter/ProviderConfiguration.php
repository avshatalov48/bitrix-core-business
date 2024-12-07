<?php

namespace Bitrix\UI\FeaturePromoter;

class ProviderConfiguration
{
	public function __construct(
		public readonly string $type,
		public string $code,
		public readonly string $currentUrl,
		public readonly ?string $featureId
	)
	{
	}
}