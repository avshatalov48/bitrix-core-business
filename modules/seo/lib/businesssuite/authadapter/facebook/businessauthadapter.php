<?php

namespace Bitrix\Seo\BusinessSuite\AuthAdapter\Facebook;

use Bitrix\Main\SystemException;
use Bitrix\Seo;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Seo\BusinessSuite\AuthAdapter\IAuthSettings;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;
use InvalidArgumentException;

final class BusinessAuthAdapter extends Retargeting\AuthAdapter
{
	/** @var IAuthSettings $installs*/
	private $setup;

	/** @var IAuthSettings $installs*/
	private $config;

	/** @var IAuthSettings $installs*/
	private $installs;

	public function getAuthUrl()
	{
		if (!Seo\Service::isRegistered())
		{
			try
			{
				Seo\Service::register();
			}
			catch (SystemException $e)
			{
				return '';
			}
		}

		$authorizeUrl = Seo\Service::getAuthorizeLink();
		$authorizeData = Seo\Service::getAuthorizeData(
			$this->getEngineCode(),
			$this->canUseMultipleClients() ? Seo\Service::CLIENT_TYPE_MULTIPLE : Seo\Service::CLIENT_TYPE_SINGLE
		);
		$uri = new Uri($authorizeUrl);
		if(!empty($this->parameters['URL_PARAMETERS']))
		{
			$authorizeData['urlParameters'] = $this->parameters['URL_PARAMETERS'];
		}
		if($this->setup && $this->config)
		{
			$authorizeData['settings'] = Json::encode([
				'setup' => $this->setup->toArray() + ($this->installs? $this->installs->toArray() : []),
				'business_config' => $this->config->toArray(),
				'repeat' => false
			]);
		}

		$uri->addParams($authorizeData);
		return $uri->getLocator();
	}

	/**
	 * @param IAuthSettings $setup
	 *
	 * @return $this
	 */
	public function setSetup(IAuthSettings $setup = null): self
	{
		if($setup instanceof Facebook\Setup)
		{
			$this->setup = $setup;
			return $this;
		}
		throw new InvalidArgumentException("Argument is not instance of Facebook/Setup");
	}

	/**
	 * @param IAuthSettings $config
	 *
	 * @return $this
	 */
	public function setConfig(IAuthSettings $config = null): self
	{
		if($config instanceof Facebook\Config)
		{
			$this->config = $config;
			return $this;
		}
		throw new InvalidArgumentException("Argument is not instance of Facebook/Config");
	}

	public function setInstalls(?IAuthSettings $installs) : self
	{
		if($installs instanceof Facebook\Installs)
		{
			$this->installs = $installs;
			return $this;
		}
		throw new InvalidArgumentException("Argument is not instance of Facebook/Installs");
	}
}