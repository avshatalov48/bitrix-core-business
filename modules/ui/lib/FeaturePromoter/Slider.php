<?php

namespace Bitrix\UI\FeaturePromoter;

use Bitrix\Main\Loader;
use Bitrix\UI\Helpdesk;
use Bitrix\Bitrix24;

class Slider extends BaseProvider
{
	private const PATH_HELPDESK = '/widget2/show/code/';

	public function getRendererParameters(): array
	{
		$requestHelpdesk = new Helpdesk\Request(self::PATH_HELPDESK, [
			'url' => $this->configuration->currentUrl,
			'featurePromoterVersion' => 2,
			'isPromoEditionAvailable' => $this->isPromoEditionAvailable(),
		]);

		return [
			'frameUrlTemplate' => $requestHelpdesk->getPreparedUrl(),
			'code' => $this->configuration->code,
			'trialableFeatureList' => $this->getTrialableFeatureList(),
			'availableDomainList' => $requestHelpdesk->getUrl()->getDomain()->getList(),
		];
	}

	private function getTrialableFeatureList(): array
	{
		if (Loader::includeModule('bitrix24'))
		{
			return Bitrix24\Feature::getTrialableFeatureList();
		}

		return [];
	}

	private function isPromoEditionAvailable(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			if (Loader::includeModule('extranet') && !\CExtranet::isIntranetUser())
			{
				return false;
			}

			return Bitrix24\Feature::isPromoEditionAvailableByFeature($this->configuration->featureId ?? '');
		}

		return false;
	}
}