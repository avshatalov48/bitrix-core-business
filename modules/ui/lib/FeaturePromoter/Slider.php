<?php

namespace Bitrix\UI\FeaturePromoter;

use Bitrix\Main\Loader;
use Bitrix\UI\Helpdesk;
use Bitrix\Bitrix24;

class Slider implements FeaturePromoterProvider
{
	private const PATH_HELPDESK = '/widget2/show/code/';

	public function __construct(private string $currentUrl = '')
	{
	}

	public function getRendererParameters(): array
	{
		$requestHelpdesk = new Helpdesk\Request(self::PATH_HELPDESK, [
			'url' => $this->currentUrl,
			'featurePromoterVersion' => 2,
		]);

		return [
			'frameUrlTemplate' => $requestHelpdesk->getPreparedUrl(),
			'trialableFeatureList' => $this->getTrialableFeatureList(),
			'demoStatus' => $this->getDemoStatus(),
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

	private function getDemoStatus(): string
	{
		if (Loader::includeModule('bitrix24'))
		{
			if (\CBitrix24::IsDemoLicense())
			{
				return 'ACTIVE';
			}

			if (Bitrix24\Feature::isEditionTrialable('demo'))
			{
				return 'AVAILABLE';
			}

			return 'EXPIRED';
		}

		return 'UNKNOWN';
	}
}