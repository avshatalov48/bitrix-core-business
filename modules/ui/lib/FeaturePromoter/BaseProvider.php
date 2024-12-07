<?php

namespace Bitrix\UI\FeaturePromoter;

abstract class BaseProvider implements FeaturePromoterProvider
{
	public function __construct(protected readonly ProviderConfiguration $configuration)
	{
	}
}