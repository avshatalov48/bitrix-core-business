<?php

namespace Bitrix\Seo\Retargeting;

use Bitrix\Seo;
use Bitrix\Seo\Retargeting;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Seo\Service;
use Bitrix\Seo\Service as SeoService;

class FacebookAuthAdapter extends AuthAdapter
{
	public function getAuthUrl()
	{
		$serviceUrl = (new Retargeting\ProxyRequest())->getServiceUrl(Seo\Service::SERVICE_URL);

		if (!Seo\Service::isRegistered())
		{
			try
			{
				Seo\Service::register($serviceUrl);
			}
			catch (SystemException $e)
			{
				return '';
			}
		}

		$authorizeUrl = (new Retargeting\ProxyRequest())->getServiceUrl(Seo\Service::getAuthorizeLink());

		$authorizeData = SeoService::getAuthorizeData(
			$this->getEngineCode(),
			$this->canUseMultipleClients() ? Service::CLIENT_TYPE_MULTIPLE : Service::CLIENT_TYPE_SINGLE
		);

		if (!empty($this->parameters['URL_PARAMETERS']))
		{
			$authorizeData['urlParameters'] = $this->parameters['URL_PARAMETERS'];
		}

		$uri = new Uri($authorizeUrl);

		return $uri->addParams($authorizeData)->getLocator();
	}
}
