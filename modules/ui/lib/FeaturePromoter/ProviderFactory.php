<?php

namespace Bitrix\UI\FeaturePromoter;

class ProviderFactory
{
	public function createProvider(ProviderConfiguration $configuration): FeaturePromoterProvider
	{
		if ($configuration->featureId)
		{
			$configuration->code = (new Code($configuration->featureId))->get();
		}

		if ($configuration->type === ProviderType::POPUP)
		{
			return (new Popup($configuration));
		}

		return (new Slider($configuration));
	}
}