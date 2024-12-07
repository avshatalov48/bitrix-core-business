<?php

namespace Bitrix\Seo\Checkout;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\SystemException;
use Bitrix\Seo\Service as SeoService;

/**
 * Class AuthAdapter
 * @package Bitrix\Seo\Checkout
 */
class AuthAdapter
{
	/** @var  IService $service */
	protected $service;
	protected $type;
	protected $data;

	/** @var array $parameters Parameters. */
	protected $parameters = ['URL_PARAMETERS' => []];

	/**
	 * AuthAdapter constructor.
	 * @param $type
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}

	/**
	 * @param $type
	 * @param IService|null $service
	 * @return AuthAdapter
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function create($type, IService $service = null)
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new SystemException('Module "socialservices" not installed.');
		}
		$instance = new static($type);
		if ($service)
		{
			$instance->setService($service);
		}

		return $instance;
	}

	/**
	 * @param IService $service
	 * @return $this
	 */
	public function setService(IService $service)
	{
		$this->service = $service;
		return $this;
	}

	/**
	 * @param array $parameters
	 * @return $this
	 */
	public function setParameters(array $parameters = [])
	{
		$this->parameters = $parameters + $this->parameters;
		return $this;
	}

	/**
	 * @return string
	 * @throws SystemException
	 */
	public function getAuthUrl()
	{
		if (!SeoService::isRegistered())
		{
			try
			{
				SeoService::register();
			}
			catch (SystemException $e)
			{
				return '';
			}
		}

		$authorizeUrl = SeoService::getAuthorizeLink();
		$authorizeData = SeoService::getAuthorizeData($this->getEngineCode());
		$uri = new Uri($authorizeUrl);
		if (!empty($this->parameters['URL_PARAMETERS']))
		{
			$authorizeData['urlParameters'] = $this->parameters['URL_PARAMETERS'];
		}
		$uri->addParams($authorizeData);

		return $uri->getLocator();
	}

	/**
	 * @param bool $isUseCache
	 * @return bool
	 */
	protected function getAuthData($isUseCache = true)
	{
		if (!$isUseCache || !$this->data || count($this->data) == 0)
		{
			$this->data = SeoService::getAuth($this->getEngineCode());
		}

		return $this->data;
	}

	public function removeAuth()
	{
		$this->data = array();

		if ($existedAuthData = $this->getAuthData(false))
		{
			SeoService::clearAuth($this->getEngineCode());
		}
	}

	/**
	 * @return string
	 */
	protected function getEngineCode()
	{
		if ($this->service)
		{
			return $this->service->getEngineCode($this->type);
		}
		else
		{
			return Service::getEngineCode($this->type);
		}
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return mixed|null
	 */
	public function getToken()
	{
		$data = $this->getAuthData();
		return $data ? $data['access_token'] : null;
	}

	/**
	 * @return bool
	 */
	public function hasAuth()
	{
		return $this->getToken() <> '';
	}
}