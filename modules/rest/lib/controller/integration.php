<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Main\Web\Uri;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\Service\ServiceContainer;

class Integration extends \Bitrix\Main\Engine\Controller
{
	public function getApplicationListAction(int $limit): array
	{
		$collection = ServiceContainer::getInstance()->getAppService()->getPaidApplications($limit);
		$appCodes = $collection->getAppCodes();

		$applications = [];
		if (!empty($appCodes))
		{
			$appsBuy = Client::getBuy($appCodes);
			if (isset($appsBuy['ITEMS']) && is_array($appsBuy['ITEMS']))
			{
				foreach ($appsBuy['ITEMS'] as $key => $app) {
					$applications[] = [
						'name' => htmlspecialcharsbx($app['NAME']) ?? null,
						'icon' => $app['ICON'] ? Uri::urnEncode($app['ICON']) : null
					];

					if ($limit > 0 && count($applications) === $limit)
					{
						break;
					}
				}
			}
		}

		return [
			'count' => $collection->count(),
			'items' => $applications
		];
	}

	public function getIntegrationListAction(int $limit): array
	{
		$collection = ServiceContainer::getInstance()->getIntegrationService()->getPaidIntegrations($limit);

		$integrations = [];
		foreach ($collection as $integration)
		{
			$integrations[] = [
				'name' => htmlspecialcharsbx($integration->getTitle())
			];

			if ($limit > 0 && count($integrations) === $limit)
			{
				break;
			}
		}

		return [
			'count' => $collection->count(),
			'items' => $integrations
		];
	}
}