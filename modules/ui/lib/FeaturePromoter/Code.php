<?php

namespace Bitrix\UI\FeaturePromoter;

class Code
{
	private string $featureId;

	public function __construct(string $featureId)
	{
		$this->featureId = $featureId;
	}

	public function get(): string
	{
		return "limit_{$this->getVersion()}_$this->featureId";
	}

	private function getVersion(): string
	{
		return 'v2';
	}
}